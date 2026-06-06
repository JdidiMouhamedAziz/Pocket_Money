<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header('Location: /pocket_money/views/login.php');
    exit();
}

$role = $_SESSION['user']['role'] ?? 'user';
if ($role === 'admin') {
    header('Location: /pocket_money/views/admin/dashboard.php');
    exit();
}

header('Location: /pocket_money/views/user/dashboard.php');
exit();
