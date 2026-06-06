<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/shared.php';

$currentUserId = $currentUser['id'] ?? ($_SESSION['user']['id'] ?? null);
$dashboardData = userDashboardData($currentUserId);
$summary = $dashboardData['summary'] ?? ['balance' => 0, 'income' => 0, 'expenses' => 0];
$recentTransactions = $dashboardData['recentTransactions'] ?? [];
$budgetRows = array_values(array_filter($dashboardData['budgetRows'] ?? [], function ($row) {
  return ((float) ($row['budget'] ?? 0)) > 0 || ((float) ($row['spent'] ?? 0)) > 0;
}));
$months = $dashboardData['months'] ?? [];
$categorySummary = $dashboardData['categorySummary'] ?? [];
$totalExpense = 0;
foreach ($categorySummary as $category) {
  $totalExpense += (float) ($category['spent'] ?? 0);
}
$topCategories = array_values(array_filter($categorySummary, function ($category) {
  return ((float) ($category['spent'] ?? 0)) > 0;
}));
$topCategories = array_slice($topCategories, 0, 5);
$displayName = $userName ?: trim(($currentUser['name'] ?? 'User') . ' ' . ($currentUser['lastName'] ?? ''));
$displayInitials = $userInitials ?: 'U';
?>

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
      display: flex; align-items: center; justify-content:space-between; gap: 10px;
      padding: 14px 22px;
      border-top: 1px solid rgba(255,255,255,.08);
    }
    .sidebar-user-info { display:flex; flex-direction:column; gap:4px; min-width:0; }
    .user-name a { color:#fff; text-decoration:none; }
    .btn-logout { background:var(--yellow); color:#7a5c00; border:none; border-radius:9px; padding:9px 12px; font-family:'Sora',sans-serif; font-weight:700; font-size:.78rem; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; }
    .btn-logout:hover { opacity:.9; }
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
      font-size: 1.1rem; box-shadow: var(--shadow); text-decoration: none; color: var(--text-dark);
    }
    .btn-add {
      background: var(--purple); color: #fff;
      border: none; border-radius: 10px; padding: 10px 18px;
      font-family: 'Sora', sans-serif; font-weight: 700; font-size: .85rem;
      display: inline-flex; align-items: center; gap: 7px;
      box-shadow: 0 4px 14px rgba(124,106,245,.35);
      transition: opacity .2s, transform .15s; text-decoration: none;
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
  <a class="nav-item active" href="<?= htmlspecialchars(userPageUrl('dashboard'), ENT_QUOTES, 'UTF-8') ?>">
    <span class="nav-icon">🏠</span> Dashboard
  </a>
  <a class="nav-item" href="<?= htmlspecialchars(userPageUrl('transaction'), ENT_QUOTES, 'UTF-8') ?>">
    <span class="nav-icon">💳</span> Transactions
  </a>
  <a class="nav-item" href="<?= htmlspecialchars(userPageUrl('budget'), ENT_QUOTES, 'UTF-8') ?>">
    <span class="nav-icon">🎯</span> Budgets
  </a>
  <a class="nav-item" href="<?= htmlspecialchars(userPageUrl('category'), ENT_QUOTES, 'UTF-8') ?>">
    <span class="nav-icon">🗂️</span> Categories
  </a>

  <div class="sidebar-section-label" style="margin-top:18px;">Collaboration</div>
  <a class="nav-item" href="<?= htmlspecialchars(userPageUrl('group'), ENT_QUOTES, 'UTF-8') ?>">
    <span class="nav-icon">👥</span> My Groups
  </a>
  <a class="nav-item" href="<?= htmlspecialchars(userPageUrl('alert'), ENT_QUOTES, 'UTF-8') ?>">
    <span class="nav-icon">🔔</span> Alerts
  </a>

  <div class="sidebar-spacer"></div>
  <div class="sidebar-user">
    <div class="user-ava"><?= htmlspecialchars($displayInitials, ENT_QUOTES, 'UTF-8') ?></div>
    <div class="sidebar-user-info">
      <div class="user-name"><a href="<?= htmlspecialchars(userPageUrl('profile'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?></a></div>
      <div class="user-plan"><?= htmlspecialchars(($currentUser['role'] ?? 'user') . ' plan', ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <a class="btn-logout" href="<?= htmlspecialchars(userPageUrl('logout'), ENT_QUOTES, 'UTF-8') ?>">Logout</a>
  </div>
</aside>

<!-- MAIN CONTENT -->
<main class="main">

  <!-- Topbar -->
  <div class="topbar">
    <div>
      <h1>Welcome back, <?= htmlspecialchars(explode(' ', trim($displayName))[0] ?? 'User', ENT_QUOTES, 'UTF-8') ?></h1>
      <p>Here's your financial overview</p>
    </div>
    <div class="topbar-right">
      <a class="btn-notif" href="<?= htmlspecialchars(userPageUrl('alert'), ENT_QUOTES, 'UTF-8') ?>" title="View alerts">🔔</a>
      <a class="btn-add" href="<?= htmlspecialchars(userPageUrl('transaction'), ENT_QUOTES, 'UTF-8') ?>">＋ Add Transaction</a>
    </div>
  </div>

  <!-- Stat Cards -->
  <div class="stat-row">
    <div class="stat-card card-balance">
      <div class="stat-label"><span class="stat-icon">💜</span> Total Balance</div>
      <div class="stat-value"><?= number_format((float) $summary['balance'], 0, '.', ',') ?> TND</div>
    </div>
    <div class="stat-card card-income">
      <div class="stat-label"><span class="stat-icon">💚</span> Total Income</div>
      <div class="stat-value"><?= number_format((float) $summary['income'], 0, '.', ',') ?> TND</div>
    </div>
    <div class="stat-card card-expenses">
      <div class="stat-label"><span class="stat-icon">❤️</span> Total Expenses</div>
      <div class="stat-value"><?= number_format((float) $summary['expenses'], 0, '.', ',') ?> TND</div>
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
      <?php
        if (empty($months)) {
          $months = [];
          for ($m = 5; $m >= 0; $m--) {
            $months[] = ['monthLabel' => date('M', strtotime("-{$m} months")), 'income' => 0, 'expenses' => 0];
          }
        }

        $maxValue = 1;
        foreach ($months as $month) {
          $maxValue = max($maxValue, (float) ($month['income'] ?? 0), (float) ($month['expenses'] ?? 0));
        }
      ?>
      <div class="bar-chart">
        <?php foreach ($months as $month): ?>
          <?php
            $income = (float) ($month['income'] ?? 0);
            $expense = (float) ($month['expenses'] ?? 0);
            $incomeHeight = (int) round(($income / $maxValue) * 100);
            $expenseHeight = (int) round(($expense / $maxValue) * 100);
          ?>
          <div class="bc-group">
            <div class="bc-bars">
              <div class="bc-bar bc-income" style="height:<?= $incomeHeight ?>%"></div>
              <div class="bc-bar bc-expense" style="height:<?= $expenseHeight ?>%"></div>
            </div>
            <div class="bc-label"><?= htmlspecialchars($month['monthLabel'], ENT_QUOTES, 'UTF-8') ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Donut Chart -->
    <div class="chart-card">
      <h4>Spending by category</h4>
      <div class="donut-wrap">
        <?php
          $donutRadius = 46;
          $donutCircumference = 2 * pi() * $donutRadius;
          $dashOffset = 0;
          $donutCategories = $topCategories;
          if ($totalExpense > 0 && count($donutCategories) < 5 && $totalExpense > array_sum(array_map(fn($cat) => (float) ($cat['spent'] ?? 0), $donutCategories))) {
            $otherAmount = $totalExpense - array_sum(array_map(fn($cat) => (float) ($cat['spent'] ?? 0), $donutCategories));
            if ($otherAmount > 0) {
              $donutCategories[] = ['name' => 'Other', 'spent' => $otherAmount];
            }
          }
          $donutCategories = array_values($donutCategories);
          if (empty($donutCategories)) {
            $donutCategories = [['name' => 'No expenses', 'spent' => 1]];
          }
        ?>
        <svg class="donut-svg" width="120" height="120" viewBox="0 0 120 120">
          <circle cx="60" cy="60" r="46" fill="none" stroke="#f0edff" stroke-width="18"/>
          <?php foreach ($donutCategories as $index => $category): ?>
            <?php
              $spent = (float) ($category['spent'] ?? 0);
              $segment = min(100, $totalExpense > 0 ? ($spent / max(1, $totalExpense)) * 100 : 100);
              $dash = ($segment / 100) * $donutCircumference;
              $color = ['#ff6b8a', '#4c9be8', '#7c6af5', '#f5c842', '#00c9a7', '#ff7c3e'][$index % 6];
              $dashStyle = $dash > 0 ? sprintf('%s %s', round($dash, 2), round(max(0, $donutCircumference - $dash), 2)) : '0 ' . round($donutCircumference, 2);
            ?>
            <circle cx="60" cy="60" r="46" fill="none" stroke="<?= $color ?>" stroke-width="18" stroke-dasharray="<?= $dashStyle ?>" stroke-dashoffset="-<?= round($dashOffset, 2) ?>" transform="rotate(-90 60 60)"/>
            <?php $dashOffset += $dash; ?>
          <?php endforeach; ?>
          <text x="60" y="57" text-anchor="middle" font-family="Sora,sans-serif" font-size="9" font-weight="700" fill="#555">Expenses</text>
          <text x="60" y="69" text-anchor="middle" font-family="Sora,sans-serif" font-size="11" font-weight="800" fill="#1a1a2e"><?= htmlspecialchars(number_format($totalExpense, 0, '.', ','), ENT_QUOTES, 'UTF-8') ?></text>
        </svg>
        <div class="donut-legend">
          <?php foreach ($donutCategories as $index => $category): ?>
            <?php $color = ['#ff6b8a', '#4c9be8', '#7c6af5', '#f5c842', '#00c9a7', '#ff7c3e'][$index % 6]; ?>
            <div class="donut-leg-item"><div class="donut-leg-dot" style="background:<?= $color ?>"></div><?= htmlspecialchars($category['name'] ?? 'Category', ENT_QUOTES, 'UTF-8') ?></div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Transactions -->
  <div class="section-card">
    <div class="section-header">
      <h3>Recent Transactions</h3>
      <a href="<?= htmlspecialchars(userPageUrl('transaction'), ENT_QUOTES, 'UTF-8') ?>">See all →</a>
    </div>
    <div class="tx-list">
      <?php if (empty($recentTransactions)): ?>
        <div class="tx-item">
          <div class="tx-left">
            <div class="tx-ava" style="background:#f5f6fc">·</div>
            <span class="tx-name">No transactions yet</span>
          </div>
          <span class="tx-amount tx-neg">0 TND</span>
        </div>
      <?php else: ?>
        <?php foreach (array_slice($recentTransactions, 0, 4) as $transaction): ?>
          <?php
            $isIncome = strtoupper((string) ($transaction['transCategory'] ?? '')) === 'INCOME';
            $amount = (float) ($transaction['amout'] ?? 0);
            $title = $transaction['description'] ?: ($transaction['categoryName'] ?: 'Transaction');
            $subtitle = $transaction['note'] ?: ($transaction['categoryName'] ?? '');
            $icon = userCategoryIcon($transaction['categoryName'] ?? $transaction['description'] ?? 'Transaction');
          ?>
          <div class="tx-item">
            <div class="tx-left">
              <div class="tx-ava" style="background:<?= $isIncome ? '#e6faf6' : '#fff0f3' ?>"><?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?></div>
              <div>
                <div class="tx-name"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></div>
                <div class="tx-desc" style="font-size:.75rem;color:var(--text-muted)"><?= htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8') ?></div>
              </div>
            </div>
            <span class="tx-amount <?= $isIncome ? 'tx-pos' : 'tx-neg' ?>"><?= $isIncome ? '+' : '-' ?><?= number_format($amount, 0, '.', ',') ?> TND</span>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Budget Progress -->
  <div class="section-card">
    <div class="section-header">
      <h3>Budget Progress</h3>
      <a href="<?= htmlspecialchars(userPageUrl('budget'), ENT_QUOTES, 'UTF-8') ?>" style="color:var(--purple)">Manage →</a>
    </div>
    <div class="budget-list">
      <?php if (empty($budgetRows)): ?>
        <div class="budget-row">
          <div class="budget-meta">
            <div class="budget-name"><div class="budget-dot" style="background:#c8beff"></div>No budgets yet</div>
            <div class="budget-amounts">0 TND – 0 TND</div>
          </div>
          <div class="prog-bg"><div class="prog-fill" style="width:0%; background:#c8beff"></div></div>
        </div>
      <?php else: ?>
        <?php foreach (array_slice($budgetRows, 0, 5) as $index => $row): ?>
          <?php
            $budget = (float) ($row['budget'] ?? 0);
            $spent = (float) ($row['spent'] ?? 0);
            $percent = $budget > 0 ? min(100, (int) round(($spent / $budget) * 100)) : 0;
            $color = userCategoryColor($index);
          ?>
          <div class="budget-row">
            <div class="budget-meta">
              <div class="budget-name"><div class="budget-dot" style="background:<?= $color ?>"></div><?= htmlspecialchars($row['name'] ?? 'Category', ENT_QUOTES, 'UTF-8') ?></div>
              <div class="budget-amounts"><?= number_format($spent, 0, '.', ',') ?> TND – <?= number_format($budget, 0, '.', ',') ?> TND</div>
            </div>
            <div class="prog-bg"><div class="prog-fill" style="width:<?= $percent ?>%; background:<?= $color ?>"></div></div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

</main>
</body>
</html>