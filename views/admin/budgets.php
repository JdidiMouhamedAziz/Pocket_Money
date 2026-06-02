<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>BudgetPro – Budget Management</title>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet"/>
  <style>
    :root{--bg:#f7f8fc;--white:#fff;--sidebar-bg:#fff;--sidebar-border:#eef0f8;--accent:#4f46e5;--accent-soft:#eef0ff;--teal:#059669;--teal-soft:#d1fae5;--red:#ef4444;--red-soft:#fee2e2;--orange:#f59e0b;--orange-soft:#fff7ed;--yellow:#fbbf24;--yellow-soft:#fffbeb;--blue:#3b82f6;--text-dark:#111827;--text-mid:#374151;--text-muted:#6b7280;--text-light:#9ca3af;--border:#e5e7eb;--radius:12px;--shadow:0 1px 4px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.04);}
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text-dark);display:flex;min-height:100vh;font-size:14px;}
    h1,h2,h3,h4{font-family:'Sora',sans-serif;}
    /* SIDEBAR */
    .sidebar{width:200px;flex-shrink:0;background:var(--sidebar-bg);border-right:1px solid var(--sidebar-border);display:flex;flex-direction:column;padding:0;min-height:100vh;position:fixed;left:0;top:0;bottom:0;}
    .sidebar-header{padding:16px 16px 14px;border-bottom:1px solid var(--sidebar-border);}
    .logo-row{display:flex;align-items:center;gap:9px;}
    .logo-mark{width:32px;height:32px;border-radius:9px;background:linear-gradient(135deg,var(--accent),#818cf8);display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:800;color:#fff;font-family:'Sora',sans-serif;}
    .logo-text h2{font-size:.9rem;font-weight:800;color:var(--text-dark);line-height:1.1;}
    .logo-text p{font-size:.62rem;color:var(--text-muted);}
    .sidebar-nav{padding:10px 8px;flex:1;}
    .nav-item{display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:8px;cursor:pointer;color:var(--text-muted);font-size:.85rem;font-weight:500;text-decoration:none;transition:all .15s;margin-bottom:2px;}
    .nav-item:hover{background:#f3f4f6;color:var(--text-dark);}
    .nav-item.active{background:var(--accent-soft);color:var(--accent);font-weight:700;}
    .nav-icon{font-size:.9rem;width:17px;text-align:center;color:#9ca3af;}
    .nav-item.active .nav-icon{color:var(--accent);}
    .sidebar-spacer{flex:1;}
    .sidebar-footer{padding:12px 14px;border-top:1px solid var(--sidebar-border);}
    .plan-box{background:#f9fafb;border:1px solid var(--border);border-radius:10px;padding:12px;margin-bottom:10px;}
    .plan-label{font-size:.65rem;font-weight:800;color:var(--accent);letter-spacing:.08em;text-transform:uppercase;margin-bottom:4px;}
    .plan-bar-bg{background:#e5e7eb;border-radius:50px;height:5px;margin-bottom:6px;}
    .plan-bar-fill{height:5px;border-radius:50px;background:linear-gradient(90deg,var(--accent),#818cf8);width:72%;}
    .plan-sub{font-size:.68rem;color:var(--text-muted);}
    .btn-upgrade{width:100%;background:var(--accent);color:#fff;border:none;border-radius:8px;padding:8px;font-family:'DM Sans',sans-serif;font-size:.78rem;font-weight:700;cursor:pointer;}
    /* MAIN */
    .main{margin-left:200px;flex:1;display:flex;flex-direction:column;}
    .topnav{background:var(--white);border-bottom:1px solid var(--border);padding:11px 24px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50;}
    .topnav-search{display:flex;align-items:center;gap:7px;background:#f3f4f6;border:1px solid var(--border);border-radius:8px;padding:7px 12px;min-width:260px;}
    .topnav-search input{background:transparent;border:none;outline:none;font-family:'DM Sans',sans-serif;font-size:.83rem;color:var(--text-mid);width:100%;}
    .topnav-search input::placeholder{color:var(--text-light);}
    .topnav-right{display:flex;align-items:center;gap:11px;}
    .notif-btn{width:32px;height:32px;border-radius:50%;background:#f3f4f6;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:.9rem;position:relative;}
    .notif-dot{position:absolute;top:5px;right:5px;width:7px;height:7px;border-radius:50%;background:var(--red);border:2px solid #fff;}
    .profile-btn{display:flex;align-items:center;gap:7px;cursor:pointer;padding:5px 10px 5px 5px;background:#f3f4f6;border:1px solid var(--border);border-radius:50px;}
    .profile-ava{width:24px;height:24px;border-radius:50%;background:linear-gradient(135deg,var(--accent),#818cf8);display:flex;align-items:center;justify-content:center;font-size:.62rem;font-weight:700;color:#fff;}
    .profile-btn span{font-size:.8rem;font-weight:600;color:var(--text-mid);}
    /* CONTENT */
    .content{padding:22px 24px;}
    .breadcrumb{display:flex;align-items:center;gap:5px;font-size:.75rem;color:var(--text-muted);margin-bottom:12px;}
    .breadcrumb a{color:var(--text-muted);text-decoration:none;}
    .breadcrumb a:hover{color:var(--accent);}
    .breadcrumb .crumb-active{color:var(--accent);font-weight:600;}
    .breadcrumb .sep{color:var(--text-light);}
    /* PAGE HEADER */
    .page-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;}
    .page-header h1{font-size:1.4rem;font-weight:800;}
    .page-header p{font-size:.82rem;color:var(--text-muted);margin-top:4px;}
    .btn-create{background:var(--accent);color:#fff;border:none;border-radius:9px;padding:9px 16px;font-family:'Sora',sans-serif;font-weight:700;font-size:.82rem;cursor:pointer;display:flex;align-items:center;gap:6px;box-shadow:0 4px 12px rgba(79,70,229,.3);}
    /* STAT STRIP */
    .stat-strip{display:grid;grid-template-columns:1fr 1fr 1fr;gap:0;margin-bottom:20px;}
    .stat-strip-item{padding:18px 22px;background:var(--white);border:1px solid var(--border);}
    .stat-strip-item:first-child{border-radius:var(--radius) 0 0 var(--radius);}
    .stat-strip-item:last-child{border-radius:0 var(--radius) var(--radius) 0;background:linear-gradient(135deg,#4f46e5,#6366f1);border-color:transparent;}
    .stat-strip-item:nth-child(2){border-left:none;border-right:none;}
    .ss-label{font-size:.68rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px;}
    .ss-value{font-family:'Sora',sans-serif;font-size:1.4rem;font-weight:800;color:var(--text-dark);}
    .ss-sub{font-size:.72rem;color:var(--text-muted);margin-top:4px;display:flex;align-items:center;gap:4px;}
    .ss-green{color:var(--teal);}
    .ss-red{color:var(--red);}
    /* health card */
    .health-label{font-size:.68rem;font-weight:700;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px;}
    .health-title{font-family:'Sora',sans-serif;font-size:1.1rem;font-weight:800;color:#fff;margin-bottom:4px;}
    .health-sub{font-size:.75rem;color:rgba(255,255,255,.75);margin-bottom:10px;}
    .health-avatars{display:flex;align-items:center;gap:4px;}
    .h-ava{width:22px;height:22px;border-radius:50%;border:2px solid rgba(255,255,255,.4);display:flex;align-items:center;justify-content:center;font-size:.58rem;font-weight:700;color:#fff;margin-left:-6px;}
    .h-ava:first-child{margin-left:0;}
    .h-count{font-size:.72rem;color:rgba(255,255,255,.8);margin-left:6px;}
    /* BUDGET GRID */
    .budget-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:22px;}
    .budget-card{background:var(--white);border-radius:var(--radius);padding:16px;box-shadow:var(--shadow);position:relative;}
    .bc-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;}
    .bc-icon{width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1rem;}
    .bi-yellow{background:#fef3c7;}
    .bi-blue{background:#dbeafe;}
    .bi-red{background:#fee2e2;}
    .bi-purple{background:#f5f3ff;}
    .bi-teal{background:#d1fae5;}
    .bc-menu{width:24px;height:24px;border-radius:6px;background:#f3f4f6;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.9rem;color:var(--text-muted);}
    .bc-title{font-size:.9rem;font-weight:700;margin-bottom:2px;}
    .bc-desc{font-size:.72rem;color:var(--text-muted);margin-bottom:10px;}
    .bc-amounts{display:flex;justify-content:space-between;margin-bottom:8px;font-size:.78rem;}
    .bc-spent{font-weight:700;color:var(--text-dark);}
    .bc-total{color:var(--text-muted);}
    .prog-bg{background:#f3f4f6;border-radius:50px;height:5px;margin-bottom:8px;}
    .prog-fill{height:5px;border-radius:50px;}
    .bc-footer{display:flex;justify-content:space-between;align-items:center;font-size:.72rem;color:var(--text-muted);}
    .pct-badge{font-size:.7rem;font-weight:700;padding:2px 8px;border-radius:50px;}
    .pb-green{background:var(--teal-soft);color:var(--teal);}
    .pb-orange{background:#fff7ed;color:#92400e;}
    .pb-red{background:var(--red-soft);color:var(--red);}
    .update-time{font-size:.68rem;color:var(--text-light);}
    .member-avas{display:flex;margin-right:4px;}
    .sm-ava{width:20px;height:20px;border-radius:50%;border:1.5px solid #fff;display:flex;align-items:center;justify-content:center;font-size:.55rem;font-weight:700;color:#fff;margin-left:-5px;}
    .sm-ava:first-child{margin-left:0;}
    /* action required */
    .action-badge{position:absolute;top:12px;right:34px;background:var(--red);color:#fff;font-size:.65rem;font-weight:700;padding:2px 8px;border-radius:5px;}
    /* add card */
    .add-budget-card{background:var(--accent-soft);border:2px dashed #c7d2fe;border-radius:var(--radius);display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:180px;cursor:pointer;transition:background .2s;gap:8px;text-align:center;padding:20px;}
    .add-budget-card:hover{background:#e0e7ff;}
    .add-plus{width:38px;height:38px;border-radius:10px;background:rgba(79,70,229,.12);border:2px solid #c7d2fe;display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:var(--accent);}
    .add-budget-card h4{font-size:.88rem;font-weight:700;color:var(--accent);}
    .add-budget-card p{font-size:.75rem;color:#818cf8;}
    /* RECENT TX */
    .tx-section{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);}
    .tx-header{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border);}
    .tx-header h3{font-size:.93rem;font-weight:700;}
    .view-all-link{font-size:.8rem;color:var(--accent);font-weight:600;cursor:pointer;text-decoration:none;}
    table{width:100%;border-collapse:collapse;}
    thead th{font-size:.65rem;font-weight:700;color:var(--text-light);text-transform:uppercase;letter-spacing:.07em;padding:9px 20px;text-align:left;background:#fafafa;border-bottom:1px solid var(--border);}
    tbody tr{border-bottom:1px solid #f3f4f6;transition:background .12s;}
    tbody tr:last-child{border-bottom:none;}
    tbody tr:hover{background:#fafbff;}
    td{padding:11px 20px;font-size:.83rem;vertical-align:middle;}
    .tx-name{font-weight:600;font-size:.85rem;}
    .tx-sub{font-size:.72rem;color:var(--text-muted);}
    .td-cat{background:#f3f4f6;color:var(--text-mid);border-radius:5px;padding:2px 8px;font-size:.72rem;font-weight:600;display:inline-block;}
    .owner-cell{display:flex;align-items:center;gap:7px;}
    .ow-ava{width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.58rem;font-weight:700;color:#fff;}
    .amt-neg{color:var(--red);font-family:'Sora',sans-serif;font-weight:700;}
    .status-pill{display:inline-flex;align-items:center;gap:4px;font-size:.72rem;font-weight:700;padding:3px 9px;border-radius:50px;}
    .sp-cleared{background:var(--teal-soft);color:var(--teal);}
    .sp-pending{background:var(--yellow-soft);color:#92400e;}
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
    <a class="nav-item active" href="#"><span class="nav-icon">🎯</span> Budgets</a>
    <a class="nav-item" href="#"><span class="nav-icon">💳</span> Transactions</a>
    <a class="nav-item" href="#"><span class="nav-icon">🗂️</span> Categories</a>
    <a class="nav-item" href="#"><span class="nav-icon">🔔</span> Alerts</a>
    <a class="nav-item" href="#"><span class="nav-icon">⚙️</span> Settings</a>
  </nav>
  <div class="sidebar-spacer"></div>
  <div class="sidebar-footer">
    <div class="plan-box">
      <div class="plan-label">PRO PLAN</div>
      <div class="plan-bar-bg"><div class="plan-bar-fill"></div></div>
      <div class="plan-sub">72% of your word used</div>
    </div>
    <button class="btn-upgrade">Upgrade</button>
  </div>
</aside>

<div class="main">
  <div class="topnav">
    <div class="topnav-search">
      <span style="color:#9ca3af;font-size:.82rem">🔍</span>
      <input type="text" placeholder="Search budgets, teams, or transactions..."/>
    </div>
    <div class="topnav-right">
      <div class="notif-btn">🔔<div class="notif-dot"></div></div>
      <div class="profile-btn">
        <div class="profile-ava">AR</div>
        <span>Alex Rivera</span>
        <span style="color:var(--text-light);font-size:.7rem">▾</span>
      </div>
    </div>
  </div>

  <div class="content">
    <div class="breadcrumb">
      <a href="#">Organisation</a><span class="sep">/</span>
      <span class="crumb-active">Budgets</span>
    </div>
    <div class="page-header">
      <div><h1>Budget Management</h1><p>Allocate and track fiscal spending across departments.</p></div>
      <button class="btn-create">✦ Create New Budget</button>
    </div>

    <!-- STAT STRIP -->
    <div class="stat-strip" style="box-shadow:var(--shadow);border-radius:var(--radius);overflow:hidden;margin-bottom:20px;">
      <div class="stat-strip-item">
        <div class="ss-label">Total Allocated</div>
        <div class="ss-value">$245,000.00</div>
        <div class="ss-sub"><span class="ss-green">▲ +12%</span> from last month</div>
      </div>
      <div class="stat-strip-item">
        <div class="ss-label">Spent to Date</div>
        <div class="ss-value">$168,430.50</div>
        <div class="ss-sub"><span class="ss-red">68.7%</span> of total budget utilized</div>
      </div>
      <div class="stat-strip-item">
        <div class="health-label">Budget Health</div>
        <div class="health-title">Optimal Efficiency</div>
        <div class="health-sub">8 active collaborators</div>
        <div class="health-avatars">
          <div class="h-ava" style="background:#4f46e5">SC</div>
          <div class="h-ava" style="background:#059669">MK</div>
          <div class="h-ava" style="background:#f59e0b">ER</div>
          <span class="h-count">+5 more</span>
        </div>
      </div>
    </div>

    <!-- BUDGET CARDS -->
    <div class="budget-grid">
      <!-- Marketing Q3 -->
      <div class="budget-card">
        <div class="bc-top">
          <div class="bc-icon bi-yellow">📣</div>
          <button class="bc-menu">⋯</button>
        </div>
        <div class="bc-title">Marketing Q3</div>
        <div class="bc-desc">Global brand awareness campaign</div>
        <div class="bc-amounts"><span class="bc-spent">$14,200</span><span class="bc-total">/ $45,000</span></div>
        <div class="prog-bg"><div class="prog-fill" style="width:31.5%;background:#f59e0b"></div></div>
        <div class="bc-footer">
          <div style="display:flex;align-items:center;">
            <div class="member-avas">
              <div class="sm-ava" style="background:#4f46e5">SC</div>
              <div class="sm-ava" style="background:#059669">MK</div>
              <div class="sm-ava" style="background:#ef4444">+1</div>
            </div>
          </div>
          <span class="pct-badge pb-green">31.5%</span>
          <span class="update-time">Updated 2h ago</span>
        </div>
      </div>

      <!-- Team Offsite -->
      <div class="budget-card">
        <div class="bc-top">
          <div class="bc-icon bi-blue">✈️</div>
          <button class="bc-menu">⋯</button>
        </div>
        <div class="bc-title">Team Offsite</div>
        <div class="bc-desc">Annual strategy retreat in Tokyo.</div>
        <div class="bc-amounts"><span class="bc-spent">$12,450</span><span class="bc-total">/ $15,000</span></div>
        <div class="prog-bg"><div class="prog-fill" style="width:83%;background:#3b82f6"></div></div>
        <div class="bc-footer">
          <div style="display:flex;align-items:center;">
            <div class="member-avas">
              <div class="sm-ava" style="background:#4f46e5">JD</div>
              <div class="sm-ava" style="background:#f59e0b">ER</div>
            </div>
          </div>
          <span class="pct-badge pb-orange">83%</span>
          <span class="update-time">Updated 1h ago</span>
        </div>
      </div>

      <!-- Infrastructure -->
      <div class="budget-card">
        <div class="action-badge">Action Required</div>
        <div class="bc-top">
          <div class="bc-icon bi-red">🏗️</div>
          <button class="bc-menu">⋯</button>
        </div>
        <div class="bc-title">Infrastructure</div>
        <div class="bc-desc">Cloud services & hosting costs.</div>
        <div class="bc-amounts"><span class="bc-spent">$105,200</span><span class="bc-total">/ $100,000</span></div>
        <div class="prog-bg"><div class="prog-fill" style="width:100%;background:#ef4444"></div></div>
        <div class="bc-footer">
          <div style="display:flex;align-items:center;">
            <div class="member-avas">
              <div class="sm-ava" style="background:#6366f1">RK</div>
            </div>
          </div>
          <span class="pct-badge pb-red">105%</span>
          <span class="update-time">Overbudget</span>
        </div>
      </div>

      <!-- Product R&D -->
      <div class="budget-card">
        <div class="bc-top">
          <div class="bc-icon bi-purple">🧪</div>
          <button class="bc-menu">⋯</button>
        </div>
        <div class="bc-title">Product R&D</div>
        <div class="bc-desc">New feature prototyping phase</div>
        <div class="bc-amounts"><span class="bc-spent">$28,000</span><span class="bc-total">/ $60,000</span></div>
        <div class="prog-bg"><div class="prog-fill" style="width:46%;background:#7c3aed"></div></div>
        <div class="bc-footer">
          <div style="display:flex;align-items:center;">
            <div class="member-avas">
              <div class="sm-ava" style="background:#4f46e5">SC</div>
              <div class="sm-ava" style="background:#059669">AS</div>
            </div>
          </div>
          <span class="pct-badge pb-green">46%</span>
          <span class="update-time">Updated yesterday</span>
        </div>
      </div>

      <!-- Office Ops -->
      <div class="budget-card">
        <div class="bc-top">
          <div class="bc-icon bi-teal">🏢</div>
          <button class="bc-menu">⋯</button>
        </div>
        <div class="bc-title">Office Ops</div>
        <div class="bc-desc">Supplies, rent and utility management</div>
        <div class="bc-amounts"><span class="bc-spent">$22,500</span><span class="bc-total">/ $25,000</span></div>
        <div class="prog-bg"><div class="prog-fill" style="width:90%;background:#059669"></div></div>
        <div class="bc-footer">
          <div style="display:flex;align-items:center;">
            <div class="member-avas">
              <div class="sm-ava" style="background:#f59e0b">ER</div>
            </div>
          </div>
          <span class="pct-badge pb-orange">90%</span>
          <span class="update-time">Updated 1h ago</span>
        </div>
      </div>

      <!-- Add New -->
      <div class="add-budget-card">
        <div class="add-plus">＋</div>
        <h4>Add New Project Budget</h4>
        <p>Instantly allocate funds</p>
      </div>
    </div>

    <!-- RECENT TRANSACTIONS -->
    <div class="tx-section">
      <div class="tx-header">
        <h3>Recent Transaction Activity</h3>
        <a class="view-all-link">View all history →</a>
      </div>
      <table>
        <thead>
          <tr>
            <th>Transaction</th>
            <th>Budget</th>
            <th>Owner</th>
            <th>Amount</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:9px;">
                <div style="width:28px;height:28px;border-radius:8px;background:#f5f3ff;display:flex;align-items:center;justify-content:center;font-size:.85rem;">☁️</div>
                <div><div class="tx-name">AWS Monthly Billing</div></div>
              </div>
            </td>
            <td><span class="td-cat">Infrastructure</span></td>
            <td>
              <div class="owner-cell">
                <div class="ow-ava" style="background:#3b82f6">CT</div>
                C. Thompson
              </div>
            </td>
            <td><span class="amt-neg">–$6,450.00</span></td>
            <td><span class="status-pill sp-cleared">● Cleared</span></td>
          </tr>
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:9px;">
                <div style="width:28px;height:28px;border-radius:8px;background:#fffbeb;display:flex;align-items:center;justify-content:center;font-size:.85rem;">✈️</div>
                <div><div class="tx-name">Tokyo Flight Bookings</div></div>
              </div>
            </td>
            <td><span class="td-cat">Team Offsite</span></td>
            <td>
              <div class="owner-cell">
                <div class="ow-ava" style="background:#059669">AM</div>
                A. Meru
              </div>
            </td>
            <td><span class="amt-neg">–$4,100.00</span></td>
            <td><span class="status-pill sp-pending">● Pending</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>