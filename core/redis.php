<?php
if (!class_exists('Redis')) {
    die("Redis extension not installed or enabled.");
}

$config = require __DIR__ . '/../config/config.php';

$redis = new Redis();
$redis->connect($config['redis']['host'], $config['redis']['port']);
