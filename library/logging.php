<?php
require_once __DIR__ . '/../src/logger.php';

function api_log_path(): string {
    return __DIR__ . '/../logs/logging.json';
}

function api_log_info(string $message) : void {
    log_info(api_log_path(), $message);
}

function api_log_error(string $message) : void {
    log_error(api_log_path(), $message);
}

function api_log_import(string $message) : void {
    log_import(api_log_path(), $message);
}

function api_log_download(string $message) : void {
    log_download(api_log_path(), $message);
}

?>
