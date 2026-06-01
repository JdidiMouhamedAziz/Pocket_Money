<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user'])) {
    header('Location: /pocket_money/views/dashboard.php');
    exit();
}

header('Location: /pocket_money/views/components/landingPage.php');
exit();
?>