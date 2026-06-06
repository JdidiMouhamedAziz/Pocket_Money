<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Alert.php';

function respondAlert($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit();
}

function ensureUserAuthAlert() {
    if (!isset($_SESSION['user'])) {
        respondAlert(['success' => false, 'message' => 'Unauthorized'], 401);
    }
}

function alertController($action = null, $params = []) {
    if ($action === null) {
        $action = $_REQUEST['action'] ?? '';
    }

    ensureUserAuthAlert();
    $alertModel = new Alert($GLOBALS['pdo']);
    $userId = $_SESSION['user']['id'] ?? null;

    switch ($action) {
        case 'markAllRead':
            if (!$userId) {
                return ['success' => false, 'message' => 'Missing user id'];
            }
            return ['success' => $alertModel->readAllAlert($userId)];

        case 'read':
            $id = $params['id'] ?? $_POST['id'] ?? $_GET['id'] ?? null;
            if (!$id) {
                return ['success' => false, 'message' => 'Missing alert id'];
            }
            return ['success' => $alertModel->readAlert($id)];

        case 'delete':
            $id = $params['id'] ?? $_POST['id'] ?? $_GET['id'] ?? null;
            if (!$id) {
                return ['success' => false, 'message' => 'Missing alert id'];
            }
            return ['success' => $alertModel->deleteAlert($id)];

        default:
            return ['success' => false, 'message' => 'Invalid action'];
    }
}

if (!defined('CONTROLLER_INCLUDED')) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(alertController());
    exit();
}
