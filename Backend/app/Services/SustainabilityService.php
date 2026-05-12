<?php

require_once __DIR__ . "/../../database/connection.php";


class SustainabilityService {

    public function calculateCarbonFootprint($material, $actionType) {

        $materials = [
            "cotton" => [
                "co2" => 2.5,
                "water" => 2700,
                "waste" => 0.2
            ],

            "polyester" => [
                "co2" => 5.5,
                "water" => 120,
                "waste" => 0.4
            ],

            "denim" => [
                "co2" => 4.2,
                "water" => 7600,
                "waste" => 0.5
            ]
        ];

        $material = strtolower($material);

        if (!isset($materials[$material])) {
            return null;
        }

        $impactMultiplier = match(strtolower($actionType)) {
            "swap" => 1.0,
            "resale" => 1.0,
            "upcycle" => 1.5,
            default => 1.0
        };

        return [
            "co2_saved" =>
                $materials[$material]["co2"] * $impactMultiplier,

            "water_saved" =>
                $materials[$material]["water"] * $impactMultiplier,

            "waste_reduced" =>
                $materials[$material]["waste"] * $impactMultiplier
        ];
    }

    public function calculateEcoPoints(
        $co2,
        $water,
        $waste
    ) {

        return round(
            ($co2 * 10) +
            ($water * 0.01) +
            ($waste * 50)
        );
    }

    public function calculateTrustScore(
        $averageRating,
        $completedTransactions,
        $disputes
    ) {

        $score =
            ($averageRating * 15) +
            ($completedTransactions * 2) -
            ($disputes * 10);

        if ($score < 0) {
            $score = 0;
        }

        if ($score > 100) {
            $score = 100;
        }

        return round($score);
    }
}