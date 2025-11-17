<?php

require_once __DIR__ . '/../core/token.php';
date_default_timezone_set('Asia/Dhaka');

/**
 * SMS sending utility, supports Kannel and Banglalink SDP.
 */
class SmsSender
{
    private array $config;
    private PDO $pdo;
    private Redis $redis;
    private string $apiToken;
    private int $maxRetries = 1;
    private int $retryDelay = 10; // seconds

    /**
     * SmsSender constructor.
     * @param array $config
     * @param PDO $pdo
     * @param Redis $redis
     * @param string|null $token
     */
    public function __construct(array $config, PDO $pdo, Redis $redis, ?string $token = null)
    {
        $this->config = $config;
        $this->pdo = $pdo;
        $this->redis = $redis;
        $this->apiToken = $token ?? getAccessToken();
    }

    /**
     * Main SMS send function, chooses between SDP and Kannel based on config.
     */
    public function send(
        string  $msisdn,
        string  $text,
        string  $shortCode,
        ?string $correlatorId = null
    ): array
    {
        $mode = $this->config['sms_mode'] ?? 'kannel';
        return [
            'success' => 1,
            'gateway' => $mode,
            'http_code' => 200,
            'result' => [],
            'payload' => [],
        ];
//        return ($mode === 'sdp')
//            ? $this->sendViaSdp($msisdn, $text, $correlatorId, $shortCode)
//            : $this->sendViaKannel($msisdn, $text, $shortCode);
    }

    /**
     * Send SMS using Kannel gateway.
     */
    public function sendViaKannel(string $msisdn, string $message, string $from = '16303'): array
    {
        $params = [
            'username' => $this->config['kannel']['username'] ?? 'bluser',
            'password' => $this->config['kannel']['password'] ?? 'banglalink54',
            'to' => $msisdn,
            'from' => $from,
            'text' => $message,
            'coding' => $this->config['kannel']['coding'] ?? 2,
            'charset' => $this->config['kannel']['charset'] ?? 'UTF-8',
        ];
        $kannelUrl = $this->config['kannel']['sms_url'] ?? 'http://192.168.33.37:13131/cgi-bin/sendsms';
        $url = $kannelUrl . '?' . http_build_query($params);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        $success = $err === '' && str_contains($result, '0: Accepted');

        $this->saveLog('kannel', $msisdn, $params, $result, $success, 1);

        return [
            'success' => $success,
            'gateway' => 'kannel',
            'http_code' => $success ? 202 : 500,
            'result' => $result,
            'payload' => $params,
        ];
    }

    /**
     * Send SMS via SDP gateway (Banglalink API).
     */
    public function sendViaSdp(string $msisdn, string $text, ?string $correlatorId, string $offerCode): array
    {
        $payload = [
            'requestId' => uniqid('sms_', true),
            'requestTimeStamp' => date('Y-m-d H:i:s'),
            'channel' => $this->config['sdp']['channel'],
            'sourceNode' => $this->config['sdp']['sourceNode'],
            'sourceAddress' => $this->config['sdp']['sourceAddress'],
            'featureId' => 'SendSMS',
            'username' => $this->config['sdp']['username'],
            'password' => $this->config['sdp']['password'],
            'externalServiceId' => $msisdn,
            'requestParam' => [
                'linkId' => $correlatorId ?: uniqid(),
                'content' => $text,
                'cpId' => $this->config['sdp']['cpId'],
                'subscriptionOfferID' => $offerCode,
                'languageId' => '3'
            ]
        ];

        $headers = [
            'X-Requested-With: XMLHttpRequest',
            'Content-Type: application/json',
            'X-Authorization: Bearer ' . $this->apiToken
        ];

        $attempt = 0;
        $success = false;
        $response = '';
        $httpCode = 0;

        while ($attempt < $this->maxRetries && !$success) {
            $attempt++;
            $result = $this->callApi($payload, $headers);
            $httpCode = $result['http_code'];
            $response = $result['response'];
            $curlErr = $result['curl_error'];

            $parsed = json_decode($response, true);
            if ($curlErr) {
                $this->logError("Attempt $attempt CURL: $curlErr", $payload, $response);
            } elseif ($httpCode === 200 && isset($parsed['responseCode']) && $parsed['responseCode'] == '0') {
                $success = true;
            } else {
                $this->logError("Attempt $attempt HTTP $httpCode: $response", $payload, $response);
            }
            if (!$success && $attempt < $this->maxRetries) {
                sleep($this->retryDelay);
            }
        }

        $this->saveLog('sdp', $msisdn, $payload, $response, $success, $attempt);

        return [
            'success' => $success,
            'gateway' => 'sdp',
            'http_code' => $httpCode,
            'result' => $parsed ?? $response,
            'attempts' => $attempt,
            'payload' => $payload,
        ];
    }

    /**
     * Internal function to call SDP API endpoint.
     */
    private function callApi(array $payload, array $headers): array
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->config['sdp']['sms_url'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($curl);
        $curlErr = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return [
            'response' => $response,
            'curl_error' => $curlErr,
            'http_code' => $httpCode
        ];
    }

    /**
     * Save SMS logs to DB and Redis.
     */
    private function saveLog($gateway, $msisdn, $payload, $response, $success, $attempts): void
    {
        $log = [
            'gateway' => $gateway,
            'msisdn' => $msisdn,
            'sender' => $payload['from'],
            'smstext' => $payload['text'],
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'response' => $response,
            'success' => $success ? 1 : 0,
            'attempts' => $attempts,
            'created_at' => date('Y-m-d H:i:s')
        ];

        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO sms_logs (
                      gateway,
                      msisdn,
                      sender,
                      smstext,
                      payload,
                      response,
                      success,
                      attempts,
                      created_at
                      )
                 VALUES (
                         ?,
                         ?,
                         ?,
                         ?,
                         ?,
                         ?,
                         ?,
                         ?,
                         ?
                         )"
            );
            $stmt->execute([
                $log['gateway'],
                $log['msisdn'],
                $log['sender'],
                $log['smstext'],
                $log['payload'],
                $log['response'],
                $log['success'],
                $log['attempts'],
                $log['created_at']
            ]);
        } catch (PDOException $e) {
            error_log("SMS log DB error: " . $e->getMessage());
        }

        try {
            $key = "IVR:SMSLog:" . $log['gateway'] . ":" . date('Y-m-d') . ":$msisdn";
            $this->redis->rPush($key, json_encode($log, JSON_UNESCAPED_UNICODE));
            $this->redis->expire($key, 60 * 60 * 24 * 90);
        } catch (Exception $e) {
            error_log("SMS log Redis error: " . $e->getMessage());
        }
    }

    /**
     * Log error to error_log for audit.
     */
    private function logError($error, $payload, $response): void
    {
        error_log("[SMS_SEND_ERR] $error | PAYLOAD: " . json_encode($payload) . " | RESPONSE: $response");
    }
}
