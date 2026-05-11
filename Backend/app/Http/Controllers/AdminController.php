<?php

require_once __DIR__ . "/../../Services/AdminService.php";

class AdminController
{
    private $adminService;

    public function __construct()
    {
        $this->adminService = new AdminService();
    }

    public function getUsers()
    {
        header("Content-Type: application/json");

        echo json_encode([
            "success" => true,
            "users" => $this->adminService->getUsers()
        ]);
    }

    public function getUser()
    {
        header("Content-Type: application/json");

        $userId = $_GET["user_id"] ?? null;

        if (!$userId) {
            echo json_encode([
                "success" => false,
                "message" => "User ID is required"
            ]);
            return;
        }

        $user = $this->adminService->getUserById($userId);

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

    public function deleteUser()
    {
        header("Content-Type: application/json");

        $userId = $_GET["user_id"] ?? null;

        if (!$userId) {
            echo json_encode([
                "success" => false,
                "message" => "User ID is required"
            ]);
            return;
        }

        echo json_encode(
            $this->adminService->deleteUser($userId)
        );
    }

    public function getReports()
    {
        header("Content-Type: application/json");

        echo json_encode([
            "success" => true,
            "reports" => $this->adminService->getReports()
        ]);
    }

    public function updateReport()
    {
        header("Content-Type: application/json");

        $reportId = $_GET["report_id"] ?? null;
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$reportId) {
            echo json_encode([
                "success" => false,
                "message" => "Report ID is required"
            ]);
            return;
        }

        echo json_encode(
            $this->adminService->updateReportStatus($reportId, $data)
        );
    }

    public function getDisputes()
    {
        header("Content-Type: application/json");

        echo json_encode([
            "success" => true,
            "disputes" => $this->adminService->getDisputes()
        ]);
    }

    public function resolveDispute()
    {
        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"), true);
        $disputeId = $data["dispute_id"] ?? null;

        if (!$disputeId) {
            echo json_encode([
                "success" => false,
                "message" => "Dispute ID is required"
            ]);
            return;
        }

        echo json_encode(
            $this->adminService->resolveDispute($disputeId)
        );
    }
}