<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/logger.php';

$logFile = __DIR__ . '/../logs/logging.json';

function verifyGoogleToken($idToken) {
    $client_id = "731126819470-jm53vv15n3nlnjpllpamji6obeqae7v1.apps.googleusercontent.com";
    
    $url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . $idToken;
    $response = file_get_contents($url);
    $userData = json_decode($response, true);
    
    if (isset($userData['aud']) && $userData['aud'] === $client_id) {
        return $userData;
    }
    
    return false;
}

// This part was created with assistance of AI

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $credential = $_POST['credential'] ?? null;
    
    if (!$credential) {
        log_error($logFile, "OAuth: Missing credential");
        die('Invalid request');
    }
    
    $userData = verifyGoogleToken($credential);
    
    if (!$userData) {
        log_error($logFile, "OAuth: Invalid token");
        die('Authentication failed');
    }
    
    $email = filter_var($userData['email'], FILTER_SANITIZE_EMAIL);
    $name = htmlspecialchars($userData['name'] ?? '', ENT_QUOTES, 'UTF-8');
    $google_id = htmlspecialchars($userData['sub'] ?? '', ENT_QUOTES, 'UTF-8');
    
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
            // Create new user with default role "visitor"
            $stmt = $db->prepare("INSERT INTO users_housing_data (email, name, google_id, role, created_at, last_login) VALUES (?, ?, ?, 'visitor', NOW(), NOW())");
            $stmt->execute([$email, $name, $google_id]);
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
        
        // Redirect to dashboard
        header('Location: interface.php');
        exit();
        
    } catch (PDOException $e) {
        log_error($logFile, "OAuth: Database error - " . $e->getMessage());
        die('Database error occurred');
    }
}
?>