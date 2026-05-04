<?php

namespace App\Models;

class Payment
{
    public $id;
    public $order_id;
    public $amount;
    public $status;
    public $createdAt;
    public $paymentMethod;
    public function getOrder($db)
        {
            $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
            $stmt->execute([$this->order_id]);
            return $stmt->fetch();
        }
        public function __construct($id = null, $amount = null, $status = null, $createdAt = null)
        {
        $this->id = $id;
        $this->amount = $amount;
        $this->status = $status;
        $this->createdAt = $createdAt;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
       return $this->id = $id;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($amount)
    {
       return $this->amount = $amount;
        
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        return $this->status = $status;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt)
    {
        return $this->createdAt = $createdAt;    
    }
}
