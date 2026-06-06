<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/shared.php';
define('CONTROLLER_INCLUDED', true);
require_once __DIR__ . '/../../controllers/categoryController.php';

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function formatMoney($amount) {
    return number_format((float) $amount, 0, '.', ',');
}

function usagePercent($expenses, $budget) {
    if ((float) $budget <= 0) {
        return 0;
    }

    return (int) min(100, round(((float) $expenses / (float) $budget) * 100));
}

$currentUserId = $currentUser['id'] ?? ($_SESSION['user']['id'] ?? null);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crud_action'])) {
    categoryController($_POST['crud_action'], $_POST);
    header('Location: ' . userPageUrl('category'));
    exit;
}

$pageData = categoryViewData($currentUserId);
$categories = $pageData['categories'] ?? [];
$categoryStats = $pageData['categoryStats'] ?? [];
$totals = $pageData['totals'] ?? ['budget' => 0, 'income' => 0, 'expenses' => 0, 'transactions' => 0];
$displayName = $userName ?: trim(($currentUser['name'] ?? 'User') . ' ' . ($currentUser['lastName'] ?? ''));
$currentInitials = $userInitials ?: 'U';
$editingCategory = null;

if (!empty($_GET['edit'])) {
  $editId = (int) $_GET['edit'];
  foreach ($categories as $category) {
    if ((int) ($category['idCategory'] ?? 0) === $editId) {
      $editingCategory = $category;
      break;
    }
  }
}

