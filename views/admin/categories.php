<?php
require_once __DIR__ . '/admin_helpers.php';

$adminFlash = getAdminFlash();
$editCategory = null;
if (!empty($_GET['editCategory'])) {
    $stmt = $pdo->prepare('SELECT * FROM category WHERE idCategory = ? LIMIT 1');
    $stmt->execute([$_GET['editCategory']]);
    $editCategory = $stmt->fetch(PDO::FETCH_ASSOC);
}

$search = trim($_GET['search'] ?? '');
$filterType = strtolower(trim($_GET['filterType'] ?? ''));
$whereClauses = [];
$queryParams = [];
if ($search !== '') {
    $whereClauses[] = 'c.name LIKE ?';
    $queryParams[] = "%{$search}%";
}
if (in_array($filterType, ['expense', 'income'], true)) {
    $whereClauses[] = 'LOWER(c.type) = ?';
    $queryParams[] = $filterType;
}
$sql = "SELECT c.*, COUNT(t.idTransaction) AS transactionCount, COALESCE(SUM(CASE WHEN t.transCategory='EXPENSE' THEN t.amout ELSE 0 END),0) AS spent, COALESCE(SUM(CASE WHEN t.transCategory='INCOME' THEN t.amout ELSE 0 END),0) AS income FROM category c LEFT JOIN transaction t ON t.categoryId=c.idCategory";
if ($whereClauses) {
    $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
}
$sql .= ' GROUP BY c.idCategory ORDER BY spent DESC';
$categoryStmt = $pdo->prepare($sql);
$categoryStmt->execute($queryParams);
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);
$totalCategories = count($categories);
$monthSpendStmt = $pdo->prepare(
    "SELECT COALESCE(SUM(amout),0) FROM transaction WHERE transCategory='EXPENSE' AND YEAR(date)=YEAR(CURDATE()) AND MONTH(date)=MONTH(CURDATE())"
);
$monthSpendStmt->execute();
$totalMonthSpend = (float) $monthSpendStmt->fetchColumn();
$mostUsedCategory = $categories[0]['name'] ?? 'None';
$mostUsedTransactions = $categories[0]['transactionCount'] ?? 0;
$topCategories = array_slice($categories, 0, 5);
$topCategorySpent = max(array_column($topCategories, 'spent') ?: [1]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>BudgetPro – Expense Categories</title>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet"/>
  <style>
    :root{--bg:#f7f8fc;--white:#fff;--accent:#4f46e5;--accent-soft:#eef0ff;--teal:#059669;--teal-soft:#d1fae5;--red:#ef4444;--red-soft:#fee2e2;--orange:#f59e0b;--blue:#3b82f6;--purple:#7c3aed;--yellow:#fbbf24;--text-dark:#111827;--text-mid:#374151;--text-muted:#6b7280;--text-light:#9ca3af;--border:#e5e7eb;--radius:12px;--shadow:0 1px 4px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.04);}
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
    .btn-logout{width:100%;background:var(--yellow);color:#7a5c00;border:none;border-radius:8px;padding:9px;font-family:'Sora',sans-serif;font-weight:700;font-size:.8rem;cursor:pointer;text-align:center;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:5px;}
    .btn-logout:hover{opacity:.9;}
    .main{margin-left:200px;flex:1;display:flex;flex-direction:column;}
    .content{padding:22px 24px;}
    .page-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;}
    .page-header h1{font-size:1.4rem;font-weight:800;}
    .page-header p{font-size:.82rem;color:var(--text-muted);margin-top:4px;}
    .header-btns{display:flex;gap:8px;}
    .btn-export{display:flex;align-items:center;gap:5px;background:var(--white);border:1px solid var(--border);border-radius:8px;padding:8px 14px;font-family:'DM Sans',sans-serif;font-size:.8rem;font-weight:600;color:var(--text-mid);cursor:pointer;}
    .btn-export:hover{background:#f3f4f6;}
    .btn-add{background:var(--accent);color:#fff;border:none;border-radius:8px;padding:8px 16px;font-family:'Sora',sans-serif;font-weight:700;font-size:.8rem;cursor:pointer;display:flex;align-items:center;gap:5px;box-shadow:0 4px 12px rgba(79,70,229,.3);}
    .action-btn{display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:8px;background:#f3f4f6;color:var(--text-mid);border:1px solid var(--border);font-size:.82rem;cursor:pointer;text-decoration:none;margin-right:6px;}
    .action-btn:hover{background:#eef2ff;color:var(--accent);}
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
    /* STAT STRIP */
    .stat-strip{display:grid;grid-template-columns:repeat(3,1fr);gap:0;background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);margin-bottom:20px;overflow:hidden;}
    .ss-item{padding:16px 20px;border-right:1px solid var(--border);}
    .ss-item:last-child{border-right:none;}
    .ss-label{font-size:.68rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px;}
    .ss-val{font-family:'Sora',sans-serif;font-size:1.4rem;font-weight:800;color:var(--text-dark);}
    .ss-sub{font-size:.72rem;color:var(--text-muted);margin-top:3px;display:flex;align-items:center;gap:5px;}
    .ss-badge{font-size:.68rem;font-weight:700;padding:2px 7px;border-radius:50px;}
    .sb-red{background:var(--red-soft);color:var(--red);}
    .sb-green{background:var(--teal-soft);color:var(--teal);}
    .ss-item.most-used{background:#f9fafb;}
    .mu-value{font-family:'Sora',sans-serif;font-size:1.2rem;font-weight:800;color:var(--text-dark);}
    .mu-sub{font-size:.78rem;color:var(--text-muted);}
    /* CAT GRID */
    .cat-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;}
    .cat-card{background:var(--white);border-radius:var(--radius);padding:18px;box-shadow:var(--shadow);}
    .cat-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;}
    .cat-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;}
    .ci-blue{background:#dbeafe;}
    .ci-yellow{background:#fef3c7;}
    .ci-purple{background:#f5f3ff;}
    .ci-green{background:#d1fae5;}
    .ci-red{background:#fee2e2;}
    .cat-menu{background:transparent;border:none;cursor:pointer;color:var(--text-muted);font-size:1rem;}
    .cat-name{font-size:.92rem;font-weight:700;margin-bottom:3px;}
    .cat-desc{font-size:.72rem;color:var(--text-muted);margin-bottom:14px;}
    .cat-stats{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px;}
    .cs-item{background:#f9fafb;border-radius:8px;padding:8px 10px;}
    .cs-num{font-family:'Sora',sans-serif;font-size:.9rem;font-weight:800;color:var(--text-dark);}
    .cs-label{font-size:.68rem;color:var(--text-muted);margin-top:1px;}
    .spend-row{display:flex;justify-content:space-between;font-size:.75rem;margin-bottom:5px;}
    .spend-total{font-weight:700;color:var(--text-dark);}
    .prog-bg{background:#f3f4f6;border-radius:50px;height:5px;}
    .prog-fill{height:5px;border-radius:50px;}
    .table-wrapper{overflow-x:auto;}
    .manage-table{width:100%;border-collapse:collapse;margin-top:10px;font-size:.9rem;color:var(--text-mid);}
    .manage-table th,.manage-table td{padding:14px 16px;border-bottom:1px solid var(--border);}
    .manage-table th{font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);}
    .manage-table tr:hover{background:#f9fafb;}
    .manage-table td:last-child{white-space:nowrap;}
    .action-btn.danger{border:1px solid #fee2e2;background:var(--red-soft);color:var(--red);}
    .action-btn.danger:hover{background:#fecaca;}
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
    <a class="nav-item" href="transactions.php"><span class="nav-icon">💳</span> Transactions</a>
    <a class="nav-item active" href="categories.php"><span class="nav-icon">🗂️</span> Categories</a>
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
      <div><h1>Expense Categories</h1><p>Organize and track your organizational spending habits.</p></div>
      <div class="header-btns">
        <a class="btn-export" href="export_data.php?type=categories">↑ Export</a>
        <a class="btn-add" href="#category-form">＋ Add Category</a>
      </div>
    </div>
    <?php if ($adminFlash): ?>
      <div class="admin-flash <?= $adminFlash['success'] ? '' : 'error' ?>"><?= htmlspecialchars($adminFlash['message'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="form-card" style="margin-bottom:20px;">
      <form method="get" action="categories.php">
        <div class="form-grid">
          <div class="form-field">
            <label for="category-search">Search categories</label>
            <input class="form-input" id="category-search" name="search" type="text" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Search by category name" />
          </div>
          <div class="form-field">
            <label for="filter-type">Type</label>
            <select class="form-input" id="filter-type" name="filterType">
              <option value="">All types</option>
              <option value="expense" <?= $filterType === 'expense' ? 'selected' : '' ?>>Expense</option>
              <option value="income" <?= $filterType === 'income' ? 'selected' : '' ?>>Income</option>
            </select>
          </div>
          <div class="form-field full" style="display:flex;align-items:flex-end;gap:10px;">
            <button type="submit" class="btn-submit">Apply filters</button>
            <a class="btn-secondary" href="categories.php">Reset</a>
          </div>
        </div>
      </form>
    </div>

    <div id="category-form" class="form-card">
      <h3><?= $editCategory ? 'Edit Category' : 'Create New Category' ?></h3>
      <form method="post" action="admin_actions.php?resource=category&action=<?= $editCategory ? 'update' : 'create' ?>">
        <?php if ($editCategory): ?>
          <input type="hidden" name="id" value="<?= htmlspecialchars($editCategory['idCategory'], ENT_QUOTES, 'UTF-8') ?>"/>
        <?php endif; ?>
        <div class="form-grid">
          <div class="form-field">
            <label for="category-name">Name</label>
            <input class="form-input" id="category-name" name="name" type="text" value="<?= htmlspecialchars($editCategory['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required/>
          </div>
          <div class="form-field">
            <label for="category-type">Type</label>
            <select class="form-input" id="category-type" name="type">
              <option value="expense" <?= isset($editCategory['type']) && strtolower($editCategory['type']) === 'expense' ? 'selected' : '' ?>>Expense</option>
              <option value="income" <?= isset($editCategory['type']) && strtolower($editCategory['type']) === 'income' ? 'selected' : '' ?>>Income</option>
            </select>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn-submit"><?= $editCategory ? 'Update Category' : 'Create Category' ?></button>
          <?php if ($editCategory): ?>
            <a href="categories.php" class="btn-secondary">Cancel</a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <!-- STAT STRIP -->
    <div class="stat-strip">
      <div class="ss-item">
        <div class="ss-label">Total Categories</div>
        <div class="ss-val"><?= htmlspecialchars($totalCategories, ENT_QUOTES, 'UTF-8') ?></div>
        <div class="ss-sub"><span style="display:inline-flex;align-items:center;gap:3px;font-size:.75rem;color:var(--teal)">📈 Active tracking</span></div>
      </div>
      <div class="ss-item most-used">
        <div class="ss-label">Most Used</div>
        <div class="mu-value"><?= htmlspecialchars($mostUsedCategory, ENT_QUOTES, 'UTF-8') ?> <span style="font-size:.78rem;font-weight:500;color:var(--text-muted)"><?= htmlspecialchars($mostUsedTransactions, ENT_QUOTES, 'UTF-8') ?> Tx</span></div>
      </div>
      <div class="ss-item">
        <div class="ss-label">Total Month Spend</div>
        <div class="ss-val"><?= htmlspecialchars(formatCurrency($totalMonthSpend), ENT_QUOTES, 'UTF-8') ?></div>
        <div class="ss-sub"><span class="ss-badge sb-red">Live</span></div>
      </div>
    </div>
    <!-- CAT GRID -->
    <div class="cat-grid">
      <?php foreach ($topCategories as $index => $category):
        $color = categoryStyle($index);
        $label = htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8');
        $spent = (float) $category['spent'];
        $transactions = (int) $category['transactionCount'];
        $progress = $topCategorySpent > 0 ? min(100, (int) round(($spent / $topCategorySpent) * 100)) : 0;
      ?>
        <div class="cat-card">
          <div class="cat-top"><div class="cat-icon" style="background:<?= htmlspecialchars($color . '33', ENT_QUOTES, 'UTF-8') ?>;color:<?= htmlspecialchars($color, ENT_QUOTES, 'UTF-8') ?>;">📌</div><button class="cat-menu">⋯</button></div>
          <div class="cat-name"><?= $label ?></div>
          <div class="cat-desc"><?= htmlspecialchars($category['type'] ?? 'Expense', ENT_QUOTES, 'UTF-8') ?></div>
          <div class="cat-stats">
            <div class="cs-item"><div class="cs-num"><?= htmlspecialchars($transactions, ENT_QUOTES, 'UTF-8') ?></div><div class="cs-label">Transactions</div></div>
            <div class="cs-item"><div class="cs-num" style="color:var(--red)"><?= htmlspecialchars(formatCurrency($spent), ENT_QUOTES, 'UTF-8') ?></div><div class="cs-label">Spent Total</div></div>
          </div>
          <div class="prog-bg"><div class="prog-fill" style="width:<?= $progress ?>%;background:<?= htmlspecialchars($color, ENT_QUOTES, 'UTF-8') ?>"></div></div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="form-card">
      <h3>Manage Categories</h3>
      <div class="table-wrapper">
        <table class="manage-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Type</th>
              <th>Transactions</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($categories as $category): ?>
              <tr>
                <td><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars(ucfirst($category['type'] ?? 'expense'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($category['transactionCount'] ?? 0, ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <a class="action-btn" href="?editCategory=<?= urlencode($category['idCategory']) ?>">✏️</a>
                  <form method="post" action="admin_actions.php?resource=category&action=delete" style="display:inline-block;margin:0;">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($category['idCategory'], ENT_QUOTES, 'UTF-8') ?>"/>
                    <button type="submit" class="action-btn danger">🗑</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</body>
</html>