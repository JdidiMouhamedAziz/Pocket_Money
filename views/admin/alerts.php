<?php
require_once __DIR__ . '/admin_helpers.php';

$adminFlash = getAdminFlash();

$filterStatus = strtolower(trim($_GET['filterStatus'] ?? 'all'));
$filterAbout = strtolower(trim($_GET['filterAbout'] ?? 'all'));
$whereClauses = [];
$queryParams = [];
if ($filterStatus === 'unread') {
    $whereClauses[] = 'a.isReaded = 0';
} elseif ($filterStatus === 'read') {
    $whereClauses[] = 'a.isReaded = 1';
}
if (in_array($filterAbout, ['transaction', 'system'], true)) {
    $whereClauses[] = 'LOWER(a.about) = ?';
    $queryParams[] = $filterAbout;
}
$sql = "SELECT a.*, u.name AS userName, u.lastName AS userLastName FROM alert a LEFT JOIN users u ON u.id=a.userId";
if ($whereClauses) {
    $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
}
$sql .= ' ORDER BY a.dateSend DESC LIMIT 10';
$alertsStmt = $pdo->prepare($sql);
$alertsStmt->execute($queryParams);
$alerts = $alertsStmt->fetchAll(PDO::FETCH_ASSOC);
$totalAlerts = (int) $pdo->query("SELECT COUNT(*) FROM alert")->fetchColumn();
$unreadAlerts = (int) $pdo->query("SELECT COUNT(*) FROM alert WHERE isReaded = 0")->fetchColumn();
$readAlerts = (int) $pdo->query("SELECT COUNT(*) FROM alert WHERE isReaded = 1")->fetchColumn();
$transactionAlerts = (int) $pdo->query("SELECT COUNT(*) FROM alert WHERE about = 'transaction'")->fetchColumn();
$systemAlerts = (int) $pdo->query("SELECT COUNT(*) FROM alert WHERE about = 'system'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>BudgetPro – Alerts & Notifications</title>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet"/>
  <style>
    :root{--bg:#f7f8fc;--white:#fff;--accent:#4f46e5;--accent-soft:#eef0ff;--teal:#059669;--teal-soft:#d1fae5;--red:#ef4444;--red-soft:#fee2e2;--orange:#f59e0b;--orange-soft:#fff7ed;--text-dark:#111827;--text-mid:#374151;--text-muted:#6b7280;--text-light:#9ca3af;--border:#e5e7eb;--radius:12px;--shadow:0 1px 4px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.04);}
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text-dark);display:flex;min-height:100vh;font-size:14px;}
    h1,h2,h3,h4{font-family:'Sora',sans-serif;}
    .sidebar{width:200px;flex-shrink:0;background:var(--white);border-right:1px solid var(--border);display:flex;flex-direction:column;min-height:100vh;position:fixed;left:0;top:0;bottom:0;}
    .sidebar-header{padding:16px;border-bottom:1px solid var(--border);}
    .logo-row{display:flex;align-items:center;gap:9px;}
    .logo-mark{width:32px;height:32px;border-radius:9px;background:linear-gradient(135deg,var(--accent),#818cf8);display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:800;color:#fff;}
    .logo-text h2{font-size:.9rem;font-weight:800;line-height:1.1;}
    .logo-text p{font-size:.62rem;color:var(--text-muted);}
    .sidebar-nav{padding:10px 8px;flex:1;}
    .nav-item{display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:8px;cursor:pointer;color:var(--text-muted);font-size:.85rem;font-weight:500;text-decoration:none;transition:all .15s;margin-bottom:2px;}
    .nav-item:hover{background:#f3f4f6;color:var(--text-dark);}
    .nav-item.active{background:var(--accent-soft);color:var(--accent);font-weight:700;}
    .nav-item.active .nav-icon{color:var(--accent);}
    .nav-icon{font-size:.9rem;width:17px;text-align:center;color:#9ca3af;}
    .sidebar-spacer{flex:1;}
    .sidebar-footer{padding:12px 14px;border-top:1px solid var(--border);}
    .btn-logout{width:100%;background:var(--yellow);color:#7a5c00;border:none;border-radius:8px;padding:9px;font-family:'Sora',sans-serif;font-weight:700;font-size:.8rem;cursor:pointer;text-align:center;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:5px;}
    .btn-logout:hover{opacity:.9;}
    .admin-flash{background:#eef6ff;border:1px solid #dbeafe;color:#1e40af;border-radius:12px;padding:14px 18px;margin-bottom:18px;font-weight:600;}
    .admin-flash.error{background:#fef2f2;border-color:#fecaca;color:#b91c1c;}
    .main{margin-left:200px;flex:1;display:flex;flex-direction:column;}
    /* LAYOUT */
    .content{padding:22px 24px;display:grid;grid-template-columns:1fr;gap:20px;}
    .left-col{display:flex;flex-direction:column;gap:0;}
    /* FILTER BAR */
    .filter-bar{background:var(--white);border-radius:var(--radius) var(--radius) 0 0;padding:14px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;}
    .filter-label{font-size:.68rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:6px;}
    .filter-tabs{display:flex;flex-direction:column;gap:4px;width:100%;}
    .ftab-row{display:flex;gap:5px;}
    .ftab{display:flex;align-items:center;justify-content:space-between;background:#f3f4f6;border:1px solid var(--border);border-radius:8px;padding:8px 12px;cursor:pointer;font-size:.82rem;font-weight:600;color:var(--text-mid);transition:all .15s;min-width:110px;}
    .ftab.active{background:var(--accent);border-color:var(--accent);color:#fff;}
    .ftab-count{font-size:.7rem;font-weight:700;padding:1px 7px;border-radius:50px;background:rgba(255,255,255,.25);color:#fff;}
    .ftab:not(.active) .ftab-count{background:#e5e7eb;color:var(--text-mid);}
    .filter-select{width:100%;background:#f9fafb;border:1px solid var(--border);border-radius:10px;padding:10px 12px;font-family:'DM Sans',sans-serif;font-size:.86rem;color:var(--text-dark);outline:none;transition:border-color .2s;}
    .filter-select:focus{border-color:var(--accent);background:#fff;}
    /* ALERT LIST */
    .alert-item{padding:16px 18px;border-bottom:1px solid #f3f4f6;display:flex;gap:12px;transition:background .12s;}
    .alert-item:hover{background:#fafbff;}
    .alert-item:last-child{border-bottom:none;}
    .alert-left-bar{width:4px;border-radius:50px;flex-shrink:0;align-self:stretch;}
    .bar-red{background:var(--red);}
    .bar-orange{background:var(--orange);}
    .bar-blue{background:#3b82f6;}
    .alert-body{flex:1;}
    .alert-top-row{display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:5px;}
    .alert-title-row{display:flex;align-items:center;gap:6px;}
    .alert-title{font-size:.9rem;font-weight:700;}
    .alert-title.red{color:var(--red);}
    .alert-title.orange{color:var(--orange);}
    .alert-title.blue{color:var(--accent);}
    .crit-badge{font-size:.62rem;font-weight:800;padding:2px 7px;border-radius:4px;letter-spacing:.04em;}
    .cb-critical{background:var(--red-soft);color:var(--red);}
    .cb-warning{background:var(--orange-soft);color:var(--orange);}
    .cb-new{background:var(--accent-soft);color:var(--accent);}
    .btn-take-action{background:var(--accent);color:#fff;border:none;border-radius:7px;padding:5px 12px;font-family:'Sora',sans-serif;font-size:.72rem;font-weight:700;cursor:pointer;white-space:nowrap;transition:opacity .2s;}
    .btn-take-action:hover{opacity:.85;}
    .alert-text{font-size:.78rem;color:var(--text-mid);line-height:1.55;margin-bottom:8px;}
    .alert-meta{display:flex;align-items:center;gap:10px;font-size:.7rem;color:var(--text-light);}
    .alert-meta-item{display:flex;align-items:center;gap:3px;}
    .end-msg{text-align:center;padding:18px;font-size:.78rem;color:var(--text-muted);}
    .end-msg a{color:var(--accent);font-weight:600;text-decoration:none;cursor:pointer;}
  </style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-header">
    <div class="logo-row"><div class="logo-mark">B</div><div class="logo-text"><h2>BudgetPro</h2><p>Collaborative Finance</p></div></div>
  </div>
  <nav class="sidebar-nav">
    <a class="nav-item" href="dashboard.php"><span class="nav-icon">📊</span> Dashboard</a>
    <a class="nav-item" href="users.php"><span class="nav-icon">👥</span> Users</a>
    <a class="nav-item" href="budgets.php"><span class="nav-icon">🎯</span> Budgets</a>
    <a class="nav-item" href="transactions.php"><span class="nav-icon">💳</span> Transactions</a>
    <a class="nav-item" href="categories.php"><span class="nav-icon">🗂️</span> Categories</a>
    <a class="nav-item active" href="alerts.php"><span class="nav-icon">🔔</span> Alerts</a>
    <a class="nav-item" href="export_data.php"><span class="nav-icon">⬇️</span> Export Data</a>
    <a class="nav-item" href="profile.php"><span class="nav-icon">⚙️</span> Settings</a>
  </nav>
  <div class="sidebar-spacer"></div>
  <div class="sidebar-footer"><a class="btn-logout" href="/pocket_money/views/logout.php">Logout</a></div>
</aside>
<div class="main">
  <div class="content">
    <!-- LEFT -->
    <div class="left-col">
      <div class="filter-bar">
        <form method="get" action="alerts.php" style="display:flex;gap:10px;align-items:flex-end;width:100%;flex-wrap:wrap;">
          <div style="flex:1;min-width:180px;">
            <div class="filter-label">Status</div>
            <select class="filter-select" name="filterStatus">
              <option value="all" <?= $filterStatus === 'all' ? 'selected' : '' ?>>All alerts</option>
              <option value="unread" <?= $filterStatus === 'unread' ? 'selected' : '' ?>>Unread</option>
              <option value="read" <?= $filterStatus === 'read' ? 'selected' : '' ?>>Read</option>
            </select>
          </div>
          <div style="flex:1;min-width:180px;">
            <div class="filter-label">Type</div>
            <select class="filter-select" name="filterAbout">
              <option value="all" <?= $filterAbout === 'all' ? 'selected' : '' ?>>All types</option>
              <option value="transaction" <?= $filterAbout === 'transaction' ? 'selected' : '' ?>>Transaction</option>
              <option value="system" <?= $filterAbout === 'system' ? 'selected' : '' ?>>System</option>
            </select>
          </div>
          <div style="display:flex;gap:10px;align-items:center;">
            <button type="submit" class="btn-take-action">Apply filters</button>
            <a class="btn-take-action" href="alerts.php" style="background:#f3f4f6;color:var(--text-dark);">Reset</a>
          </div>
        </form>
      </div>
      <?php if ($adminFlash): ?>
        <div class="admin-flash <?= $adminFlash['success'] ? '' : 'error' ?>"><?= htmlspecialchars($adminFlash['message'], ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>
      <div style="display:flex;align-items:center;justify-content:flex-end;gap:10px;margin-bottom:12px;">
        <form method="post" action="admin_actions.php?resource=alert&action=markAllRead" style="margin:0;">
          <button type="submit" class="btn-take-action">Mark All Read</button>
        </form>
      </div>
      <div class="alerts-list">
        <!-- Budget Exceeded -->
        <?php if (empty($alerts)): ?>
        <div class="alert-item" data-type="all">
          <div class="alert-left-bar bar-blue"></div>
          <div class="alert-body">
            <div class="alert-top-row"><div class="alert-title-row"><span class="alert-title blue">No alerts</span></div></div>
            <div class="alert-text">You currently have no notifications. Check back later for system updates and spend alerts.</div>
          </div>
        </div>
      <?php else: ?>
        <?php foreach ($alerts as $alert):
          $isUnread = !$alert['isReaded'];
          $isTransaction = strtolower($alert['about'] ?? '') === 'transaction';
          $barClass = $isTransaction ? 'bar-red' : 'bar-blue';
          $titleClass = $isTransaction ? 'red' : 'blue';
          $badgeClass = $isUnread ? 'cb-critical' : 'cb-new';
          $badgeLabel = $isTransaction ? 'CRITICAL' : ($isUnread ? 'NEW' : 'INFO');
          $userName = trim(($alert['userName'] ?? '') . ' ' . ($alert['userLastName'] ?? '')) ?: 'System';
          $metaType = $isTransaction ? 'Transaction' : 'System';
        ?>
          <div class="alert-item" data-type="all <?= $isUnread ? 'unread' : 'read' ?>">
            <div class="alert-left-bar <?= $barClass ?>"></div>
            <div class="alert-body">
              <div class="alert-top-row">
                <div class="alert-title-row">
                  <span class="alert-title <?= $titleClass ?>"><?= htmlspecialchars($alert['name'] ?? 'Alert', ENT_QUOTES, 'UTF-8') ?></span>
                  <span class="crit-badge <?= $badgeClass ?>"><?= htmlspecialchars($badgeLabel, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <form method="post" action="admin_actions.php?resource=alert&action=read" style="margin:0;display:inline-block;">
                  <input type="hidden" name="id" value="<?= htmlspecialchars($alert['idAlert'], ENT_QUOTES, 'UTF-8') ?>"/>
                  <button type="submit" class="btn-take-action"><?= $isUnread ? 'Mark Read' : 'View' ?></button>
                </form>
                <form method="post" action="admin_actions.php?resource=alert&action=delete" style="margin:0;display:inline-block;">
                  <input type="hidden" name="id" value="<?= htmlspecialchars($alert['idAlert'], ENT_QUOTES, 'UTF-8') ?>"/>
                  <button type="submit" class="btn-take-action" style="background:#fef2f2;color:#b91c1c;">Delete</button>
                </form>
              </div>
              <div class="alert-text"><?= htmlspecialchars($alert['message'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
              <div class="alert-meta">
                <span class="alert-meta-item">🕐 <?= htmlspecialchars(relativeTime($alert['dateSend']), ENT_QUOTES, 'UTF-8') ?></span>
                <span class="alert-meta-item">👤 <?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
        <div class="end-msg">You've reached the end of your notifications for today. <a href="export_data.php?type=alerts">Export alert history</a></div>
      </div>
    </div>
  </div>
</div>
</body>
</html>