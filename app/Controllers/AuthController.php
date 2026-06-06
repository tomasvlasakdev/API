<?php

namespace App\Controllers;

use PDO;
use PDOException;

class AuthController
{
    public function showLogin()
    {
        require_once __DIR__ . '/../../config/config.php';

        if (isset($_SESSION['user_id'])) {
            header('Location: /');
            exit();
        }

        $clientId = $_ENV['GOOGLE_CLIENT_ID'] ?? '731126819470-jm53vv15n3nlnjpllpamji6obeqae7v1.apps.googleusercontent.com';
        $baseUrl = $_ENV['BASE_URL'] ?? '/weby/API';
        // The original oauth handler was in public/oauth_handler.php or views/oauth_handler.php
        // The new login route is /login POST based on web.php: `$router->post('/login', [AuthController::class, 'login']);`
        $loginUri = rtrim($baseUrl, '/') . "/login";
        
        // Output HTML
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - London Housing Data</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://accounts.google.com/gsi/client" async></script>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-box glass-panel">
            <h1>London Housing Data</h1>
            <p>Sign in to access the dashboard</p>
            
            <div id="g_id_onload"
                 data-client_id="<?= htmlspecialchars($clientId) ?>"
                 data-context="signin"
                 data-ux_mode="popup"
                 data-login_uri="<?= htmlspecialchars($loginUri) ?>"
                 data-auto_prompt="false">
            </div>

            <div class="g_id_signin"
                 data-type="standard"
                 data-shape="pill"
                 data-theme="outline"
                 data-text="signin_with"
                 data-size="large"
                 data-logo_alignment="left">
            </div>
        </div>
    </div>
</body>
</html>
        <?php
    }

    private function verifyGoogleToken($idToken)
    {
        $client_id = $_ENV['GOOGLE_CLIENT_ID'] ?? "731126819470-jm53vv15n3nlnjpllpamji6obeqae7v1.apps.googleusercontent.com";
        
        $url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . $idToken;
        $response = file_get_contents($url);
        $userData = json_decode($response, true);
        
        if (isset($userData['aud']) && $userData['aud'] === $client_id) {
            return $userData;
        }
        
        return false;
    }

    public function login()
    {
        require_once __DIR__ . '/../../config/config.php';
        require_once __DIR__ . '/../../src/logger.php';

        $logFile = __DIR__ . '/../../logs/logging.json';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $credential = $_POST['credential'] ?? null;
            
            if (!$credential) {
                log_error($logFile, "OAuth: Missing credential");
                die('Invalid request');
            }
            
            $userData = $this->verifyGoogleToken($credential);
            
            if (!$userData) {
                log_error($logFile, "OAuth: Invalid token");
                die('Authentication failed');
            }
            
            $email = filter_var($userData['email'], FILTER_SANITIZE_EMAIL);
            $name = htmlspecialchars($userData['name'] ?? '', ENT_QUOTES, 'UTF-8');
            $google_id = htmlspecialchars($userData['sub'] ?? '', ENT_QUOTES, 'UTF-8');
            
            global $db;
            if (!isset($db)) {
                $db = (require __DIR__ . '/../../config/config.php')['db'];
            }

            try {
                // Check if user exists
                $stmt = $db->prepare("SELECT * FROM users_housing_data WHERE email = ? OR google_id = ?");
                $stmt->execute([$email, $google_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    // Update existing user
                    if ($user['is_blocked']) {
                        log_error($logFile, "OAuth: Blocked user attempted login: $email");
                        die('Your account has been blocked. Please contact administrator.');
                    }
                    
                    $stmt = $db->prepare("UPDATE users_housing_data SET last_login = NOW(), google_id = ? WHERE id = ?");
                    $stmt->execute([$google_id, $user['id']]);
                    
                    log_info($logFile, "OAuth: User logged in: $email");
                } else {
                    // Check if this is the first user
                    $countStmt = $db->query("SELECT COUNT(*) FROM users_housing_data");
                    $userCount = $countStmt->fetchColumn();
                    $role = ($userCount == 0) ? 'admin' : 'visitor';

                    // Create new user
                    $stmt = $db->prepare("INSERT INTO users_housing_data (email, name, google_id, role, created_at, last_login) VALUES (?, ?, ?, ?, NOW(), NOW())");
                    $stmt->execute([$email, $name, $google_id, $role]);
                    $user_id = $db->lastInsertId();
                    
                    // Fetch newly created user
                    $stmt = $db->prepare("SELECT * FROM users_housing_data WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    log_info($logFile, "OAuth: New user registered: $email");
                }
                
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['logged_in'] = true;
                
                header('Location: /');
                exit();
                
            } catch (PDOException $e) {
                log_error($logFile, "OAuth: Database error - " . $e->getMessage());
                die('Database error occurred');
            }
        }
    }

    public function logout()
    {
        session_start();
        require_once __DIR__ . '/../../src/logger.php';

        $logFile = __DIR__ . '/../../logs/logging.json';

        if (isset($_SESSION['user_email'])) {
            log_info($logFile, "User logged out: " . $_SESSION['user_email']);
        }

        // Destroy session
        session_unset();
        session_destroy();

        // Redirect to login page
        header('Location: /login');
        exit();
    }
}
