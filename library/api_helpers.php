<?php

function build_base_api_url(string $endpoint = 'itemList.php'): string {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script_name = $_SERVER['SCRIPT_NAME'] ?? ('/' . $endpoint);
    $dir = dirname($script_name);
    return $protocol . '://' . $host . rtrim($dir, '/') . '/' . ltrim($endpoint, '/');
}

function send_json_response($payload, int $status = 200): void {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($status);
    }
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

?>
