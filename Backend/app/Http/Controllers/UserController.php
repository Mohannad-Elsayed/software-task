<?php

require_once __DIR__ . "/../../Services/UserService.php";
require_once __DIR__ . "/../../Services/SustainabilityService.php";

class UserController {

    private $userService;

    public function __construct() {
        $this->userService = new UserService();
    }

    public function getUserProfile() {
        header("Content-Type: application/json");
        
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

    public function calculateImpact() {
        header("Content-Type: application/json");

        $data = json_decode(
            file_get_contents("php://input"),
            true
        );

        $service = new SustainabilityService();

        $impact = $service->calculateCarbonFootprint(
            $data["material"],
            $data["action_type"]
        );

        if (!$impact) {
            echo json_encode([
                "success" => false,
                "message" => "Unknown material"
            ]);
            return;
        }

        $ecoPoints = $service->calculateEcoPoints(
            $impact["co2_saved"],
            $impact["water_saved"],
            $impact["waste_reduced"]
        );

        echo json_encode([
            "success" => true,
            "impact" => $impact,
            "eco_points" => $ecoPoints
        ]);
    }

    public function calculateTrustScore() {
        header("Content-Type: application/json");

        $data = json_decode(
            file_get_contents("php://input"),
            true
        );

        $service = new SustainabilityService();

        $score = $service->calculateTrustScore(
            $data["average_rating"],
            $data["completed_transactions"],
            $data["disputes"]
        );

        echo json_encode([
            "success" => true,
            "trust_score" => $score
        ]);
    }
}