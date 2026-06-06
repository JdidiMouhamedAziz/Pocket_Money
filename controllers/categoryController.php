<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Budget.php';
require_once __DIR__ . '/../models/BudgetCategory.php';
require_once __DIR__ . '/../models/Transaction.php';

function respond($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit();
}

function ensureUserAuth() {
    if (!isset($_SESSION['user'])) {
        respond(['success' => false, 'message' => 'Unauthorized'], 401);
    }
}

function getCategoryModel() {
    global $pdo;
    return new Category($pdo);
}

function categoryViewData($userId) {
    global $pdo;

    $categoryModel = getCategoryModel();
    $categories = $categoryModel->findAllCategories();

    $categoryStats = [];
    $totals = [
        'budget' => 0.0,
        'income' => 0.0,
        'expenses' => 0.0,
        'transactions' => 0,
    ];

    if (!$userId) {
        return [
            'categories' => $categories,
            'categoryStats' => $categoryStats,
            'totals' => $totals,
        ];
    }

    $transactionStmt = $pdo->prepare(
        "SELECT categoryId,
                COUNT(*) AS transactionCount,
                COALESCE(SUM(CASE WHEN transCategory = 'INCOME' THEN amout ELSE 0 END), 0) AS income,
                COALESCE(SUM(CASE WHEN transCategory = 'EXPENSE' THEN amout ELSE 0 END), 0) AS expenses
         FROM transaction
         WHERE userId = ?
         GROUP BY categoryId"
    );
    $transactionStmt->execute([$userId]);

    foreach ($transactionStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $categoryId = (int) $row['categoryId'];
        $categoryStats[$categoryId] = [
            'income' => (float) $row['income'],
            'expenses' => (float) $row['expenses'],
            'transactions' => (int) $row['transactionCount'],
            'budget' => 0.0,
        ];
    }

    $budgetStmt = $pdo->prepare(
        "SELECT bc.categoryId,
                COALESCE(SUM(bc.limitAmout), 0) AS budget
         FROM budgetcategory bc
         INNER JOIN budget b ON b.idBudget = bc.budgetId
         WHERE b.userId = ?
         GROUP BY bc.categoryId"
    );
    $budgetStmt->execute([$userId]);

    foreach ($budgetStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $categoryId = (int) $row['categoryId'];
        if (!isset($categoryStats[$categoryId])) {
            $categoryStats[$categoryId] = [
                'income' => 0.0,
                'expenses' => 0.0,
                'transactions' => 0,
                'budget' => 0.0,
            ];
        }

        $categoryStats[$categoryId]['budget'] = (float) $row['budget'];
    }

    foreach ($categories as $category) {
        $categoryId = (int) $category['idCategory'];
        if (!isset($categoryStats[$categoryId])) {
            $categoryStats[$categoryId] = [
                'income' => 0.0,
                'expenses' => 0.0,
                'transactions' => 0,
                'budget' => 0.0,
            ];
        }

        $totals['budget'] += $categoryStats[$categoryId]['budget'];
        $totals['income'] += $categoryStats[$categoryId]['income'];
        $totals['expenses'] += $categoryStats[$categoryId]['expenses'];
        $totals['transactions'] += $categoryStats[$categoryId]['transactions'];
    }

    return [
        'categories' => $categories,
        'categoryStats' => $categoryStats,
        'totals' => $totals,
    ];
}

function categoryController($action = null, $params = []) {
    if ($action === null) {
        $action = $_REQUEST['action'] ?? '';
    }

    ensureUserAuth();
    $categoryModel = getCategoryModel();

    switch ($action) {
        case 'list':
            return ['success' => true, 'categories' => $categoryModel->findAllCategories()];

        case 'get':
            $id = $params['id'] ?? $_GET['id'] ?? null;
            if (!$id) {
                return ['success' => false, 'message' => 'Missing category id'];
            }
            return ['success' => true, 'category' => $categoryModel->findcategoryById($id)];

        case 'create':
            $name = trim($params['name'] ?? $_POST['name'] ?? '');
            $type = trim($params['type'] ?? $_POST['type'] ?? 'expense');

            if (!$name) {
                return ['success' => false, 'message' => 'Missing category name'];
            }

            return ['success' => $categoryModel->createCategory($name, $type)];

        case 'update':
            $id = $params['id'] ?? $_POST['id'] ?? null;
            $name = trim($params['name'] ?? $_POST['name'] ?? '');
            $type = trim($params['type'] ?? $_POST['type'] ?? 'expense');

            if (!$id || !$name) {
                return ['success' => false, 'message' => 'Missing required fields'];
            }

            return ['success' => $categoryModel->updateCategory($id, $name, $type)];

        case 'delete':
            $id = $params['id'] ?? $_POST['id'] ?? $_GET['id'] ?? null;
            if (!$id) {
                return ['success' => false, 'message' => 'Missing category id'];
            }

            $categoryModel->deleteCategory($id);
            return ['success' => true];

        default:
            return ['success' => false, 'message' => 'Invalid action'];
    }
}

if (!defined('CONTROLLER_INCLUDED')) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(categoryController());
    exit();
}
