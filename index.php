<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/download.php';
require_once __DIR__ . '/src/import_data.php';
require_once __DIR__ . '/src/logger.php';
require_once __DIR__ . '/config/db.php';

$config = include __DIR__ . '/config/config.php';
$logFile = $config['error_log'];

if (download_file($config)) {
    echo "Download completed successfully." . PHP_EOL;

    if (import_csv($db, $config['output_file'], $logFile)) {
        echo "Import completed successfully." . PHP_EOL;
        header("Location: /API/public/interface.php");
        exit;

    } else {
        echo "Import failed. Check log for details." . PHP_EOL;
        exit;

    }
} else {
    echo "Download failed. Check {$logFile} for details." . PHP_EOL;
    exit;

}