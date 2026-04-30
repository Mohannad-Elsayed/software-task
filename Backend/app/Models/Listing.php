<?php
namespace app\Models;

class Listing {
    public $listing_id;
    public $user_id;
    public $material_id;
    public $title;
    public $description;
    public $price;
    public $category;
    public $condition_status;
    public $listing_type;
    public $status;
    
    // Relations mapping
    public $user;
    public $material;
    public $upcycle_transformation;
    
    public function __construct($data = []) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}