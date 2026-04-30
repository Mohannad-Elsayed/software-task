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
}