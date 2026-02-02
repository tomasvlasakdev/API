<?php
session_start();

if (!defined('DB_NAME')) {
    define('DB_NAME', 'vlasato23');
    define('DB_USER', 'vlasato23');
    define('DB_PASSWORD', 'HzQmSPbF');
    define('DB_HOST', '127.0.0.1');

    define('LOG_FILE', __DIR__ . '/../logs/logging.json');
    define('OUTPUT_FILE', __DIR__ . '/../data/downloaded_housing_data.csv');
    define('ERROR_LOG', __DIR__ . '/../logs/logging.json');

    try {
        $db = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASSWORD,
            array(
                Pdo\Mysql::ATTR_INIT_COMMAND => "SET NAMES utf8"

            )
        );
    }   
    catch (Exception $e) {
        $response = [
            "DB_CONNECTION" => "Database connection failed"
        ];
        echo json_encode($response, JSON_PRETTY_PRINT);

    }
}

return [
    'data_url' => 'https://data.london.gov.uk/download/ad95cd2f-3ceb-4049-82f3-13b570bb1231/bdf8eee7-41e1-4d24-90ce-93fe5cf040ae/land-registry-house-prices-MSOA.csv',
    'output_file' => OUTPUT_FILE,
    'error_log' => ERROR_LOG,
    'db' => $db
];
