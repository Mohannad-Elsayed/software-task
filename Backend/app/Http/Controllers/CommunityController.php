<?php

require_once __DIR__ . "/../../Services/CommunityService.php";

class CommunityController
{
    private $communityService;

    public function __construct()
    {
        $this->communityService = new CommunityService();
    }

    public function getReviews()
    {
        header("Content-Type: application/json");

        $reviews = $this->communityService->getReviews();

        echo json_encode([
            "success" => true,
            "reviews" => $reviews
        ]);
    }

    public function addReview()
    {
        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"), true);

        $success = $this->communityService->addReview(
            $data["user_id"],
            $data["rating"],
            $data["comment"] ?? ""
        );

        echo json_encode(["success" => $success]);
    }

    public function addComment()
    {
        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"), true);

        $success = $this->communityService->addComment(
            $data["user_id"],
            $data["listing_id"],
            $data["content"]
        );

        echo json_encode(["success" => $success]);
    }

    public function editComment($commentId)
    {
        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"), true);

        $success = $this->communityService->editComment(
            $commentId,
            $data["user_id"],
            $data["content"]
        );

        echo json_encode(["success" => $success]);
    }

    public function reportContent()
    {
        header("Content-Type: application/json");

        echo json_encode([
            "success" => true,
            "message" => "Content reported successfully"
        ]);
    }

    public function notifyUser()
    {
        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"), true);

        $success = $this->communityService->notifyUser(
            $data["user_id"],
            $data["type"],
            $data["message"]
        );

        echo json_encode(["success" => $success]);
    }
    public function notifyAllUsers()
    {
        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"), true);

        $success = $this->communityService->notifyAllUsers(
            $data["type"],
            $data["message"],
            $data["exclude_user_id"] ?? null
        );

        echo json_encode(["success" => $success]);
    }

    public function getNotifications()
    {
        header("Content-Type: application/json");

        $userId = $_GET["user_id"] ?? null;

        if (!$userId) {
            echo json_encode([
                "success" => false,
                "message" => "user_id is required"
            ]);
            return;
        }

        echo json_encode([
            "success" => true,
            "notifications" => $this->communityService->getNotifications($userId)
        ]);
    }

    public function markNotificationAsRead($notificationId)
    {
        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"), true);

        $success = $this->communityService->markNotificationAsRead(
            $notificationId,
            $data["user_id"]
        );

        echo json_encode(["success" => $success]);
    }
}