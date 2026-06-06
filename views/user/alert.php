<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/shared.php';

$currentUserId = $currentUser['id'] ?? ($_SESSION['user']['id'] ?? null);
$alertModel = new Alert($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crud_action']) && $_POST['crud_action'] === 'markAllRead') {
  $alertModel->readAllAlert($currentUserId);
  header('Location: ' . userPageUrl('alert'));
  exit;
}

$alertData = userAlertPageData($currentUserId);
$alerts = $alertData['alerts'] ?? [];
$unreadCount = (int) ($alertData['unreadCount'] ?? 0);
$displayName = $userName ?: trim(($currentUser['name'] ?? 'User') . ' ' . ($currentUser['lastName'] ?? ''));
$displayInitials = $userInitials ?: 'U';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Finzo – Alerts</title>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet"/>
  <style>
    :root{--bg:#f4f5fb;--sidebar-bg:#2d2d3a;--purple:#7c6af5;--purple-light:#f0edff;--teal:#00c9a7;--red:#ff6b8a;--yellow:#f5c842;--white:#ffffff;--text-dark:#1a1a2e;--text-mid:#555770;--text-muted:#9295a8;--sidebar-text:#b0b3c6;--sidebar-label:#6b6e80;--card-radius:14px;--shadow:0 2px 16px rgba(0,0,0,.07);}
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text-dark);display:flex;min-height:100vh;}
    h1,h2,h3,h4{font-family:'Sora',sans-serif;}
    .sidebar{width:200px;flex-shrink:0;background:var(--sidebar-bg);display:flex;flex-direction:column;padding:28px 0 20px;min-height:100vh;position:fixed;left:0;top:0;bottom:0;}
    .sidebar-logo{display:flex;align-items:center;gap:10px;padding:0 22px;margin-bottom:36px;}
    .logo-icon{width:40px;height:40px;border-radius:50%;flex-shrink:0;background:conic-gradient(#f5c842 0deg 90deg,#00c9a7 90deg 180deg,#ff7c3e 180deg 270deg,#7c6af5 270deg 360deg);display:flex;align-items:center;justify-content:center;position:relative;}
    .logo-icon::before{content:'';position:absolute;width:26px;height:26px;border-radius:50%;background:var(--sidebar-bg);}
    .logo-icon span{position:relative;z-index:1;font-size:.8rem;font-weight:800;color:#fff;}
    .sidebar-logo h2{font-size:1.3rem;font-weight:800;color:#fff;}
    .sidebar-section-label{font-size:.68rem;font-weight:600;color:var(--sidebar-label);letter-spacing:.1em;text-transform:uppercase;padding:0 22px;margin-bottom:8px;margin-top:10px;}
    .nav-item{display:flex;align-items:center;gap:11px;padding:11px 22px;cursor:pointer;color:var(--sidebar-text);font-size:.92rem;font-weight:500;text-decoration:none;transition:background .15s,color .15s;}
    .nav-item:hover{background:rgba(255,255,255,.06);color:#fff;}
    .nav-item.active{background:var(--purple);color:#fff;font-weight:700;border-radius:10px;margin:0 10px;padding:11px 12px;}
    .nav-icon{font-size:1rem;width:20px;text-align:center;}
    .sidebar-spacer{flex:1;}
    .sidebar-user{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:14px 22px;border-top:1px solid rgba(255,255,255,.08);}
    .sidebar-user-info{display:flex;flex-direction:column;gap:4px;min-width:0;}
    .user-name a{color:#fff;text-decoration:none;}
    .btn-logout{background:var(--yellow);color:#7a5c00;border:none;border-radius:9px;padding:9px 12px;font-family:'Sora',sans-serif;font-weight:700;font-size:.78rem;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;}
    .btn-logout:hover{opacity:.9;}
    .user-ava{width:36px;height:36px;border-radius:50%;background:var(--purple);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.78rem;font-weight:700;flex-shrink:0;}
    .user-name{font-size:.85rem;font-weight:600;color:#fff;}
    .user-plan{font-size:.72rem;color:var(--sidebar-label);}
    .main{margin-left:200px;flex:1;padding:30px 28px;}

    /* TOPBAR */
    .topbar{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;}
    .topbar h1{font-size:1.4rem;font-weight:800;}
    .topbar p{font-size:.82rem;color:var(--text-muted);margin-top:3px;}
    .btn-mark-read{background:var(--white);border:1.5px solid #e2e3ee;border-radius:10px;padding:9px 16px;font-family:'DM Sans',sans-serif;font-size:.83rem;font-weight:600;color:var(--purple);cursor:pointer;display:flex;align-items:center;gap:7px;box-shadow:var(--shadow);transition:background .15s;}
    .btn-mark-read:hover{background:#f5f6fc;}

    /* FILTER TABS */
    .filter-tabs{display:flex;gap:8px;margin-bottom:22px;}
    .ftab{padding:7px 16px;border-radius:50px;border:none;font-family:'DM Sans',sans-serif;font-size:.85rem;font-weight:600;cursor:pointer;transition:all .15s;display:flex;align-items:center;gap:6px;}
    .ftab .badge{width:20px;height:20px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;}
    .ftab.active{background:var(--purple);color:#fff;}
    .ftab.active .badge{background:rgba(255,255,255,.25);color:#fff;}
    .ftab:not(.active){background:var(--white);color:var(--text-mid);border:1.5px solid #e2e3ee;}
    .ftab:not(.active) .badge{background:#f0edff;color:var(--purple);}
    .ftab:not(.active):hover{border-color:var(--purple);color:var(--purple);}

    /* ALERT CARDS */
    .alerts-list{display:flex;flex-direction:column;gap:14px;}
    .alert-card{background:var(--white);border-radius:var(--card-radius);padding:20px 22px;box-shadow:var(--shadow);border-left:4px solid transparent;position:relative;transition:box-shadow .2s;}
    .alert-card:hover{box-shadow:0 6px 24px rgba(0,0,0,.1);}
    .alert-card.type-red{border-left-color:var(--red);}
    .alert-card.type-teal{border-left-color:var(--teal);}
    .alert-card.type-yellow{border-left-color:var(--yellow);}
    .alert-title{font-size:.98rem;font-weight:700;margin-bottom:6px;}
    .alert-title.red{color:var(--red);}
    .alert-title.teal{color:var(--teal);}
    .alert-title.yellow{color:#b8900a;}
    .alert-body{font-size:.85rem;color:var(--text-mid);line-height:1.55;margin-bottom:10px;}
    .alert-meta{font-size:.75rem;color:var(--text-muted);text-align:right;}
    .unread-dot{position:absolute;top:18px;right:18px;width:9px;height:9px;border-radius:50%;background:var(--purple);}
  </style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-logo"><div class="logo-icon"><span>$</span></div><h2>Finzo</h2></div>
  <div class="sidebar-section-label">Main</div>
  <a class="nav-item" href="<?= htmlspecialchars(userPageUrl('dashboard'), ENT_QUOTES, 'UTF-8') ?>"><span class="nav-icon">🏠</span> Dashboard</a>
  <a class="nav-item" href="<?= htmlspecialchars(userPageUrl('transaction'), ENT_QUOTES, 'UTF-8') ?>"><span class="nav-icon">💳</span> Transactions</a>
  <a class="nav-item" href="<?= htmlspecialchars(userPageUrl('budget'), ENT_QUOTES, 'UTF-8') ?>"><span class="nav-icon">🎯</span> Budgets</a>
  <a class="nav-item" href="<?= htmlspecialchars(userPageUrl('category'), ENT_QUOTES, 'UTF-8') ?>"><span class="nav-icon">🗂️</span> Categories</a>
  <div class="sidebar-section-label" style="margin-top:18px;">Collaboration</div>
  <a class="nav-item" href="<?= htmlspecialchars(userPageUrl('group'), ENT_QUOTES, 'UTF-8') ?>"><span class="nav-icon">👥</span> My Groups</a>
  <a class="nav-item active" href="<?= htmlspecialchars(userPageUrl('alert'), ENT_QUOTES, 'UTF-8') ?>"><span class="nav-icon">🔔</span> Alerts</a>
  <div class="sidebar-spacer"></div>
  <div class="sidebar-user">
    <div class="user-ava"><?= htmlspecialchars($displayInitials, ENT_QUOTES, 'UTF-8') ?></div>
    <div class="sidebar-user-info">
      <div class="user-name"><a href="<?= htmlspecialchars(userPageUrl('profile'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?></a></div>
      <div class="user-plan"><?= htmlspecialchars(($currentUser['role'] ?? 'user') . ' plan', ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <a class="btn-logout" href="<?= htmlspecialchars(userPageUrl('logout'), ENT_QUOTES, 'UTF-8') ?>">Logout</a>
  </div>
</aside>

<main class="main">
  <div class="topbar">
    <div>
      <h1>Alerts</h1>
      <p><?= $unreadCount ?> unread notifications</p>
    </div>
    <form method="post" action="<?= htmlspecialchars(userPageUrl('alert'), ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="crud_action" value="markAllRead"/>
      <button class="btn-mark-read" type="submit">✓ Make all as read</button>
    </form>
  </div>

  <div class="filter-tabs">
    <button class="ftab active" onclick="filterAlerts('all',this)">All <span class="badge"><?= count($alerts) ?></span></button>
    <button class="ftab" onclick="filterAlerts('unread',this)">Unread <span class="badge"><?= $unreadCount ?></span></button>
    <button class="ftab" onclick="filterAlerts('transactions',this)">Transactions <span class="badge"><?= count(array_filter($alerts, fn($alert) => ($alert['about'] ?? '') === 'transaction')) ?></span></button>
    <button class="ftab" onclick="filterAlerts('system',this)">System <span class="badge"><?= count(array_filter($alerts, fn($alert) => ($alert['about'] ?? '') === 'system')) ?></span></button>
  </div>

  <div class="alerts-list" id="alertsList">
    <?php if (empty($alerts)): ?>
      <div class="alert-card type-yellow" data-type="system">
        <div class="alert-title yellow">No alerts yet</div>
        <div class="alert-body">When your budgets or transactions trigger notifications, they will appear here.</div>
        <div class="alert-meta">Now</div>
      </div>
    <?php else: ?>
      <?php foreach ($alerts as $alert): ?>
        <?php
          $typeClass = ($alert['about'] ?? '') === 'system' ? 'type-yellow' : 'type-red';
          $titleClass = ($alert['about'] ?? '') === 'system' ? 'yellow' : 'red';
          $isUnread = (int) ($alert['isReaded'] ?? 0) === 0;
          $typeFilter = trim(($isUnread ? 'unread ' : '') . ($alert['about'] ?? 'system'));
          $body = $alert['message'] ?? '';
          $title = $alert['name'] ?? 'Alert';
          $createdAt = !empty($alert['dateSend']) ? date('M d, h:i A', strtotime($alert['dateSend'])) : 'Now';
        ?>
        <div class="alert-card <?= $typeClass ?>" data-type="<?= htmlspecialchars($typeFilter, ENT_QUOTES, 'UTF-8') ?>">
          <?php if ($isUnread): ?><div class="unread-dot"></div><?php endif; ?>
          <div class="alert-title <?= $titleClass ?>"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></div>
          <div class="alert-body"><?= htmlspecialchars($body, ENT_QUOTES, 'UTF-8') ?></div>
          <div class="alert-meta"><?= htmlspecialchars($createdAt, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</main>

<script>
  function filterAlerts(type, btn) {
    document.querySelectorAll('.ftab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.alert-card').forEach(card => {
      if (type === 'all') { card.style.display = ''; return; }
      card.style.display = card.dataset.type.includes(type) ? '' : 'none';
    });
  }
</script>
</body>
</html>