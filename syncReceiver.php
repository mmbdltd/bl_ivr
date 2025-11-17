<?php
// declare(strict_types=1);
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

require_once __DIR__ . '/core/db.php';
require_once __DIR__ . '/core/redis.php';
require_once __DIR__ . '/core/token.php';
require_once __DIR__ . '/utils/helpers.php';
require_once __DIR__ . '/syncToSsdTech.php';
date_default_timezone_set('Asia/Dhaka');

$serviceConfigurations = require __DIR__ . '/config/service_config.php';
$config = require __DIR__ . '/config/config.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents("php://input"), true);

$logDir = $config['webhook_log_dir'] ?? __DIR__ . '/logs';
$logFile = $logDir . '/webhook_' . date('Y-m-d') . '.log';

if (!is_dir($logDir)) {
    mkdir($logDir, 0775, true);
    chown($logDir, '_www');
}
file_put_contents($logFile, date('c') . " - " . json_encode($input) . PHP_EOL, FILE_APPEND);

if (
    !isset($input['msisdn']) ||
    !isset($input['featureId']) ||
    !isset($input['requestParam']['data']) ||
    !is_array($input['requestParam']['data'])
) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid webhook format']);
    exit;
}

processSsdTech($input);

$msisdn = trim($input['msisdn']);
$featureId = trim($input['featureId']);
$command = trim($input['requestParam']['command'] ?? 'Unknown');
$requestId = $input['requestId'] ?? uniqid('REQ_', true);
$payload = json_encode($input, JSON_UNESCAPED_UNICODE);
$subscriptionOfferId = $input['requestParam']['subscriptionOfferID'] ?? $input['requestParam']['planId'] ?? null;
$milliseconds = substr(date('u'), 0, 3);
$timestamp = date('Y-m-d H:i:s:') . $milliseconds;

// Flatten data section
$data = [
    'TransactionId' => null,
    'ClientTransactionId' => null,
    'ChargeAmount' => 0,
    'SubscriptionStatus' => null,
    'Reason' => null,
    'Type' => null,
    'OfferCode' => $subscriptionOfferId,
    'ShortCode' => null,
    'BillingId' => null,
    'Language' => null,
    'Channel' => null,
    'SubscriberLifeCycle' => null,
    'NextBillingDate' => null,
    'subscriptionOfferId' => $subscriptionOfferId,
];
foreach ($input['requestParam']['data'] as $item) {
    $key = trim($item['name']);
    $value = trim($item['value']);
    $data[$key] = $value;
}

$offerCode = $data['OfferCode'] ?? $subscriptionOfferId;
$actionType = mapActionType($data['SubscriptionStatus'], $data['SubscriberLifeCycle']);
$serviceConfig = $serviceConfigurations['serviceConfig'];
$serviceName = $serviceConfig[$offerCode] ?? 'UNKNOWN';
$ShortCode = $data['ShortCode'] ?? '16303';

if ($actionType === 'UNKNOWN' || ($data['Reason'] ?? '') === 'Consent Expired') {
    $data['SubscriptionStatus'] = $data['SubscriptionStatus'] ?? 'E';
    $data['SubscriberLifeCycle'] = $data['SubscriberLifeCycle'] ?? 'Consent Expired';
    $data['Type'] = $data['Type'] ?? 'Expired';
    $actionType = 'Expired';
}

$operatorCode = 1;
$subscriberId = 0;

function logToRedis($redis, $msisdn, $featureId, $command, $requestId, $input)
{
    try {
        $keyDate = date('Y-m-d');
        $key = "IVR:webhook:$keyDate:$featureId:$command:$msisdn";

        $entry = json_encode([
            'timestamp' => date('c'),
            'requestId' => $requestId,
            'data' => $input
        ]);

        if ($redis->type($key) != 'list') {
            $redis->del($key);
        }

        $redis->rPush($key, $entry);
        $redis->expire($key, 60 * 60 * 24 * 45); // 45 days
    } catch (Exception $e) {
        error_log("Redis logging failed: " . $e->getMessage());
    }
}

