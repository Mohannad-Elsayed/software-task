<?php

require_once __DIR__ . "/../../database/connection.php";

class UserService {

    public function emailExists($email) {
        $conn = db();

        $stmt = $conn->prepare("SELECT user_id FROM User WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }

    public function createUser($username, $email, $password) {
        $conn = db();

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("
            INSERT INTO User (username, email, password)
            VALUES (?, ?, ?)
        ");

        $stmt->bind_param("sss", $username, $email, $hashedPassword);

        if (!$stmt->execute()) {
            return false;
        }

        $userId = $stmt->insert_id;

        $impact = $conn->prepare("
            INSERT INTO EcoImpact (user_id)
            VALUES (?)
        ");

        $impact->bind_param("i", $userId);
        $impact->execute();

        return $userId;
    }

    public function findByEmail($email) {
        $conn = db();

        $stmt = $conn->prepare("
            SELECT 
                User.user_id,
                User.username,
                User.email,
                User.password,
                User.trust_score,
                EcoImpact.eco_points,
                UserRole.role_name
            FROM User
            LEFT JOIN EcoImpact 
                ON User.user_id = EcoImpact.user_id
            LEFT JOIN UserRole 
                ON User.user_id = UserRole.user_id
            WHERE User.email = ?
        ");

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return null;
        }

        return $result->fetch_assoc();
    }

    public function findById($userId) {
        $conn = db();

        $stmt = $conn->prepare("
            SELECT 
                User.user_id,
                User.username,
                User.email,
                User.trust_score,
                User.created_at,
                EcoImpact.co2_saved,
                EcoImpact.waste_reduced,
                EcoImpact.water_saved,
                EcoImpact.eco_points
            FROM User
            LEFT JOIN EcoImpact ON User.user_id = EcoImpact.user_id
            WHERE User.user_id = ?
        ");

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return null;
        }

        return $result->fetch_assoc();
    }
}