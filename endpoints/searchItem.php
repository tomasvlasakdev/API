<?php
/*
 * Endpoint: https://vlasato23.sps-prosek.cz/weby/API/endpoints/searchItem.php?code=E02&area=London&year=1995&page=1&per_page=10 
 * 
 * At least one search parameter required: code, area, year, measure, or value 
 * 
 * Documentation: https://api-housing-data.gitbook.io/api-housing-data-docs/documentation/basics/endpoint-vyhledavani-polozek
 * 
 */

include_once __DIR__ . '/../library/sql_commands.php';
include_once __DIR__ . '/../library/logging.php';
include_once __DIR__ . '/../library/db_ping.php';
include_once __DIR__ . '/../library/api_common.php';

$config = require __DIR__ . '/../config/config.php';


// functions

function get_str(string $key): ?string
{
    if (!isset($_GET[$key])) return null;

    $v = trim((string) $_GET[$key]);
    return $v === '' ? null : $v;
}

function get_int(string $key): ?int
{
    if (!isset($_GET[$key])) return null;

    $v = trim((string) $_GET[$key]);
    if ($v === '' || !ctype_digit(ltrim($v, '-'))) return null;

    return (int) $v;
}

function validate_params(array &$out): array
{
    $errors = [];

    // code
    $code = get_str('code');
    if ($code !== null) {
        if (mb_strlen($code, 'UTF-8') > 20) {
            $errors[] = 'code too long';
        } elseif (!preg_match('/^[\pL\pN\-_.]+$/u', $code)) {
            $errors[] = 'code has invalid chars';
        }
    }
    $out['code'] = $code ?? '';

    // area
    $area = get_str('area');
    if ($area !== null) {
        if (mb_strlen($area, 'UTF-8') > 100) {
            $errors[] = 'area too long';
        } elseif (!preg_match('/^[\pL\pN\s\-\'.,()\x{2019}]+$/u', $area)) {
            $errors[] = 'area has invalid chars';
        }
    }
    $out['area'] = $area ?? '';

    // year (YYYY or YYYY-MM-DD)
    $year_raw = get_str('year');
    $year = null;

    if ($year_raw !== null) {
        if (preg_match('/^\d{4}$/', $year_raw)) {
            $year = $year_raw;
        } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $year_raw)) {
            $year = $year_raw;
        } else {
            $errors[] = 'year must be YYYY or YYYY-MM-DD';
        }
    }

    $out['year'] = $year;

    // measure
    $measure = get_str('measure');
    if ($measure !== null) {
        if (mb_strlen($measure, 'UTF-8') > 100) {
            $errors[] = 'measure too long';
        } elseif (!preg_match('/^[\pL\pN\s\-_.,%()\/:]+$/u', $measure)) {
            $errors[] = 'measure has invalid chars';
        }
    }
    $out['measure'] = $measure ?? '';

    // value
    $value = get_str('value');
    if ($value !== null) {
        if (mb_strlen($value, 'UTF-8') > 100) {
            $errors[] = 'value too long';
        } elseif (!preg_match('/^[\pL\pN\s\-_.,+%()\/:]+$/u', $value)) {
            $errors[] = 'value has invalid chars';
        }
    }
    $out['value'] = $value ?? '';

    // page
    $page_raw = get_str('page');
    $page = 1;

    if ($page_raw !== null) {
        if (!ctype_digit($page_raw) || (int)$page_raw < 1) {
            $errors[] = 'page must be positive int';
        } else {
            $page = (int)$page_raw;
        }
    }
    $out['page'] = $page;

    // per_page
    $per_page_raw = get_str('per_page');
    $per_page = 10;

    if ($per_page_raw !== null) {
        if (!ctype_digit($per_page_raw) || (int)$per_page_raw < 1) {
            $errors[] = 'per_page must be positive int';
        } else {
            $per_page = min((int)$per_page_raw, 50);
        }
    }
    $out['per_page'] = $per_page;

    return $errors;
}

