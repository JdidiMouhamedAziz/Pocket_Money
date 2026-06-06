<?php
require_once __DIR__ . '/admin_helpers.php';

$adminFlash = getAdminFlash();
$editUser = null;
if (!empty($_GET['editUser'])) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$_GET['editUser']]);
    $editUser = $stmt->fetch(PDO::FETCH_ASSOC);
}

$search = trim($_GET['search'] ?? '');
$filterStatus = strtolower(trim($_GET['filterStatus'] ?? ''));
$filterRole = strtolower(trim($_GET['filterRole'] ?? ''));
$whereClauses = [];
$queryParams = [];
if ($search !== '') {
    $whereClauses[] = '(name LIKE ? OR lastName LIKE ? OR email LIKE ? OR role LIKE ? OR status LIKE ?)';
    $likeSearch = "%{$search}%";
    $queryParams = array_merge($queryParams, [$likeSearch, $likeSearch, $likeSearch, $likeSearch, $likeSearch]);
}
if (in_array($filterStatus, ['active', 'pending', 'blocked', 'deleted'], true)) {
    $whereClauses[] = 'LOWER(status) = ?';
    $queryParams[] = $filterStatus;
}
if (in_array($filterRole, ['admin', 'user'], true)) {
    $whereClauses[] = 'LOWER(role) = ?';
    $queryParams[] = $filterRole;
}
$sql = 'SELECT * FROM users' . ($whereClauses ? ' WHERE ' . implode(' AND ', $whereClauses) : '') . ' ORDER BY updatedAt DESC';
$userStmt = $pdo->prepare($sql);
$userStmt->execute($queryParams);
$users = $userStmt->fetchAll(PDO::FETCH_ASSOC);
$userCounts = ['active' => 0, 'pending' => 0, 'blocked' => 0, 'deleted' => 0, 'admin' => 0, 'user' => 0];
foreach ($users as $user) {
    $status = strtolower($user['status'] ?? 'pending');
    if (isset($userCounts[$status])) {
        $userCounts[$status]++;
    }
    $role = strtolower($user['role'] ?? 'user');
    if (isset($userCounts[$role])) {
        $userCounts[$role]++;
    }
}
$showingCount = count($users);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>BudgetPro – User Management</title>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet"/>
  <style>
    :root{
      --bg:#f7f8fc;--white:#fff;--sidebar-bg:#fff;--sidebar-border:#eef0f8;
      --accent:#4f46e5;--accent-soft:#eef0ff;
      --teal:#059669;--teal-soft:#d1fae5;
      --red:#ef4444;--red-soft:#fee2e2;
      --orange:#f59e0b;--orange-soft:#fff7ed;
      --yellow:#fbbf24;--yellow-soft:#fffbeb;
      --text-dark:#111827;--text-mid:#374151;--text-muted:#6b7280;--text-light:#9ca3af;
      --border:#e5e7eb;--radius:12px;
      --shadow:0 1px 4px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.04);
    }
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text-dark);display:flex;min-height:100vh;font-size:14px;}
    h1,h2,h3,h4{font-family:'Sora',sans-serif;}

    /* SIDEBAR */
    .sidebar{width:220px;flex-shrink:0;background:var(--sidebar-bg);border-right:1px solid var(--sidebar-border);display:flex;flex-direction:column;padding:0;min-height:100vh;position:fixed;left:0;top:0;bottom:0;}
    .sidebar-header{padding:20px 20px 16px;border-bottom:1px solid var(--sidebar-border);}
    .logo-row{display:flex;align-items:center;gap:10px;}
    .logo-mark{width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,var(--accent),#818cf8);display:flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:800;color:#fff;font-family:'Sora',sans-serif;}
    .logo-text h2{font-size:.98rem;font-weight:800;color:var(--text-dark);line-height:1.1;}
    .logo-text p{font-size:.68rem;color:var(--text-muted);margin-top:1px;}
    .sidebar-nav{padding:12px 10px;flex:1;}
    .nav-item{display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:8px;cursor:pointer;color:var(--text-muted);font-size:.87rem;font-weight:500;text-decoration:none;transition:all .15s;margin-bottom:2px;}
    .nav-item:hover{background:#f3f4f6;color:var(--text-dark);}
    .nav-item.active{background:var(--accent-soft);color:var(--accent);font-weight:700;}
    .nav-item.active .nav-icon{color:var(--accent);}
    .nav-icon{font-size:1rem;width:18px;text-align:center;color:#9ca3af;}
    .sidebar-spacer{flex:1;}
    .sidebar-footer{padding:14px 16px;border-top:1px solid var(--sidebar-border);}
    .btn-logout{width:100%;background:var(--yellow);color:#7a5c00;border:none;border-radius:9px;padding:10px;font-family:'Sora',sans-serif;font-weight:700;font-size:.83rem;cursor:pointer;text-align:center;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:6px;transition:opacity .2s;}
    .btn-logout:hover{opacity:.9;}

    /* MAIN */
    .main{margin-left:220px;flex:1;display:flex;flex-direction:column;}

    /* CONTENT */
    .content{padding:26px 28px;}

    /* BREADCRUMB */
    .breadcrumb{display:flex;align-items:center;gap:6px;font-size:.78rem;color:var(--text-muted);margin-bottom:14px;}
    .breadcrumb a{color:var(--text-muted);text-decoration:none;}
    .breadcrumb a:hover{color:var(--accent);}
    .breadcrumb .active-crumb{color:var(--accent);font-weight:600;}
    .breadcrumb .sep{color:var(--text-light);}

    /* PAGE HEADER */
    .page-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:24px;}
    .page-header h1{font-size:1.45rem;font-weight:800;}
    .page-header p{font-size:.84rem;color:var(--text-muted);margin-top:5px;}
    .btn-add-user{background:var(--accent);color:#fff;border:none;border-radius:9px;padding:10px 18px;font-family:'Sora',sans-serif;font-weight:700;font-size:.83rem;cursor:pointer;display:flex;align-items:center;gap:7px;box-shadow:0 4px 12px rgba(79,70,229,.3);transition:opacity .2s;}
    .btn-add-user:hover{opacity:.9;}

    .admin-flash{background:#eef6ff;border:1px solid #dbeafe;color:#1e40af;border-radius:12px;padding:14px 18px;margin-bottom:18px;font-weight:600;}
    .admin-flash.error{background:#fef2f2;border-color:#fecaca;color:#b91c1c;}
    .form-card{background:var(--white);border:1px solid var(--border);border-radius:18px;padding:18px;margin-bottom:20px;box-shadow:var(--shadow);}
    .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:14px;}
    .form-field{display:flex;flex-direction:column;gap:6px;}
    .form-field.full{grid-column:1/-1;}
    .form-input{background:#f9fafb;border:1px solid var(--border);border-radius:10px;padding:10px 12px;font-family:'DM Sans',sans-serif;font-size:.86rem;color:var(--text-dark);outline:none;}
    .form-input:focus{border-color:var(--accent);background:#fff;}
    .form-actions{display:flex;gap:10px;align-items:center;margin-top:12px;}
    .btn-submit{background:var(--accent);color:#fff;border:none;border-radius:10px;padding:10px 16px;font-family:'Sora',sans-serif;font-weight:700;font-size:.85rem;cursor:pointer;}
    .btn-secondary{background:#f3f4f6;color:var(--text-dark);border:none;border-radius:10px;padding:10px 16px;font-family:'DM Sans',sans-serif;font-weight:700;font-size:.85rem;cursor:pointer;}

    /* USERS TABLE */
    .users-card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);}
    /* TABLE */
    table{width:100%;border-collapse:collapse;}
    thead th{font-size:.67rem;font-weight:700;color:var(--text-light);text-transform:uppercase;letter-spacing:.07em;padding:10px 20px;text-align:left;background:#fafafa;border-bottom:1px solid var(--border);}
    tbody tr{border-bottom:1px solid #f3f4f6;transition:background .12s;}
    tbody tr:last-child{border-bottom:none;}
    tbody tr:hover{background:#fafbff;}
    td{padding:13px 20px;font-size:.85rem;vertical-align:middle;}

    /* User cell */
    .user-cell{display:flex;align-items:center;gap:10px;}
    .user-ava-img{width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;color:#fff;flex-shrink:0;font-family:'Sora',sans-serif;}
    .user-name{font-size:.87rem;font-weight:600;color:var(--text-dark);}
    .user-email{font-size:.75rem;color:var(--text-muted);}

    /* Role badge */
    .role-badge{font-size:.71rem;font-weight:800;padding:3px 10px;border-radius:5px;letter-spacing:.04em;}
    .rb-admin{background:var(--accent-soft);color:var(--accent);}
    .rb-user{background:#f3f4f6;color:var(--text-mid);}

    /* Status badge */
    .status-badge{display:inline-flex;align-items:center;gap:5px;font-size:.78rem;font-weight:600;padding:4px 10px;border-radius:50px;}
    .sb-active{background:var(--teal-soft);color:var(--teal);}
    .sb-pending{background:var(--yellow-soft);color:#92400e;}
    .sb-blocked{background:var(--red-soft);color:var(--red);}
    .status-dot{width:6px;height:6px;border-radius:50%;}
    .dot-teal{background:var(--teal);}
    .dot-yellow{background:var(--yellow);}
    .dot-red{background:var(--red);}

    .td-activity{color:var(--text-muted);font-size:.82rem;}

    /* Actions */
    .actions-cell{display:flex;align-items:center;gap:6px;}
    .action-btn{width:28px;height:28px;border-radius:7px;background:#f3f4f6;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:.8rem;color:var(--text-muted);transition:all .15s;}
    .action-btn:hover{background:var(--accent-soft);border-color:#c7d2fe;color:var(--accent);}
    .action-btn.danger:hover{background:var(--red-soft);border-color:#fecaca;color:var(--red);}
    .btn-approve{background:var(--teal);color:#fff;border:none;border-radius:7px;padding:5px 12px;font-family:'DM Sans',sans-serif;font-size:.77rem;font-weight:700;cursor:pointer;transition:opacity .2s;}
    .btn-approve:hover{opacity:.85;}

  </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-header">
    <div class="logo-row">
      <div class="logo-mark">B</div>
      <div class="logo-text">
        <h2>BudgetPro</h2>
        <p>Collaborative Finance</p>
      </div>
    </div>
  </div>
  <nav class="sidebar-nav">
    <a class="nav-item" href="dashboard.php"><span class="nav-icon">📊</span> Dashboard</a>
    <a class="nav-item active" href="users.php"><span class="nav-icon">👥</span> Users</a>
    <a class="nav-item" href="budgets.php"><span class="nav-icon">🎯</span> Budgets</a>
    <a class="nav-item" href="transactions.php"><span class="nav-icon">💳</span> Transactions</a>
    <a class="nav-item" href="categories.php"><span class="nav-icon">🗂️</span> Categories</a>
    <a class="nav-item" href="alerts.php"><span class="nav-icon">🔔</span> Alerts</a>
    <a class="nav-item" href="export_data.php"><span class="nav-icon">⬇️</span> Export Data</a>
    <a class="nav-item" href="profile.php"><span class="nav-icon">⚙️</span> Settings</a>
  </nav>
  <div class="sidebar-spacer"></div>
  <div class="sidebar-footer">
    <a class="btn-logout" href="/pocket_money/views/logout.php">Logout</a>
  </div>
</aside>

<!-- MAIN -->
<div class="main">
  <div class="content">
    <!-- BREADCRUMB -->
    <div class="breadcrumb">
      <span>Management</span>
      <span class="sep">/</span>
      <span class="active-crumb">User Management</span>
    </div>

    <!-- PAGE HEADER -->
    <div class="page-header">
      <div>
        <h1>User Management</h1>
        <p>Manage organizational access, roles, and security permissions.</p>
      </div>
      <a class="btn-add-user" href="#new-user-form">👤 Add New User</a>
    </div>
    <div class="form-card" style="margin-bottom:20px;">
      <form method="get" action="users.php">
        <div class="form-grid">
          <div class="form-field">
            <label for="user-search">Search users</label>
            <input class="form-input" id="user-search" name="search" type="text" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Search by name, email, role or status" />
          </div>
          <div class="form-field">
            <label for="filter-status">Status</label>
            <select class="form-input" id="filter-status" name="filterStatus">
              <option value="">All statuses</option>
              <option value="active" <?= $filterStatus === 'active' ? 'selected' : '' ?>>Active</option>
              <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
              <option value="blocked" <?= $filterStatus === 'blocked' ? 'selected' : '' ?>>Blocked</option>
              <option value="deleted" <?= $filterStatus === 'deleted' ? 'selected' : '' ?>>Deleted</option>
            </select>
          </div>
          <div class="form-field">
            <label for="filter-role">Role</label>
            <select class="form-input" id="filter-role" name="filterRole">
              <option value="">All roles</option>
              <option value="admin" <?= $filterRole === 'admin' ? 'selected' : '' ?>>Admin</option>
              <option value="user" <?= $filterRole === 'user' ? 'selected' : '' ?>>User</option>
            </select>
          </div>
          <div class="form-field full" style="display:flex;align-items:flex-end;gap:10px;">
            <button type="submit" class="btn-submit">Apply filters</button>
            <a class="btn-secondary" href="users.php">Reset</a>
          </div>
        </div>
      </form>
    </div>

    <?php if ($adminFlash): ?>
      <div class="admin-flash <?= $adminFlash['success'] ? '' : 'error' ?>"><?= htmlspecialchars($adminFlash['message'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div id="new-user-form" class="form-card">
      <h3><?= $editUser ? 'Edit User' : 'Create New User' ?></h3>
      <form method="post" action="admin_actions.php?resource=user&action=<?= $editUser ? 'update' : 'create' ?>">
        <?php if ($editUser): ?>
          <input type="hidden" name="id" value="<?= htmlspecialchars($editUser['id'], ENT_QUOTES, 'UTF-8') ?>"/>
        <?php endif; ?>
        <div class="form-grid">
          <div class="form-field">
            <label for="name">First Name</label>
            <input class="form-input" id="name" name="name" type="text" value="<?= htmlspecialchars($editUser['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required/>
          </div>
          <div class="form-field">
            <label for="lastName">Last Name</label>
            <input class="form-input" id="lastName" name="lastName" type="text" value="<?= htmlspecialchars($editUser['lastName'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required/>
          </div>
          <div class="form-field">
            <label for="email">Email</label>
            <input class="form-input" id="email" name="email" type="email" value="<?= htmlspecialchars($editUser['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required/>
          </div>
          <div class="form-field">
            <label for="role">Role</label>
            <select class="form-input" id="role" name="role">
              <option value="user" <?= isset($editUser['role']) && strtolower($editUser['role']) === 'user' ? 'selected' : '' ?>>User</option>
              <option value="admin" <?= isset($editUser['role']) && strtolower($editUser['role']) === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
          </div>
          <div class="form-field">
            <label for="status">Status</label>
            <select class="form-input" id="status" name="status">
              <option value="active" <?= isset($editUser['status']) && strtolower($editUser['status']) === 'active' ? 'selected' : '' ?>>Active</option>
              <option value="pending" <?= isset($editUser['status']) && strtolower($editUser['status']) === 'pending' ? 'selected' : '' ?>>Pending</option>
              <option value="blocked" <?= isset($editUser['status']) && strtolower($editUser['status']) === 'blocked' ? 'selected' : '' ?>>Blocked</option>
            </select>
          </div>
          <div class="form-field full">
            <label for="password"><?= $editUser ? 'New Password (leave blank to keep current)' : 'Password' ?></label>
            <input class="form-input" id="password" name="password" type="password" <?= $editUser ? '' : 'required' ?>/>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn-submit"><?= $editUser ? 'Save Changes' : 'Create User' ?></button>
          <?php if ($editUser): ?>
            <a href="users.php" class="btn-secondary">Cancel</a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <!-- USERS TABLE -->
    <div class="users-card">
      <table>
        <thead>
          <tr>
            <th>User</th>
            <th>Role</th>
            <th>Status</th>
            <th>Last Activity</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $user):
              $badge = statusBadge($user['status'] ?? 'pending');
              $name = trim($user['name'] . ' ' . $user['lastName']);
              $role = strtoupper($user['role'] ?? 'USER');
              $initials = initials($user['name'], $user['lastName']);
          ?>
            <tr>
              <td>
                <div class="user-cell">
                  <div class="user-ava-img" style="background:linear-gradient(135deg,<?= htmlspecialchars(categoryStyle(array_search(strtolower($role), ['admin','user']) ?: 0), ENT_QUOTES, 'UTF-8') ?>, #818cf8)"><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?></div>
                  <div>
                    <div class="user-name"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="user-email"><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></div>
                  </div>
                </div>
              </td>
              <td><span class="role-badge <?= roleBadgeClass($user['role'] ?? 'user') ?>"><?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?></span></td>
              <td><span class="status-badge <?= htmlspecialchars($badge['class'], ENT_QUOTES, 'UTF-8') ?>"><span class="status-dot <?= $badge['class'] === 'sb-active' ? 'dot-teal' : ($badge['class'] === 'sb-pending' ? 'dot-yellow' : 'dot-red') ?>"></span><?= htmlspecialchars($badge['label'], ENT_QUOTES, 'UTF-8') ?></span></td>
              <td class="td-activity"><?= htmlspecialchars(relativeTime($user['updatedAt'] ?? $user['createdAt']), ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <div class="actions-cell">
                  <a class="action-btn" href="?editUser=<?= urlencode($user['id']) ?>">✏️</a>
                  <form method="post" action="admin_actions.php?resource=user&action=delete" style="display:inline-block; margin:0;">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8') ?>"/>
                    <button type="submit" class="action-btn danger" style="border:none;background:transparent;padding:0;">🗑</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

    </div>
  </div>
</div>

</body>
</html>