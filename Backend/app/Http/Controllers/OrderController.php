<?php

namespace app\Http\Controllers;

use app\Services\OrderService;

require_once __DIR__ . '/../../Services/OrderService.php';
require_once __DIR__ . '/../Middleware/RoleMiddleware.php';
class OrderController
{
    private $orderService;

    public function __construct()
    {
        $this->orderService = new OrderService();
    }

    /*
    =========================================
    GET /api/orders
    =========================================
    */
    public function index()
    {
        header('Content-Type: application/json');

        $order_id = $_GET['order_id'] ?? null;

        if ($order_id) {
            $order = $this->orderService->getOrderById($order_id);

            if ($order) {
                echo json_encode($order);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Order not found"]);
            }
        } else {
            echo json_encode($this->orderService->getOrders());
        }
    }

    /*
    =========================================
    POST /api/orders (PLACE ORDER - NEW MAIN FLOW)
    =========================================
    */
    public function store()
    {
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);
       
        
        $buyerId = $data['buyer_id'] ?? null;
        $items = $data['items'] ?? [];
        
        $check = RoleMiddleware::checkNotBanned($buyerId);

            if (!$check['allowed']) {
                http_response_code(403);
                echo json_encode($check);
                return;
            }
        

        $shippingStreet = $data['shipping_street'] ?? null;
        $shippingCity = $data['shipping_city'] ?? null;

        $result = $this->orderService->placeOrder(
            $buyerId,
            $items,
            $shippingStreet,
            $shippingCity
        );

        if (isset($result['error'])) {
            http_response_code(400);
        } else {
            http_response_code(201);
        }

        echo json_encode($result);
    }

    /*
    =========================================
    GET /api/order-items?order_id=X
    =========================================
    */
    public function getItems()
    {
        header('Content-Type: application/json');

        $order_id = $_GET['order_id'] ?? null;

        if (!$order_id) {
            http_response_code(400);
            echo json_encode(["error" => "order_id is required"]);
            return;
        }

        echo json_encode(
            $this->orderService->getOrderItems($order_id)
        );
    }

    public function processPayment($orderId)
    {
        return $this->orderService->processEscrowPayment($orderId);
    }

    /*
    =========================================
    POST /api/orders/cancel
    =========================================
    */
    public function cancel()
    {
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);
        $orderId = $data['order_id'] ?? null;

        if (!$orderId) {
            http_response_code(400);
            echo json_encode(["error" => "order_id is required"]);
            return;
        }

        $result = $this->orderService->cancelOrder($orderId);

        echo json_encode($result);
    }
    public function generateShipping($orderId)
    {
        return $this->orderService->generateShippingLabel($orderId);
    }

    public function releasePayment($orderId)
    {
        return $this->orderService->releasePayment($orderId);
    }
}