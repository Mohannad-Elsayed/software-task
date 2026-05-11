<?php

namespace app\Http\Controllers;

use app\Services\SwapService;
require_once __DIR__ . '/../../Services/SwapService.php';

class SwapController {
    
    private $swapService;

    public function __construct() {
        $this->swapService = new SwapService();
    }

    public function sendSwapRequest() {
        // Read JSON input
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        if (!$data) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid JSON payload."]);
            return;
        }

        // Validate required fields
        $required = ['initiator_id', 'partner_id', 'requested_listing_id'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Missing required field: $field"]);
                return;
            }
        }

        try {
            $requestId = $this->swapService->createSwapRequest(
                $data['initiator_id'],
                $data['partner_id'],
                $data['requested_listing_id'],
                $data['offered_listing_id'] ?? null
            );
            http_response_code(201);
            echo json_encode([
                "status" => "success",
                "message" => "Swap proposal sent!",
                "data" => ["request_id" => $requestId]
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
        }
    }

    public function listSwapRequests() {
        $userId = $_GET['user_id'] ?? null;
        $type = $_GET['type'] ?? 'incoming'; // incoming or outgoing

        if (!$userId) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "user_id is required."]);
            return;
        }

        try {
            $requests = $this->swapService->getSwapRequestsByUser((int) $userId, $type);
            echo json_encode(["status" => "success", "data" => $requests]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function acceptSwapRequest($requestId) {
        if (!$requestId || !is_numeric($requestId)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid request ID provided."]);
            return;
        }

        try {
            $result = $this->swapService->acceptSwapRequest((int) $requestId);
            if ($result) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Swap request accepted."]);
            } else {
                http_response_code(404);
                echo json_encode(["status" => "error", "message" => "Swap request not found."]);
            }
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
        }
    }

    public function rejectSwapRequest($requestId) {
        if (!$requestId || !is_numeric($requestId)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid request ID provided."]);
            return;
        }

        try {
            $result = $this->swapService->rejectSwapRequest((int) $requestId);
            if ($result) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Swap request rejected."]);
            } else {
                http_response_code(404);
                echo json_encode(["status" => "error", "message" => "Swap request not found."]);
            }
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
        }
    }

    public function makeOffer($requestId) {
        if (!$requestId || !is_numeric($requestId)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid request ID provided."]);
            return;
        }

        // Read JSON input
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        if (!$data) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid JSON payload."]);
            return;
        }

        // Validate required fields
        $required = ['initiator_id', 'offer_value'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Missing required field: $field"]);
                return;
            }
        }

        try {
            $offerId = $this->swapService->createOffer(
                (int) $requestId,
                $data['initiator_id'],
                $data['offer_value']
            );
            http_response_code(201);
            echo json_encode([
                "status" => "success",
                "message" => "Offer created successfully.",
                "data" => ["offer_id" => $offerId]
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
        }
    }

    public function counterOffer($requestId) {
        if (!$requestId || !is_numeric($requestId)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid request ID provided."]);
            return;
        }

        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        if (!$data) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid JSON payload."]);
            return;
        }

        $required = ['initiator_id', 'offer_value'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Missing required field: $field"]);
                return;
            }
        }

        try {
            $offerId = $this->swapService->counterOffer(
                (int) $requestId,
                $data['initiator_id'],
                $data['offer_value']
            );
            http_response_code(201);
            echo json_encode([
                "status" => "success",
                "message" => "Counter offer created successfully.",
                "data" => ["offer_id" => $offerId]
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
        }
    }

    public function balanceSwapValue($requestId) {
        if (!$requestId || !is_numeric($requestId)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid request ID provided."]);
            return;
        }

        try {
            $balance = $this->swapService->balanceSwapValue((int) $requestId);
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "data" => $balance
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
        }
    }

    public function lockSwapAgreement($requestId) {
        if (!$requestId || !is_numeric($requestId)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid request ID provided."]);
            return;
        }

        try {
            $result = $this->swapService->lockSwapAgreement((int) $requestId);
            if ($result) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Swap agreement locked."]);
            } else {
                http_response_code(404);
                echo json_encode(["status" => "error", "message" => "Swap request not found."]);
            }
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
        }
    }

    public function expirePendingSwap($requestId) {
        if (!$requestId || !is_numeric($requestId)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid request ID provided."]);
            return;
        }

        try {
            $result = $this->swapService->expirePendingSwap((int) $requestId);
            if ($result) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Swap request expired."]);
            } else {
                http_response_code(404);
                echo json_encode(["status" => "error", "message" => "Swap request not found."]);
            }
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
        }
    }
}
