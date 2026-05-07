<?php

namespace app\Services;

use app\Models\Order;
use app\Models\OrderItem;

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

        $this->conn->begin_transaction();

        try {
            // 1. Create order
            $stmt = $this->conn->prepare("
                INSERT INTO Orders (buyer_id, status, total_amount, shipping_street, shipping_city)
                VALUES (?, 'pending', 0, ?, ?)
            ");
            $stmt->bind_param("iss", $buyerId, $shippingStreet, $shippingCity);

            if (!$stmt->execute()) {
                throw new \Exception("Failed to create order: " . $stmt->error);
            }

            $orderId = $stmt->insert_id;
            $stmt->close();

            $total = 0;

            // 2. Process items
            foreach ($items as $item) {
                $listingId = $item['listing_id'];
                $quantity = $item['quantity'] ?? 1;

                if ($quantity <= 0) {
                    throw new \Exception("Quantity must be strictly greater than zero.");
                }

                // get listing
                $stmt = $this->conn->prepare("
                    SELECT * FROM Listing WHERE listing_id = ? FOR UPDATE
                ");
                $stmt->bind_param("i", $listingId);
                $stmt->execute();

                $listing = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (!$listing) {
                    throw new \Exception("Listing not found: $listingId");
                }

                if ($listing['status'] !== 'active') {
                    throw new \Exception("Listing is no longer available: $listingId");
                }

                $price = $listing['price'];
                $subtotal = $price * $quantity;

                // insert order item
                $stmt = $this->conn->prepare("
                    INSERT INTO OrderItem (order_id, listing_id, price, quantity)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->bind_param("iidi", $orderId, $listingId, $price, $quantity);
                if (!$stmt->execute()) {
                    throw new \Exception("Failed to add item to order: " . $stmt->error);
                }
                $stmt->close();

                // lock listing (important business rule)
                $stmt = $this->conn->prepare("
                    UPDATE Listing 
                    SET status = 'locked'
                    WHERE listing_id = ?
                ");
                $stmt->bind_param("i", $listingId);
                if (!$stmt->execute()) {
                    throw new \Exception("Failed to lock listing: " . $stmt->error);
                }
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
            if (!$stmt->execute()) {
                throw new \Exception("Failed to update order total: " . $stmt->error);
            }
            $stmt->close();

            $this->conn->commit();

            return [
                "success" => true,
                "order_id" => $orderId,
                "total_amount" => $total
            ];
        } catch (\Exception $e) {
            $this->conn->rollback();
            return ["error" => $e->getMessage()];
        }
    }

    /*
    =========================================
    CANCEL ORDER
    =========================================
    */
    public function cancelOrder($orderId)
    {
        $this->conn->begin_transaction();
        try {
            $stmt = $this->conn->prepare("SELECT status FROM Orders WHERE order_id = ? FOR UPDATE");
            $stmt->bind_param("i", $orderId);
            $stmt->execute();
            $order = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$order) {
                $this->conn->rollback();
                return ["error" => "Order not found"];
            }

            if ($order['status'] !== 'pending') {
                $this->conn->rollback();
                return ["error" => "Only pending orders can be cancelled"];
            }

            // 1. get items
            $items = $this->getOrderItems($orderId);

            // 2. unlock listings
            foreach ($items as $item) {
                $stmt = $this->conn->prepare("
                    UPDATE Listing 
                    SET status = 'active'
                    WHERE listing_id = ? AND status = 'locked'
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

            $this->conn->commit();
            return ["success" => true];
        } catch (\Exception $e) {
            $this->conn->rollback();
            return ["error" => $e->getMessage()];
        }
    }
    public function processEscrowPayment($orderId, $paymentMethod = 'mock')
    {
        if (!$orderId) {
            return ["error" => "order_id is required"];
        }

        $this->conn->begin_transaction();
        try {
            // 1. Get order
            $stmt = $this->conn->prepare("
                SELECT * FROM Orders WHERE order_id = ? FOR UPDATE
            ");
            $stmt->bind_param("i", $orderId);
            $stmt->execute();

            $order = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$order) {
                $this->conn->rollback();
                return ["error" => "Order not found"];
            }

            if ($order['status'] !== 'pending') {
                $this->conn->rollback();
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
            $stmt->close(); // Close early

            // 5. Mark listings as SOLD
            while ($row = $result->fetch_assoc()) {

                $listingId = $row['listing_id'];

                $stmt2 = $this->conn->prepare("
                    UPDATE Listing 
                    SET status = 'sold'
                    WHERE listing_id = ?
                ");
                $stmt2->bind_param("i", $listingId);
                $stmt2->execute();
                $stmt2->close();
            }

            $this->conn->commit();

            return [
                "success" => true,
                "message" => "Payment processed successfully",
                "order_id" => $orderId,
                "amount" => $amount
            ];
        } catch (\Exception $e) {
            $this->conn->rollback();
            return ["error" => $e->getMessage()];
        }
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

            // NOTE: Shipping columns (shipping_status, tracking_number, shipping_label) do not exist
            // in the current Orders table schema. Return tracking info in response for now.
            // If/when the schema is updated to include shipping fields, restore DB update here.
            // $stmt = $this->conn->prepare("
            // UPDATE Orders 
            // SET shipping_status = 'shipped',
            //     tracking_number = ?,
            //     shipping_label = ?
            // WHERE order_id = ?
            // ");
            // $stmt->bind_param("ssi", $tracking, $label, $orderId);
            // $stmt->execute();
            // $stmt->close();

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