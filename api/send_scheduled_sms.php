<?php
// ini_set('display_errors', 1);
// error_reporting(E_ALL);
date_default_timezone_set('Asia/Dhaka');
echo "[" . date('Y-m-d H:i:s') . "] Processing scheduled SMS..." . PHP_EOL;

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/redis.php';
require_once __DIR__ . '/../utils/helpers.php';
require_once __DIR__ . '/../utils/SmsSender.php';

class ScheduledSmsProcessor
{
    private PDO $pdo;
    private Redis $redis;
    private array $config;

    public function __construct(PDO $pdo, Redis $redis, array $config)
    {
        $this->pdo = $pdo;
        $this->redis = $redis;
        $this->config = $config;
    }

    public function process(): void
    {
        $now = new DateTime();
        $fromTime = clone $now;
        $fromTime->modify('-120 minutes');
        echo '--------------------------- Start -----------------' . PHP_EOL;
        echo "fromTime " . $fromTime->format('H:i:s') . " to " . $now->format('H:i:s') . PHP_EOL;

        // Full date-time format (including date and time)
        $fromTimeFormatted = $fromTime->format('Y-m-d H:i:s');
        $nowFormatted = $now->format('Y-m-d H:i:s');

        try {
            $sql = "SELECT *
                    FROM scheduled_sms
                    WHERE status = 'pending'
                      AND scheduled_time BETWEEN '$fromTimeFormatted' AND '$nowFormatted'";
            echo $sql . PHP_EOL;
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $smsSchedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo "Error fetching scheduled SMS: " . $e->getMessage();
            return; // Stop further execution
        }

        // print_r('$smsSchedules:' . json_encode($smsSchedules)) . PHP_EOL;
        // Fetch all active users once
        $userStmt = $this->pdo->prepare(
            "SELECT msisdn, subscription_offer_id, service_name FROM subscribers WHERE action_type = 'ACTIVE'"
        );
        $userStmt->execute();
        $allUsers = $userStmt->fetchAll(PDO::FETCH_ASSOC);

        // Create an associative array where key is the service_name
        $usersByService = [];
        foreach ($allUsers as $user) {
            $usersByService[$user['service_name']][] = $user;
        }

        // print_r('usersByService:' . json_encode($usersByService)) . PHP_EOL;

        foreach ($smsSchedules as $schedule) {
            $serviceName = $schedule['service_name'];
            $message = $schedule['message'];

            if (!isset($usersByService[$serviceName])) {
                echo "No active users for service: $serviceName" . PHP_EOL;
                continue;
            }

            $users = $usersByService[$serviceName];
            $totalUsers = count($users);
            echo "Processing service: $serviceName" . PHP_EOL;
            echo "Total users to send SMS: $totalUsers" . PHP_EOL;
            echo "The processing SMS: $message" . PHP_EOL;

            $smsSender = new SmsSender($this->config, $this->pdo, $this->redis);
            foreach ($users as $user) {
                $msisdn = $user['msisdn'];
                $offerCode = $user['subscription_offer_id'];

                // Use the actual message from scheduled_sms
                $smsSender->send($msisdn, $message, '16303', $offerCode);
            }

            $update = $this->pdo->prepare("UPDATE scheduled_sms SET total_user_count = ?, status = 'sent', updated_at = NOW() WHERE id = ?");
            $update->execute([$totalUsers, $schedule['id']]);
        }
        echo '--------------------------- End -----------------' . PHP_EOL;
    }
}

$config = require __DIR__ . '/../config/config.php';
$processor = new ScheduledSmsProcessor($pdo, $redis, $config);
$processor->process();
