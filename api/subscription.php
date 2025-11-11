<?php
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

require_once __DIR__ . '/../core/sdp_feature.php'; // Contains activateFeature()
require_once __DIR__ . '/../core/redis.php';
require_once __DIR__ . '/../utils/helpers.php';
date_default_timezone_set('Asia/Dhaka');

$serviceConfigurations = require __DIR__ . '/../config/service_config.php'; // Contains $activationKeywords, $smsTemplates

header('Content-Type: application/json');

// --- Input Validation
$msisdn = isset($_GET['msisdn']) ? trim($_GET['msisdn']) : null;
$mo = isset($_GET['mo']) ? trim($_GET['mo']) : null;
$shortCode = isset($_GET['sc']) ? trim($_GET['sc']) : '16303';

if (empty($msisdn) || empty($mo)) {
    echo json_encode(['error' => 'Missing msisdn or mo parameter']);
    exit;
}

$keyword = strtolower(preg_replace('/\s+/', ' ', $mo));
$matchedKey = null;

// --- Keyword Match (Handles any leading space/case)
$activationKeywords = $serviceConfigurations['activationKeywords'];
foreach ($activationKeywords as $key => $conf) {
    if (str_starts_with($keyword, strtolower($key))) {
        $matchedKey = $key;
        break;
    }
}

if (!$matchedKey) {
    echo json_encode(['error' => 'No matching activation keyword']);
    exit;
}

$featureId = empty($_GET['consentNo']) ? 'ACTIVATION' : 'CONSENT';
$serviceConfig = $activationKeywords[$matchedKey];
$serviceName = $serviceConfig['serviceName'];

// --- Prepare request
$request = [
    'chargecode' => $serviceConfig['offerId'],
    'featureId' => $featureId,
    'requestId' => uniqid('req_', true),
    'consentNo' => $_GET['consentNo'] ?? '',
    'msisdn' => $msisdn,
    'shortCode' => $shortCode,
];
// --- SDP or Consent Activation Logic
$response = activateFeature($request);

echo json_encode($response, JSON_UNESCAPED_UNICODE);
