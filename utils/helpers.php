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

/**
 * Get subscriber data by MSISDN excluding Deactivated users.
 * Returns an array of objects.
 *
 * @param PDO $pdo Active PDO connection
 * @param string $msisdn Mobile number (can be empty string based on your query)
 * @param string $operatorCode Default '1'
 * @return array List of row objects
 */
function getActiveSubscriberByMsisdn(string $msisdn = '', string $operatorCode = '1'): array
{
    global $pdo;

    $sql = "SELECT
            id,
            msisdn,
            subscription_offer_id,
            operator_code,
            operator,
            offer_code,
            service_name,
            action_type,
            subscription_status,
            subscriber_lifecycle,
            transaction_id,
            client_transaction_id,
            channel,
            shortcode,
            billing_id,
            charge_amount,
            success_count,
            grease_count,
            suspend_count,
            reason,
            command,
            created_at,
            updated_at
        FROM ivr_subscription.subscribers
        WHERE msisdn = :msisdn
          AND operator_code = :operatorCode
          AND subscription_status NOT IN ('S','D')
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':msisdn' => $msisdn,
        ':operatorCode' => $operatorCode,
    ]);

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
