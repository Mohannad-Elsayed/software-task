<?php

namespace app\Http\Controllers;

use app\Services\AdminService;

//require_once __DIR__ . '/../../app/Services/AdminService.php';
require_once __DIR__ . '/../../Services/AdminService.php';
class AdminController
{
    
    private $adminService;

    public function __construct()
    {
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
}