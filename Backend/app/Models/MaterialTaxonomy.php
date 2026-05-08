<?php
namespace app\Models;

class MaterialTaxonomy {
    private $material_id;
    private $name;
    private $parent_material_id;
    
    public function __construct(
        $material_id = null,
        $name = null,
        $parent_material_id = null
    ) {
        $this->material_id = $material_id;
        $this->name = $name;
        $this->parent_material_id = $parent_material_id;
    }

    public function getMaterialId() { return $this->material_id; }
    public function setMaterialId($material_id) { $this->material_id = $material_id; return $this; }

    public function getName() { return $this->name; }
    public function setName($name) { $this->name = $name; return $this; }

    public function getParentMaterialId() { return $this->parent_material_id; }
    public function setParentMaterialId($parent_material_id) { $this->parent_material_id = $parent_material_id; return $this; }
}