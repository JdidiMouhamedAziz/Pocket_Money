<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';

function formatCurrency($amount) {
    return '$' . number_format((float) $amount, 0, '.', ',');
}

function safePercent($value) {
    return min(100, max(0, (int) round($value)));
}

function buildChartPath(array $values, int $width = 500, int $height = 160, int $paddingTop = 20, int $paddingBottom = 20) {
    if (empty($values)) {
        return ['line' => '', 'area' => ''];
    }

    $count = count($values);
    $maxValue = max(1, max($values));
    $dx = $count > 1 ? $width / ($count - 1) : 0;
    $points = [];

    foreach ($values as $index => $value) {
        $x = round($index * $dx, 1);
        $scaled = min(1, max(0, $value / $maxValue));
        $y = round($paddingTop + ($height - $paddingTop - $paddingBottom) * (1 - $scaled), 1);
        $points[] = "$x,$y";
    }

    $line = 'M' . $points[0];
    foreach (array_slice($points, 1) as $point) {
        $line .= ' L' . $point;
    }

    $area = $line . ' L ' . $width . ',' . ($height - $paddingBottom) . ' L 0,' . ($height - $paddingBottom) . ' Z';
    return ['line' => $line, 'area' => $area];
}

$totalRevenueStmt = $pdo->query("SELECT COALESCE(SUM(amout), 0) AS totalRevenue FROM transaction WHERE transCategory = 'INCOME'");
$totalRevenue = (float) $totalRevenueStmt->fetchColumn();

$totalExpensesStmt = $pdo->query("SELECT COALESCE(SUM(amout), 0) AS totalExpenses FROM transaction WHERE transCategory = 'EXPENSE'");
$totalExpenses = (float) $totalExpensesStmt->fetchColumn();

$availableBalance = $totalRevenue - $totalExpenses;

$budgetTotalsStmt = $pdo->query("SELECT COALESCE(SUM(`limit`), 0) AS budgetLimit FROM budget");
$budgetLimit = (float) $budgetTotalsStmt->fetchColumn();

$budgetSpentStmt = $pdo->query("SELECT COALESCE(SUM(t.amout), 0) AS spent FROM transaction t INNER JOIN budgettransaction bt ON bt.transactionId = t.idTransaction WHERE t.transCategory = 'EXPENSE'");
$budgetSpent = (float) $budgetSpentStmt->fetchColumn();

$budgetUsagePct = $budgetLimit > 0 ? min(100, (int) round(($budgetSpent / $budgetLimit) * 100)) : 0;
$budgetLeft = max(0, $budgetLimit - $budgetSpent);
$budgetStatus = $budgetUsagePct >= 75 ? 'At Risk' : ($budgetUsagePct >= 40 ? 'On Track' : 'Healthy');

$transactionCountStmt = $pdo->query("SELECT COUNT(*) FROM transaction");
$transactionCount = (int) $transactionCountStmt->fetchColumn();

$userCountStmt = $pdo->query("SELECT COUNT(*) FROM users");
$userCount = (int) $userCountStmt->fetchColumn();

$expenseTrendStmt = $pdo->prepare("SELECT DATE_FORMAT(date, '%b %e') AS dateLabel, COALESCE(SUM(amout),0) AS amount FROM transaction WHERE transCategory = 'EXPENSE' AND date >= DATE_SUB(CURDATE(), INTERVAL 29 DAY) GROUP BY DATE(date) ORDER BY DATE(date) ASC");
$expenseTrendStmt->execute();
$expenseTrendRows = $expenseTrendStmt->fetchAll(PDO::FETCH_ASSOC);
if (empty($expenseTrendRows)) {
    for ($day = 29; $day >= 0; $day--) {
        $expenseTrendRows[] = ['dateLabel' => date('M j', strtotime("-{$day} days")), 'amount' => 0];
    }
}
$expenseTrendValues = array_map(fn($row) => (float) $row['amount'], $expenseTrendRows);
$expenseTrendPath = buildChartPath($expenseTrendValues);

