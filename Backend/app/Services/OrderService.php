<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;

require_once __DIR__ . '/../../database/connection.php';
require_once __DIR__ . '/../Models/Order.php';
require_once __DIR__ . '/../Models/OrderItem.php';

class OrderService
{
    private $conn;

    public function __construct()
    {
        $this->conn = db();
    }

    public function getOrders()
    {
        $sql = "SELECT * FROM Orders";
        $result = $this->conn->query($sql);

        $orders = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
        }
        return $orders;
    }

    public function createOrder($data)
    {
        $buyer_id = $data['buyer_id'] ?? null;
        $total_amount = $data['total_amount'] ?? 0.00;
        $status = $data['status'] ?? 'pending';
        $shipping_street = $data['shipping_street'] ?? null;
        $shipping_city = $data['shipping_city'] ?? null;

        if (!$buyer_id) {
            return ["error" => "buyer_id is required"];
        }

        $stmt = $this->conn->prepare("INSERT INTO Orders (buyer_id, total_amount, status, shipping_street, shipping_city) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("idsss", $buyer_id, $total_amount, $status, $shipping_street, $shipping_city);

        if ($stmt->execute()) {
            $insert_id = $stmt->insert_id;
            $stmt->close();
            return ["success" => true, "order_id" => $insert_id];
        } else {
            $error = $stmt->error;
            $stmt->close();
            return ["error" => $error];
        }
    }

    public function getOrderItems($orderId)
    {
        $sql = "SELECT * FROM OrderItem WHERE order_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();

        $items = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
        }
        $stmt->close();
        return $items;
    }

    public function createOrderItem($data)
    {
        $order_id = $data['order_id'] ?? null;
        $listing_id = $data['listing_id'] ?? null;
        $price = $data['price'] ?? 0.00;
        $quantity = $data['quantity'] ?? 1;

        if (!$order_id || !$listing_id) {
            return ["error" => "order_id and listing_id are required"];
        }

        // Note: subtotal is generally GENERATED ALWAYS AS (price * quantity) in DB schema
        $stmt = $this->conn->prepare("INSERT INTO OrderItem (order_id, listing_id, price, quantity) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iidi", $order_id, $listing_id, $price, $quantity);

        if ($stmt->execute()) {
            $insert_id = $stmt->insert_id;
            $stmt->close();
            return ["success" => true, "order_item_id" => $insert_id];
        } else {
            $error = $stmt->error;
            $stmt->close();
            return ["error" => $error];
        }
    }
}
