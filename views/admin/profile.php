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
    .btn-new-report{width:100%;background:var(--accent);color:#fff;border:none;border-radius:8px;padding:9px;font-family:'Sora',sans-serif;font-weight:700;font-size:.8rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:5px;}
    .main{margin-left:200px;flex:1;display:flex;flex-direction:column;}
    .topnav{background:var(--white);border-bottom:1px solid var(--border);padding:11px 24px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50;}
    .topnav-search{display:flex;align-items:center;gap:7px;background:#f3f4f6;border:1px solid var(--border);border-radius:8px;padding:7px 12px;min-width:220px;}
    .topnav-search input{background:transparent;border:none;outline:none;font-family:'DM Sans',sans-serif;font-size:.83rem;color:var(--text-mid);width:100%;}
    .topnav-search input::placeholder{color:var(--text-light);}
    .topnav-right{display:flex;align-items:center;gap:10px;}
    .notif-btn{width:32px;height:32px;border-radius:50%;background:#f3f4f6;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;cursor:pointer;position:relative;}
    .notif-dot{position:absolute;top:5px;right:5px;width:7px;height:7px;border-radius:50%;background:var(--red);border:2px solid #fff;}
    .profile-btn{display:flex;align-items:center;gap:7px;cursor:pointer;padding:5px 10px 5px 5px;background:#f3f4f6;border:1px solid var(--border);border-radius:50px;}
    .profile-ava{width:24px;height:24px;border-radius:50%;background:linear-gradient(135deg,var(--accent),#818cf8);display:flex;align-items:center;justify-content:center;font-size:.62rem;font-weight:700;color:#fff;}
    .profile-btn span{font-size:.8rem;font-weight:600;color:var(--text-mid);}
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
    .photo-circle{width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#e5e7eb,#d1d5db);display:flex;align-items:center;justify-content:center;font-size:1.4rem;position:relative;border:3px solid var(--border);}
    .photo-edit{position:absolute;bottom:0;right:0;width:22px;height:22px;border-radius:50%;background:var(--accent);border:2px solid #fff;display:flex;align-items:center;justify-content:center;font-size:.6rem;cursor:pointer;}
    .photo-label{font-size:.88rem;font-weight:700;color:var(--text-dark);margin-bottom:4px;}
    .photo-sub{font-size:.75rem;color:var(--text-muted);margin-bottom:10px;}
    .photo-btns{display:flex;gap:8px;}
    .btn-upload{background:var(--accent);color:#fff;border:none;border-radius:7px;padding:7px 14px;font-family:'DM Sans',sans-serif;font-size:.78rem;font-weight:700;cursor:pointer;}
    .btn-remove{background:var(--white);border:1px solid var(--border);border-radius:7px;padding:7px 14px;font-family:'DM Sans',sans-serif;font-size:.78rem;font-weight:600;color:var(--text-mid);cursor:pointer;}
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
    <a class="nav-item" href="#"><span class="nav-icon">📊</span> Dashboard</a>
    <a class="nav-item" href="#"><span class="nav-icon">👥</span> Users</a>
    <a class="nav-item" href="#"><span class="nav-icon">🎯</span> Budgets</a>
    <a class="nav-item" href="#"><span class="nav-icon">💳</span> Transactions</a>
    <a class="nav-item" href="#"><span class="nav-icon">🗂️</span> Categories</a>
    <a class="nav-item" href="#"><span class="nav-icon">🔔</span> Alerts</a>
    <a class="nav-item active" href="#"><span class="nav-icon">⚙️</span> Settings</a>
  </nav>
  <div class="sidebar-spacer"></div>
  <div class="sidebar-footer"><button class="btn-new-report">＋ New Report</button></div>
</aside>
<div class="main">
  <div class="topnav">
    <div class="topnav-search"><span style="color:#9ca3af">🔍</span><input type="text" placeholder="Search settings..."/></div>
    <div class="topnav-right">
      <div class="notif-btn">🔔<div class="notif-dot"></div></div>
      <div class="profile-btn"><div class="profile-ava">AR</div><span>Alex Rivera</span><span style="color:var(--text-light);font-size:.7rem">▾</span><span style="font-size:.72rem;color:var(--text-muted);margin-left:2px;">Profile Settings</span></div>
    </div>
  </div>
  <div class="content">
    <div class="page-title-area">
      <h1>Account Settings</h1>
      <p>Manage your personal information, security preferences, and global notifications.</p>
    </div>
    <!-- LEFT NAV -->
    <div class="settings-nav">
      <div class="snav-item active" onclick="showTab('profile',this)"><span class="snav-icon">👤</span> Profile</div>
      <div class="snav-item" onclick="showTab('security',this)"><span class="snav-icon">🔒</span> Security</div>
      <div class="snav-item" onclick="showTab('billing',this)"><span class="snav-icon">💳</span> Billing</div>
      <div class="snav-item" onclick="showTab('integrations',this)"><span class="snav-icon">🔗</span> Integrations</div>
    </div>
    <!-- RIGHT PANELS -->
    <div class="settings-panel">
      <!-- PROFILE INFO -->
      <div class="section-card" id="tab-profile">
        <h3>Profile Information</h3>
        <div class="photo-row">
          <div class="photo-circle">👤<div class="photo-edit">✎</div></div>
          <div>
            <div class="photo-label">Profile Photo</div>
            <div class="photo-sub">Update your avatar. Recommended size is 256×256px.</div>
            <div class="photo-btns">
              <button class="btn-upload">Upload New</button>
              <button class="btn-remove">Remove</button>
            </div>
          </div>
        </div>
        <div class="form-grid-2">
          <div class="form-group">
            <label>Full Name</label>
            <input class="form-input" type="text" value="Alex Rivera"/>
          </div>
          <div class="form-group">
            <label>Email Address</label>
            <input class="form-input" type="email" value="alex.rivera@budgetpro.io"/>
          </div>
          <div class="form-group full">
            <label>Job Title</label>
            <input class="form-input" type="text" value="Senior Financial Controller"/>
          </div>
        </div>
        <div class="clearfix"><button class="btn-save">Save Changes</button></div>
      </div>
      <!-- SECURITY -->
      <div class="section-card" id="tab-security">
        <h3>Security</h3>
        <div class="form-grid-2">
          <div class="form-group">
            <label>Current Password</label>
            <input class="form-input" type="password" value="password123"/>
          </div>
          <div class="form-group">
            <label>New Password</label>
            <input class="form-input" type="password" placeholder="••••••••••••"/>
          </div>
          <div class="form-group">
            <label>Confirm New Password</label>
            <input class="form-input" type="password" placeholder="••••••••••••"/>
          </div>
        </div>
        <div class="pw-hint"><span>ℹ️</span><p>Password must be at least 12 characters long and include a mix of uppercase, lowercase, numbers, and symbols.</p></div>
        <div class="clearfix"><button class="btn-update-pw btn-save">Update Password</button></div>
      </div>
      <!-- DANGER ZONE -->
      <div class="danger-card">
        <div>
          <h3>Delete Account</h3>
          <p>Once you delete your account, there is no going back. Please be certain.</p>
        </div>
        <button class="btn-delete-forever">Delete Forever</button>
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