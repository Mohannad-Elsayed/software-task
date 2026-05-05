<?php

namespace App\Services;

require_once __DIR__ . '/../../database/connection.php';

class AdminService
{
    private $conn;

    public function __construct()
    {
        $this->conn = db();
    }

    // =========================
    // GET ALL USERS
    // =========================
    public function getUsers()
    {
        $sql = "SELECT * FROM user";
        $result = $this->conn->query($sql);

        $users = [];

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }

        return $users;
    }

    // =========================
    // GET USER BY ID
    // =========================
    public function getUserById($userId)
    {
        $sql = "SELECT * FROM user WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc() ?: null;

        $stmt->close();

        return $user;
    }

    // =========================
    // DELETE USER
    // =========================
    public function deleteUser($userId)
    {
        $stmt = $this->conn->prepare("DELETE FROM user WHERE user_id = ?");
        $stmt->bind_param("i", $userId);

        if ($stmt->execute()) {
            $stmt->close();

            return [
                "success" => true,
                "deleted_user_id" => $userId
            ];
        }

        $error = $stmt->error;
        $stmt->close();

        return ["error" => $error];
    }

    // =========================
    // GET ALL REPORTS (ADMIN VIEW)
    // =========================
    public function getReports()
    {
        $sql = "SELECT * FROM report";
        $result = $this->conn->query($sql);

        $reports = [];

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $reports[] = $row;
            }
        }

        return $reports;
    }

    // =========================
    // UPDATE REPORT STATUS
    // =========================
    public function updateReportStatus($reportId, $data)
    {
        $status = $data['status'] ?? null;

        if (!$status) {
            return ["error" => "status is required"];
        }

        $stmt = $this->conn->prepare(
            "UPDATE report SET status = ? WHERE report_id = ?"
        );

        $stmt->bind_param("si", $status, $reportId);

        if ($stmt->execute()) {
            $stmt->close();

            return [
                "success" => true,
                "report_id" => $reportId
            ];
        }

        $error = $stmt->error;
        $stmt->close();

        return ["error" => $error];
    }
}