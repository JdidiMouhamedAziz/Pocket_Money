<?php
    session_start ();

    require_once '../config/database.php';
    require_once '../models/User.php';

    $userModel = new User($pdo);
    if ($_SERVER ["REQUEST_METHOD"] === "POST"){
        $action = trim($_POST["action"] ?? 'login');

        if ($action === 'register') {
            $firstName = trim($_POST["firstName"] ?? '');
            $lastName = trim($_POST["lastName"] ?? '');
            $email = trim($_POST["email"] ?? '');
            $password = $_POST["password"] ?? '';
            $confirmPassword = $_POST["confirmPassword"] ?? '';

            if (!$firstName || !$lastName || !$email || !$password || !$confirmPassword) {
                $_SESSION['auth_error'] = 'Please complete all registration fields.';
                header("Location: ../views/login.php");
                exit();
            }

            if ($password !== $confirmPassword) {
                $_SESSION['auth_error'] = 'Passwords do not match.';
                header("Location: ../views/login.php");
                exit();
            }

            if ($userModel->findUserByEmail($email)) {
                $_SESSION['auth_error'] = 'This email is already registered.';
                header("Location: ../views/login.php");
                exit();
            }

            $created = $userModel->create($firstName, $lastName, $email, $password, 'user', 'active');
            if ($created) {
                $_SESSION['auth_success'] = 'Account created successfully. Please sign in.';
                header("Location: ../views/login.php");
                exit();
            }

            $_SESSION['auth_error'] = 'Unable to create account. Please try again.';
            header("Location: ../views/login.php");
            exit();
        }

        $email = trim($_POST["email"] ?? '');
        $password = $_POST["password"] ?? '';

        //verif email
        $user = $userModel->findUserByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            if (strtolower(trim($user['status'] ?? '')) !== 'active') {
                $_SESSION['auth_error'] = 'Your account has been deactivated. Please contact support if this is an error.';
                header("Location: ../views/login.php");
                exit();
            }

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
        }

        $_SESSION['auth_error'] = "Invalid credentials";
        header("Location: ../views/login.php");
        exit();
    }

?>