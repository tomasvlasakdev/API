<?php
/**
 * Endpoint: /API/endpoints/itemList.php
 * 
 * Vrátí stránkovaný seznam záznamů z tabulky DATA_API_HOUSING
 * 
 * Podporované query parametry:
 *   ?page=3           → číslo stránky (výchozí: 1)
 *   ?per_page=25      → počet záznamů na stránku (5–100, výchozí: 10)
 */

include_once __DIR__ . '/../library/sql_commands.php';

header('Content-Type: application/json; charset=utf-8');

$config = require __DIR__ . '/../config/config.php';

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

if (!$db_ok) {
    http_response_code(503);
    echo json_encode([
        'error'   => 'Databáze není dostupná',
        'details' => $db_error ?: 'Neznámá chyba připojení k databázi'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    $db = $config['db'];

    // ────────────────────────────────────────────────
    // Zpracování stránkovacích parametrů
    // ────────────────────────────────────────────────
    $per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
    $page     = isset($_GET['page'])     ? (int)$_GET['page']     : 1;

    // Omezení rozsahu
    $per_page = max(1, min(100, $per_page));  
    $page     = max(1, $page);

    // Výpočet offsetu
    $offset = ($page - 1) * $per_page;

    // ────────────────────────────────────────────────
    // Celkový počet záznamů
    // ────────────────────────────────────────────────
    $totalStmt = $db->query('SELECT COUNT(*) FROM DATA_API_HOUSING');
    $total = (int) $totalStmt->fetchColumn();

    // Počet stránek
    $total_pages = $total > 0 ? (int) ceil($total / $per_page) : 1;

    // Oprava příliš velkého čísla stránky
    if ($page > $total_pages) {
        $page = $total_pages;
        $offset = ($page - 1) * $per_page;
    }

    // ────────────────────────────────────────────────
    // Načtení dat
    // ────────────────────────────────────────────────
    $stmt = $db->prepare('
        SELECT 
            ID          AS id,
            Code        AS code,
            Area        AS area,
            Year        AS year,
            Measure     AS measure,
            Value       AS value,
            imported_at AS imported_at
        FROM DATA_API_HOUSING
        ORDER BY ID DESC
        LIMIT :limit OFFSET :offset
    ');

    $stmt->bindValue(':limit',  $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset,   PDO::PARAM_INT);
    $stmt->execute();

    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $from = $offset + 1;
    $to   = $offset + count($items);
    if ($to > $total) $to = $total;

    // ────────────────────────────────────────────────
    // Finální odpověď
    // ────────────────────────────────────────────────
    $response = [
        'status' => 'success',
        'data' => [
            'items'        => $items,
            'total'        => $total,
            'per_page'     => $per_page,
            'current_page' => $page,
            'total_pages'  => $total_pages,
            'range'        => [
                'from' => $from,
                'to'   => $to
            ]
        ],
        'meta' => [
            'timestamp'    => date('c'),
            'api_version'  => '1.0',
            'endpoint'     => basename(__FILE__)
        ]
    ];

} catch (Exception $e) {
    http_response_code(500);
    $response = [
        'status'  => 'error',
        'error'   => 'Chyba serveru při zpracování požadavku',
        'message' => $e->getMessage()
    ];
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);