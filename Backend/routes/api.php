<?php

use app\Http\Controllers\ListingController;
use app\Http\Controllers\OrderController;
use app\Http\Controllers\SwapController;

require_once __DIR__ . '/../app/Http/Controllers/ListingController.php';
require_once __DIR__ . '/../app/Http/Controllers/OrderController.php';
require_once __DIR__ . '/../app/Http/Controllers/SwapController.php';

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

if (preg_match('#^/api/listings/(\d+)/condition$#', $requestUri, $matches)) {
    $controller = new ListingController();
    $id = $matches[1];
    if ($method === 'POST') {
        $controller->assessCondition($id);
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method Not Allowed"]);
    }
    exit;
}

if (preg_match('#^/api/listings/(\d+)/care$#', $requestUri, $matches)) {
    $controller = new ListingController();
    $id = $matches[1];
    if ($method === 'POST' || $method === 'GET') {
        $controller->generateCareInstructions($id);
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method Not Allowed"]);
    }
    exit;
}

if (preg_match('#^/api/listings/(\d+)/upcycle$#', $requestUri, $matches)) {
    $controller = new ListingController();
    $id = $matches[1];
    if ($method === 'POST') {
        $controller->logUpcycleTransformation($id);
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
    } elseif ($method === 'PUT') {
        $controller->editListing($id);
    } elseif ($method === 'DELETE') {
        $controller->deleteListing($id);
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

// Swap Requests endpoints
if (preg_match('#^/api/swap-requests/?$#', $requestUri)) {
    $controller = new SwapController();
    if ($method === 'POST') {
        $controller->sendSwapRequest();
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method Not Allowed"]);
    }
    exit;
}

if (preg_match('#^/api/swap-requests/(\d+)/accept$#', $requestUri, $matches)) {
    $controller = new SwapController();
    $requestId = $matches[1];
    if ($method === 'POST') {
        $controller->acceptSwapRequest($requestId);
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method Not Allowed"]);
    }
    exit;
}

if (preg_match('#^/api/swap-requests/(\d+)/reject$#', $requestUri, $matches)) {
    $controller = new SwapController();
    $requestId = $matches[1];
    if ($method === 'POST') {
        $controller->rejectSwapRequest($requestId);
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method Not Allowed"]);
    }
    exit;
}

if (preg_match('#^/api/swap-requests/(\d+)/offers$#', $requestUri, $matches)) {
    $controller = new SwapController();
    $requestId = $matches[1];
    if ($method === 'POST') {
        $controller->makeOffer($requestId);
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method Not Allowed"]);
    }
    exit;
}

if (preg_match('#^/api/swap-requests/(\d+)/counter-offer$#', $requestUri, $matches)) {
    $controller = new SwapController();
    $requestId = $matches[1];
    if ($method === 'POST') {
        $controller->counterOffer($requestId);
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method Not Allowed"]);
    }
    exit;
}

if (preg_match('#^/api/swap-requests/(\d+)/balance$#', $requestUri, $matches)) {
    $controller = new SwapController();
    $requestId = $matches[1];
    if ($method === 'GET') {
        $controller->balanceSwapValue($requestId);
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method Not Allowed"]);
    }
    exit;
}

if (preg_match('#^/api/swap-requests/(\d+)/lock$#', $requestUri, $matches)) {
    $controller = new SwapController();
    $requestId = $matches[1];
    if ($method === 'POST') {
        $controller->lockSwapAgreement($requestId);
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method Not Allowed"]);
    }
    exit;
}

if (preg_match('#^/api/swap-requests/(\d+)/expire$#', $requestUri, $matches)) {
    $controller = new SwapController();
    $requestId = $matches[1];
    if ($method === 'POST') {
        $controller->expirePendingSwap($requestId);
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method Not Allowed"]);
    }
    exit;
}