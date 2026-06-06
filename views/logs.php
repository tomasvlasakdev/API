<!DOCTYPE html>
<html lang="cs">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Logs">
  <title>Logy - London Data</title>
  <link rel="stylesheet" href="/style.css" />
  <script>
    function updateFilters() {
      const level = document.getElementById('filter-level').value;
      const sort = document.getElementById('filter-sort').value;
      window.location.href = `?level=${level}&sort=${sort}`;
    }
  </script>
</head>

<body>
  <div class="container">
    <?php include __DIR__ . '/components/sidebar.php'; ?>
    <main class="main-content">

      <section class="log glass-panel" style="padding: 24px;">
        <div class="page-header">
          <h2 id="title_log">Logy</h2>
        </div>

        <div class="log-filters">
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