<?php

namespace App\Http\Controllers;

use App\Services\OrderService;

require_once __DIR__ . '/../../Services/OrderService.php';

class OrderController
{
    private $orderService;

    public function __construct()
    {
        $this->orderService = new OrderService();
    }

    // GET /api/orders
    public function index()
    {
        $orders = $this->orderService->getOrders();
        header('Content-Type: application/json');
        echo json_encode($orders);
    }

    // POST /api/orders
    public function store()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $result = $this->orderService->createOrder($data);

        header('Content-Type: application/json');
        if (isset($result['error'])) {
            http_response_code(400);
            echo json_encode($result);
        } else {
            http_response_code(201);
            echo json_encode($result);
        }
    }

    // GET /api/order-items?order_id=xyz
    public function getItems()
    {
        $order_id = $_GET['order_id'] ?? null;
        if (!$order_id) {
            http_response_code(400);
            echo json_encode(["error" => "order_id is required in query parameter"]);
            return;
        }

        $items = $this->orderService->getOrderItems($order_id);
        header('Content-Type: application/json');
        echo json_encode($items);
    }

    // POST /api/order-items
    public function storeItem()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $result = $this->orderService->createOrderItem($data);

        header('Content-Type: application/json');
        if (isset($result['error'])) {
            http_response_code(400);
            echo json_encode($result);
        } else {
            http_response_code(201);
            echo json_encode($result);
        }
    }
}
