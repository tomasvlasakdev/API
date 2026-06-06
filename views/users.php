<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - London Housing Data</title>
    <link rel="stylesheet" href="/style.css">
    
</head>

<body>
    <div class="container">
        <?php include __DIR__ . '/components/sidebar.php'; ?>
        <main class="main-content">

            <section class="content glass-panel" style="padding: 24px;">
                <div class="page-header">
                    <h1>User Management</h1>
                    <p>Manage users, roles, and permissions</p>
                </div>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <table class="user-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['id'] ?? '') ?></td>
                                <td><?= htmlspecialchars($user['name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                                
                                <td>
                                    <span class="role-badge role-<?= htmlspecialchars($user['role'] ?? 'visitor') ?>">
                                        <?= strtoupper(htmlspecialchars($user['role'] ?? 'visitor')) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="<?= ($user['is_blocked'] ?? 0) ? 'status-blocked' : 'status-active' ?>">
                                        <?= ($user['is_blocked'] ?? 0) ? 'BLOCKED' : 'ACTIVE' ?>
                                    </span>
                                </td>
                                <td><?= isset($user['created_at']) ? date('d.m.Y H:i', strtotime($user['created_at'])) : '' ?>
                                </td>
                                <td><?= isset($user['last_login']) ? date('d.m.Y H:i', strtotime($user['last_login'])) : 'Never' ?>
                                </td>
                                <td>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <select name="new_role" class="btn btn-primary">
                                                <option value="">Change Role</option>
                                                <option value="admin">Admin</option>
                                                <option value="developer">Developer</option>
                                                <option value="visitor">Visitor</option>
                                            </select>
                                            <button type="submit" name="action" value="change_role"
                                                class="btn btn-primary">Apply</button>
                                        </form>

                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <?php if ($user['is_blocked'] ?? 0): ?>
                                                <button type="submit" name="action" value="unblock" class="btn btn-success">Unblock</button>
                                            <?php else: ?>
                                                <button type="submit" name="action" value="block" class="btn btn-warning">Block</button>
                                            <?php endif; ?>
                                        </form>

                                        <form method="POST" style="display: inline;"
                                            onsubmit="return confirm('Are you sure you want to delete this user?');">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <button type="submit" name="action" value="delete" class="btn btn-danger">Delete</button>
                                        </form>
                                    <?php else: ?>
                                        <em>Current User</em>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>

</html>