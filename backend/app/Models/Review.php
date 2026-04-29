<?php

namespace app\Models;

class Review
{
    private $review_id;
    private $user_id;
    private $listing_id;
    private $rating;
    private $comment;

    public function __construct(
        $review_id = null,
        $user_id = null,
        $listing_id = null,
        $rating = null,
        $comment = null
    ) {
        $this->review_id = $review_id;
        $this->user_id = $user_id;
        $this->listing_id = $listing_id;
        $this->rating = $rating;
        $this->comment = $comment;
    }

    public function getReviewId() { return $this->review_id; }
    public function setReviewId($review_id) { $this->review_id = $review_id; return $this; }

    public function getUserId() { return $this->user_id; }
    public function setUserId($user_id) { $this->user_id = $user_id; return $this; }

    public function getListingId() { return $this->listing_id; }
    public function setListingId($listing_id) { $this->listing_id = $listing_id; return $this; }

    public function getRating() { return $this->rating; }
    public function setRating($rating) { $this->rating = $rating; return $this; }

    public function getComment() { return $this->comment; }
    public function setComment($comment) { $this->comment = $comment; return $this; }
}