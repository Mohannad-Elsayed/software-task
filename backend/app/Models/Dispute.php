<?php

namespace App\Models;

class Dispute
{
    private $dispute_id;
    private $initiator_id;
    private $order_id;
    private $reason;
    private $status;
    private $request_id;


    public function __construct($dispute_id = null, $initiator_id = null, $order_id = null, $reason = null, $status = null, $request_id = null) {
        $this->dispute_id = $dispute_id;
        $this->initiator_id = $initiator_id;
        $this->order_id = $order_id;
        $this->reason = $reason;
        $this->status = $status;
        $this->request_id = $request_id;
    }

    

    // Getters
    public function getDisputeId()
    {
        return $this->dispute_id;
    }

    public function getInitiatorId()
    {
        return $this->initiator_id;
    }

    public function getOrderId()
    {
        return $this->order_id;
    }

    public function getReason()
    {
        return $this->reason;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getRequestId()
    {
        return $this->request_id;
    }


    public function setDisputeId($dispute_id)
    {
        $this->dispute_id = $dispute_id;
    }

    public function setInitiatorId($initiator_id)
    {
        $this->initiator_id = $initiator_id;
    }

    public function setOrderId($order_id)
    {
        $this->order_id = $order_id;
    }

    public function setReason($reason)
    {
        $this->reason = $reason;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function setRequestId($request_id)
    {
        $this->request_id = $request_id;
    }
}