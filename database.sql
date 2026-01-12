-- Create database
CREATE DATABASE IF NOT EXISTS secscope_store;
USE secscope_store;

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create messages table
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- These hashes have been verified to work with the specified passwords

INSERT INTO users (username, email, password, role) VALUES 
-- Username: admin, Password: admin123
('admin', 'admin@secscope.net', '0192023a7bbd73250516f069df18b500', 'admin'),

-- Username: jeff.smith, Password: password123
('jeff.smith', 'jeff.smith@secscope.net', '482c811da5d5b4bc6d497ffa98491e38', 'admin'),

-- Username: eric.adams, Password: iloveyou
('eric.adams', 'eric.adams@secscope.net', 'f25a2fc72690b780b2a14e140ef6a9e0', 'admin'),

-- Username: john.doe, Password: qwerty123
('john.doe', 'john.doe@mail.net', '3fc0a7acf087f549ac2b266baf94b8b1', 'user'),

-- Username: joe.bloggs, Password: password
('joe.bloggs', 'joe.bloggs@email.net', '5f4dcc3b5aa765d61d8327deb882cf99', 'user'),

-- Username: sally.jones, Password: 123456
('sally.jones', 'sally.jones@email.com', 'e10adc3949ba59abbe56e057f20f883e', 'user');

-- Products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    category VARCHAR(100) NOT NULL,
    image VARCHAR(255),
    stock INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cart table
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Order items table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Insert sample products
INSERT INTO products (name, description, price, category, image, stock) VALUES
('MacBook Pro 16"', 'Powerful laptop for professionals with M2 Pro chip', 2499.99, 'Laptops', 'macbook-pro.jpg', 15),
('iPhone 14 Pro', 'Latest iPhone with Dynamic Island and A16 Bionic', 999.99, 'Smartphones', 'iphone-14-pro.jpg', 30),
('Samsung Galaxy S23', 'Android flagship with Snapdragon 8 Gen 2', 899.99, 'Smartphones', 'galaxy-s23.jpg', 25),
('Sony WH-1000XM5', 'Industry-leading noise canceling headphones', 349.99, 'Accessories', 'sony-headphones.jpg', 40),
('iPad Pro 12.9"', 'Powerful tablet with M2 chip and Liquid Retina XDR', 1099.99, 'Tablets', 'ipad-pro.jpg', 20),
('Apple Watch Series 8', 'Advanced health and fitness tracking smartwatch', 399.99, 'Smart Devices', 'apple-watch.jpg', 35),
('Dell XPS 13', 'Compact premium laptop with InfinityEdge display', 1299.99, 'Laptops', 'dell-xps.jpg', 18),
('Google Pixel 7 Pro', 'Google flagship with Tensor G2 and superior camera', 899.99, 'Smartphones', 'pixel-7-pro.jpg', 22),
('Samsung Galaxy Tab S8', 'Premium Android tablet with S Pen included', 699.99, 'Tablets', 'galaxy-tab.jpg', 15),
('Logitech MX Keys', 'Wireless illuminated keyboard for precision typing', 99.99, 'Accessories', 'logitech-keyboard.jpg', 50),
('Anker PowerCore 10000', 'Compact portable charger with fast charging', 59.99, 'Accessories', 'anker-charger.jpg', 60),
('Fitbit Charge 5', 'Advanced fitness tracker with stress management', 149.99, 'Smart Devices', 'fitbit-charge.jpg', 30),
('ASUS ROG Zephyrus', 'Gaming laptop with NVIDIA RTX graphics', 1999.99, 'Laptops', 'asus-rog.jpg', 12),
('OnePlus 11', 'Flagship killer with Hasselblad camera', 699.99, 'Smartphones', 'oneplus-11.jpg', 20),
('Apple AirPods Pro', 'Wireless earbuds with Active Noise Cancellation', 249.99, 'Accessories', 'airpods-pro.jpg', 45);