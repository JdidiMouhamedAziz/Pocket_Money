<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/GroupMember.php';

header('Content-Type: application/json; charset=utf-8');

function respond($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit();
}

if (!isset($_SESSION['user'])) {
    respond(['success' => false, 'message' => 'Unauthorized'], 401);
}

$groupMemberModel = new GroupMember($pdo);
$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'create':
        $groupId = $_POST['groupId'] ?? null;
        $userId = $_POST['userId'] ?? null;
        $role = trim($_POST['role'] ?? 'member');
        $status = trim($_POST['status'] ?? 'pending');

        if (!$groupId || !$userId) {
            respond(['success' => false, 'message' => 'Missing required fields'], 400);
        }

        $created = $groupMemberModel->createMember($groupId, $userId, $role, $status);
        respond(['success' => $created]);
        break;

    case 'update':
        $groupId = $_POST['groupId'] ?? null;
        $userId = $_POST['userId'] ?? null;
        $role = trim($_POST['role'] ?? 'member');
        $status = isset($_POST['status']) ? trim($_POST['status']) : null;

        if (!$groupId || !$userId) {
            respond(['success' => false, 'message' => 'Missing required fields'], 400);
        }

        $updated = $groupMemberModel->updateMember($groupId, $userId, $role, $status);
        respond(['success' => $updated]);
        break;

    case 'delete':
        $groupId = $_POST['groupId'] ?? $_GET['groupId'] ?? null;
        $userId = $_POST['userId'] ?? $_GET['userId'] ?? null;

        if (!$groupId || !$userId) {
            respond(['success' => false, 'message' => 'Missing required fields'], 400);
        }

        $deleted = $groupMemberModel->deleteGroupMember($groupId, $userId);
        respond(['success' => $deleted]);
        break;

    case 'listByGroup':
        $groupId = $_GET['groupId'] ?? null;
        if (!$groupId) {
            respond(['success' => false, 'message' => 'Missing group id'], 400);
        }
        $members = $groupMemberModel->findUsersByGroup($groupId);
        respond(['success' => true, 'members' => $members]);
        break;

    case 'listByUser':
        $userId = $_GET['userId'] ?? $_SESSION['user']['id'];
        $groups = $groupMemberModel->findGroupByUser($userId);
        respond(['success' => true, 'groups' => $groups]);
        break;

    default:
        respond(['success' => false, 'message' => 'Invalid action'], 400);
}
