<?php

// Local time
date_default_timezone_set('Europe/Prague');

function log_message($filePath, $level, $message) {
    $caller = debug_backtrace()[1];
    $log_data = [
        'timestamp' => date('Y-m-d H:i:s'),
        'level' => $level,
        'message' => $message,
        'source_file' => str_replace($_SERVER['DOCUMENT_ROOT'], '', $caller['file']),
        'request_id' => uniqid(),
        'pid' => getmypid()
    ];

    // Checks if file exists and is not empty
    if (file_exists($filePath) && filesize($filePath) > 0) {
        // Read current content and decodes json
        $current_content = file_get_contents($filePath);
        $logs = json_decode($current_content, true);

        if (!is_array($logs)) {
            $logs = [];
        }
    } else {
        $logs = [];
    }
    
    $logs[] = $log_data;

    $json_entry = json_encode($logs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
    file_put_contents($filePath, $json_entry, LOCK_EX);
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

