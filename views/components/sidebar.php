<?php $current = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH); ?>
<aside class="sidebar glass-panel">
  <div class="sidebar-header">
    <h2>London Housing Data</h2>
    <nav class="sidebar-nav">
      <a href="/" class="<?= $current === '/' ? 'active' : '' ?>">Dashboard</a>
      <a href="/logs" class="<?= $current === '/logs' ? 'active' : '' ?>">Logs</a>
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="/users" class="<?= $current === '/users' ? 'active' : '' ?>">Users</a>
        <a href="/api-keys" class="<?= $current === '/api-keys' ? 'active' : '' ?>">API Keys</a>
        <a href="/logout">Logout</a>
      <?php endif; ?>
    </nav>
  </div>
</aside>
