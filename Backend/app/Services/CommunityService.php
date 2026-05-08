<?php

require_once __DIR__ . '/../../database/connection.php';

class CommunityService {

    private $conn;

    public function __construct() {
        $this->conn = db();
    }

    public function addReview(
        $userId,
        $listingId,
        $rating,
        $comment
    ) {

        $stmt = $this->conn->prepare("
            INSERT INTO Review
            (user_id, listing_id, rating, comment)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "iiis",
            $userId,
            $listingId,
            $rating,
            $comment
        );

        return $stmt->execute();
    }

    public function addComment(
        $userId,
        $listingId,
        $content
    ) {

        $stmt = $this->conn->prepare("
            INSERT INTO Comment
            (user_id, listing_id, content)
            VALUES (?, ?, ?)
        ");

        $stmt->bind_param(
            "iis",
            $userId,
            $listingId,
            $content
        );

        return $stmt->execute();
    }

    public function editComment(
        $commentId,
        $userId,
        $content
    ) {

        $stmt = $this->conn->prepare("
            UPDATE Comment
            SET content = ?
            WHERE comment_id = ?
            AND user_id = ?
        ");

        $stmt->bind_param(
            "sii",
            $content,
            $commentId,
            $userId
        );

        return $stmt->execute();
    }

    public function notifyUser(
        $userId,
        $type,
        $message
    ) {

        $stmt = $this->conn->prepare("
            INSERT INTO Notification
            (user_id, type, message)
            VALUES (?, ?, ?)
        ");

        $stmt->bind_param(
            "iss",
            $userId,
            $type,
            $message
        );

        return $stmt->execute();
    }
}