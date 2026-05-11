<?php
namespace app\Services;

class ListingService {

    private $db;

    public function __construct() {
        $this->db = db();
    }

    public function listListings() {
        $query = "SELECT l.*, u.username, m.name as material_name 
                  FROM Listing l 
                  LEFT JOIN User u ON l.user_id = u.user_id 
                  LEFT JOIN MaterialTaxonomy m ON l.material_id = m.material_id
                  WHERE l.status = 'active'
                  ORDER BY l.listing_id DESC";

        $result = $this->db->query($query);
        $listings = [];

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $listing = $this->mapListingRow($row);
                $listings[] = $this->listingToArray($listing, [
                    'user' => [
                        'user_id' => $row['user_id'],
                        'username' => $row['username'],
                    ],
                    'material' => $row['material_name'] ? [
                        'material_id' => $row['material_id'],
                        'name' => $row['material_name'],
                    ] : null,
                ]);
            }
        }

        return $listings;
    }

    public function getListingsByUser($userId) {
        $stmt = $this->db->prepare("
            SELECT l.*, u.username, m.name as material_name 
            FROM Listing l 
            LEFT JOIN User u ON l.user_id = u.user_id 
            LEFT JOIN MaterialTaxonomy m ON l.material_id = m.material_id
            WHERE l.user_id = ?
            ORDER BY l.listing_id DESC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $listings = [];

        while ($row = $result->fetch_assoc()) {
            $listing = $this->mapListingRow($row);
            $listings[] = $this->listingToArray($listing, [
                'user' => [
                    'user_id' => $row['user_id'],
                    'username' => $row['username'],
                ],
                'material' => $row['material_name'] ? [
                    'material_id' => $row['material_id'],
                    'name' => $row['material_name'],
                ] : null,
            ]);
        }
        return $listings;
    }

    public function showListing($id) {
        $stmt = $this->db->prepare("
            SELECT l.*, u.username, m.name as material_name 
            FROM Listing l 
            LEFT JOIN User u ON l.user_id = u.user_id 
            LEFT JOIN MaterialTaxonomy m ON l.material_id = m.material_id
            WHERE l.listing_id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $listing = $this->mapListingRow($row);

            $relations = [
                'user' => [
                    'user_id' => $row['user_id'],
                    'username' => $row['username'],
                ],
                'material' => $row['material_name'] ? [
                    'material_id' => $row['material_id'],
                    'name' => $row['material_name'],
                ] : null,
            ];

            if ($listing->getListingType() === 'upcycle') {
                $upcycleStmt = $this->db->prepare("SELECT * FROM UpcycleTransformation WHERE listing_id = ?");
                $upcycleStmt->bind_param("i", $id);
                $upcycleStmt->execute();
                $upcycleResult = $upcycleStmt->get_result();

                if ($upcycleResult && $upcycleResult->num_rows > 0) {
                    $upcycleRow = $upcycleResult->fetch_assoc();
                    $relations['upcycle_transformation'] = $this->upcycleToArray($this->mapUpcycleRow($upcycleRow));
                }
            }

            return $this->listingToArray($listing, $relations);
        }

        return null;
    }

    public function createListing($data) {
        if (!isset($data['price']) || $data['price'] <= 0) {
            throw new \Exception("Price must be greater than zero.");
        }
        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare("
                INSERT INTO Listing 
                (user_id, material_id, title, description, price, category, condition_status, listing_type, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param("iissdssss",
                $data['user_id'],
                $data['material_id'],
                $data['title'],
                $data['description'],
                $data['price'],
                $data['category'],
                $data['condition_status'],
                $data['listing_type'],
                $data['status']
            );

            $stmt->execute();
            $listingId = $stmt->insert_id;

            if (isset($data['listing_type']) && $data['listing_type'] === 'upcycle' && isset($data['upcycle_data'])) {
                $upStmt = $this->db->prepare("
                    INSERT INTO UpcycleTransformation 
                    (user_id, listing_id, before_image_url, after_image_url, steps, materials_used) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $upData = $data['upcycle_data'];
                $upStmt->bind_param("iissss",
                    $data['user_id'],
                    $listingId,
                    $upData['before_image_url'],
                    $upData['after_image_url'],
                    $upData['steps'],
                    $upData['materials_used']
                );
                $upStmt->execute();
            }

            $this->db->commit();
            return $listingId;

        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function editListing($id, $data) {
        $stmt = $this->db->prepare("SELECT * FROM Listing WHERE listing_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return false;
        }
        
        $existing = $result->fetch_assoc();

        $stmt = $this->db->prepare("
            UPDATE Listing 
            SET title = ?, description = ?, price = ?, category = ?, condition_status = ?, listing_type = ?, status = ?
            WHERE listing_id = ?
        ");

        $title = $data['title'] ?? $existing['title'];
        $description = $data['description'] ?? $existing['description'];
        $price = $data['price'] ?? $existing['price'];
        $category = $data['category'] ?? $existing['category'];
        $condition_status = $data['condition_status'] ?? $existing['condition_status'];
        $listing_type = $data['listing_type'] ?? $existing['listing_type'];
        $status = $data['status'] ?? $existing['status'];

        $stmt->bind_param("ssdssssi", 
            $title,
            $description,
            $price,
            $category,
            $condition_status,
            $listing_type,
            $status,
            $id
        );

        return $stmt->execute();
    }

    public function deleteListing($id) {
        $stmt = $this->db->prepare("DELETE FROM Listing WHERE listing_id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function assessCondition($id, $conditionData) {
        $stmt = $this->db->prepare("UPDATE Listing SET condition_status = ? WHERE listing_id = ?");
        $conditionStatus = $conditionData['condition_status'] ?? null;
        $stmt->bind_param("si", $conditionStatus, $id);
        return $stmt->execute();
    }

    public function generateCareInstructions($id) {
        return "Wash with cold water. Dry on low heat.";
    }

    public function logUpcycleTransformation($id, $upcycleData) {
        $stmt = $this->db->prepare("
            INSERT INTO UpcycleTransformation 
            (listing_id, user_id, before_image_url, after_image_url, steps, materials_used) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iissss",
            $id,
            $upcycleData['user_id'],
            $upcycleData['before_image_url'],
            $upcycleData['after_image_url'],
            $upcycleData['steps'],
            $upcycleData['materials_used']
        );
        return $stmt->execute();
    }

    private function mapListingRow(array $row) {
        return new \app\Models\Listing(
            $row['listing_id'] ?? null,
            $row['user_id'] ?? null,
            $row['material_id'] ?? null,
            $row['title'] ?? null,
            $row['description'] ?? null,
            $row['price'] ?? null,
            $row['category'] ?? null,
            $row['condition_status'] ?? null,
            $row['listing_type'] ?? null,
            $row['status'] ?? null
        );
    }

    private function mapUpcycleRow(array $row) {
        return new \app\Models\UpcycleTransformation(
            $row['transformation_id'] ?? null,
            $row['user_id'] ?? null,
            $row['listing_id'] ?? null,
            $row['before_image_url'] ?? null,
            $row['after_image_url'] ?? null,
            $row['steps'] ?? null,
            $row['materials_used'] ?? null
        );
    }

    private function listingToArray(\app\Models\Listing $listing, array $relations = []) {
        $payload = [
            'listing_id' => $listing->getListingId(),
            'user_id' => $listing->getUserId(),
            'material_id' => $listing->getMaterialId(),
            'title' => $listing->getTitle(),
            'description' => $listing->getDescription(),
            'price' => $listing->getPrice(),
            'category' => $listing->getCategory(),
            'condition_status' => $listing->getConditionStatus(),
            'listing_type' => $listing->getListingType(),
            'status' => $listing->getStatus(),
        ];

        foreach ($relations as $key => $value) {
            if ($value !== null) {
                $payload[$key] = $value;
            }
        }

        return $payload;
    }

    private function upcycleToArray(\app\Models\UpcycleTransformation $transformation) {
        return [
            'transformation_id' => $transformation->getTransformationId(),
            'user_id' => $transformation->getUserId(),
            'listing_id' => $transformation->getListingId(),
            'before_image_url' => $transformation->getBeforeImageUrl(),
            'after_image_url' => $transformation->getAfterImageUrl(),
            'steps' => $transformation->getSteps(),
            'materials_used' => $transformation->getMaterialsUsed(),
        ];
    }
}