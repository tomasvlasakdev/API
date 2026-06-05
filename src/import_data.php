<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/logger.php';

$logFile = __DIR__ . '/../logs/logging.json'; 


function import_csv($db, $filePath, $logFile) {
    if (!file_exists($filePath)) {
        log_error($logFile, "Soubor $filePath neexistuje.");
        return false;
    }

    $handle = fopen($filePath, 'r');
    if ($handle === false) {
        log_error($logFile, "Nelze otevřít soubor $filePath.");
        return false;
    }

    $header = fgetcsv($handle, 0, ",", '"', "\\");

    try {
        $db->exec("TRUNCATE TABLE DATA_API_HOUSING");
        log_info($logFile, "Tabulka DATA_API_HOUSING byla vyprázdněna.");
    } catch (PDOException $e) {
        log_error($logFile, "Chyba při TRUNCATE: " . $e->getMessage());
        fclose($handle);
        return false;
    }


// Created with assistance of ChatGPT

    $davka = 5000;
    $rows = [];
    $rowCount = 0;
    $totalInserted = 0;

    while (($row = fgetcsv($handle, 0, ",", '"', "\\")) !== false) {
        // validate row
        if (count($row) < 5) {
            log_error($logFile, "Řádek s nedostatečným počtem sloupců: " . implode(",", $row));
            continue;
        }

        $yearStr = $row[2];
        $date = parse_year_string($yearStr);

        if ($date === null) {
            log_error($logFile, "Nelze parsovat datum z: '$yearStr' (řádek: " . ($totalInserted + $rowCount + 1) . ")");
            continue;
        }

        $rows[] = [
            $row[0],             
            $row[1],            
            $date,          
            $row[3],         
            (int)$row[4],   
        ];
        $rowCount++;

        if ($rowCount >= $davka) {
            // insert badge
            if (!insert_davku_000($db, $rows, $logFile)) {
                log_error($logFile, "Chyba při vkládání dávky. Import přerušen.");
                fclose($handle);
                return false;
            }
            $totalInserted += $rowCount;
            $rows = [];
            $rowCount = 0;
        }
    }

    if ($rowCount > 0) {
        if (!insert_davku_000($db, $rows, $logFile)) {
            log_error($logFile, "Chyba při vkládání poslední dávky.");
            fclose($handle);
            return false;
        }
        $totalInserted += $rowCount;
    }

    fclose($handle);
    log_import($logFile, "Import dokončen. Do tabulky DATA_API_HOUSING vloženo $totalInserted řádků.");
    return true;
}

function insert_davku_000($db, array $rows, $logFile) {
    if (empty($rows)) return true;

    try {
        $db->beginTransaction();
        insert_davku($db, $rows);
        $db->commit();
        return true;
    } catch (PDOException $e) {
        $db->rollBack();
        log_error($logFile, "PDO Exception během vkládání dávky: " . $e->getMessage());
        return false;
    } catch (Exception $e) {
        $db->rollBack();
        log_error($logFile, "Obecná chyba během vkládání dávky: " . $e->getMessage());
        return false;
    }
}

function insert_davku($db, array $rows) {
    if (empty($rows)) return;

    $placeholders = [];
    $values = [];
    foreach ($rows as $row) {
        $placeholders[] = "(?, ?, ?, ?, ?)";
        $values = array_merge($values, $row);
    }

    $sql = "INSERT INTO DATA_API_HOUSING (Code, Area, Year, Measure, Value) VALUES " . implode(",", $placeholders);
    $stmt = $db->prepare($sql);
    $stmt->execute($values);
}

// Created with assistance of ChatGPT
function parse_year_string($str) {
    $s = trim($str);
    if ($s === '') return null;

    // ex. Q1 2019 changes to last day of March 2019
    if (preg_match('/^Q([1-4])\s+(\d{4})$/i', $s, $m)) {
        $q = (int)$m[1];
        $year = (int)$m[2];
        $month = $q * 3;
        return date("Y-m-t", strtotime("$year-$month-01"));
    }

    // Month & Year
    if (preg_match('/([A-Za-zěščřžýáíéúůĚŠČŘŽÝÁÍÉÚŮ]+)\s+(\d{4})/u', $s, $m)) {
        $monthName = $m[1];
        $year = (int)$m[2];
        $ts = strtotime("$monthName $year");
        if ($ts !== false) {
            return date("Y-m-t", $ts);
        }
    }

    // 3) Only year: 2019 changes to last day of December
    if (preg_match('/^(\d{4})$/', $s, $m)) {
        $year = (int)$m[1];
        return date("Y-m-t", strtotime("$year-12-01"));
    }

    $parts = preg_split('/\s+/', $s);
    if (count($parts) >= 4) {
        $month = $parts[2];
        $year  = $parts[3];
        $ts = strtotime("$month $year");
        if ($ts !== false) {
            return date("Y-m-t", $ts);
        }
    }

    return null;
}
