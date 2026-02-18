<?php

date_default_timezone_set('Europe/Prague');

function log_message($filePath, $level, $message) {
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
    $callerFile = $trace[1]['file'] ?? 'unknown';

    $log_data = [
        'timestamp'   => date('Y-m-d H:i:s'),
        'level'       => $level,
        'message'     => $message,
        'source_file' => basename($callerFile),
        'request_id'  => uniqid('', true),
        'pid'         => getmypid()
    ];

    $logs = [];
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        if (!empty(trim($content))) {
            $logs = json_decode($content, true) ?? [];
            if (!is_array($logs)) {
                $logs = [];
            }
        }
    }

    $logs[] = $log_data;

    file_put_contents($filePath, json_encode($logs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
}

function log_info($filePath, $message) {
    log_message($filePath, 'INFO', $message);
}

function log_import($filePath, $message) {
    log_message($filePath, 'IMPORT', $message);
}

function log_download($filePath, $message) {
    log_message($filePath, 'DOWNLOAD', $message);
}

function log_error($filePath, $message) {
    log_message($filePath, 'ERROR', $message);
}
