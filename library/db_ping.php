<?php
function db_ping($config, &$db_ok, &$db_error) { // & - mění externí proměnné
    if (isset($config['db']) && $config['db'] instanceof PDO) {
        try {
            $config['db']->query('SELECT 1');
            $db_ok = true;
            return true;
        } catch (Exception $e) {
            $db_ok = false;
            $db_error = $e->getMessage();
            return false;
        }
    }
    $db_error = "Konfigurace databáze chybí nebo není PDO.";
    return false;
}
?>
