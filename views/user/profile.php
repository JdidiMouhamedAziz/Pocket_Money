<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/shared.php';
require_once __DIR__ . '/../../models/User.php';

$currentUserId = $currentUser['id'] ?? ($_SESSION['user']['id'] ?? null);
$profileData = userProfilePageData($currentUserId);
$profile = $profileData['profile'] ?? [];
$summary = $profileData['summary'] ?? ['transactions' => 0];
$displayName = trim(($profile['name'] ?? $currentUser['name'] ?? 'User') . ' ' . ($profile['lastName'] ?? $currentUser['lastName'] ?? ''));
$displayInitials = $userInitials ?: 'U';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crud_action'])) {
  $userModel = new User($pdo);

  if ($_POST['crud_action'] === 'updateProfile') {
    $name = trim($_POST['name'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($name && $lastName && $email) {
      $userModel->updateUser($currentUserId, $name, $lastName, $email, $password !== '' ? $password : null);
      $_SESSION['user'] = $userModel->findUserById($currentUserId) ?: $_SESSION['user'];
    }

    header('Location: ' . userPageUrl('profile'));
    exit;
  }

  if ($_POST['crud_action'] === 'deleteAccount') {
    $userModel->softDeleteUser($currentUserId);
    session_destroy();
    header('Location: /pocket_money/views/login.php');
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Finzo – My Profile</title>
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
    .topbar{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:24px;}
    .topbar h1{font-size:1.4rem;font-weight:800;}
    .topbar p{font-size:.85rem;color:var(--text-muted);margin-top:3px;}
    .btn-save{background:var(--purple);color:#fff;border:none;border-radius:10px;padding:10px 20px;font-family:'Sora',sans-serif;font-weight:700;font-size:.85rem;cursor:pointer;display:flex;align-items:center;gap:7px;box-shadow:0 4px 14px rgba(124,106,245,.35);transition:opacity .2s;}
    .btn-save:hover{opacity:.9;}

    /* PROFILE LAYOUT */
    .profile-layout{display:grid;grid-template-columns:240px 1fr;gap:20px;margin-bottom:20px;}

    /* LEFT CARD */
    .profile-card{background:#2d2d3a;border-radius:var(--card-radius);padding:28px 20px;display:flex;flex-direction:column;align-items:center;text-align:center;box-shadow:var(--shadow);}
    .profile-ava-wrap{position:relative;margin-bottom:16px;}
    .profile-ava{width:90px;height:90px;border-radius:50%;background:var(--purple);display:flex;align-items:center;justify-content:center;font-family:'Sora',sans-serif;font-size:2rem;font-weight:800;color:#fff;}
    .profile-name{font-family:'Sora',sans-serif;font-size:1.1rem;font-weight:700;color:#fff;margin-bottom:4px;}
    .profile-email{font-size:.8rem;color:var(--sidebar-text);margin-bottom:16px;}
    .status-pill{background:rgba(0,201,167,.15);border:1px solid var(--teal);color:var(--teal);border-radius:50px;padding:5px 14px;font-size:.78rem;font-weight:600;display:flex;align-items:center;gap:6px;margin-bottom:24px;}
    .status-dot{width:7px;height:7px;border-radius:50%;background:var(--teal);}
    .stat-pills{display:flex;flex-direction:column;gap:8px;width:100%;}
    .stat-pill{background:var(--purple);border-radius:10px;padding:9px 14px;display:flex;align-items:center;gap:10px;text-align:left;}
    .stat-pill.teal{background:var(--teal);}
    .stat-pill.orange{background:#ff7c3e;}
    .sp-num{font-family:'Sora',sans-serif;font-size:.95rem;font-weight:800;color:#fff;min-width:24px;}
    .sp-label{font-size:.82rem;color:rgba(255,255,255,.85);font-weight:500;}

    /* RIGHT: PERSONAL INFO */
    .info-card{background:var(--white);border-radius:var(--card-radius);padding:26px 28px;box-shadow:var(--shadow);}
    .info-header{display:flex;align-items:center;gap:10px;margin-bottom:22px;}
    .info-header h3{font-size:1rem;font-weight:700;}
    .info-icon{width:36px;height:36px;border-radius:10px;background:var(--purple-light);display:flex;align-items:center;justify-content:center;font-size:1rem;}
    .field-row{display:flex;align-items:center;gap:16px;padding:12px 0;border-bottom:1px solid #f2f3f8;}
    .field-row:last-child{border-bottom:none;}
    .field-icon{width:34px;height:34px;border-radius:9px;background:#f5f6fc;border:1px solid #e2e3ee;display:flex;align-items:center;justify-content:center;font-size:.95rem;flex-shrink:0;}
    .field-label{font-size:.78rem;color:var(--text-muted);min-width:70px;}
    .field-input{flex:1;background:#f5f6fc;border:1px solid #e2e3ee;border-radius:8px;padding:9px 13px;font-family:'DM Sans',sans-serif;font-size:.9rem;color:var(--text-dark);outline:none;transition:border-color .2s;}
    .field-input:focus{border-color:var(--purple);}

    /* DANGER ZONE */
    .danger-card{background:#fff5f5;border:1.5px solid #ffd0d0;border-radius:var(--card-radius);padding:22px 26px;box-shadow:var(--shadow);}
    .danger-title{display:flex;align-items:center;gap:8px;font-family:'Sora',sans-serif;font-size:1.2rem;font-weight:800;color:var(--red);margin-bottom:20px;}
    .danger-row{display:flex;align-items:center;justify-content:space-between;padding:14px 0;border-bottom:1px solid #ffe0e0;}
    .danger-row:last-child{border-bottom:none;}
    .danger-row-left h4{font-size:.95rem;font-weight:700;color:var(--text-dark);margin-bottom:3px;}
    .danger-row-left p{font-size:.78rem;color:var(--text-muted);}
    .btn-logout{background:var(--yellow);color:#7a5c00;border:none;border-radius:9px;padding:9px 22px;font-family:'Sora',sans-serif;font-weight:700;font-size:.85rem;cursor:pointer;transition:opacity .2s;}
    .btn-logout:hover{opacity:.85;}
    .btn-delete{background:var(--red);color:#fff;border:none;border-radius:9px;padding:9px 22px;font-family:'Sora',sans-serif;font-weight:700;font-size:.85rem;cursor:pointer;transition:opacity .2s;}
    .btn-delete:hover{opacity:.85;}
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
  <a class="nav-item" href="<?= htmlspecialchars(userPageUrl('alert'), ENT_QUOTES, 'UTF-8') ?>"><span class="nav-icon">🔔</span> Alerts</a>
  <div class="sidebar-spacer"></div>
  <div class="sidebar-user">
    <div class="user-ava"><?= htmlspecialchars($displayInitials, ENT_QUOTES, 'UTF-8') ?></div>
    <div class="sidebar-user-info">
      <div class="user-name"><a href="<?= htmlspecialchars(userPageUrl('profile'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($displayName ?: 'User', ENT_QUOTES, 'UTF-8') ?></a></div>
      <div class="user-plan"><?= htmlspecialchars(($profile['role'] ?? $currentUser['role'] ?? 'user') . ' plan', ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <a class="btn-logout" href="<?= htmlspecialchars(userPageUrl('logout'), ENT_QUOTES, 'UTF-8') ?>">Logout</a>
  </div>
</aside>

<main class="main">
  <div class="topbar">
    <div><h1>My Profile</h1><p>Manage your account and preferences</p></div>
    <button class="btn-save" type="submit" form="profileDetailsForm">✓ Save Changes</button>
  </div>

  <div class="profile-layout">
    <!-- Left card -->
    <div class="profile-card">
      <div class="profile-ava-wrap">
        <div class="profile-ava"><?= htmlspecialchars($displayInitials, ENT_QUOTES, 'UTF-8') ?></div>
      </div>
      <div class="profile-name"><?= htmlspecialchars($displayName ?: 'User', ENT_QUOTES, 'UTF-8') ?></div>
      <div class="profile-email"><?= htmlspecialchars($profile['email'] ?? $currentUser['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
      <div class="status-pill"><span class="status-dot"></span><?= htmlspecialchars(ucfirst($profile['status'] ?? $currentUser['status'] ?? 'active'), ENT_QUOTES, 'UTF-8') ?></div>
      <div class="stat-pills">
        <div class="stat-pill"><span class="sp-num"><?= (int) ($profile['transactionCount'] ?? 0) ?></span><span class="sp-label">Transactions</span></div>
        <div class="stat-pill teal"><span class="sp-num"><?= (int) ($profile['budgetCount'] ?? 0) ?></span><span class="sp-label">Budgets</span></div>
        <div class="stat-pill orange"><span class="sp-num"><?= (int) ($profile['groupCount'] ?? 0) ?></span><span class="sp-label">Groups</span></div>
      </div>
    </div>

    <!-- Right: Personal Info -->
    <form class="info-card" method="post" action="<?= htmlspecialchars(userPageUrl('profile'), ENT_QUOTES, 'UTF-8') ?>" id="profileDetailsForm">
      <input type="hidden" name="crud_action" value="updateProfile"/>
      <div class="info-header">
        <div class="info-icon">👤</div>
        <h3>Personal Information</h3>
      </div>
      <div class="field-row">
        <div class="field-icon">👤</div>
        <span class="field-label">Full name</span>
        <input class="field-input" type="text" name="name" value="<?= htmlspecialchars($profile['name'] ?? $currentUser['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
      </div>
      <div class="field-row">
        <div class="field-icon">✉️</div>
        <span class="field-label">Email</span>
        <input class="field-input" type="email" name="email" value="<?= htmlspecialchars($profile['email'] ?? $currentUser['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
      </div>
      <div class="field-row">
        <div class="field-icon">📞</div>
        <span class="field-label">Phone</span>
        <input class="field-input" type="text" value="<?= htmlspecialchars($profile['phone'] ?? '+216 ** *** ***', ENT_QUOTES, 'UTF-8') ?>" readonly/>
      </div>
      <div class="field-row">
        <div class="field-icon">🔒</div>
        <span class="field-label">Password</span>
        <input class="field-input" type="password" name="password" value="" placeholder="Enter new password"/>
      </div>
      <input type="hidden" name="lastName" value="<?= htmlspecialchars($profile['lastName'] ?? $currentUser['lastName'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
    </form>
  </div>

  <!-- Danger Zone -->
  <div class="danger-card">
    <div class="danger-title">⚠️ Danger Zone</div>
    <div class="danger-row">
      <div class="danger-row-left">
        <h4>Log out</h4>
        <p>Sign out of your account on this device</p>
      </div>
      <button class="btn-logout" type="button" onclick="window.location.href='<?= htmlspecialchars('/pocket_money/views/logout.php', ENT_QUOTES, 'UTF-8') ?>'">Log out</button>
    </div>
    <div class="danger-row">
      <div class="danger-row-left">
        <h4>Deactivate account</h4>
        <p>Mark your account as deleted while keeping data in the system</p>
      </div>
      <form method="post" action="<?= htmlspecialchars(userPageUrl('profile'), ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Are you sure you want to deactivate your account?');">
        <input type="hidden" name="crud_action" value="deleteAccount"/>
        <button class="btn-delete" type="submit">Deactivate</button>
      </form>
    </div>
  </div>
</main>
</body>
</html>