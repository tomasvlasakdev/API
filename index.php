<?php

$config = include __DIR__ . '/config/config.php';

$db = $config['db']; 

require_once __DIR__ . '/src/download.php';
require_once __DIR__ . '/src/import_data.php';
require_once __DIR__ . '/src/logger.php';

$result = download_file($config);

if ($result['success']) {
    echo "Download completed successfully." . PHP_EOL;

    if (import_csv($db, $config['output_file'], $config['error_log'])) {
        echo "Import completed successfully." . PHP_EOL;
        header("Location: /weby/API/public/interface.php");
        exit;
    } else {
        echo "Import failed. Check log for details." . PHP_EOL;
        exit;
    }
} else {
    echo "Download failed. Check {$config['error_log']} for details." . PHP_EOL;
    exit;
}
