<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header('Location: /pocket_money/views/login.php');
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Category.php';
require_once __DIR__ . '/../../models/Budget.php';
require_once __DIR__ . '/../../models/Transaction.php';
require_once __DIR__ . '/../../models/Alert.php';
require_once __DIR__ . '/../../models/Group.php';
require_once __DIR__ . '/../../models/GroupMember.php';

$userModel = new User($pdo);
$currentUser = $userModel->findUserById($_SESSION['user']['id']) ?: $_SESSION['user'];
$userName = trim(($currentUser['name'] ?? '') . ' ' . ($currentUser['lastName'] ?? ''));
$userInitials = strtoupper(substr($currentUser['name'] ?? 'U', 0, 1) . substr($currentUser['lastName'] ?? ' ', 0, 1));

function sidebarLinkClass($page, $current) {
    return $page === $current ? 'nav-item active' : 'nav-item';
}

function userPageUrl($page) {
    $routes = [
        'dashboard' => '/pocket_money/views/user/dashboard.php',
        'transaction' => '/pocket_money/views/user/transaction.php',
        'budget' => '/pocket_money/views/user/budget.php',
        'category' => '/pocket_money/views/user/category.php',
        'group' => '/pocket_money/views/user/group.php',
        'alert' => '/pocket_money/views/user/alert.php',
        'profile' => '/pocket_money/views/user/profile.php',
        'logout' => '/pocket_money/views/logout.php',
    ];

    return $routes[$page] ?? '/pocket_money/views/user/dashboard.php';
}

function userFormatMoney($amount) {
    return number_format((float) $amount, 0, '.', ',');
}

function userCategoryIcon($name) {
    $normalized = strtolower(trim((string) $name));

    return match ($normalized) {
        'food', 'dining', 'coffee' => '🍽️',
        'transport', 'travel', 'travelling' => '🚗',
        'health', 'pharmacy' => '💊',
        'housing', 'rent' => '🏠',
        'salary', 'income', 'revenue' => '💼',
        'shopping' => '🛍️',
        'software', 'tech' => '💻',
        'group', 'groups' => '👥',
        default => '●',
    };
}

function userCategoryColor($index) {
    $palette = ['#ff6b8a', '#4c9be8', '#7c6af5', '#f5c842', '#00c9a7', '#ff7c3e'];
    return $palette[$index % count($palette)];
}

