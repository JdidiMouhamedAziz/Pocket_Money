<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>BudgetPro – Expense Categories</title>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet"/>
  <style>
    :root{--bg:#f7f8fc;--white:#fff;--accent:#4f46e5;--accent-soft:#eef0ff;--teal:#059669;--teal-soft:#d1fae5;--red:#ef4444;--red-soft:#fee2e2;--orange:#f59e0b;--blue:#3b82f6;--purple:#7c3aed;--yellow:#fbbf24;--text-dark:#111827;--text-mid:#374151;--text-muted:#6b7280;--text-light:#9ca3af;--border:#e5e7eb;--radius:12px;--shadow:0 1px 4px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.04);}
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
    .topnav-search{display:flex;align-items:center;gap:7px;background:#f3f4f6;border:1px solid var(--border);border-radius:8px;padding:7px 12px;min-width:240px;}
    .topnav-search input{background:transparent;border:none;outline:none;font-family:'DM Sans',sans-serif;font-size:.83rem;color:var(--text-mid);width:100%;}
    .topnav-search input::placeholder{color:var(--text-light);}
    .topnav-right{display:flex;align-items:center;gap:10px;}
    .notif-btn{width:32px;height:32px;border-radius:50%;background:#f3f4f6;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;cursor:pointer;position:relative;}
    .notif-dot{position:absolute;top:5px;right:5px;width:7px;height:7px;border-radius:50%;background:var(--red);border:2px solid #fff;}
    .profile-btn{display:flex;align-items:center;gap:7px;cursor:pointer;padding:5px 10px 5px 5px;background:#f3f4f6;border:1px solid var(--border);border-radius:50px;}
    .profile-ava{width:24px;height:24px;border-radius:50%;background:linear-gradient(135deg,var(--accent),#818cf8);display:flex;align-items:center;justify-content:center;font-size:.62rem;font-weight:700;color:#fff;}
    .profile-btn span{font-size:.8rem;font-weight:600;color:var(--text-mid);}
    .content{padding:22px 24px;}
    .page-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;}
    .page-header h1{font-size:1.4rem;font-weight:800;}
    .page-header p{font-size:.82rem;color:var(--text-muted);margin-top:4px;}
    .header-btns{display:flex;gap:8px;}
    .btn-export{display:flex;align-items:center;gap:5px;background:var(--white);border:1px solid var(--border);border-radius:8px;padding:8px 14px;font-family:'DM Sans',sans-serif;font-size:.8rem;font-weight:600;color:var(--text-mid);cursor:pointer;}
    .btn-export:hover{background:#f3f4f6;}
    .btn-add{background:var(--accent);color:#fff;border:none;border-radius:8px;padding:8px 16px;font-family:'Sora',sans-serif;font-weight:700;font-size:.8rem;cursor:pointer;display:flex;align-items:center;gap:5px;box-shadow:0 4px 12px rgba(79,70,229,.3);}
    /* STAT STRIP */
    .stat-strip{display:grid;grid-template-columns:repeat(3,1fr);gap:0;background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);margin-bottom:20px;overflow:hidden;}
    .ss-item{padding:16px 20px;border-right:1px solid var(--border);}
    .ss-item:last-child{border-right:none;}
    .ss-label{font-size:.68rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px;}
    .ss-val{font-family:'Sora',sans-serif;font-size:1.4rem;font-weight:800;color:var(--text-dark);}
    .ss-sub{font-size:.72rem;color:var(--text-muted);margin-top:3px;display:flex;align-items:center;gap:5px;}
    .ss-badge{font-size:.68rem;font-weight:700;padding:2px 7px;border-radius:50px;}
    .sb-red{background:var(--red-soft);color:var(--red);}
    .sb-green{background:var(--teal-soft);color:var(--teal);}
    .ss-item.most-used{background:#f9fafb;}
    .mu-value{font-family:'Sora',sans-serif;font-size:1.2rem;font-weight:800;color:var(--text-dark);}
    .mu-sub{font-size:.78rem;color:var(--text-muted);}
    /* CAT GRID */
    .cat-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;}
    .cat-card{background:var(--white);border-radius:var(--radius);padding:18px;box-shadow:var(--shadow);}
    .cat-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;}
    .cat-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;}
    .ci-blue{background:#dbeafe;}
    .ci-yellow{background:#fef3c7;}
    .ci-purple{background:#f5f3ff;}
    .ci-green{background:#d1fae5;}
    .ci-red{background:#fee2e2;}
    .cat-menu{background:transparent;border:none;cursor:pointer;color:var(--text-muted);font-size:1rem;}
    .cat-name{font-size:.92rem;font-weight:700;margin-bottom:3px;}
    .cat-desc{font-size:.72rem;color:var(--text-muted);margin-bottom:14px;}
    .cat-stats{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px;}
    .cs-item{background:#f9fafb;border-radius:8px;padding:8px 10px;}
    .cs-num{font-family:'Sora',sans-serif;font-size:.9rem;font-weight:800;color:var(--text-dark);}
    .cs-label{font-size:.68rem;color:var(--text-muted);margin-top:1px;}
    .spend-row{display:flex;justify-content:space-between;font-size:.75rem;margin-bottom:5px;}
    .spend-total{font-weight:700;color:var(--text-dark);}
    .prog-bg{background:#f3f4f6;border-radius:50px;height:5px;}
    .prog-fill{height:5px;border-radius:50px;}
    /* Add card */
    .add-cat-card{background:var(--white);border:2px dashed var(--border);border-radius:var(--radius);display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:220px;cursor:pointer;transition:all .2s;gap:8px;text-align:center;padding:20px;}
    .add-cat-card:hover{border-color:var(--accent);background:var(--accent-soft);}
    .add-plus{width:36px;height:36px;border-radius:10px;background:#f3f4f6;border:1.5px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:var(--text-muted);}
    .add-cat-card:hover .add-plus{background:var(--accent-soft);border-color:#c7d2fe;color:var(--accent);}
    .add-cat-card h4{font-size:.85rem;font-weight:700;color:var(--text-muted);}
    .add-cat-card:hover h4{color:var(--accent);}
    .add-cat-card p{font-size:.72rem;color:var(--text-light);}
  </style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-header">
    <div class="logo-row">
      <div class="logo-mark">B</div>
      <div class="logo-text"><h2>BudgetPro</h2><p>Collaborative Finance</p></div>
    </div>
  </div>
  <nav class="sidebar-nav">
    <a class="nav-item" href="#"><span class="nav-icon">📊</span> Dashboard</a>
    <a class="nav-item" href="#"><span class="nav-icon">👥</span> Users</a>
    <a class="nav-item" href="#"><span class="nav-icon">🎯</span> Budgets</a>
    <a class="nav-item" href="#"><span class="nav-icon">💳</span> Transactions</a>
    <a class="nav-item active" href="#"><span class="nav-icon">🗂️</span> Categories</a>
    <a class="nav-item" href="#"><span class="nav-icon">🔔</span> Alerts</a>
    <a class="nav-item" href="#"><span class="nav-icon">⚙️</span> Settings</a>
  </nav>
  <div class="sidebar-spacer"></div>
  <div class="sidebar-footer">
    <button class="btn-new-report">＋ New Report</button>
  </div>
</aside>
<div class="main">
  <div class="topnav">
    <div class="topnav-search"><span style="color:#9ca3af">🔍</span><input type="text" placeholder="Search categories..."/></div>
    <div class="topnav-right">
      <div class="notif-btn">🔔<div class="notif-dot"></div></div>
      <div class="profile-btn"><div class="profile-ava">AR</div><span>Alex Rivera</span><span style="color:var(--text-light);font-size:.7rem">▾</span></div>
    </div>
  </div>
  <div class="content">
    <div class="page-header">
      <div><h1>Expense Categories</h1><p>Organize and track your organizational spending habits.</p></div>
      <div class="header-btns">
        <button class="btn-export">↑ Export</button>
        <button class="btn-add">＋ Add Category</button>
      </div>
    </div>
    <!-- STAT STRIP -->
    <div class="stat-strip">
      <div class="ss-item">
        <div class="ss-label">Total Categories</div>
        <div class="ss-val">12</div>
        <div class="ss-sub"><span style="display:inline-flex;align-items:center;gap:3px;font-size:.75rem;color:var(--teal)">📈 Active tracking</span></div>
      </div>
      <div class="ss-item most-used">
        <div class="ss-label">Most Used</div>
        <div class="mu-value">Software <span style="font-size:.78rem;font-weight:500;color:var(--text-muted)">42 Transactions</span></div>
      </div>
      <div class="ss-item">
        <div class="ss-label">Total Month Spend</div>
        <div class="ss-val">$24,402</div>
        <div class="ss-sub"><span class="ss-badge sb-red">↑ -10%</span></div>
      </div>
    </div>
    <!-- CAT GRID -->
    <div class="cat-grid">
      <!-- Software -->
      <div class="cat-card">
        <div class="cat-top"><div class="cat-icon ci-blue">💻</div><button class="cat-menu">⋯</button></div>
        <div class="cat-name">Software</div>
        <div class="cat-desc">SaaS, Cloud & Subscriptions</div>
        <div class="cat-stats">
          <div class="cs-item"><div class="cs-num">08</div><div class="cs-label">Active</div></div>
          <div class="cs-item"><div class="cs-num cs-spend" style="color:var(--red)">$12,452</div><div class="cs-label">Spent Total</div></div>
        </div>
        <div class="prog-bg"><div class="prog-fill" style="width:78%;background:var(--blue)"></div></div>
      </div>
      <!-- Travel -->
      <div class="cat-card">
        <div class="cat-top"><div class="cat-icon ci-yellow">✈️</div><button class="cat-menu">⋯</button></div>
        <div class="cat-name">Travel</div>
        <div class="cat-desc">Business Trips & Transport</div>
        <div class="cat-stats">
          <div class="cs-item"><div class="cs-num">04</div><div class="cs-label">Active</div></div>
          <div class="cs-item"><div class="cs-num" style="color:var(--red)">$4,210</div><div class="cs-label">Spent Total</div></div>
        </div>
        <div class="prog-bg"><div class="prog-fill" style="width:55%;background:var(--yellow)"></div></div>
      </div>
      <!-- Rent -->
      <div class="cat-card">
        <div class="cat-top"><div class="cat-icon ci-purple">🏢</div><button class="cat-menu">⋯</button></div>
        <div class="cat-name">Rent</div>
        <div class="cat-desc">Office Leases & Utilities</div>
        <div class="cat-stats">
          <div class="cs-item"><div class="cs-num">01</div><div class="cs-label">Active</div></div>
          <div class="cs-item"><div class="cs-num" style="color:var(--red)">$9,500</div><div class="cs-label">Spent Total</div></div>
        </div>
        <div class="prog-bg"><div class="prog-fill" style="width:90%;background:var(--purple)"></div></div>
      </div>
      <!-- Meals -->
      <div class="cat-card">
        <div class="cat-top"><div class="cat-icon ci-green">🍽️</div><button class="cat-menu">⋯</button></div>
        <div class="cat-name">Meals</div>
        <div class="cat-desc">Client Entertaining & Snacks</div>
        <div class="cat-stats">
          <div class="cs-item"><div class="cs-num">12</div><div class="cs-label">Active</div></div>
          <div class="cs-item"><div class="cs-num" style="color:var(--red)">$1,241</div><div class="cs-label">Spent Total</div></div>
        </div>
        <div class="prog-bg"><div class="prog-fill" style="width:30%;background:var(--teal)"></div></div>
      </div>
      <!-- Marketing -->
      <div class="cat-card">
        <div class="cat-top"><div class="cat-icon ci-red">📣</div><button class="cat-menu">⋯</button></div>
        <div class="cat-name">Marketing</div>
        <div class="cat-desc">Ads & Brand Events</div>
        <div class="cat-stats">
          <div class="cs-item"><div class="cs-num">05</div><div class="cs-label">Active</div></div>
          <div class="cs-item"><div class="cs-num" style="color:var(--red)">$8,900</div><div class="cs-label">Spent Total</div></div>
        </div>
        <div class="prog-bg"><div class="prog-fill" style="width:66%;background:var(--red)"></div></div>
      </div>
      <!-- Add new -->
      <div class="add-cat-card">
        <div class="add-plus">＋</div>
        <h4>Create New Category</h4>
        <p>Define custom spending rules</p>
      </div>
    </div>
  </div>
</div>
</body>
</html>