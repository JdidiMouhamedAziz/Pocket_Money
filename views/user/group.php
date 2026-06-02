<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Finzo – My Groups</title>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet"/>
  <style>
    :root{--bg:#f4f5fb;--sidebar-bg:#2d2d3a;--purple:#7c6af5;--purple-light:#f0edff;--teal:#00c9a7;--red:#ff6b8a;--yellow:#f5c842;--orange:#ff7c3e;--white:#ffffff;--text-dark:#1a1a2e;--text-mid:#555770;--text-muted:#9295a8;--sidebar-text:#b0b3c6;--sidebar-label:#6b6e80;--card-radius:14px;--shadow:0 2px 16px rgba(0,0,0,.07);}
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

    /* TOPBAR */
    .topbar{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:6px;}
    .topbar h1{font-size:1.4rem;font-weight:800;}
    .topbar-right{display:flex;gap:8px;}
    .btn-join{background:var(--white);border:1.5px solid #e2e3ee;border-radius:10px;padding:9px 16px;font-family:'Sora',sans-serif;font-weight:700;font-size:.83rem;color:var(--purple);cursor:pointer;transition:background .15s;}
    .btn-join:hover{background:var(--purple-light);}
    .btn-create-grp{background:var(--purple);color:#fff;border:none;border-radius:10px;padding:9px 16px;font-family:'Sora',sans-serif;font-weight:700;font-size:.83rem;cursor:pointer;box-shadow:0 4px 14px rgba(124,106,245,.3);transition:opacity .2s;}
    .btn-create-grp:hover{opacity:.9;}
    .invite-row{display:flex;align-items:center;gap:8px;margin-bottom:22px;}
    .invite-label{font-size:.82rem;color:var(--text-muted);}
    .invite-code{font-family:'Sora',sans-serif;font-size:.85rem;font-weight:700;color:var(--purple);background:var(--purple-light);border:1.5px solid #c8beff;border-radius:8px;padding:4px 12px;letter-spacing:.08em;}

    /* STAT ROW */
    .stat-row{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:26px;}
    .stat-card{border-radius:var(--card-radius);padding:20px 22px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;box-shadow:var(--shadow);}
    .stat-card.purple-card{background:var(--purple-light);}
    .stat-card.teal-card{background:#e6faf6;}
    .stat-card.yellow-card{background:#fff8e0;}
    .stat-num{font-family:'Sora',sans-serif;font-size:2.2rem;font-weight:800;margin-bottom:4px;}
    .stat-num.c-purple{color:var(--purple);}
    .stat-num.c-teal{color:var(--teal);}
    .stat-num.c-yellow{color:#b8900a;}
    .stat-label{font-size:.8rem;font-weight:600;color:var(--text-muted);}

    /* GROUPS GRID */
    .groups-section-label{font-size:.85rem;font-weight:700;color:var(--text-dark);margin-bottom:14px;}
    .groups-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}

    /* GROUP CARD */
    .group-card{background:var(--white);border-radius:var(--card-radius);overflow:hidden;box-shadow:var(--shadow);}
    .group-banner{padding:14px 16px;display:flex;align-items:center;justify-content:space-between;}
    .group-banner.purple{background:var(--purple);}
    .group-banner.teal{background:var(--teal);}
    .group-banner.orange{background:var(--orange);}
    .role-badge{font-size:.72rem;font-weight:700;padding:4px 12px;border-radius:50px;background:rgba(255,255,255,.25);color:#fff;letter-spacing:.04em;}
    .group-info-btn{width:26px;height:26px;border-radius:50%;background:rgba(255,255,255,.2);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.8rem;color:#fff;}
    .group-body{padding:16px;}
    .group-name{font-size:1rem;font-weight:700;margin-bottom:4px;}
    .group-desc{font-size:.8rem;color:var(--text-muted);margin-bottom:12px;line-height:1.45;}
    .member-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;}
    .member-avas{display:flex;}
    .mava{width:26px;height:26px;border-radius:50%;border:2px solid #fff;display:flex;align-items:center;justify-content:center;font-size:.62rem;font-weight:700;margin-left:-7px;color:#fff;}
    .mava:first-child{margin-left:0;}
    .member-count{font-size:.75rem;color:var(--text-muted);margin-left:8px;}
    .group-stats{display:flex;gap:24px;padding-top:12px;border-top:1px solid #f2f3f8;}
    .gstat-label{font-size:.72rem;color:var(--text-muted);margin-bottom:3px;}
    .gstat-val{font-family:'Sora',sans-serif;font-size:.9rem;font-weight:700;}
    .gstat-val.red{color:var(--red);}
    .gstat-val.dark{color:var(--text-dark);}

    /* CREATE GROUP CARD */
    .create-group-card{background:var(--purple-light);border:2px dashed #c8beff;border-radius:var(--card-radius);display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:200px;cursor:pointer;transition:background .2s;gap:10px;text-align:center;padding:24px;}
    .create-group-card:hover{background:#e2deff;}
    .cg-plus{width:44px;height:44px;border-radius:12px;background:rgba(124,106,245,.15);border:2px solid #c8beff;display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:var(--purple);}
    .create-group-card h4{font-size:1rem;font-weight:700;color:var(--purple);}
    .create-group-card p{font-size:.8rem;color:#9d90f5;line-height:1.5;}
  </style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-logo"><div class="logo-icon"><span>$</span></div><h2>Finzo</h2></div>
  <div class="sidebar-section-label">Main</div>
  <a class="nav-item" href="#"><span class="nav-icon">🏠</span> Dashboard</a>
  <a class="nav-item" href="#"><span class="nav-icon">💳</span> Transactions</a>
  <a class="nav-item" href="#"><span class="nav-icon">🎯</span> Budgets</a>
  <a class="nav-item" href="#"><span class="nav-icon">🗂️</span> Categories</a>
  <div class="sidebar-section-label" style="margin-top:18px;">Collaboration</div>
  <a class="nav-item active" href="#"><span class="nav-icon">👥</span> My Groups</a>
  <a class="nav-item" href="#"><span class="nav-icon">🔔</span> Alerts</a>
  <div class="sidebar-spacer"></div>
  <div class="sidebar-user"><div class="user-ava">KM</div><div><div class="user-name">Karim M.</div><div class="user-plan">Free plan</div></div></div>
</aside>

<main class="main">
  <div class="topbar">
    <div><h1>My groups</h1><p style="font-size:.85rem;color:var(--text-muted);margin-top:3px;">Manage your shared budgets and collaborations</p></div>
    <div class="topbar-right">
      <button class="btn-join">+ Join a Group</button>
      <button class="btn-create-grp">+ Create Group</button>
    </div>
  </div>

  <div class="invite-row">
    <span class="invite-label">Invite · Code</span>
    <span class="invite-code">FNZ-7K3M</span>
  </div>

  <!-- Stats -->
  <div class="stat-row">
    <div class="stat-card purple-card">
      <div class="stat-num c-purple">3</div>
      <div class="stat-label">Active groups</div>
    </div>
    <div class="stat-card teal-card">
      <div class="stat-num c-teal">3,800 TND</div>
      <div class="stat-label">Total shared budget</div>
    </div>
    <div class="stat-card yellow-card">
      <div class="stat-num c-yellow">10</div>
      <div class="stat-label">Total members</div>
    </div>
  </div>

  <div class="groups-section-label">Your groups</div>

  <div class="groups-grid">
    <!-- Family Budget -->
    <div class="group-card">
      <div class="group-banner purple">
        <span class="role-badge">Admin</span>
        <button class="group-info-btn">ℹ</button>
      </div>
      <div class="group-body">
        <div class="group-name">Family Budget</div>
        <div class="group-desc">Monthly household expenses and savings goals</div>
        <div class="member-row">
          <div style="display:flex;align-items:center;">
            <div class="member-avas">
              <div class="mava" style="background:#f5c842;color:#333">JK</div>
              <div class="mava" style="background:#7c6af5">SA</div>
              <div class="mava" style="background:#00c9a7">MR</div>
              <div class="mava" style="background:#ff7c3e">TN</div>
            </div>
            <span class="member-count">4 members</span>
          </div>
        </div>
        <div class="group-stats">
          <div><div class="gstat-label">Budget</div><div class="gstat-val dark">2,000 TND</div></div>
          <div><div class="gstat-label">Spent</div><div class="gstat-val red">2,000 TND</div></div>
        </div>
      </div>
    </div>

    <!-- Work Team -->
    <div class="group-card">
      <div class="group-banner teal">
        <span class="role-badge">Member</span>
        <button class="group-info-btn">ℹ</button>
      </div>
      <div class="group-body">
        <div class="group-name">Work Team</div>
        <div class="group-desc">Monthly household expenses and savings goals</div>
        <div class="member-row">
          <div style="display:flex;align-items:center;">
            <div class="member-avas">
              <div class="mava" style="background:#7c6af5">AM</div>
              <div class="mava" style="background:#ff6b8a">LB</div>
              <div class="mava" style="background:#f5c842;color:#333">KS</div>
              <div class="mava" style="background:#4c9be8">RH</div>
            </div>
            <span class="member-count">6 members</span>
          </div>
        </div>
        <div class="group-stats">
          <div><div class="gstat-label">Budget</div><div class="gstat-val dark">2,000 TND</div></div>
          <div><div class="gstat-label">Spent</div><div class="gstat-val red">2,000 TND</div></div>
        </div>
      </div>
    </div>

    <!-- Summer Trip -->
    <div class="group-card">
      <div class="group-banner orange">
        <span class="role-badge">Admin</span>
        <button class="group-info-btn">ℹ</button>
      </div>
      <div class="group-body">
        <div class="group-name">Summer Trip 2026</div>
        <div class="group-desc">Monthly household expenses and savings goals</div>
        <div class="member-row">
          <div style="display:flex;align-items:center;">
            <div class="member-avas">
              <div class="mava" style="background:#7c6af5">KM</div>
              <div class="mava" style="background:#00c9a7">SA</div>
              <div class="mava" style="background:#ff6b8a">NB</div>
              <div class="mava" style="background:#4c9be8">OT</div>
            </div>
            <span class="member-count">4 members</span>
          </div>
        </div>
        <div class="group-stats">
          <div><div class="gstat-label">Budget</div><div class="gstat-val dark">2,000 TND</div></div>
          <div><div class="gstat-label">Spent</div><div class="gstat-val red">2,000 TND</div></div>
        </div>
      </div>
    </div>

    <!-- Create new -->
    <div class="create-group-card">
      <div class="cg-plus">＋</div>
      <h4>Create new group</h4>
      <p>Invite people and manage a shared budget together</p>
    </div>
  </div>
</main>
</body>
</html>