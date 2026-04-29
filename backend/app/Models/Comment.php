<?php

namespace app\Models;

class Comment
{
    private $comment_id;
    private $user_id;
    private $listing_id;
    private $content;
    private $created_at;

    public function __construct(
        $comment_id = null,
        $user_id = null,
        $listing_id = null,
        $content = null,
        $created_at = null
    ) {
        $this->comment_id = $comment_id;
        $this->user_id = $user_id;
        $this->listing_id = $listing_id;
        $this->content = $content;
        $this->created_at = $created_at;
    }

    public function getCommentId() { return $this->comment_id; }
    public function setCommentId($comment_id) { $this->comment_id = $comment_id; return $this; }

    public function getUserId() { return $this->user_id; }
    public function setUserId($user_id) { $this->user_id = $user_id; return $this; }

    public function getListingId() { return $this->listing_id; }
    public function setListingId($listing_id) { $this->listing_id = $listing_id; return $this; }

    public function getContent() { return $this->content; }
    public function setContent($content) { $this->content = $content; return $this; }

    public function getCreatedAt() { return $this->created_at; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; return $this; }
}