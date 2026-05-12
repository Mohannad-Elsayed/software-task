<?php

namespace app\Services;

require_once __DIR__ . '/../../database/connection.php';

class DisputeService
{
    private $conn;

    public function __construct()
    {
        $this->conn = db();
    }

    public function createDispute($data)
    {
        $initiatorId = $data['initiator_id'] ?? null;
        $orderId = $data['order_id'] ?? null;
        $requestId = $data['request_id'] ?? null;
        $reason = $data['reason'] ?? '';

        if (!$initiatorId || (!$orderId && !$requestId)) {
            return ["error" => "Initiator ID and either Order ID or Request ID are required."];
        }

        $stmt = $this->conn->prepare("
            INSERT INTO Dispute (initiator_id, order_id, request_id, reason, status)
            VALUES (?, ?, ?, ?, 'open')
        ");
        $stmt->bind_param("iiis", $initiatorId, $orderId, $requestId, $reason);

        if ($stmt->execute()) {
            return [
                "success" => true,
                "dispute_id" => $stmt->insert_id,
                "message" => "Dispute created successfully."
            ];
        } else {
            return ["error" => "Failed to create dispute: " . $stmt->error];
        }
    }

    public function getDisputesByInitiator($userId)
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM Dispute WHERE initiator_id = ? ORDER BY created_at DESC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $disputes = [];
        while ($row = $result->fetch_assoc()) {
            $disputes[] = $row;
        }
        return $disputes;
    }
}
