<?php
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/redis.php';
require_once __DIR__ . '/../utils/helpers.php';
$config= require __DIR__ . '/../config/config.php';

$query = $pdo->query("SELECT msisdn, service_name FROM subscribers WHERE status = 'Subscribed'");
$rows = $query->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as $row) {
    $msisdn = $row['msisdn'];
    $service = $row['service_name'];
    $message = "Dear user, here’s your daily {$service} update. Stay connected!";
    // send_sms($msisdn, $message, '16303', $offerCode, $config, $pdo, $redis);
}
