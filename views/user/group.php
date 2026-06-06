<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/shared.php';
require_once __DIR__ . '/../../models/Group.php';
require_once __DIR__ . '/../../models/GroupMember.php';
require_once __DIR__ . '/../../models/Alert.php';

$currentUserId = $currentUser['id'] ?? ($_SESSION['user']['id'] ?? null);
$groupModel = new Group($pdo);
$groupMemberModel = new GroupMember($pdo);
$alertModel = new Alert($pdo);
$userBudgets = userBudgetRecords($currentUserId);
$joinError = '';
$joinSuccess = '';
$actionError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crud_action'])) {
  $name = trim($_POST['name'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $budget = (float) ($_POST['budget'] ?? 0);
  $spent = (float) ($_POST['spent'] ?? 0);
  $theme = trim($_POST['theme'] ?? 'purple');
  $budgetId = $_POST['budgetId'] ?? null;

  if ($_POST['crud_action'] === 'createGroup' && $name && $description && $budgetId) {
    $groupModel->createGroup($name, $description, $budget, $spent, $theme, $budgetId);
    header('Location: ' . userPageUrl('group'));
    exit;
  }

  if ($_POST['crud_action'] === 'joinGroup') {
    $inviteCode = trim($_POST['inviteCode'] ?? '');
    if ($inviteCode) {
      $groupInfo = $groupModel->findGroupByInviteCode($inviteCode);
      if ($groupInfo) {
        $membership = $groupMemberModel->findMemberByGroupAndUser($groupInfo['idGroup'], $currentUserId);
        if (!$membership) {
          $groupMemberModel->createMember($groupInfo['idGroup'], $currentUserId, 'member', 'pending');

          $ownerStmt = $pdo->prepare("SELECT userId FROM groupmember WHERE groupId = ? AND role = 'owner' AND status = 'approved'");
          $ownerStmt->execute([$groupInfo['idGroup']]);
          $owners = $ownerStmt->fetchAll(PDO::FETCH_ASSOC);
          foreach ($owners as $owner) {
            $alertModel->createAlert(
              'Group join request',
              'A user requested to join the group "' . $groupInfo['name'] . '".',
              'group_request',
              $owner['userId'],
              $groupInfo['budgetId'] ?? null
            );
          }

          $joinSuccess = 'Your join request has been sent. The group owner will review it soon.';
        } else {
          $joinError = 'You already have a membership or request for this group.';
        }
      } else {
        $joinError = 'Invite code not found. Please check and try again.';
      }
    } else {
      $joinError = 'Please enter a valid invite code.';
    }
  }

  if ($_POST['crud_action'] === 'approveGroupMember' && !empty($_POST['groupId']) && !empty($_POST['userId'])) {
    $groupId = (int) $_POST['groupId'];
    $userId = (int) $_POST['userId'];
    $groupMemberModel->updateMember($groupId, $userId, 'member', 'approved');
    $alertModel->createAlert('Group join approved', 'Your request to join the group has been approved.', 'group_request', $userId, null);
    header('Location: ' . userPageUrl('group'));
    exit;
  }

  if ($_POST['crud_action'] === 'rejectGroupMember' && !empty($_POST['groupId']) && !empty($_POST['userId'])) {
    $groupId = (int) $_POST['groupId'];
    $userId = (int) $_POST['userId'];
    $groupMemberModel->updateMember($groupId, $userId, 'member', 'rejected');
    $alertModel->createAlert('Group join denied', 'Your request to join the group has been denied.', 'group_request', $userId, null);
    header('Location: ' . userPageUrl('group'));
    exit;
  }

  if ($_POST['crud_action'] === 'updateGroup' && !empty($_POST['idGroup']) && $name && $description && $budgetId) {
    $groupId = (int) $_POST['idGroup'];
    if (!$groupModel->isOwner($groupId, $currentUserId)) {
      $actionError = 'Only the group owner can update this group.';
    } else {
      $groupModel->updateGroup($groupId, $name, $description, $budget, $theme, $spent, null, $budgetId);
      header('Location: ' . userPageUrl('group'));
      exit;
    }
  }

  if ($_POST['crud_action'] === 'deleteGroup' && !empty($_POST['idGroup'])) {
    $groupId = (int) $_POST['idGroup'];
    if (!$groupModel->isOwner($groupId, $currentUserId)) {
      $actionError = 'Only the group owner can delete this group.';
    } else {
      $groupModel->deleteGroup($groupId);
      header('Location: ' . userPageUrl('group'));
      exit;
    }
  }
}

$editingGroup = null;
if (!empty($_GET['edit'])) {
  $editingGroup = $groupModel->findGroupById((int) $_GET['edit']);
  if ($editingGroup) {
    $ownerMember = $groupMemberModel->findMemberByGroupAndUser((int) $editingGroup['idGroup'], $currentUserId);
    if (!$ownerMember || $ownerMember['role'] !== 'owner' || $ownerMember['status'] !== 'approved') {
      $editingGroup = null;
    }
  }
}

$groupData = userGroupPageData($currentUserId);
$groups = $groupData['groups'] ?? [];
$groupStats = $groupData['stats'] ?? ['count' => 0, 'budget' => 0, 'members' => 0];
$inviteCode = $groupData['inviteCode'] ?? 'INVITE-CODE';
$pendingRequests = $groupMemberModel->findPendingRequestsForOwner($currentUserId);
$displayName = $userName ?: trim(($currentUser['name'] ?? 'User') . ' ' . ($currentUser['lastName'] ?? ''));
$displayInitials = $userInitials ?: 'U';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Finzo – My Groups</title>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet"/>
  <style>
    :root{--bg:#f4f5fb;--sidebar-bg:#2d2d3a;--purple:#7c6af5;--purple-light:#f0edff;--teal:#00c9a7;--red:#ff6b8a;--yellow:#f5c842;--orange:#ff7c3e;--white:#ffffff;--text-dark:#1a1a2e;--text-mid:#555770;--text-muted:#9295a8;--sidebar-text:#b0b3c6;--sidebar-label:#6b6e80;--card-radius:14px;--shadow:0 2px 16px rgba(0,0,0,.07);}
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text-dark);display:flex;min-height:100vh;}
    h1,h2,h3,h4{font-family:'Sora',sans-serif;}
    .sidebar{width:200px;flex-shrink:0;background:var(--sidebar-bg);display:flex;flex-direction:column;padding:28px 0 20px;min-height:100vh;position:fixed;left:0;top:0;bottom:0;}
    .sidebar-logo{display:flex;align-items:center;gap:10px;padding:0 22px;margin-bottom:36px;}
    .logo-icon{width:40px;height:40px;border-radius:50%;flex-shrink:0;background:conic-gradient(#f5c842 0deg 90deg,#00c9a7 90deg 180deg,#ff7c3e 180deg 270deg,#7c6af5 270deg 360deg);display:flex;align-items:center;justify-content:center;position:relative;}
    .logo-icon::before{content:'';position:absolute;width:26px;height:26px;border-radius:50%;background:var(--sidebar-bg);}
    .logo-icon span{position:relative;z-index:1;font-size:.8rem;font-weight:800;color:#fff;}
    .sidebar-logo h2{font-size:1.3rem;font-weight:800;color:#fff;}
    .sidebar-section-label{font-size:.68rem;font-weight:600;color:var(--sidebar-label);letter-spacing:.1em;text-transform:uppercase;padding:0 22px;margin-bottom:8px;margin-top:10px;}
    .nav-item{display:flex;align-items:center;gap:11px;padding:11px 22px;cursor:pointer;color:var(--sidebar-text);font-size:.92rem;font-weight:500;text-decoration:none;transition:background .15s,color .15s;}
    .nav-item:hover{background:rgba(255,255,255,.06);color:#fff;}
    .nav-item.active{background:var(--purple);color:#fff;font-weight:700;border-radius:10px;margin:0 10px;padding:11px 12px;}
    .nav-icon{font-size:1rem;width:20px;text-align:center;}
    .sidebar-spacer{flex:1;}
    .sidebar-user{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:14px 22px;border-top:1px solid rgba(255,255,255,.08);}
    .sidebar-user-info{display:flex;flex-direction:column;gap:4px;min-width:0;}
    .user-name a{color:#fff;text-decoration:none;}
    .btn-logout{background:var(--yellow);color:#7a5c00;border:none;border-radius:9px;padding:9px 12px;font-family:'Sora',sans-serif;font-weight:700;font-size:.78rem;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;}
    .btn-logout:hover{opacity:.9;}
    .user-ava{width:36px;height:36px;border-radius:50%;background:var(--purple);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.78rem;font-weight:700;flex-shrink:0;}
    .user-name{font-size:.85rem;font-weight:600;color:#fff;}
    .user-plan{font-size:.72rem;color:var(--sidebar-label);}
    .main{margin-left:200px;flex:1;padding:30px 28px;}

    /* TOPBAR */
    .topbar{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:6px;}
    .topbar h1{font-size:1.4rem;font-weight:800;}
    .topbar-right{display:flex;gap:8px;}
    .btn-join{background:var(--white);border:1.5px solid #e2e3ee;border-radius:10px;padding:9px 16px;font-family:'Sora',sans-serif;font-weight:700;font-size:.83rem;color:var(--purple);cursor:pointer;transition:background .15s;}
    .btn-join:hover{background:var(--purple-light);}
    .btn-create-grp{background:var(--purple);color:#fff;border:none;border-radius:10px;padding:9px 16px;font-family:'Sora',sans-serif;font-weight:700;font-size:.83rem;cursor:pointer;box-shadow:0 4px 14px rgba(124,106,245,.3);transition:opacity .2s;}
    .btn-create-grp:hover{opacity:.9;}
    .invite-row{display:flex;align-items:center;gap:8px;margin-bottom:22px;}
    .invite-label{font-size:.82rem;color:var(--text-muted);}
    .invite-code{font-family:'Sora',sans-serif;font-size:.85rem;font-weight:700;color:var(--purple);background:var(--purple-light);border:1.5px solid #c8beff;border-radius:8px;padding:4px 12px;letter-spacing:.08em;}

    /* STAT ROW */
    .stat-row{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:26px;}
    .stat-card{border-radius:var(--card-radius);padding:20px 22px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;box-shadow:var(--shadow);}
    .stat-card.purple-card{background:var(--purple-light);}
    .stat-card.teal-card{background:#e6faf6;}
    .stat-card.yellow-card{background:#fff8e0;}
    .stat-num{font-family:'Sora',sans-serif;font-size:2.2rem;font-weight:800;margin-bottom:4px;}
    .stat-num.c-purple{color:var(--purple);}
    .stat-num.c-teal{color:var(--teal);}
    .stat-num.c-yellow{color:#b8900a;}
    .stat-label{font-size:.8rem;font-weight:600;color:var(--text-muted);}

    /* GROUPS GRID */
    .groups-section-label{font-size:.85rem;font-weight:700;color:var(--text-dark);margin-bottom:14px;}
    .groups-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}

    /* GROUP CARD */
    .group-card{background:var(--white);border-radius:var(--card-radius);overflow:hidden;box-shadow:var(--shadow);}
    .group-banner{padding:14px 16px;display:flex;align-items:center;justify-content:space-between;}
    .group-banner.purple{background:var(--purple);}
    .group-banner.teal{background:var(--teal);}
    .group-banner.orange{background:var(--orange);}
    .role-badge{font-size:.72rem;font-weight:700;padding:4px 12px;border-radius:50px;background:rgba(255,255,255,.25);color:#fff;letter-spacing:.04em;}
    .group-info-btn{width:26px;height:26px;border-radius:50%;background:rgba(255,255,255,.2);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.8rem;color:#fff;}
    .group-body{padding:16px;}
    .group-name{font-size:1rem;font-weight:700;margin-bottom:4px;}
    .group-desc{font-size:.8rem;color:var(--text-muted);margin-bottom:12px;line-height:1.45;}
    .member-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;}
    .member-avas{display:flex;}
    .mava{width:26px;height:26px;border-radius:50%;border:2px solid #fff;display:flex;align-items:center;justify-content:center;font-size:.62rem;font-weight:700;margin-left:-7px;color:#fff;}
    .mava:first-child{margin-left:0;}
    .member-count{font-size:.75rem;color:var(--text-muted);margin-left:8px;}
    .group-stats{display:flex;gap:24px;padding-top:12px;border-top:1px solid #f2f3f8;}
    .gstat-label{font-size:.72rem;color:var(--text-muted);margin-bottom:3px;}
    .gstat-val{font-family:'Sora',sans-serif;font-size:.9rem;font-weight:700;}
    .gstat-val.red{color:var(--red);}
    .gstat-val.dark{color:var(--text-dark);}

    /* CREATE GROUP CARD */
    .create-group-card{background:var(--purple-light);border:2px dashed #c8beff;border-radius:var(--card-radius);display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:200px;cursor:pointer;transition:background .2s;gap:10px;text-align:center;padding:24px;}
    .create-group-card:hover{background:#e2deff;}
    .cg-plus{width:44px;height:44px;border-radius:12px;background:rgba(124,106,245,.15);border:2px solid #c8beff;display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:var(--purple);}
    .create-group-card h4{font-size:1rem;font-weight:700;color:var(--purple);}
    .create-group-card p{font-size:.8rem;color:#9d90f5;line-height:1.5;}
    .modal-overlay{position:fixed;inset:0;background:rgba(12,16,32,.45);display:none;align-items:center;justify-content:center;z-index:40;padding:20px;}
    .modal-overlay.open{display:flex;}
    .modal{width:min(560px,100%);background:#fff;border-radius:18px;box-shadow:0 25px 60px rgba(8,12,30,.25);overflow:hidden;}
    .modal-header{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid #eceef6;}
    .modal-header h3{font-size:1.05rem;}
    .modal-close{border:none;background:none;font-size:1.2rem;cursor:pointer;color:var(--text-muted);}
    .modal-body{padding:20px 22px;display:grid;gap:14px;}
    .field{display:grid;gap:6px;}
    .field label{font-size:.8rem;font-weight:700;color:var(--text-mid);}
    .field input,.field textarea,.field select{width:100%;border:1px solid #dde0ea;border-radius:10px;padding:10px 12px;font:inherit;background:#fff;}
    .modal-actions{display:flex;justify-content:flex-end;gap:10px;padding:0 22px 20px;}
    .btn-secondary,.btn-primary{border:none;border-radius:10px;padding:10px 16px;font-family:'Sora',sans-serif;font-weight:700;cursor:pointer;}
    .btn-secondary{background:#eef1f8;color:var(--text-mid);}
    .btn-primary{background:var(--purple);color:#fff;}
    .card-actions{display:flex;gap:8px;margin-top:12px;flex-wrap:wrap;}
    .card-link,.card-button{border:none;border-radius:8px;padding:8px 12px;font-size:.78rem;font-weight:700;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;}
    .card-link{background:var(--purple-light);color:var(--purple);}
    .card-button{background:#fff0f4;color:#d94a6b;}
  </style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-logo"><div class="logo-icon"><span>$</span></div><h2>Finzo</h2></div>
  <div class="sidebar-section-label">Main</div>
  <a class="nav-item" href="<?= htmlspecialchars(userPageUrl('dashboard'), ENT_QUOTES, 'UTF-8') ?>"><span class="nav-icon">🏠</span> Dashboard</a>
  <a class="nav-item" href="<?= htmlspecialchars(userPageUrl('transaction'), ENT_QUOTES, 'UTF-8') ?>"><span class="nav-icon">💳</span> Transactions</a>
  <a class="nav-item" href="<?= htmlspecialchars(userPageUrl('budget'), ENT_QUOTES, 'UTF-8') ?>"><span class="nav-icon">🎯</span> Budgets</a>
  <a class="nav-item" href="<?= htmlspecialchars(userPageUrl('category'), ENT_QUOTES, 'UTF-8') ?>"><span class="nav-icon">🗂️</span> Categories</a>
  <div class="sidebar-section-label" style="margin-top:18px;">Collaboration</div>
  <a class="nav-item active" href="<?= htmlspecialchars(userPageUrl('group'), ENT_QUOTES, 'UTF-8') ?>"><span class="nav-icon">👥</span> My Groups</a>
  <a class="nav-item" href="<?= htmlspecialchars(userPageUrl('alert'), ENT_QUOTES, 'UTF-8') ?>"><span class="nav-icon">🔔</span> Alerts</a>
  <div class="sidebar-spacer"></div>
  <div class="sidebar-user">
    <div class="user-ava"><?= htmlspecialchars($displayInitials, ENT_QUOTES, 'UTF-8') ?></div>
    <div class="sidebar-user-info">
      <div class="user-name"><a href="<?= htmlspecialchars(userPageUrl('profile'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?></a></div>
      <div class="user-plan"><?= htmlspecialchars(($currentUser['role'] ?? 'user') . ' plan', ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <a class="btn-logout" href="<?= htmlspecialchars(userPageUrl('logout'), ENT_QUOTES, 'UTF-8') ?>">Logout</a>
  </div>
</aside>

<main class="main">
  <div class="topbar">
    <div><h1>My groups</h1><p style="font-size:.85rem;color:var(--text-muted);margin-top:3px;">Manage your shared budgets and collaborations</p></div>
    <div class="topbar-right">
      <button class="btn-join" type="button" onclick="openModal('joinModal')">+ Join a Group</button>
      <button class="btn-create-grp" type="button" onclick="openModal('groupModal')">+ Create Group</button>
    </div>
  </div>

  <?php if ($joinError || $joinSuccess || $actionError): ?>
    <?php
      $message = $joinError ?: ($actionError ?: $joinSuccess);
      $isSuccess = (bool) $joinSuccess;
      $background = $isSuccess ? '#e6fbf2' : '#fff3f4';
      $border = $isSuccess ? '#c8f3d9' : '#f7d2d8';
      $color = $isSuccess ? '#1d7a34' : '#b4203f';
    ?>
    <div style="margin-bottom:20px;padding:14px 18px;border-radius:14px;background:<?= $background ?>;border:1px solid <?= $border ?>;color:<?= $color ?>;">
      <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php endif; ?>

  <div class="invite-row">
    <span class="invite-label">Invite · Code</span>
    <span class="invite-code"><?= htmlspecialchars($inviteCode, ENT_QUOTES, 'UTF-8') ?></span>
  </div>

  <!-- Stats -->
  <div class="stat-row">
    <div class="stat-card purple-card">
      <div class="stat-num c-purple"><?= (int) $groupStats['count'] ?></div>
      <div class="stat-label">Active groups</div>
    </div>
    <div class="stat-card teal-card">
      <div class="stat-num c-teal"><?= number_format((float) $groupStats['budget'], 0, '.', ',') ?> TND</div>
      <div class="stat-label">Total shared budget</div>
    </div>
    <div class="stat-card yellow-card">
      <div class="stat-num c-yellow"><?= (int) $groupStats['members'] ?></div>
      <div class="stat-label">Total members</div>
    </div>
  </div>

  <?php if (!empty($pendingRequests)): ?>
    <div style="margin-bottom:22px;background:#fff;border:1px solid #e7e9f0;border-radius:18px;box-shadow:var(--shadow);padding:18px;">
      <div style="font-weight:700;font-size:1rem;margin-bottom:12px;">Pending join requests</div>
      <?php foreach ($pendingRequests as $request): ?>
        <div style="display:grid;grid-template-columns:1fr auto;gap:10px;padding:12px 0;border-top:1px solid #f2f3f8;">
          <div>
            <div style="font-weight:700;"><?= htmlspecialchars($request['userName'] . ' ' . $request['userLastName'], ENT_QUOTES, 'UTF-8') ?></div>
            <div style="font-size:.85rem;color:var(--text-muted);">Requested to join <?= htmlspecialchars($request['groupName'], ENT_QUOTES, 'UTF-8') ?></div>
          </div>
          <div style="display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end;">
            <form method="post" action="<?= htmlspecialchars(userPageUrl('group'), ENT_QUOTES, 'UTF-8') ?>">
              <input type="hidden" name="crud_action" value="approveGroupMember"/>
              <input type="hidden" name="groupId" value="<?= (int) $request['groupId'] ?>"/>
              <input type="hidden" name="userId" value="<?= (int) $request['userId'] ?>"/>
              <button class="card-link" type="submit">Approve</button>
            </form>
            <form method="post" action="<?= htmlspecialchars(userPageUrl('group'), ENT_QUOTES, 'UTF-8') ?>">
              <input type="hidden" name="crud_action" value="rejectGroupMember"/>
              <input type="hidden" name="groupId" value="<?= (int) $request['groupId'] ?>"/>
              <input type="hidden" name="userId" value="<?= (int) $request['userId'] ?>"/>
              <button class="card-button" type="submit">Reject</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="groups-section-label">Your groups</div>

  <div class="groups-grid">
    <?php if (empty($groups)): ?>
      <div class="group-card" style="grid-column:1 / -1;">
        <div class="group-body">
          <div class="group-name">No groups yet</div>
          <div class="group-desc">Join or create a group to start sharing budgets and members.</div>
        </div>
      </div>
    <?php else: ?>
      <?php foreach ($groups as $index => $group): ?>
        <?php
          $colorClasses = ['purple', 'teal', 'orange'];
          $bannerClass = $colorClasses[$index % count($colorClasses)];
          $budget = (float) ($group['budget'] ?? 0);
          $spent = (float) ($group['spent'] ?? 0);
          $role = ucfirst((string) ($group['role'] ?? 'user'));
        ?>
        <div class="group-card">
          <div class="group-banner <?= $bannerClass ?>">
            <span class="role-badge"><?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?></span>
            <button class="group-info-btn" type="button" data-invite="<?= htmlspecialchars($group['invitCode'] ?? '', ENT_QUOTES, 'UTF-8') ?>">ℹ</button>
          </div>
          <div class="group-body">
            <div class="group-name"><?= htmlspecialchars($group['name'] ?? 'Group', ENT_QUOTES, 'UTF-8') ?></div>
            <div class="group-desc"><?= htmlspecialchars($group['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
            <div class="member-row">
              <div style="display:flex;align-items:center;">
                <div class="member-avas">
                  <div class="mava" style="background:#7c6af5">G</div>
                </div>
                <span class="member-count"><?= (int) ($group['memberCount'] ?? 0) ?> members</span>
              </div>
            </div>
            <div class="group-stats">
              <div><div class="gstat-label">Budget</div><div class="gstat-val dark"><?= number_format($budget, 0, '.', ',') ?> TND</div></div>
              <div><div class="gstat-label">Spent</div><div class="gstat-val red"><?= number_format($spent, 0, '.', ',') ?> TND</div></div>
            </div>
            <?php if (($group['role'] ?? '') === 'owner'): ?>
              <div class="card-actions">
                <a class="card-link" href="<?= htmlspecialchars(userPageUrl('group'), ENT_QUOTES, 'UTF-8') ?>?edit=<?= (int) ($group['idGroup'] ?? 0) ?>">Edit</a>
                <form method="post" action="<?= htmlspecialchars(userPageUrl('group'), ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Delete this group?');">
                  <input type="hidden" name="crud_action" value="deleteGroup"/>
                  <input type="hidden" name="idGroup" value="<?= (int) ($group['idGroup'] ?? 0) ?>"/>
                  <button class="card-button" type="submit">Delete</button>
                </form>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <div class="create-group-card" role="button" tabindex="0" onclick="openModal('groupModal')">
      <div class="cg-plus">＋</div>
      <h4>Create new group</h4>
      <p>Invite people and manage a shared budget together</p>
    </div>
  </div>

  <div class="modal-overlay" id="joinModal">
    <div class="modal">
      <div class="modal-header">
        <h3>Join a group</h3>
        <button class="modal-close" type="button" onclick="closeModal('joinModal')">×</button>
      </div>
      <form method="post" action="<?= htmlspecialchars(userPageUrl('group'), ENT_QUOTES, 'UTF-8') ?>">
        <div class="modal-body">
          <input type="hidden" name="crud_action" value="joinGroup"/>
          <div class="field">
            <label for="inviteCode">Group invite code</label>
            <input id="inviteCode" name="inviteCode" type="text" placeholder="Enter invite code" required />
          </div>
        </div>
        <div class="modal-actions">
          <button class="btn-secondary" type="button" onclick="closeModal('joinModal')">Cancel</button>
          <button class="btn-primary" type="submit">Request to join</button>
        </div>
      </form>
    </div>
  </div>

  <div class="modal-overlay<?= $editingGroup ? ' open' : '' ?>" id="groupModal">
    <div class="modal">
      <div class="modal-header">
        <h3><?= $editingGroup ? 'Edit group' : 'Create group' ?></h3>
        <button class="modal-close" type="button" onclick="closeModal('groupModal')">×</button>
      </div>
      <form method="post" action="<?= htmlspecialchars(userPageUrl('group'), ENT_QUOTES, 'UTF-8') ?>">
        <div class="modal-body">
          <input type="hidden" name="crud_action" value="<?= $editingGroup ? 'updateGroup' : 'createGroup' ?>"/>
          <input type="hidden" name="idGroup" value="<?= htmlspecialchars((string) ($editingGroup['idGroup'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"/>
          <div class="field">
            <label for="groupName">Group name</label>
            <input id="groupName" name="name" type="text" value="<?= htmlspecialchars($editingGroup['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required/>
          </div>
          <div class="field">
            <label for="groupDescription">Description</label>
            <textarea id="groupDescription" name="description" rows="4" required><?= htmlspecialchars($editingGroup['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>
          <div class="field">
            <label for="groupBudget">Budget</label>
            <input id="groupBudget" name="budget" type="number" step="0.01" min="0" value="<?= htmlspecialchars((string) ($editingGroup['budget'] ?? 0), ENT_QUOTES, 'UTF-8') ?>" required/>
          </div>
          <div class="field">
            <label for="groupSpent">Spent</label>
            <input id="groupSpent" name="spent" type="number" step="0.01" min="0" value="<?= htmlspecialchars((string) ($editingGroup['spent'] ?? 0), ENT_QUOTES, 'UTF-8') ?>"/>
          </div>
          <div class="field">
            <label for="groupTheme">Theme</label>
            <select id="groupTheme" name="theme">
              <?php foreach (['purple' => 'Purple', 'teal' => 'Teal', 'orange' => 'Orange'] as $themeValue => $themeLabel): ?>
                <option value="<?= $themeValue ?>"<?= (($editingGroup['theme'] ?? 'purple') === $themeValue) ? ' selected' : '' ?>><?= $themeLabel ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label for="groupBudgetId">Linked budget</label>
            <select id="groupBudgetId" name="budgetId" required>
              <option value="">Select a budget</option>
              <?php foreach ($userBudgets as $budgetRow): ?>
                <option value="<?= (int) ($budgetRow['idBudget'] ?? 0) ?>"<?= ((int) ($editingGroup['budgetId'] ?? 0) === (int) ($budgetRow['idBudget'] ?? 0)) ? ' selected' : '' ?>>
                  <?= htmlspecialchars(($budgetRow['categoryName'] ?? 'Budget') . ' - ' . ($budgetRow['limit'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-actions">
          <button class="btn-secondary" type="button" onclick="closeModal('groupModal')">Cancel</button>
          <button class="btn-primary" type="submit"><?= $editingGroup ? 'Update group' : 'Create group' ?></button>
        </div>
      </form>
    </div>
  </div>
</main>

<script>
  function openModal(id) {
    var modal = document.getElementById(id);
    if (modal) {
      modal.classList.add('open');
    }
  }

  function closeModal(id) {
    var modal = document.getElementById(id);
    if (modal) {
      modal.classList.remove('open');
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    <?php if ($editingGroup): ?>
    openModal('groupModal');
    <?php endif; ?>

    document.querySelectorAll('.group-info-btn').forEach(function(button) {
      button.addEventListener('click', function() {
        var code = button.getAttribute('data-invite') || 'INVITE-CODE';
        var display = document.querySelector('.invite-code');
        if (display) {
          display.textContent = code;
        }
      });
    });
  });
</script>
</body>
</html>