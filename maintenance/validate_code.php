<?php
declare(strict_types=1);

header('Content-Type: application/json');

$configPath = __DIR__ . '/config.php';

$respond = static function (int $status, array $body): void {
    http_response_code($status);
    echo json_encode($body);
    exit;
};

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $respond(405, [
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}

if (!is_readable($configPath)) {
    $respond(500, [
        'success' => false,
        'message' => 'Challenge misconfigured'
    ]);
}

$config = require $configPath;
$correctCode = isset($config['door_code']) ? (string) $config['door_code'] : '';
$flag = isset($config['flag']) ? (string) $config['flag'] : '';

if (!preg_match('/^\d{4}$/', $correctCode) || $flag === '') {
    $respond(500, [
        'success' => false,
        'message' => 'Challenge misconfigured'
    ]);
}

$inputCode = $_POST['code'] ?? '';
$trimmed = trim((string) $inputCode);

if (hash_equals($correctCode, $trimmed)) {
    $respond(200, [
        'success' => true,
        'flag' => $flag
    ]);
}

$respond(200, [
    'success' => false,
    'message' => 'Incorrect code. Try again.'
]);