$categoryExpenseStmt = $pdo->prepare("SELECT c.name AS categoryName, COALESCE(SUM(t.amout),0) AS amount FROM category c LEFT JOIN transaction t ON t.categoryId = c.idCategory AND t.transCategory = 'EXPENSE' GROUP BY c.idCategory, c.name ORDER BY amount DESC LIMIT 4");
$categoryExpenseStmt->execute();
$categoryExpenses = $categoryExpenseStmt->fetchAll(PDO::FETCH_ASSOC);
$totalCategoryExpense = max(1, array_sum(array_map(fn($row) => (float) $row['amount'], $categoryExpenses)));
$categoryColors = ['#4f46e5', '#3b82f6', '#f59e0b', '#10b981'];
$categorySegments = [];
$offset = 0;
$radius = 52;
$circumference = 2 * pi() * $radius;
foreach ($categoryExpenses as $index => $categoryRow) {
    $amount = (float) $categoryRow['amount'];
    $pct = $totalCategoryExpense > 0 ? $amount / $totalCategoryExpense : 0;
    $dash = round($circumference * $pct, 2);
    $categorySegments[] = [
        'color' => $categoryColors[$index % count($categoryColors)],
        'dash' => $dash,
        'offset' => round($offset, 2),
        'label' => $categoryRow['categoryName'] ?: 'Other',
        'pct' => round($pct * 100),
        'amount' => $amount,
    ];
    $offset += $dash;
}

$monthlyComparisonStmt = $pdo->prepare("SELECT DATE_FORMAT(date, '%b') AS monthLabel, SUM(CASE WHEN transCategory = 'INCOME' THEN amout ELSE 0 END) AS income, SUM(CASE WHEN transCategory = 'EXPENSE' THEN amout ELSE 0 END) AS expenses FROM transaction WHERE date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY DATE_FORMAT(date, '%Y-%m') ORDER BY MIN(date) ASC");
$monthlyComparisonStmt->execute();
$monthlyComparisonRows = $monthlyComparisonStmt->fetchAll(PDO::FETCH_ASSOC);

$monthlyLabels = array_column($monthlyComparisonRows, 'monthLabel');
$monthlyIncomes = array_map(fn($row) => (float) $row['income'], $monthlyComparisonRows);
$monthlyExpenses = array_map(fn($row) => (float) $row['expenses'], $monthlyComparisonRows);
$maxMonthly = max(1, max(array_merge($monthlyIncomes, $monthlyExpenses)));

$recentTransStmt = $pdo->prepare("SELECT t.date, u.name AS userName, u.lastName AS userLastName, c.name AS categoryName, t.transCategory, t.amout FROM transaction t LEFT JOIN users u ON u.id = t.userId LEFT JOIN category c ON c.idCategory = t.categoryId ORDER BY t.date DESC LIMIT 5");
$recentTransStmt->execute();
$recentTransactions = $recentTransStmt->fetchAll(PDO::FETCH_ASSOC);
?>

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
    <a class="nav-item active" href="dashboard.php"><span class="nav-icon">📊</span> Dashboard</a>
    <a class="nav-item" href="users.php"><span class="nav-icon">👥</span> Users</a>
    <a class="nav-item" href="budgets.php"><span class="nav-icon">🎯</span> Budgets</a>
    <a class="nav-item" href="transactions.php"><span class="nav-icon">💳</span> Transactions <span class="nav-badge"><?= number_format($transactionCount) ?></span></a>
    <a class="nav-item" href="categories.php"><span class="nav-icon">🗂️</span> Categories</a>
    <a class="nav-item" href="alerts.php"><span class="nav-icon">🔔</span> Alerts</a>
    <a class="nav-item" href="export_data.php"><span class="nav-icon">⬇️</span> Export Data</a>
    <a class="nav-item" href="profile.php"><span class="nav-icon">⚙️</span> Settings</a>
  </nav>
  <div class="sidebar-footer">
    <a class="btn-logout" href="/pocket_money/views/logout.php">Logout</a>
  </div>
</aside>

