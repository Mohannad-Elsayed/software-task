<?php

require_once __DIR__ . '/../../database/connection.php';

class CommunityService {

    private $conn;

    public function __construct() {
        $this->conn = db();
    }

    public function addReview($userId, $rating, $comment)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO Review
            (user_id, rating, comment)
            VALUES (?, ?, ?)
        ");

        $stmt->bind_param("iis", $userId, $rating, $comment);

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
    public function getReviews()
    {
        $stmt = $this->conn->prepare("
            SELECT 
                Review.review_id,
                Review.user_id,
                User.username,
                Review.rating,
                Review.comment
            FROM Review
            JOIN User ON Review.user_id = User.user_id
            ORDER BY Review.review_id DESC
        ");

        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }
}