<?php
namespace app\Models;

class MaterialTaxonomy {
    public $material_id;
    public $name;
    public $parent_material_id;
    
    public function __construct($data = []) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}