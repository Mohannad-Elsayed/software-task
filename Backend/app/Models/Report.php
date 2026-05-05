<?php

namespace app\Models;

class Report
{
    private $report_id;
    private $reported_by_user_id;
    private $reported_user_id;
    private $reason;
    private $description;
    private $status;
    private $created_at;

    public function __construct(
        $report_id = null,
        $reported_by_user_id = null,
        $reported_user_id = null,
        $reason = null,
        $description = null,
        $status = null,
        $created_at = null
    ) {
        $this->report_id = $report_id;
        $this->reported_by_user_id = $reported_by_user_id;
        $this->reported_user_id = $reported_user_id;
        $this->reason = $reason;
        $this->description = $description;
        $this->status = $status;
        $this->created_at = $created_at;
    }

    public function getReportId() { return $this->report_id; }
    public function setReportId($report_id) { $this->report_id = $report_id; return $this; }

    public function getReportedByUserId() { return $this->reported_by_user_id; }
    public function setReportedByUserId($reported_by_user_id) { $this->reported_by_user_id = $reported_by_user_id; return $this; }

    public function getReportedUserId() { return $this->reported_user_id; }
    public function setReportedUserId($reported_user_id) { $this->reported_user_id = $reported_user_id; return $this; }

    public function getReason() { return $this->reason; }
    public function setReason($reason) { $this->reason = $reason; return $this; }

    public function getDescription() { return $this->description; }
    public function setDescription($description) { $this->description = $description; return $this; }

    public function getStatus() { return $this->status; }
    public function setStatus($status) { $this->status = $status; return $this; }

    public function getCreatedAt() { return $this->created_at; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; return $this; }
}
