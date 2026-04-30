<?php
class Payment
{
    private $payment_id;
    private $order_id;
    private $amount;
    private $status; // pending, held, completed, refunded
    private $payment_method;
    private $created_at;

    public function __construct($order_id = null, $amount = null, $payment_method = null)
    {
        $this->order_id = $order_id;
        $this->amount = $amount;
        $this->payment_method = $payment_method;
        $this->status = "pending";
        $this->created_at = date("Y-m-d H:i:s");
    }

    public function processPayment()
    {
        $this->status = "held"; // moved to escrow
    }

    public function markCompleted()
    {
        $this->status = "completed";
    }

    public function refund()
    {
        $this->status = "refunded";
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getOrderId()
    {
        return $this->order_id;
    }
}