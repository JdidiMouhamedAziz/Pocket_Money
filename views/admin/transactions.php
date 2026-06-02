<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>BudgetPro – Transactions</title>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet"/>
  <style>
    :root{--bg:#f7f8fc;--white:#fff;--sidebar-bg:#fff;--sidebar-border:#eef0f8;--accent:#4f46e5;--accent-soft:#eef0ff;--teal:#059669;--teal-soft:#d1fae5;--red:#ef4444;--red-soft:#fee2e2;--orange:#f59e0b;--text-dark:#111827;--text-mid:#374151;--text-muted:#6b7280;--text-light:#9ca3af;--border:#e5e7eb;--radius:12px;--shadow:0 1px 4px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.04);}
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text-dark);display:flex;min-height:100vh;font-size:14px;}
    h1,h2,h3,h4{font-family:'Sora',sans-serif;}
    .sidebar{width:200px;flex-shrink:0;background:var(--sidebar-bg);border-right:1px solid var(--sidebar-border);display:flex;flex-direction:column;min-height:100vh;position:fixed;left:0;top:0;bottom:0;}
    .sidebar-header{padding:16px 16px 14px;border-bottom:1px solid var(--sidebar-border);}
    .logo-row{display:flex;align-items:center;gap:9px;}
    .logo-mark{width:32px;height:32px;border-radius:9px;background:linear-gradient(135deg,var(--accent),#818cf8);display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:800;color:#fff;font-family:'Sora',sans-serif;}
    .logo-text h2{font-size:.9rem;font-weight:800;line-height:1.1;}
    .logo-text p{font-size:.62rem;color:var(--text-muted);}
    .sidebar-nav{padding:10px 8px;flex:1;}
    .nav-item{display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:8px;cursor:pointer;color:var(--text-muted);font-size:.85rem;font-weight:500;text-decoration:none;transition:all .15s;margin-bottom:2px;}
    .nav-item:hover{background:#f3f4f6;color:var(--text-dark);}
    .nav-item.active{background:var(--accent-soft);color:var(--accent);font-weight:700;}
    .nav-icon{font-size:.9rem;width:17px;text-align:center;color:#9ca3af;}
    .nav-item.active .nav-icon{color:var(--accent);}
    .sidebar-spacer{flex:1;}
    .sidebar-footer{padding:12px 14px;border-top:1px solid var(--sidebar-border);}
    .btn-new-report{width:100%;background:var(--accent);color:#fff;border:none;border-radius:8px;padding:9px;font-family:'Sora',sans-serif;font-weight:700;font-size:.8rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:5px;}
    .main{margin-left:200px;flex:1;display:flex;flex-direction:column;}
    .topnav{background:var(--white);border-bottom:1px solid var(--border);padding:11px 24px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50;}
    .topnav-search{display:flex;align-items:center;gap:7px;background:#f3f4f6;border:1px solid var(--border);border-radius:8px;padding:7px 12px;min-width:260px;}
    .topnav-search input{background:transparent;border:none;outline:none;font-family:'DM Sans',sans-serif;font-size:.83rem;color:var(--text-mid);width:100%;}
    .topnav-search input::placeholder{color:var(--text-light);}
    .topnav-right{display:flex;align-items:center;gap:10px;}
    .notif-btn{width:32px;height:32px;border-radius:50%;background:#f3f4f6;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;cursor:pointer;position:relative;}
    .notif-dot{position:absolute;top:5px;right:5px;width:7px;height:7px;border-radius:50%;background:var(--red);border:2px solid #fff;}
    .profile-btn{display:flex;align-items:center;gap:7px;cursor:pointer;padding:5px 10px 5px 5px;background:#f3f4f6;border:1px solid var(--border);border-radius:50px;}
    .profile-ava{width:24px;height:24px;border-radius:50%;background:linear-gradient(135deg,var(--accent),#818cf8);display:flex;align-items:center;justify-content:center;font-size:.62rem;font-weight:700;color:#fff;}
    .profile-btn span{font-size:.8rem;font-weight:600;color:var(--text-mid);}
    .content{padding:22px 24px;}
    /* HEADER */
    .page-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;}
    .page-header h1{font-size:1.4rem;font-weight:800;}
    .page-header p{font-size:.82rem;color:var(--text-muted);margin-top:4px;}
    .header-btns{display:flex;gap:8px;}
    .btn-export{display:flex;align-items:center;gap:5px;background:var(--white);border:1px solid var(--border);border-radius:8px;padding:8px 14px;font-family:'DM Sans',sans-serif;font-size:.8rem;font-weight:600;color:var(--text-mid);cursor:pointer;transition:background .15s;}
    .btn-export:hover{background:#f3f4f6;}
    /* FILTER BAR */
    .filter-bar{background:var(--white);border-radius:var(--radius);padding:16px 20px;box-shadow:var(--shadow);margin-bottom:18px;display:grid;grid-template-columns:1fr auto auto auto auto;gap:10px;align-items:end;}
    .filter-group{display:flex;flex-direction:column;gap:4px;}
    .filter-label{font-size:.68rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;}
    .filter-input{background:#f3f4f6;border:1px solid var(--border);border-radius:8px;padding:7px 11px;font-family:'DM Sans',sans-serif;font-size:.82rem;color:var(--text-mid);outline:none;transition:border-color .2s;}
    .filter-input:focus{border-color:var(--accent);}
    .filter-select{background:#f3f4f6;border:1px solid var(--border);border-radius:8px;padding:7px 11px;font-family:'DM Sans',sans-serif;font-size:.82rem;color:var(--text-mid);outline:none;}
    .btn-apply{background:var(--accent);color:#fff;border:none;border-radius:8px;padding:8px 16px;font-family:'Sora',sans-serif;font-weight:700;font-size:.8rem;cursor:pointer;white-space:nowrap;align-self:end;}
    /* MAIN TABLE CARD */
    .tx-card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);margin-bottom:18px;}
    table{width:100%;border-collapse:collapse;}
    thead th{font-size:.65rem;font-weight:700;color:var(--text-light);text-transform:uppercase;letter-spacing:.07em;padding:10px 20px;text-align:left;background:#fafafa;border-bottom:1px solid var(--border);}
    tbody tr{border-bottom:1px solid #f3f4f6;transition:background .12s;cursor:pointer;}
    tbody tr:last-child{border-bottom:none;}
    tbody tr:hover{background:#fafbff;}
    td{padding:12px 20px;vertical-align:middle;}
    .type-dot{width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.8rem;flex-shrink:0;}
    .type-expense-dot{background:var(--red-soft);}
    .type-income-dot{background:var(--teal-soft);}
    .tx-name-cell{display:flex;align-items:center;gap:10px;}
    .tx-name{font-size:.87rem;font-weight:600;}
    .tx-id{font-size:.7rem;color:var(--text-muted);}
    .td-cat{background:#f3f4f6;color:var(--text-mid);border-radius:5px;padding:3px 8px;font-size:.7rem;font-weight:600;display:inline-block;}
    .td-cat.software{background:#ede9fe;color:var(--accent);}
    .td-cat.income{background:var(--teal-soft);color:var(--teal);}
    .td-cat.travel{background:#fffbeb;color:#92400e;}
    .user-cell{display:flex;align-items:center;gap:7px;}
    .user-ava{width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:700;color:#fff;}
    .td-date{font-size:.8rem;color:var(--text-muted);}
    .amt-neg{font-family:'Sora',sans-serif;font-weight:700;font-size:.88rem;color:var(--red);}
    .amt-pos{font-family:'Sora',sans-serif;font-weight:700;font-size:.88rem;color:var(--teal);}
    /* PAGINATION */
    .pagination{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-top:1px solid var(--border);}
    .showing-txt{font-size:.78rem;color:var(--text-muted);}
    .page-btns{display:flex;gap:3px;}
    .page-btn{width:28px;height:28px;border-radius:6px;border:1px solid var(--border);background:var(--white);display:flex;align-items:center;justify-content:center;font-size:.78rem;font-weight:600;color:var(--text-mid);cursor:pointer;}
    .page-btn:hover{background:#f3f4f6;}
    .page-btn.active{background:var(--accent);border-color:var(--accent);color:#fff;}
    .rows-select{background:#f3f4f6;border:1px solid var(--border);border-radius:6px;padding:4px 8px;font-family:'DM Sans',sans-serif;font-size:.78rem;color:var(--text-mid);outline:none;}
    /* BOTTOM CARDS */
    .bottom-row{display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;}
    .bottom-card{background:var(--white);border-radius:var(--radius);padding:18px 20px;box-shadow:var(--shadow);}
    .bottom-card.accent-card{background:linear-gradient(135deg,#4f46e5,#6366f1);}
    .bc-label{font-size:.68rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;}
    .bc-label.light{color:rgba(255,255,255,.7);}
    .bc-value{font-family:'Sora',sans-serif;font-size:1.5rem;font-weight:800;color:var(--text-dark);}
    .bc-value.white{color:#fff;}
    .bc-sub{font-size:.75rem;color:var(--text-muted);margin-top:3px;display:flex;align-items:center;gap:4px;}
    .bc-sub.light{color:rgba(255,255,255,.75);}
    .bc-sub .green{color:var(--teal);}
    .sync-btn{width:100%;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.25);border-radius:8px;padding:9px;font-family:'DM Sans',sans-serif;font-size:.8rem;font-weight:700;color:#fff;cursor:pointer;margin-top:12px;transition:background .15s;}
    .sync-btn:hover{background:rgba(255,255,255,.25);}
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
    <a class="nav-item active" href="#"><span class="nav-icon">💳</span> Transactions</a>
    <a class="nav-item" href="#"><span class="nav-icon">🗂️</span> Categories</a>
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
    <div class="topnav-search">
      <span style="color:#9ca3af;font-size:.82rem">🔍</span>
      <input type="text" placeholder="Global search..."/>
    </div>
    <div class="topnav-right">
      <div class="notif-btn">🔔<div class="notif-dot"></div></div>
      <div class="profile-btn">
        <div class="profile-ava">AC</div>
        <span>Alex Chen</span>
        <span style="color:var(--text-light);font-size:.7rem">▾</span>
      </div>
    </div>
  </div>

  <div class="content">
    <div class="page-header">
      <div>
        <h1>Transactions</h1>
        <p>Review and manage collaborative financial activity.</p>
      </div>
      <div class="header-btns">
        <button class="btn-export">↑ Export</button>
        <button class="btn-export">CSV</button>
        <button class="btn-export">PDF</button>
      </div>
    </div>

    <!-- FILTER BAR -->
    <div class="filter-bar">
      <div class="filter-group">
        <div class="filter-label">Search Transaction</div>
        <input class="filter-input" type="text" placeholder="ID, Vendor, Usr..."/>
      </div>
      <div class="filter-group">
        <div class="filter-label">Date Range</div>
        <input class="filter-input" type="text" value="Oct 1 – Oct 31, 2023"/>
      </div>
      <div class="filter-group">
        <div class="filter-label">Category</div>
        <select class="filter-select"><option>All Categories</option><option>Software</option><option>Travel</option><option>Infrastructure</option></select>
      </div>
      <div class="filter-group">
        <div class="filter-label">Type</div>
        <div style="display:flex;gap:5px;align-items:center;background:#f3f4f6;border:1px solid var(--border);border-radius:8px;padding:5px 8px;">
          <span style="font-size:.78rem;font-weight:600;color:var(--text-mid)">Both</span>
          <span style="font-size:.78rem;color:var(--teal);font-weight:600;margin:0 6px">Income</span>
          <span style="font-size:.78rem;color:var(--red);font-weight:600">Expense</span>
        </div>
      </div>
      <button class="btn-apply">✦ Apply Filters</button>
    </div>

    <!-- TABLE -->
    <div class="tx-card">
      <table>
        <thead>
          <tr>
            <th>Type</th>
            <th>Transaction Details</th>
            <th>Category</th>
            <th>Date</th>
            <th>Team Member</th>
            <th style="text-align:right">Amount</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><div class="type-dot type-expense-dot">↗</div></td>
            <td>
              <div class="tx-name">Amazon Web Services</div>
              <div class="tx-id">AWS-32034-2023</div>
            </td>
            <td><span class="td-cat software">SOFTWARE SAAS</span></td>
            <td class="td-date">Oct 24, 2023</td>
            <td>
              <div class="user-cell">
                <div class="user-ava" style="background:#4f46e5">SM</div>
                Sarah Miller
              </div>
            </td>
            <td style="text-align:right"><span class="amt-neg">–$1,240</span></td>
          </tr>
          <tr>
            <td><div class="type-dot type-income-dot" style="color:var(--teal)">✓</div></td>
            <td>
              <div class="tx-name">Stripe Payout</div>
              <div class="tx-id">STP-00108</div>
            </td>
            <td><span class="td-cat income">INCOME</span></td>
            <td class="td-date">Oct 23, 2023</td>
            <td>
              <div class="user-cell">
                <div class="user-ava" style="background:#059669">SY</div>
                System
              </div>
            </td>
            <td style="text-align:right"><span class="amt-pos">+$12,500</span></td>
          </tr>
          <tr>
            <td><div class="type-dot type-expense-dot">↗</div></td>
            <td>
              <div class="tx-name">Adobe Creative Cloud</div>
              <div class="tx-id">ADO-71112</div>
            </td>
            <td><span class="td-cat software">SOFTWARE SAAS</span></td>
            <td class="td-date">Oct 21, 2023</td>
            <td>
              <div class="user-cell">
                <div class="user-ava" style="background:#f59e0b">JW</div>
                Jordan Watts
              </div>
            </td>
            <td style="text-align:right"><span class="amt-neg">–$52.99</span></td>
          </tr>
          <tr>
            <td><div class="type-dot type-expense-dot">↗</div></td>
            <td>
              <div class="tx-name">Delta Airlines</div>
              <div class="tx-id">TRV-TAI-741</div>
            </td>
            <td><span class="td-cat travel">TRAVEL DINING</span></td>
            <td class="td-date">Oct 18, 2023</td>
            <td>
              <div class="user-cell">
                <div class="user-ava" style="background:#6366f1">RV</div>
                Robert Vance
              </div>
            </td>
            <td style="text-align:right"><span class="amt-neg">–$840.15</span></td>
          </tr>
        </tbody>
      </table>
      <div class="pagination">
        <div class="showing-txt">Showing 1–10 of 245 transactions</div>
        <div class="page-btns">
          <button class="page-btn">‹</button>
          <button class="page-btn active">1</button>
          <button class="page-btn">2</button>
          <button class="page-btn">3</button>
          <button class="page-btn">…</button>
          <button class="page-btn">25</button>
          <button class="page-btn">›</button>
        </div>
        <select class="rows-select"><option>10</option><option>25</option><option>50</option></select>
      </div>
    </div>

    <!-- BOTTOM CARDS -->
    <div class="bottom-row">
      <div class="bottom-card accent-card">
        <div class="bc-label light">This Month's Spend</div>
        <div class="bc-value white">$42,508.00</div>
        <div class="bc-sub light"><span class="green">▼ 12% lower than Sept</span></div>
      </div>
      <div class="bottom-card">
        <div class="bc-label">Top Category</div>
        <div style="display:flex;align-items:center;gap:10px;margin-top:4px;">
          <div style="width:36px;height:36px;border-radius:10px;background:#f5f3ff;display:flex;align-items:center;justify-content:center;font-size:1rem;">☁️</div>
          <div>
            <div style="font-family:'Sora',sans-serif;font-size:1rem;font-weight:800;">Software SaaS</div>
            <div style="font-size:.72rem;color:var(--text-muted);">61% of total discretionary budget</div>
          </div>
        </div>
      </div>
      <div class="bottom-card">
        <div class="bc-label">Pending Sync</div>
        <div style="display:flex;align-items:center;gap:8px;margin-top:4px;">
          <div style="font-family:'Sora',sans-serif;font-size:1.5rem;font-weight:800;color:var(--text-dark);">12</div>
          <span style="font-size:.82rem;font-weight:600;color:var(--text-muted);">Entries</span>
        </div>
        <button class="sync-btn" style="background:var(--accent-soft);border:1px solid #c7d2fe;color:var(--accent);margin-top:10px;border-radius:8px;padding:8px;width:100%;font-family:'DM Sans',sans-serif;font-size:.8rem;font-weight:700;cursor:pointer;">Sync with Bank</button>
      </div>
    </div>
  </div>
</div>
</body>
</html>