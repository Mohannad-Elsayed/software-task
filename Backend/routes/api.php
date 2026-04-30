<?php

use App\Http\Controllers\ListingController;
use App\Http\Controllers\OrderController;

require_once __DIR__ . '/../app/Http/Controllers/ListingController.php';
require_once __DIR__ . '/../app/Http/Controllers/OrderController.php';

// Basic router wrapper
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Listings endpoints
if (preg_match('#^/api/listings/?$#', $requestUri)) {
    $controller = new ListingController();
    if ($method === 'GET') {
        $controller->index();
    } elseif ($method === 'POST') {
        $controller->store();
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method Not Allowed"]);
    }
    exit;
}

if (preg_match('#^/api/listings/(\d+)$#', $requestUri, $matches)) {
    $controller = new ListingController();
    $id = $matches[1];
    if ($method === 'GET') {
        $controller->show($id);
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method Not Allowed"]);
    }
    exit;
}

// Orders endpoints
if (preg_match('#^/api/orders/?$#', $requestUri)) {
    $controller = new OrderController();
    if ($method === 'GET') {
        $controller->index();
    } elseif ($method === 'POST') {
        $controller->store();
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method Not Allowed"]);
    }
    exit;
}

// Order Items endpoints
if (preg_match('#^/api/order-items/?$#', $requestUri)) {
    $controller = new OrderController();
    if ($method === 'GET') {
        $controller->getItems();
    } elseif ($method === 'POST') {
        $controller->storeItem();
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method Not Allowed"]);
    }
    exit;
}
    }
    exit;
}