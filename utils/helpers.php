<?php
$config = require __DIR__ . '/../config/config.php';
date_default_timezone_set('Asia/Dhaka');
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/SmsSender.php';
require_once __DIR__ . '/../config/service_config.php';

/**
 * Generate the password hash required by SDP APIs.
 */
function generatePasswordHash($cpid, $password, $timestamp): string
{
    return md5($cpid . $password . $timestamp);
}

/**
 * Generate a random request ID.
 */
function generateRandomId($length = 16): string
{
    return str_pad((string)mt_rand(1, 9999999999999999), $length, '0', STR_PAD_LEFT);
}

function mapActionType($status, $lifecycle): string
{
    //enum('ACTIVE', 'ACTIVATION_PARKING', 'SUSPEND', 'GRACE', 'DEACTIVE')
    $map = [
        'A' => ['SUB1' => 'ACTIVE', 'REN1' => 'ACTIVE'],
        'G' => ['SUB2' => 'ACTIVATION_PARKING', 'REN2' => 'GRACE'],
        'S' => ['SUB3' => 'SUSPEND', 'REN3' => 'SUSPEND'],
        'D' => ['UNSUB1' => 'DEACTIVE', 'UNSUB2' => 'DEACTIVE'],
    ];
    return $map[$status][$lifecycle] ?? 'UNKNOWN';
}

/**
 * Fetch a daily content message by service.
 */
function get_content_message($serviceName, $contentPdo)
{
    global $serviceConfigurations;
    $serviceKeywordMap = $serviceConfigurations['serviceKeywordMap'];
    $keyword = $serviceKeywordMap[$serviceName] ?? null;

    if (!$keyword) return null;
    // $today = date('Y-m-d');

    $contentQuery = "SELECT id as content_id, trim(message) as message FROM contents
         WHERE keyword = ? AND DATE(scheduled_at) = DATE(NOW()) AND status = 1
         ORDER BY id ASC LIMIT 1";

    $stmt = $contentPdo->prepare($contentQuery);
    $stmt->execute([$keyword]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row['message'] ?? null;
}

/**
 * Send Unicode SMS using Kannel.
 */
function sendUnicodeSmsViaKannel($to, $message, $from = '16303'): array
{
    global $config;
    $params = [
        'username' => $config['kannel']['username'] ?? 'bluser',
        'password' => $config['kannel']['password'] ?? 'banglalink54',
        'to' => $to,
        'from' => $from,
        'text' => $message,
        'coding' => $config['kannel']['coding'] ?? 2,
        'charset' => $config['kannel']['charset'] ?? 'UTF-8',
    ];
    $kannelUrl = $config['kannel']['charset'] ?? 'http://192.168.33.37:13131/cgi-bin/sendsms';
    $url = $kannelUrl . '?' . http_build_query($params);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        return ['success' => false, 'error' => $err];
    }
    return ['success' => true, 'response' => $result];
}


/**
 * Send an SMS using SmsSender class, defaulting to config gateway.
 */
function send_sms($msisdn, $text, $shortCode, $offerCode, $config, $pdo, $redis): void
{
    $smsSender = new SmsSender($config, $pdo, $redis);
    $result = $smsSender->send($msisdn, $text, $shortCode, $offerCode);

    if (!$result['success']) {
        error_log('SMS failed: ' . print_r($result, true));
    }
}

/**
 * If user already subscribed, send them a notification SMS.
 */
function sendAlreadySubscribedSms($responseData, $msisdn, $serviceName, $shortCode, $offerCode, $config, $pdo, $redis): void
{
    global $serviceConfigurations;
    $smsTemplates = $serviceConfigurations['smsTemplates'];

    if (
        isset($responseData['responseCode'], $responseData['resultParam']['resultDescription']) &&
        $responseData['responseCode'] == '1' &&
        strtolower($responseData['resultParam']['resultDescription']) === 'subscription already exists'
    ) {
        $smsText = $smsTemplates['ALREADY_SUBSCRIBED'][$serviceName] ?? null;
        if ($smsText) {
            send_sms($msisdn, $smsText, $shortCode, $offerCode, $config, $pdo, $redis);
        }
    }
}
