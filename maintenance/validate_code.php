<?php
declare(strict_types=1);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

$inputCode = $_POST['code'] ?? '';
$trimmed = trim((string) $inputCode);

$correctCode = '4392';
$flag = 'THM{escape_complete}';

if (hash_equals($correctCode, $trimmed)) {
    echo json_encode([
        'success' => true,
        'flag' => $flag
    ]);
    exit;
}

echo json_encode([
    'success' => false,
    'message' => 'Incorrect code. Try again.'
]);
