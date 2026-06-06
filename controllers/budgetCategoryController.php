<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/BudgetCategory.php';

header('Content-Type: application/json; charset=utf-8');

function respond($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit();
}

if (!isset($_SESSION['user'])) {
    respond(['success' => false, 'message' => 'Unauthorized'], 401);
}

$budgetCategoryModel = new BudgetCategory($pdo);
$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'create':
        $budgetId = $_POST['budgetId'] ?? null;
        $categoryId = $_POST['categoryId'] ?? null;
        $limitAmount = trim($_POST['limitAmount'] ?? '0');

        if (!$budgetId || !$categoryId) {
            respond(['success' => false, 'message' => 'Missing required fields'], 400);
        }

        $created = $budgetCategoryModel->createBudgetCategory($categoryId, $budgetId, $limitAmount);
        respond(['success' => $created]);
        break;

    case 'listByBudget':
        $budgetId = $_GET['budgetId'] ?? null;
        if (!$budgetId) {
            respond(['success' => false, 'message' => 'Missing budget id'], 400);
        }
        $categories = $budgetCategoryModel->findCategoryByBudget($budgetId);
        respond(['success' => true, 'categories' => $categories]);
        break;

    case 'listByCategory':
        $categoryId = $_GET['categoryId'] ?? null;
        if (!$categoryId) {
            respond(['success' => false, 'message' => 'Missing category id'], 400);
        }
        $budgets = $budgetCategoryModel->findBudgetByCategory($categoryId);
        respond(['success' => true, 'budgets' => $budgets]);
        break;

    default:
        respond(['success' => false, 'message' => 'Invalid action'], 400);
}
