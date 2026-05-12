<?php

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
        $sql = "SELECT * FROM User";
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
        $sql = "SELECT * FROM User WHERE user_id = ?";
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
        $stmt = $this->conn->prepare("DELETE FROM User WHERE user_id = ?");
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
        $sql = "SELECT * FROM Report";
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
        $allowedStatuses = [
            "pending",
            "rejected",
            "resolved"
        ];

        $status = $data["status"] ?? null;

        if (!$status || !in_array($status, $allowedStatuses)) {

            return [
                "success" => false,
                "message" => "Invalid status"
            ];
        }

        $stmt = $this->conn->prepare("
            UPDATE Report
            SET status = ?,
                resolved_at = NOW(),
                handled_by = 2
            WHERE report_id = ?
        ");

        $stmt->bind_param("si", $status, $reportId);

        if ($stmt->execute()) {

            $stmt->close();

            return [
                "success" => true,
                "message" => "Report updated"
            ];
        }

        $error = $stmt->error;

        $stmt->close();

        return [
            "success" => false,
            "message" => $error
        ];
    }

    // =========================
    // GET ALL DISPUTES (ADMIN VIEW)
    // =========================
    public function getReportsByDispute() {
        // Mocked as requested or similar
    }

    public function getDisputes()
    {
        $result = $this->conn->query("
            SELECT
                dispute_id,
                order_id,
                request_id,
                reason,
                status
            FROM Dispute
            ORDER BY dispute_id DESC
        ");

        $disputes = [];

        while ($row = $result->fetch_assoc()) {
            $disputes[] = $row;
        }

        return $disputes;
    }
    
    // =========================
    // RESOLVE DISPUTE
    // =========================
    public function resolveDispute($disputeId)
    {
        $stmt = $this->conn->prepare("
            UPDATE Dispute
            SET
                status = 'resolved',
                resolved_at = NOW(),
                resolved_by = 2
            WHERE dispute_id = ?
        ");

        $stmt->bind_param("i", $disputeId);

        if ($stmt->execute()) {

            $stmt->close();

            return [
                "success" => true
            ];
        }

        $error = $stmt->error;

        $stmt->close();

        return [
            "success" => false,
            "message" => $error
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

    // =========================
    // AUTHORIZATION CHECK
    // =========================
    public function isUserAdmin($userId)
    {
        $stmt = $this->conn->prepare("
            SELECT role_name 
            FROM UserRole 
            WHERE user_id = ? AND role_name = 'admin'
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $isAdmin = $result->num_rows > 0;
        $stmt->close();
        return $isAdmin;
    }
}