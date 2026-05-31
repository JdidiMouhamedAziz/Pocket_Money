<?php
session_start ();

require_once '../config/database.php';
require_once '../models/User.php';

$userModel = new User($pdo);
if ($_SERVER ["REQUEST_METHOD"] === "POST"){
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $user = $userModel->findUserByemail($email);

}

?>