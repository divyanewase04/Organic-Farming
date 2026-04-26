-- Railway-friendly schema import (no CREATE DATABASE / USE statements)

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(180) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sku VARCHAR(50) NOT NULL UNIQUE,
  name VARCHAR(150) NOT NULL,
  category VARCHAR(60) NOT NULL,
  unit VARCHAR(20) NOT NULL DEFAULT 'kg',
  price DECIMAL(10,2) NOT NULL,
  stock_qty INT NOT NULL DEFAULT 0,
  image_url TEXT NOT NULL,
  description TEXT NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS orders (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  order_code VARCHAR(30) NOT NULL UNIQUE,
  user_id INT NOT NULL,
  status ENUM('PLACED', 'PROCESSING', 'SHIPPED', 'DELIVERED', 'CANCELLED') NOT NULL DEFAULT 'PLACED',
  payment_mode ENUM('ONLINE', 'COD') NOT NULL DEFAULT 'COD',
  subtotal DECIMAL(10,2) NOT NULL,
  delivery_charge DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  total_amount DECIMAL(10,2) NOT NULL,
  customer_name VARCHAR(120) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  address_line TEXT NOT NULL,
  pincode VARCHAR(10) NOT NULL,
  expected_delivery DATE NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS order_items (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT NOT NULL,
  product_id INT NOT NULL,
  product_name VARCHAR(150) NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL,
  quantity INT NOT NULL,
  line_total DECIMAL(10,2) NOT NULL,
  CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE IF NOT EXISTS contact_messages (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(180) NOT NULL,
  message TEXT NOT NULL,
  status ENUM('NEW', 'READ', 'RESOLVED') NOT NULL DEFAULT 'NEW',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_contact_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS chat_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  user_message TEXT NOT NULL,
  bot_response TEXT NOT NULL,
  category VARCHAR(40) NOT NULL DEFAULT 'fallback',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_chat_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

INSERT INTO products (sku, name, category, unit, price, stock_qty, image_url, description) VALUES
('VEG-TOM-001', 'Tomato', 'Vegetable', 'kg', 40.00, 120, 'https://upload.wikimedia.org/wikipedia/commons/8/88/Bright_red_tomato_and_cross_section02.jpg', 'Fresh pesticide-free red tomatoes.'),
('GRA-RIC-001', 'Rice', 'Grains', 'kg', 60.00, 200, 'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=900&q=80', 'Naturally grown rice with rich aroma.'),
('GRA-WHE-001', 'Wheat', 'Grains', 'kg', 50.00, 180, 'https://images.pexels.com/photos/326082/pexels-photo-326082.jpeg?auto=compress&cs=tinysrgb&w=1200', 'Whole grain wheat from organic farms.'),
('VEG-POT-001', 'Potato', 'Vegetable', 'kg', 30.00, 220, 'https://images.pexels.com/photos/144248/potatoes-potato-vegetables-food-144248.jpeg?auto=compress&cs=tinysrgb&w=1200', 'Starchy fresh potatoes for daily use.'),
('VEG-ONI-001', 'Onion', 'Vegetable', 'kg', 35.00, 150, 'https://upload.wikimedia.org/wikipedia/commons/1/1b/Onions.jpg', 'Naturally cured onions.'),
('VEG-CAR-001', 'Carrot', 'Vegetable', 'kg', 45.00, 130, 'https://images.unsplash.com/photo-1447175008436-170170753d52?auto=format&fit=crop&w=900&q=80', 'Crunchy carrots rich in nutrients.'),
('GRA-JOW-001', 'Jowar', 'Millets', 'kg', 55.00, 90, 'https://m.media-amazon.com/images/I/61eKPTRJisL._AC_UF1000,1000_QL80_.jpg', 'Organic sorghum millet.'),
('GRA-BAJ-001', 'Bajra', 'Millets', 'kg', 33.00, 100, 'https://images.pexels.com/photos/4110251/pexels-photo-4110251.jpeg?auto=compress&cs=tinysrgb&w=1200', 'Pearl millet with high fiber.'),
('VEG-BEE-001', 'Beetroot', 'Vegetable', 'kg', 60.00, 80, 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?auto=format&fit=crop&w=900&q=80', 'Naturally cultivated beetroot.'),
('FRU-CHK-001', 'Chiku', 'Fruits', 'kg', 60.00, 70, 'https://images.unsplash.com/photo-1608147130451-b5f24f7f6a4d?auto=format&fit=crop&w=900&q=80', 'Sweet chiku fruits from local farms.'),
('FRU-APP-001', 'Apple', 'Fruits', 'kg', 180.00, 85, 'https://images.unsplash.com/photo-1567306226416-28f0efdc88ce?auto=format&fit=crop&w=900&q=80', 'Fresh apples with natural sweetness.'),
('FRU-MAN-001', 'Mango', 'Fruits', 'kg', 150.00, 75, 'https://images.unsplash.com/photo-1553279768-865429fa0078?auto=format&fit=crop&w=900&q=80', 'Seasonal organic mangoes.')
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  category = VALUES(category),
  unit = VALUES(unit),
  price = VALUES(price),
  stock_qty = VALUES(stock_qty),
  image_url = VALUES(image_url),
  description = VALUES(description),
  is_active = 1;
