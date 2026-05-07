<?php

namespace app\Services;

require_once __DIR__ . '/../../database/connection.php';
require_once __DIR__ . '/../Models/Report.php';

class ReportService
{
    private $conn;

    public function __construct()
    {
        $this->conn = db();
    }

    // =========================
    // GET ALL REPORTS
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
    // GET REPORT BY ID
    // =========================
    public function getReportById($reportId)
    {
        $sql = "SELECT * FROM Report WHERE report_id = ?";
        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("i", $reportId);
        $stmt->execute();

        $result = $stmt->get_result();
        $report = $result->fetch_assoc() ?: null;

        $stmt->close();

        return $report;
    }

    // =========================
    // CREATE REPORT
    // =========================
    public function createReport($data)
    {
        $status = $data['status'] ?? 'pending';
        $initiator_id = $data['initiator_id'] ?? null;
        $comment_id = $data['comment_id'] ?? null;
        $reason = $data['reason'] ?? null;
        $listing_id = $data['listing_id'] ?? null;

        if (!$initiator_id) {
            return ["error" => "initiator_id is required"];
        }
    
        $stmt = $this->conn->prepare(
            "INSERT INTO Report (status, initiator_id, comment_id, reason, listing_id)
             VALUES (?, ?, ?, ?, ?)"
        );

        // FIXED: correct bind types (important)
        $stmt->bind_param(
            "siisi",
            $status,
            $initiator_id,
            $comment_id,
            $reason,
            $listing_id
        );

        if ($stmt->execute()) {
            $id = $stmt->insert_id;
            $stmt->close();

            return [
                "success" => true,
                "report_id" => $id
            ];
        }

        $error = $stmt->error;
        $stmt->close();

        return ["error" => $error];
    }

    // =========================
    // UPDATE REPORT
    // =========================
    public function updateReport($reportId, $data)
    {
        $status = $data['status'] ?? null;
        $reason = $data['reason'] ?? null;

        if (!$status) {
            return ["error" => "status is required"];
        }

        $stmt = $this->conn->prepare(
            "UPDATE Report 
             SET status = ?, reason = ? 
             WHERE report_id = ?"
        );

        $stmt->bind_param("ssi", $status, $reason, $reportId);

        if ($stmt->execute()) {
            $stmt->close();
            return ["success" => true, "report_id" => $reportId];
        }

        $error = $stmt->error;
        $stmt->close();

        return ["error" => $error];
    }

    // =========================
    // DELETE REPORT
    // =========================
    public function deleteReport($reportId)
    {
        $stmt = $this->conn->prepare("DELETE FROM Report WHERE report_id = ?");
        $stmt->bind_param("i", $reportId);

        if ($stmt->execute()) {
            $stmt->close();
            return ["success" => true, "deleted_id" => $reportId];
        }

        $error = $stmt->error;
        $stmt->close();

        return ["error" => $error];
    }
}