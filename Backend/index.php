<?php
require_once __DIR__ . '/routes/api.php';
// Autoloader to avoid manual require_once everywhere
spl_autoload_register(function ($class) {
    $prefix = 'app\\';
    $base_dir = __DIR__ . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Require global dependencies that aren't classes
require_once __DIR__ . '/database/connection.php';

// Basic CORS headers to allow the frontend to communicate with the backend
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

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
