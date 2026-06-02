<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>BudgetPro – Admin Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="/pocket_money/public/css/admin/dashboard.css">
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-header">
    <div class="logo-row">
      <div class="logo-mark">B</div>
      <div class="logo-text">
        <h2>BudgetPro</h2>
        <p>Collaborative Finance</p>
      </div>
    </div>
  </div>
  <div class="sidebar-search">
    <div class="search-box">
      <span class="search-icon">🔍</span>
      <input type="text" placeholder="Search analytics..."/>
    </div>
  </div>
  <nav class="sidebar-nav">
    <a class="nav-item active" href="#"><span class="nav-icon">📊</span> Dashboard</a>
    <a class="nav-item" href="#"><span class="nav-icon">👥</span> Users</a>
    <a class="nav-item" href="#"><span class="nav-icon">🎯</span> Budgets</a>
    <a class="nav-item" href="#"><span class="nav-icon">💳</span> Transactions <span class="nav-badge">4</span></a>
    <a class="nav-item" href="#"><span class="nav-icon">🗂️</span> Categories</a>
    <a class="nav-item" href="#"><span class="nav-icon">🔔</span> Alerts</a>
    <a class="nav-item" href="#"><span class="nav-icon">⚙️</span> Settings</a>
  </nav>
  <div class="sidebar-footer">
    <button class="new-report-btn">＋ New Report</button>
  </div>
</aside>

