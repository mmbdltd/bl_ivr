<?php
require_once __DIR__ . '/../core/sdp_feature.php'; // Contains activateFeature()
require_once __DIR__ . '/../core/redis.php';
require_once __DIR__ . '/../utils/helpers.php';
date_default_timezone_set('Asia/Dhaka');

$serviceConfigurations = require __DIR__ . '/../config/service_config.php'; // Contains $activationKeywords, $smsTemplates

header("Content-Type: application/json; charset=UTF-8");

$method = $_SERVER['REQUEST_METHOD'];
$uri    = strtok($_SERVER['REQUEST_URI'], '?');
$input  = $method === 'POST'
    ? json_decode(file_get_contents('php://input'), true)
    : $_GET;

// Shared responder
function respond($data, $code = 200)
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

if ("$method" === 'POST') {

    if (empty($input['calling_number']) || empty($input['called_number']) || empty($input['channel_id'])) {
        respond(['error' => 'Invalid request body'], 400);
    }

    $msisdn     = $input['phone_number'] ?? $input['calling_number'];
    $shortCode  = $input['called_number'] ?? '16303';
    $channelId  = $input['channel_id'] ?? '1';

    $subscriberData = getActiveSubscriberByMsisdn($msisdn, '1');
    $menuId = count($subscriberData) == 0 ? 1 : 2;

    respond(['menu_id' => $menuId]);
}
