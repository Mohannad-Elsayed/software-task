<?php

class Report
{
    private $reportId;
    private $status;
    private $initiatorId;
    private $commentId;
    private $reason;
    private $listing_id;

    public function __construct($reportId = null , $status = null, $initiatorId = null, $commentId = null, $reason = null, $listing_id = null)
    {
        $this->reportId = $reportId;
        $this->status = $status;
        $this->initiatorId = $initiatorId;
        $this->commentId = $commentId;
        $this->reason = $reason;
        $this->listing_id = $listing_id;
    }

    public function getReportId()
    {
        return $this->reportId;
    }

    public function setReportId($reportId)
    {
        $this->reportId = $reportId;
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
        return $this->listing_id;
    }

    public function setListingId($listing_id)
    {
        $this->listing_id = $listing_id;
    }
}