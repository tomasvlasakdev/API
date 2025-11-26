<?php
include_once '../config/db.php';
include_once '../library/functions.php';
include_once './notifs.php';
include_once './sendNotif.php';
?>

<!DOCTYPE html>
<html lang="cs">
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
