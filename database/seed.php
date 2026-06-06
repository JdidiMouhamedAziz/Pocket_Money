<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/SeedData.php';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $columnCheck = $pdo->query("SHOW COLUMNS FROM users LIKE 'passowrd'")->fetch();
    if ($columnCheck) {
        $pdo->exec("ALTER TABLE users CHANGE COLUMN `passowrd` `password` VARCHAR(100) NOT NULL");
    }

    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    $tables = [
        'alert',
        'budgettransaction',
        'groupmember',
        'budgetcategory',
        'transaction',
        'budget',
        'category',
        '`group`',
        'users'
    ];

    foreach ($tables as $table) {
        $pdo->exec("TRUNCATE TABLE $table");
    }
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

    $userModel = new User($pdo);
    $createdCount = 0;
    foreach (SeedData::getUsers() as $userData) {
        $created = $userModel->create(
            $userData['name'],
            $userData['lastName'],
            $userData['email'],
            $userData['password'],
            $userData['role'],
            $userData['status']
        );
        if ($created) {
            $createdCount++;
        }
    }

    echo "Seed completed. {$createdCount} users inserted. Password for all seeded users is '123456'.\n";
} catch (PDOException $e) {
    echo 'Seeder failed: ' . $e->getMessage() . "\n";
}
