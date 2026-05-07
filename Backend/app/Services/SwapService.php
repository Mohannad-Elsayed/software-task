<?php

namespace app\Services;

class SwapService {

    private $db;
    private const ACTIVE_SWAP_STATUSES = ['pending', 'negotiating', 'accepted'];
    private const FINAL_SWAP_STATUSES = ['rejected', 'expired', 'completed'];

    public function __construct() {
        $this->db = db();
    }

    public function createSwapRequest($initiatorId, $partnerId, $requestedListingId) {
        $this->db->begin_transaction();
        try {
            // Validate that the requested listing exists and belongs to the partner
            $stmt = $this->db->prepare("SELECT user_id, status FROM Listing WHERE listing_id = ? FOR UPDATE");
            $stmt->bind_param("i", $requestedListingId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                throw new \Exception("Listing not found.");
            }

            $row = $result->fetch_assoc();
            if ($row['user_id'] != $partnerId) {
                throw new \Exception("Listing does not belong to the specified partner.");
            }

            if (!in_array($row['status'], ['active', 'pending', 'negotiating'], true)) {
                throw new \Exception("Listing is not available for swapping.");
            }

            // Create swap request
            $stmt = $this->db->prepare("
                INSERT INTO SwapRequest (initiator_id, partner_id, requested_listing_id, status)
                VALUES (?, ?, ?, 'pending')
            ");
            $stmt->bind_param("iii", $initiatorId, $partnerId, $requestedListingId);
            $stmt->execute();

            $requestId = $this->db->insert_id;
            $this->db->commit();

            return $requestId;
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function acceptSwapRequest($requestId) {
        $this->db->begin_transaction();
        try {
            // Get swap request details
            $stmt = $this->db->prepare("SELECT * FROM SwapRequest WHERE request_id = ? FOR UPDATE");
            $stmt->bind_param("i", $requestId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                throw new \Exception("Swap request not found.");
            }

            $swapRequest = $result->fetch_assoc();

            if (in_array($swapRequest['status'], self::FINAL_SWAP_STATUSES, true)) {
                throw new \Exception("Swap request is no longer active.");
            }

            // Update swap request status to accepted
            $stmt = $this->db->prepare("UPDATE SwapRequest SET status = 'accepted' WHERE request_id = ?");
            $stmt->bind_param("i", $requestId);
            $stmt->execute();

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function rejectSwapRequest($requestId) {
        $this->db->begin_transaction();
        try {
            // Get swap request details
            $stmt = $this->db->prepare("SELECT * FROM SwapRequest WHERE request_id = ? FOR UPDATE");
            $stmt->bind_param("i", $requestId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                throw new \Exception("Swap request not found.");
            }

            $swapRequest = $result->fetch_assoc();
            if (in_array($swapRequest['status'], self::FINAL_SWAP_STATUSES, true)) {
                throw new \Exception("Swap request is no longer active.");
            }

            // Update swap request status to rejected
            $stmt = $this->db->prepare("UPDATE SwapRequest SET status = 'rejected' WHERE request_id = ?");
            $stmt->bind_param("i", $requestId);
            $stmt->execute();

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function createOffer($requestId, $initiatorId, $offerValue) {
        $this->db->begin_transaction();
        try {
            // Verify the swap request exists
            $stmt = $this->db->prepare("SELECT * FROM SwapRequest WHERE request_id = ? FOR UPDATE");
            $stmt->bind_param("i", $requestId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                throw new \Exception("Swap request not found.");
            }

            $swapRequest = $result->fetch_assoc();
            if (in_array($swapRequest['status'], self::FINAL_SWAP_STATUSES, true)) {
                throw new \Exception("Swap request is no longer active.");
            }

            // Create offer
            $stmt = $this->db->prepare("
                INSERT INTO Offer (request_id, initiator_id, offer_value, status)
                VALUES (?, ?, ?, 'pending')
            ");
            $stmt->bind_param("iis", $requestId, $initiatorId, $offerValue);
            $stmt->execute();
            $offerId = $this->db->insert_id;

            $this->transitionSwapStatus($requestId, 'negotiating');

            $this->db->commit();

            return $offerId;
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function counterOffer($requestId, $initiatorId, $offerValue) {
        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare("SELECT * FROM SwapRequest WHERE request_id = ? FOR UPDATE");
            $stmt->bind_param("i", $requestId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                throw new \Exception("Swap request not found.");
            }

            $swapRequest = $result->fetch_assoc();
            if (in_array($swapRequest['status'], self::FINAL_SWAP_STATUSES, true)) {
                throw new \Exception("Swap request is no longer active.");
            }

            $stmt = $this->db->prepare("
                INSERT INTO Offer (request_id, initiator_id, offer_value, status)
                VALUES (?, ?, ?, 'negotiating')
            ");
            $stmt->bind_param("iis", $requestId, $initiatorId, $offerValue);
            $stmt->execute();
            $offerId = $this->db->insert_id;

            $this->transitionSwapStatus($requestId, 'negotiating');

            $this->db->commit();

            return $offerId;
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function balanceSwapValue($requestId) {
        $stmt = $this->db->prepare("
            SELECT offer_value, initiator_id
            FROM Offer
            WHERE request_id = ?
            ORDER BY offer_id DESC
            LIMIT 2
        ");
        $stmt->bind_param("i", $requestId);
        $stmt->execute();
        $result = $stmt->get_result();

        $offers = [];
        while ($result && ($row = $result->fetch_assoc())) {
            $offers[] = [
                'value' => (float) $row['offer_value'],
                'initiator_id' => $row['initiator_id']
            ];
        }

        if (count($offers) < 2) {
            return [
                'request_id' => $requestId,
                'latest_offer_value' => $offers[0]['value'] ?? null,
                'previous_offer_value' => null,
                'balance_value' => $offers[0]['value'] ?? null,
                'owed_to' => $offers[0]['initiator_id'] ?? null
            ];
        }

        // Note: This logic is simplified for demo purposes.
        // It compares the last two offers directly. For a completely accurate calculation,
        // it should compare offer values against the base listing price.
        $latest = $offers[0];
        $previous = $offers[1];
        $balance = round($latest['value'] - $previous['value'], 2);
        
        $owedTo = null;
        if ($balance > 0) {
            $owedTo = $latest['initiator_id'];
        } elseif ($balance < 0) {
            $owedTo = $previous['initiator_id'];
        }

        return [
            'request_id' => $requestId,
            'latest_offer_value' => $latest['value'],
            'previous_offer_value' => $previous['value'],
            'balance_value' => abs($balance),
            'owed_to' => $owedTo
        ];
    }

    public function lockSwapAgreement($requestId) {
        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare("SELECT * FROM SwapRequest WHERE request_id = ? FOR UPDATE");
            $stmt->bind_param("i", $requestId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                throw new \Exception("Swap request not found.");
            }

            $swapRequest = $result->fetch_assoc();
            if (in_array($swapRequest['status'], ['rejected', 'expired', 'completed'], true)) {
                throw new \Exception("Swap request cannot be locked.");
            }

            $listingId = (int) $swapRequest['requested_listing_id'];
            $stmt = $this->db->prepare("SELECT listing_id, status FROM Listing WHERE listing_id = ? FOR UPDATE");
            $stmt->bind_param("i", $listingId);
            $stmt->execute();
            $listingResult = $stmt->get_result();

            if ($listingResult->num_rows === 0) {
                throw new \Exception("Requested listing not found.");
            }

            $listing = $listingResult->fetch_assoc();
            if (in_array($listing['status'], ['locked', 'swap_locked'], true)) {
                throw new \Exception("Listing is already locked for a swap.");
            }

            $stmt = $this->db->prepare("UPDATE SwapRequest SET status = 'accepted' WHERE request_id = ?");
            $stmt->bind_param("i", $requestId);
            $stmt->execute();

            $this->updateListingStatus($listingId, 'swap_locked');

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function expirePendingSwap($requestId) {
        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare("SELECT * FROM SwapRequest WHERE request_id = ? FOR UPDATE");
            $stmt->bind_param("i", $requestId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                throw new \Exception("Swap request not found.");
            }

            $swapRequest = $result->fetch_assoc();
            if (in_array($swapRequest['status'], ['expired', 'rejected', 'completed'], true)) {
                return true;
            }

            $stmt = $this->db->prepare("UPDATE SwapRequest SET status = 'expired' WHERE request_id = ?");
            $stmt->bind_param("i", $requestId);
            $stmt->execute();

            if ($swapRequest['status'] === 'accepted') {
                $listingId = (int) $swapRequest['requested_listing_id'];
                $chkStmt = $this->db->prepare("SELECT status FROM Listing WHERE listing_id = ? FOR UPDATE");
                $chkStmt->bind_param("i", $listingId);
                $chkStmt->execute();
                $lRow = $chkStmt->get_result()->fetch_assoc();
                if ($lRow && $lRow['status'] === 'swap_locked') {
                    $this->updateListingStatus($listingId, 'active');
                }
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function getSwapRequest($requestId) {
        $stmt = $this->db->prepare("
            SELECT sr.*, 
                   u1.username as initiator_username, 
                   u2.username as partner_username,
                   l.title as requested_listing_title
            FROM SwapRequest sr
            LEFT JOIN User u1 ON sr.initiator_id = u1.user_id
            LEFT JOIN User u2 ON sr.partner_id = u2.user_id
            LEFT JOIN Listing l ON sr.requested_listing_id = l.listing_id
            WHERE sr.request_id = ?
        ");
        $stmt->bind_param("i", $requestId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }

        return null;
    }

    public function getOffers($requestId) {
        $stmt = $this->db->prepare("
            SELECT o.*, u.username as initiator_username
            FROM Offer o
            LEFT JOIN User u ON o.initiator_id = u.user_id
            WHERE o.request_id = ?
            ORDER BY o.offer_id DESC
        ");
        $stmt->bind_param("i", $requestId);
        $stmt->execute();
        $result = $stmt->get_result();

        $offers = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $offers[] = $row;
            }
        }

        return $offers;
    }

    private function transitionSwapStatus($requestId, $status) {
        $stmt = $this->db->prepare("UPDATE SwapRequest SET status = ? WHERE request_id = ?");
        $stmt->bind_param("si", $status, $requestId);
        $stmt->execute();
    }

    private function updateListingStatus($listingId, $status) {
        $stmt = $this->db->prepare("UPDATE Listing SET status = ? WHERE listing_id = ?");
        $stmt->bind_param("si", $status, $listingId);
        $stmt->execute();
    }
}
