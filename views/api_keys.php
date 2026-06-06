<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>API Keys - London Housing Data</title>
  <link rel="stylesheet" href="/style.css">
  <link rel="icon" type="image/x-icon" href="/favicon.png">
  <style>
    .key-list { margin-top: 20px; }
    .key-item { background: rgba(255,255,255,0.8); padding: 15px; margin-bottom: 10px; border-radius: 8px; border: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; }
    .key-info h4 { margin: 0 0 5px 0; }
    .key-info p { margin: 0; color: #666; font-size: 0.9em; }
    .btn-danger { background-color: #dc3545; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; }
    .alert-success { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb; }
  </style>
</head>
<body>
  <div class="container">
    <?php include __DIR__ . '/components/sidebar.php'; ?>
    <main class="main-content">
      <div class="page-header" style="display:flex; justify-content:space-between; align-items:center;">
        <h2>API Keys Administration</h2>
        <form method="POST" action="/api-keys">
            <input type="hidden" name="action" value="generate">
            <button type="submit" class="action-btn">Generate New API Key</button>
        </form>
      </div>

      <?php if (isset($new_secret) && $new_secret): ?>
      <div class="alert-success">
        <strong>Important:</strong> Save your new Client Secret now. It will not be shown again!<br><br>
        <code>Client Secret: <?= htmlspecialchars($new_secret) ?></code>
      </div>
      <?php endif; ?>

      <section class="cards" style="display:block;">
        <div class="glass-panel" style="padding: 20px;">
          <h3>Your API Keys</h3>
          <p>Use the Client ID and Client Secret to generate a temporary access token via POST to <code>/api/token.php</code>.</p>
          
          <div class="key-list">
            <?php if (empty($keys)): ?>
                <p>No API keys generated yet.</p>
            <?php else: ?>
                <?php foreach ($keys as $key): ?>
                <div class="key-item">
                    <div class="key-info">
                        <h4>Client ID: <code><?= htmlspecialchars($key['client_id']) ?></code></h4>
                        <p>Created at: <?= htmlspecialchars($key['created_at']) ?></p>
                    </div>
                    <form method="POST" action="/api-keys" onsubmit="return confirm('Are you sure you want to delete this API key?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $key['id'] ?>">
                        <button type="submit" class="btn-danger">Delete</button>
                    </form>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </section>

    </main>
  </div>
</body>
</html>
