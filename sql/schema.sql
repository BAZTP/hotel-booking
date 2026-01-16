CREATE DATABASE IF NOT EXISTS hotel_booking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hotel_booking;

-- Tipos de habitación
CREATE TABLE room_types (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  description TEXT NULL,
  capacity INT NOT NULL DEFAULT 2,
  base_price_cents INT NOT NULL DEFAULT 5000,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Habitaciones individuales (stock real)
CREATE TABLE rooms (
  id INT AUTO_INCREMENT PRIMARY KEY,
  room_type_id INT NOT NULL,
  room_number VARCHAR(10) NOT NULL,
  status ENUM('active','maintenance') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_room_number (room_number),
  INDEX idx_type (room_type_id),
  CONSTRAINT fk_room_type FOREIGN KEY(room_type_id) REFERENCES room_types(id) ON DELETE CASCADE
);

-- Reservas
CREATE TABLE bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  booking_code CHAR(12) NOT NULL UNIQUE,
  room_id INT NOT NULL,
  room_type_id INT NOT NULL,
  customer_name VARCHAR(120) NOT NULL,
  customer_email VARCHAR(120) NULL,
  check_in DATE NOT NULL,
  check_out DATE NOT NULL,
  guests INT NOT NULL DEFAULT 1,
  total_cents INT NOT NULL DEFAULT 0,
  status ENUM('pending','paid','cancelled') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  paid_at TIMESTAMP NULL DEFAULT NULL,
  cancelled_at TIMESTAMP NULL DEFAULT NULL,
  INDEX idx_dates (check_in, check_out),
  INDEX idx_status (status),
  CONSTRAINT fk_booking_room FOREIGN KEY(room_id) REFERENCES rooms(id) ON DELETE RESTRICT,
  CONSTRAINT fk_booking_type FOREIGN KEY(room_type_id) REFERENCES room_types(id) ON DELETE RESTRICT
);

-- Admin users (simple)
CREATE TABLE admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80) NOT NULL UNIQUE,
  pass_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','staff') NOT NULL DEFAULT 'admin',
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed room types
INSERT INTO room_types(name, description, capacity, base_price_cents) VALUES
('Standard', 'Habitación estándar con baño privado.', 2, 4500),
('Deluxe', 'Habitación deluxe con vista y amenities.', 3, 6500),
('Suite', 'Suite amplia con sala y jacuzzi.', 4, 9800);

-- Seed rooms (ejemplo stock)
INSERT INTO rooms(room_type_id, room_number, status) VALUES
(1, '101', 'active'),
(1, '102', 'active'),
(1, '103', 'active'),
(2, '201', 'active'),
(2, '202', 'active'),
(3, '301', 'active');

-- Seed admin user (HASH PLACEHOLDER)
-- Genera hash con: password_hash("Admin123!", PASSWORD_BCRYPT)
INSERT INTO admin_users(username, pass_hash, role, status) VALUES
('admin', 'REEMPLAZA_CON_HASH', 'admin', 'active');
