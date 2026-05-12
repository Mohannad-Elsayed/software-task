<?php

// Require global dependencies that aren't classes
require_once __DIR__ . '/database/connection.php';

// Basic CORS headers to allow the frontend to communicate with the backend
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Basic router
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($requestUri === '/api/test') {
    echo json_encode(["status" => "success", "message" => "Backend is connected!"]);
    exit();
}

// Include API Routes
require_once __DIR__ . '/routes/api.php';

// Fallback if no routes match
http_response_code(404);
echo json_encode(["error" => "Endpoint not found"]);
