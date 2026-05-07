<?php
namespace app\Models;

class UpcycleTransformation {
    private $transformation_id;
    private $user_id;
    private $listing_id;
    private $before_image_url;
    private $after_image_url;
    private $steps;
    private $materials_used;
    
    public function __construct(
        $transformation_id = null,
        $user_id = null,
        $listing_id = null,
        $before_image_url = null,
        $after_image_url = null,
        $steps = null,
        $materials_used = null
    ) {
        $this->transformation_id = $transformation_id;
        $this->user_id = $user_id;
        $this->listing_id = $listing_id;
        $this->before_image_url = $before_image_url;
        $this->after_image_url = $after_image_url;
        $this->steps = $steps;
        $this->materials_used = $materials_used;
    }

    public function getTransformationId() { return $this->transformation_id; }
    public function setTransformationId($transformation_id) { $this->transformation_id = $transformation_id; return $this; }

    public function getUserId() { return $this->user_id; }
    public function setUserId($user_id) { $this->user_id = $user_id; return $this; }

    public function getListingId() { return $this->listing_id; }
    public function setListingId($listing_id) { $this->listing_id = $listing_id; return $this; }

    public function getBeforeImageUrl() { return $this->before_image_url; }
    public function setBeforeImageUrl($before_image_url) { $this->before_image_url = $before_image_url; return $this; }

    public function getAfterImageUrl() { return $this->after_image_url; }
    public function setAfterImageUrl($after_image_url) { $this->after_image_url = $after_image_url; return $this; }

    public function getSteps() { return $this->steps; }
    public function setSteps($steps) { $this->steps = $steps; return $this; }

    public function getMaterialsUsed() { return $this->materials_used; }
    public function setMaterialsUsed($materials_used) { $this->materials_used = $materials_used; return $this; }
}