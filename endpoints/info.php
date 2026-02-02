<?php
include_once __DIR__ . '/../library/sql_commands.php';

/*
 * Dokumentace k endpointu /info.php:
 * https://app.gitbook.com/o/CJz4qlCVwDL2Hn3AuhmU/s/r2ekOEUU8ZTbgSwKjutn/
 */

header('Content-Type: application/json; charset=utf-8');

$config = require __DIR__ . '../../config/config.php';

$db_ok = false;
$db_error = null;

if (isset($config['db']) && $config['db'] instanceof PDO) {
    try {
        $config['db']->query('SELECT 1');
        $db_ok = true;
    } catch (Exception $e) {
        $db_error = $e->getMessage();
    }
}

$response = [
    'api' => [
        'name'        => 'Housing Data API Endpoint',
        'version'     => '1.0.0',
        'description' => 'API pro práci s daty o cenách nemovitostí (MSOA – Londýn)',
        'documentation' => 'https://app.gitbook.com/o/CJz4qlCVwDL2Hn3AuhmU/s/r2ekOEUU8ZTbgSwKjutn/',
        'status'      => $db_ok ? 'operational' : 'degraded'
    ],
    'server' => [
        'php_version'     => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
        'protocol'        => $_SERVER['SERVER_PROTOCOL'] ?? 'unknown',
        'request_method'  => $_SERVER['REQUEST_METHOD'],
        'request_time'    => date('c'),
        'memory_limit'    => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time') . ' s',
    ],
    'database' => [
        'connection' => $db_ok ? 'ok' : 'failed',
    ],
];

if (!$db_ok) {
    $response['database']['error'] = $db_error ?: 'Neznámá chyba připojení';
    $response['note'] = 'Statistiky dat nejsou dostupné – databáze není přístupná';
} else {
    try {
        $db = $config['db'];


$response['statistics'] = [
    'total_records'       => sql_commands2(1, $config['db']),
    'avg_median_price'    => sql_commands2(2, $config['db']),
    'avg_mean_price'      => sql_commands2(3, $config['db']),
    'total_sales_count'   => sql_commands2(4, $config['db']),
    'table_size_mb'       => sql_commands2(5, $config['db']),
    'latest_import'       => sql_commands2(6, $config['db']),
];

    } catch (Exception $e) {
        $response['statistics'] = [
            'error' => 'Nepodařilo se načíst statistiky: ' . $e->getMessage()
        ];
    }
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);