<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/shared.php';
define('CONTROLLER_INCLUDED', true);
require_once __DIR__ . '/../../controllers/transactionController.php';

$currentUserId = $currentUser['id'] ?? ($_SESSION['user']['id'] ?? null);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crud_action'])) {
  $crudAction = $_POST['crud_action'];
  transactionController($crudAction, $_POST);
  header('Location: ' . userPageUrl('transaction'));
  exit;
}

$filters = [
  'search' => trim($_GET['search'] ?? ''),
  'type' => strtoupper($_GET['type'] ?? ''),
  'categoryId' => $_GET['categoryId'] ?? '',
  'sort' => $_GET['sort'] ?? 'date_desc',
];

$transactionData = userTransactionPageData($currentUserId, $filters);
$summary = $transactionData['summary'] ?? ['income' => 0, 'expenses' => 0, 'balance' => 0, 'transactions' => 0];
$transactions = $transactionData['transactions'] ?? [];
$categories = userCategoryList();
$budgets = userBudgetRecords($currentUserId);
$months = $transactionData['months'] ?? [];
$displayName = $userName ?: trim(($currentUser['name'] ?? 'User') . ' ' . ($currentUser['lastName'] ?? ''));
$displayInitials = $userInitials ?: 'U';
$editingTransaction = null;

if (!empty($_GET['edit'])) {
  $editId = (int) $_GET['edit'];
  foreach ($transactions as $transaction) {
    if ((int) ($transaction['idTransaction'] ?? 0) === $editId) {
      $editingTransaction = $transaction;
      break;
    }
  }
}