function userCategoryList() {
    global $pdo;

    $stmt = $pdo->prepare("SELECT idCategory, name, type FROM category ORDER BY name ASC");
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function userTransactionSummary($userId, $filters = []) {
    global $pdo;

    $params = [$userId];
    $where = "t.userId = ?";

    if (!empty($filters['search'])) {
        $search = '%' . $filters['search'] . '%';
        $where .= " AND (t.description LIKE ? OR t.note LIKE ? OR t.transType LIKE ? OR c.name LIKE ? OR c.type LIKE ?)";
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
    }

    if (!empty($filters['type'])) {
        $type = strtoupper($filters['type']);
        if (in_array($type, ['INCOME', 'EXPENSE'], true)) {
            $where .= " AND t.transCategory = ?";
            $params[] = $type;
        }
    }

    if (!empty($filters['categoryId']) && is_numeric($filters['categoryId'])) {
        $where .= " AND t.categoryId = ?";
        $params[] = (int) $filters['categoryId'];
    }

    $stmt = $pdo->prepare("SELECT COALESCE(SUM(CASE WHEN t.transCategory = 'INCOME' THEN t.amout ELSE 0 END), 0) AS income, COALESCE(SUM(CASE WHEN t.transCategory = 'EXPENSE' THEN t.amout ELSE 0 END), 0) AS expenses, COUNT(*) AS transactionCount FROM transaction t LEFT JOIN category c ON c.idCategory = t.categoryId WHERE $where");
    $stmt->execute($params);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['income' => 0, 'expenses' => 0, 'transactionCount' => 0];

    $income = (float) ($summary['income'] ?? 0);
    $expenses = (float) ($summary['expenses'] ?? 0);

    return [
        'income' => $income,
        'expenses' => $expenses,
        'balance' => $income - $expenses,
        'transactions' => (int) ($summary['transactionCount'] ?? 0),
    ];
}

function userFilteredTransactions($userId, $filters = [], $limit = 200) {
    global $pdo;

    $params = [$userId];
    $where = "t.userId = ?";

    if (!empty($filters['search'])) {
        $search = '%' . $filters['search'] . '%';
        $where .= " AND (t.description LIKE ? OR t.note LIKE ? OR t.transType LIKE ? OR c.name LIKE ? OR c.type LIKE ?)";
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
    }

    if (!empty($filters['type'])) {
        $type = strtoupper($filters['type']);
        if (in_array($type, ['INCOME', 'EXPENSE'], true)) {
            $where .= " AND t.transCategory = ?";
            $params[] = $type;
        }
    }

    if (!empty($filters['categoryId']) && is_numeric($filters['categoryId'])) {
        $where .= " AND t.categoryId = ?";
        $params[] = (int) $filters['categoryId'];
    }

    $sort = $filters['sort'] ?? 'date_desc';
    $orderBy = match ($sort) {
        'date_asc' => 't.date ASC, t.idTransaction ASC',
        'amount_desc' => 't.amout DESC, t.date DESC',
        'amount_asc' => 't.amout ASC, t.date DESC',
        'category' => 'c.name ASC, t.date DESC',
        default => 't.date DESC, t.idTransaction DESC',
    };

    $limit = max(1, min(500, (int) $limit));
    $sql = "SELECT t.idTransaction, t.description, t.transCategory, t.date, t.note, t.amout, t.transType, t.categoryId, bt.budgetId, b.note AS budgetNote, b.`limit` AS budgetLimit, c.name AS categoryName, c.type AS categoryType FROM transaction t LEFT JOIN category c ON c.idCategory = t.categoryId LEFT JOIN budgettransaction bt ON bt.transactionId = t.idTransaction LEFT JOIN budget b ON b.idBudget = bt.budgetId WHERE $where ORDER BY $orderBy LIMIT {$limit}";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function userCategoryExpenseSummary($userId) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT c.idCategory, c.name, COALESCE(SUM(CASE WHEN t.transCategory = 'EXPENSE' THEN t.amout ELSE 0 END), 0) AS spent, COALESCE(SUM(CASE WHEN t.transCategory = 'INCOME' THEN t.amout ELSE 0 END), 0) AS income, COUNT(t.idTransaction) AS transactions FROM category c LEFT JOIN transaction t ON t.categoryId = c.idCategory AND t.userId = ? GROUP BY c.idCategory, c.name ORDER BY spent DESC, c.name ASC");
    $stmt->execute([$userId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function userBudgetProgressRows($userId) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT b.idBudget, b.name AS name, b.`limit` AS originalLimit, b.period, b.startDate, b.note, b.sendAlertAt, bc.categoryId, c.name AS categoryName, c.type AS categoryType, COALESCE(totalTotals.totalIncome, 0) AS income, COALESCE(totalTotals.totalExpense, 0) AS spent, COALESCE(b.`limit`, 0) + COALESCE(totalTotals.totalIncome, 0) AS budget FROM budget b LEFT JOIN budgetcategory bc ON bc.budgetId = b.idBudget LEFT JOIN category c ON c.idCategory = bc.categoryId LEFT JOIN (SELECT bt.budgetId, SUM(CASE WHEN t.transCategory = 'INCOME' THEN t.amout ELSE 0 END) AS totalIncome, SUM(CASE WHEN t.transCategory = 'EXPENSE' THEN t.amout ELSE 0 END) AS totalExpense FROM budgettransaction bt INNER JOIN transaction t ON t.idTransaction = bt.transactionId GROUP BY bt.budgetId) totalTotals ON totalTotals.budgetId = b.idBudget WHERE b.userId = ? ORDER BY b.idBudget DESC");
    $stmt->execute([$userId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function userBudgetSummary($userId) {
    $rows = userBudgetProgressRows($userId);
    $summary = ['budget' => 0.0, 'spent' => 0.0, 'active' => 0];

    foreach ($rows as $row) {
        $budget = (float) ($row['budget'] ?? 0);
        $spent = (float) ($row['spent'] ?? 0);
        if ($budget > 0) {
            $summary['active']++;
        }
        $summary['budget'] += $budget;
        $summary['spent'] += $spent;
    }

    $summary['remaining'] = $summary['budget'] - $summary['spent'];
    return $summary;
}

function userAlertRows($userId) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT a.*, b.note AS budgetNote, b.period, b.startDate FROM alert a LEFT JOIN budget b ON b.idBudget = a.budgetId WHERE a.userId = ? ORDER BY a.dateSend DESC, a.idAlert DESC");
    $stmt->execute([$userId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function userGroupRows($userId) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT g.idGroup, g.name, g.description, g.budget, g.spent, g.theme, g.invitCode, gm.role, COUNT(DISTINCT gm2.userId) AS memberCount FROM `group` g INNER JOIN groupmember gm ON gm.groupId = g.idGroup AND gm.userId = ? AND gm.status = 'approved' LEFT JOIN groupmember gm2 ON gm2.groupId = g.idGroup AND gm2.status = 'approved' GROUP BY g.idGroup, g.name, g.description, g.budget, g.spent, g.theme, g.invitCode, gm.role ORDER BY g.idGroup DESC");
    $stmt->execute([$userId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function userProfileMetrics($userId) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT u.*, COALESCE(tx.transactionCount, 0) AS transactionCount, COALESCE(bu.budgetCount, 0) AS budgetCount, COALESCE(gr.groupCount, 0) AS groupCount FROM users u LEFT JOIN (SELECT userId, COUNT(*) AS transactionCount FROM transaction WHERE userId = ? GROUP BY userId) tx ON tx.userId = u.id LEFT JOIN (SELECT userId, COUNT(*) AS budgetCount FROM budget WHERE userId = ? GROUP BY userId) bu ON bu.userId = u.id LEFT JOIN (SELECT userId, COUNT(*) AS groupCount FROM groupmember WHERE userId = ? GROUP BY userId) gr ON gr.userId = u.id WHERE u.id = ? LIMIT 1");
    $stmt->execute([$userId, $userId, $userId, $userId]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

function userRecentTransactions($userId, $limit = 6) {
    global $pdo;

    $limit = max(1, min(50, (int) $limit));
    $stmt = $pdo->prepare("SELECT t.idTransaction, t.description, t.transCategory, t.date, t.note, t.amout, t.transType, t.categoryId, c.name AS categoryName, c.type AS categoryType FROM transaction t LEFT JOIN category c ON c.idCategory = t.categoryId WHERE t.userId = ? ORDER BY t.date DESC, t.idTransaction DESC LIMIT {$limit}");
    $stmt->execute([$userId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function userDashboardData($userId) {
    global $pdo;

    $summary = userTransactionSummary($userId);
    $recentTransactions = userRecentTransactions($userId, 6);
    $budgetRows = userBudgetProgressRows($userId);
    $categorySummary = userCategoryExpenseSummary($userId);

    $monthStmt = $pdo->prepare("SELECT DATE_FORMAT(date, '%b') AS monthLabel, SUM(CASE WHEN transCategory = 'INCOME' THEN amout ELSE 0 END) AS income, SUM(CASE WHEN transCategory = 'EXPENSE' THEN amout ELSE 0 END) AS expenses FROM transaction WHERE userId = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH) GROUP BY DATE_FORMAT(date, '%Y-%m') ORDER BY MIN(date) ASC");
    $monthStmt->execute([$userId]);
    $months = $monthStmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'summary' => $summary,
        'recentTransactions' => $recentTransactions,
        'budgetRows' => $budgetRows,
        'categorySummary' => $categorySummary,
        'months' => $months,
    ];
}

function userTransactionPageData($userId, $filters = []) {
    global $pdo;

    $summary = userTransactionSummary($userId, $filters);
    $transactions = userFilteredTransactions($userId, $filters, 200);
    $categories = userCategoryExpenseSummary($userId);

    $params = [$userId];
    $where = "t.userId = ?";

    if (!empty($filters['search'])) {
        $search = '%' . $filters['search'] . '%';
        $where .= " AND (t.description LIKE ? OR t.note LIKE ? OR t.transType LIKE ? OR c.name LIKE ? OR c.type LIKE ?)";
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
    }

    if (!empty($filters['type'])) {
        $type = strtoupper($filters['type']);
        if (in_array($type, ['INCOME', 'EXPENSE'], true)) {
            $where .= " AND t.transCategory = ?";
            $params[] = $type;
        }
    }

    if (!empty($filters['categoryId']) && is_numeric($filters['categoryId'])) {
        $where .= " AND t.categoryId = ?";
        $params[] = (int) $filters['categoryId'];
    }

    $monthStmt = $pdo->prepare("SELECT DATE_FORMAT(t.date, '%b') AS monthLabel, SUM(CASE WHEN t.transCategory = 'INCOME' THEN t.amout ELSE 0 END) AS income, SUM(CASE WHEN t.transCategory = 'EXPENSE' THEN t.amout ELSE 0 END) AS expenses FROM transaction t LEFT JOIN category c ON c.idCategory = t.categoryId WHERE $where AND t.date >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH) GROUP BY DATE_FORMAT(t.date, '%Y-%m') ORDER BY MIN(t.date) ASC");
    $monthStmt->execute($params);
    $months = $monthStmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'summary' => $summary,
        'transactions' => $transactions,
        'categories' => $categories,
        'months' => $months,
    ];
}

function userBudgetPageData($userId) {
    $summary = userBudgetSummary($userId);
    $rows = userBudgetProgressRows($userId);
    $alerts = [];

    foreach ($rows as $row) {
        $budget = (float) ($row['budget'] ?? 0);
        $spent = (float) ($row['spent'] ?? 0);
        $threshold = (float) ($row['sendAlertAt'] ?? 0);
        if ($budget > 0 && $threshold > 0) {
            $percent = (int) round(($spent / $budget) * 100);
            if ($percent >= $threshold) {
                $alerts[] = [
                    'name' => $row['name'],
                    'budget' => $budget,
                    'spent' => $spent,
                    'percent' => min(100, $percent),
                    'remaining' => max(0, $budget - $spent),
                ];
            }
        }
    }

    usort($alerts, fn($left, $right) => $right['percent'] <=> $left['percent']);

    return [
        'summary' => $summary,
        'rows' => $rows,
        'alerts' => $alerts,
    ];
}

function userBudgetRecords($userId) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT b.idBudget, b.name AS name, b.`limit` AS originalLimit, b.period, b.startDate, b.note, b.sendAlertAt, bc.categoryId, c.name AS categoryName, c.type AS categoryType, COALESCE(totalTotals.totalIncome, 0) AS income, COALESCE(totalTotals.totalExpense, 0) AS spent, COALESCE(b.`limit`, 0) + COALESCE(totalTotals.totalIncome, 0) AS budget FROM budget b LEFT JOIN budgetcategory bc ON bc.budgetId = b.idBudget LEFT JOIN category c ON c.idCategory = bc.categoryId LEFT JOIN (SELECT bt.budgetId, SUM(CASE WHEN t.transCategory = 'INCOME' THEN t.amout ELSE 0 END) AS totalIncome, SUM(CASE WHEN t.transCategory = 'EXPENSE' THEN t.amout ELSE 0 END) AS totalExpense FROM budgettransaction bt INNER JOIN transaction t ON t.idTransaction = bt.transactionId WHERE t.userId = ? GROUP BY bt.budgetId) totalTotals ON totalTotals.budgetId = b.idBudget WHERE b.userId = ? ORDER BY b.idBudget DESC");
    $stmt->execute([$userId, $userId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function userAlertPageData($userId) {
    $alerts = userAlertRows($userId);
    $unreadCount = 0;

    foreach ($alerts as $alert) {
        if ((int) ($alert['isReaded'] ?? 0) === 0) {
            $unreadCount++;
        }
    }

    return [
        'alerts' => $alerts,
        'unreadCount' => $unreadCount,
    ];
}

function userGroupPageData($userId) {
    $groups = userGroupRows($userId);
    $stats = ['count' => 0, 'budget' => 0.0, 'members' => 0];

    foreach ($groups as $group) {
        $stats['count']++;
        $stats['budget'] += (float) ($group['budget'] ?? 0);
        $stats['members'] += (int) ($group['memberCount'] ?? 0);
    }

    return [
        'groups' => $groups,
        'stats' => $stats,
        'inviteCode' => $groups[0]['invitCode'] ?? null,
    ];
}

function userProfilePageData($userId) {
    $profile = userProfileMetrics($userId);
    return [
        'profile' => $profile,
        'summary' => userTransactionSummary($userId),
    ];
}
