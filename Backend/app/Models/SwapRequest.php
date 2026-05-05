<?php

namespace app\Models;

class SwapRequest
{
    private $swap_request_id;
    private $from_user_id;
    private $to_user_id;
    private $offering_listing_id;
    private $requested_listing_id;
    private $status;
    private $created_at;

    public function __construct(
        $swap_request_id = null,
        $from_user_id = null,
        $to_user_id = null,
        $offering_listing_id = null,
        $requested_listing_id = null,
        $status = null,
        $created_at = null
    ) {
        $this->swap_request_id = $swap_request_id;
        $this->from_user_id = $from_user_id;
        $this->to_user_id = $to_user_id;
        $this->offering_listing_id = $offering_listing_id;
        $this->requested_listing_id = $requested_listing_id;
        $this->status = $status;
        $this->created_at = $created_at;
    }

    public function getSwapRequestId() { return $this->swap_request_id; }
    public function setSwapRequestId($swap_request_id) { $this->swap_request_id = $swap_request_id; return $this; }

    public function getFromUserId() { return $this->from_user_id; }
    public function setFromUserId($from_user_id) { $this->from_user_id = $from_user_id; return $this; }

    public function getToUserId() { return $this->to_user_id; }
    public function setToUserId($to_user_id) { $this->to_user_id = $to_user_id; return $this; }

    public function getOfferingListingId() { return $this->offering_listing_id; }
    public function setOfferingListingId($offering_listing_id) { $this->offering_listing_id = $offering_listing_id; return $this; }

    public function getRequestedListingId() { return $this->requested_listing_id; }
    public function setRequestedListingId($requested_listing_id) { $this->requested_listing_id = $requested_listing_id; return $this; }

    public function getStatus() { return $this->status; }
    public function setStatus($status) { $this->status = $status; return $this; }

    public function getCreatedAt() { return $this->created_at; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; return $this; }
}
