CREATE DATABASE IF NOT EXISTS finmark_db;
USE finmark_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE (user_id, product_id)
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('Pending','Processing','Completed','Cancelled') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    estimated_delivery DATE GENERATED ALWAYS AS (DATE_ADD(created_at, INTERVAL 7 DAY)) STORED,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CHECK (LENGTH(comment) <= 1000)
);

INSERT INTO users (username, email, password, is_admin) VALUES
('admin', 'admin@finmark.com', '$2y$12$W6fQz5Yk7z4Xh8m9j0kLue1n2o3p4q5r6s7t8u9v0w1x2y3z4a5b6', TRUE),
('user1', 'user1@finmark.com', '$2y$12$X7gRz6Zk8a5Yh9m0j1kMvf2n3o4p5q6r7s8t9u0v1w2x3y4z5a6b7', FALSE);

INSERT INTO products (name, description, price) VALUES
('Financial Analysis', 'Comprehensive financial health assessment for startups and SMBs.', 499.99),
('Marketing Analytics', 'Data-driven marketing strategies to boost ROI.', 799.99),
('Business Intelligence', 'Custom BI dashboards for actionable insights.', 999.99),
('Consulting Services', 'Tailored consulting for SME growth and efficiency.', 1499.99);

CREATE INDEX idx_user_id_cart ON cart_items (user_id);
CREATE INDEX idx_product_id_cart ON cart_items (product_id);
CREATE INDEX idx_user_id_orders ON orders (user_id);
CREATE INDEX idx_status_orders ON orders (status);
CREATE INDEX idx_user_id_feedback ON feedback (user_id);