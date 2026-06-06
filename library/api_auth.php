<?php

function require_api_token() {
    global $db;

    $headers = null;
    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
    }
    
    $authHeader = $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    if (empty($authHeader) || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $token = $matches[1];

    $stmt = $db->prepare("SELECT id FROM api_tokens WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $validToken = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$validToken) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
}