function bind_params(PDOStatement $stmt, array $params): void
{
    foreach ($params as $name => $value) {
        if (in_array($name, [':limit_value', ':offset_value'], true)) {
            $stmt->bindValue($name, (int)$value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($name, (string)$value, PDO::PARAM_STR);
        }
    }
}

function build_search_where(
    string $code,
    string $area,
    ?string $year,
    string $measure,
    string $value,
    array &$params
): string {
    $where = [];

    if ($code !== '') {
        $where[] = 'LOWER(Code) LIKE :code';
        $params[':code'] = '%' . mb_strtolower($code, 'UTF-8') . '%';
    }

    if ($area !== '') {
        $where[] = 'LOWER(Area) LIKE :area';
        $params[':area'] = '%' . mb_strtolower($area, 'UTF-8') . '%';
    }

    if ($year !== null) {
        // if only year -> match LIKE '2012%'
        if (preg_match('/^\d{4}$/', $year)) {
            $where[] = 'CAST(Year AS CHAR) LIKE :year';
            $params[':year'] = $year . '%';
        } else {
            $where[] = 'CAST(Year AS CHAR) = :year';
            $params[':year'] = $year;
        }
    }

    if ($measure !== '') {
        $where[] = 'LOWER(Measure) LIKE :measure';
        $params[':measure'] = '%' . mb_strtolower($measure, 'UTF-8') . '%';
    }

    if ($value !== '') {
        $where[] = 'LOWER(Value) LIKE :value';
        $params[':value'] = '%' . mb_strtolower($value, 'UTF-8') . '%';
    }

    return implode(' AND ', $where);
}

function row_to_item(array $row): array
{
    return [
        'id' => (int)$row['id'],
        'code' => $row['code'],
        'area' => $row['area'],
        'year' => $row['year'],
        'measure' => $row['measure'],
        'value' => $row['value'],
        'imported_at' => $row['imported_at'],
    ];
}

function build_label(array $item): string
{
    return trim($item['code'] . ' - ' . $item['area'] . ' (' . $item['year'] . ')');
}

// -------------------------------------------------------------------------------------
// end of functions


// main

$db_ok = false;
$db_error = null;

db_ping($config, $db_ok, $db_error);

if (!$db_ok) {
    api_log_error('DB down: ' . ($db_error ?: 'unknown'));

    send_json([
        'items' => [],
        'meta' => [
            'status' => 'error',
            'error' => 'database not available',
            'details' => $db_error ?: 'unknown error',
        ],
    ], 503);

    exit;
}

$params = [];
$errors = validate_params($params);

if (!empty($errors)) {
    send_json([
        'items' => [],
        'meta' => [
            'status' => 'error',
            'error' => 'bad request',
            'details' => $errors,
        ],
    ], 400);

    exit;
}

$code = $params['code'];
$area = $params['area'];
$year = $params['year'];
$measure = $params['measure'];
$value = $params['value'];
$page = $params['page'];
$per_page = $params['per_page'];

if ($code === '' && $area === '' && $year === null && $measure === '' && $value === '') {
    send_json([
        'items' => [],
        'meta' => [
            'status' => 'error',
            'error' => 'at least one filter required',
        ],
    ], 400);

    exit;
}

try {
    $db = $config['db'];

    $count_params = [];
    $where_sql = build_search_where($code, $area, $year, $measure, $value, $count_params);

    $stmt = $db->prepare("
        SELECT COUNT(*) FROM DATA_API_HOUSING WHERE $where_sql
    ");
    bind_params($stmt, $count_params);
    $stmt->execute();

    $total = (int)$stmt->fetchColumn();

    $offset = ($page - 1) * $per_page;

    $data_params = $count_params;
    $data_params[':limit_value'] = $per_page;
    $data_params[':offset_value'] = $offset;

    $stmt = $db->prepare("
        SELECT ID AS id, Code AS code, Area AS area, Year AS year,
               Measure AS measure, Value AS value, imported_at
        FROM DATA_API_HOUSING
        WHERE $where_sql
        ORDER BY Year DESC, ID ASC
        LIMIT :limit_value OFFSET :offset_value
    ");

    bind_params($stmt, $data_params);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $items = [];
    foreach ($rows as $row) {
        $item = row_to_item($row);
        $item['label'] = build_label($item);
        $items[] = $item;
    }

    send_json([
        'items' => $items,
        'meta' => [
            'status' => 'ok',
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'has_more' => ($page * $per_page) < $total,
        ],
    ]);

} catch (Exception $e) {
    api_log_error($e->getMessage());

    send_json([
        'items' => [],
        'meta' => [
            'status' => 'error',
            'error' => 'server error',
        ],
    ], 500);
}