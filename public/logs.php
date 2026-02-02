<?php
include_once '../library/functions.php';
include_once 'notifs.php';

requireLogin();

$logs = loadLogs("../logs/logging.json");

$level = $_GET['level'] ?? 'all';
$current_sort = $_GET['sort'] ?? 'newest';

$logs = filterLogs($logs, $level);
$logs = sortLogs($logs, $current_sort);

$total_logs = count($logs);
$logs_per_page = 6;

$current_page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$total_pages = max(1, ceil($total_logs / $logs_per_page));

$logs_page = paginate_logs($logs, $current_page, $logs_per_page);
?>

<!DOCTYPE html>
<html lang="cs">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Logs">
  <title>Logy - London Data</title>
  <link rel="stylesheet" href="style.css" />
  <script src='../library/functions.js'></script>
</head>

<body>
  <div class="container">
    <main class="main">
      <?php sidebar(); ?>

      <section class="log">
        <h2 id="title_log">Logy</h2>

        <div class="filters">
          <label for="filter_level">Filtrování dle typu:</label>
          <select id="filter-level" onchange="updateFilters()">
            <option value="all" <?= $level === 'all' ? 'selected' : '' ?>>Vše</option>
            <option value="INFO" <?= $level === 'INFO' ? 'selected' : '' ?>>INFO</option>
            <option value="ERROR" <?= $level === 'ERROR' ? 'selected' : '' ?>>ERROR</option>
            <option value="IMPORT" <?= $level === 'IMPORT' ? 'selected' : '' ?>>IMPORT</option>
            <option value="DOWNLOAD" <?= $level === 'DOWNLOAD' ? 'selected' : '' ?>>DOWNLOAD</option>
          </select>

          <label for="filter-sort">Řazení:</label>
          <select id="filter-sort" onchange="updateFilters()">
            <option value="newest" <?= $current_sort === 'newest' ? 'selected' : '' ?>>Od nejnovějšího</option>
            <option value="oldest" <?= $current_sort === 'oldest' ? 'selected' : '' ?>>Od nejstaršího</option>
          </select>
        </div>

        <ul class="log-list">
          <?php
          if (is_array($logs_page) && count($logs_page) > 0) {
            foreach ($logs_page as $log) {
              echo '<li>' . format_log_entry($log, 'html') . '</li>';
            }
          } else {
            echo '<li>No logs for entered filter.</li>';
          }
          ?>
        </ul>

        <?= render_pagination($current_page, $total_pages) ?>
      </section>
    </main>
  </div>
</body>

</html>