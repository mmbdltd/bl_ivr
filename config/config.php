<?php

/**
 * Application Configuration (PSR-12 Compliant)
 * Usage: $config = require __DIR__ . '/../config/config.php';
 */

// Banglalink SDP API Base URL
$baseUrl = "http://192.168.33.37/original/api/v2/charging/banglalink";
// $baseUrl = "http://192.168.102.38/original/api/v2/charging/banglalink";
// $baseUrl = 'https://blk-web.apps-2.prod.banglalinkgsm.com/api';

return [
    'db' => [
        'host' => '192.168.33.13',
        'dbname' => 'push_pull_subscription',
        'user' => 'alamin',
        'pass' => 'Ak!JK$@6',
        'charset' => 'utf8mb4',
    ],
    'content_db' => [
        'host' => '192.168.33.122',
        'dbname' => 'mvas_push_pull',
        'user' => 'alamin',
        'pass' => 'Ak!JK$@6',
        'charset' => 'utf8mb4',
    ],
    'redis' => [
        'host' => '127.0.0.1',
        'port' => 6379,
    ],
    'sdp' => [
        'username' => 'momagic',
        'password' => 'Mom@gic97$',
        'cpid' => '131',
        'sourceNode' => 'momagic',
        'sourceAddress' => '192.168.102.36',
        'channel' => '4',
        'token_url' => $baseUrl . '/auth/login',
        'refresh_url' => $baseUrl . '/auth/RefreshToken',
        'sms_url' => $baseUrl . '/public/SMSMS/SENDSMS',
        'consent_url' => $baseUrl . '/public/SMCONSENT/Consent',
        'activation_url' => $baseUrl . '/public/SMACTIVATION/Activation',
        'unsub_url' => $baseUrl . '/public/SMDEACTIVATION/DEACTIVATION',
    ],
    'robisdp' => [
        'spId' => '200019',
        'spPassword' => 'Robi1234',
        'sms_url' => 'http://192.168.53.76:8310/SendSmsService/services/SendSms',
        'senderName' => '16303',
        'receiptEndpoint' => 'http://192.168.230.27/robi_push_pull/smsDLR.php',
    ],
    'sms_mode' => 'kannel',   // Default SMS sending mode: 'kannel' or 'sdp' or 'robisdp'
    'kannel' => [
        'username' => 'bluser',
        'password' => 'banglalink54',
        'sms_url' => 'http://192.168.33.37:13131/cgi-bin/sendsms',
        'coding' => 2,
        'charset' => 'UTF-8',
        'from' => '16303',
    ],
    'webhook_log_dir' => __DIR__ . '/../logs', // Directory for logging webhooks
];
