<?php

namespace app\Models;

class Payment
{
    private $payment_id;
    private $order_id;
    private $amount;
    private $payment_method;
    private $status;
    private $transaction_id;
    private $created_at;

    public function __construct(
        $payment_id = null,
        $order_id = null,
        $amount = null,
        $payment_method = null,
        $status = null,
        $transaction_id = null,
        $created_at = null
    ) {
        $this->payment_id = $payment_id;
        $this->order_id = $order_id;
        $this->amount = $amount;
        $this->payment_method = $payment_method;
        $this->status = $status;
        $this->transaction_id = $transaction_id;
        $this->created_at = $created_at;
    }

    public function getPaymentId() { return $this->payment_id; }
    public function setPaymentId($payment_id) { $this->payment_id = $payment_id; return $this; }

    public function getOrderId() { return $this->order_id; }
    public function setOrderId($order_id) { $this->order_id = $order_id; return $this; }

    public function getAmount() { return $this->amount; }
    public function setAmount($amount) { $this->amount = $amount; return $this; }

    public function getPaymentMethod() { return $this->payment_method; }
    public function setPaymentMethod($payment_method) { $this->payment_method = $payment_method; return $this; }

    public function getStatus() { return $this->status; }
    public function setStatus($status) { $this->status = $status; return $this; }

    public function getTransactionId() { return $this->transaction_id; }
    public function setTransactionId($transaction_id) { $this->transaction_id = $transaction_id; return $this; }

    public function getCreatedAt() { return $this->created_at; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; return $this; }
}