<!-- MAIN -->
<div class="main">
  <div class="content">
    <!-- PAGE HEADER -->
    <div class="page-header">
      <div>
        <h1>Dashboard Overview</h1>
        <p>Welcome back. Here's what's happening with your finances today.</p>
      </div>
      <div class="header-actions">
        <a class="btn-export" href="export_data.php?type=transactions">↑ Export</a>
      </div>
    </div>

    <!-- STAT CARDS -->
    <div class="stat-row">
      <!-- Total Revenue -->
      <div class="stat-card">
        <div class="stat-top">
          <div class="stat-icon-wrap si-blue">📈</div>
          <div class="stat-badge badge-green"><?= $transactionCount > 0 ? '▲ ' . safePercent($totalRevenue ? (($totalRevenue - $totalExpenses) / max(1, $totalRevenue)) * 100 : 0) . '%' : '—' ?></div>
        </div>
        <div class="stat-label">Total Revenue</div>
        <div class="stat-value"><?= htmlspecialchars(formatCurrency($totalRevenue), ENT_QUOTES, 'UTF-8') ?></div>
        <div class="sparkline">
          <?php
          $sparkValues = array_slice(array_reverse($expenseTrendValues), 0, 7);
          if (empty($sparkValues)) {
              $sparkValues = array_fill(0, 7, 0);
          }
          $maxSpark = max(1, max($sparkValues));
          foreach ($sparkValues as $value):
              $height = (int) round(($value / $maxSpark) * 100);
          ?>
            <div class="spark-bar" style="height:<?= $height ?>%;background:#bfdbfe"></div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Total Expenses -->
      <div class="stat-card">
        <div class="stat-top">
          <div class="stat-icon-wrap si-red">📉</div>
          <div class="stat-badge badge-red"><?= $transactionCount > 0 ? '▲ ' . safePercent($totalExpenses ? (($budgetSpent - $totalExpenses) / max(1, $totalExpenses)) * 100 : 0) . '%' : '—' ?></div>
        </div>
        <div class="stat-label">Total Expenses</div>
        <div class="stat-value"><?= htmlspecialchars(formatCurrency($totalExpenses), ENT_QUOTES, 'UTF-8') ?></div>
        <div class="sparkline">
          <?php
          $expenseSpark = array_slice(array_reverse($expenseTrendValues), 0, 7);
          if (empty($expenseSpark)) {
              $expenseSpark = array_fill(0, 7, 0);
          }
          $maxExpenseSpark = max(1, max($expenseSpark));
          foreach ($expenseSpark as $value):
              $height = (int) round(($value / $maxExpenseSpark) * 100);
          ?>
            <div class="spark-bar" style="height:<?= $height ?>%;background:#fecaca"></div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Available Balance -->
      <div class="stat-card">
        <div class="stat-top">
          <div class="stat-icon-wrap si-purple">💳</div>
          <div class="stat-badge badge-blue"><?= htmlspecialchars($availableBalance >= 0 ? 'Healthy' : 'Negative', ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <div class="stat-label">Available Balance</div>
        <div class="stat-value"><?= htmlspecialchars(formatCurrency($availableBalance), ENT_QUOTES, 'UTF-8') ?></div>
        <div class="sparkline">
          <?php
          $balanceSpark = array_slice(array_reverse($expenseTrendValues), 0, 7);
          if (empty($balanceSpark)) {
              $balanceSpark = array_fill(0, 7, 0);
          }
          $maxBalanceSpark = max(1, max($balanceSpark));
          foreach ($balanceSpark as $value):
              $height = (int) round(($value / $maxBalanceSpark) * 100);
          ?>
            <div class="spark-bar" style="height:<?= $height ?>%;background:#e9d5ff"></div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Budget Usage -->
      <div class="stat-card">
        <div class="stat-top">
          <div class="stat-icon-wrap si-orange">🎯</div>
          <div class="stat-badge badge-orange"><?= htmlspecialchars($budgetStatus, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <div class="stat-label">Budget Usage</div>
        <div class="gauge-wrap">
          <div class="gauge-pct"><?= htmlspecialchars($budgetUsagePct, ENT_QUOTES, 'UTF-8') ?>%</div>
          <div class="gauge-bg"><div class="gauge-fill" style="width:<?= htmlspecialchars($budgetUsagePct, ENT_QUOTES, 'UTF-8') ?>%"></div></div>
          <div class="gauge-sub"><span><?= htmlspecialchars(formatCurrency($budgetLeft), ENT_QUOTES, 'UTF-8') ?> left</span><span>Limit: <?= htmlspecialchars(formatCurrency($budgetLimit), ENT_QUOTES, 'UTF-8') ?></span></div>
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
          <path d="<?= htmlspecialchars($expenseTrendPath['area'], ENT_QUOTES, 'UTF-8') ?>" fill="url(#lineGrad)"/>
          <!-- line -->
          <path d="<?= htmlspecialchars($expenseTrendPath['line'], ENT_QUOTES, 'UTF-8') ?>" fill="none" stroke="#4f46e5" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
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
            <circle cx="70" cy="70" r="52" fill="none" stroke="#e5e7eb" stroke-width="22"/>
            <?php foreach ($categorySegments as $segment): ?>
              <circle cx="70" cy="70" r="52" fill="none" stroke="<?= htmlspecialchars($segment['color'], ENT_QUOTES, 'UTF-8') ?>" stroke-width="22"
                stroke-dasharray="<?= htmlspecialchars($segment['dash'], ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars(max(0, $circumference - $segment['dash']), ENT_QUOTES, 'UTF-8') ?>" stroke-dashoffset="-<?= htmlspecialchars($segment['offset'], ENT_QUOTES, 'UTF-8') ?>" transform="rotate(-90 70 70)"/>
            <?php endforeach; ?>
            <text x="70" y="65" text-anchor="middle" font-family="Sora,sans-serif" font-size="13" font-weight="800" fill="#111827"><?= htmlspecialchars(formatCurrency(array_sum(array_column($categoryExpenses, 'amount'))), ENT_QUOTES, 'UTF-8') ?></text>
            <text x="70" y="80" text-anchor="middle" font-family="DM Sans,sans-serif" font-size="9" fill="#6b7280">TOTAL</text>
          </svg>
          <div class="donut-legend" style="width:100%">
            <?php foreach ($categorySegments as $segment): ?>
              <div class="dl-item"><div class="dl-left"><div class="dl-dot" style="background:<?= htmlspecialchars($segment['color'], ENT_QUOTES, 'UTF-8') ?>"></div><span class="dl-name"><?= htmlspecialchars($segment['label'], ENT_QUOTES, 'UTF-8') ?></span></div><span class="dl-pct"><?= htmlspecialchars($segment['pct'], ENT_QUOTES, 'UTF-8') ?>%</span></div>
            <?php endforeach; ?>
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
        <?php foreach ($monthlyComparisonRows as $row):
            $incomeHeight = (int) round((floatval($row['income']) / max(1, $maxMonthly)) * 100);
            $expenseHeight = (int) round((floatval($row['expenses']) / max(1, $maxMonthly)) * 100);
        ?>
          <div class="bcm-group"><div class="bcm-bars"><div class="bcm-bar bar-blue" style="height:<?= $incomeHeight ?>px"></div><div class="bcm-bar bar-red" style="height:<?= $expenseHeight ?>px"></div></div><span class="bcm-label"><?= htmlspecialchars($row['monthLabel'], ENT_QUOTES, 'UTF-8') ?></span></div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- RECENT TRANSACTIONS -->
    <div class="tx-card">
      <div class="tx-header">
        <h3>Recent Transactions</h3>
        <a class="view-all" href="transactions.php">View All</a>
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
          <?php if (empty($recentTransactions)): ?>
            <tr><td colspan="5" style="padding:20px 0;text-align:center;color:#6b7280;">No recent activity to show.</td></tr>
          <?php else: ?>
            <?php foreach ($recentTransactions as $transaction):
              $isIncome = strtoupper($transaction['transCategory'] ?? '') === 'INCOME';
              $amountValue = (float) ($transaction['amout'] ?? 0);
              $userName = trim(($transaction['userName'] ?? '') . ' ' . ($transaction['userLastName'] ?? '')) ?: 'Unknown';
              $categoryName = $transaction['categoryName'] ?: 'Uncategorized';
            ?>
              <tr>
                <td class="td-date"><?= htmlspecialchars(date('M d, Y', strtotime($transaction['date'] ?? 'now')), ENT_QUOTES, 'UTF-8') ?></td>
                <td><div class="user-cell"><div class="user-cell-ava" style="background:<?= $isIncome ? '#059669' : '#4f46e5' ?>"><?= htmlspecialchars(substr($userName, 0, 2), ENT_QUOTES, 'UTF-8') ?></div><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></div></td>
                <td><span class="td-cat"><?= htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8') ?></span></td>
                <td><span class="type-pill <?= $isIncome ? 'type-income' : 'type-expense' ?>"><?= $isIncome ? '✓ Income' : '↗ Expense' ?></span></td>
                <td class="td-amount <?= $isIncome ? 'amt-pos' : 'amt-neg' ?>" style="text-align:right"><?= $isIncome ? '+' : '–' ?><?= htmlspecialchars(formatCurrency($amountValue), ENT_QUOTES, 'UTF-8') ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>