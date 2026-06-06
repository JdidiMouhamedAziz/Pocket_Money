<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/admin_helpers.php';

$resource = $_REQUEST['resource'] ?? '';
$action = $_REQUEST['action'] ?? '';
$redirect = $_SERVER['HTTP_REFERER'] ?? 'dashboard.php';
$result = ['success' => false, 'message' => 'Unknown action'];

try {
    switch ($resource) {
        case 'user':
            define('CONTROLLER_INCLUDED', true);
            require_once __DIR__ . '/../../controllers/userController.php';
            $result = userController($action, $_REQUEST);
            break;

        case 'category':
            define('CONTROLLER_INCLUDED', true);
            require_once __DIR__ . '/../../controllers/categoryController.php';
            $result = categoryController($action, $_REQUEST);
            break;

        case 'transaction':
            define('CONTROLLER_INCLUDED', true);
            require_once __DIR__ . '/../../controllers/transactionController.php';
            $result = transactionController($action, $_REQUEST);
            break;

        case 'budget':
            define('CONTROLLER_INCLUDED', true);
            require_once __DIR__ . '/../../controllers/budgetController.php';
            $result = budgetController($action, $_REQUEST);
            break;

        case 'alert':
            define('CONTROLLER_INCLUDED', true);
            require_once __DIR__ . '/../../controllers/alertController.php';
            $result = alertController($action, $_REQUEST);
            break;

        default:
            $result = ['success' => false, 'message' => 'Invalid resource'];
    }
} catch (Throwable $e) {
    $result = ['success' => false, 'message' => $e->getMessage()];
}

if (!is_array($result)) {
    $result = ['success' => false, 'message' => 'Invalid response from controller'];
}

$message = trim((string) ($result['message'] ?? '')) ?: ($result['success'] ? 'Saved successfully.' : 'Unable to complete the requested action.');
$_SESSION['admin_flash'] = ['success' => !empty($result['success']), 'message' => $message];

header('Location: ' . $redirect);
exit();
