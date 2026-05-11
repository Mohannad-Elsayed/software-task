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
}