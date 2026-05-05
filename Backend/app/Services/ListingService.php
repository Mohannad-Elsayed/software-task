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
                  ORDER BY l.listing_id DESC";
                  
        $result = $this->db->query($query);
        $listings = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $listing = new \app\Models\Listing($row);
                $listing->user = ['user_id' => $row['user_id'], 'username' => $row['username']];
                if ($row['material_name']) {
                    $listing->material = ['material_id' => $row['material_id'], 'name' => $row['material_name']];
                }
                $listings[] = $listing;
            }
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
            $listing = new \app\Models\Listing($row);
            $listing->user = ['user_id' => $row['user_id'], 'username' => $row['username']];
            
            if ($row['material_name']) {
                $listing->material = ['material_id' => $row['material_id'], 'name' => $row['material_name']];
            }
            
            // If upcycle, fetch transformations
            if ($listing->listing_type === 'upcycle') {
                $upcycleStmt = $this->db->prepare("SELECT * FROM UpcycleTransformation WHERE listing_id = ?");
                $upcycleStmt->bind_param("i", $id);
                $upcycleStmt->execute();
                $upcycleResult = $upcycleStmt->get_result();
                
                if ($upcycleResult && $upcycleResult->num_rows > 0) {
                    $listing->upcycle_transformation = new \app\Models\UpcycleTransformation($upcycleResult->fetch_assoc());
                }
            }
            
            return $listing;
        }
        
        return null;
    }

    public function createListing($data) {
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
            
            // If it's an upcycle, we need to create the UpcycleTransformation record too
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
        $stmt = $this->db->prepare("
            UPDATE Listing 
            SET title = ?, description = ?, price = ?, category = ?, condition_status = ?, listing_type = ?, status = ?
            WHERE listing_id = ?
        ");
        
        $title = $data['title'] ?? null;
        $description = $data['description'] ?? null;
        $price = $data['price'] ?? null;
        $category = $data['category'] ?? null;
        $condition_status = $data['condition_status'] ?? null;
        $listing_type = $data['listing_type'] ?? null;
        $status = $data['status'] ?? null;

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
        $stmt->bind_param("si", $conditionData['condition_status'], $id);
        return $stmt->execute();
    }

    public function generateCareInstructions($id) {
        // Mock generation
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
}