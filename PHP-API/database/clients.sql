-- Script para crear la base de datos y tabla de clientes

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS clients CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE clients;

-- Crear tabla de clientes
CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insertar datos de ejemplo
INSERT INTO clients (name, email, phone, address) VALUES
('Juan Pérez', 'juan.perez@email.com', '+34 666 123 456', 'Calle Mayor 123, Madrid'),
('María García', 'maria.garcia@email.com', '+34 666 789 012', 'Avenida del Sol 45, Barcelona'),
('Carlos López', 'carlos.lopez@email.com', '+34 666 345 678', 'Plaza Central 67, Valencia'),
('Ana Martínez', 'ana.martinez@email.com', '+34 666 901 234', 'Calle Luna 89, Sevilla');

-- Mostrar los datos insertados
SELECT * FROM clients;
