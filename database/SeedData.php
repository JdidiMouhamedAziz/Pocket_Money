<?php
class SeedData {
    public static function getUsers(): array {
        return [
            [
                'name' => 'Admin',
                'lastName' => 'User',
                'email' => 'admin@example.com',
                'password' => '123456',
                'role' => 'admin',
                'status' => 'active',
            ],
            [
                'name' => 'Jane',
                'lastName' => 'Doe',
                'email' => 'jane.doe@example.com',
                'password' => '123456',
                'role' => 'user',
                'status' => 'active',
            ],
            [
                'name' => 'John',
                'lastName' => 'Smith',
                'email' => 'john.smith@example.com',
                'password' => '123456',
                'role' => 'user',
                'status' => 'active',
            ],
        ];
    }
}
