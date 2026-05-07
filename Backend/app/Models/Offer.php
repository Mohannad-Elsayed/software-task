<?php

namespace app\Models;

class Offer
{
    private $offer_id;
    private $from_user_id;
    private $to_user_id;
    private $listing_id;
    private $message;
    private $status;
    private $created_at;

    public function __construct(
        $offer_id = null,
        $from_user_id = null,
        $to_user_id = null,
        $listing_id = null,
        $message = null,
        $status = null,
        $created_at = null
    ) {
        $this->offer_id = $offer_id;
        $this->from_user_id = $from_user_id;
        $this->to_user_id = $to_user_id;
        $this->listing_id = $listing_id;
        $this->message = $message;
        $this->status = $status;
        $this->created_at = $created_at;
    }

    public function getOfferId() { return $this->offer_id; }
    public function setOfferId($offer_id) { $this->offer_id = $offer_id; return $this; }

    public function getFromUserId() { return $this->from_user_id; }
    public function setFromUserId($from_user_id) { $this->from_user_id = $from_user_id; return $this; }

    public function getToUserId() { return $this->to_user_id; }
    public function setToUserId($to_user_id) { $this->to_user_id = $to_user_id; return $this; }

    public function getListingId() { return $this->listing_id; }
    public function setListingId($listing_id) { $this->listing_id = $listing_id; return $this; }

    public function getMessage() { return $this->message; }
    public function setMessage($message) { $this->message = $message; return $this; }

    public function getStatus() { return $this->status; }
    public function setStatus($status) { $this->status = $status; return $this; }

    public function getCreatedAt() { return $this->created_at; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; return $this; }
}
