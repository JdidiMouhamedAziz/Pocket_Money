<?php
require_once __DIR__ . '/admin_helpers.php';

$search = trim($_GET['search'] ?? '');
$filterCategory = (int) ($_GET['filterCategory'] ?? 0);
$filterType = strtoupper(trim($_GET['filterType'] ?? ''));
$dateFrom = trim($_GET['dateFrom'] ?? '');
$dateTo = trim($_GET['dateTo'] ?? '');
$whereClauses = [];
$queryParams = [];
if ($search !== '') {
    $whereClauses[] = '(t.description LIKE ? OR t.note LIKE ? OR u.name LIKE ? OR u.lastName LIKE ? OR c.name LIKE ?)';
    $likeSearch = "%{$search}%";
    $queryParams = array_merge($queryParams, [$likeSearch, $likeSearch, $likeSearch, $likeSearch, $likeSearch]);
}
if ($filterCategory > 0) {
    $whereClauses[] = 't.categoryId = ?';
    $queryParams[] = $filterCategory;
}
if (in_array($filterType, ['EXPENSE', 'INCOME'], true)) {
    $whereClauses[] = 't.transCategory = ?';
    $queryParams[] = $filterType;
}
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
    $whereClauses[] = 't.date >= ?';
    $queryParams[] = $dateFrom;
}
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
    $whereClauses[] = 't.date <= ?';
    $queryParams[] = $dateTo;
}
$sql = "SELECT t.*, u.name AS userName, u.lastName AS userLastName, c.name AS categoryName, c.type AS categoryType
     FROM transaction t
     LEFT JOIN users u ON u.id = t.userId
     LEFT JOIN category c ON c.idCategory = t.categoryId";
if ($whereClauses) {
    $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
}
$sql .= ' ORDER BY t.date DESC LIMIT 50';
$transactionStmt = $pdo->prepare($sql);
$transactionStmt->execute($queryParams);
$transactions = $transactionStmt->fetchAll(PDO::FETCH_ASSOC);
$totalTransactions = (int) $pdo->query("SELECT COUNT(*) FROM transaction")->fetchColumn();
$thisMonthSpendStmt = $pdo->prepare(
    "SELECT COALESCE(SUM(amout),0) FROM transaction WHERE transCategory='EXPENSE' AND YEAR(date)=YEAR(CURDATE()) AND MONTH(date)=MONTH(CURDATE())"
);
$thisMonthSpendStmt->execute();
$thisMonthSpend = (float) $thisMonthSpendStmt->fetchColumn();
$topCategoryStmt = $pdo->prepare(
    "SELECT c.name, COALESCE(SUM(t.amout),0) AS totalSpent FROM category c
     LEFT JOIN transaction t ON t.categoryId=c.idCategory AND t.transCategory='EXPENSE'
     GROUP BY c.idCategory ORDER BY totalSpent DESC LIMIT 1"
);
$topCategoryStmt->execute();
$topCategoryRow = $topCategoryStmt->fetch(PDO::FETCH_ASSOC);
$topCategoryName = $topCategoryRow['name'] ?? 'Uncategorized';
$pendingSyncCount = (int) $pdo->query(
    "SELECT COUNT(*) FROM transaction t LEFT JOIN budgettransaction bt ON t.idTransaction = bt.transactionId WHERE bt.transactionId IS NULL AND t.transCategory='EXPENSE'"
)->fetchColumn();

