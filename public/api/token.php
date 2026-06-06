<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$client_id = $_POST['client_id'] ?? $data['client_id'] ?? null;
$client_secret = $_POST['client_secret'] ?? $data['client_secret'] ?? null;

if (!$client_id || !$client_secret) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing client_id or client_secret']);
    exit;
}

global $db;
$stmt = $db->prepare("SELECT id, client_secret FROM api_keys WHERE client_id = ?");
$stmt->execute([$client_id]);
$key = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$key || !password_verify($client_secret, $key['client_secret'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid credentials']);
    exit;
}

// Clean up old tokens for this client (optional but good practice)
$db->prepare("DELETE FROM api_tokens WHERE client_id = ? AND expires_at < NOW()")->execute([$client_id]);

$token = bin2hex(random_bytes(32));
$expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

$stmt = $db->prepare("INSERT INTO api_tokens (client_id, token, expires_at) VALUES (?, ?, ?)");
$stmt->execute([$client_id, $token, $expires_at]);

echo json_encode([
    'access_token' => $token,
    'token_type' => 'Bearer',
    'expires_in' => 3600
]);
