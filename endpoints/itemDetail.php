<?php
/**
 * Endpoint: itemDetail.php?id=123&scope=basic
 */

include_once __DIR__ . '/../library/sql_commands.php';
include_once __DIR__ . '/../library/logging.php';
include_once __DIR__ . '/../library/db_ping.php';
include_once __DIR__ . '/../library/api_common.php';

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

    $id = param_int('id', 0, 1, null);
    $scope = strtolower(param_str('scope', 'basic'));
    $allowed = array('basic','extended','complete');
    if (!in_array($scope, $allowed, true)) $scope = 'basic';

    if ($id < 1) {
        api_log_info('Invalid or missing id parameter for itemDetail');
        $response = array(
            'items' => array(),
            'meta' => array(
                'status' => 'error',
                'error'  => 'Parametr id je povinný a musí být kladné číslo.',
                'timestamp'   => date('c'),
                'api_version' => '1.0',
                'endpoint'    => basename(__FILE__),
                'scope'       => $scope,
            )
        );
        send_json($response, 400);
        exit;
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
        WHERE ID = :id
        LIMIT 1
    ';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        api_log_info('Item not found: id=' . $id);
        $response = array(
            'items' => array(),
            'meta' => array(
                'status' => 'error',
                'error'  => 'Položka s tímto ID nebyla nalezena.',
                'timestamp'   => date('c'),
                'api_version' => '1.0',
                'endpoint'    => basename(__FILE__),
                'requested_id' => $id,
                'scope' => $scope,
            )
        );
        send_json($response, 404);
        exit;
    }

    $baseItem = row_to_item($row);
    $label = build_label($baseItem);

    list($prev_id, $next_id) = get_prev_next($db, $id);

    // sestavíme item podle scope
    $item = array('id' => $baseItem['id'], 'label' => $label);

    if (in_array($scope, array('basic','extended','complete'), true)) {
        $item['code'] = $baseItem['code'];
        $item['area'] = $baseItem['area'];
        $item['year'] = $baseItem['year'];
    }

    if (in_array($scope, array('extended','complete'), true)) {
        $item['measure'] = $baseItem['measure'];
        $item['value'] = $baseItem['value'];
        $item['imported_at'] = $baseItem['imported_at'];
    }

    if ($scope === 'complete') {
        // pro complete vypíšu původní data z db
        $item['raw'] = $row;
    }

    $response = array(
        'items' => array($item),
        'meta' => array(
            'status'       => 'success',
            'total'        => 1,
            'per_page'     => 1,
            'current_id'   => $id,
            'previous_id'  => $prev_id,
            'next_id'      => $next_id,
            'scope'        => $scope,
            'timestamp'    => date('c'),
            'api_version'  => '1.0',
            'endpoint'     => basename(__FILE__),
        )
    );

    api_log_info('Served item id=' . $id . ' (scope=' . $scope . ')');
    send_json($response, 200);
    exit;

} catch (Exception $e) {
    api_log_error('Exception in itemDetail.php: ' . $e->getMessage());
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