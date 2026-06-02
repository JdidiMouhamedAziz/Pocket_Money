<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Finzo – Categories</title>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet"/>
  <style>
    :root{--bg:#f4f5fb;--sidebar-bg:#2d2d3a;--purple:#7c6af5;--purple-light:#f0edff;--purple-mid:#e8e3ff;--teal:#00c9a7;--red:#ff6b8a;--yellow:#f5c842;--white:#ffffff;--text-dark:#1a1a2e;--text-mid:#555770;--text-muted:#9295a8;--sidebar-text:#b0b3c6;--sidebar-label:#6b6e80;--card-radius:14px;--shadow:0 2px 16px rgba(0,0,0,.07);}
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
    .sidebar-user{display:flex;align-items:center;gap:10px;padding:14px 22px;border-top:1px solid rgba(255,255,255,.08);}
    .user-ava{width:36px;height:36px;border-radius:50%;background:var(--purple);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.78rem;font-weight:700;flex-shrink:0;}
    .user-name{font-size:.85rem;font-weight:600;color:#fff;}
    .user-plan{font-size:.72rem;color:var(--sidebar-label);}
    .main{margin-left:200px;flex:1;padding:30px 28px;}
    .topbar{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:24px;}
    .topbar h1{font-size:1.4rem;font-weight:800;}
    .topbar p{font-size:.85rem;color:var(--text-muted);margin-top:3px;}

    /* CATEGORY GRID */
    .cat-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:16px;}
    .cat-card{background:var(--white);border-radius:var(--card-radius);padding:22px 24px;box-shadow:var(--shadow);}
    .cat-card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;}
    .cat-card-header h3{font-size:1.1rem;font-weight:700;color:var(--text-dark);}
    .info-btn{width:24px;height:24px;border-radius:50%;background:#f5f6fc;border:1px solid #e2e3ee;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:.75rem;color:var(--text-muted);}
    .cat-stat-row{display:flex;flex-direction:column;gap:8px;}
    .cat-stat{display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid #f2f3f8;}
    .cat-stat:last-child{border-bottom:none;}
    .cat-stat .label{font-size:.85rem;color:var(--text-mid);font-weight:500;}
    .cat-stat .value{font-size:.85rem;font-weight:700;}
    .v-red{color:var(--red);}
    .v-teal{color:var(--teal);}
    .v-dark{color:var(--text-dark);}

    /* ADD CARD */
    .add-card{background:var(--purple-light);border:2px dashed #c8beff;border-radius:var(--card-radius);display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:200px;cursor:pointer;transition:background .2s;gap:12px;}
    .add-card:hover{background:var(--purple-mid);}
    .add-plus{width:42px;height:42px;border-radius:12px;background:rgba(124,106,245,.15);border:2px solid #c8beff;display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:var(--purple);}
    .add-card h4{font-size:1rem;font-weight:700;color:var(--purple);}
  </style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-logo"><div class="logo-icon"><span>$</span></div><h2>Finzo</h2></div>
  <div class="sidebar-section-label">Main</div>
  <a class="nav-item" href="#"><span class="nav-icon">🏠</span> Dashboard</a>
  <a class="nav-item" href="#"><span class="nav-icon">💳</span> Transactions</a>
  <a class="nav-item" href="#"><span class="nav-icon">🎯</span> Budgets</a>
  <a class="nav-item active" href="#"><span class="nav-icon">🗂️</span> Categories</a>
  <div class="sidebar-section-label" style="margin-top:18px;">Collaboration</div>
  <a class="nav-item" href="#"><span class="nav-icon">👥</span> My Groups</a>
  <a class="nav-item" href="#"><span class="nav-icon">🔔</span> Alerts</a>
  <div class="sidebar-spacer"></div>
  <div class="sidebar-user"><div class="user-ava">KM</div><div><div class="user-name">Karim M.</div><div class="user-plan">Free plan</div></div></div>
</aside>

<main class="main">
  <div class="topbar">
    <div><h1>Categories</h1><p>Manage your categories</p></div>
  </div>

  <div class="cat-grid">
    <!-- Food -->
    <div class="cat-card">
      <div class="cat-card-header"><h3>Food</h3><div class="info-btn">ℹ</div></div>
      <div class="cat-stat-row">
        <div class="cat-stat"><span class="label">Total Budget</span><span class="value v-red">1500 TND</span></div>
        <div class="cat-stat"><span class="label">Income</span><span class="value v-teal">2500 TND</span></div>
        <div class="cat-stat"><span class="label">Expenses</span><span class="value v-red">1000 TND</span></div>
        <div class="cat-stat"><span class="label">Transactions</span><span class="value v-dark">25</span></div>
      </div>
    </div>

    <!-- Transport -->
    <div class="cat-card">
      <div class="cat-card-header"><h3>Transport</h3><div class="info-btn">ℹ</div></div>
      <div class="cat-stat-row">
        <div class="cat-stat"><span class="label">Total Budget</span><span class="value v-red">1500 TND</span></div>
        <div class="cat-stat"><span class="label">Income</span><span class="value v-teal">2500 TND</span></div>
        <div class="cat-stat"><span class="label">Expenses</span><span class="value v-red">1000 TND</span></div>
        <div class="cat-stat"><span class="label">Transactions</span><span class="value v-dark">25</span></div>
      </div>
    </div>

    <!-- Health -->
    <div class="cat-card">
      <div class="cat-card-header"><h3>Health</h3><div class="info-btn">ℹ</div></div>
      <div class="cat-stat-row">
        <div class="cat-stat"><span class="label">Total Budget</span><span class="value v-red">1500 TND</span></div>
        <div class="cat-stat"><span class="label">Income</span><span class="value v-teal">2500 TND</span></div>
        <div class="cat-stat"><span class="label">Expenses</span><span class="value v-red">1000 TND</span></div>
        <div class="cat-stat"><span class="label">Transactions</span><span class="value v-dark">25</span></div>
      </div>
    </div>

    <!-- Add New -->
    <div class="add-card">
      <div class="add-plus">＋</div>
      <h4>Add New Category</h4>
    </div>
  </div>
</main>
</body>
</html>