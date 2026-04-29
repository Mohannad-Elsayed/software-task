<?php

namespace app\Models;

class User
{
    private $user_id;
    private $name;
    private $email;
    private $password;
    private $trust_score;

    public function __construct(
        $user_id = null,
        $name = null,
        $email = null,
        $password = null,
        $trust_score = null
    ) {
        $this->user_id = $user_id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->trust_score = $trust_score;
    }

    // Getters & Setters

    public function getUserId() { return $this->user_id; }
    public function setUserId($user_id) { $this->user_id = $user_id; return $this; }

    public function getName() { return $this->name; }
    public function setName($name) { $this->name = $name; return $this; }

    public function getEmail() { return $this->email; }
    public function setEmail($email) { $this->email = $email; return $this; }

    public function getPassword() { return $this->password; }
    public function setPassword($password) { $this->password = $password; return $this; }

    public function getTrustScore() { return $this->trust_score; }
    public function setTrustScore($trust_score) { $this->trust_score = $trust_score; return $this; }
}