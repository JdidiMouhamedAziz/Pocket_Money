<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Finzo – Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet"/>
  <style>
    :root {
      --bg: #f4f5fb;
      --sidebar-bg: #2d2d3a;
      --sidebar-active: #7c6af5;
      --sidebar-text: #b0b3c6;
      --sidebar-label: #6b6e80;
      --white: #ffffff;
      --text-dark: #1a1a2e;
      --text-mid: #555770;
      --text-muted: #9295a8;
      --teal: #00c9a7;
      --purple: #7c6af5;
      --red: #ff6b8a;
      --yellow: #f5c842;
      --blue: #4c9be8;
      --green: #2ecc71;
      --orange: #ff7c3e;
      --card-radius: 14px;
      --shadow: 0 2px 16px rgba(0,0,0,.07);
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--bg);
      color: var(--text-dark);
      display: flex;
      min-height: 100vh;
    }
    h1,h2,h3,h4 { font-family: 'Sora', sans-serif; }

    /* ── SIDEBAR ── */
    .sidebar {
      width: 200px;
      flex-shrink: 0;
      background: var(--sidebar-bg);
      display: flex;
      flex-direction: column;
      padding: 28px 0 20px;
      min-height: 100vh;
      position: fixed;
      left: 0; top: 0; bottom: 0;
    }
    .sidebar-logo {
      display: flex; align-items: center; gap: 10px;
      padding: 0 22px; margin-bottom: 36px;
    }
    .logo-icon {
      width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0;
      background: conic-gradient(#f5c842 0deg 90deg, #00c9a7 90deg 180deg, #ff7c3e 180deg 270deg, #7c6af5 270deg 360deg);
      display: flex; align-items: center; justify-content: center;
      position: relative;
    }
    .logo-icon::before {
      content: ''; position: absolute;
      width: 26px; height: 26px; border-radius: 50%;
      background: var(--sidebar-bg);
    }
    .logo-icon span { position: relative; z-index: 1; font-size: .8rem; font-weight: 800; color: #fff; }
    .sidebar-logo h2 { font-size: 1.3rem; font-weight: 800; color: #fff; }

    .sidebar-section-label {
      font-size: .68rem; font-weight: 600; color: var(--sidebar-label);
      letter-spacing: .1em; text-transform: uppercase;
      padding: 0 22px; margin-bottom: 8px; margin-top: 10px;
    }
    .nav-item {
      display: flex; align-items: center; gap: 11px;
      padding: 11px 22px; cursor: pointer;
      color: var(--sidebar-text); font-size: .92rem; font-weight: 500;
      border-radius: 0; transition: background .15s, color .15s;
      text-decoration: none;
    }
    .nav-item:hover { background: rgba(255,255,255,.06); color: #fff; }
    .nav-item.active {
      background: var(--purple);
      color: #fff; font-weight: 700;
      border-radius: 10px;
      margin: 0 10px;
      padding: 11px 12px;
    }
    .nav-icon { font-size: 1rem; width: 20px; text-align: center; }

    .sidebar-spacer { flex: 1; }
    .sidebar-user {
      display: flex; align-items: center; gap: 10px;
      padding: 14px 22px;
      border-top: 1px solid rgba(255,255,255,.08);
    }
    .user-ava {
      width: 36px; height: 36px; border-radius: 50%;
      background: var(--purple); color: #fff;
      display: flex; align-items: center; justify-content: center;
      font-size: .78rem; font-weight: 700; flex-shrink: 0;
    }
    .user-name { font-size: .85rem; font-weight: 600; color: #fff; }
    .user-plan { font-size: .72rem; color: var(--sidebar-label); }

    /* ── MAIN ── */
    .main {
      margin-left: 200px;
      flex: 1;
      padding: 30px 28px;
      max-width: calc(100% - 200px);
    }

    /* TOPBAR */
    .topbar {
      display: flex; align-items: flex-start; justify-content: space-between;
      margin-bottom: 26px;
    }
    .topbar h1 { font-size: 1.5rem; font-weight: 800; color: var(--text-dark); }
    .topbar p  { font-size: .85rem; color: var(--text-muted); margin-top: 3px; }
    .topbar-right { display: flex; align-items: center; gap: 14px; }
    .btn-notif {
      width: 38px; height: 38px; border-radius: 50%;
      background: var(--white); border: 1px solid #e5e7ef;
      display: flex; align-items: center; justify-content: center;
      cursor: pointer; font-size: 1.1rem; box-shadow: var(--shadow);
    }
    .btn-add {
      background: var(--purple); color: #fff;
      border: none; border-radius: 10px; padding: 10px 18px;
      font-family: 'Sora', sans-serif; font-weight: 700; font-size: .85rem;
      cursor: pointer; display: flex; align-items: center; gap: 7px;
      box-shadow: 0 4px 14px rgba(124,106,245,.35);
      transition: opacity .2s, transform .15s;
    }
    .btn-add:hover { opacity: .9; transform: translateY(-1px); }

    /* STAT CARDS */
    .stat-row { display: grid; grid-template-columns: repeat(3,1fr); gap: 16px; margin-bottom: 22px; }
    .stat-card {
      background: var(--white); border-radius: var(--card-radius);
      padding: 18px 20px; box-shadow: var(--shadow);
      display: flex; flex-direction: column; gap: 6px;
    }
    .stat-label {
      font-size: .75rem; font-weight: 600; display: flex; align-items: center; gap: 6px;
    }
    .stat-icon { font-size: .95rem; }
    .stat-value { font-family: 'Sora', sans-serif; font-size: 1.5rem; font-weight: 800; }
    .card-balance .stat-label { color: var(--purple); }
    .card-balance .stat-value { color: var(--purple); }
    .card-balance { background: #f0edff; }
    .card-income .stat-label { color: var(--teal); }
    .card-income .stat-value { color: var(--teal); }
    .card-income { background: #e6faf6; }
    .card-expenses .stat-label { color: var(--red); }
    .card-expenses .stat-value { color: var(--red); }
    .card-expenses { background: #fff0f3; }

    /* CHARTS ROW */
    .charts-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 22px; }
    .chart-card {
      background: var(--white); border-radius: var(--card-radius);
      padding: 20px; box-shadow: var(--shadow);
    }
    .chart-card h4 { font-size: .95rem; font-weight: 700; margin-bottom: 14px; color: var(--text-dark); }
    .chart-legend-inline { display: flex; gap: 14px; margin-bottom: 12px; }
    .leg { display: flex; align-items: center; gap: 5px; font-size: .75rem; color: var(--text-muted); }
    .leg-dot { width: 8px; height: 8px; border-radius: 50%; }

    /* Bar chart */
    .bar-chart { display: flex; align-items: flex-end; gap: 6px; height: 90px; }
    .bc-group { display: flex; gap: 3px; align-items: flex-end; flex: 1; }
    .bc-bar { border-radius: 4px 4px 0 0; width: 50%; }
    .bc-income { background: #7c6af5; }
    .bc-expense { background: #ff6b8a; }

    /* Donut */
    .donut-wrap { display: flex; align-items: center; gap: 20px; }
    .donut-svg { flex-shrink: 0; }
    .donut-label { text-anchor: middle; dominant-baseline: middle; }
    .donut-legend { display: flex; flex-direction: column; gap: 6px; }
    .donut-leg-item { display: flex; align-items: center; gap: 7px; font-size: .78rem; color: var(--text-mid); }
    .donut-leg-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }

    /* RECENT TX */
    .section-card {
      background: var(--white); border-radius: var(--card-radius);
      padding: 22px; box-shadow: var(--shadow);
      margin-bottom: 22px;
    }
    .section-header {
      display: flex; justify-content: space-between; align-items: center;
      margin-bottom: 18px;
    }
    .section-header h3 { font-size: 1rem; font-weight: 700; }
    .section-header a { font-size: .82rem; color: var(--purple); text-decoration: none; font-weight: 600; }
    .tx-list { display: flex; flex-direction: column; gap: 2px; }
    .tx-item {
      display: flex; align-items: center; justify-content: space-between;
      padding: 10px 0;
      border-bottom: 1px solid #f2f3f8;
    }
    .tx-item:last-child { border-bottom: none; }
    .tx-left { display: flex; align-items: center; gap: 13px; }
    .tx-ava {
      width: 36px; height: 36px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: .9rem;
    }
    .tx-name { font-size: .92rem; font-weight: 600; color: var(--text-dark); }
    .tx-amount { font-family: 'Sora', sans-serif; font-size: .95rem; font-weight: 700; }
    .tx-neg { color: var(--red); }
    .tx-pos { color: var(--teal); }

    /* BUDGET PROGRESS */
    .budget-list { display: flex; flex-direction: column; gap: 14px; }
    .budget-row { }
    .budget-meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
    .budget-name { display: flex; align-items: center; gap: 8px; font-size: .88rem; font-weight: 600; color: var(--text-dark); }
    .budget-dot { width: 9px; height: 9px; border-radius: 50%; }
    .budget-amounts { font-size: .8rem; color: var(--text-muted); }
    .prog-bg { background: #ececf5; border-radius: 50px; height: 7px; }
    .prog-fill { height: 7px; border-radius: 50px; }
  </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <div class="logo-icon"><span>$</span></div>
    <h2>Finzo</h2>
  </div>

  <div class="sidebar-section-label">Main</div>
  <a class="nav-item active" href="#">
    <span class="nav-icon">🏠</span> Dashboard
  </a>
  <a class="nav-item" href="#">
    <span class="nav-icon">💳</span> Transactions
  </a>
  <a class="nav-item" href="#">
    <span class="nav-icon">🎯</span> Budgets
  </a>
  <a class="nav-item" href="#">
    <span class="nav-icon">🗂️</span> Categories
  </a>

  <div class="sidebar-section-label" style="margin-top:18px;">Collaboration</div>
  <a class="nav-item" href="#">
    <span class="nav-icon">👥</span> My Groups
  </a>
  <a class="nav-item" href="#">
    <span class="nav-icon">🔔</span> Alerts
  </a>

  <div class="sidebar-spacer"></div>
  <div class="sidebar-user">
    <div class="user-ava">KM</div>
    <div>
      <div class="user-name">Karim M.</div>
      <div class="user-plan">Free plan</div>
    </div>
  </div>
</aside>

<!-- MAIN CONTENT -->
<main class="main">

  <!-- Topbar -->
  <div class="topbar">
    <div>
      <h1>Welcome back, Karim</h1>
      <p>Here's your financial overview</p>
    </div>
    <div class="topbar-right">
      <div class="btn-notif">🔔</div>
      <button class="btn-add">＋ Add Transaction</button>
    </div>
  </div>

  <!-- Stat Cards -->
  <div class="stat-row">
    <div class="stat-card card-balance">
      <div class="stat-label"><span class="stat-icon">💜</span> Total Balance</div>
      <div class="stat-value">4280 TND</div>
    </div>
    <div class="stat-card card-income">
      <div class="stat-label"><span class="stat-icon">💚</span> Total Income</div>
      <div class="stat-value">6230 TND</div>
    </div>
    <div class="stat-card card-expenses">
      <div class="stat-label"><span class="stat-icon">❤️</span> Total Expenses</div>
      <div class="stat-value">2485 TND</div>
    </div>
  </div>

  <!-- Charts -->
  <div class="charts-row">
    <!-- Bar Chart -->
    <div class="chart-card">
      <h4>Income vs Expenses</h4>
      <div class="chart-legend-inline">
        <div class="leg"><div class="leg-dot" style="background:var(--purple)"></div>Income</div>
        <div class="leg"><div class="leg-dot" style="background:var(--red)"></div>Expenses</div>
      </div>
      <div class="bar-chart">
        <div class="bc-group"><div class="bc-bar bc-income" style="height:55%"></div><div class="bc-bar bc-expense" style="height:40%"></div></div>
        <div class="bc-group"><div class="bc-bar bc-income" style="height:70%"></div><div class="bc-bar bc-expense" style="height:50%"></div></div>
        <div class="bc-group"><div class="bc-bar bc-income" style="height:60%"></div><div class="bc-bar bc-expense" style="height:38%"></div></div>
        <div class="bc-group"><div class="bc-bar bc-income" style="height:85%"></div><div class="bc-bar bc-expense" style="height:60%"></div></div>
        <div class="bc-group"><div class="bc-bar bc-income" style="height:75%"></div><div class="bc-bar bc-expense" style="height:55%"></div></div>
        <div class="bc-group"><div class="bc-bar bc-income" style="height:90%"></div><div class="bc-bar bc-expense" style="height:65%"></div></div>
      </div>
    </div>

    <!-- Donut Chart -->
    <div class="chart-card">
      <h4>Spending by category</h4>
      <div class="donut-wrap">
        <svg class="donut-svg" width="120" height="120" viewBox="0 0 120 120">
          <circle cx="60" cy="60" r="46" fill="none" stroke="#f0edff" stroke-width="18"/>
          <!-- segments approximated via stroke-dasharray on a circumference ~289 -->
          <circle cx="60" cy="60" r="46" fill="none" stroke="#ff6b8a" stroke-width="18"
            stroke-dasharray="58 231" stroke-dashoffset="0" transform="rotate(-90 60 60)"/>
          <circle cx="60" cy="60" r="46" fill="none" stroke="#4c9be8" stroke-width="18"
            stroke-dasharray="52 237" stroke-dashoffset="-58" transform="rotate(-90 60 60)"/>
          <circle cx="60" cy="60" r="46" fill="none" stroke="#7c6af5" stroke-width="18"
            stroke-dasharray="62 227" stroke-dashoffset="-110" transform="rotate(-90 60 60)"/>
          <circle cx="60" cy="60" r="46" fill="none" stroke="#f5c842" stroke-width="18"
            stroke-dasharray="46 243" stroke-dashoffset="-172" transform="rotate(-90 60 60)"/>
          <circle cx="60" cy="60" r="46" fill="none" stroke="#00c9a7" stroke-width="18"
            stroke-dasharray="40 249" stroke-dashoffset="-218" transform="rotate(-90 60 60)"/>
          <circle cx="60" cy="60" r="46" fill="none" stroke="#ff7c3e" stroke-width="18"
            stroke-dasharray="31 258" stroke-dashoffset="-258" transform="rotate(-90 60 60)"/>
          <text x="60" y="57" text-anchor="middle" font-family="Sora,sans-serif" font-size="9" font-weight="700" fill="#555">Total</text>
          <text x="60" y="69" text-anchor="middle" font-family="Sora,sans-serif" font-size="11" font-weight="800" fill="#1a1a2e">6230</text>
        </svg>
        <div class="donut-legend">
          <div class="donut-leg-item"><div class="donut-leg-dot" style="background:#ff6b8a"></div>Health</div>
          <div class="donut-leg-item"><div class="donut-leg-dot" style="background:#4c9be8"></div>Transport</div>
          <div class="donut-leg-item"><div class="donut-leg-dot" style="background:#7c6af5"></div>Housing</div>
          <div class="donut-leg-item"><div class="donut-leg-dot" style="background:#f5c842"></div>Food</div>
          <div class="donut-leg-item"><div class="donut-leg-dot" style="background:#00c9a7"></div>Travelling</div>
          <div class="donut-leg-item"><div class="donut-leg-dot" style="background:#ff7c3e"></div>Other</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Transactions -->
  <div class="section-card">
    <div class="section-header">
      <h3>Recent Transactions</h3>
      <a href="#">See all →</a>
    </div>
    <div class="tx-list">
      <div class="tx-item">
        <div class="tx-left">
          <div class="tx-ava" style="background:#fff0f3">🛒</div>
          <span class="tx-name">Store</span>
        </div>
        <span class="tx-amount tx-neg">-20 TND</span>
      </div>
      <div class="tx-item">
        <div class="tx-left">
          <div class="tx-ava" style="background:#e6faf6">💼</div>
          <span class="tx-name">Salary</span>
        </div>
        <span class="tx-amount tx-pos">+1500 TND</span>
      </div>
      <div class="tx-item">
        <div class="tx-left">
          <div class="tx-ava" style="background:#fff0f3">💊</div>
          <span class="tx-name">Pharmacy</span>
        </div>
        <span class="tx-amount tx-neg">-50 TND</span>
      </div>
      <div class="tx-item">
        <div class="tx-left">
          <div class="tx-ava" style="background:#fff8e6">☕</div>
          <span class="tx-name">Coffee</span>
        </div>
        <span class="tx-amount tx-neg">-10 TND</span>
      </div>
    </div>
  </div>

  <!-- Budget Progress -->
  <div class="section-card">
    <div class="section-header">
      <h3>Budget Progress</h3>
      <a href="#" style="color:var(--purple)">Manage →</a>
    </div>
    <div class="budget-list">
      <div class="budget-row">
        <div class="budget-meta">
          <div class="budget-name"><div class="budget-dot" style="background:#ff6b8a"></div>Food</div>
          <div class="budget-amounts">80 TND – 100 TND</div>
        </div>
        <div class="prog-bg"><div class="prog-fill" style="width:80%; background:#ff6b8a"></div></div>
      </div>
      <div class="budget-row">
        <div class="budget-meta">
          <div class="budget-name"><div class="budget-dot" style="background:#f5c842"></div>Transport</div>
          <div class="budget-amounts">70 TND – 100 TND</div>
        </div>
        <div class="prog-bg"><div class="prog-fill" style="width:70%; background:#f5c842"></div></div>
      </div>
      <div class="budget-row">
        <div class="budget-meta">
          <div class="budget-name"><div class="budget-dot" style="background:#4c9be8"></div>Housing</div>
          <div class="budget-amounts">75 TND – 100 TND</div>
        </div>
        <div class="prog-bg"><div class="prog-fill" style="width:75%; background:#4c9be8"></div></div>
      </div>
      <div class="budget-row">
        <div class="budget-meta">
          <div class="budget-name"><div class="budget-dot" style="background:#00c9a7"></div>Health</div>
          <div class="budget-amounts">90 TND – 150 TND</div>
        </div>
        <div class="prog-bg"><div class="prog-fill" style="width:60%; background:#00c9a7"></div></div>
      </div>
      <div class="budget-row">
        <div class="budget-meta">
          <div class="budget-name"><div class="budget-dot" style="background:#7c6af5"></div>Coffee</div>
          <div class="budget-amounts">40 TND – 50 TND</div>
        </div>
        <div class="prog-bg"><div class="prog-fill" style="width:80%; background:#7c6af5"></div></div>
      </div>
    </div>
  </div>

</main>
</body>
</html>