$categoryModalAction = $editingCategory ? 'update' : 'create';
$categoryModalTitle = $editingCategory ? 'Edit Category' : 'Add New Category';
$categoryModalButton = $editingCategory ? 'Save Category' : 'Create Category';
$categoryModalName = $editingCategory['name'] ?? '';
$categoryModalType = $editingCategory['type'] ?? 'expense';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Finzo – Categories</title>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet"/>
  <style>
    :root{--bg:#f4f5fb;--sidebar-bg:#2d2d3a;--purple:#7c6af5;--purple-light:#f0edff;--purple-mid:#e8e3ff;--teal:#00c9a7;--red:#ff6b8a;--yellow:#f5c842;--white:#ffffff;--text-dark:#1a1a2e;--text-mid:#555770;--text-muted:#9295a8;--sidebar-text:#b0b3c6;--sidebar-label:#6b6e80;--card-radius:14px;--shadow:0 2px 16px rgba(0,0,0,.07);}
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text-dark);display:flex;min-height:100vh;}
    h1,h2,h3,h4{font-family:'Sora',sans-serif;}
    .sidebar{width:200px;flex-shrink:0;background:var(--sidebar-bg);display:flex;flex-direction:column;padding:28px 0 20px;min-height:100vh;position:fixed;left:0;top:0;bottom:0;}
    .sidebar-logo{display:flex;align-items:center;gap:10px;padding:0 22px;margin-bottom:36px;}
    .logo-icon{width:40px;height:40px;border-radius:50%;flex-shrink:0;background:conic-gradient(#f5c842 0deg 90deg,#00c9a7 90deg 180deg,#ff7c3e 180deg 270deg,#7c6af5 270deg 360deg);display:flex;align-items:center;justify-content:center;position:relative;}
    .logo-icon::before{content:'';position:absolute;width:26px;height:26px;border-radius:50%;background:var(--sidebar-bg);}
    .logo-icon span{position:relative;z-index:1;font-size:.8rem;font-weight:800;color:#fff;}
    .sidebar-logo h2{font-size:1.3rem;font-weight:800;color:#fff;}
    .sidebar-section-label{font-size:.68rem;font-weight:600;color:var(--sidebar-label);letter-spacing:.1em;text-transform:uppercase;padding:0 22px;margin-bottom:8px;margin-top:10px;}
    .nav-item{display:flex;align-items:center;gap:11px;padding:11px 22px;cursor:pointer;color:var(--sidebar-text);font-size:.92rem;font-weight:500;text-decoration:none;transition:background .15s,color .15s;}
    .nav-item:hover{background:rgba(255,255,255,.06);color:#fff;}
    .nav-item.active{background:var(--purple);color:#fff;font-weight:700;border-radius:10px;margin:0 10px;padding:11px 12px;}
    .nav-icon{font-size:1rem;width:20px;text-align:center;}
    .sidebar-spacer{flex:1;}
    .sidebar-user{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:14px 22px;border-top:1px solid rgba(255,255,255,.08);}
    .sidebar-user-info{display:flex;flex-direction:column;gap:4px;min-width:0;}
    .user-name a{color:#fff;text-decoration:none;}
    .btn-logout{background:var(--yellow);color:#7a5c00;border:none;border-radius:9px;padding:9px 12px;font-family:'Sora',sans-serif;font-weight:700;font-size:.78rem;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;}
    .btn-logout:hover{opacity:.9;}
    .user-ava{width:36px;height:36px;border-radius:50%;background:var(--purple);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.78rem;font-weight:700;flex-shrink:0;}
    .user-name{font-size:.85rem;font-weight:600;color:#fff;}
    .user-plan{font-size:.72rem;color:var(--sidebar-label);}
    .main{margin-left:200px;flex:1;padding:30px 28px;}
    .topbar{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:24px;gap:20px;}
    .topbar h1{font-size:1.4rem;font-weight:800;}
    .topbar p{font-size:.85rem;color:var(--text-muted);margin-top:3px;}

    .summary-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:18px;}
    .summary-card{background:var(--white);border-radius:var(--card-radius);padding:18px 20px;box-shadow:var(--shadow);display:flex;flex-direction:column;gap:6px;}
    .summary-card .label{font-size:.75rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;}
    .summary-card .value{font-family:'Sora',sans-serif;font-size:1.35rem;font-weight:800;}
    .summary-card.budget{background:#f0edff;}
    .summary-card.budget .value{color:var(--purple);}
    .summary-card.income{background:#e6faf6;}
    .summary-card.income .value{color:var(--teal);}
    .summary-card.expenses{background:#fff0f3;}
    .summary-card.expenses .value{color:var(--red);}
    .summary-card.transactions{background:#f7f8fe;}
    .summary-card.transactions .value{color:var(--text-dark);}

    .cat-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:16px;}
    .cat-card{background:var(--white);border-radius:var(--card-radius);padding:22px 24px;box-shadow:var(--shadow);}
    .cat-card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;gap:12px;}
    .cat-card-title{display:flex;align-items:center;gap:12px;}
    .cat-card-header h3{font-size:1.1rem;font-weight:700;color:var(--text-dark);}
    .info-badge{min-width:24px;height:24px;border-radius:50%;background:#f5f6fc;border:1px solid #e2e3ee;display:flex;align-items:center;justify-content:center;font-size:.75rem;color:var(--text-muted);}
    .cat-card-actions{display:flex;gap:6px;}
    .cat-action{width:28px;height:28px;border-radius:8px;border:1px solid #e2e3ee;background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.78rem;color:var(--text-mid);text-decoration:none;}
    .cat-action:hover{border-color:var(--purple);color:var(--purple);}
    .cat-stat-row{display:flex;flex-direction:column;gap:8px;}
    .cat-stat{display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid #f2f3f8;gap:14px;}
    .cat-stat:last-child{border-bottom:none;}
    .cat-stat .label{font-size:.85rem;color:var(--text-mid);font-weight:500;}
    .cat-stat .value{font-size:.85rem;font-weight:700;white-space:nowrap;}
    .v-red{color:var(--red);}
    .v-teal{color:var(--teal);}
    .v-dark{color:var(--text-dark);}

    .empty-state{padding:32px 26px;}
    .empty-state h3{margin-bottom:6px;}
    .empty-state p{color:var(--text-muted);font-size:.9rem;line-height:1.5;}

    .add-card{background:var(--purple-light);border:2px dashed #c8beff;border-radius:var(--card-radius);display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:200px;cursor:pointer;transition:background .2s;gap:12px;}
    .add-card:hover{background:var(--purple-mid);}
    .add-plus{width:42px;height:42px;border-radius:12px;background:rgba(124,106,245,.15);border:2px solid #c8beff;display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:var(--purple);}
    .add-card h4{font-size:1rem;font-weight:700;color:var(--purple);}

    @media (max-width: 1100px){
      .summary-grid,.cat-grid{grid-template-columns:1fr;}
    }
  </style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-logo"><div class="logo-icon"><span>$</span></div><h2>Finzo</h2></div>
  <div class="sidebar-section-label">Main</div>
  <a class="nav-item" href="<?= htmlspecialchars(userPageUrl('dashboard'), ENT_QUOTES, 'UTF-8') ?>"><span class="nav-icon">🏠</span> Dashboard</a>
  <a class="nav-item" href="<?= htmlspecialchars(userPageUrl('transaction'), ENT_QUOTES, 'UTF-8') ?>"><span class="nav-icon">💳</span> Transactions</a>
  <a class="nav-item" href="<?= htmlspecialchars(userPageUrl('budget'), ENT_QUOTES, 'UTF-8') ?>"><span class="nav-icon">🎯</span> Budgets</a>
  <a class="nav-item active" href="<?= htmlspecialchars(userPageUrl('category'), ENT_QUOTES, 'UTF-8') ?>"><span class="nav-icon">🗂️</span> Categories</a>
  <div class="sidebar-section-label" style="margin-top:18px;">Collaboration</div>
  <a class="nav-item" href="<?= htmlspecialchars(userPageUrl('group'), ENT_QUOTES, 'UTF-8') ?>"><span class="nav-icon">👥</span> My Groups</a>
  <a class="nav-item" href="<?= htmlspecialchars(userPageUrl('alert'), ENT_QUOTES, 'UTF-8') ?>"><span class="nav-icon">🔔</span> Alerts</a>
  <div class="sidebar-spacer"></div>
  <div class="sidebar-user">
    <div class="user-ava"><?= e($currentInitials) ?></div>
    <div class="sidebar-user-info">
      <div class="user-name"><a href="<?= e(userPageUrl('profile')) ?>"><?= e($displayName) ?></a></div>
      <div class="user-plan"><?= e($currentUser['role'] ?? 'user') ?> plan</div>
    </div>
    <a class="btn-logout" href="<?= e(userPageUrl('logout')) ?>">Logout</a>
  </div>
</aside>

<main class="main">
  <div class="topbar">
    <div><h1>Categories</h1><p>Manage your categories for <?= e($displayName) ?></p></div>
  </div>

  <div class="summary-grid">
    <div class="summary-card budget">
      <div class="label">Total Budget</div>
      <div class="value"><?= formatMoney($totals['budget']) ?> TND</div>
    </div>
    <div class="summary-card income">
      <div class="label">Income</div>
      <div class="value"><?= formatMoney($totals['income']) ?> TND</div>
    </div>
    <div class="summary-card expenses">
      <div class="label">Expenses</div>
      <div class="value"><?= formatMoney($totals['expenses']) ?> TND</div>
    </div>
    <div class="summary-card transactions">
      <div class="label">Transactions</div>
      <div class="value"><?= (int) $totals['transactions'] ?></div>
    </div>
  </div>

  <div class="cat-grid">
    <?php if (empty($categories)): ?>
      <div class="cat-card empty-state">
        <h3>No categories yet</h3>
        <p>Add a category in the database or through your create flow, then the cards here will update automatically.</p>
      </div>
    <?php else: ?>
      <?php foreach ($categories as $category): ?>
        <?php
          $categoryId = (int) $category['idCategory'];
          $stats = $categoryStats[$categoryId] ?? ['budget' => 0, 'income' => 0, 'expenses' => 0, 'transactions' => 0];
          $usedPercent = usagePercent($stats['expenses'], $stats['budget']);
          $budgetLabel = $stats['budget'] > 0 ? formatMoney($stats['budget']) . ' TND' : 'No budget set';
          $incomeLabel = formatMoney($stats['income']) . ' TND';
          $expensesLabel = formatMoney($stats['expenses']) . ' TND';
        ?>
        <div class="cat-card">
          <div class="cat-card-header">
            <div class="cat-card-title">
              <h3><?= e($category['name']) ?></h3>
              <span class="info-badge" title="<?= e($category['type']) ?>"><?= $usedPercent ?>%</span>
            </div>
            <div class="cat-card-actions">
              <a class="cat-action" href="<?= htmlspecialchars(userPageUrl('category') . '?edit=' . $categoryId, ENT_QUOTES, 'UTF-8') ?>">✏️</a>
              <form method="post" action="<?= htmlspecialchars(userPageUrl('category'), ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Delete this category?');" style="display:inline;">
                <input type="hidden" name="crud_action" value="delete"/>
                <input type="hidden" name="id" value="<?= $categoryId ?>"/>
                <button class="cat-action" type="submit">🗑️</button>
              </form>
            </div>
          </div>
          <div class="cat-stat-row">
            <div class="cat-stat"><span class="label">Total Budget</span><span class="value v-red"><?= e($budgetLabel) ?></span></div>
            <div class="cat-stat"><span class="label">Income</span><span class="value v-teal"><?= e($incomeLabel) ?></span></div>
            <div class="cat-stat"><span class="label">Expenses</span><span class="value v-red"><?= e($expensesLabel) ?></span></div>
            <div class="cat-stat"><span class="label">Transactions</span><span class="value v-dark"><?= (int) $stats['transactions'] ?></span></div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <div class="add-card" onclick="openModal()">
      <div class="add-plus">＋</div>
      <h4>Add New Category</h4>
    </div>
  </div>
</main>

<div class="modal-overlay<?= $editingCategory ? ' open' : '' ?>" id="modalOverlay" onclick="handleOverlay(event)" style="position:fixed;inset:0;background:rgba(30,28,50,.5);display:flex;align-items:center;justify-content:center;z-index:1000;opacity:0;pointer-events:none;transition:opacity .2s;backdrop-filter:blur(2px);">
  <div class="modal" style="background:#fff;border-radius:18px;padding:28px;width:100%;max-width:420px;box-shadow:0 24px 60px rgba(0,0,0,.18);transform:translateY(16px) scale(.97);transition:transform .2s;">
    <div class="modal-header" style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:18px;">
      <div>
        <h3 style="font-size:1rem;font-weight:800;color:var(--text-dark);font-family:'Sora',sans-serif;"><?= htmlspecialchars($categoryModalTitle, ENT_QUOTES, 'UTF-8') ?></h3>
        <p style="font-size:.78rem;color:var(--text-muted);margin-top:2px;">Manage your category list</p>
      </div>
      <button type="button" class="modal-close" onclick="closeModal()" style="background:none;border:none;font-size:1.2rem;cursor:pointer;color:var(--text-muted);">✕</button>
    </div>

    <form method="post" action="<?= htmlspecialchars(userPageUrl('category'), ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="crud_action" value="<?= htmlspecialchars($categoryModalAction, ENT_QUOTES, 'UTF-8') ?>"/>
      <input type="hidden" name="id" value="<?= htmlspecialchars($editingCategory['idCategory'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>

      <div class="form-group">
        <label>Name <span>*</span></label>
        <input type="text" name="name" value="<?= e($categoryModalName) ?>" required/>
      </div>

      <div class="form-group">
        <label>Type <span>*</span></label>
        <select name="type" required>
          <option value="expense" <?= $categoryModalType === 'expense' ? 'selected' : '' ?>>Expense</option>
          <option value="income" <?= $categoryModalType === 'income' ? 'selected' : '' ?>>Income</option>
        </select>
      </div>

      <div class="modal-footer" style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;">
        <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
        <button type="submit" class="btn-create"><?= htmlspecialchars($categoryModalButton, ENT_QUOTES, 'UTF-8') ?></button>
      </div>
    </form>
  </div>
</div>

<script>
  function openModal() { document.getElementById('modalOverlay').classList.add('open'); document.getElementById('modalOverlay').style.opacity = '1'; document.getElementById('modalOverlay').style.pointerEvents = 'all'; }
  function closeModal() { document.getElementById('modalOverlay').classList.remove('open'); document.getElementById('modalOverlay').style.opacity = '0'; document.getElementById('modalOverlay').style.pointerEvents = 'none'; }
  function handleOverlay(event) { if (event.target === event.currentTarget) closeModal(); }
  <?php if ($editingCategory): ?>
  document.addEventListener('DOMContentLoaded', function () { openModal(); });
  <?php endif; ?>
</script>
</body>
</html>
