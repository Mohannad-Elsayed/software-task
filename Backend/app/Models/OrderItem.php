<?php

namespace App\Models;

class OrderItem
{
    private $order_item_id;
    private $order_id;
    private $listing_id;
    private $price;
    private $quantity;
    private $subtotal;

    public function __construct(
        $order_item_id = null,
        $order_id = null,
        $listing_id = null,
        $price = 0.00,
        $quantity = 1,
        $subtotal = 0.00
    ) {
        $this->order_item_id = $order_item_id;
        $this->order_id = $order_id;
        $this->listing_id = $listing_id;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->subtotal = $subtotal;
    }

    public function getOrderItemId() { return $this->order_item_id; }
    public function setOrderItemId($order_item_id) { $this->order_item_id = $order_item_id; return $this; }

    public function getOrderId() { return $this->order_id; }
    public function setOrderId($order_id) { $this->order_id = $order_id; return $this; }

    public function getListingId() { return $this->listing_id; }
    public function setListingId($listing_id) { $this->listing_id = $listing_id; return $this; }

    public function getPrice() { return $this->price; }
    public function setPrice($price) { $this->price = $price; return $this; }

    public function getQuantity() { return $this->quantity; }
    public function setQuantity($quantity) { $this->quantity = $quantity; return $this; }

    public function getSubtotal() { return $this->subtotal; }
    public function setSubtotal($subtotal) { $this->subtotal = $subtotal; return $this; }
}
