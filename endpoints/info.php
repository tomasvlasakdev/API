<?php
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

        // 1. Total records
        $stmt = $db->query("SELECT COUNT(*) AS cnt FROM DATA_API_HOUSING");
        $total_records = $stmt->fetchColumn() ?: 0;

        // 2. Median average
        $stmt = $db->query("SELECT AVG(value) AS avg FROM DATA_API_HOUSING WHERE measure = 'median'");
        $avg_median = round($stmt->fetchColumn() ?: 0);

        // 3. Mean average
        $stmt = $db->query("SELECT AVG(value) AS avg FROM DATA_API_HOUSING WHERE measure = 'mean'");
        $avg_mean = round($stmt->fetchColumn() ?: 0);

        // 4. Total sales
        $stmt = $db->query("SELECT COUNT(*) AS cnt FROM DATA_API_HOUSING WHERE measure = 'sales'");
        $total_sales = $stmt->fetchColumn() ?: 0;

        // 5. DB size (MB)
        $stmt = $db->query("
            SELECT ROUND((data_length + index_length) / 1024 / 1024, 2) AS size_mb
            FROM information_schema.tables 
            WHERE table_schema = DATABASE() 
            AND table_name = 'DATA_API_HOUSING'
        ");
        $table_size_mb = $stmt->fetchColumn() ?: 'n/a';

        // 6. Date of last import
        $stmt = $db->query("SELECT MAX(imported_at) FROM DATA_API_HOUSING");
        $latest = $stmt->fetchColumn();
        $latest_import = $latest 
            ? (new DateTime($latest))->format('j. n. Y H:i:s')
            : 'žádný záznam';

        $response['statistics'] = [
            'total_records'       => $total_records,
            'avg_median_price'    => $avg_median,
            'avg_mean_price'      => $avg_mean,
            'total_sales_count'   => $total_sales,
            'table_size_mb'       => $table_size_mb,
            'latest_import'       => $latest_import,
        ];
    } catch (Exception $e) {
        $response['statistics'] = [
            'error' => 'Nepodařilo se načíst statistiky: ' . $e->getMessage()
        ];
    }
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);