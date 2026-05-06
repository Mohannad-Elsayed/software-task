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
            INSERT INTO User (username, email, password, trust_score)
            VALUES (?, ?, ?, 0)
        ");
        $stmt->bind_param("sss", $name, $email, $hashedPassword);

        if (!$stmt->execute()) {
            return false;
        }

        $userId = $conn->insert_id;

        $assignStmt = $conn->prepare("
            INSERT INTO UserRole (user_id, role_name)
            VALUES (?, ?)
        ");
        $assignStmt->bind_param("is", $userId, $roleName);
        $assignStmt->execute();

        return $this->findById($userId);
    }

    public function login($email, $password)
    {
        $conn = db();

        $stmt = $conn->prepare("SELECT * FROM User WHERE email = ?");
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
                u.username,
                u.email,
                u.trust_score,
                GROUP_CONCAT(ur.role_name) AS roles
            FROM User u
            LEFT JOIN UserRole ur ON u.user_id = ur.user_id
            WHERE u.user_id = ?
            GROUP BY u.user_id
        ");

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }
}