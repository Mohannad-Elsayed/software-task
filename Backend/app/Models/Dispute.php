<?php

class Dispute
{
    private $disputeId;
    private $status;
    private $initiatorId;
    private $commentId;
    private $reason;
    private $listingId;

    public function __construct($disputeId = null, $status = null, $initiatorId = null, $commentId = null, $reason = null, $listingId = null)
    {
        $this->disputeId = $disputeId;
        $this->status = $status;
        $this->initiatorId = $initiatorId;
        $this->commentId = $commentId;
        $this->reason = $reason;
        $this->listingId = $listingId;
    }

    public function getDisputeId()
    {
        return $this->disputeId;
    }

    public function setDisputeId($disputeId)
    {
        $this->disputeId = $disputeId;
    }       
  
    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getInitiatorId()
    {
        return $this->initiatorId;
    }

    public function setInitiatorId($initiatorId)
    {
        $this->initiatorId = $initiatorId;
    }

    public function getCommentId()
    {
        return $this->commentId;
    }

    public function setCommentId($commentId)
    {
        $this->commentId = $commentId;
    }

    public function getReason()
    {
        return $this->reason;
    }

    public function setReason($reason)
    {
        $this->reason = $reason;
    }

    public function getListingId()
    {
        return $this->listingId;
    }

    public function setListingId($listingId)
    {
        $this->listingId = $listingId;
    }
}