<?php
require_once __DIR__ . '/admin_helpers.php';

$adminFlash = getAdminFlash();
$editBudget = null;
if (!empty($_GET['editBudget'])) {
    $stmt = $pdo->prepare(
        'SELECT b.*, bc.categoryId FROM budget b LEFT JOIN budgetcategory bc ON bc.budgetId = b.idBudget WHERE b.idBudget = ? LIMIT 1'
    );
    $stmt->execute([$_GET['editBudget']]);
    $editBudget = $stmt->fetch(PDO::FETCH_ASSOC);
}
$categoryList = $pdo->query('SELECT idCategory, name FROM category ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);

$search = trim($_GET['search'] ?? '');
$filterCategory = (int) ($_GET['filterCategory'] ?? 0);
$filterPeriod = trim($_GET['filterPeriod'] ?? '');
$whereClauses = [];
$queryParams = [];
if ($search !== '') {
    $whereClauses[] = '(b.name LIKE ? OR b.note LIKE ? OR c.name LIKE ?)';
    $likeSearch = "%{$search}%";
    $queryParams = array_merge($queryParams, [$likeSearch, $likeSearch, $likeSearch]);
}
if ($filterCategory > 0) {
    $whereClauses[] = 'bc.categoryId = ?';
    $queryParams[] = $filterCategory;
}
if (in_array(strtolower($filterPeriod), ['monthly', 'quarterly', 'yearly'], true)) {
    $whereClauses[] = 'LOWER(b.period) = ?';
    $queryParams[] = strtolower($filterPeriod);
}
$sql = "SELECT b.*, COALESCE(SUM(t.amout),0) AS spent FROM budget b LEFT JOIN budgettransaction bt ON bt.budgetId=b.idBudget LEFT JOIN transaction t ON t.idTransaction=bt.transactionId AND t.transCategory='EXPENSE' LEFT JOIN budgetcategory bc ON bc.budgetId=b.idBudget LEFT JOIN category c ON c.idCategory=bc.categoryId";
if ($whereClauses) {
    $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
}
$sql .= ' GROUP BY b.idBudget ORDER BY b.startDate DESC';
$budgetStmt = $pdo->prepare($sql);
$budgetStmt->execute($queryParams);
$budgets = $budgetStmt->fetchAll(PDO::FETCH_ASSOC);
$totalAllocated = array_reduce($budgets, fn($sum, $item) => $sum + (float) $item['limit'], 0.0);
$totalSpent = array_reduce($budgets, fn($sum, $item) => $sum + (float) $item['spent'], 0.0);
$topBudgets = array_slice($budgets, 0, 5);
$budgetHealth = $totalAllocated > 0 ? min(100, (int) round(($totalSpent / $totalAllocated) * 100)) : 0;
$recentBudgetStmt = $pdo->prepare(
    "SELECT t.description, t.amout, t.transCategory, t.date, b.name AS budgetName, u.name AS userName, u.lastName AS userLastName FROM transaction t LEFT JOIN budgettransaction bt ON bt.transactionId = t.idTransaction LEFT JOIN budget b ON b.idBudget = bt.budgetId LEFT JOIN users u ON u.id = t.userId ORDER BY t.date DESC LIMIT 5"
);
$recentBudgetStmt->execute();
$recentTransactions = $recentBudgetStmt->fetchAll(PDO::FETCH_ASSOC);
?>
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
    .btn-logout{width:100%;background:var(--yellow);color:#7a5c00;border:none;border-radius:8px;padding:8px;font-family:'DM Sans',sans-serif;font-size:.78rem;font-weight:700;cursor:pointer;text-align:center;text-decoration:none;display:flex;align-items:center;justify-content:center;}
    .btn-logout:hover{opacity:.9;}
    /* MAIN */
    .main{margin-left:200px;flex:1;display:flex;flex-direction:column;}
    .content{padding:22px 24px;}
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
    .action-btn{display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:8px;background:#f3f4f6;color:var(--text-mid);border:1px solid var(--border);font-size:.82rem;cursor:pointer;text-decoration:none;margin-right:6px;}
    .action-btn:hover{background:#eef2ff;color:var(--accent);}
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
    /* RECENT TX */
    .tx-section{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);}
    .tx-header{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border);}
    .tx-header h3{font-size:.93rem;font-weight:700;}
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
    <a class="nav-item" href="dashboard.php"><span class="nav-icon">📊</span> Dashboard</a>
    <a class="nav-item" href="users.php"><span class="nav-icon">👥</span> Users</a>
    <a class="nav-item active" href="budgets.php"><span class="nav-icon">🎯</span> Budgets</a>
    <a class="nav-item" href="transactions.php"><span class="nav-icon">💳</span> Transactions</a>
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
    <div class="breadcrumb">
      <span>Organisation</span><span class="sep">/</span>
      <span class="crumb-active">Budgets</span>
    </div>
    <div class="page-header">
      <div><h1>Budget Management</h1><p>Allocate and track fiscal spending across departments.</p></div>
      <a class="btn-create" href="#budget-form">✦ Create New Budget</a>
    </div>

    <?php if ($adminFlash): ?>
      <div class="admin-flash <?= $adminFlash['success'] ? '' : 'error' ?>"><?= htmlspecialchars($adminFlash['message'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="form-card" style="margin-bottom:20px;">
      <form method="get" action="budgets.php">
        <div class="form-grid">
          <div class="form-field">
            <label for="budget-search">Search budgets</label>
            <input class="form-input" id="budget-search" name="search" type="text" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Search by budget or category" />
          </div>
          <div class="form-field">
            <label for="filter-category">Category</label>
            <select class="form-input" id="filter-category" name="filterCategory">
              <option value="0">All categories</option>
              <?php foreach ($categoryList as $category): ?>
                <option value="<?= htmlspecialchars($category['idCategory'], ENT_QUOTES, 'UTF-8') ?>" <?= $filterCategory === (int)$category['idCategory'] ? 'selected' : '' ?>><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-field">
            <label for="filter-period">Period</label>
            <select class="form-input" id="filter-period" name="filterPeriod">
              <option value="">All periods</option>
              <option value="Monthly" <?= strtolower($filterPeriod) === 'monthly' ? 'selected' : '' ?>>Monthly</option>
              <option value="Quarterly" <?= strtolower($filterPeriod) === 'quarterly' ? 'selected' : '' ?>>Quarterly</option>
              <option value="Yearly" <?= strtolower($filterPeriod) === 'yearly' ? 'selected' : '' ?>>Yearly</option>
            </select>
          </div>
          <div class="form-field full" style="display:flex;align-items:flex-end;gap:10px;">
            <button type="submit" class="btn-submit">Apply filters</button>
            <a class="btn-secondary" href="budgets.php">Reset</a>
          </div>
        </div>
      </form>
    </div>

    <div id="budget-form" class="form-card">
      <h3><?= $editBudget ? 'Edit Budget' : 'New Budget' ?></h3>
      <form method="post" action="admin_actions.php?resource=budget&action=<?= $editBudget ? 'update' : 'create' ?>">
        <?php if ($editBudget): ?>
          <input type="hidden" name="id" value="<?= htmlspecialchars($editBudget['idBudget'], ENT_QUOTES, 'UTF-8') ?>"/>
        <?php endif; ?>
        <div class="form-grid">
          <div class="form-field">
            <label for="budget-name">Budget Name</label>
            <input class="form-input" id="budget-name" name="name" type="text" value="<?= htmlspecialchars($editBudget['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required/>
          </div>
          <div class="form-field">
            <label for="budget-limit">Limit</label>
            <input class="form-input" id="budget-limit" name="limit" type="number" step="0.01" value="<?= htmlspecialchars($editBudget['limit'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required/>
          </div>
          <div class="form-field">
            <label for="budget-period">Period</label>
            <select class="form-input" id="budget-period" name="period">
              <option value="Monthly" <?= isset($editBudget['period']) && $editBudget['period'] === 'Monthly' ? 'selected' : '' ?>>Monthly</option>
              <option value="Quarterly" <?= isset($editBudget['period']) && $editBudget['period'] === 'Quarterly' ? 'selected' : '' ?>>Quarterly</option>
              <option value="Yearly" <?= isset($editBudget['period']) && $editBudget['period'] === 'Yearly' ? 'selected' : '' ?>>Yearly</option>
            </select>
          </div>
          <div class="form-field">
            <label for="budget-category">Category</label>
            <select class="form-input" id="budget-category" name="categoryId" required>
              <option value="">Select category</option>
              <?php foreach ($categoryList as $category): ?>
                <option value="<?= htmlspecialchars($category['idCategory'], ENT_QUOTES, 'UTF-8') ?>" <?= isset($editBudget['categoryId']) && $editBudget['categoryId'] == $category['idCategory'] ? 'selected' : '' ?>><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-field">
            <label for="budget-start">Start Date</label>
            <input class="form-input" id="budget-start" name="startDate" type="date" value="<?= htmlspecialchars($editBudget['startDate'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required/>
          </div>
          <div class="form-field">
            <label for="budget-alert">Alert Threshold</label>
            <input class="form-input" id="budget-alert" name="sendAlertAt" type="number" min="0" max="100" value="<?= htmlspecialchars($editBudget['sendAlertAt'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="90"/>
          </div>
          <div class="form-field full">
            <label for="budget-note">Note</label>
            <textarea class="form-input" id="budget-note" name="note" rows="3"><?= htmlspecialchars($editBudget['note'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn-submit"><?= $editBudget ? 'Save Budget' : 'Create Budget' ?></button>
          <?php if ($editBudget): ?>
            <a href="budgets.php" class="btn-secondary">Cancel</a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <!-- STAT STRIP -->
    <div class="stat-strip" style="box-shadow:var(--shadow);border-radius:var(--radius);overflow:hidden;margin-bottom:20px;">
      <div class="stat-strip-item">
        <div class="ss-label">Total Allocated</div>
        <div class="ss-value"><?= htmlspecialchars(formatCurrency($totalAllocated), ENT_QUOTES, 'UTF-8') ?></div>
        <div class="ss-sub"><span class="ss-green">▲ Live total</span></div>
      </div>
      <div class="stat-strip-item">
        <div class="ss-label">Spent to Date</div>
        <div class="ss-value"><?= htmlspecialchars(formatCurrency($totalSpent), ENT_QUOTES, 'UTF-8') ?></div>
        <div class="ss-sub"><span class="ss-red"><?= htmlspecialchars($budgetHealth, ENT_QUOTES, 'UTF-8') ?>%</span> of total budget utilized</div>
      </div>
      <div class="stat-strip-item">
        <div class="health-label">Budget Health</div>
        <div class="health-title"><?= $budgetHealth >= 80 ? 'Healthy' : ($budgetHealth >= 50 ? 'At Risk' : 'Review Needed') ?></div>
        <div class="health-sub"><?= htmlspecialchars(count($budgets) . ' budgets', ENT_QUOTES, 'UTF-8') ?></div>
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
      <?php foreach ($topBudgets as $index => $budget):
        $spent = (float) $budget['spent'];
        $limit = (float) $budget['limit'];
        $pct = $limit > 0 ? min(100, (int) round(($spent / $limit) * 100)) : 0;
        $badgeClass = progressClass($pct);
        $icon = ['📣','✈️','🏗️','🧪','🏢'][$index % 5];
      ?>
        <div class="budget-card">
          <?php if ($pct >= 100): ?><div class="action-badge">Action Required</div><?php endif; ?>
          <div class="bc-top">
            <div class="bc-icon" style="background:<?= htmlspecialchars(categoryStyle($index) . '33', ENT_QUOTES, 'UTF-8') ?>;color:<?= htmlspecialchars(categoryStyle($index), ENT_QUOTES, 'UTF-8') ?>;"><?= $icon ?></div>
            <button class="bc-menu">⋯</button>
          </div>
          <div class="bc-title"><?= htmlspecialchars($budget['name'] ?: 'Unnamed Budget', ENT_QUOTES, 'UTF-8') ?></div>
          <div class="bc-desc"><?= htmlspecialchars($budget['note'] ?: 'Budget details', ENT_QUOTES, 'UTF-8') ?></div>
          <div class="bc-amounts"><span class="bc-spent"><?= htmlspecialchars(formatCurrency($spent), ENT_QUOTES, 'UTF-8') ?></span><span class="bc-total">/ <?= htmlspecialchars(formatCurrency($limit), ENT_QUOTES, 'UTF-8') ?></span></div>
          <div class="prog-bg"><div class="prog-fill" style="width:<?= $pct ?>%;background:<?= htmlspecialchars(categoryStyle($index), ENT_QUOTES, 'UTF-8') ?>"></div></div>
          <div class="bc-footer">
            <div style="display:flex;align-items:center;"><div class="member-avas"><div class="sm-ava" style="background:<?= htmlspecialchars(categoryStyle($index), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(substr($budget['name'] ?: 'B', 0, 2), ENT_QUOTES, 'UTF-8') ?></div></div></div>
            <span class="pct-badge <?= $badgeClass ?>"><?= htmlspecialchars($pct, ENT_QUOTES, 'UTF-8') ?>%</span>
            <span class="update-time"><?= htmlspecialchars(relativeTime($budget['startDate'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="form-card">
      <h3>Budget Catalog</h3>
      <table>
        <thead>
          <tr>
            <th>Budget</th>
            <th>Limit</th>
            <th>Period</th>
            <th>Category</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($budgets as $budget): ?>
            <tr>
              <td><?= htmlspecialchars($budget['name'] ?? 'Untitled', ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars(formatCurrency($budget['limit']), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($budget['period'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <?php
                  $budgetCategory = array_filter($categoryList, fn($item) => $item['idCategory'] == ($budget['categoryId'] ?? null));
                  $budgetCategory = reset($budgetCategory);
                ?>
                <?= htmlspecialchars($budgetCategory['name'] ?? 'Unassigned', ENT_QUOTES, 'UTF-8') ?>
              </td>
              <td>
                <a class="action-btn" href="?editBudget=<?= urlencode($budget['idBudget']) ?>">✏️</a>
                <form method="post" action="admin_actions.php?resource=budget&action=delete" style="display:inline-block;margin:0;">
                  <input type="hidden" name="id" value="<?= htmlspecialchars($budget['idBudget'], ENT_QUOTES, 'UTF-8') ?>"/>
                  <button type="submit" class="action-btn danger" style="border:none;background:transparent;padding:0;">🗑</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- RECENT TRANSACTIONS -->
    <div class="tx-section">
      <div class="tx-header">
        <h3>Recent Transaction Activity</h3>
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
      <?php if (empty($recentTransactions)): ?>
        <tr><td colspan="5" style="padding:20px 0;text-align:center;color:#6b7280;">No recent budget transactions available.</td></tr>
      <?php else: ?>
        <?php foreach ($recentTransactions as $tx):
          $isExpense = strtoupper($tx['transCategory'] ?? '') === 'EXPENSE';
          $owner = trim(($tx['userName'] ?? '') . ' ' . ($tx['userLastName'] ?? '')) ?: 'Unknown';
          $budgetLabel = $tx['budgetName'] ?: 'Unassigned';
          $statusClass = $isExpense ? 'sp-pending' : 'sp-cleared';
        ?>
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:9px;">
                <div style="width:28px;height:28px;border-radius:8px;background:#f5f3ff;display:flex;align-items:center;justify-content:center;font-size:.85rem;"><?= $isExpense ? '☁️' : '💰' ?></div>
                <div><div class="tx-name"><?= htmlspecialchars($tx['description'] ?? 'Budget transaction', ENT_QUOTES, 'UTF-8') ?></div></div>
              </div>
            </td>
            <td><span class="td-cat"><?= htmlspecialchars($budgetLabel, ENT_QUOTES, 'UTF-8') ?></span></td>
            <td>
              <div class="owner-cell">
                <div class="ow-ava" style="background:<?= htmlspecialchars(categoryStyle(mt_rand(0, 5)), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(initials($tx['userName'] ?? '', $tx['userLastName'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                <?= htmlspecialchars($owner, ENT_QUOTES, 'UTF-8') ?>
              </div>
            </td>
            <td><span class="amt-<?= $isExpense ? 'neg' : 'pos' ?>"><?= $isExpense ? '–' : '+' ?><?= htmlspecialchars(formatCurrency(abs((float) $tx['amout'])), ENT_QUOTES, 'UTF-8') ?></span></td>
            <td><span class="status-pill <?= $statusClass ?>">● <?= $isExpense ? 'Pending' : 'Cleared' ?></span></td>
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