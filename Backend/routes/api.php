<?php

use app\Http\Controllers\ListingController;
use app\Http\Controllers\OrderController;
use app\Http\Controllers\AdminController;
use app\Http\Controllers\ReportController;
use app\Http\Controllers\SwapController;

require_once __DIR__ . '/../app/Http/Controllers/ListingController.php';
require_once __DIR__ . '/../app/Http/Controllers/OrderController.php';
require_once __DIR__ . '/../app/Http/Controllers/AdminController.php';
require_once __DIR__ . '/../app/Http/Controllers/ReportController.php';
require_once __DIR__ . '/../app/Http/Controllers/SwapController.php';

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$basePath = dirname($_SERVER['SCRIPT_NAME']);
// Remove basePath from request URI only when necessary (supports web root deploy)
if ($basePath && $basePath !== '/' && strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}

// Remove /index.php if present
$requestUri = str_replace('/index.php', '', $requestUri);

$method = $_SERVER['REQUEST_METHOD'];


// =========================
// LISTINGS endpoints
// =========================
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


// =========================
// ORDERS endpoints
// =========================

// payment
if (preg_match('#^/api/orders/pay/?$#', $requestUri)) {
    $controller = new OrderController();

    if ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        $result = $controller->processPayment($data['order_id'] ?? null);
        header('Content-Type: application/json');
        if (isset($result['error'])) {
            http_response_code(400);
        }
        echo json_encode($result);
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method Not Allowed"]);
    }
    exit;
}

// cancel
if (preg_match('#^/api/orders/cancel/?$#', $requestUri)) {
    $controller = new OrderController();

    if ($method === 'POST') {
        $controller->cancel();
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method Not Allowed"]);
    }
    exit;
}

// shipping label
if (preg_match('#^/api/orders/ship/?$#', $requestUri)) {
    $controller = new OrderController();

    if ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        $orderId = $data['order_id'] ?? null;
        if (!$orderId) {
            http_response_code(400);
            echo json_encode(["error" => "order_id is required"]);
            exit;
        }
        echo json_encode(
            $controller->generateShipping($orderId)
        );
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method Not Allowed"]);
    }
    exit;
}

// release payment
if (preg_match('#^/api/orders/release/?$#', $requestUri)) {
    $controller = new OrderController();

    if ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        $orderId = $data['order_id'] ?? null;
        if (!$orderId) {
            http_response_code(400);
            echo json_encode(["error" => "order_id is required"]);
            exit;
        }
        echo json_encode(
            $controller->releasePayment($orderId)
        );
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method Not Allowed"]);
    }
    exit;
}

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


// =========================
// ORDER ITEMS endpoints
// =========================
if (preg_match('#^/api/order-items/?$#', $requestUri)) {
    $controller = new OrderController();

    if ($method === 'GET') {
        $controller->getItems();
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method Not Allowed"]);
    }
    exit;
}

if (preg_match('#^/api/payments/?$#', $requestUri)) {
    $controller = new OrderController();

    if ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        $orderId = $data['order_id'] ?? null;
        $result = $controller->processPayment($orderId);
        header('Content-Type: application/json');
        if (isset($result['error'])) {
            http_response_code(400);
        }
        echo json_encode($result);
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method Not Allowed"]);
    }
    exit;
}


// =========================
// ADMIN endpoints
// =========================

if (preg_match('#^/api/admin/users/?$#', $requestUri)) {
    $controller = new AdminController();

    if ($method === 'GET') {
        $controller->getUsers();
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method Not Allowed"]);
    }
    exit;
}

if (preg_match('#^/api/admin/user/?$#', $requestUri)) {
    $controller = new AdminController();

    if ($method === 'GET') {
        $controller->getUser();
    } elseif ($method === 'DELETE') {
        $controller->deleteUser();
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method Not Allowed"]);
    }
    exit;
}

if (preg_match('#^/api/admin/reports/?$#', $requestUri)) {
    $controller = new AdminController();

    if ($method === 'GET') {
        $controller->getReports();
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method Not Allowed"]);
    }
    exit;
}

if (preg_match('#^/api/admin/report/?$#', $requestUri)) {
    $controller = new AdminController();

    if ($method === 'PUT' || $method === 'POST') {
        $controller->updateReport();
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method Not Allowed"]);
    }
    exit;
}


// =========================
// REPORTS endpoints
// =========================

if (preg_match('#^/api/reports/?$#', $requestUri)) {
    $controller = new ReportController();

    if ($method === 'GET') {
        if (isset($_GET['report_id'])) {
            $controller->show();
        } else {
            $controller->index();
        }
    } elseif ($method === 'POST') {
        $controller->store();
    } elseif ($method === 'PUT') {
        $controller->update();
    } elseif ($method === 'DELETE') {
        $controller->destroy();
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