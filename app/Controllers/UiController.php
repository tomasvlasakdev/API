<?php

namespace App\Controllers;

use PDO;
use PDOException;

class UiController {
    public function index() {
        global $db;
        requireLogin();
        require_once __DIR__ . '/../../library/sql_commands.php';
        require __DIR__ . '/../../views/interface.php';
    }

    public function refresh() {
        requireLogin();
        require __DIR__ . '/../../jobs/import_job.php';
    }

    public function logs() {
        requireLogin();
        $logs = loadLogs(__DIR__ . "/../../logs/logging.json");

        $level = $_GET['level'] ?? 'all';
        $current_sort = $_GET['sort'] ?? 'newest';

        $logs = filterLogs($logs, $level);
        $logs = sortLogs($logs, $current_sort);

        $total_logs = count($logs);
        $logs_per_page = 6;

        $current_page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $total_pages = max(1, ceil($total_logs / $logs_per_page));

        $logs_page = paginate_logs($logs, $current_page, $logs_per_page);
        
        require __DIR__ . '/../../views/logs.php';
    }

    public function users() {
        global $db;
        require_once __DIR__ . '/../../src/logger.php';
        $logFile = __DIR__ . '/../../logs/logging.json';
        requireRole('admin');

        // Handle user actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $action = $_POST['action'];
            $user_id = filter_var($_POST['user_id'] ?? 0, FILTER_VALIDATE_INT);

            if (!$user_id) {
                $error = "Invalid user ID";
            } else {
                try {
                    switch ($action) {
                        case 'change_role':
                            $new_role = $_POST['new_role'];
                            if (in_array($new_role, ['admin', 'developer', 'visitor'])) {
                                $stmt = $db->prepare("UPDATE users_housing_data SET role = ? WHERE id = ?");
                                $stmt->execute([$new_role, $user_id]);
                                log_info($logFile, "Admin changed user role: User ID $user_id to $new_role");
                                $success = "Role updated successfully";
                            }
                            break;

                        case 'block':
                            $stmt = $db->prepare("UPDATE users_housing_data SET is_blocked = 1 WHERE id = ?");
                            $stmt->execute([$user_id]);
                            log_info($logFile, "Admin blocked user: User ID $user_id");
                            $success = "User blocked successfully";
                            break;

                        case 'unblock':
                            $stmt = $db->prepare("UPDATE users_housing_data SET is_blocked = 0 WHERE id = ?");
                            $stmt->execute([$user_id]);
                            log_info($logFile, "Admin unblocked user: User ID $user_id");
                            $success = "User unblocked successfully";
                            break;

                        case 'delete':
                            $stmt = $db->prepare("DELETE FROM users_housing_data WHERE id = ?");
                            $stmt->execute([$user_id]);
                            log_info($logFile, "Admin deleted user: User ID $user_id");
                            $success = "User deleted successfully";
                            break;
                    }
                } catch (PDOException $e) {
                    log_error($logFile, "User management error: " . $e->getMessage());
                    $error = "Database error occurred";
                }
            }
        }

        $stmt = $db->prepare("SELECT * FROM users_housing_data ORDER BY created_at DESC");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../../views/users.php';
    }

    public function privacyPolicy() {
        require __DIR__ . '/../../views/privacyPolicy.php';
    }

    public function notifs() {
        require __DIR__ . '/../../views/notifs.php';
    }

    public function apiKeys() {
        global $db;
        requireLogin();
        $user_id = $_SESSION['user_id'] ?? 1;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            if ($_POST['action'] === 'generate') {
                $client_id = bin2hex(random_bytes(16));
                $client_secret = bin2hex(random_bytes(32));
                $hashed_secret = password_hash($client_secret, PASSWORD_DEFAULT);

                $stmt = $db->prepare("INSERT INTO api_keys (user_id, client_id, client_secret) VALUES (?, ?, ?)");
                $stmt->execute([$user_id, $client_id, $hashed_secret]);

                $_SESSION['new_secret'] = $client_secret;
                header("Location: /api-keys");
                exit;
            } elseif ($_POST['action'] === 'delete') {
                $id = (int)$_POST['id'];
                $stmt = $db->prepare("DELETE FROM api_keys WHERE id = ? AND user_id = ?");
                $stmt->execute([$id, $user_id]);
                header("Location: /api-keys");
                exit;
            }
        }

        $stmt = $db->prepare("SELECT id, client_id, created_at FROM api_keys WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        $keys = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $new_secret = $_SESSION['new_secret'] ?? null;
        unset($_SESSION['new_secret']);

        require __DIR__ . '/../../views/api_keys.php';
    }
}
