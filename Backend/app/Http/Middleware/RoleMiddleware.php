<?php

namespace app\Http\Middleware;

require_once __DIR__ . '/../../../database/connection.php';
class RoleMiddleware
{
public static function checkNotBanned($userId)
    {
        $conn = db();

        $stmt = $conn->prepare("
            SELECT is_banned 
            FROM User 
            WHERE user_id = ?
        ");

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$user) {
            return [
                "allowed" => false,
                "message" => "User not found"
            ];
        }

        if ($user['is_banned'] == 1) {
            return [
                "allowed" => false,
                "message" => "User is banned"
            ];
        }

        return [
            "allowed" => true
        ];
    }
}