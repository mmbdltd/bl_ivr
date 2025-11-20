<?php
//declare(strict_types=1);

require_once __DIR__ . '/../core/token.php';
require_once __DIR__ . '/../core/redis.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../utils/helpers.php';

date_default_timezone_set('Asia/Dhaka');

$config = require __DIR__ . '/../config/config.php';
$serviceConfigurations = require __DIR__ . '/../config/service_config.php'; // Contains $activationKeywords, $smsTemplates

function activateFeature($params): array
{
    global $config, $redis, $pdo, $serviceConfigurations;

    $timestamp = date('Y-m-d H:i:s');
    $token = getAccessToken();
    if (!$token) {
        return ['error' => 'Token missing'];
    }

    $featureId = strtoupper($params['featureId'] ?? 'ACTIVATION');
    $url = $featureId === 'CONSENT'
        ? $config['sdp']['consent_url']
        : $config['sdp']['activation_url'];

    $requestId = $params['requestId'] ?? generateRandomId();

    $payload = [
        "requestId" => $requestId,
        "requestTimeStamp" => $timestamp,
        "channel" => $config['sdp']['channel'],
        "sourceNode" => $config['sdp']['sourceNode'],
        "sourceAddress" => $config['sdp']['sourceAddress'],
        "featureId" => $featureId,
        "username" => $config['sdp']['username'],
        "password" => generatePasswordHash($config['sdp']['cpid'], $config['sdp']['password'], $timestamp),
        "externalServiceId" => $params['msisdn'],
        "requestParam" => [
            "subscriptionOfferID" => $params['chargecode'],
            "cpId" => $config['sdp']['cpid']
        ]
    ];

    if ($featureId === 'CONSENT' && isset($params['consentNo'])) {
        $payload['requestParam']['consentNo'] = $params['consentNo'];
    }

    $headers = [
        'X-Requested-With: XMLHttpRequest',
        'Content-Type: application/json',
        'X-Authorization: Bearer ' . $token
    ];
    $response = apiRequest('POST', $url, $payload, $headers);

    $serviceName = 'UNKNOWN';
    $serviceConfig = $serviceConfigurations['serviceConfig'];

    foreach ($serviceConfig['services'] as $key => $svc) {
        if ($svc['subscriptionOfferId'] == $params['chargecode']) {
            $serviceName = $key;
            break;
        }
    }

    // Send "Already Subscribed" SMS if response says so
    sendAlreadySubscribedSms(
        $response,
        $params['msisdn'],
        $serviceName,
        $params['shortCode'] ?? '16303',
        $params['chargecode'],
        $config,
        $pdo,
        $redis
    );

    // Redis logging
    $logKey = "IVR:request:" . date('Y-m-d') . ":$serviceName:$featureId:{$params['msisdn']}";
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'requestId' => $requestId,
        'data' => [
            'url' => $url,
            'body' => $payload,
            'response' => $response
        ]
    ];
    $redis->rPush($logKey, json_encode($logEntry, JSON_UNESCAPED_UNICODE));
    $redis->expire($logKey, 90 * 24 * 60 * 60);

    return $response;
}

function deactivateFeature($params): array
{
    global $redis, $serviceConfigurations;
    $config = require __DIR__ . '/../config/config.php';
    $timestamp = date('Y-m-d H:i:s.000');
    $token = getAccessToken();
    if (!$token) {
        return ['error' => 'Token missing'];
    }

    $url = $config['sdp']['unsub_url'];
    $requestId = $params['requestId'] ?? generateRandomId();

    $payload = [
        "requestId" => $requestId,
        "requestTimeStamp" => $timestamp,
        "channel" => $config['sdp']['channel'] ?? "4",
        "sourceNode" => $config['sdp']['sourceNode'],
        "sourceAddress" => $config['sdp']['sourceAddress'],
        "featureId" => $params['featureId'],
        "username" => $config['sdp']['username'],
        "password" => generatePasswordHash($config['sdp']['cpid'], $config['sdp']['password'], $timestamp),
        "externalServiceId" => $params['msisdn'],
        "requestParam" => [
            "subscriptionOfferID" => $params['chargecode'],
            "cpId" => $config['sdp']['cpid']
        ]
    ];

    $headers = [
        'X-Requested-With: XMLHttpRequest',
        'Content-Type: application/json',
        'X-Authorization: Bearer ' . $token
    ];
    $response = apiRequest('POST', $url, $payload, $headers);

    $serviceConfig = $serviceConfigurations['serviceConfig'];
 //   $serviceName = 'UNKNOWN';
//    foreach ($serviceConfig['services'] as $key => $svc) {
//        if ($svc['subscriptionOfferId'] == $params['chargecode']) {
//            $serviceName = $key;
//            break;
//        }
//    }
    $chargeCode = $params['chargecode'];
    $serviceName = $serviceConfig[$chargeCode];
    // Redis logging
    $logKey = "IVR:request:" . date('Y-m-d') . ":$serviceName:UNSUBSCRIPTION:{$params['msisdn']}";
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'requestId' => $requestId,
        'data' => [
            'url' => $url,
            'body' => $payload,
            'response' => $response
        ]
    ];
    $redis->rPush($logKey, json_encode($logEntry, JSON_UNESCAPED_UNICODE));
    $redis->expire($logKey, 90 * 24 * 60 * 60);

    return $response;
}
