<?php
namespace app\Models;

class UpcycleTransformation {
    public $transformation_id;
    public $user_id;
    public $listing_id;
    public $before_image_url;
    public $after_image_url;
    public $steps;
    public $materials_used;
    
    public function __construct($data = []) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}