<?php

if (!function_exists('param_int')) {
    /**
     * Vrátí integer GET parametr s fallbackem a ohraničením.
     * @param string $key
     * @param int $default
     * @param int|null $min
     * @param int|null $max
     * @return int
     */
    function param_int($key, $default = 0, $min = null, $max = null) {
        if (!isset($_GET[$key])) return $default;
        $v = $_GET[$key];
        if (!is_numeric($v)) return $default;
        $i = (int)$v;
        if ($min !== null && $i < $min) return $default;
        if ($max !== null && $i > $max) return $max;
        return $i;
    }
}

if (!function_exists('param_str')) {
    /**
     * Vrátí string GET parametr s fallbackem.
     */
    function param_str($key, $default = '') {
        if (!isset($_GET[$key])) return $default;
        return trim((string) $_GET[$key]);
    }
}

if (!function_exists('to_iso')) {
    /**
     * Normalizuje datum/čas na ISO 8601 (DATE_ATOM).
     * Pokud nelze parsovat, vrátí původní string.
     */
    function to_iso($s) {
        if ($s === null || $s === '') return null;
        $ts = strtotime($s);
        if ($ts === false) return (string)$s;
        return date('c', $ts);
    }
}

if (!function_exists('extract_year')) {
    /**
     * Z datového pole (např. "1995-12-31" nebo "1995") vrátí rok jako int, nebo null.
     */
    function extract_year($s) {
        if ($s === null || $s === '') return null;
        // pokud je to přímo číslo
        if (is_numeric($s) && strlen((string)$s) === 4) {
            return (int)$s;
        }
        $ts = strtotime($s);
        if ($ts === false) return null;
        return (int) date('Y', $ts);
    }
}

if (!function_exists('row_to_item')) {
    /**
     * Převod DB řádku na konsistentní item (bez scope).
     * Vrací asociativní pole s typy: id(int), code(string), area(string), year(int|null), measure(string), value(float|null), imported_at(ISO|null)
     */
    function row_to_item(array $row) {
        $id = isset($row['id']) ? (int)$row['id'] : (isset($row['ID']) ? (int)$row['ID'] : null);
        $code = isset($row['code']) ? (string)$row['code'] : (isset($row['Code']) ? (string)$row['Code'] : '');
        $area = isset($row['area']) ? (string)$row['area'] : (isset($row['Area']) ? (string)$row['Area'] : '');

        $yearSource = null;
        if (isset($row['year'])) $yearSource = $row['year'];
        elseif (isset($row['Year'])) $yearSource = $row['Year'];
        $year = extract_year($yearSource);

        $measure = isset($row['measure']) ? (string)$row['measure'] : (isset($row['Measure']) ? (string)$row['Measure'] : '');
        $value = null;
        if (isset($row['value']) && is_numeric($row['value'])) $value = (float)$row['value'];
        elseif (isset($row['Value']) && is_numeric($row['Value'])) $value = (float)$row['Value'];

        $imported = null;
        if (isset($row['imported_at'])) $imported = to_iso($row['imported_at']);
        elseif (isset($row['importedAt'])) $imported = to_iso($row['importedAt']);
        elseif (isset($row['imported_at'])) $imported = to_iso($row['imported_at']);

        return array(
            'id' => $id,
            'code' => $code,
            'area' => $area,
            'year' => $year,
            'measure' => $measure,
            'value' => $value,
            'imported_at' => $imported,
        );
    }
}

if (!function_exists('build_label')) {
    /**
     * Sestaví textový label z code, area a year.
     */
    function build_label(array $item) {
        $parts = array();
        if (!empty($item['code'])) $parts[] = $item['code'];
        if (!empty($item['area'])) $parts[] = $item['area'];
        if (!empty($parts)) {
            $label = implode(' - ', $parts);
            if (!empty($item['year']) || $item['year'] === 0) $label .= ' (' . $item['year'] . ')';
            return $label;
        }
        return 'item-' . ($item['id'] ?? '0');
    }
}

// AI used to generate prev/next IDs for itemDetail endpoint, based on current ID.
if (!function_exists('get_prev_next')) {
    /**
     * Vrátí předchozí a následující ID (prev, next) relativně k $id.
     * @param PDO $db
     * @param int $id
     * @return array [prev_id|null, next_id|null]
     */
    function get_prev_next($db, $id) {
        $prev = null; $next = null;

        $pstmt = $db->prepare('SELECT ID AS id FROM DATA_API_HOUSING WHERE ID < :id ORDER BY ID DESC LIMIT 1');
        $pstmt->bindValue(':id', $id, PDO::PARAM_INT);
        $pstmt->execute();
        $pr = $pstmt->fetch(PDO::FETCH_ASSOC);
        if ($pr && isset($pr['id'])) $prev = (int)$pr['id'];

        $nstmt = $db->prepare('SELECT ID AS id FROM DATA_API_HOUSING WHERE ID > :id ORDER BY ID ASC LIMIT 1');
        $nstmt->bindValue(':id', $id, PDO::PARAM_INT);
        $nstmt->execute();
        $nr = $nstmt->fetch(PDO::FETCH_ASSOC);
        if ($nr && isset($nr['id'])) $next = (int)$nr['id'];

        return array($prev, $next);
    }
}

function send_json_response($payload, int $status = 200): void {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($status);
    }
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

if (!function_exists('send_json')) {
    /**
     * Jednoduchý wrapper pro send_json_response (pokud existuje v api_helpers.php).
     */
    function send_json($payload, $status = 200) {
        if (function_exists('send_json_response')) {
            send_json_response($payload, $status);
        } else {
            header('Content-Type: application/json');
            http_response_code($status);
            echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        }
    }
}