$transactionModalAction = $editingTransaction ? 'update' : 'create';
$transactionModalTitle = $editingTransaction ? 'Edit Transaction' : 'Add Transaction';
$transactionModalButton = $editingTransaction ? '💾 Update transaction' : '💾 Save transaction';
$transactionModalDescription = $editingTransaction['description'] ?? '';
$transactionModalNote = $editingTransaction['note'] ?? '';
$transactionModalAmount = $editingTransaction['amout'] ?? '';
$transactionModalDate = !empty($editingTransaction['date']) ? date('Y-m-d', strtotime($editingTransaction['date'])) : date('Y-m-d');
$transactionModalCategoryId = $editingTransaction['categoryId'] ?? ($categories[0]['idCategory'] ?? '');
$transactionModalBudgetId = $editingTransaction['budgetId'] ?? '';
$transactionModalType = strtoupper($editingTransaction['transCategory'] ?? 'EXPENSE');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Finzo – Transactions</title>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet"/>
  <style>
    :root {
      --bg: #f4f5fb;
      --sidebar-bg: #2d2d3a;
      --purple: #7c6af5;
      --purple-light: #f0edff;
      --purple-mid: #ebe8ff;
      --teal: #00c9a7;
      --red: #ff4d6d;
      --green: #00c9a7;
      --yellow: #f5c842;
      --blue: #4c9be8;
      --orange: #ff7c3e;
      --white: #ffffff;
      --text-dark: #1a1a2e;
      --text-mid: #555770;
      --text-muted: #9295a8;
      --border: #e5e7ef;
      --card-radius: 14px;
      --shadow: 0 2px 16px rgba(0,0,0,.07);
      --input-bg: #f8f9fe;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text-dark); display: flex; min-height: 100vh; }
    h1,h2,h3,h4 { font-family: 'Sora', sans-serif; }

    /* ── SIDEBAR ── */
    .sidebar {
      width: 200px; flex-shrink: 0; background: var(--sidebar-bg);
      display: flex; flex-direction: column; padding: 28px 0 20px;
      min-height: 100vh; position: fixed; left: 0; top: 0; bottom: 0;
    }
    .sidebar-logo { display: flex; align-items: center; gap: 10px; padding: 0 22px; margin-bottom: 36px; }
    .logo-icon {
      width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0;
      background: conic-gradient(#f5c842 0deg 90deg,#00c9a7 90deg 180deg,#ff7c3e 180deg 270deg,#7c6af5 270deg 360deg);
      display: flex; align-items: center; justify-content: center; position: relative;
    }
    .logo-icon::before { content:''; position:absolute; width:26px; height:26px; border-radius:50%; background:var(--sidebar-bg); }
    .logo-icon span { position:relative; z-index:1; font-size:.8rem; font-weight:800; color:#fff; }
    .sidebar-logo h2 { font-size:1.3rem; font-weight:800; color:#fff; }
    .sidebar-section-label { font-size:.68rem; font-weight:600; color:#6b6e80; letter-spacing:.1em; text-transform:uppercase; padding:0 22px; margin-bottom:8px; margin-top:10px; }
    .nav-item { display:flex; align-items:center; gap:11px; padding:11px 22px; cursor:pointer; color:#b0b3c6; font-size:.92rem; font-weight:500; text-decoration:none; transition:background .15s,color .15s; }
    .nav-item:hover { background:rgba(255,255,255,.06); color:#fff; }
    .nav-item.active { background:var(--purple); color:#fff; font-weight:700; border-radius:10px; margin:0 10px; padding:11px 12px; }
    .nav-icon { font-size:1rem; width:20px; text-align:center; }
    .sidebar-spacer { flex:1; }
    .sidebar-user { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:14px 22px; border-top:1px solid rgba(255,255,255,.08); }
    .sidebar-user-info { display:flex; flex-direction:column; gap:4px; min-width:0; }
    .user-name a { color:#fff; text-decoration:none; }
    .btn-logout { background:var(--yellow); color:#7a5c00; border:none; border-radius:9px; padding:9px 12px; font-family:'Sora',sans-serif; font-weight:700; font-size:.78rem; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; }
    .btn-logout:hover { opacity:.9; }
    .user-ava { width:36px; height:36px; border-radius:50%; background:var(--purple); color:#fff; display:flex; align-items:center; justify-content:center; font-size:.78rem; font-weight:700; flex-shrink:0; }
    .user-name { font-size:.85rem; font-weight:600; color:#fff; }
    .user-plan { font-size:.72rem; color:#6b6e80; }

    /* ── MAIN ── */
    .main { margin-left:200px; flex:1; padding:28px 26px; }

    /* TOPBAR */
    .topbar { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:22px; }
    .topbar h1 { font-size:1.4rem; font-weight:800; }
    .topbar p  { font-size:.82rem; color:var(--text-muted); margin-top:2px; }
    .topbar-right { display:flex; gap:10px; align-items:center; }
    .btn-add { background:var(--purple); color:#fff; border:none; border-radius:9px; padding:9px 18px; font-family:'Sora',sans-serif; font-weight:700; font-size:.85rem; cursor:pointer; display:flex; align-items:center; gap:6px; box-shadow:0 4px 14px rgba(124,106,245,.3); transition:opacity .2s,transform .15s; }
    .btn-add:hover { opacity:.9; transform:translateY(-1px); }

    /* STAT STRIP */
    .stat-strip { display:grid; grid-template-columns:repeat(3,1fr); background:var(--white); border-radius:var(--card-radius); box-shadow:var(--shadow); margin-bottom:18px; overflow:hidden; }
    .stat-cell { padding:18px 22px; }
    .stat-cell + .stat-cell { border-left:1px solid var(--border); }
    .stat-cell .label { font-size:.78rem; color:var(--text-muted); font-weight:500; margin-bottom:6px; }
    .stat-cell .value { font-family:'Sora',sans-serif; font-size:1.3rem; font-weight:800; }
    .v-green { color:var(--green); }
    .v-red   { color:var(--red); }
    .v-purple{ color:var(--purple); }

    /* FILTERS */
    .filters { display:flex; align-items:center; gap:10px; margin-bottom:14px; flex-wrap:wrap; }
    .search-box { display:flex; align-items:center; gap:8px; background:var(--white); border:1px solid var(--border); border-radius:9px; padding:9px 14px; flex:1; min-width:180px; }
    .search-box input { border:none; outline:none; font-family:'DM Sans',sans-serif; font-size:.88rem; color:var(--text-dark); background:transparent; width:100%; }
    .search-box input::placeholder { color:var(--text-muted); }
    .tab-group { display:flex; background:var(--white); border:1px solid var(--border); border-radius:9px; overflow:hidden; }
    .tab-btn { padding:9px 16px; font-size:.85rem; font-weight:600; border:none; background:none; cursor:pointer; color:var(--text-muted); font-family:'DM Sans',sans-serif; transition:background .15s,color .15s; }
    .tab-btn.active { background:var(--purple); color:#fff; }
    .filter-select { background:var(--white); border:1px solid var(--border); border-radius:9px; padding:9px 14px; font-family:'DM Sans',sans-serif; font-size:.85rem; color:var(--text-mid); cursor:pointer; }

    /* TABLE */
    .table-card { background:var(--white); border-radius:var(--card-radius); box-shadow:var(--shadow); overflow:hidden; margin-bottom:20px; }
    table { width:100%; border-collapse:collapse; }
    thead th { font-size:.72rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.06em; padding:12px 16px; text-align:left; border-bottom:1px solid var(--border); }
    tbody tr { transition:background .1s; }
    tbody tr:hover { background:#fafbff; }
    tbody td { padding:13px 16px; font-size:.87rem; border-bottom:1px solid #f2f3f8; vertical-align:middle; }
    tbody tr:last-child td { border-bottom:none; }
    .td-date { color:var(--text-muted); font-size:.78rem; }
    .td-date .time { display:block; font-size:.72rem; margin-top:1px; }
    .td-desc .name { font-weight:600; color:var(--text-dark); }
    .td-desc .sub  { font-size:.75rem; color:var(--text-muted); margin-top:2px; }
    .cat-badge { display:inline-flex; align-items:center; gap:5px; background:var(--purple-light); color:var(--purple); font-size:.74rem; font-weight:600; padding:3px 10px; border-radius:50px; }
    .cat-icon { font-size:.85rem; }
    .td-amount { font-family:'Sora',sans-serif; font-weight:700; font-size:.9rem; }
    .status-badge { display:inline-flex; align-items:center; gap:4px; font-size:.75rem; font-weight:600; padding:3px 10px; border-radius:50px; }
    .s-completed { background:#e6faf6; color:var(--green); }
    .s-pending   { background:#fff8e6; color:#d4a017; }
    .s-cancelled { background:#fff0f3; color:var(--red); }
    .td-actions { display:flex; gap:8px; }
    .action-btn { background:none; border:none; cursor:pointer; font-size:.85rem; color:var(--text-muted); transition:color .15s; }
    .action-btn:hover { color:var(--purple); }

    /* BOTTOM ROW */
    .bottom-row { display:grid; grid-template-columns:1fr 280px; gap:16px; }

    /* Cash Flow Card */
    .cashflow-card { background:var(--white); border-radius:var(--card-radius); padding:20px; box-shadow:var(--shadow); }
    .cashflow-top { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:6px; }
    .cashflow-top h4 { font-size:.95rem; font-weight:700; }
    .cashflow-top select { background:var(--input-bg); border:1px solid var(--border); border-radius:7px; font-size:.78rem; color:var(--text-mid); padding:4px 10px; cursor:pointer; }
    .cashflow-sub { font-size:.78rem; color:var(--text-muted); margin-bottom:16px; }
    .cf-bars { display:flex; align-items:flex-end; gap:8px; height:100px; }
    .cf-group { display:flex; flex-direction:column; align-items:center; justify-content:flex-end; gap:8px; flex:1; }
    .cf-group-bars { display:flex; align-items:flex-end; gap:4px; width:100%; max-width:48px; }
    .cf-bar { width:100%; border-radius:5px 5px 0 0; }
    .cf-bar-low  { background:#e0dbff; }
    .cf-bar-high { background:var(--purple); }
    .cf-label { font-size:.72rem; color:var(--text-muted); text-align:center; }

    /* Wallet Card */
    .wallet-card { background:var(--purple); border-radius:var(--card-radius); padding:22px; box-shadow:0 8px 30px rgba(124,106,245,.35); color:#fff; display:flex; flex-direction:column; gap:10px; }
    .wallet-label { font-size:.7rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; opacity:.7; }
    .wallet-amount { font-family:'Sora',sans-serif; font-size:1.6rem; font-weight:800; }
    .wallet-sub { font-size:.75rem; opacity:.65; }
    .wallet-avatars { display:flex; align-items:center; gap:-4px; margin-top:8px; }
    .w-ava { width:28px; height:28px; border-radius:50%; border:2px solid var(--purple); display:flex; align-items:center; justify-content:center; font-size:.65rem; font-weight:700; margin-left:-6px; }
    .w-ava:first-child { margin-left:0; }
    .w-shared { font-size:.75rem; opacity:.7; margin-left:8px; }

    /* ── MODAL ── */
    .modal-overlay {
      position:fixed; inset:0; background:rgba(30,28,50,.55);
      display:flex; align-items:center; justify-content:center;
      z-index:1000; opacity:0; pointer-events:none; transition:opacity .2s;
      backdrop-filter:blur(2px);
    }
    .modal-overlay.open { opacity:1; pointer-events:all; }
    .modal {
      background:#fff; border-radius:18px; padding:28px;
      width:100%; max-width:420px;
      box-shadow:0 24px 60px rgba(0,0,0,.18);
      transform:translateY(16px) scale(.97); transition:transform .2s;
    }
    .modal-overlay.open .modal { transform:translateY(0) scale(1); }
    .modal-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:18px; }
    .modal-header h3 { font-size:1rem; font-weight:800; color:var(--text-dark); }
    .modal-header p  { font-size:.78rem; color:var(--text-muted); margin-top:2px; }
    .modal-close { background:none; border:none; font-size:1.2rem; cursor:pointer; color:var(--text-muted); line-height:1; }
    .modal-close:hover { color:var(--text-dark); }

    /* Type toggle */
    .type-toggle { display:flex; gap:22px; margin-bottom:20px; }
    .type-btn { display:flex; align-items:center; gap:6px; font-size:.9rem; font-weight:700; border:none; background:none; cursor:pointer; padding:0; transition:opacity .2s; opacity:.45; font-family:'DM Sans',sans-serif; }
    .type-btn.active { opacity:1; }
    .type-btn.expense { color:var(--red); }
    .type-btn.income  { color:var(--green); }
    .type-btn .arrow { font-size:1.1rem; }

    /* Amount display */
    .amount-display {
      background:var(--purple-light); border-radius:12px;
      display:flex; align-items:center; justify-content:space-between;
      padding:14px 20px; margin-bottom:20px;
    }
    .amount-display input {
      font-family:'Sora',sans-serif; font-size:1.8rem; font-weight:800;
      color:var(--purple); background:none; border:none; outline:none;
      width:160px;
    }
    .amount-display .currency { font-family:'Sora',sans-serif; font-size:1.2rem; font-weight:700; color:var(--purple); opacity:.6; }

    /* Form fields */
    .form-row-2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px; }
    .form-group { display:flex; flex-direction:column; gap:5px; margin-bottom:14px; }
    .form-group label { font-size:.78rem; font-weight:600; color:var(--text-mid); }
    .form-group label span { color:var(--red); }
    .form-group input, .form-group textarea {
      background:var(--input-bg); border:1.5px solid var(--border);
      border-radius:9px; padding:10px 13px;
      font-family:'DM Sans',sans-serif; font-size:.88rem; color:var(--text-dark);
      outline:none; transition:border-color .2s;
    }
    .form-group input:focus, .form-group textarea:focus { border-color:var(--purple); }
    .form-group input::placeholder, .form-group textarea::placeholder { color:var(--text-muted); }
    .form-group textarea { resize:none; height:70px; }
    .date-input-wrap { position:relative; }
    .date-input-wrap input { padding-right:36px; }
    .date-icon { position:absolute; right:12px; top:50%; transform:translateY(-50%); color:var(--text-muted); pointer-events:none; }

    /* Modal footer */
    .modal-footer { display:flex; justify-content:flex-end; gap:10px; margin-top:20px; }
    .btn-cancel { background:none; border:1.5px solid var(--border); border-radius:9px; padding:10px 20px; font-family:'DM Sans',sans-serif; font-weight:600; font-size:.88rem; color:var(--text-mid); cursor:pointer; transition:background .15s; }
    .btn-cancel:hover { background:var(--bg); }
    .btn-save { background:var(--purple); color:#fff; border:none; border-radius:9px; padding:10px 22px; font-family:'Sora',sans-serif; font-weight:700; font-size:.88rem; cursor:pointer; display:flex; align-items:center; gap:6px; box-shadow:0 4px 14px rgba(124,106,245,.3); transition:opacity .2s; }
    .btn-save:hover { opacity:.9; }
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
  <a class="nav-item" href="<?= htmlspecialchars(userPageUrl('dashboard'), ENT_QUOTES, 'UTF-8') ?>">
    <span class="nav-icon">🏠</span> Dashboard
  </a>
  <a class="nav-item active" href="<?= htmlspecialchars(userPageUrl('transaction'), ENT_QUOTES, 'UTF-8') ?>">
    <span class="nav-icon">💳</span> Transactions
  </a>
  <a class="nav-item" href="<?= htmlspecialchars(userPageUrl('budget'), ENT_QUOTES, 'UTF-8') ?>">
    <span class="nav-icon">🎯</span> Budgets
  </a>
  <a class="nav-item" href="<?= htmlspecialchars(userPageUrl('category'), ENT_QUOTES, 'UTF-8') ?>">
    <span class="nav-icon">🗂️</span> Categories
  </a>
  <div class="sidebar-section-label" style="margin-top:18px;">Collaboration</div>
  <a class="nav-item" href="<?= htmlspecialchars(userPageUrl('group'), ENT_QUOTES, 'UTF-8') ?>"><span class="nav-icon">👥</span> My Groups</a>
  <a class="nav-item" href="<?= htmlspecialchars(userPageUrl('alert'), ENT_QUOTES, 'UTF-8') ?>"><span class="nav-icon">🔔</span> Alerts</a>
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

<!-- MAIN -->
<main class="main">

  <!-- Topbar -->
  <div class="topbar">
    <div>
      <h1>Transactions</h1>
      <p><?= (int) $summary['transactions'] ?> transactions</p>
    </div>
    <div class="topbar-right">
      <button class="btn-add" onclick="openModal()">＋ Add Transaction</button>
    </div>
  </div>

  <!-- Stat Strip -->
  <div class="stat-strip">
    <div class="stat-cell">
      <div class="label">Total Income</div>
      <div class="value v-green">+ <?= number_format((float) $summary['income'], 0, '.', ',') ?> TND</div>
    </div>
    <div class="stat-cell">
      <div class="label">Total Expenses</div>
      <div class="value v-red">- <?= number_format((float) $summary['expenses'], 0, '.', ',') ?> TND</div>
    </div>
    <div class="stat-cell">
      <div class="label">Net Balance</div>
      <div class="value v-purple"><?= number_format((float) $summary['balance'], 0, '.', ',') ?> TND</div>
    </div>
  </div>

  <!-- Filters -->
  <form method="get" action="<?= htmlspecialchars(userPageUrl('transaction'), ENT_QUOTES, 'UTF-8') ?>" class="filters" id="transactionFilters">
    <div class="search-box">
      <span style="color:var(--text-muted)">🔍</span>
      <input type="text" name="search" placeholder="Search transactions…" value="<?= htmlspecialchars($filters['search'], ENT_QUOTES, 'UTF-8') ?>" />
    </div>
    <div class="tab-group">
      <button type="button" class="tab-btn<?= $filters['type'] === '' ? ' active' : '' ?>" onclick="setFilterType('')">All</button>
      <button type="button" class="tab-btn<?= $filters['type'] === 'INCOME' ? ' active' : '' ?>" onclick="setFilterType('INCOME')">Income</button>
      <button type="button" class="tab-btn<?= $filters['type'] === 'EXPENSE' ? ' active' : '' ?>" onclick="setFilterType('EXPENSE')">Expenses</button>
    </div>
    <input type="hidden" name="type" id="filterType" value="<?= htmlspecialchars($filters['type'], ENT_QUOTES, 'UTF-8') ?>" />
    <select class="filter-select" name="categoryId" onchange="this.form.submit()">
      <option value="">Category ∨</option>
      <?php foreach ($categories as $category): ?>
        <option value="<?= htmlspecialchars($category['idCategory'], ENT_QUOTES, 'UTF-8') ?>"<?= $filters['categoryId'] === (string) $category['idCategory'] ? ' selected' : '' ?>><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></option>
      <?php endforeach; ?>
    </select>
    <select class="filter-select" name="sort" onchange="this.form.submit()">
      <option value="date_desc"<?= $filters['sort'] === 'date_desc' ? ' selected' : '' ?>>Sort: Date ∨</option>
      <option value="date_asc"<?= $filters['sort'] === 'date_asc' ? ' selected' : '' ?>>Sort: Oldest</option>
      <option value="amount_desc"<?= $filters['sort'] === 'amount_desc' ? ' selected' : '' ?>>Sort: Amount ▾</option>
      <option value="amount_asc"<?= $filters['sort'] === 'amount_asc' ? ' selected' : '' ?>>Sort: Amount ▴</option>
      <option value="category"<?= $filters['sort'] === 'category' ? ' selected' : '' ?>>Sort: Category</option>
    </select>
  </form>

  <!-- Table -->
  <div class="table-card">
    <table>
      <thead>
        <tr>
          <th>Date</th>
          <th>Description</th>
          <th>Category</th>
          <th>Amount</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
          <?php if (empty($transactions)): ?>
            <tr>
              <td colspan="6" style="padding:20px 16px;color:var(--text-muted);text-align:center;">No transactions found for this account.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($transactions as $transaction): ?>
              <?php
                $isIncome = strtoupper((string) ($transaction['transCategory'] ?? '')) === 'INCOME';
                $amount = (float) ($transaction['amout'] ?? 0);
                $dateValue = !empty($transaction['date']) ? strtotime($transaction['date']) : false;
                $dateLabel = $dateValue ? date('M d, Y', $dateValue) : 'Unknown date';
                $timeLabel = $dateValue ? date('h:i A', $dateValue) : '';
                $categoryName = $transaction['categoryName'] ?? ($transaction['transType'] ?? 'Uncategorized');
                $icon = userCategoryIcon($categoryName ?: ($transaction['description'] ?? 'Transaction'));
              ?>
              <tr>
                <td><div class="td-date"><?= htmlspecialchars($dateLabel, ENT_QUOTES, 'UTF-8') ?><span class="time"><?= htmlspecialchars($timeLabel, ENT_QUOTES, 'UTF-8') ?></span></div></td>
                <td><div class="td-desc"><div class="name"><?= htmlspecialchars($transaction['description'] ?? 'Transaction', ENT_QUOTES, 'UTF-8') ?></div><div class="sub"><?= htmlspecialchars($transaction['note'] ?? '', ENT_QUOTES, 'UTF-8') ?></div></div></td>
                <td><span class="cat-badge" style="<?= $isIncome ? 'background:#e6faf6;color:var(--green)' : '' ?>"><span class="cat-icon"><?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?></span><?= htmlspecialchars(strtoupper($categoryName), ENT_QUOTES, 'UTF-8') ?></span></td>
                <td class="td-amount" style="color:<?= $isIncome ? 'var(--green)' : 'var(--red)' ?>"><?= $isIncome ? '+' : '-' ?><?= number_format($amount, 0, '.', ',') ?> TND</td>
                <td><span class="status-badge s-completed">● Completed</span></td>
                <td>
                  <div class="td-actions">
                    <a class="action-btn" href="<?= htmlspecialchars(userPageUrl('transaction') . '?edit=' . (int) ($transaction['idTransaction'] ?? 0), ENT_QUOTES, 'UTF-8') ?>">✏️</a>
                    <form method="post" action="<?= htmlspecialchars(userPageUrl('transaction'), ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Delete this transaction?');" style="display:inline;">
                      <input type="hidden" name="crud_action" value="delete"/>
                      <input type="hidden" name="id" value="<?= (int) ($transaction['idTransaction'] ?? 0) ?>"/>
                      <button class="action-btn" type="submit">🗑️</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="cashflow-card">
      <div class="cashflow-top">
        <div>
          <h4>Cash Flow Over Time</h4>
          <div class="cashflow-sub">Visualize your monthly spending habits</div>
        </div>
        <select><option>Last 6 Months ∨</option><option>Last 3 Months</option><option>This Year</option></select>
      </div>
      <?php
      if (empty($months)) {
        $months = [];
        for ($m = 5; $m >= 0; $m--) {
          $months[] = ['monthLabel' => date('M', strtotime("-{$m} months")), 'income' => 0, 'expenses' => 0];
        }
      }
      $maxMonthValue = 1;
      foreach ($months as $month) {
        $maxMonthValue = max($maxMonthValue, (float) ($month['income'] ?? 0), (float) ($month['expenses'] ?? 0));
      }
    ?>
    <div class="cf-bars">
      <?php foreach ($months as $month): ?>
        <?php
          $incomeHeight = (int) round(((float) ($month['income'] ?? 0) / $maxMonthValue) * 100);
          $expenseHeight = (int) round(((float) ($month['expenses'] ?? 0) / $maxMonthValue) * 100);
        ?>
        <div class="cf-group">
          <div class="cf-group-bars">
            <div class="cf-bar cf-bar-high" style="height:<?= $incomeHeight ?>%" title="Income: <?= number_format((float) ($month['income'] ?? 0), 0, '.', ',') ?> TND"></div>
            <div class="cf-bar cf-bar-low" style="height:<?= $expenseHeight ?>%" title="Expenses: <?= number_format((float) ($month['expenses'] ?? 0), 0, '.', ',') ?> TND"></div>
          </div>
          <div class="cf-label"><?= htmlspecialchars($month['monthLabel'], ENT_QUOTES, 'UTF-8') ?></div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Wallet -->
    <div class="wallet-card">
      <div class="wallet-label">Organization Wallet</div>
      <div class="wallet-amount"><?= number_format((float) $summary['balance'], 2, '.', ',') ?> TND</div>
      <div class="wallet-sub">Available balance across all accounts</div>
      <div style="display:flex;align-items:center;gap:8px;margin-top:10px;">
        <div class="wallet-avatars">
          <div class="w-ava" style="background:rgba(255,255,255,.3)">KM</div>
          <div class="w-ava" style="background:rgba(255,255,255,.2)">SA</div>
        </div>
        <div class="w-shared">Shared with team</div>
      </div>
    </div>
  </div>

</main>

<!-- ── MODAL ── -->
<div class="modal-overlay<?= $editingTransaction ? ' open' : '' ?>" id="modalOverlay" onclick="handleOverlayClick(event)">
  <div class="modal">
    <div class="modal-header">
      <div>
        <h3><?= htmlspecialchars($transactionModalTitle, ENT_QUOTES, 'UTF-8') ?></h3>
        <p>Log a new income or expense</p>
      </div>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>

    <form method="post" action="<?= htmlspecialchars(userPageUrl('transaction'), ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="crud_action" id="crudAction" value="<?= htmlspecialchars($transactionModalAction, ENT_QUOTES, 'UTF-8') ?>"/>
      <input type="hidden" name="id" id="transactionId" value="<?= htmlspecialchars($editingTransaction['idTransaction'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
      <input type="hidden" name="transCategory" id="transCategory" value="<?= htmlspecialchars($transactionModalType, ENT_QUOTES, 'UTF-8') ?>"/>
      <input type="hidden" name="transType" id="transType" value="<?= htmlspecialchars($transactionModalType, ENT_QUOTES, 'UTF-8') ?>"/>

    <!-- Type toggle -->
    <div class="type-toggle">
      <button type="button" class="type-btn expense<?= $transactionModalType === 'EXPENSE' ? ' active' : '' ?>" id="btnExpense" onclick="setType('EXPENSE')">
        <span class="arrow">↓</span> Expense
      </button>
      <button type="button" class="type-btn income<?= $transactionModalType === 'INCOME' ? ' active' : '' ?>" id="btnIncome" onclick="setType('INCOME')">
        <span class="arrow">↑</span> Income
      </button>
    </div>

    <!-- Amount -->
    <div class="amount-display">
      <input type="number" id="amountInput" name="amount" placeholder="0.00" step="0.01" min="0" value="<?= htmlspecialchars((string) $transactionModalAmount, ENT_QUOTES, 'UTF-8') ?>" required/>
      <span class="currency">TND</span>
    </div>

    <!-- Description + Date -->
    <div class="form-row-2">
      <div class="form-group" style="margin-bottom:0">
        <label>Description <span>*</span></label>
        <input type="text" name="description" placeholder="e.g. Grocery store" value="<?= htmlspecialchars($transactionModalDescription, ENT_QUOTES, 'UTF-8') ?>" required/>
      </div>
      <div class="form-group" style="margin-bottom:0">
        <label>Date <span>*</span></label>
        <div class="date-input-wrap">
          <input type="date" id="dateInput" name="date" value="<?= htmlspecialchars($transactionModalDate, ENT_QUOTES, 'UTF-8') ?>" required/>
          <span class="date-icon">📅</span>
        </div>
      </div>
    </div>

    <div class="form-group" style="margin-top:14px;">
      <label>Budget <span>*</span></label>
      <select name="budgetId" id="budgetSelect" required>
        <option value="">Select a budget</option>
        <?php foreach ($budgets as $budget): ?>
          <?php
            $labelParts = [];
            if (!empty($budget['name'])) {
              $labelParts[] = $budget['name'];
            }
            if (!empty($budget['categoryName'])) {
              $labelParts[] = $budget['categoryName'];
            }
            if (!empty($budget['note'])) {
              $labelParts[] = $budget['note'];
            }
            if (empty($labelParts)) {
              $labelParts[] = 'Budget #' . ($budget['idBudget'] ?? '');
            }
            $budgetLabel = implode(' • ', $labelParts);
          ?>
          <option value="<?= htmlspecialchars($budget['idBudget'], ENT_QUOTES, 'UTF-8') ?>" <?= (string) $transactionModalBudgetId === (string) $budget['idBudget'] ? 'selected' : '' ?>><?= htmlspecialchars($budgetLabel, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group" style="margin-top:14px;">
      <label>Category <span>*</span></label>
      <select name="categoryId" id="categorySelect" required>
        <?php foreach ($categories as $category): ?>
          <option value="<?= htmlspecialchars($category['idCategory'], ENT_QUOTES, 'UTF-8') ?>" <?= (string) $transactionModalCategoryId === (string) $category['idCategory'] ? 'selected' : '' ?>><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Note -->
    <div class="form-group">
      <label>Note (optional)</label>
      <textarea name="note" placeholder="Add a short note about this transaction…"><?= htmlspecialchars($transactionModalNote, ENT_QUOTES, 'UTF-8') ?></textarea>
    </div>

    <div class="modal-footer">
      <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
      <button class="btn-save" type="submit"><?= htmlspecialchars($transactionModalButton, ENT_QUOTES, 'UTF-8') ?></button>
    </div>
    </form>
  </div>
</div>

<script>
  // Set today's date as default
  if (!document.getElementById('dateInput').value) {
    document.getElementById('dateInput').valueAsDate = new Date();
  }

  function openModal() {
    document.getElementById('modalOverlay').classList.add('open');
  }
  function closeModal() {
    document.getElementById('modalOverlay').classList.remove('open');
  }
  function handleOverlayClick(e) {
    if (e.target === e.currentTarget) closeModal();
  }

  function setType(type) {
    const btnE = document.getElementById('btnExpense');
    const btnI = document.getElementById('btnIncome');
    document.getElementById('transCategory').value = type;
    document.getElementById('transType').value = type;
    if (type === 'EXPENSE') {
      btnE.classList.add('active'); btnI.classList.remove('active');
    } else {
      btnI.classList.add('active'); btnE.classList.remove('active');
    }
  }

  function setFilterType(type) {
    document.getElementById('filterType').value = type;
    document.getElementById('transactionFilters').submit();
  }

  // Keyboard close
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
</script>
</body>
</html>