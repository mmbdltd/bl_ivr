<?php
// declare(strict_types=1);
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

require_once __DIR__ . '/../core/sdp_feature.php';
require_once __DIR__ . '/../core/redis.php';
date_default_timezone_set('Asia/Dhaka');

$serviceConfigurations = require __DIR__ . '/../config/service_config.php'; // Contains $activationKeywords, $smsTemplates

header('Content-Type: application/json');

// --- Input Validation
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

$subscriberData = getActiveSubscriberByMsisdn($msisdn, '1');

print_r($subscriberData['offer_code']);
// --- Prepare and send API request
$request = [
    'chargecode' => $subscriberData['offer_code'],
    'featureId' => 'DEACTIVATION',
    'requestId' => uniqid('ivr_unsub_', true),
    'msisdn' => $msisdn,
];

$response = deactivateFeature($request);

echo json_encode($response, JSON_UNESCAPED_UNICODE);
