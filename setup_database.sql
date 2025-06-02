-- Create the database
CREATE DATABASE IF NOT EXISTS qr_scanner;
USE qr_scanner;

-- Create table for authorized QR codes
CREATE TABLE IF NOT EXISTS authorized_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    qr_code VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    photo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create table for scan logs
CREATE TABLE IF NOT EXISTS scan_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    qr_hash VARCHAR(255) NOT NULL UNIQUE,
    qr_data VARCHAR(255) NOT NULL,
    status VARCHAR(50) NOT NULL,
    name VARCHAR(255),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert some sample authorized codes (replace with your actual data)
INSERT INTO authorized_codes (qr_hash, qr_code, name, photo) VALUES
('SAMPLE123', 'SAMPLE123', 'John Doe', 'photos/john.jpg'),
('SAMPLE456', 'SAMPLE456', 'Jane Smith', 'photos/jane.jpg'); 