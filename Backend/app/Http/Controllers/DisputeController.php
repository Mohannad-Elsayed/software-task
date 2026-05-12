<?php

require_once __DIR__ . '/../../Services/DisputeService.php';

class DisputeController
{
    private $disputeService;

    public function __construct()
    {
        $this->disputeService = new DisputeService();
    }

    public function store()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid JSON input"]);
            return;
        }

        try {
            $result = $this->disputeService->createDispute($data);
            if (isset($result['error'])) {
                http_response_code(400);
            } else {
                http_response_code(201);
            }
            echo json_encode($result);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(["error" => "Server error: " . $e->getMessage()]);
        }
    }

    public function index()
    {
        header('Content-Type: application/json');
        $userId = $_GET['user_id'] ?? null;

        if (!$userId) {
            http_response_code(400);
            echo json_encode(["error" => "user_id is required"]);
            return;
        }

        try {
            $disputes = $this->disputeService->getDisputesByInitiator($userId);
            echo json_encode(["status" => "success", "data" => $disputes]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(["error" => "Server error: " . $e->getMessage()]);
        }
    }
}
