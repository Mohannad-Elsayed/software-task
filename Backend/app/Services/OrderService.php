<?php

namespace App\Services;

require_once __DIR__ . '/../../database/connection.php';

class OrderService
{
    private $conn;

    public function __construct()
    {
        $this->conn = db();
    }

    /*
    =========================================
    GET ALL ORDERS
    =========================================
    */
    public function getOrders()
    {
        $result = $this->conn->query("SELECT * FROM Orders");

        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }

        return $orders;
    }

    /*
    =========================================
    GET ORDER BY ID
    =========================================
    */
    public function getOrderById($orderId)
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM Orders WHERE order_id = ?
        ");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    /*
    =========================================
    GET ORDER ITEMS
    =========================================
    */
    public function getOrderItems($orderId)
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM OrderItem WHERE order_id = ?
        ");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();

        $result = $stmt->get_result();

        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }

        return $items;
    }

    /*
    =========================================
    PLACE ORDER (MULTI-LISTING CART)
    =========================================
    */
    public function placeOrder($buyerId, $items, $shippingStreet = null, $shippingCity = null)
    {
        if (!$buyerId || empty($items)) {
            return ["error" => "buyer_id and items are required"];
        }

        // 1. Create order
        $stmt = $this->conn->prepare("
            INSERT INTO Orders (buyer_id, status, total_amount, shipping_street, shipping_city)
            VALUES (?, 'pending', 0, ?, ?)
        ");
        $stmt->bind_param("iss", $buyerId, $shippingStreet, $shippingCity);

        if (!$stmt->execute()) {
            return ["error" => $stmt->error];
        }

        $orderId = $stmt->insert_id;
        $stmt->close();

        $total = 0;

        // 2. Process items
        foreach ($items as $item) {

            $listingId = $item['listing_id'];
            $quantity = $item['quantity'] ?? 1;

            // get listing
            $stmt = $this->conn->prepare("
                SELECT * FROM Listing WHERE listing_id = ?
            ");
            $stmt->bind_param("i", $listingId);
            $stmt->execute();

            $listing = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$listing) {
                return ["error" => "Listing not found: $listingId"];
            }

            $price = $listing['price'];
            $subtotal = $price * $quantity;

            // insert order item
            $stmt = $this->conn->prepare("
                INSERT INTO OrderItem (order_id, listing_id, price, quantity)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("iidi", $orderId, $listingId, $price, $quantity);
            $stmt->execute();
            $stmt->close();

            // lock listing (important business rule)
            $stmt = $this->conn->prepare("
                UPDATE Listing 
                SET status = 'locked'
                WHERE listing_id = ?
            ");
            $stmt->bind_param("i", $listingId);
            $stmt->execute();
            $stmt->close();

            $total += $subtotal;
        }

        $total = $this->applyBundleDiscount($total);

        // 3. update total_amount
        $stmt = $this->conn->prepare("
            UPDATE Orders 
            SET total_amount = ?
            WHERE order_id = ?
        ");
        $stmt->bind_param("di", $total, $orderId);
        $stmt->execute();
        $stmt->close();

        return [
            "success" => true,
            "order_id" => $orderId,
            "total_amount" => $total
        ];
    }

    /*
    =========================================
    CANCEL ORDER
    =========================================
    */
    public function cancelOrder($orderId)
    {
        // 1. get items
        $items = $this->getOrderItems($orderId);

        // 2. unlock listings
        foreach ($items as $item) {
            $stmt = $this->conn->prepare("
                UPDATE Listing 
                SET status = 'available'
                WHERE listing_id = ?
            ");
            $stmt->bind_param("i", $item['listing_id']);
            $stmt->execute();
            $stmt->close();
        }

        // 3. update order status
        $stmt = $this->conn->prepare("
            UPDATE Orders 
            SET status = 'cancelled'
            WHERE order_id = ?
        ");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $stmt->close();

        return ["success" => true];
    }
    public function processEscrowPayment($orderId, $paymentMethod = 'mock')
    {
        if (!$orderId) {
            return ["error" => "order_id is required"];
        }

        // 1. Get order
        $stmt = $this->conn->prepare("
        SELECT * FROM Orders WHERE order_id = ?
    ");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();

        $order = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$order) {
            return ["error" => "Order not found"];
        }

        if ($order['status'] !== 'pending') {
            return ["error" => "Order is not pending"];
        }

        $amount = $order['total_amount'];

        // 2. Create Payment record
        $stmt = $this->conn->prepare("
        INSERT INTO Payment (order_id, amount, status, payment_method)
        VALUES (?, ?, 'successful', ?)
    ");
        $stmt->bind_param("ids", $orderId, $amount, $paymentMethod);
        $stmt->execute();
        $stmt->close();

        // 3. Update order → PAID
        $stmt = $this->conn->prepare("
        UPDATE Orders SET status = 'paid'
        WHERE order_id = ?
    ");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $stmt->close();

        // 4. Get order items
        $stmt = $this->conn->prepare("
        SELECT listing_id FROM OrderItem WHERE order_id = ?
    ");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();

        $result = $stmt->get_result();

        // 5. Mark listings as SOLD
        while ($row = $result->fetch_assoc()) {

            $listingId = $row['listing_id'];

            $stmt2 = $this->conn->prepare("
            UPDATE listing 
            SET status = 'sold'
            WHERE listing_id = ?
        ");
            $stmt2->bind_param("i", $listingId);
            $stmt2->execute();
            $stmt2->close();
        }

        $stmt->close();

        return [
            "success" => true,
            "message" => "Payment processed successfully",
            "order_id" => $orderId,
            "amount" => $amount
        ];
    }
    public function releasePayment($orderId)
    {
        // 1. Check order
        $stmt = $this->conn->prepare("
        SELECT status FROM Orders WHERE order_id = ?
    ");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$order) {
            return ["error" => "Order not found"];
        }

        if ($order['status'] !== 'paid') {
            return ["error" => "Order is not paid yet"];
        }

        // 2. Mark as completed (money released)
        $stmt = $this->conn->prepare("
        UPDATE Orders SET status = 'completed'
        WHERE order_id = ?
    ");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $stmt->close();

        return ["success" => true, "message" => "Payment released"];
    }
    public function generateShippingLabel($orderId)
    {
        $tracking = "TRK-" . rand(100000, 999999);

        $label = "Order #$orderId\nTracking: $tracking\nStatus: Shipped";

        $stmt = $this->conn->prepare("
        UPDATE Orders 
        SET shipping_status = 'shipped',
            tracking_number = ?,
            shipping_label = ?
        WHERE order_id = ?
    ");
        $stmt->bind_param("ssi", $tracking, $label, $orderId);
        $stmt->execute();
        $stmt->close();

        return [
            "success" => true,
            "tracking_number" => $tracking,
            "label" => $label
        ];
    }
    public function applyBundleDiscount($total)
    {
        if ($total >= 200) {
            return $total * 0.9; // 10% discount
        }

        return $total;
    }
    public function convertCurrency($amount, $rate = 50)
    {
        return $amount * $rate;
    }

}