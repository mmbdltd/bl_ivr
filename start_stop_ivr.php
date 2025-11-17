<?php
date_default_timezone_set('Asia/Dhaka');
$planId = '9913110002';

$subscriptionId = $_REQUEST['mo_id'];
$msisdn = substr($_REQUEST['msisdn'], -10);
$operatorId = (int)trim($_REQUEST['operator']);

$moSms = $_REQUEST['mo'];
list($keyword, $secondKeyword) = explode(' ', strtoupper($moSms));

$logData = [
    'datetime' => date("Y-m-d H:i:s"),
    'request' => $_REQUEST,
    'queries' => [],
    'responses' => []
];

$smsConfirmMessages = [
    // '9913110012' => 'আপনার সার্ভিস সফলভাবে বন্ধ হয়েছে। পুনরায় চালু করতে START BD লিখে পাঠান 16303 নাম্বারে।',
    // '9913110013' => 'আপনার সাপ্তাহিক সার্ভিস সফলভাবে বন্ধ হয়েছে। পুনরায় চালু করতে START BW লিখে পাঠান 16303 নাম্বারে।',
    // '9913110014' => 'আপনার মাসিক সার্ভিস সফলভাবে বন্ধ হয়েছে। পুনরায় চালু করতে START BM লিখে পাঠান 16303 নাম্বারে।',
    '9913110029' => 'Sports Update Daily সার্ভিসটি সফলভাবে বন্ধ হয়েছে। START SU লিখে 16303 নাম্বারে পাঠিয়ে পুনরায় চালু করুন। হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ৮টা পর্যন্ত)',
    '9913110030' => 'Love Tips Daily সার্ভিসটি সফলভাবে বন্ধ হয়েছে। START LT লিখে 16303 নাম্বারে পাঠিয়ে পুনরায় চালু করুন। হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ৮টা পর্যন্ত)',
    '9913110031' => 'Friendship Quotes Daily সার্ভিসটি সফলভাবে বন্ধ হয়েছে। START FQ লিখে 16303 নাম্বারে পাঠিয়ে পুনরায় চালু করুন। হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ৮টা পর্যন্ত)',
    '9913110032' => 'Celebrity Alerts Daily সার্ভিসটি সফলভাবে বন্ধ হয়েছে। START CA লিখে 16303 নাম্বারে পাঠিয়ে পুনরায় চালু করুন। হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ৮টা পর্যন্ত)',
    '9913110033' => 'Comics Alerts Daily সার্ভিসটি সফলভাবে বন্ধ হয়েছে। START CM লিখে 16303 নাম্বারে পাঠিয়ে পুনরায় চালু করুন। হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ৮টা পর্যন্ত)',
    '9913110034' => 'Horoscope Alerts Daily সার্ভিসটি সফলভাবে বন্ধ হয়েছে। START HA লিখে 16303 নাম্বারে পাঠিয়ে পুনরায় চালু করুন। হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ৮টা পর্যন্ত)',
];

// 🔹 New 6 services for STOP ALL
$newServices = [
    '9913110012' => 'ivr_daily',
    '9913110013' => 'ivr_weekly',
    '9913110014' => 'ivr_monthly',
    '9913110029' => 'sports_update_daily',
    '9913110030' => 'love_tips_daily',
    '9913110031' => 'friendship_quotes_daily',
    '9913110032' => 'celebrity_alerts_daily',
    '9913110033' => 'comics_alerts_daily',
    '9913110034' => 'horoscope_alerts_daily',
];

// 🔁 STOP ALL: Loop through all 6 new services
if ($keyword === "STOP" && $secondKeyword === 'ALL') {
    foreach ($newServices as $chargecode => $serviceName) {
        $requestId = mt_rand(1000000000000000, 9999999999999999); // Generate a random 16-digit number
        $queryParams = [
            'requestId' => $requestId,
            'subscriptionId' => $subscriptionId,
            'msisdn' => $msisdn,
            'amount' => 0,
            'planId' => $planId,
            'featureId' => "Deactivation",
            'chargecode' => $chargecode,
            'action' => "DACT"
        ];
        $queryString = http_build_query($queryParams);
        $url = "http://localhost/bl_cgw/bl_sdp_cgw.php?" . $queryString;
        $response = @file_get_contents($url);

        $parsedResponse = json_decode($response, true);
        $smsResponse = '';
        if (
            isset($parsedResponse['responseCode']) && $parsedResponse['responseCode'] == '0' &&
            isset($parsedResponse['resultParam']['resultDescription']) &&
            stripos($parsedResponse['resultParam']['resultDescription'], 'Deactivate Subscription Success') !== false
        ) {

            if (isset($smsConfirmMessages[$chargecode])) {
                $smsResponse = sendViaKannel($msisdn, $smsConfirmMessages[$chargecode], '16303');
            }
        }

        $logData['queries'][] = $queryParams;
        $logData['responses']['deactivation'][$chargecode] = ['url' => $url, 'response' => $response];
        $logData['responses']['sms'][$chargecode] = $smsResponse;

    }
}

function sendViaKannel(string $msisdn, string $message, string $from = '16303'): array
{
    $params = [
        'username' => 'bluser',
        'password' => 'banglalink54',
        'to' => $msisdn,
        'from' => $from,
        'text' => $message,
        'coding' => 2,
        'charset' => 'UTF-8',
    ];
    $kannelUrl = 'http://192.168.33.37:13131/cgi-bin/sendsms';
    $url = $kannelUrl . '?' . http_build_query($params);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    $success = $err === '' && str_contains($result, '0: Accepted');

    // $this->saveLog('kannel', $msisdn, $params, $result, $success, 1);

    return [
        'success' => $success,
        'gateway' => 'kannel',
        'http_code' => $success ? 202 : 500,
        'result' => $result,
        'payload' => $params,
    ];
}

echo "+OK";
date_default_timezone_set("Asia/Dhaka");
$dateTime = date("Y-m-d H:i:s");
$log_filename = "logs/START_STOP_" . date("Y_m_d") . ".txt";

$fp = fopen($log_filename, 'a+');
fwrite($fp, date("Y-m-d H:i:s") . "|" . json_encode($logData, JSON_UNESCAPED_UNICODE) . PHP_EOL);
fclose($fp);