$adminFlash = getAdminFlash();
$categories = $pdo->query('SELECT idCategory,name FROM category ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
$budgets = $pdo->query('SELECT idBudget,name FROM budget ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
$editTransaction = null;
if (!empty($_GET['editTransaction'])) {
    $stmt = $pdo->prepare(
        "SELECT t.*, bt.budgetId FROM transaction t LEFT JOIN budgettransaction bt ON bt.transactionId = t.idTransaction WHERE t.idTransaction = ? LIMIT 1"
    );
    $stmt->execute([$_GET['editTransaction']]);
    $editTransaction = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
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
    .btn-logout{width:100%;background:var(--yellow);color:#7a5c00;border:none;border-radius:8px;padding:9px;font-family:'Sora',sans-serif;font-weight:700;font-size:.8rem;cursor:pointer;text-align:center;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:5px;}
    .btn-logout:hover{opacity:.9;}
    .main{margin-left:200px;flex:1;display:flex;flex-direction:column;}
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
    .admin-flash{background:#eef6ff;border:1px solid #dbeafe;color:#1e40af;border-radius:12px;padding:14px 18px;margin-bottom:18px;font-weight:600;}
    .admin-flash.error{background:#fef2f2;border-color:#fecaca;color:#b91c1c;}
    .form-card{background:var(--white);border:1px solid var(--border);border-radius:18px;padding:18px;margin-bottom:20px;box-shadow:var(--shadow);}
    .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:14px;}
    .form-field{display:flex;flex-direction:column;gap:6px;}
    .form-field.full{grid-column:1/-1;}
    .form-input{background:#f9fafb;border:1px solid var(--border);border-radius:10px;padding:10px 12px;font-family:'DM Sans',sans-serif;font-size:.86rem;color:var(--text-dark);outline:none;}
    .form-input:focus{border-color:var(--accent);background:#fff;}
    .form-actions{display:flex;justify-content:flex-end;gap:10px;align-items:center;margin-top:14px;}
    .btn-submit{background:var(--accent);color:#fff;border:none;border-radius:10px;padding:10px 16px;font-family:'Sora',sans-serif;font-weight:700;font-size:.85rem;cursor:pointer;}
    .btn-secondary{background:#f3f4f6;color:var(--text-dark);border:none;border-radius:10px;padding:10px 16px;font-family:'DM Sans',sans-serif;font-weight:700;font-size:.85rem;cursor:pointer;}
    /* MAIN TABLE CARD */
    .tx-card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);margin-bottom:18px;}
    table{width:100%;border-collapse:collapse;}
    thead th{font-size:.65rem;font-weight:700;color:var(--text-light);text-transform:uppercase;letter-spacing:.07em;padding:10px 20px;text-align:left;background:#fafafa;border-bottom:1px solid var(--border);}
    tbody tr{border-bottom:1px solid #f3f4f6;transition:background .12s;cursor:pointer;}
    tbody tr:last-child{border-bottom:none;}
    tbody tr:hover{background:#fafbff;}
    td{padding:12px 20px;vertical-align:middle;}
    .action-btn{display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:8px;background:#f3f4f6;color:var(--text-mid);border:1px solid var(--border);font-size:.82rem;cursor:pointer;text-decoration:none;margin-right:4px;}
    .action-btn:hover{background:#eef2ff;color:var(--accent);}
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
    <a class="nav-item" href="dashboard.php"><span class="nav-icon">📊</span> Dashboard</a>
    <a class="nav-item" href="users.php"><span class="nav-icon">👥</span> Users</a>
    <a class="nav-item" href="budgets.php"><span class="nav-icon">🎯</span> Budgets</a>
    <a class="nav-item active" href="transactions.php"><span class="nav-icon">💳</span> Transactions</a>
    <a class="nav-item" href="categories.php"><span class="nav-icon">🗂️</span> Categories</a>
    <a class="nav-item" href="alerts.php"><span class="nav-icon">🔔</span> Alerts</a>
    <a class="nav-item" href="export_data.php"><span class="nav-icon">⬇️</span> Export Data</a>
    <a class="nav-item" href="profile.php"><span class="nav-icon">⚙️</span> Settings</a>
  </nav>
  <div class="sidebar-spacer"></div>
  <div class="sidebar-footer">
    <a class="btn-logout" href="/pocket_money/views/logout.php">Logout</a>
  </div>
</aside>

<div class="main">
  <div class="content">
    <div class="page-header">
      <div>
        <h1>Transactions</h1>
        <p>Review and manage collaborative financial activity.</p>
      </div>
      <div class="header-btns">
        <a class="btn-export" href="export_data.php?type=transactions">↑ Export</a>
        <a class="btn-export" href="export_data.php?download=1&type=transactions">CSV</a>
      </div>
    </div>

    <!-- FILTER BAR -->
    <div class="filter-bar">
      <form method="get" action="transactions.php" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr auto;gap:10px;align-items:end;width:100%;">
        <div class="filter-group" style="margin-bottom:0;">
          <div class="filter-label">Search</div>
          <input class="filter-input" id="search" name="search" type="text" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Search description, user, category" />
        </div>
        <div class="filter-group" style="margin-bottom:0;">
          <div class="filter-label">Date from</div>
          <input class="filter-input" id="dateFrom" name="dateFrom" type="date" value="<?= htmlspecialchars($dateFrom, ENT_QUOTES, 'UTF-8') ?>" />
        </div>
        <div class="filter-group" style="margin-bottom:0;">
          <div class="filter-label">Date to</div>
          <input class="filter-input" id="dateTo" name="dateTo" type="date" value="<?= htmlspecialchars($dateTo, ENT_QUOTES, 'UTF-8') ?>" />
        </div>
        <div class="filter-group" style="margin-bottom:0;">
          <div class="filter-label">Category</div>
          <select class="filter-select" id="filterCategory" name="filterCategory">
            <option value="0">All Categories</option>
            <?php foreach ($categories as $category): ?>
              <option value="<?= htmlspecialchars($category['idCategory'], ENT_QUOTES, 'UTF-8') ?>" <?= $filterCategory === (int)$category['idCategory'] ? 'selected' : '' ?>><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="filter-group" style="margin-bottom:0;display:flex;flex-direction:column;gap:4px;">
          <div class="filter-label">Type</div>
          <select class="filter-select" id="filterType" name="filterType">
            <option value="">All types</option>
            <option value="EXPENSE" <?= $filterType === 'EXPENSE' ? 'selected' : '' ?>>Expense</option>
            <option value="INCOME" <?= $filterType === 'INCOME' ? 'selected' : '' ?>>Income</option>
          </select>
        </div>
        <div style="display:flex;gap:10px;align-items:center;">
          <button type="submit" class="btn-apply">✦ Apply Filters</button>
          <a class="btn-secondary" href="transactions.php" style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center;">Reset</a>
        </div>
      </form>
    </div>

    <?php if ($adminFlash): ?>
      <div class="admin-flash <?= $adminFlash['success'] ? '' : 'error' ?>"><?= htmlspecialchars($adminFlash['message'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div id="transaction-form" class="form-card">
      <h3><?= $editTransaction ? 'Edit Transaction' : 'New Transaction' ?></h3>
      <form method="post" action="admin_actions.php?resource=transaction&action=<?= $editTransaction ? 'update' : 'create' ?>">
        <?php if ($editTransaction): ?>
          <input type="hidden" name="id" value="<?= htmlspecialchars($editTransaction['idTransaction'], ENT_QUOTES, 'UTF-8') ?>"/>
        <?php endif; ?>
        <div class="form-grid">
          <div class="form-field">
            <label for="txn-description">Description</label>
            <input class="form-input" id="txn-description" name="description" type="text" value="<?= htmlspecialchars($editTransaction['description'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required/>
          </div>
          <div class="form-field">
            <label for="txn-amount">Amount</label>
            <input class="form-input" id="txn-amount" name="amount" type="number" step="0.01" value="<?= htmlspecialchars($editTransaction['amout'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required/>
          </div>
          <div class="form-field">
            <label for="txn-category">Category</label>
            <select class="form-input" id="txn-category" name="categoryId" required>
              <option value="">Select category</option>
              <?php foreach ($categories as $category): ?>
                <option value="<?= htmlspecialchars($category['idCategory'], ENT_QUOTES, 'UTF-8') ?>" <?= isset($editTransaction['categoryId']) && $editTransaction['categoryId'] == $category['idCategory'] ? 'selected' : '' ?>><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-field">
            <label for="txn-budget">Budget</label>
            <select class="form-input" id="txn-budget" name="budgetId" required>
              <option value="">Select budget</option>
              <?php foreach ($budgets as $budget): ?>
                <option value="<?= htmlspecialchars($budget['idBudget'], ENT_QUOTES, 'UTF-8') ?>" <?= isset($editTransaction['budgetId']) && $editTransaction['budgetId'] == $budget['idBudget'] ? 'selected' : '' ?>><?= htmlspecialchars($budget['name'], ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-field">
            <label for="txn-date">Date</label>
            <input class="form-input" id="txn-date" name="date" type="date" value="<?= htmlspecialchars($editTransaction['date'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>" required/>
          </div>
          <div class="form-field">
            <label for="txn-type">Type</label>
            <select class="form-input" id="txn-type" name="transCategory" required>
              <option value="EXPENSE" <?= isset($editTransaction['transCategory']) && strtoupper($editTransaction['transCategory']) === 'EXPENSE' ? 'selected' : '' ?>>Expense</option>
              <option value="INCOME" <?= isset($editTransaction['transCategory']) && strtoupper($editTransaction['transCategory']) === 'INCOME' ? 'selected' : '' ?>>Income</option>
            </select>
          </div>
          <div class="form-field full">
            <label for="txn-note">Note</label>
            <textarea class="form-input" id="txn-note" name="note" rows="3"><?= htmlspecialchars($editTransaction['note'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn-submit"><?= $editTransaction ? 'Update Transaction' : 'Create Transaction' ?></button>
          <?php if ($editTransaction): ?>
            <a href="transactions.php" class="btn-secondary">Cancel</a>
          <?php endif; ?>
        </div>
      </form>
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
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($transactions)): ?>
            <tr><td colspan="6" style="padding:20px 0;text-align:center;color:#6b7280;">No transactions found.</td></tr>
          <?php else: ?>
            <?php foreach ($transactions as $tx):
              $isIncome = strtoupper($tx['transCategory'] ?? '') === 'INCOME';
              $userName = trim(($tx['userName'] ?? '') . ' ' . ($tx['userLastName'] ?? '')) ?: 'Unknown';
              $categoryName = $tx['categoryName'] ?: ($tx['categoryType'] === 'EXPENSE' ? 'Expense' : 'Income');
              $amount = (float) $tx['amout'];
            ?>
              <tr>
                <td><div class="type-dot <?= $isIncome ? 'type-income-dot' : 'type-expense-dot' ?>"><?= $isIncome ? '✓' : '↗' ?></div></td>
                <td>
                  <div class="tx-name"><?= htmlspecialchars($tx['description'] ?? 'Transaction', ENT_QUOTES, 'UTF-8') ?></div>
                  <div class="tx-id"><?= htmlspecialchars('TX-' . ($tx['idTransaction'] ?? '000'), ENT_QUOTES, 'UTF-8') ?></div>
                </td>
                <td><span class="td-cat <?= $isIncome ? 'income' : 'software' ?>"><?= htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8') ?></span></td>
                <td class="td-date"><?= htmlspecialchars(formatDate($tx['date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <div class="user-cell">
                    <div class="user-ava" style="background:<?= htmlspecialchars(categoryStyle(mt_rand(0, 5)), ENT_QUOTES, 'UTF-8') ?>;"><?= htmlspecialchars(initials($tx['userName'] ?? '', $tx['userLastName'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                    <?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?>
                  </div>
                </td>
                <td style="text-align:right"><span class="<?= $isIncome ? 'amt-pos' : 'amt-neg' ?>"><?= $isIncome ? '+' : '–' ?><?= htmlspecialchars(formatCurrency(abs($amount)), ENT_QUOTES, 'UTF-8') ?></span></td>
                <td>
                  <a class="action-btn" href="?editTransaction=<?= urlencode($tx['idTransaction']) ?>">✏️</a>
                  <form method="post" action="admin_actions.php?resource=transaction&action=delete" style="display:inline-block;margin:0;">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($tx['idTransaction'], ENT_QUOTES, 'UTF-8') ?>"/>
                    <button type="submit" class="action-btn danger" style="border:none;background:transparent;padding:0;">🗑</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>

    </div>

    <!-- BOTTOM CARDS -->
    <div class="bottom-row">
      <div class="bottom-card accent-card">
        <div class="bc-label light">This Month's Spend</div>
        <div class="bc-value white"><?= htmlspecialchars(formatCurrency($thisMonthSpend), ENT_QUOTES, 'UTF-8') ?></div>
        <div class="bc-sub light"><span class="green"><?= htmlspecialchars($totalTransactions > 0 ? 'Live overview' : 'No spend yet', ENT_QUOTES, 'UTF-8') ?></span></div>
      </div>
      <div class="bottom-card">
        <div class="bc-label">Top Category</div>
        <div style="display:flex;align-items:center;gap:10px;margin-top:4px;">
          <div style="width:36px;height:36px;border-radius:10px;background:#f5f3ff;display:flex;align-items:center;justify-content:center;font-size:1rem;">📊</div>
          <div>
            <div style="font-family:'Sora',sans-serif;font-size:1rem;font-weight:800;"><?= htmlspecialchars($topCategoryName, ENT_QUOTES, 'UTF-8') ?></div>
            <div style="font-size:.72rem;color:var(--text-muted);">Top tracked expense category</div>
          </div>
        </div>
      </div>
      <div class="bottom-card">
        <div class="bc-label">Pending Sync</div>
        <div style="display:flex;align-items:center;gap:8px;margin-top:4px;">
          <div style="font-family:'Sora',sans-serif;font-size:1.5rem;font-weight:800;color:var(--text-dark);"><?= htmlspecialchars($pendingSyncCount, ENT_QUOTES, 'UTF-8') ?></div>
          <span style="font-size:.82rem;font-weight:600;color:var(--text-muted);">Unmatched expenses</span>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>