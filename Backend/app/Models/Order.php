<?php

namespace App\Models;

class Order
{
    private $order_id;
    private $buyer_id;
    private $total_amount;
    private $order_date;
    private $status;
    private $shipping_street;
    private $shipping_city;

    public function __construct(
        $order_id = null,
        $buyer_id = null,
        $total_amount = 0.00,
        $order_date = null,
        $status = 'pending',
        $shipping_street = null,
        $shipping_city = null
    ) {
        $this->order_id = $order_id;
        $this->buyer_id = $buyer_id;
        $this->total_amount = $total_amount;
        $this->order_date = $order_date;
        $this->status = $status;
        $this->shipping_street = $shipping_street;
        $this->shipping_city = $shipping_city;
    }

    public function getOrderId() { return $this->order_id; }
    public function setOrderId($order_id) { $this->order_id = $order_id; return $this; }

    public function getBuyerId() { return $this->buyer_id; }
    public function setBuyerId($buyer_id) { $this->buyer_id = $buyer_id; return $this; }

    public function getTotalAmount() { return $this->total_amount; }
    public function setTotalAmount($total_amount) { $this->total_amount = $total_amount; return $this; }

    public function getOrderDate() { return $this->order_date; }
    public function setOrderDate($order_date) { $this->order_date = $order_date; return $this; }

    public function getStatus() { return $this->status; }
    public function setStatus($status) { $this->status = $status; return $this; }

    public function getShippingStreet() { return $this->shipping_street; }
    public function setShippingStreet($shipping_street) { $this->shipping_street = $shipping_street; return $this; }

    public function getShippingCity() { return $this->shipping_city; }
    public function setShippingCity($shipping_city) { $this->shipping_city = $shipping_city; return $this; }
}
