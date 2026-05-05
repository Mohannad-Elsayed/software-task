<?php
namespace app\Models;

class Listing {
    private $listing_id;
    private $user_id;
    private $material_id;
    private $title;
    private $description;
    private $price;
    private $category;
    private $condition_status;
    private $listing_type;
    private $status;
    
    public function __construct(
        $listing_id = null,
        $user_id = null,
        $material_id = null,
        $title = null,
        $description = null,
        $price = null,
        $category = null,
        $condition_status = null,
        $listing_type = null,
        $status = null
    ) {
        $this->listing_id = $listing_id;
        $this->user_id = $user_id;
        $this->material_id = $material_id;
        $this->title = $title;
        $this->description = $description;
        $this->price = $price;
        $this->category = $category;
        $this->condition_status = $condition_status;
        $this->listing_type = $listing_type;
        $this->status = $status;
    }

    public function getListingId() { return $this->listing_id; }
    public function setListingId($listing_id) { $this->listing_id = $listing_id; return $this; }

    public function getUserId() { return $this->user_id; }
    public function setUserId($user_id) { $this->user_id = $user_id; return $this; }

    public function getMaterialId() { return $this->material_id; }
    public function setMaterialId($material_id) { $this->material_id = $material_id; return $this; }

    public function getTitle() { return $this->title; }
    public function setTitle($title) { $this->title = $title; return $this; }

    public function getDescription() { return $this->description; }
    public function setDescription($description) { $this->description = $description; return $this; }

    public function getPrice() { return $this->price; }
    public function setPrice($price) { $this->price = $price; return $this; }

    public function getCategory() { return $this->category; }
    public function setCategory($category) { $this->category = $category; return $this; }

    public function getConditionStatus() { return $this->condition_status; }
    public function setConditionStatus($condition_status) { $this->condition_status = $condition_status; return $this; }

    public function getListingType() { return $this->listing_type; }
    public function setListingType($listing_type) { $this->listing_type = $listing_type; return $this; }

    public function getStatus() { return $this->status; }
    public function setStatus($status) { $this->status = $status; return $this; }
}