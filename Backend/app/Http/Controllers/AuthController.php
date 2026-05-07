<?php

require_once __DIR__ . "/../../Services/UserService.php";

class AuthController {

    private $userService;

    public function __construct() {
        $this->userService = new UserService();
    }

    public function register() {
        $data = json_decode(file_get_contents("php://input"), true);

        $username = trim($data["username"] ?? "");
        $email = trim($data["email"] ?? "");
        $password = trim($data["password"] ?? "");

        if ($username === "" || $email === "" || $password === "") {
            echo json_encode([
                "success" => false,
                "message" => "Username, email, and password are required"
            ]);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "success" => false,
                "message" => "Invalid email format"
            ]);
            return;
        }

        if (strlen($password) < 6) {
            echo json_encode([
                "success" => false,
                "message" => "Password must be at least 6 characters"
            ]);
            return;
        }

        if ($this->userService->emailExists($email)) {
            echo json_encode([
                "success" => false,
                "message" => "Email already exists"
            ]);
            return;
        }

        $userId = $this->userService->createUser($username, $email, $password);

        if ($userId) {
            echo json_encode([
                "success" => true,
                "message" => "User registered successfully",
                "user_id" => $userId
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Registration failed"
            ]);
        }
    }

    public function login() {
        $data = json_decode(file_get_contents("php://input"), true);

        $email = trim($data["email"] ?? "");
        $password = trim($data["password"] ?? "");

        if ($email === "" || $password === "") {
            echo json_encode([
                "success" => false,
                "message" => "Email and password are required"
            ]);
            return;
        }

        $user = $this->userService->findByEmail($email);

        if (!$user || !password_verify($password, $user["password"])) {
            echo json_encode([
                "success" => false,
                "message" => "Invalid email or password"
            ]);
            return;
        }

        echo json_encode([
            "success" => true,
            "message" => "Login successful",
            "user" => [
                "user_id" => $user["user_id"],
                "username" => $user["username"],
                "email" => $user["email"],
                "trust_score" => $user["trust_score"],
                "eco_points" => $user["eco_points"] ?? 0,
                "account_type" => "normal_user"
            ]
        ]);
    }

    public function logout() {
        echo json_encode([
            "success" => true,
            "message" => "Logout successful"
        ]);
    }
}