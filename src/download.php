<?php
require_once __DIR__ . '/logger.php';

$logFile = __DIR__ . '/../logs/logging.json'; 

// Downloads data from URL and saves to a file
function download_file(array $config) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $config['data_url'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_USERAGENT      => 'PHP Downloader/1.0'
    ]);

    $responseBody = curl_exec($ch);
    $statusCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error        = curl_error($ch);
    curl_close($ch);

    if ($error) {
        log_error($config['error_log'], "cURL chyba: $error");
        return ['success' => false, 'error' => $error];
    }

    if ($statusCode !== 200) {
        log_error($config['error_log'], "HTTP chyba: $statusCode (URL: {$config['data_url']})");
        return ['success' => false, 'httpCode' => $statusCode];
    }

    if (empty($responseBody)) {
        log_error($config['error_log'], "Prázdná odpověď z URL: {$config['data_url']}");
        return ['success' => false, 'error' => 'empty response'];
    }

    // Saves to a file
    $tmpFile = $config['output_file'] . '.tmp';
    if (file_put_contents($tmpFile, $responseBody) === false) {
        log_error($config['error_log'], "Nepodařilo se zapsat do souboru: $tmpFile");
        return ['success' => false, 'error' => 'write failed'];
    }
    // Change name
    rename($tmpFile, $config['output_file']);

    $size = strlen($responseBody);
    log_download($config['error_log'], "Soubor uložen: {$config['output_file']} (velikost: $size B)");

    return [
        'success'   => true,
        'httpCode'  => $statusCode,
        'file'      => $config['output_file'],
        'sizeBytes' => $size
    ];
}
