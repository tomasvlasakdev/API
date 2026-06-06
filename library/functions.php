<?php

// Gets data
function get_data($sql) {
  global $db;
  $sql_com = $db->prepare($sql);
  $sql_com->execute();
  $data = $sql_com->fetchAll(PDO::FETCH_ASSOC);
  return $data;
}

// Formats data from logging.json
function format_log_entry($log, $format = 'text') {
  if (!is_array($log) || !isset($log['timestamp'], $log['level'], $log['message'], $log['source_file'])) {
    return $format === 'html' ? '<span class="log-error">Invalid log entry</span>' : 'Invalid log entry';
  }

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
    usort($logs, fn($a, $b) => ($a['timestamp'] ?? 0) <=> ($b['timestamp'] ?? 0));
  } else {
    usort($logs, fn($a, $b) => ($b['timestamp'] ?? 0) <=> ($a['timestamp'] ?? 0));
  }
  return $logs;
}

function paginate_logs($logs, $current_page, $logs_per_page) {
  $offset = ($current_page - 1) * $logs_per_page;
  return array_slice($logs, $offset, $logs_per_page);
}

function render_pagination($current_page, $total_pages) {
  $params = $_GET;
  $build_url = function($page) use ($params) {
      $params['page'] = $page;
      return '?' . http_build_query($params);
  };

  $output = '<div class="pagination">';
  
  $output .= '<a href="' . $build_url(max(1, $current_page - 1)) . '" class="page-btn prev ' . ($current_page == 1 ? 'disabled' : '') . '">&laquo;</a>';

  $start = max(1, $current_page - 2);
  $end = min($total_pages, $current_page + 2);

  if ($start > 1)
    $output .= '<a href="' . $build_url(1) . '" class="page-btn">1</a><span class="dots">...</span>';
  
  for ($i = $start; $i <= $end; $i++) {
    $active = $i == $current_page ? 'active' : '';
    $output .= '<a href="' . $build_url($i) . '" class="page-btn ' . $active . '">' . $i . '</a>';
  }
  
  if ($end < $total_pages)
    $output .= '<span class="dots">...</span><a href="' . $build_url($total_pages) . '" class="page-btn">' . $total_pages . '</a>';

  $output .= '<a href="' . $build_url(min($total_pages, $current_page + 1)) . '" class="page-btn next ' . ($current_page == $total_pages ? 'disabled' : '') . '">&raquo;</a>';
  $output .= '</div>';
  return $output;
}

// Check if user is logged in
function requireLogin() {
  if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit();
  }
}

// Check if user has specific role
function requireRole($role) {
  requireLogin();
  if ($_SESSION['user_role'] !== $role && $_SESSION['user_role'] !== 'admin') {
    die('Access denied. Insufficient permissions.');
  }
}

// Get current user info
function getCurrentUser() {
  if (!isset($_SESSION['user_id'])) {
    return null;
  }
  
  global $db;
  $stmt = $db->prepare("SELECT * FROM users_housing_data WHERE id = ?");
  $stmt->execute([$_SESSION['user_id']]);
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

?>