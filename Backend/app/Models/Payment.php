<?php

namespace App\Models;

class Payment
{
    private $id;
    private $amount;
    private $currency;
    private $status;
    private $createdAt;

    public function __construct($id = null, $amount = null, $currency = null, $status = null, $createdAt = null)
    {
        $this->id = $id;
        $this->amount = $amount;
        $this->currency = $currency;
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

    public function getCurrency()
    {
        return $this->currency;
    }

    public function setCurrency($currency)
    {
       return $this->currency = $currency;
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
