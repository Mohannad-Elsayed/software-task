<?php


use App\Http\Controllers\ListingController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ReportController;

require_once __DIR__ . '/../app/Http/Controllers/ListingController.php';
require_once __DIR__ . '/../app/Http/Controllers/OrderController.php';
require_once __DIR__ . '/../app/Http/Controllers/AdminController.php';
require_once __DIR__ . '/../app/Http/Controllers/ReportController.php';

// Basic router wrapper
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
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
        $controller->storeItem();
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method Not Allowed"]);
    }
    exit;
}


// =========================
// ADMIN endpoints
// =========================

// GET /api/admin/users
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

// GET /api/admin/user?user_id=1
// DELETE /api/admin/user?user_id=1
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

// GET /api/admin/reports
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

// PUT /api/admin/report?report_id=1
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

// GET /api/reports
// POST /api/reports
// GET /api/reports?report_id=1
// PUT /api/reports?report_id=1
// DELETE /api/reports?report_id=1
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
