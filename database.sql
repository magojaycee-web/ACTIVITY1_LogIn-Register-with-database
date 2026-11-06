CREATE DATABASE IF NOT EXISTS mags_coffee CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mags_coffee;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('customer','admin') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    category VARCHAR(50) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    image_url VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    address VARCHAR(255) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pending','processing','shipped','completed','cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NULL,
    product_name VARCHAR(120) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    qty INT UNSIGNED NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Admin account: username "admin", password "admin123"  (PALITAN agad pag live na!)
INSERT INTO users (username, email, password_hash, role) VALUES
('admin', 'admin@example.com', '$2y$10$AJ/kxc1hqvtzFNeMWm6WhuqegBV38cS4yCOhqEjih1TalJSNne2c6', 'admin');

INSERT INTO products (name, category, description, price, stock) VALUES
('Espresso', 'Hot Coffee', 'Rich, bold single shot of espresso.', 90.00, 100),
('Americano', 'Hot Coffee', 'Espresso with hot water, smooth and clean.', 110.00, 100),
('Cappuccino', 'Hot Coffee', 'Espresso with steamed milk and thick foam.', 130.00, 100),
('Caramel Macchiato', 'Hot Coffee', 'Vanilla, steamed milk, espresso and caramel drizzle.', 150.00, 80),
('Iced Latte', 'Iced Coffee', 'Chilled espresso and milk over ice.', 140.00, 100),
('Spanish Latte', 'Iced Coffee', 'Sweet condensed milk latte served cold.', 155.00, 80),
('Iced Mocha', 'Iced Coffee', 'Chocolate and espresso with cold milk.', 160.00, 70),
('House Blend Beans 250g', 'Coffee Beans', 'Medium roast, balanced and nutty.', 420.00, 40),
('Benguet Arabica 250g', 'Coffee Beans', 'Locally grown Philippine arabica, bright and fruity.', 480.00, 30),
('Butter Croissant', 'Pastries', 'Flaky, freshly baked.', 85.00, 25),
('Chocolate Chip Cookie', 'Pastries', 'Soft and chewy.', 65.00, 40),
('Banana Bread Slice', 'Pastries', 'Moist homemade banana bread.', 75.00, 30);
