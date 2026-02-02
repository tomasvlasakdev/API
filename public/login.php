<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: interface.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - London Housing Data</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://accounts.google.com/gsi/client" async></script>
    <link rel="stylesheet" src="style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1>London Housing Data</h1>
            <p>Sign in to access the dashboard</p>
            
            <div id="g_id_onload"
                 data-client_id="731126819470-jm53vv15n3nlnjpllpamji6obeqae7v1.apps.googleusercontent.com"
                 data-context="signin"
                 data-ux_mode="popup"
                 data-login_uri="https://vlasato23.sps-prosek.cz/weby/API/public/oauth_handler.php"
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