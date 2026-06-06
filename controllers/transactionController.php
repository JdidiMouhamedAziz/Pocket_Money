<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Transaction.php';
require_once __DIR__ . '/../models/BudgetTransaction.php';
require_once __DIR__ . '/../models/Budget.php';
require_once __DIR__ . '/../models/Alert.php';

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

function getTransactionModel() {
    global $pdo;
    return new Transaction($pdo);
}

function getBudgetTransactionModel() {
    global $pdo;
    return new BudgetTransaction($pdo);
}

function getBudgetModel() {
    global $pdo;
    return new Budget($pdo);
}

function getAlertModel() {
    global $pdo;
    return new Alert($pdo);
}

function transactionController($action = null, $params = []) {
    global $pdo;

    if ($action === null) {
        $action = $_REQUEST['action'] ?? '';
    }

    ensureUserAuth();
    $transactionModel = getTransactionModel();
    $budgetTransactionModel = getBudgetTransactionModel();

    switch ($action) {
        case 'list':
            return ['success' => true, 'transactions' => $transactionModel->findTransactionByUserId($_SESSION['user']['id'])];

        case 'get':
            $id = $params['id'] ?? $_GET['id'] ?? null;
            if (!$id) {
                return ['success' => false, 'message' => 'Missing transaction id'];
            }
            return ['success' => true, 'transaction' => $transactionModel->findTransactionById($id)];

        case 'create':
            $description = trim($params['description'] ?? $_POST['description'] ?? '');
            $transCategory = strtoupper(trim($params['transCategory'] ?? $_POST['transCategory'] ?? 'EXPENSE'));
            $date = trim($params['date'] ?? $_POST['date'] ?? '');
            $note = trim($params['note'] ?? $_POST['note'] ?? '');
            $amount = trim($params['amount'] ?? $_POST['amount'] ?? $_POST['amout'] ?? '');
            $transType = trim($params['transType'] ?? $_POST['transType'] ?? $transCategory);
            $categoryId = $params['categoryId'] ?? $_POST['categoryId'] ?? null;
            $budgetId = $params['budgetId'] ?? $_POST['budgetId'] ?? null;
            $userId = $_SESSION['user']['id'];

            if (!$description || !$date || !$amount || !$transType || !$categoryId || !$budgetId) {
                return ['success' => false, 'message' => 'Missing required fields'];
            }

            $created = $transactionModel->createTransaction($description, $transCategory, $date, $note, $amount, $transType, $userId, $categoryId);
            if (!$created) {
                return ['success' => false, 'message' => 'Unable to create transaction'];
            }

            $transactionId = $pdo->lastInsertId();
            $budgetTransactionModel->createBudgettransaction($budgetId, $transactionId);
            sendBudgetAlertIfThresholdReached($budgetId, $userId);

            return ['success' => true, 'transactionId' => $transactionId];

        case 'update':
            $id = $params['id'] ?? $_POST['id'] ?? null;
            $description = trim($params['description'] ?? $_POST['description'] ?? '');
            $transCategory = strtoupper(trim($params['transCategory'] ?? $_POST['transCategory'] ?? 'EXPENSE'));
            $date = trim($params['date'] ?? $_POST['date'] ?? '');
            $note = trim($params['note'] ?? $_POST['note'] ?? '');
            $amount = trim($params['amount'] ?? $_POST['amount'] ?? $_POST['amout'] ?? '');
            $transType = trim($params['transType'] ?? $_POST['transType'] ?? $transCategory);
            $categoryId = $params['categoryId'] ?? $_POST['categoryId'] ?? null;
            $budgetId = $params['budgetId'] ?? $_POST['budgetId'] ?? null;

            if (!$id || !$description || !$date || !$amount || !$categoryId || !$budgetId) {
                return ['success' => false, 'message' => 'Missing required fields'];
            }

            $updated = $transactionModel->updateTransaction($id, $description, $transCategory, $date, $note, $amount, $transType, $categoryId);
            if (!$updated) {
                return ['success' => false, 'message' => 'Unable to update transaction'];
            }

            $budgetTransactionModel->deleteBudgettransactionByTransactionId($id);
            $budgetTransactionModel->createBudgettransaction($budgetId, $id);
            sendBudgetAlertIfThresholdReached($budgetId, $userId);

            return ['success' => true];

        case 'delete':
            $id = $params['id'] ?? $_POST['id'] ?? $_GET['id'] ?? null;
            if (!$id) {
                return ['success' => false, 'message' => 'Missing transaction id'];
            }

            return ['success' => $transactionModel->deleteTransaction($id)];

        case 'listByCategory':
            $categoryId = $params['categoryId'] ?? $_GET['categoryId'] ?? null;
            if (!$categoryId) {
                return ['success' => false, 'message' => 'Missing category id'];
            }
            return ['success' => true, 'transactions' => $transactionModel->findTransactionByCategoryId($categoryId)];

        case 'listByType':
            $transType = $params['transType'] ?? $_GET['transType'] ?? null;
            if (!$transType) {
                return ['success' => false, 'message' => 'Missing transaction type'];
            }
            return ['success' => true, 'transactions' => $transactionModel->findtransactionByTransType($transType)];

        case 'listByDate':
            $date = $params['date'] ?? $_GET['date'] ?? null;
            if (!$date) {
                return ['success' => false, 'message' => 'Missing date'];
            }
            return ['success' => true, 'transactions' => $transactionModel->findTransactionByDate($date)];

        default:
            return ['success' => false, 'message' => 'Invalid action'];
    }
}

function sendBudgetAlertIfThresholdReached($budgetId, $userId) {
    global $pdo;

    $budgetModel = getBudgetModel();
    $alertModel = getAlertModel();
    $budget = $budgetModel->findBudgetById($budgetId);
    if (!$budget) {
        return;
    }

    $sendAlertAt = (float) ($budget['sendAlertAt'] ?? 0);
    if ($sendAlertAt <= 0) {
        return;
    }

    $stmt = $pdo->prepare("SELECT SUM(CASE WHEN t.transCategory = 'INCOME' THEN t.amout ELSE 0 END) AS totalIncome, SUM(CASE WHEN t.transCategory = 'EXPENSE' THEN t.amout ELSE 0 END) AS totalExpense FROM budgettransaction bt INNER JOIN transaction t ON t.idTransaction = bt.transactionId WHERE bt.budgetId = ? AND t.userId = ?");
    $stmt->execute([$budgetId, $userId]);
    $totals = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $totalIncome = (float) ($totals['totalIncome'] ?? 0);
    $totalExpense = (float) ($totals['totalExpense'] ?? 0);
    $effectiveLimit = (float) ($budget['limit'] ?? 0) + $totalIncome;

    if ($effectiveLimit <= 0) {
        return;
    }

    $percentUsed = $effectiveLimit > 0 ? round(($totalExpense / $effectiveLimit) * 100, 2) : 0;
    if ($percentUsed < $sendAlertAt) {
        return;
    }

    $existingAlert = $alertModel->findAlertByBudgetId($budgetId, $userId);
    if (!empty($existingAlert)) {
        return;
    }

    $name = trim($budget['name'] ?? 'Budget alert');
    $message = sprintf('Budget "%s" has reached %s%% of its threshold (%s%% used).', $name, $sendAlertAt, $percentUsed);
    $alertModel->createAlert($name, $message, 'system', $userId, $budgetId);
}

if (!defined('CONTROLLER_INCLUDED')) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(transactionController());
    exit();
}
