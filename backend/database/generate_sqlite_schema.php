<?php
// Set plain text content type if running in browser, or skip if CLI
if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/plain');
}

$schema = <<<SQL
-- Initialize database
CREATE DATABASE IF NOT EXISTS eco_project;
USE eco_project;

-- Core User Tables
CREATE TABLE User (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    trust_score INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE User_Role (
    user_id INT,
    role_name VARCHAR(100),
    PRIMARY KEY (user_id, role_name),
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

CREATE TABLE Eco_Impact (
    impact_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    co2_saved DECIMAL(10, 2) DEFAULT 0.00,
    waste_reduced DECIMAL(10, 2) DEFAULT 0.00,
    water_saved DECIMAL(10, 2) DEFAULT 0.00,
    eco_points INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

-- Material Taxonomy
CREATE TABLE Material_Taxonomy (
    material_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    parent_material_id INT NULL,
    FOREIGN KEY (parent_material_id) REFERENCES Material_Taxonomy(material_id) ON DELETE SET NULL
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
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES Material_Taxonomy(material_id) ON DELETE SET NULL
);

CREATE TABLE Upcycle_Transformation (
    transformation_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    listing_id INT NOT NULL,
    before_image_url VARCHAR(255),
    after_image_url VARCHAR(255),
    steps TEXT,
    materials_used TEXT,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (listing_id) REFERENCES Listings(listing_id) ON DELETE CASCADE
);

CREATE TABLE Review (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    FOREIGN KEY (listing_id) REFERENCES Listings(listing_id) ON DELETE CASCADE
);

CREATE TABLE Report (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    initiator_id INT NOT NULL,
    listing_id INT NULL,
    comment_id INT NULL,
    reason TEXT NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    FOREIGN KEY (initiator_id) REFERENCES User(user_id),
    FOREIGN KEY (listing_id) REFERENCES Listing(listing_id)
);

-- Transactions: Orders & Swaps
CREATE TABLE [Order] (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(50) DEFAULT 'pending',
    shipping_street VARCHAR(255),
    shipping_city VARCHAR(100),
    FOREIGN KEY (buyer_id) REFERENCES User(user_id)
);

CREATE TABLE Order_Item (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    listing_id INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    subtotal DECIMAL(10, 2) GENERATED ALWAYS AS (price * quantity), -- Handles calculateSubtotal() at DB level
    FOREIGN KEY (order_id) REFERENCES Order(order_id) ON DELETE CASCADE,
    FOREIGN KEY (listing_id) REFERENCES Listing(listing_id)
);

CREATE TABLE Payment (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) DEFAULT 'unpaid',
    FOREIGN KEY (order_id) REFERENCES Orders(order_id) ON DELETE CASCADE
);

CREATE TABLE Swap_Request (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    initiator_id INT NOT NULL,
    partner_id INT NOT NULL,
    requested_listing_id INT NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    FOREIGN KEY (initiator_id) REFERENCES Users(user_id),
    FOREIGN KEY (partner_id) REFERENCES Users(user_id),
    FOREIGN KEY (requested_listing_id) REFERENCES Listings(listing_id)
);

CREATE TABLE Offer (
    offer_id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    initiator_id INT NOT NULL,
    offer_value VARCHAR(255),
    status VARCHAR(50) DEFAULT 'pending',
    FOREIGN KEY (request_id) REFERENCES Swap_Requests(request_id) ON DELETE CASCADE,
    FOREIGN KEY (initiator_id) REFERENCES Users(user_id)
);

-- Support & Operations
CREATE TABLE Dispute (
    dispute_id INT AUTO_INCREMENT PRIMARY KEY,
    initiator_id INT NOT NULL,
    order_id INT NULL,
    request_id INT NULL,
    reason TEXT NOT NULL,
    status VARCHAR(50) DEFAULT 'open',
    FOREIGN KEY (initiator_id) REFERENCES Users(user_id),
    FOREIGN KEY (order_id) REFERENCES Orders(order_id) ON DELETE SET NULL,
    FOREIGN KEY (request_id) REFERENCES Swap_Requests(request_id) ON DELETE SET NULL
);

CREATE TABLE Notification (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(100),
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

SQL;

// Write output directly to standard output
fwrite(STDOUT, $schema);
echo "\n-- Schema generation complete. Pipe this output directly into your DB client.\n";
?>