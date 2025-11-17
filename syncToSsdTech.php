<?php
function processSsdTech($responseData ): void
{
    date_default_timezone_set("Asia/Dhaka");
    $dateTime = date("Y-m-d H:i:s");
    $log_filename = "syncLogs/syncReceiver_" . date("Y_m_d") . ".txt";

    $logDir = dirname($log_filename);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }

    $requestData = array();
    foreach ($responseData['requestParam']['data'] as $item) {
        $name = $item['name'];
        $value = $item['value'];
        $requestData[$name] = $value;
    }
    $requestData['subscriptionOfferID'] = $responseData['requestParam']['subscriptionOfferID'];
    $requestData['command'] = $responseData['requestParam']['command'];
    $requestData['msisdn'] = $responseData['msisdn'];
    $requestData['featureId'] = $responseData['featureId'];

    // API endpoint URL
    $apiUrl = 'http://103.239.252.108/Robi_SDMWrapper/callback.php';

    $subscriptionstatus = 'Registered';
    $subscriptiongroupid = '16303_momagic_ivr_daily_auto';

    $requestedFor = 'renewal';
    $ChargeAmount = $requestData['ChargeAmount'] ? $requestData['ChargeAmount'] : '0.00';

    if ($requestData['command'] == 'NotifyDeActivation') {
        $requestedFor = 'deregistration';
    } else if ($requestData['command'] == 'NotifyActivation') {
        $requestedFor = 'registration';
    }

    switch ($requestData['SubscriptionStatus']) {    // <-- previously it was $responseData['SubscriptionStatus'] | Modified by Joy on 10-Dec-2024
        case 'A':
            $subscriptionstatus = 'Registered';
            if ($requestData['action'] == 'REN') {
                $requestedFor = 'renewal';
            } else {
                $requestedFor = 'registration';
            }
            break;
        case 'G':
            $subscriptionstatus = 'InGracePeriod';
            break;
        case 'D':
            $subscriptionstatus = 'Deregistered';
            $requestedFor = 'deregistration';        // <-- this line was added by Joy on 10-Dec-2024
            break;
        case 'S':
            $subscriptionstatus = 'Deregistered';
            $requestedFor = 'deregistration';        // <-- this line was added by Joy on 10-Dec-2024
            break;
        case 'P':
            $subscriptionstatus = 'InGracePeriod';
            break;
        default:
            $subscriptionstatus = 'Registered';
            $requestedFor = 'registration';         // <-- this line was added by Joy on 10-Dec-2024
            break;
    }

    switch ($requestData['subscriptionOfferID']) {
        case '9913110012':
            $subscriptiongroupid = '16303_momagic_ivr_daily_auto';
            break;
        case '9913110013':
            $subscriptiongroupid = '16303_momagic_ivr_Weekly_auto';
            break;
        case '9913110014':
            $subscriptiongroupid = '16303_momagic_ivr_monthly_auto';
            break;
        default:
            $subscriptiongroupid = '16303_momagic_ivr_daily_auto';
            break;
    }

    // Request data
    $requestData = array(
        'app_user' => 'momagic',
        'app_pass' => 'mom@g!c',
        'msisdn' => $requestData['msisdn'],
        'subscriptionstatus' => $subscriptionstatus,
        'channelId' => 3, //$requestData['channel']
        'transactionId' => $requestData['TransactionId'],
        'subscriptiongroupid' => $subscriptiongroupid,
        'subscriptionroot' => '16303_momagic_ivr',
        'requestedFor' => $requestedFor,
        'shortCode' => 16303,
        'autoRenewal' => 'YES',
        'userName' => 'Momag1c',
        'chargeAmount' => $ChargeAmount,
        'reason' => $requestData['Reason']
    );

    $dataJson = json_encode($requestData);
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $dataJson);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
    }
    curl_close($ch);

    $fp = fopen($log_filename, 'a+');
    fwrite($fp, PHP_EOL . "|SSD-Tech Request|" . $dataJson . PHP_EOL . "|SSD-Tech Response|" . $response . PHP_EOL);
    fclose($fp);

    if ($response === false) {
        echo 'Failed to make request.';
    } else {
        $responseData = json_decode($response, true);
        if ($responseData['success'] === true) {
            // Request successful
            // echo 'Request successful.';
        } else {
            // Request failed
            // echo 'Request failed: ' . $responseData['code'];
        }
        print_r($responseData);
    }
}
