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
$msisdn = isset($_GET['msisdn']) ? trim($_GET['msisdn']) : null;
$mo = isset($_GET['mo']) ? trim($_GET['mo']) : null;

if (empty($msisdn) || empty($mo)) {
    echo json_encode(['error' => 'Missing msisdn or mo parameter']);
    exit;
}

// --- Keyword match (case/space insensitive)
$keyword = strtolower(preg_replace('/\s+/', ' ', $mo));
$matchedKey = null;

$deactivationKeywords = $serviceConfigurations['deactivationKeywords'];
foreach ($deactivationKeywords as $key => $config) {
    if (str_starts_with($keyword, strtolower($key))) {
        $matchedKey = $key;
        break;
    }
}

if (!$matchedKey) {
    echo json_encode(['error' => 'No matching deactivation keyword']);
    exit;
}

$deactivationConfig = $deactivationKeywords[$matchedKey];

// --- Prepare and send API request
$request = [
    'chargecode' => $deactivationConfig['offerId'],
    'featureId' => 'DEACTIVATION',
    'requestId' => uniqid('unsub_', true),
    'msisdn' => $msisdn,
];

$response = deactivateFeature($request);

echo json_encode($response, JSON_UNESCAPED_UNICODE);
