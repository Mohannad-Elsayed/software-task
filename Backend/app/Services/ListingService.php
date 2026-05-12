<?php

require_once __DIR__ . '/../../database/connection.php';

class ListingService
{
    private $conn;

    public function __construct()
    {
        $this->conn = db();
    }

    // =========================
    // GET ALL LISTINGS
    // =========================
    public function listListings()
    {
        $sql = "
            SELECT *
            FROM Listing
            WHERE status = 'active'
            ORDER BY listing_id DESC
        ";

        $result = $this->conn->query($sql);

        $listings = [];

        while ($row = $result->fetch_assoc()) {
            $listings[] = $this->mapListingRow($row);
        }

        return $listings;
    }

    // =========================
    // GET SINGLE LISTING
    // =========================
    public function showListing($id)
    {
        $stmt = $this->conn->prepare("
            SELECT *
            FROM Listing
            WHERE listing_id = ?
        ");

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return null;
        }

        return $this->mapListingRow(
            $result->fetch_assoc()
        );
    }

    // =========================
    // CREATE LISTING
    // =========================
    public function createListing($data)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO Listing
            (
                user_id,
                material_id,
                title,
                description,
                price,
                category,
                condition_status,
                listing_type,
                status
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "iissdssss",
            $data["user_id"],
            $data["material_id"],
            $data["title"],
            $data["description"],
            $data["price"],
            $data["category"],
            $data["condition_status"],
            $data["listing_type"],
            $data["status"]
        );

        return $stmt->execute();
    }

    // =========================
    // UPDATE LISTING
    // =========================
    public function editListing($id, $data)
    {
        $stmt = $this->conn->prepare("
            UPDATE Listing
            SET
                title = ?,
                description = ?,
                price = ?,
                category = ?,
                condition_status = ?,
                listing_type = ?,
                status = ?
            WHERE listing_id = ?
        ");

        $stmt->bind_param(
            "ssdssssi",
            $data["title"],
            $data["description"],
            $data["price"],
            $data["category"],
            $data["condition_status"],
            $data["listing_type"],
            $data["status"],
            $id
        );

        return $stmt->execute();
    }

    // =========================
    // DELETE LISTING
    // =========================
    public function deleteListing($id)
    {
        $stmt = $this->conn->prepare("
            DELETE FROM Listing
            WHERE listing_id = ?
        ");

        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }

    // =========================
    // USER LISTINGS
    // =========================
    public function userListings($userId)
    {
        $stmt = $this->conn->prepare("
            SELECT *
            FROM Listing
            WHERE user_id = ?
            ORDER BY listing_id DESC
        ");

        $stmt->bind_param("i", $userId);

        $stmt->execute();

        $result = $stmt->get_result();

        $listings = [];

        while ($row = $result->fetch_assoc()) {
            $listings[] = $this->mapListingRow($row);
        }

        return $listings;
    }

    // =========================
    // CONDITION ASSESSMENT
    // =========================
    public function assessCondition($id)
    {
        return [
            "listing_id" => $id,
            "condition" => "Good",
            "tags" => [
                "Minor Wear",
                "Clean Fabric"
            ]
        ];
    }

    // =========================
    // CARE INSTRUCTIONS
    // =========================
    public function generateCareInstructions($id)
    {
        return [
            "listing_id" => $id,
            "instructions" => [
                "Wash cold",
                "Do not bleach",
                "Air dry"
            ]
        ];
    }

    // =========================
    // UPCYCLE LOG
    // =========================
    public function logUpcycleTransformation($data)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO UpcycleTransformation
            (
                user_id,
                listing_id,
                before_image_url,
                after_image_url,
                steps,
                materials_used
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "iissss",
            $data["user_id"],
            $data["listing_id"],
            $data["before_image_url"],
            $data["after_image_url"],
            $data["steps"],
            $data["materials_used"]
        );

        return $stmt->execute();
    }

    // =========================
    // MAP ROW
    // =========================
    private function mapListingRow($row)
    {
        return $row;
    }
}