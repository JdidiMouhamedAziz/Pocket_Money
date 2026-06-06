<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/BudgetTransaction.php';

header('Content-Type: application/json; charset=utf-8');

function respond($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit();
}

if (!isset($_SESSION['user'])) {
    respond(['success' => false, 'message' => 'Unauthorized'], 401);
}

$budgetTransactionModel = new BudgetTransaction($pdo);
$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'create':
        $budgetId = $_POST['budgetId'] ?? null;
        $transactionId = $_POST['transactionId'] ?? null;

        if (!$budgetId || !$transactionId) {
            respond(['success' => false, 'message' => 'Missing required fields'], 400);
        }

        $created = $budgetTransactionModel->createBudgettransaction($budgetId, $transactionId);
        respond(['success' => $created]);
        break;

    case 'listByBudget':
        $budgetId = $_GET['budgetId'] ?? null;
        if (!$budgetId) {
            respond(['success' => false, 'message' => 'Missing budget id'], 400);
        }
        $transactions = $budgetTransactionModel->findTransactionByBudgetId($budgetId);
        respond(['success' => true, 'transactions' => $transactions]);
        break;

    case 'listByTransaction':
        $transactionId = $_GET['transactionId'] ?? null;
        if (!$transactionId) {
            respond(['success' => false, 'message' => 'Missing transaction id'], 400);
        }
        $budgets = $budgetTransactionModel->findbugetBytransactionId($transactionId);
        respond(['success' => true, 'budgets' => $budgets]);
        break;

    default:
        respond(['success' => false, 'message' => 'Invalid action'], 400);
}
