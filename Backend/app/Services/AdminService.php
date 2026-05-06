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
   
    
    // =========================
    // REOVE DISPUTE
    // =========================
    public function resolveDispute($disputeId)
{
    $stmt = $this->conn->prepare("
        UPDATE Dispute
        SET status = 'resolved'
        WHERE dispute_id = ?
    ");

    $stmt->bind_param("i", $disputeId);
    $stmt->execute();
    $stmt->close();

    return [
        "success" => true,
        "message" => "Dispute resolved",
        "dispute_id" => $disputeId
    ];
}
    // =========================
    // SELLER ANALYTICS
    // =========================
public function generateSellerAnalytics()
{
    // Total listings per seller
    $listingsQuery = $this->conn->query("
        SELECT user_id, COUNT(*) AS total_listings
        FROM Listing
        GROUP BY user_id
    ");

    $listings = [];
    while ($row = $listingsQuery->fetch_assoc()) {
        $listings[$row['user_id']] = $row['total_listings'];
    }

    // Total sales per seller (from sold listings)
    $salesQuery = $this->conn->query("
        SELECT l.user_id, COUNT(*) AS total_sales
        FROM Listing l
        WHERE l.status = 'sold'
        GROUP BY l.user_id
    ");

    $sales = [];
    while ($row = $salesQuery->fetch_assoc()) {
        $sales[$row['user_id']] = $row['total_sales'];
    }

    // Merge results
    $result = [];

    foreach ($listings as $userId => $count) {
        $result[] = [
            "user_id" => $userId,
            "total_listings" => $count,
            "total_sales" => $sales[$userId] ?? 0
        ];
    }

    return [
        "success" => true,
        "data" => $result
    ];
}
    // =========================
    // SUSTAINABILITY REPORT
    // =========================
public function generateSustainabilityReport()
{
    $completed = $this->conn->query("
        SELECT COUNT(*) AS total FROM Orders WHERE status = 'completed'
    ")->fetch_assoc()['total'];

    $cancelled = $this->conn->query("
        SELECT COUNT(*) AS total FROM Orders WHERE status = 'cancelled'
    ")->fetch_assoc()['total'];

    $total = $completed + $cancelled;

    $efficiency = $total > 0
        ? ($completed / $total) * 100
        : 0;

    return [
        "success" => true,
        "data" => [
            "completed_orders" => $completed,
            "cancelled_orders" => $cancelled,
            "efficiency_rate" => round($efficiency, 2) . "%"
        ]
    ];
}
}