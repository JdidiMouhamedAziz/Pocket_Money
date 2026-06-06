<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/admin_helpers.php';
require_once __DIR__ . '/../../config/database.php';

$adminFlash = getAdminFlash();
$currentUserId = $_SESSION['user']['id'] ?? null;
$currentUser = null;
if ($currentUserId) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$currentUserId]);
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
}
$currentUser = $currentUser ?: ($_SESSION['user'] ?? []);
$fullName = trim(($currentUser['name'] ?? '') . ' ' . ($currentUser['lastName'] ?? '')) ?: 'Admin User';
$email = $currentUser['email'] ?? '';
$role = ucfirst($currentUser['role'] ?? 'Admin');
$status = ucfirst($currentUser['status'] ?? 'active');
$createdAt = $currentUser['createdAt'] ?? '';
$updatedAt = $currentUser['updatedAt'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>BudgetPro – Account Settings</title>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet"/>
  <style>
    :root{--bg:#f7f8fc;--white:#fff;--accent:#4f46e5;--accent-soft:#eef0ff;--teal:#059669;--red:#ef4444;--red-soft:#fee2e2;--text-dark:#111827;--text-mid:#374151;--text-muted:#6b7280;--text-light:#9ca3af;--border:#e5e7eb;--radius:12px;--shadow:0 1px 4px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.04);}
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
    .main{margin-left:200px;flex:1;display:flex;flex-direction:column;}
    /* CONTENT */
    .content{padding:22px 24px;display:grid;grid-template-columns:200px 1fr;gap:24px;}
    .page-title-area{grid-column:1/-1;margin-bottom:0;}
    .page-title-area h1{font-size:1.4rem;font-weight:800;}
    .page-title-area p{font-size:.82rem;color:var(--text-muted);margin-top:4px;}
    /* SETTINGS NAV */
    .settings-nav{display:flex;flex-direction:column;gap:3px;padding-top:4px;}
    .snav-item{display:flex;align-items:center;gap:9px;padding:9px 12px;border-radius:9px;cursor:pointer;color:var(--text-muted);font-size:.85rem;font-weight:500;transition:all .15s;}
    .snav-item:hover{background:#f3f4f6;color:var(--text-dark);}
    .snav-item.active{background:var(--accent-soft);color:var(--accent);font-weight:700;}
    .snav-icon{font-size:.9rem;width:17px;text-align:center;}
    /* SETTINGS PANELS */
    .settings-panel{display:flex;flex-direction:column;gap:16px;}
    /* SECTION CARD */
    .section-card{background:var(--white);border-radius:var(--radius);padding:24px;box-shadow:var(--shadow);}
    .section-card h3{font-size:1rem;font-weight:700;margin-bottom:18px;padding-bottom:12px;border-bottom:1px solid var(--border);}
    /* PROFILE PHOTO */
    .photo-row{display:flex;align-items:center;gap:18px;margin-bottom:20px;}
    .admin-flash{background:#eef6ff;border:1px solid #dbeafe;color:#1e40af;border-radius:12px;padding:14px 18px;margin-bottom:18px;font-weight:600;}
    .admin-flash.error{background:#fef2f2;border-color:#fecaca;color:#b91c1c;}
    .photo-circle{width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#e5e7eb,#d1d5db);display:flex;align-items:center;justify-content:center;font-size:1.4rem;position:relative;border:3px solid var(--border);}
    .photo-edit{position:absolute;bottom:0;right:0;width:22px;height:22px;border-radius:50%;background:var(--accent);border:2px solid #fff;display:flex;align-items:center;justify-content:center;font-size:.6rem;cursor:pointer;}
    .photo-label{font-size:.88rem;font-weight:700;color:var(--text-dark);margin-bottom:4px;}
    .photo-sub{font-size:.75rem;color:var(--text-muted);margin-bottom:10px;}
    /* FORM */
    .form-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;}
    .form-group{display:flex;flex-direction:column;gap:5px;}
    .form-group label{font-size:.75rem;font-weight:600;color:var(--text-mid);}
    .form-input{background:#f9fafb;border:1px solid var(--border);border-radius:8px;padding:9px 12px;font-family:'DM Sans',sans-serif;font-size:.88rem;color:var(--text-dark);outline:none;transition:border-color .2s;width:100%;}
    .form-input:focus{border-color:var(--accent);background:#fff;}
    .form-group.full{grid-column:1/-1;}
    .btn-save{background:var(--accent);color:#fff;border:none;border-radius:9px;padding:9px 20px;font-family:'Sora',sans-serif;font-weight:700;font-size:.83rem;cursor:pointer;float:right;box-shadow:0 4px 12px rgba(79,70,229,.3);transition:opacity .2s;}
    .btn-save:hover{opacity:.9;}
    .clearfix::after{content:'';display:table;clear:both;}
    /* SECURITY */
    .pw-hint{display:flex;align-items:flex-start;gap:8px;background:#eff6ff;border:1px solid #dbeafe;border-radius:8px;padding:10px 12px;margin:14px 0;}
    .pw-hint p{font-size:.75rem;color:#1e40af;line-height:1.5;}
    .btn-update-pw{background:var(--accent);color:#fff;border:none;border-radius:9px;padding:9px 20px;font-family:'Sora',sans-serif;font-weight:700;font-size:.83rem;cursor:pointer;float:right;}
    /* DANGER */
    .danger-card{background:#fff5f5;border:1.5px solid #fecaca;border-radius:var(--radius);padding:18px 22px;display:flex;align-items:center;justify-content:space-between;}
    .danger-card h3{font-size:.95rem;font-weight:700;color:var(--red);margin-bottom:4px;}
    .danger-card p{font-size:.78rem;color:var(--text-muted);}
    .btn-delete-forever{background:var(--red);color:#fff;border:none;border-radius:9px;padding:9px 20px;font-family:'Sora',sans-serif;font-weight:700;font-size:.83rem;cursor:pointer;white-space:nowrap;transition:opacity .2s;}
    .btn-delete-forever:hover{opacity:.85;}
  </style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-header"><div class="logo-row"><div class="logo-mark">B</div><div class="logo-text"><h2>BudgetPro</h2><p>Collaborative Finance</p></div></div></div>
  <nav class="sidebar-nav">
    <a class="nav-item" href="dashboard.php"><span class="nav-icon">📊</span> Dashboard</a>
    <a class="nav-item" href="users.php"><span class="nav-icon">👥</span> Users</a>
    <a class="nav-item" href="budgets.php"><span class="nav-icon">🎯</span> Budgets</a>
    <a class="nav-item" href="transactions.php"><span class="nav-icon">💳</span> Transactions</a>
    <a class="nav-item" href="categories.php"><span class="nav-icon">🗂️</span> Categories</a>
    <a class="nav-item" href="alerts.php"><span class="nav-icon">🔔</span> Alerts</a>
    <a class="nav-item" href="export_data.php"><span class="nav-icon">⬇️</span> Export Data</a>
    <a class="nav-item active" href="profile.php"><span class="nav-icon">⚙️</span> Settings</a>
  </nav>
  <div class="sidebar-spacer"></div>
  <div class="sidebar-footer"><a class="btn-logout" href="/pocket_money/views/logout.php">Logout</a></div>
</aside>
<div class="main">
  <div class="content">
    <div class="page-title-area">
      <h1>Account Settings</h1>
      <p>Manage your profile information.</p>
    </div>
    <!-- LEFT NAV -->
    <div class="settings-nav">
      <div class="snav-item active"><span class="snav-icon">👤</span> Profile</div>
    </div>
    <!-- RIGHT PANELS -->
    <div class="settings-panel">
      <?php if ($adminFlash): ?>
        <div class="admin-flash <?= $adminFlash['success'] ? '' : 'error' ?>"><?= htmlspecialchars($adminFlash['message'], ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>
      <!-- PROFILE INFO -->
      <form method="post" action="admin_actions.php?resource=user&action=update">
        <input type="hidden" name="id" value="<?= htmlspecialchars($currentUserId, ENT_QUOTES, 'UTF-8') ?>"/>
        <div class="section-card" id="tab-profile">
          <h3>Profile Information</h3>
          <div class="photo-row">
            <div class="photo-circle">👤<div class="photo-edit">✎</div></div>
            <div>
              <div class="photo-label">Profile Photo</div>
              <div class="photo-sub">Update your avatar. Recommended size is 256×256px.</div>
              <div class="photo-note">Update the fields below to keep your account information current.</div>
            </div>
          </div>
          <div class="form-grid-2">
            <div class="form-group">
              <label>First Name</label>
              <input class="form-input" name="name" type="text" value="<?= htmlspecialchars($currentUser['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required/>
            </div>
            <div class="form-group">
              <label>Last Name</label>
              <input class="form-input" name="lastName" type="text" value="<?= htmlspecialchars($currentUser['lastName'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required/>
            </div>
            <div class="form-group">
              <label>Email Address</label>
              <input class="form-input" name="email" type="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" required/>
            </div>
            <div class="form-group full">
              <label>Role</label>
              <input class="form-input" type="text" value="<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>" readonly/>
            </div>
            <div class="form-group full">
              <label>New Password</label>
              <input class="form-input" name="password" type="password" placeholder="Leave blank to keep current password"/>
            </div>
          </div>
          <div class="clearfix"><button class="btn-save" type="submit">Save Changes</button></div>
        </div>
      </form>
      <!-- DANGER ZONE -->
      <div class="danger-card">
        <div>
          <h3>Account Status</h3>
          <p>Role: <?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?> · Status: <?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></p>
          <p>Member since <?= htmlspecialchars(formatDate($createdAt), ENT_QUOTES, 'UTF-8') ?>, updated <?= htmlspecialchars(formatDate($updatedAt), ENT_QUOTES, 'UTF-8') ?>.</p>
        </div>
        <form method="post" action="admin_actions.php?resource=user&action=status" style="margin:0;">
          <input type="hidden" name="id" value="<?= htmlspecialchars($currentUserId, ENT_QUOTES, 'UTF-8') ?>"/>
          <input type="hidden" name="status" value="deleted"/>
          <button class="btn-delete-forever" type="submit">Deactivate Account</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
  function showTab(tab,btn){
    document.querySelectorAll('.snav-item').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
  }
</script>
</body>
</html>