<?php
require_once __DIR__ . '/redis.php';
$config = require __DIR__ . '/../config/config.php';

function getAccessToken()
{
    global $redis, $config;
    if ($redis->exists('bl_token')) {
        return $redis->get('bl_token');
    }

    $url = $config['sdp']['token_url'];
    $response = apiRequest('POST', $url, [
        'username' => $config['sdp']['username'],
        'password' => $config['sdp']['password']
    ]);

    if (isset($response['token'])) {
        $redis->set('bl_token', $response['token'], 1700);
        $redis->set('bl_refresh', $response['refreshToken']);
        return $response['token'];
    }

    return null;
}

function refreshToken()
{
    global $redis, $config;

    $refreshToken = $redis->get('bl_refresh');
    $headers = [
        'Consent-Type: application/json',
        'X-Requested-With: XMLHttpRequest',
        'X-Authorization: Bearer ' . $refreshToken
    ];

    $ch = curl_init($config['sdp']['refresh_url']);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers
    ]);

    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (isset($response['token'])) {
        $redis->set('bl_token', $response['token'], 1700);
        return $response['token'];
    }

    return null;
}

function apiRequest($method, $url, $body = [], $headers = ['Content-Type: application/json', 'X-Requested-With: XMLHttpRequest'])
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_SSL_VERIFYHOST => false,    //In Deployment, it will be commented out
        CURLOPT_SSL_VERIFYPEER => false,    //In Deployment, it will be commented out
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_POSTFIELDS => json_encode($body),
        CURLOPT_HTTPHEADER => $headers
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        return "cURL Error #(Grant Api):" . $err;

    }
    return json_decode($response, true);
}
