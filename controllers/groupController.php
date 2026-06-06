<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Group.php';
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

$groupModel = new Group($pdo);
$groupMemberModel = new GroupMember($pdo);
$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'list':
        respond(['success' => true, 'groups' => []]);
        break;

    case 'get':
        $id = $_GET['id'] ?? null;
        if (!$id) {
            respond(['success' => false, 'message' => 'Missing group id'], 400);
        }
        $group = $groupModel->findGroupById($id);
        respond(['success' => true, 'group' => $group]);
        break;

    case 'create':
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $budget = trim($_POST['budget'] ?? '0');
        $spent = trim($_POST['spent'] ?? '0');
        $theme = trim($_POST['theme'] ?? '');
        $budgetId = $_POST['budgetId'] ?? null;

        if (!$name || !$description || !$budgetId) {
            respond(['success' => false, 'message' => 'Missing required fields'], 400);
        }

        $created = $groupModel->createGroup($name, $description, $budget, $spent, $theme, $budgetId);
        respond(['success' => $created]);
        break;

    case 'update':
        $id = $_POST['id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $budget = trim($_POST['budget'] ?? '0');
        $spent = trim($_POST['spent'] ?? '0');
        $theme = trim($_POST['theme'] ?? '');
        $budgetId = $_POST['budgetId'] ?? null;

        if (!$id || !$name || !$description || !$budgetId) {
            respond(['success' => false, 'message' => 'Missing required fields'], 400);
        }

        if (!$groupModel->isOwner($id, $_SESSION['user']['id'])) {
            respond(['success' => false, 'message' => 'Only the group owner can update this group'], 403);
        }

        $updated = $groupModel->updateGroup($id, $name, $description, $budget, $theme, $spent, null, $budgetId);
        respond(['success' => $updated]);
        break;

    case 'delete':
        $id = $_POST['id'] ?? $_GET['id'] ?? null;
        if (!$id) {
            respond(['success' => false, 'message' => 'Missing group id'], 400);
        }

        if (!$groupModel->isOwner($id, $_SESSION['user']['id'])) {
            respond(['success' => false, 'message' => 'Only the group owner can delete this group'], 403);
        }

        $deleted = $groupModel->deleteGroup($id);
        respond(['success' => $deleted]);
        break;

    default:
        respond(['success' => false, 'message' => 'Invalid action'], 400);
}
