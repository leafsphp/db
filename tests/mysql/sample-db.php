<?php

# sample database dump

/*
  -- Create users table
  CREATE TABLE users (
      id INT AUTO_INCREMENT PRIMARY KEY,
      name VARCHAR(255) NOT NULL,
      email VARCHAR(255) NOT NULL,
      balance DECIMAL(10, 2) DEFAULT 0.00,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  );
  
  -- Insert data into users
  INSERT INTO users (name, email, balance) VALUES
  ('John Doe', 'john@example.com', 100.00),
  ('Jane Smith', 'jane@example.com', 50.00),
  ('Alice Johnson', 'alice@example.com', 200.00);
  
  -- Create products table
  CREATE TABLE products (
      id INT AUTO_INCREMENT PRIMARY KEY,
      name VARCHAR(255) NOT NULL,
      stock INT NOT NULL DEFAULT 0,
      price DECIMAL(10, 2) NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  );
  
  -- Insert data into products
  INSERT INTO products (name, stock, price) VALUES
  ('Laptop', 10, 999.99),
  ('Smartphone', 20, 499.99),
  ('Tablet', 15, 299.99);
  
  -- Create orders table
  CREATE TABLE orders (
      id INT AUTO_INCREMENT PRIMARY KEY,
      user_id INT NOT NULL,
      product_id INT NOT NULL,
      quantity INT NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (user_id) REFERENCES users(id),
      FOREIGN KEY (product_id) REFERENCES products(id)
  );
  
  -- Insert data into orders
  INSERT INTO orders (user_id, product_id, quantity) VALUES
  (1, 1, 1), -- John Doe ordered 1 Laptop
  (2, 2, 2); -- Jane Smith ordered 2 Smartphones
  
  -- Create transaction_logs table
  CREATE TABLE transaction_logs (
      id INT AUTO_INCREMENT PRIMARY KEY,
      user_id INT NOT NULL,
      amount DECIMAL(10, 2) NOT NULL,
      type ENUM('credit', 'debit') NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (user_id) REFERENCES users(id)
  );
  
  -- Insert data into transaction_logs
  INSERT INTO transaction_logs (user_id, amount, type) VALUES
  (1, 100.00, 'debit'), -- John Doe made a debit transaction
  (2, 50.00, 'credit'); -- Jane Smith made a credit transaction
  
  -- Create order_logs table
  CREATE TABLE order_logs (
      id INT AUTO_INCREMENT PRIMARY KEY,
      order_id INT NOT NULL,
      status VARCHAR(50) NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (order_id) REFERENCES orders(id)
  );
  
  -- Insert data into order_logs
  INSERT INTO order_logs (order_id, status) VALUES
  (1, 'created'), -- Log for John Doe's Laptop order
  (2, 'created'); -- Log for Jane Smith's Smartphone order
*/

?>
