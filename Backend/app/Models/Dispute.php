<?php

namespace app\Models;

class Dispute
{
    private $dispute_id;
    private $order_id;
    private $buyer_id;
    private $seller_id;
    private $reason;
    private $status;
    private $created_at;

    public function __construct(
        $dispute_id = null,
        $order_id = null,
        $buyer_id = null,
        $seller_id = null,
        $reason = null,
        $status = null,
        $created_at = null
    ) {
        $this->dispute_id = $dispute_id;
        $this->order_id = $order_id;
        $this->buyer_id = $buyer_id;
        $this->seller_id = $seller_id;
        $this->reason = $reason;
        $this->status = $status;
        $this->created_at = $created_at;
    }

    public function getDisputeId() { return $this->dispute_id; }
    public function setDisputeId($dispute_id) { $this->dispute_id = $dispute_id; return $this; }

    public function getOrderId() { return $this->order_id; }
    public function setOrderId($order_id) { $this->order_id = $order_id; return $this; }

    public function getBuyerId() { return $this->buyer_id; }
    public function setBuyerId($buyer_id) { $this->buyer_id = $buyer_id; return $this; }

    public function getSellerId() { return $this->seller_id; }
    public function setSellerId($seller_id) { $this->seller_id = $seller_id; return $this; }

    public function getReason() { return $this->reason; }
    public function setReason($reason) { $this->reason = $reason; return $this; }

    public function getStatus() { return $this->status; }
    public function setStatus($status) { $this->status = $status; return $this; }

    public function getCreatedAt() { return $this->created_at; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; return $this; }
}
