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
    .btn-new-report{width:100%;background:var(--accent);color:#fff;border:none;border-radius:8px;padding:9px;font-family:'Sora',sans-serif;font-weight:700;font-size:.8rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:5px;}
    .main{margin-left:200px;flex:1;display:flex;flex-direction:column;}
    .topnav{background:var(--white);border-bottom:1px solid var(--border);padding:11px 24px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50;}
    .topnav-right{display:flex;align-items:center;gap:10px;}
    .notif-btn{width:32px;height:32px;border-radius:50%;background:#f3f4f6;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;cursor:pointer;position:relative;}
    .notif-dot{position:absolute;top:5px;right:5px;width:7px;height:7px;border-radius:50%;background:var(--red);border:2px solid #fff;}
    .profile-btn{display:flex;align-items:center;gap:7px;cursor:pointer;padding:5px 10px 5px 5px;background:#f3f4f6;border:1px solid var(--border);border-radius:50px;}
    .profile-ava{width:24px;height:24px;border-radius:50%;background:linear-gradient(135deg,var(--accent),#818cf8);display:flex;align-items:center;justify-content:center;font-size:.62rem;font-weight:700;color:#fff;}
    .profile-btn span{font-size:.8rem;font-weight:600;color:var(--text-mid);}
    /* LAYOUT */
    .content{padding:22px 24px;display:grid;grid-template-columns:1fr 280px;gap:20px;}
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
    /* ALERT LIST */
    .alerts-list{background:var(--white);border-radius:0 0 var(--radius) var(--radius);box-shadow:var(--shadow);}
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
    /* RIGHT COLUMN */
    .right-col{display:flex;flex-direction:column;gap:16px;}
    /* DAILY SUMMARY */
    .summary-card{background:var(--accent);border-radius:var(--radius);padding:18px;color:#fff;}
    .summary-card h4{font-size:.88rem;font-weight:700;margin-bottom:6px;}
    .summary-card p{font-size:.78rem;color:rgba(255,255,255,.8);line-height:1.5;margin-bottom:14px;}
    .btn-gen-report{width:100%;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);border-radius:8px;padding:9px;font-family:'Sora',sans-serif;font-weight:700;font-size:.8rem;color:#fff;cursor:pointer;transition:background .15s;}
    .btn-gen-report:hover{background:rgba(255,255,255,.25);}
    /* ANALYTICS */
    .analytics-card{background:var(--white);border-radius:var(--radius);padding:18px;box-shadow:var(--shadow);}
    .analytics-card h4{font-size:.88rem;font-weight:700;margin-bottom:14px;}
    .analytics-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;}
    .analytics-label{font-size:.78rem;color:var(--text-mid);}
    .analytics-right{display:flex;align-items:center;gap:8px;}
    .analytics-pct{font-size:.78rem;font-weight:700;}
    .analytics-bar-bg{width:80px;height:6px;background:#f3f4f6;border-radius:50px;}
    .analytics-bar-fill{height:6px;border-radius:50px;}
    .pct-red{color:var(--red);}
    .pct-orange{color:var(--orange);}
    .pct-blue{color:var(--accent);}
    /* AI CARD */
    .ai-card{background:linear-gradient(135deg,#1e1b4b,#3730a3);border-radius:var(--radius);padding:18px;color:#fff;}
    .ai-card-top{display:flex;align-items:center;gap:8px;margin-bottom:8px;}
    .ai-icon{width:32px;height:32px;border-radius:8px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;font-size:.9rem;}
    .ai-card h4{font-size:.9rem;font-weight:800;}
    .ai-card p{font-size:.75rem;color:rgba(255,255,255,.75);line-height:1.5;margin-bottom:14px;}
    .btn-view-strat{width:100%;background:rgba(255,255,255,.15);border:1.5px solid rgba(255,255,255,.3);border-radius:8px;padding:8px;font-family:'Sora',sans-serif;font-weight:700;font-size:.78rem;color:#fff;cursor:pointer;}
    .btn-view-strat:hover{background:rgba(255,255,255,.25);}
  </style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-header">
    <div class="logo-row"><div class="logo-mark">B</div><div class="logo-text"><h2>BudgetPro</h2><p>Collaborative Finance</p></div></div>
  </div>
  <nav class="sidebar-nav">
    <a class="nav-item" href="#"><span class="nav-icon">📊</span> Dashboard</a>
    <a class="nav-item" href="#"><span class="nav-icon">👥</span> Users</a>
    <a class="nav-item" href="#"><span class="nav-icon">🎯</span> Budgets</a>
    <a class="nav-item" href="#"><span class="nav-icon">💳</span> Transactions</a>
    <a class="nav-item" href="#"><span class="nav-icon">🗂️</span> Categories</a>
    <a class="nav-item active" href="#"><span class="nav-icon">🔔</span> Alerts</a>
    <a class="nav-item" href="#"><span class="nav-icon">⚙️</span> Settings</a>
  </nav>
  <div class="sidebar-spacer"></div>
  <div class="sidebar-footer"><button class="btn-new-report">＋ New Report</button></div>
</aside>
<div class="main">
  <div class="topnav">
    <h2 style="font-size:1.1rem;font-weight:800;">Alerts &amp; Notifications</h2>
    <div class="topnav-right">
      <div class="notif-btn">🔔<div class="notif-dot"></div></div>
      <div class="profile-btn"><div class="profile-ava">AR</div><span>Alex Rivera</span><span style="color:var(--text-light);font-size:.7rem">▾</span></div>
    </div>
  </div>
  <div class="content">
    <!-- LEFT -->
    <div class="left-col">
      <div class="filter-bar">
        <div style="display:flex;flex-direction:column;gap:6px;width:100%;">
          <div class="filter-label">Filter Alerts</div>
          <div class="ftab-row">
            <div class="ftab active" onclick="filterAlerts('all',this)">All Alerts <span class="ftab-count">8</span></div>
            <div class="ftab" onclick="filterAlerts('unread',this)">Unread <span class="ftab-count">4</span></div>
            <div class="ftab" onclick="filterAlerts('archived',this)">Archived <span class="ftab-count">2</span></div>
          </div>
        </div>
      </div>
      <div class="alerts-list">
        <!-- Budget Exceeded -->
        <div class="alert-item" data-type="all unread">
          <div class="alert-left-bar bar-red"></div>
          <div class="alert-body">
            <div class="alert-top-row">
              <div class="alert-title-row">
                <span class="alert-title red">Budget Exceeded</span>
                <span class="crit-badge cb-critical">CRITICAL</span>
              </div>
              <button class="btn-take-action">Take Action</button>
            </div>
            <div class="alert-text">The Q3 Cloud Infrastructure budget has exceeded its limit by 12% ($81,348).</div>
            <div class="alert-meta">
              <span class="alert-meta-item">🕐 2 hours ago</span>
              <span class="alert-meta-item">👤 IT Dept</span>
            </div>
          </div>
        </div>
        <!-- Warning Near Limit -->
        <div class="alert-item" data-type="all unread">
          <div class="alert-left-bar bar-orange"></div>
          <div class="alert-body">
            <div class="alert-top-row">
              <div class="alert-title-row">
                <span class="alert-title orange">Warning: Near Limit</span>
                <span class="crit-badge cb-warning">CRITICAL</span>
              </div>
              <button class="btn-take-action">Take Action</button>
            </div>
            <div class="alert-text">The Employee Wellness Program is at 85% of its monthly allocation ($4,250 of $5,000).</div>
            <div class="alert-meta">
              <span class="alert-meta-item">🕐 3 hours ago</span>
              <span class="alert-meta-item">👤 HR Benefits</span>
            </div>
          </div>
        </div>
        <!-- Monthly Reconciliation -->
        <div class="alert-item" data-type="all">
          <div class="alert-left-bar bar-blue"></div>
          <div class="alert-body">
            <div class="alert-top-row">
              <div class="alert-title-row">
                <span class="alert-title blue">Monthly Reconciliation Due</span>
                <span class="crit-badge cb-new">NEW</span>
              </div>
              <button class="btn-take-action" style="background:#f3f4f6;color:var(--text-mid);box-shadow:none;border:1px solid var(--border);">⋯</button>
            </div>
            <div class="alert-text">It's time to review and reconcile the Marketing Operations expenses for last month.</div>
            <div class="alert-meta">
              <span class="alert-meta-item">🕐 Yesterday</span>
              <span class="alert-meta-item">🗂️ Marketing</span>
            </div>
          </div>
        </div>
        <div class="end-msg">You've reached the end of your notifications for today. <a>View Notification History</a></div>
      </div>
    </div>
    <!-- RIGHT -->
    <div class="right-col">
      <!-- Daily Summary -->
      <div class="summary-card">
        <h4>Daily Summary</h4>
        <p>You have 2 critical alerts that require immediate attention today.</p>
        <button class="btn-gen-report">Generate Report</button>
      </div>
      <!-- Analytics -->
      <div class="analytics-card">
        <h4>Alert Analytics</h4>
        <div class="analytics-row">
          <span class="analytics-label">Critical</span>
          <div class="analytics-right">
            <span class="analytics-pct pct-red">34%</span>
            <div class="analytics-bar-bg"><div class="analytics-bar-fill" style="width:34%;background:var(--red)"></div></div>
          </div>
        </div>
        <div class="analytics-row">
          <span class="analytics-label">Warning</span>
          <div class="analytics-right">
            <span class="analytics-pct pct-orange">56%</span>
            <div class="analytics-bar-bg"><div class="analytics-bar-fill" style="width:56%;background:var(--orange)"></div></div>
          </div>
        </div>
        <div class="analytics-row">
          <span class="analytics-label">Info</span>
          <div class="analytics-right">
            <span class="analytics-pct pct-blue">38%</span>
            <div class="analytics-bar-bg"><div class="analytics-bar-fill" style="width:38%;background:var(--accent)"></div></div>
          </div>
        </div>
      </div>
      <!-- AI -->
      <div class="ai-card">
        <div class="ai-card-top"><div class="ai-icon">🤖</div><h4>AI Budget Optimizer</h4></div>
        <p>Based on your alerts, we can suggest 3 reallocation strategies to prevent future budget overflows.</p>
        <button class="btn-view-strat">View Strategies</button>
      </div>
    </div>
  </div>
</div>
<script>
  function filterAlerts(type,btn){
    document.querySelectorAll('.ftab').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.alert-item').forEach(item=>{
      item.style.display = (type==='all'||item.dataset.type.includes(type)) ? '' : 'none';
    });
  }
</script>
</body>
</html>