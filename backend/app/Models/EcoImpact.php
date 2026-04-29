<?php

namespace app\Models;

class EcoImpact
{
    private $impact_id;
    private $user_id;
    private $co2_saved;
    private $water_saved;
    private $waste_reduced;
    private $eco_points;

    public function __construct(
        $impact_id = null,
        $user_id = null,
        $co2_saved = null,
        $water_saved = null,
        $waste_reduced = null,
        $eco_points = null
    ) {
        $this->impact_id = $impact_id;
        $this->user_id = $user_id;
        $this->co2_saved = $co2_saved;
        $this->water_saved = $water_saved;
        $this->waste_reduced = $waste_reduced;
        $this->eco_points = $eco_points;
    }

    public function getImpactId() { return $this->impact_id; }
    public function setImpactId($impact_id) { $this->impact_id = $impact_id; return $this; }

    public function getUserId() { return $this->user_id; }
    public function setUserId($user_id) { $this->user_id = $user_id; return $this; }

    public function getCo2Saved() { return $this->co2_saved; }
    public function setCo2Saved($co2_saved) { $this->co2_saved = $co2_saved; return $this; }

    public function getWaterSaved() { return $this->water_saved; }
    public function setWaterSaved($water_saved) { $this->water_saved = $water_saved; return $this; }

    public function getWasteReduced() { return $this->waste_reduced; }
    public function setWasteReduced($waste_reduced) { $this->waste_reduced = $waste_reduced; return $this; }

    public function getEcoPoints() { return $this->eco_points; }
    public function setEcoPoints($eco_points) { $this->eco_points = $eco_points; return $this; }
}