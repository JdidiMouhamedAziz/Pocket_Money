<?php

    session_start();
    require_once "/pocket_money/config/database.php";
    require_once "/pocket_money/models/Alert.php";

    if(!isset($_SESSION["user"])){
       echo json_encode(['success' => false]);
        exit();
    }

    $alertModel=new Alert($pdo);
    $action = $_GET['action'];
    $user_id= $_SESSION['user']['id'];

    if($action === 'markAllRead'){
        $alertModel->readAllAlert($user_id);
        echo json_encode(['success' => true]);
        exit();
    }

?>