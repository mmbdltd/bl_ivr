<?php

header("Content-Type: application/json; charset=UTF-8");

$method = $_SERVER['REQUEST_METHOD'];
$uri = strtok($_SERVER['REQUEST_URI'], '?');
$input = $method === 'POST' ? json_decode(file_get_contents('php://input'), true) : $_GET;

$serviceConfig = require __DIR__ . '/config/service_config.php';

require_once __DIR__ . '/core/sdp_feature.php';
require_once __DIR__ . '/utils/helpers.php';
/**
 * UTIL: Respond and exit
 */
function respond($data, $code = 200)
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

switch ("$method $uri") {

    /*
    |--------------------------------------------------------------------------
    | POST /ivr/resolve-menu
    |--------------------------------------------------------------------------
    */
    case 'POST /ivr/resolve-menu':
        if (
            empty($input['calling_number']) ||
            empty($input['called_number']) ||
            empty($input['channel_id'])
        ) {
            respond(['error' => 'Invalid request body'], 400);
        }
        $msisdn = $input['phone_number'] ?? $input['calling_number'];
        $shortCode = $input['called_number'] ?? 'unknown';
        $channelId = $input['channel_id'] ?? '1';

        // $menuId = rand(1, 2); // Can be replaced with real decision logic later

        // TODO retrieve subscriber info and set chargecode for unsubscription
        $subscriberData = getActiveSubscriberByMsisdn($msisdn, '1');

        if (count($subscriberData) == 0) {
            $menuId = 1;
        } else {
            $menuId = 2;
        }

        respond(['menu_id' => $menuId]);
        break;


    /*
    |--------------------------------------------------------------------------
    | POST /subscribe
    |--------------------------------------------------------------------------
    */
    case 'POST /subscribe':
        if (
            empty($input['calling_number']) &&
            empty($input['phone_number'])
        ) {
            respond(['error' => 'Invalid request body'], 400);
        }

        $msisdn = $input['phone_number'] ?? $input['calling_number'];
        $shortCode = $input['called_number'] ?? 'unknown';
        $dtmfDigit = $input['dtmf_digit'] ?? '1';

        $requestId = uniqid('ivr_req_', true);
        $request = [
            'chargecode' => $serviceConfig['serviceKeyMap'][$dtmfDigit] ?? $serviceConfig['serviceKeyMap']['1'],
            'featureId' => 'ACTIVATION',
            'requestId' => $requestId,
            'consentNo' => '', //consentNo
            'msisdn' => $msisdn,
            'shortCode' => $shortCode,
        ];

        $response = activateFeature($request);
        respond([
            'success' => $response['success'] ?? true,
            'message' => $response['message'] ?? 'Successfully subscribed',
            'request_id' => $requestId
        ]);
        break;


    /*
    |--------------------------------------------------------------------------
    | GET /subscribe (Get subscription logs/status)
    |--------------------------------------------------------------------------
    */
    case 'GET /subscribe':
        if (empty($input['msisdn'])) {
            respond(['error' => 'msisdn required'], 400);
        }

        // $deactivationConfig = getDeactivationConfig();

        $request = [
            'chargecode' => $serviceConfig['serviceKeyMap']['offerId'],
            'featureId' => 'DEACTIVATION',
            'requestId' => uniqid('check_', true),
            'msisdn' => $input['msisdn'],
        ];

        ///$response = checkSubscriptionStatus($request); // Dummy result
        respond([
            'logs' => [[
                'timestamp' => date('c'),
                'phone_number' => $input['msisdn']
            ]],
            'count' => 1
        ]);
        break;


    /*
    |--------------------------------------------------------------------------
    | POST /unsubscribe
    |--------------------------------------------------------------------------
    */
    case 'POST /unsubscribe':
        if (empty($input['calling_number']) && empty($input['phone_number'])) {
            respond(['error' => 'Invalid request body'], 400);
        }

        $msisdn = $input['phone_number'] ?? $input['calling_number'];

        // TODO retrieve subscriber info and set chargecode for unsubscription
        $subscriberData = getActiveSubscriberByMsisdn($msisdn, '1');

        $request = [
            'chargecode' => $subscriberData['offer_code'],
            'featureId' => 'DEACTIVATION',
            'requestId' => uniqid('ivr_unsub_', true),
            'msisdn' => $msisdn,
        ];

        $response = deactivateFeature($request);
        respond([
            'success' => $response['success'] ?? true,
            'message' => $response['message'] ?? 'Successfully unsubscribed',
            'request_id' => $request['requestId']
        ]);
        break;


    /*
    |--------------------------------------------------------------------------
    | GET /unsubscribe
    |--------------------------------------------------------------------------
    */
    case 'GET /unsubscribe':
        respond([
            'logs' => [[
                'timestamp' => date('c'),
                'phone_number' => $input['msisdn'] ?? null,
            ]],
            'count' => 1
        ]);
        break;


    /*
    |--------------------------------------------------------------------------
    | POST /log-dtmf
    |--------------------------------------------------------------------------
    */
    case 'POST /log-dtmf':
        if (
            empty($input['channel_id']) ||
            empty($input['dtmf_digit']) ||
            empty($input['calling_number'])
        ) {
            respond(['error' => 'Invalid request body'], 400);
        }

        respond([
            'success' => true,
            'message' => 'DTMF logged successfully',
            'log_id' => rand(1, 999)
        ]);
        break;


    /*
    |--------------------------------------------------------------------------
    | GET /log-dtmf
    |--------------------------------------------------------------------------
    */
    case 'GET /log-dtmf':
        respond([
            'logs' => [[
                'timestamp' => date('c'),
                'channel_id' => $input['channel_id'] ?? 'unknown',
                'dtmf_digit' => $input['dtmf_digit'] ?? 'unknown',
                'source' => $input['source'] ?? 'unknown',
            ]],
            'count' => 1
        ]);
        break;


    /*
    |--------------------------------------------------------------------------
    | GET /health
    |--------------------------------------------------------------------------
    */
    case 'GET /health':
        respond([
            'status' => 'ok',
            'service' => 'mock-external-api'
        ]);
        break;


    default:
        respond(['error' => 'Method not allowed'], 405);
}
