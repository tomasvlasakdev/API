<?php
include_once '../config/config.php';
include_once '../library/functions.php';
include_once './notifs.php';
include_once './sendNotif.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>London housing data</title>
  <link rel="stylesheet" href="style.css">
  <link rel="icon" type="image/x-icon" href="favicon.png">
  <script src='../library/functions.js'></script>
  <script src="https://accounts.google.com/gsi/client" async></script>

</head>
<body>
  <div class="container">
    <main class="main">

      <?php 
        sidebar();
      ?>

      <div id="g_id_onload"
     data-client_id="731126819470-jm53vv15n3nlnjpllpamji6obeqae7v1.apps.googleusercontent.com"
     data-context="signin"
     data-ux_mode="popup"
     data-login_uri="https://vlasato23.sps-prosek.cz/weby/API/googleAuth.php"
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

      <div id="refresh"><a href="../index.php"><p>Refresh</p></a></div>

      <section class="cards">
        <div class="card">
          <h3>Number of records in database:</h3>
          <p><?php sql_commands($type = "1");?></p>
        </div>

        <div class="card">
          <h3>Median real estate price (£)</h3>
          <p><?php sql_commands($type = "2"); ?> </p>
        </div>

        <div class="card">
          <h3>Mean real estate price (£)</h3>
          <p><?php sql_commands($type = "3"); ?> </p>
        </div>

        <div class="card">
          <h3>Number of sold real estate</h3>
          <p><?php sql_commands($type = "4"); ?></p>
        </div>

        <div class="card">
          <h3>Database size (MB)</h3>
          <p><?php sql_commands($type = "5"); ?></p>
        </div>

        <div class="card">
          <h3>Last import</h3>
          <p><?php sql_commands($type = "6"); ?></p>
        </div>
        </section>

        
    </main>
  </div>
</body>
</html>
