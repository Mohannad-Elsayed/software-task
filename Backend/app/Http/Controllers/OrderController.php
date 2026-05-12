<?php

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
    GET /api/orders/pending?user_id=X
    =========================================
    */
    public function getPendingOrders()
    {
        header('Content-Type: application/json');
        $userId = $_GET['user_id'] ?? null;

        if (!$userId) {
            http_response_code(400);
            echo json_encode(["error" => "user_id is required"]);
            return;
        }

        $orders = $this->orderService->getPendingOrdersByUser($userId);
        echo json_encode(["status" => "success", "data" => $orders]);
    }

    /*
    =========================================
    GET /api/orders/buyer?user_id=X
    =========================================
    */
    public function getBuyerOrders()
    {
        header('Content-Type: application/json');
        $userId = $_GET['user_id'] ?? null;

        if (!$userId) {
            http_response_code(400);
            echo json_encode(["error" => "user_id is required"]);
            return;
        }

        try {
            $orders = $this->orderService->getOrdersByBuyer($userId);
            echo json_encode(["status" => "success", "data" => $orders]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
        }
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
        
        // $check = RoleMiddleware::checkNotBanned($buyerId);

        //     if (!$check['allowed']) {
        //         http_response_code(403);
        //         echo json_encode($check);
        //         return;
        //     }
        

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

    /*
    =========================================
    GET /api/orders/cart?order_id=X
    =========================================
    */
    public function getCart()
    {
        header('Content-Type: application/json');
        $orderId = $_GET['order_id'] ?? null;

        if (!$orderId) {
            http_response_code(400);
            echo json_encode(["error" => "order_id is required"]);
            return;
        }

        $data = $this->orderService->getCartData($orderId);
        if (!$data) {
            http_response_code(404);
            echo json_encode(["error" => "Order/Cart not found"]);
            return;
        }

        echo json_encode(["status" => "success", "data" => $data]);
    }

    /*
    =========================================
    DELETE /api/order-items/:id
    =========================================
    */
    public function removeItem($id)
    {
        header('Content-Type: application/json');
        $result = $this->orderService->removeItemFromOrder($id);

        if (isset($result['error'])) {
            http_response_code(400);
        }

        echo json_encode($result);
    }

    /*
    =========================================
    POST /api/orders/payment
    =========================================
    */
    public function processPayment()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $orderId = $data['order_id'] ?? null;

        if (!$orderId) {
            http_response_code(400);
            echo json_encode(["error" => "order_id is required"]);
            return;
        }

        $result = $this->orderService->processEscrowPayment($orderId);
        
        if (isset($result['error'])) {
            http_response_code(400);
        }

        echo json_encode($result);
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