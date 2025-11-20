<?php
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

require_once __DIR__ . '/../core/sdp_feature.php'; // Contains activateFeature()
require_once __DIR__ . '/../core/redis.php';
require_once __DIR__ . '/../utils/helpers.php';
date_default_timezone_set('Asia/Dhaka');

$serviceConfigurations = require __DIR__ . '/../config/service_config.php'; // Contains $activationKeywords, $smsTemplates

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$uri = strtok($_SERVER['REQUEST_URI'], '?');
$input = $method === 'POST' ? json_decode(file_get_contents('php://input'), true) : $_GET;

// --- Input Validation
if (empty($input['calling_number']) && empty($input['phone_number'])) {
    respond(['error' => 'Invalid request body'], 400);
}

$msisdn = $input['phone_number'] ?? $input['calling_number'];
$shortCode = $input['called_number'] ?? '16303';
$dtmfDigit = $input['dtmf_digit'] ?? '1';

$requestId = uniqid('ivr_req_', true);

if ("$method" === 'POST') {

    $request = [
        'chargecode' => $serviceConfigurations['serviceKeyMap'][$dtmfDigit] ?? $serviceConfigurations['serviceKeyMap']['1'],
        'featureId' => 'ACTIVATION',
        'requestId' => $requestId,
        'consentNo' => '', //consentNo
        'msisdn' => $msisdn,
        'shortCode' => $shortCode,
    ];

// --- SDP or Consent Activation Logic
    $response = activateFeature($request);

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
} else {
    respond(['error' => 'Method not allowed', 'aa' => "$method $uri"], 405);
}

function respond($data, $code = 200)
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}
