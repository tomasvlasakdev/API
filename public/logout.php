<?php
session_start();
require_once __DIR__ . '/../src/logger.php';

$logFile = __DIR__ . '/../logs/logging.json';

if (isset($_SESSION['user_email'])) {
    log_info($logFile, "User logged out: " . $_SESSION['user_email']);
}

// Destroy session
session_unset();
session_destroy();

// Redirect to login page
header('Location: login.php');
exit();
?>