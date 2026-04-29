<?php

namespace app\Models;

class Notification
{
    private $notification_id;
    private $user_id;
    private $type;
    private $message;
    private $is_read;
    private $created_at;

    public function __construct(
        $notification_id = null,
        $user_id = null,
        $type = null,
        $message = null,
        $is_read = null,
        $created_at = null
    ) {
        $this->notification_id = $notification_id;
        $this->user_id = $user_id;
        $this->type = $type;
        $this->message = $message;
        $this->is_read = $is_read;
        $this->created_at = $created_at;
    }

    public function getNotificationId() { return $this->notification_id; }
    public function setNotificationId($notification_id) { $this->notification_id = $notification_id; return $this; }

    public function getUserId() { return $this->user_id; }
    public function setUserId($user_id) { $this->user_id = $user_id; return $this;  }

    public function getType() { return $this->type; }
    public function setType($type) { $this->type = $type; return $this;  }

    public function getMessage() { return $this->message; }
    public function setMessage($message) { $this->message = $message; return $this;  }

    public function getIsRead() { return $this->is_read; }
    public function setIsRead($is_read) { $this->is_read = $is_read; return $this; }

    public function getCreatedAt() { return $this->created_at; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; return $this;  }
}