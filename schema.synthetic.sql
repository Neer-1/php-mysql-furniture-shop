CREATE DATABASE IF NOT EXISTS chisel
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE chisel;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(50) NOT NULL,
  last_name VARCHAR(50) NOT NULL,
  username VARCHAR(40) NOT NULL UNIQUE,
  email VARCHAR(254) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(15) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE admin (
  id INT AUTO_INCREMENT PRIMARY KEY,
  admin VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description TEXT NOT NULL,
  price DECIMAL(10,2) NOT NULL CHECK (price > 0),
  quantity INT NOT NULL CHECK (quantity >= 0),
  image VARCHAR(255) DEFAULT NULL,
  category ENUM('fullrooms','livingroom','kitchen','diningroom','bedroom','office','bathroom') NOT NULL
) ENGINE=InnoDB;

CREATE TABLE cart (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL CHECK (quantity > 0),
  UNIQUE KEY uq_cart_user_product (user_id, product_id),
  CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_cart_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO products (name, description, price, quantity, image, category) VALUES
  ('Demo Coffee Table', 'Synthetic catalog item for local testing.', 199.99, 7, 'images/livingroom_coffeetable.jpg', 'livingroom'),
  ('Demo Bookshelf', 'Synthetic catalog item for local testing.', 299.99, 5, 'images/livingroom_bookshelf.jpg', 'livingroom'),
  ('Demo Cabinet', 'Synthetic catalog item for local testing.', 129.50, 3, 'images/kitchen_kitchencabinerts.png', 'kitchen');

-- No users, admins, password hashes, real customer rows, or order records are seeded.