<?php

use App\Http\Controllers\ListingController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ReportController;

require_once __DIR__ . '/../app/Http/Controllers/ListingController.php';
require_once __DIR__ . '/../app/Http/Controllers/OrderController.php';
require_once __DIR__ . '/../app/Http/Controllers/AdminController.php';
require_once __DIR__ . '/../app/Http/Controllers/ReportController.php';

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$basePath = dirname($_SERVER['SCRIPT_NAME']);
$requestUri = substr($requestUri, strlen($basePath));

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
        echo json_encode(
            $controller->generateShipping($data['order_id'])
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
        echo json_encode(
            $controller->releasePayment($data['order_id'])
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
    } elseif ($method === 'POST') {
        $controller->store();
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

//resolve dipute
    if (preg_match('#^/api/admin/dispute/resolve/?$#', $requestUri)) {
    $controller = new AdminController();

    if ($method === 'POST') {
        $controller->resolveDispute();
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
//seller analytics
if (preg_match('#^/api/admin/seller-analytics/?$#', $requestUri)) {
    $controller = new AdminController();

    if ($method === 'GET') {
        $controller->sellerAnalytics();
    }

    exit;
}
//SUSTAINABILITY REPORT
if (preg_match('#^/api/admin/sustainability/?$#', $requestUri)) {
    $controller = new AdminController();

    if ($method === 'GET') {
        $controller->sustainabilityReport();
    }

    exit;
}