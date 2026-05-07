<?php

require_once __DIR__ . "/../../Services/UserService.php";

class UserController {

    private $userService;

    public function __construct() {
        $this->userService = new UserService();
    }

    public function getUserProfile() {
        $userId = $_GET["user_id"] ?? null;

        if (!$userId) {
            echo json_encode([
                "success" => false,
                "message" => "User ID is required"
            ]);
            return;
        }

        $user = $this->userService->findById($userId);

        if (!$user) {
            echo json_encode([
                "success" => false,
                "message" => "User not found"
            ]);
            return;
        }

        echo json_encode([
            "success" => true,
            "user" => $user
        ]);
    }
}