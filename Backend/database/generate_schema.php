<?php
// Set plain text content type if running in browser, or skip if CLI
if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/plain');
}

$conn = new mysqli("localhost", "root", "", "");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ create + select DB manually
$conn->query("CREATE DATABASE IF NOT EXISTS eco_project");
$conn->select_db("eco_project");

$schema = <<<SQL

-- Drop Before Any Edits

DROP TABLE IF EXISTS Notification;
DROP TABLE IF EXISTS Dispute;
DROP TABLE IF EXISTS Offer;
DROP TABLE IF EXISTS SwapRequest;
DROP TABLE IF EXISTS Payment;
DROP TABLE IF EXISTS OrderItem;
DROP TABLE IF EXISTS Orders;
DROP TABLE IF EXISTS Report;
DROP TABLE IF EXISTS Comment;
DROP TABLE IF EXISTS Review;
DROP TABLE IF EXISTS UpcycleTransformation;
DROP TABLE IF EXISTS Listing;
DROP TABLE IF EXISTS MaterialTaxonomy;
DROP TABLE IF EXISTS EcoImpact;
DROP TABLE IF EXISTS UserRole;
DROP TABLE IF EXISTS User;

-- Core User Tables
CREATE TABLE User (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, 
    trust_score INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE UserRole (
    user_id INT,
    role_name VARCHAR(100),
    PRIMARY KEY (user_id, role_name),
    FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE
);

CREATE TABLE EcoImpact (
    impact_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    co2_saved DECIMAL(10, 2) DEFAULT 0.00,
    waste_reduced DECIMAL(10, 2) DEFAULT 0.00,
    water_saved DECIMAL(10, 2) DEFAULT 0.00,
    eco_points INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE
);

-- Material Taxonomy
CREATE TABLE MaterialTaxonomy (
    material_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    parent_material_id INT NULL,
    FOREIGN KEY (parent_material_id) REFERENCES MaterialTaxonomy(material_id) ON DELETE SET NULL
);

-- Listings & Transformations
CREATE TABLE Listing (
    listing_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    material_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    category VARCHAR(100),
    condition_status VARCHAR(100),
    listing_type VARCHAR(100),
    status VARCHAR(50) DEFAULT 'active',
    FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES MaterialTaxonomy(material_id) ON DELETE SET NULL
);

CREATE TABLE UpcycleTransformation (
    transformation_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    listing_id INT NOT NULL,
    before_image_url VARCHAR(255),
    after_image_url VARCHAR(255),
    steps TEXT,
    materials_used TEXT,
    FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE,
    FOREIGN KEY (listing_id) REFERENCES Listing(listing_id) ON DELETE CASCADE
);

CREATE TABLE Review (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    FOREIGN KEY (listing_id) REFERENCES Listing(listing_id) ON DELETE CASCADE
);

CREATE TABLE Comment (
    comment_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    listing_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE,
    FOREIGN KEY (listing_id) REFERENCES Listing(listing_id) ON DELETE CASCADE
);

CREATE TABLE Report (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    initiator_id INT NOT NULL,
    listing_id INT NULL,
    comment_id INT NULL,
    reason TEXT NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    FOREIGN KEY (initiator_id) REFERENCES User(user_id),
    FOREIGN KEY (listing_id) REFERENCES Listing(listing_id),
    FOREIGN KEY (comment_id) REFERENCES Comment(comment_id)
);

-- Transactions: Orders & Swaps
CREATE TABLE Orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(50) DEFAULT 'pending',
    shipping_street VARCHAR(255),
    shipping_city VARCHAR(100),
    FOREIGN KEY (buyer_id) REFERENCES User(user_id)
);

CREATE TABLE OrderItem (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    listing_id INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    subtotal DECIMAL(10, 2) GENERATED ALWAYS AS (price * quantity), -- Handles calculateSubtotal() at DB level
    FOREIGN KEY (order_id) REFERENCES Orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (listing_id) REFERENCES Listing(listing_id)
);

CREATE TABLE Payment (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) DEFAULT 'unpaid',
    payment_method VARCHAR(50),
    Created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES Orders(order_id) ON DELETE CASCADE
);

CREATE TABLE SwapRequest (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    initiator_id INT NOT NULL,
    partner_id INT NOT NULL,
    requested_listing_id INT NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    FOREIGN KEY (initiator_id) REFERENCES User(user_id),
    FOREIGN KEY (partner_id) REFERENCES User(user_id),
    FOREIGN KEY (requested_listing_id) REFERENCES Listing(listing_id)
);

CREATE TABLE Offer (
    offer_id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    initiator_id INT NOT NULL,
    offer_value VARCHAR(255),
    status VARCHAR(50) DEFAULT 'pending',
    FOREIGN KEY (request_id) REFERENCES SwapRequest(request_id) ON DELETE CASCADE,
    FOREIGN KEY (initiator_id) REFERENCES User(user_id)
);

-- Support & Operations
CREATE TABLE Dispute (
    dispute_id INT AUTO_INCREMENT PRIMARY KEY,
    initiator_id INT NOT NULL,
    order_id INT NULL,
    request_id INT NULL,
    reason TEXT NOT NULL,
    status VARCHAR(50) DEFAULT 'open',
    FOREIGN KEY (initiator_id) REFERENCES User(user_id),
    FOREIGN KEY (order_id) REFERENCES Orders(order_id) ON DELETE SET NULL,
    FOREIGN KEY (request_id) REFERENCES SwapRequest(request_id) ON DELETE SET NULL
);

CREATE TABLE Notification (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(100),
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE
);

SQL;

$conn = new mysqli("localhost", "root", "", "");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ create DB
$conn->query("CREATE DATABASE IF NOT EXISTS eco_project");

// ✅ select DB
$conn->select_db("eco_project");

// ✅ execute schema
if ($conn->multi_query($schema)) {
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());

    echo "Database schema generated successfully.";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();

// // Write output directly to standard output
// fwrite(STDOUT, $schema);
// echo "\n-- Schema generation complete. Pipe this output directly into your DB client.\n";
?>