function logWebhookToDb($pdo, $requestId, $msisdn, $offerCode, $serviceName, $actionType, $data, $featureId, $command, $payload)
{
    try {
        $stmt = $pdo->prepare("
            INSERT INTO webhook_logs (
                                      request_id,
                                      msisdn,
                                      offer_code,
                                      service_name,
                                      event_type,
                                      action_type,
                                      subscription_status,
                                      subscriber_lifecycle,
                                      feature_id,
                                      command,
                                      reason,
                                      payload
                                      )
                                VALUES
                                    (
                                        ?,
                                        ?,
                                        ?,
                                        ?,
                                        ?,
                                        ?,
                                        ?,
                                        ?,
                                        ?,
                                        ?,
                                        ?,
                                        ?
                                    )
        ");
        $webhookDataValue = [
            $requestId,
            $msisdn,
            $offerCode,
            $serviceName,
            $data['Type'] ?? 'UNKNOWN',
            $actionType,
            $data['SubscriptionStatus'],
            $data['SubscriberLifeCycle'],
            $featureId,
            $command,
            $data['Reason'],
            $payload
        ];

        $stmt->execute($webhookDataValue);
//        if ($stmt->execute($webhookDataValue)) {
//            echo "Webhook log insert successful" . PHP_EOL;
//        } else {
//            $errorInfo = $stmt->errorInfo();
//            echo "Webhook log insert failed: " . json_encode($errorInfo) . PHP_EOL;
//        }

    } catch (PDOException $e) {
        echo $e->getMessage() . PHP_EOL;
        error_log("Webhook log DB error: " . $e->getMessage() . " | Payload: " . $payload);
    }
}

function updateOrInsertSubscriber($pdo, $msisdn, $subscriptionOfferId, $operatorCode, $ShortCode, $offerCode, $serviceName, $actionType, $data, $command)
{
    $subscriberId = 0;

    try {
        if ($actionType == 'Expired') {
            return $subscriberId;
        }
        $chkQuery = "
            SELECT id FROM subscribers
            WHERE msisdn = ? AND subscription_offer_id = ? AND operator_code = ? and shortcode= ?
            ORDER BY id DESC LIMIT 1
        ";
        $checkStmt = $pdo->prepare($chkQuery);
        $checkStmt->execute([$msisdn, $subscriptionOfferId, $operatorCode, $ShortCode]);
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
        $subscriberId = $existing['id'] ?? 0;
        //success_count,grease_count,suspend_count
        $successCount = $greaseCount = $suspendCount = 0;
        if ($actionType == 'ACTIVE') $successCount = 1;
        if ($actionType == 'ACTIVATION_PARKING') $greaseCount = 1;
        if ($actionType == 'SUSPEND' || $actionType == 'DEACTIVE') $suspendCount = 1;
        if ($existing) {
            $updateQuery = "
                UPDATE subscribers SET
                    offer_code = ?,
                    service_name = ?,
                    action_type = ?,
                    subscription_status = ?,
                    subscriber_lifecycle = ?,
                    transaction_id = ?,
                    client_transaction_id = ?,
                    billing_id = ?,
                    charge_amount = IFNULL(charge_amount, 0) + ?,
                    success_count = IFNULL(success_count, 0) + ?,
                    grease_count = IFNULL(grease_count, 0) + ?,
                    suspend_count = IFNULL(suspend_count, 0) + ?,
                    reason = ?,
                    command = ?,
                    updated_at = NOW()
                WHERE id = ?
            ";
            $updateStmt = $pdo->prepare($updateQuery);
            $subscribersProperty = [
                $offerCode,
                $serviceName,
                $actionType,
                $data['SubscriptionStatus'],
                $data['SubscriberLifeCycle'],
                $data['TransactionId'],
                $data['ClientTransactionId'],
                $data['BillingId'],
                (float)($data['ChargeAmount'] ?? 0),
                $successCount,
                $greaseCount,
                $suspendCount,
                $data['Reason'],
                $command,
                $existing['id']
            ];
            $updateStmt->execute($subscribersProperty);
        } else {
            $insertQuery = "
                INSERT INTO subscribers (
                    msisdn,
                    subscription_offer_id,
                    operator_code,
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
                    command
                ) VALUES
                      (
                          ?,
                          ?,
                          ?,
                          ?,
                          ?,
                          ?,
                          ?,
                          ?,
                          ?,
                          ?,
                          ?,
                          ?,
                          ?,
                          ?,
                          ?,
                          ?,
                          ?,
                          ?,
                          ?
                      )";
            $insertStmt = $pdo->prepare($insertQuery);
            $subscribersProperty = [
                $msisdn,
                $subscriptionOfferId,
                $operatorCode,
                $offerCode,
                $serviceName,
                $actionType,
                $data['SubscriptionStatus'],
                $data['SubscriberLifeCycle'],
                $data['TransactionId'],
                $data['ClientTransactionId'],
                $data['Channel'],
                $ShortCode,
                $data['BillingId'],
                (float)($data['ChargeAmount'] ?? 0),
                $successCount,
                $greaseCount,
                $suspendCount,
                $data['Reason'],
                $command
            ];
            $insertStmt->execute($subscribersProperty);

//            if ($insertStmt->execute($subscribersProperty)) {
//                echo "subscribers insert successful" . PHP_EOL;
//            } else {
//                $errorInfo = $insertStmt->errorInfo();
//                echo "subscribers insert failed: " . json_encode($errorInfo) . PHP_EOL;
//            }
            $subscriberId = $pdo->lastInsertId();
        }
    } catch (PDOException $e) {
//        print_r($e->getMessage());
        error_log("Subscriber DB error: " . $e->getMessage());
    }
    return $subscriberId;
}

// 1️⃣ Redis Log
global $redis;
logToRedis($redis, $msisdn, $featureId, $command, $requestId, $input);

// 2️⃣ Webhook Logs
global $pdo;
logWebhookToDb($pdo, $requestId, $msisdn, $offerCode, $serviceName, $actionType, $data, $featureId, $command, $payload);

// 3️⃣ Subscribers Table
$subscriberId = updateOrInsertSubscriber($pdo, $msisdn, $subscriptionOfferId, $operatorCode, $ShortCode, $offerCode, $serviceName, $actionType, $data, $command);

// 4️⃣ SMS Sending Logic ---------------------------------------
$templateType = null;
if ($actionType === 'ACTIVE' && $data['SubscriberLifeCycle'] === 'SUB1') {
    $templateType = 'ACTIVE';
} elseif ($actionType === 'ACTIVATION_PARKING' && $data['SubscriberLifeCycle'] === 'SUB2') {
    $templateType = 'ACTIVATION_PARKING';
} elseif (
    ($actionType === 'SUSPEND' && $data['SubscriberLifeCycle'] === 'SUB3') ||
    ($actionType === 'DEACTIVE' && $data['SubscriberLifeCycle'] === 'UNSUB1')
) {
    $templateType = 'DEACTIVATE';
}

$smsTemplates = $serviceConfigurations['smsTemplates'];
if ($templateType && isset($smsTemplates[$templateType][$serviceName])) {
    $smsText = $smsTemplates[$templateType][$serviceName];

    if ($templateType === 'ACTIVE') {
        $smsText = str_replace('{date}', $data['NextBillingDate'] ?? '', $smsText);
    }
    send_sms($msisdn, $smsText, $ShortCode, $offerCode, $config, $pdo, $redis);
}

// Content message for SUB1 or REN1
if (in_array($data['SubscriberLifeCycle'], ['SUB1', 'REN1'])) {
    global $contentPdo;
    $smsText = get_content_message($serviceName, $contentPdo);

    if ($smsText) {
        send_sms($msisdn, $smsText, $ShortCode, $offerCode, $config, $pdo, $redis);
    }
}

$returnToSDP = [
    "requestId" => $requestId,
    "responseId" => "$subscriberId",
    "requestTimeStamp" => $timestamp,
    "responseTimeStamp" => $timestamp,
    "responseCode" => "0",
    "featureId" => "DataSync",
    "resultParam" => [
        "resultCode" => "981",
        "resultDescription" => "Success",
        "subscriptionOfferID" => $subscriptionOfferId
    ]
];

echo json_encode($returnToSDP);
