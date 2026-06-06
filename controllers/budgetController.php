<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Budget.php';
require_once __DIR__ . '/../models/BudgetCategory.php';

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

function getBudgetModel() {
    global $pdo;
    return new Budget($pdo);
}

function getBudgetCategoryModel() {
    global $pdo;
    return new BudgetCategory($pdo);
}

function budgetController($action = null, $params = []) {
    global $pdo;

    if ($action === null) {
        $action = $_REQUEST['action'] ?? '';
    }

    ensureUserAuth();
    $budgetModel = getBudgetModel();
    $budgetCategoryModel = getBudgetCategoryModel();

    switch ($action) {
        case 'list':
            return ['success' => true, 'budgets' => $budgetModel->findAllBudget()];

        case 'get':
            $id = $params['id'] ?? $_GET['id'] ?? null;
            if (!$id) {
                return ['success' => false, 'message' => 'Missing budget id'];
            }
            return ['success' => true, 'budget' => $budgetModel->findBudgetById($id)];

        case 'create':
            $name = trim($params['name'] ?? $_POST['name'] ?? '');
            $limit = trim($params['limit'] ?? $_POST['limit'] ?? '');
            $period = trim($params['period'] ?? $_POST['period'] ?? '');
            $startDate = trim($params['startDate'] ?? $_POST['startDate'] ?? '');
            $note = trim($params['note'] ?? $_POST['note'] ?? '');
            $sendAlertAt = trim($params['sendAlertAt'] ?? $_POST['sendAlertAt'] ?? '');
            $categoryId = $params['categoryId'] ?? $_POST['categoryId'] ?? null;
            $userId = $_SESSION['user']['id'];

            if (!$name || !$limit || !$period || !$startDate || !$categoryId) {
                return ['success' => false, 'message' => 'Missing required fields'];
            }

            $created = $budgetModel->createBdget($name, $limit, $period, $startDate, $note, $sendAlertAt, $userId);
            if (!$created) {
                return ['success' => false, 'message' => 'Unable to create budget'];
            }

            $budgetId = (int) $pdo->lastInsertId();
            $budgetCategoryModel->createBudgetCategory($categoryId, $budgetId, $limit);

            return ['success' => true, 'budgetId' => $budgetId];

        case 'update':
            $id = $params['id'] ?? $_POST['id'] ?? null;
            $name = trim($params['name'] ?? $_POST['name'] ?? '');
            $limit = trim($params['limit'] ?? $_POST['limit'] ?? '');
            $period = trim($params['period'] ?? $_POST['period'] ?? '');
            $startDate = trim($params['startDate'] ?? $_POST['startDate'] ?? '');
            $note = trim($params['note'] ?? $_POST['note'] ?? '');
            $sendAlertAt = trim($params['sendAlertAt'] ?? $_POST['sendAlertAt'] ?? '');
            $categoryId = $params['categoryId'] ?? $_POST['categoryId'] ?? null;

            if (!$id || !$name || !$limit || !$period || !$startDate || !$categoryId) {
                return ['success' => false, 'message' => 'Missing required fields'];
            }

            $updated = $budgetModel->updateBudget($id, $name, $limit, $period, $startDate, $note, $sendAlertAt);
            $budgetCategoryModel->updateBudgetCategory($id, $categoryId, $limit);

            return ['success' => $updated];

        case 'delete':
            $id = $params['id'] ?? $_POST['id'] ?? $_GET['id'] ?? null;
            if (!$id) {
                return ['success' => false, 'message' => 'Missing budget id'];
            }

            $budget = $budgetModel->findBudgetById($id);
            if ($budget) {
                $links = $budgetCategoryModel->findCategoryByBudget($id);
                foreach ($links as $link) {
                    $budgetCategoryModel->deleteBudgetCategory($id, $link['categoryId']);
                }
            }
            return ['success' => $budgetModel->deleteBudget($id)];

        default:
            return ['success' => false, 'message' => 'Invalid action'];
    }
}

if (!defined('CONTROLLER_INCLUDED')) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(budgetController());
    exit();
}
