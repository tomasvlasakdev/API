<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

if (!defined('DB_NAME')) {
    define('DB_NAME', $_ENV['DB_NAME'] ?? 'vlasato23');
    define('DB_USER', $_ENV['DB_USER'] ?? 'vlasato23');
    define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? 'p6ctWXir');
    define('DB_HOST', 'db');

    define('LOG_FILE', __DIR__ . '/../logs/logging.json');
    define('OUTPUT_FILE', __DIR__ . '/../data/downloaded_housing_data.csv');
    define('ERROR_LOG', __DIR__ . '/../logs/logging.json');
}

global $db;
if (!isset($db)) {
    try {
        $db = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASSWORD,
            array(
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
            )
        );
    }   
    catch (Exception $e) {
        $response = [
            "DB_CONNECTION" => "Database connection failed"
        ];
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }
}

return [
    'data_url' => 'https://data.london.gov.uk/download/ad95cd2f-3ceb-4049-82f3-13b570bb1231/bdf8eee7-41e1-4d24-90ce-93fe5cf040ae/land-registry-house-prices-MSOA.csv',
    'output_file' => OUTPUT_FILE,
    'error_log' => ERROR_LOG,
    'db' => $db
];
