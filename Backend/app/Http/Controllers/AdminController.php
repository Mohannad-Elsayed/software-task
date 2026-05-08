<?php

namespace app\Http\Controllers;

use app\Services\AdminService;
require_once __DIR__ . '/../../Services/AdminService.php';
class AdminController
{
    private $conn;
    
    private $adminService;

    public function __construct()
    {
        $this->conn = db();
        $this->adminService = new AdminService();
    }

    // =========================
    // GET ALL USERS (ADMIN)
    // /api/admin/users
    // =========================
    public function getUsers()
    {
        echo json_encode($this->adminService->getUsers());
    }

    // =========================
    // GET USER BY ID
    // /api/admin/user?id=1
    // =========================
    public function getUser()
    {
        $user_id = $_GET['user_id'] ?? null;

        if (!$user_id) {
            http_response_code(400);
            echo json_encode(["error" => "user_id is required"]);
            return;
        }

        $user = $this->adminService->getUserById($user_id);

        if ($user) {
            echo json_encode($user);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "User not found"]);
        }
    }

    // =========================
    // DELETE USER
    // /api/admin/user?id=1
    // =========================
    public function deleteUser()
    {
        $user_id = $_GET['user_id'] ?? null;

        if (!$user_id) {
            http_response_code(400);
            echo json_encode(["error" => "user_id is required"]);
            return;
        }

        $result = $this->adminService->deleteUser($user_id);

        echo json_encode($result);
    }

    // =========================
    // GET ALL REPORTS (ADMIN VIEW)
    // /api/admin/reports
    // =========================
    public function getReports()
    {
        echo json_encode($this->adminService->getReports());
    }

    // =========================
    // UPDATE REPORT STATUS (MODERATION)
    // /api/admin/report?id=1
    // =========================
    public function updateReport()
    {
        $report_id = $_GET['report_id'] ?? null;
        $data = $_POST ?: json_decode(file_get_contents('php://input'), true);

        if (!$report_id) {
            http_response_code(400);
            echo json_encode(["error" => "report_id is required"]);
            return;
        }

        $result = $this->adminService->updateReportStatus($report_id, $data);

        if (isset($result['error'])) {
            http_response_code(400);
        }

        echo json_encode($result);
    }

    public function banUser()
{
    header('Content-Type: application/json');

    $data = json_decode(file_get_contents("php://input"), true);
    $userId = $data['user_id'] ?? null;

    if (!$userId) {
        http_response_code(400);
        echo json_encode(["error" => "user_id is required"]);
        return;
    }

    // check user exists
    $stmt = $this->conn->prepare("
        SELECT user_id FROM User WHERE user_id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        http_response_code(404);
        echo json_encode(["error" => "User not found"]);
        return;
    }

    // REAL BAN (persistent)
    $stmt = $this->conn->prepare("
        UPDATE User 
        SET is_banned = 1 
        WHERE user_id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    echo json_encode([
        "success" => true,
        "message" => "User banned successfully",
        "user_id" => $userId
    ]);
}
    public function resolveDispute()
{
    header('Content-Type: application/json');

    $data = json_decode(file_get_contents("php://input"), true);
    $disputeId = $data['dispute_id'] ?? null;

    if (!$disputeId) {
        http_response_code(400);
        echo json_encode(["error" => "dispute_id is required"]);
        return;
    }

    echo json_encode(
        $this->adminService->resolveDispute($disputeId)
    );
}

public function sellerAnalytics()
{
    echo json_encode(
        $this->adminService->generateSellerAnalytics()
    );
}

public function sustainabilityReport()
{
    echo json_encode(
        $this->adminService->generateSustainabilityReport()
    );
}
}