<!-- MAIN -->
<div class="main">
  <!-- TOP NAV -->
  <div class="topnav">
    <div class="topnav-search">
      <span style="color:#9ca3af;font-size:.9rem">🔍</span>
      <input type="text" placeholder="Search analytics..."/>
    </div>
    <div class="topnav-right">
      <div class="notif-btn">🔔<div class="notif-dot"></div></div>
      <div class="profile-btn">
        <div class="profile-ava">JD</div>
        <span>Profile Settings</span>
      </div>
    </div>
  </div>

  <div class="content">
    <!-- PAGE HEADER -->
    <div class="page-header">
      <div>
        <h1>Dashboard Overview</h1>
        <p>Welcome back. Here's what's happening with your finances today.</p>
      </div>
      <div class="header-actions">
        <button class="btn-period">📅 This Month</button>
        <button class="btn-export">↑ Export</button>
      </div>
    </div>

    <!-- STAT CARDS -->
    <div class="stat-row">
      <!-- Total Revenue -->
      <div class="stat-card">
        <div class="stat-top">
          <div class="stat-icon-wrap si-blue">📈</div>
          <div class="stat-badge badge-green">▲ 12.5%</div>
        </div>
        <div class="stat-label">Total Revenue</div>
        <div class="stat-value">$124,500</div>
        <div class="sparkline">
          <div class="spark-bar" style="height:30%;background:#bfdbfe"></div>
          <div class="spark-bar" style="height:45%;background:#bfdbfe"></div>
          <div class="spark-bar" style="height:35%;background:#bfdbfe"></div>
          <div class="spark-bar" style="height:55%;background:#93c5fd"></div>
          <div class="spark-bar" style="height:50%;background:#93c5fd"></div>
          <div class="spark-bar" style="height:70%;background:#60a5fa"></div>
          <div class="spark-bar" style="height:90%;background:#3b82f6"></div>
        </div>
      </div>

      <!-- Total Expenses -->
      <div class="stat-card">
        <div class="stat-top">
          <div class="stat-icon-wrap si-red">📉</div>
          <div class="stat-badge badge-red">▲ 4.2%</div>
        </div>
        <div class="stat-label">Total Expenses</div>
        <div class="stat-value">$45,200</div>
        <div class="sparkline">
          <div class="spark-bar" style="height:40%;background:#fecaca"></div>
          <div class="spark-bar" style="height:60%;background:#fecaca"></div>
          <div class="spark-bar" style="height:35%;background:#fca5a5"></div>
          <div class="spark-bar" style="height:70%;background:#fca5a5"></div>
          <div class="spark-bar" style="height:45%;background:#f87171"></div>
          <div class="spark-bar" style="height:55%;background:#f87171"></div>
          <div class="spark-bar" style="height:65%;background:#ef4444"></div>
        </div>
      </div>

      <!-- Available Balance -->
      <div class="stat-card">
        <div class="stat-top">
          <div class="stat-icon-wrap si-purple">💳</div>
          <div class="stat-badge badge-blue">Healthy</div>
        </div>
        <div class="stat-label">Available Balance</div>
        <div class="stat-value">$79,300</div>
        <div class="sparkline">
          <div class="spark-bar" style="height:50%;background:#e9d5ff"></div>
          <div class="spark-bar" style="height:55%;background:#d8b4fe"></div>
          <div class="spark-bar" style="height:65%;background:#c4b5fd"></div>
          <div class="spark-bar" style="height:60%;background:#a78bfa"></div>
          <div class="spark-bar" style="height:75%;background:#8b5cf6"></div>
          <div class="spark-bar" style="height:80%;background:#7c3aed"></div>
          <div class="spark-bar" style="height:90%;background:#6d28d9"></div>
        </div>
      </div>

      <!-- Budget Usage -->
      <div class="stat-card">
        <div class="stat-top">
          <div class="stat-icon-wrap si-orange">🎯</div>
          <div class="stat-badge badge-orange">On Track</div>
        </div>
        <div class="stat-label">Budget Usage</div>
        <div class="gauge-wrap">
          <div class="gauge-pct">62%</div>
          <div class="gauge-bg"><div class="gauge-fill" style="width:62%"></div></div>
          <div class="gauge-sub"><span>$26,000 left</span><span>Limit: $72,200</span></div>
        </div>
      </div>
    </div>

    <!-- CHART ROW -->
    <div class="charts-row">
      <!-- Line Chart -->
      <div class="chart-card">
        <div class="chart-header">
          <div>
            <h3>Expenses Over Time</h3>
            <p>Daily financial outflow monitoring</p>
          </div>
          <div class="chip">Last 30 Days ▾</div>
        </div>
        <svg width="100%" height="160" viewBox="0 0 500 160" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <linearGradient id="lineGrad" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stop-color="#4f46e5" stop-opacity="0.18"/>
              <stop offset="100%" stop-color="#4f46e5" stop-opacity="0"/>
            </linearGradient>
          </defs>
          <!-- grid lines -->
          <line x1="0" y1="40" x2="500" y2="40" stroke="#f3f4f6" stroke-width="1"/>
          <line x1="0" y1="80" x2="500" y2="80" stroke="#f3f4f6" stroke-width="1"/>
          <line x1="0" y1="120" x2="500" y2="120" stroke="#f3f4f6" stroke-width="1"/>
          <!-- area fill -->
          <path d="M0,120 C40,110 60,90 100,95 C140,100 160,130 200,100 C240,70 260,40 300,30 C340,20 360,60 400,55 C440,50 470,70 500,65 L500,160 L0,160 Z" fill="url(#lineGrad)"/>
          <!-- line -->
          <path d="M0,120 C40,110 60,90 100,95 C140,100 160,130 200,100 C240,70 260,40 300,30 C340,20 360,60 400,55 C440,50 470,70 500,65" fill="none" stroke="#4f46e5" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
          <!-- peak dot -->
          <circle cx="300" cy="30" r="5" fill="#fff" stroke="#4f46e5" stroke-width="2.5"/>
        </svg>
      </div>

      <!-- Donut -->
      <div class="chart-card">
        <div class="chart-header">
          <div>
            <h3>Expenses by Category</h3>
            <p>Allocation across segments</p>
          </div>
        </div>
        <div class="donut-wrap">
          <svg width="140" height="140" viewBox="0 0 140 140">
            <!-- Housing 42% -->
            <circle cx="70" cy="70" r="52" fill="none" stroke="#e5e7eb" stroke-width="22"/>
            <circle cx="70" cy="70" r="52" fill="none" stroke="#4f46e5" stroke-width="22"
              stroke-dasharray="136 191" stroke-dashoffset="0" transform="rotate(-90 70 70)"/>
            <!-- Marketing 28% -->
            <circle cx="70" cy="70" r="52" fill="none" stroke="#3b82f6" stroke-width="22"
              stroke-dasharray="91 236" stroke-dashoffset="-136" transform="rotate(-90 70 70)"/>
            <!-- SaaS 18% -->
            <circle cx="70" cy="70" r="52" fill="none" stroke="#f59e0b" stroke-width="22"
              stroke-dasharray="58 269" stroke-dashoffset="-227" transform="rotate(-90 70 70)"/>
            <!-- other 12% -->
            <circle cx="70" cy="70" r="52" fill="none" stroke="#e5e7eb" stroke-width="22"
              stroke-dasharray="39 288" stroke-dashoffset="-285" transform="rotate(-90 70 70)"/>
            <text x="70" y="65" text-anchor="middle" font-family="Sora,sans-serif" font-size="13" font-weight="800" fill="#111827">$45.2k</text>
            <text x="70" y="80" text-anchor="middle" font-family="DM Sans,sans-serif" font-size="9" fill="#6b7280">TOTAL</text>
          </svg>
          <div class="donut-legend" style="width:100%">
            <div class="dl-item"><div class="dl-left"><div class="dl-dot" style="background:#4f46e5"></div><span class="dl-name">Housing</span></div><span class="dl-pct">42%</span></div>
            <div class="dl-item"><div class="dl-left"><div class="dl-dot" style="background:#3b82f6"></div><span class="dl-name">Marketing</span></div><span class="dl-pct">28%</span></div>
            <div class="dl-item"><div class="dl-left"><div class="dl-dot" style="background:#f59e0b"></div><span class="dl-name">SaaS Tools</span></div><span class="dl-pct">18%</span></div>
          </div>
        </div>
      </div>
    </div>

    <!-- MONTHLY COMPARISON -->
    <div class="chart-card monthly-row">
      <div class="chart-header">
        <div>
          <h3>Monthly Comparison</h3>
          <p>Income vs Expenses performance</p>
        </div>
        <div class="legend-row" style="margin:0">
          <div class="leg"><div class="leg-dot" style="background:#4f46e5"></div>Income</div>
          <div class="leg"><div class="leg-dot" style="background:#ef4444"></div>Expenses</div>
        </div>
      </div>
      <div class="bar-chart-monthly" style="margin-top:18px;height:130px;align-items:flex-end;display:flex;gap:14px;">
        <!-- Jan -->
        <div class="bcm-group"><div class="bcm-bars"><div class="bcm-bar bar-blue" style="height:70px"></div><div class="bcm-bar bar-red" style="height:30px"></div></div><span class="bcm-label">Jan</span></div>
        <!-- Feb -->
        <div class="bcm-group"><div class="bcm-bars"><div class="bcm-bar bar-blue" style="height:90px"></div><div class="bcm-bar bar-red" style="height:50px"></div></div><span class="bcm-label">Feb</span></div>
        <!-- Mar -->
        <div class="bcm-group"><div class="bcm-bars"><div class="bcm-bar bar-blue" style="height:65px"></div><div class="bcm-bar bar-red" style="height:40px"></div></div><span class="bcm-label">Mar</span></div>
        <!-- Apr -->
        <div class="bcm-group"><div class="bcm-bars"><div class="bcm-bar bar-blue" style="height:80px"></div><div class="bcm-bar bar-red" style="height:35px"></div></div><span class="bcm-label">Apr</span></div>
        <!-- May -->
        <div class="bcm-group"><div class="bcm-bars"><div class="bcm-bar bar-blue" style="height:60px"></div><div class="bcm-bar bar-red" style="height:45px"></div></div><span class="bcm-label">May</span></div>
        <!-- Jun -->
        <div class="bcm-group"><div class="bcm-bars"><div class="bcm-bar bar-blue" style="height:100px"></div><div class="bcm-bar bar-red" style="height:55px"></div></div><span class="bcm-label">Jun</span></div>
        <!-- Jul -->
        <div class="bcm-group"><div class="bcm-bars"><div class="bcm-bar bar-blue" style="height:110px"></div><div class="bcm-bar bar-red" style="height:40px"></div></div><span class="bcm-label">Jul</span></div>
      </div>
    </div>

    <!-- RECENT TRANSACTIONS -->
    <div class="tx-card">
      <div class="tx-header">
        <h3>Recent Transactions</h3>
        <a class="view-all">View All</a>
      </div>
      <table>
        <thead>
          <tr>
            <th>Date</th>
            <th>User</th>
            <th>Category</th>
            <th>Type</th>
            <th style="text-align:right">Amount</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="td-date">Oct 24, 2023</td>
            <td><div class="user-cell"><div class="user-cell-ava" style="background:#4f46e5">JD</div>Jane Doe</div></td>
            <td><span class="td-cat">Marketing</span></td>
            <td><span class="type-pill type-expense">↗ Expense</span></td>
            <td class="td-amount amt-neg" style="text-align:right">–$1,240.00</td>
          </tr>
          <tr>
            <td class="td-date">Oct 23, 2023</td>
            <td><div class="user-cell"><div class="user-cell-ava" style="background:#059669">AS</div>Alex Smith</div></td>
            <td><span class="td-cat">Sales</span></td>
            <td><span class="type-pill type-income">✓ Income</span></td>
            <td class="td-amount amt-pos" style="text-align:right">+$12,500.00</td>
          </tr>
          <tr>
            <td class="td-date">Oct 22, 2023</td>
            <td><div class="user-cell"><div class="user-cell-ava" style="background:#f59e0b">RK</div>Ryan Kim</div></td>
            <td><span class="td-cat">Infrastructure</span></td>
            <td><span class="type-pill type-expense">↗ Expense</span></td>
            <td class="td-amount amt-neg" style="text-align:right">–$3,450.00</td>
          </tr>
          <tr>
            <td class="td-date">Oct 21, 2023</td>
            <td><div class="user-cell"><div class="user-cell-ava" style="background:#4f46e5">JD</div>Jane Doe</div></td>
            <td><span class="td-cat">Software</span></td>
            <td><span class="type-pill type-expense">↗ Expense</span></td>
            <td class="td-amount amt-neg" style="text-align:right">–$820.00</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>