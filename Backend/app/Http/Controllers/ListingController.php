<?php
require_once __DIR__ . '/../../Services/ListingService.php';


class ListingController {
    
    private $listingService;

    public function __construct() {
        $this->listingService = new ListingService();
    }

    public function index() {
        try {
            $listings = $this->listingService->listListings();
            http_response_code(200);
            echo json_encode(["status" => "success", "data" => $listings]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function userListings() {
        $userId = $_GET['user_id'] ?? null;
        if (!$userId) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "user_id is required."]);
            return;
        }
        try {
            $listings = $this->listingService->listListings((int) $userId);
            http_response_code(200);
            echo json_encode(["status" => "success", "data" => $listings]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function show($id) {
        if (!$id || !is_numeric($id)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid ID provided."]);
            return;
        }

        try {
            $listing = $this->listingService->showListing((int) $id);
            if ($listing) {
                http_response_code(200);
                echo json_encode(["status" => "success", "data" => $listing]);
            } else {
                http_response_code(404);
                echo json_encode(["status" => "error", "message" => "Listing not found."]);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function store() {
        // Read JSON input
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        if (!$data) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid JSON payload."]);
            return;
        }

        // Basic validation
        $required = ['user_id', 'title', 'price'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Missing required field: $field"]);
                return;
            }
        }

        // Default constraints
        $data['material_id'] = $data['material_id'] ?? null;
        $data['description'] = $data['description'] ?? null;
        $data['category'] = $data['category'] ?? 'Uncategorized';
        $data['condition_status'] = $data['condition_status'] ?? 'good';
        $data['listing_type'] = $data['listing_type'] ?? 'sale';
        $data['status'] = $data['status'] ?? 'active';

        try {
            $listingId = $this->listingService->createListing($data);
            http_response_code(201);
            echo json_encode([
                "status" => "success", 
                "message" => "Listing created successfully.",
                "data" => ["listing_id" => $listingId]
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
        }
    }

    public function editListing($id) {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        if (!$data) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid JSON payload."]);
            return;
        }

        try {
            $success = $this->listingService->editListing($id, $data);
            if ($success) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Listing updated successfully."]);
            } else {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Failed to update listing."]);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function deleteListing($id) {
        try {
            $success = $this->listingService->deleteListing($id);
            if ($success) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Listing deleted successfully."]);
            } else {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Failed to delete listing."]);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function assessCondition($id) {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        if (!$data || !isset($data['condition_status'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Missing required field: condition_status"]);
            return;
        }

        try {
            $success = $this->listingService->assessCondition($id, $data);
            if ($success) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Condition assessed successfully."]);
            } else {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Failed to assess condition."]);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function generateCareInstructions($id) {
        try {
            $instructions = $this->listingService->generateCareInstructions($id);
            http_response_code(200);
            echo json_encode(["status" => "success", "data" => ["instructions" => $instructions]]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function logUpcycleTransformation($id) {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        if (!$data || !isset($data['user_id'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Missing required upcycle data."]);
            return;
        }

        try {
            $success = $this->listingService->logUpcycleTransformation($id, $data);
            http_response_code(201);
            echo json_encode(["status" => "success", "message" => "Upcycle transformation logged successfully."]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}