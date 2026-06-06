<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';

$allowedTypes = [
    'transactions' => ['label' => 'Transactions', 'sql' => 'SELECT * FROM `transaction`'],
    'users' => ['label' => 'Users', 'sql' => 'SELECT * FROM `users`'],
    'categories' => ['label' => 'Categories', 'sql' => 'SELECT * FROM `category`'],
    'budgets' => ['label' => 'Budgets', 'sql' => 'SELECT * FROM `budget`'],
    'alerts' => ['label' => 'Alerts', 'sql' => 'SELECT * FROM `alert`'],
];

$type = strtolower(trim((string) ($_GET['type'] ?? 'transactions')));
if (!array_key_exists($type, $allowedTypes)) {
    $type = 'transactions';
}

if (isset($_GET['download']) && $_GET['download'] === '1') {
    $definition = $allowedTypes[$type];
    $stmt = $pdo->query($definition['sql']);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $filename = sprintf('%s-export-%s.csv', $type, date('Y-m-d'));

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    if ($output === false) {
        http_response_code(500);
        echo 'Unable to create export file.';
        exit();
    }

    if (!empty($rows)) {
        fputcsv($output, array_keys($rows[0]));
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
    } else {
        $columnCount = $stmt->columnCount();
        $headers = [];
        for ($i = 0; $i < $columnCount; $i++) {
            $meta = $stmt->getColumnMeta($i);
            $headers[] = $meta['name'] ?? 'column_' . $i;
        }
        fputcsv($output, $headers);
    }

    fclose($output);
    exit();
}

$pageTitle = 'Export Data';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>BudgetPro – Export Data</title>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/pocket_money/public/css/admin/dashboard.css" />
  <style>
    .export-grid {
      display: grid;
      gap: 18px;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      margin-top: 22px;
    }
    .export-card {
      background: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 20px;
      box-shadow: 0 18px 50px rgba(15, 23, 42, 0.06);
      padding: 24px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      min-height: 190px;
      transition: transform .18s ease, border-color .18s ease;
    }
    .export-card:hover {
      transform: translateY(-3px);
      border-color: rgba(79, 70, 229, 0.24);
    }
    .export-card h3 {
      margin: 0 0 10px;
      font-size: 1.1rem;
      font-weight: 700;
      color: #111827;
    }
    .export-card p {
      margin: 0 0 22px;
      color: #4b5563;
      line-height: 1.7;
      font-size: 0.95rem;
    }
    .export-card .card-meta {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 14px;
      color: #6b7280;
      font-size: 0.82rem;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      font-weight: 700;
    }
    .export-card .btn-export {
      width: 100%;
      justify-content: center;
      font-size: 0.92rem;
      padding: 12px 16px;
      border-radius: 14px;
      box-shadow: 0 12px 30px rgba(79, 70, 229, .12);
    }
    .export-card .btn-export:hover {
      opacity: 0.96;
    }
    .header-description {
      color: #4b5563;
      max-width: 560px;
      margin-top: 10px;
      line-height: 1.75;
    }
    .page-header {
      gap: 18px;
      align-items: flex-start;
    }
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
      <a class="nav-item" href="categories.php"><span class="nav-icon">🗂️</span> Categories</a>
      <a class="nav-item" href="alerts.php"><span class="nav-icon">🔔</span> Alerts</a>
      <a class="nav-item active" href="export_data.php"><span class="nav-icon">⬇️</span> Export Data</a>
      <a class="nav-item" href="profile.php"><span class="nav-icon">⚙️</span> Settings</a>
    </nav>
    <div class="sidebar-footer">
      <a class="btn-logout" href="/pocket_money/views/logout.php">Logout</a>
    </div>
  </aside>

  <div class="main">
    <div class="content">
      <div class="page-header">
        <div>
          <h1>Export Data</h1>
          <p class="header-description">Choose the dataset you want to download. Each export creates a clean CSV file that can be opened in Excel, Google Sheets, or your preferred reporting tool.</p>
        </div>
      </div>

      <div class="export-grid">
        <?php foreach ($allowedTypes as $key => $definition): ?>
          <div class="export-card">
            <div>
              <div class="card-meta">CSV export</div>
              <h3><?= htmlspecialchars($definition['label'], ENT_QUOTES, 'UTF-8') ?></h3>
              <p>Download the complete <?= strtolower(htmlspecialchars($definition['label'], ENT_QUOTES, 'UTF-8')) ?> dataset for reporting, auditing, or archive purposes.</p>
            </div>
            <a class="btn-export" href="?download=1&type=<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>">Download <?= htmlspecialchars($definition['label'], ENT_QUOTES, 'UTF-8') ?></a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</body>
</html>
