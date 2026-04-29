<?php

namespace app\Services;

require_once __DIR__ . '/../../database/connection.php';

class UserService
{
    public function register($name, $email, $password, $roleName)
    {
        $conn = db();

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("
            INSERT INTO users (name, email, password, trust_score)
            VALUES (?, ?, ?, 0)
        ");
        $stmt->bind_param("sss", $name, $email, $hashedPassword);

        if (!$stmt->execute()) {
            return false;
        }

        $userId = $conn->insert_id;

        $roleStmt = $conn->prepare("SELECT role_id FROM roles WHERE role_name = ?");
        $roleStmt->bind_param("s", $roleName);
        $roleStmt->execute();

        $role = $roleStmt->get_result()->fetch_assoc();

        if (!$role) {
            return false;
        }

        $roleId = $role['role_id'];

        $assignStmt = $conn->prepare("
            INSERT INTO user_roles (user_id, role_id)
            VALUES (?, ?)
        ");
        $assignStmt->bind_param("ii", $userId, $roleId);
        $assignStmt->execute();

        return $this->findById($userId);
    }

    public function login($email, $password)
    {
        $conn = db();

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $user = $stmt->get_result()->fetch_assoc();

        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        return $user;
    }

    public function findById($userId)
    {
        $conn = db();

        $stmt = $conn->prepare("
            SELECT 
                u.user_id,
                u.name,
                u.email,
                u.trust_score,
                GROUP_CONCAT(r.role_name) AS roles
            FROM users u
            LEFT JOIN user_roles ur ON u.user_id = ur.user_id
            LEFT JOIN roles r ON ur.role_id = r.role_id
            WHERE u.user_id = ?
            GROUP BY u.user_id
        ");

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }
}