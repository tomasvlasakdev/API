<?php
/**
 * Endpoint: .../itemList.php?per_page=10&page=1
 */

include_once __DIR__ . '/../library/sql_commands.php';
include_once __DIR__ . '/../library/logging.php';
include_once __DIR__ . '/../library/db_ping.php';
include_once __DIR__ . '/../library/api_common.php'; // nový společný helper

$config = require __DIR__ . '/../config/config.php';
$db_ok = false;
$db_error = null;

db_ping($config, $db_ok, $db_error);

if (! $db_ok) {
    api_log_error('Database unavailable: ' . ($db_error ?: 'unknown'));
    send_json(array(
        'items' => array(),
        'meta' => array(
            'status' => 'error',
            'error'  => 'Databáze není dostupná',
            'details'=> $db_error ?: 'Neznámá chyba připojení k databázi',
            'timestamp'   => date('c'),
            'api_version' => '1.0',
            'endpoint'    => basename(__FILE__),
        )
    ), 503);
    exit;
}

try {
    $db = $config['db'];

    // používáme helper param_int
    $per_page = param_int('per_page', 10, 5, 100);
    $page = param_int('page', 1, 1, null);

    $offset = ($page - 1) * $per_page;

    $totalStmt = $db->query('SELECT COUNT(*) FROM DATA_API_HOUSING');
    $total = (int) $totalStmt->fetchColumn();

    $total_pages = ($total > 0) ? (int) ceil($total / $per_page) : 1;
    if ($page > $total_pages) {
        $page = $total_pages;
        $offset = ($page - 1) * $per_page;
    }

    $sql = '
        SELECT
            ID AS id,
            Code AS code,
            Area AS area,
            Year AS year,
            Measure AS measure,
            Value AS value,
            imported_at AS imported_at
        FROM DATA_API_HOUSING
        ORDER BY ID DESC
        LIMIT :limit OFFSET :offset
    ';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':limit',  $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset,   PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $items = array();
    foreach ($rows as $r) {
        $items[] = row_to_item($r);
    }

    if ($total === 0) {
        $from = 0; $to = 0;
    } else {
        $from = $offset + 1;
        $to = $offset + count($items);
        if ($to > $total) $to = $total;
    }

    $response = array(
        'items' => $items,
        'meta' => array(
            'status'       => 'success',
            'total'        => $total,
            'per_page'     => $per_page,
            'current_page' => $page,
            'total_pages'  => $total_pages,
            'range'        => array('from' => $from, 'to' => $to),
            'timestamp'    => date('c'),
            'api_version'  => '1.0',
            'endpoint'     => basename(__FILE__),
        )
    );

    api_log_info(sprintf('Served %d items (page %d, per_page %d, total %d)', count($items), $page, $per_page, $total));
    send_json($response, 200);

} catch (Exception $e) {
    api_log_error('Exception in itemList.php: ' . $e->getMessage());
    $response = array(
        'items' => array(),
        'meta' => array(
            'status'  => 'error',
            'error'   => 'Chyba serveru při zpracování požadavku',
            'message' => $e->getMessage(),
            'timestamp'   => date('c'),
            'api_version' => '1.0',
            'endpoint'    => basename(__FILE__),
        )
    );
    send_json($response, 500);
    exit;
}