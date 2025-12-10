<?php

// Gets data
function get_data($sql) {
  global $db;
  $sql_com = $db->prepare($sql);
  $sql_com->execute();
  $data = $sql_com->fetchAll(PDO::FETCH_ASSOC);
  return $data;
};

// Formats data from logging.json
function format_log_entry ($log, $format = 'text') {
if (!is_array($log) || !isset($log['timestamp'], $log['level'], $log['message'], $log['source_file'])) {
  return $format === 'html' ? '<span class="log-error">Invalid log entry</span>' : 'Invalid log entry';
}

// AI used to find date format of the date function
if($format === 'text') {
    $timestamp = date('H:i:s d/m/Y', strtotime($log['timestamp']));
    $level = strtoupper($log['level']);
    $message = $log['message'];
    $source = basename($log['source_file']);
    return "[$timestamp] [$level] $message (Source: $source)";
}
elseif ($format === 'html') {
    $timestamp = date('H:i:s d/m/Y', strtotime($log['timestamp']));
    $level = strtoupper($log['level']);
    $message = htmlspecialchars($log['message']);
    $source = htmlspecialchars(basename($log['source_file']));
    $level_class = strtolower($level);
    return "<span class=\"log-timestamp\">[$timestamp]</span> <span class=\"log-level $level_class\">[$level]</span> $message <span class=\"log-source\">(Source: $source)</span>";
}
return ' ';
}


// HTML sidebar
// AI used for icons php in this function
function sidebar() {
  $current = basename($_SERVER['PHP_SELF']); 
  ?>

  
  <section class="sidebar">
    <div class="sidebar-header">
      <h2>London Housing Data</h2>
      <div class="sidebar-nav">
        <a href="interface.php" class="<?= $current === 'interface.php' ? 'active' : '' ?>">Dashboard</a>
        <a href="logs.php" class="<?= $current === 'logs.php' ? 'active' : '' ?>">Logs</a>
      </div>
    </div>
  </section>
  <?php
}

// SQL Commands
function sql_commands($type) {
  $sum = get_data("SELECT COUNT(*) FROM DATA_API_HOUSING");

  if($type == 1) {
    // Number of records
    $total_records = $sum[0]["COUNT(*)"];
    echo $total_records;
    return;

  }
  elseif($type == 2) {
    // Median
    $median_val = get_data("SELECT value FROM DATA_API_HOUSING WHERE measure = 'median'");
    $avg_val = 0 ;
    foreach ($median_val as $key => $value) {
    $avg_val += $value["value"];
    }
    $avg = $avg_val / $sum[0]["COUNT(*)"];
    echo round($avg);

    return;

  } elseif($type == 3) {
      // Mean
      $mean_val = get_data("SELECT value FROM DATA_API_HOUSING WHERE measure = 'mean'");
      $avg_val_mean = 0 ;
      foreach ($mean_val as $key => $value) {
      $avg_val_mean += $value["value"];
      }
      $avg_mean = $avg_val_mean / $sum[0]["COUNT(*)"];  
      echo round($avg_mean);

      return;

  } elseif($type == 4) {
      // Sold real estate
      $sales_sum = get_data("SELECT COUNT(value) FROM DATA_API_HOUSING WHERE measure = 'sales'");
      $number_of_sales = $sales_sum[0]["COUNT(value)"];
      echo $number_of_sales;

      return;

    }
    elseif($type == 5) {
      // Database size
      $db_size = get_data("SELECT round((data_length + index_length) / 1024 / 1024, 2) AS velikost_MB FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'DATA_API_HOUSING'");
      $db_size_MB = $db_size[0]["velikost_MB"];
      echo $db_size_MB;

      return;

    }
    elseif($type == 6) {
      // Last import
      $latest_import = get_data("SELECT MAX(imported_at) FROM DATA_API_HOUSING ");
      $date_string = $latest_import[0]["MAX(imported_at)"]; // to string
      $date_object = new DateTime($date_string);
      $formatted_latest_import = $date_object->format('j. n. Y H:i:s'); // change of format time
      echo $formatted_latest_import;

      return;

    }
}


function loadLogs($file_path) {
  if(!file_exists($file_path)) 
  return [];
  
  $content = file_get_contents($file_path);
  return json_decode($content, true) ?? [];
}

function filterLogs($logs, $level) {
  if($level === 'all') return $logs;
  return array_filter($logs, fn($log) => strtoupper($log['level'] ?? '') === strtoupper($level));
}

function sortLogs($logs, $sort) {
  if($sort === 'oldest') {
    usort($logs, fn($a, $b) => ($a['timestamp'] ?? 0) <=> ($b['timestamp'] ?? 0)); // fn creates array functions
  } else {
    usort($logs, fn($a, $b) => ($b['timestamp'] ?? 0) <=> ($a['timestamp'] ?? 0));
  }
  return $logs;
}



function paginate_logs($logs, $current_page, $logs_per_page) {
  $offset = ($current_page - 1) * $logs_per_page;
  return array_slice($logs, $offset, $logs_per_page); // returns sequence of elements from array
}



function render_pagination($current_page, $total_pages)
{
  $output = '<div class="pagination">';

// AI used to find characters &laquo ad &raquo 

  // Previous button
  $output .= '<a href="?page=' . max(1, $current_page - 1) . '" class="page-btn prev ' . ($current_page == 1 ? 'disabled' : '') . '">&laquo;</a>';

  // Page number windows
  $start = max(1, $current_page - 2);
  $end = min($total_pages, $current_page + 2);

  if ($start > 1)
    $output .= '<a href="?page=1" class="page-btn">1</a><span class="dots">...</span>';
  for ($i = $start; $i <= $end; $i++) {
    $active = $i == $current_page ? 'active' : '';
    $output .= '<a href="?page=' . $i . '" class="page-btn ' . $active . '">' . $i . '</a>';
  }
  if ($end < $total_pages)
    $output .= '<span class="dots">...</span><a href="?page=' . $total_pages . '" class="page-btn">' . $total_pages . '</a>';

  // Next button
  $output .= '<a href="?page=' . min($total_pages, $current_page + 1) . '" class="page-btn next ' . ($current_page == $total_pages ? 'disabled' : '') . '">&raquo;</a>';
  $output .= '</div>';
  return $output;
}

?>