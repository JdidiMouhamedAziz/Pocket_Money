<?php
    session_start ();

    require_once '../config/database.php';
    require_once '../models/User.php';

    $userModel = new User($pdo);
    if ($_SERVER ["REQUEST_METHOD"] === "POST"){
        $email = trim($_POST["email"]);
        $password = $_POST["password"];

        //verif email
        $user = $userModel->findUserByemail($email);

        //verif password
        if ($user && password_verify($password, $user['password'])) {

            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'lastName' => $user['lastName'],
                'username' => $user['name'] . ' ' . $user['lastName'],
                'email'=> $user['email'],
                'role' => $user['role'],
                'status'=> $user['status'],
                'createdAt'=> $user['createdAt'],
                'updatedAt'=> $user['updatedAt'],
            ];

            header("Location: ../views/dashboard.php");
            exit();

        } else {

            $_SESSION['error'] = "Invalid credentials";

            header("Location: ../login.php");
            exit();
        }
    }

?>