<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

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

function getUserModel() {
    global $pdo;
    return new User($pdo);
}

function userController($action = null, $params = []) {
    if ($action === null) {
        $action = $_REQUEST['action'] ?? '';
    }

    ensureUserAuth();
    $userModel = getUserModel();

    switch ($action) {
        case 'list':
            return ['success' => true, 'users' => $userModel->findAllUsers()];

        case 'get':
            $id = $params['id'] ?? $_GET['id'] ?? null;
            if (!$id) {
                return ['success' => false, 'message' => 'Missing user id'];
            }
            return ['success' => true, 'user' => $userModel->findUserById($id)];

        case 'create':
            $name = trim($params['name'] ?? $_POST['name'] ?? '');
            $lastName = trim($params['lastName'] ?? $_POST['lastName'] ?? '');
            $email = trim($params['email'] ?? $_POST['email'] ?? '');
            $password = $params['password'] ?? $_POST['password'] ?? '';
            $role = trim($params['role'] ?? $_POST['role'] ?? 'user');
            $status = trim($params['status'] ?? $_POST['status'] ?? 'pending');

            if (!$name || !$lastName || !$email || !$password) {
                return ['success' => false, 'message' => 'Missing required fields'];
            }

            return ['success' => $userModel->create($name, $lastName, $email, $password, $role, $status)];

        case 'update':
            $id = $params['id'] ?? $_POST['id'] ?? null;
            $name = trim($params['name'] ?? $_POST['name'] ?? '');
            $lastName = trim($params['lastName'] ?? $_POST['lastName'] ?? '');
            $email = trim($params['email'] ?? $_POST['email'] ?? '');
            $password = $params['password'] ?? $_POST['password'] ?? null;

            if (!$id || !$name || !$lastName || !$email) {
                return ['success' => false, 'message' => 'Missing required fields'];
            }

            return ['success' => $userModel->updateUser($id, $name, $lastName, $email, $password ?: null)];

        case 'status':
            $id = $params['id'] ?? $_POST['id'] ?? null;
            $status = trim($params['status'] ?? $_POST['status'] ?? '');
            if (!$id || !$status) {
                return ['success' => false, 'message' => 'Missing status update values'];
            }
            return ['success' => $userModel->updateStatus($id, $status)];

        case 'delete':
            $id = $params['id'] ?? $_POST['id'] ?? $_GET['id'] ?? null;
            if (!$id) {
                return ['success' => false, 'message' => 'Missing user id'];
            }
            return ['success' => $userModel->deleteUser($id)];

        default:
            return ['success' => false, 'message' => 'Invalid action'];
    }
}

if (!defined('CONTROLLER_INCLUDED')) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(userController());
    exit();
}
