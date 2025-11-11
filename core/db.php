<?php

$config = require __DIR__ . '/../config/config.php';

function getPdo($which = 'db'): PDO
{
    global $config;
    $db = $config[$which];
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        $db['host'], $db['dbname'], $db['charset']
    );
    return new PDO(
        $dsn,
        $db['user'],
        $db['pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
}

$pdo = getPdo('db'); // Main
$contentPdo = getPdo('content_db'); // Content
