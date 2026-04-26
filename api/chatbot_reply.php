<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/chatbot_engine.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$token = $_POST['csrf_token'] ?? null;
if (!validateCsrfToken($token)) {
    http_response_code(419);
    echo json_encode(['error' => 'Invalid token']);
    exit();
}

$message = trim($_POST['message'] ?? '');
if ($message === '' || strlen($message) > 400) {
    http_response_code(422);
    echo json_encode(['error' => 'Message must be 1 to 400 characters']);
    exit();
}

$response = buildChatbotReply($message);

$pdo = getPDO();
$insert = $pdo->prepare(
    'INSERT INTO chat_logs (user_id, user_message, bot_response, category) VALUES (?, ?, ?, ?)'
);
$insert->execute([
    (int) ($_SESSION['user_id'] ?? 0),
    $message,
    $response['reply'],
    $response['category'],
]);

echo json_encode([
    'reply' => $response['reply'],
    'category' => $response['category'],
]);
