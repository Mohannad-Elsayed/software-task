<?php
require_once __DIR__ . '/connection.php';

$conn = db();

// Clear existing data (optional but good for "fresh" data)
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$tables = ['UserRole', 'EcoImpact', 'User'];
foreach ($tables as $table) {
    $conn->query("TRUNCATE TABLE $table");
}
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

// Create Admin User
$adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
$conn->query("INSERT INTO User (username, email, password) VALUES ('admin', 'admin@example.com', '$adminPassword')");
$adminId = $conn->insert_id;
$conn->query("INSERT INTO UserRole (user_id, role_name) VALUES ($adminId, 'admin')");
$conn->query("INSERT INTO EcoImpact (user_id) VALUES ($adminId)");

// Create Regular User
$userPassword = password_hash('user123', PASSWORD_DEFAULT);
$conn->query("INSERT INTO User (username, email, password) VALUES ('regularuser', 'user@example.com', '$userPassword')");
$userId = $conn->insert_id;
$conn->query("INSERT INTO UserRole (user_id, role_name) VALUES ($userId, 'user')");
$conn->query("INSERT INTO EcoImpact (user_id) VALUES ($userId)");

echo "Data seeded successfully.\n";
?>
