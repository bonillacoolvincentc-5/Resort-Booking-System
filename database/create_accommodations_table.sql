-- Create accommodations table for resort booking system
-- This table stores information about different types of accommodations
-- including rooms, cottages, villas, and function halls

CREATE TABLE IF NOT EXISTS accommodations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type ENUM('room', 'cottage', 'villa', 'hall') NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    capacity INT NOT NULL,
    image VARCHAR(255),
    status ENUM('available', 'unavailable') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Add indexes for better query performance
    INDEX idx_type (type),
    INDEX idx_status (status),
    INDEX idx_price (price),
    INDEX idx_capacity (capacity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add sample data (optional - can be removed if not needed)
INSERT INTO accommodations (name, type, description, price, capacity, image, status) VALUES
('Deluxe Room', 'room', 'Spacious room with king-size bed, ocean view, and modern amenities', 150.00, 2, 'images/rooms/deluxe.jpg', 'available'),
('Garden Cottage', 'cottage', 'Cozy cottage surrounded by tropical gardens, perfect for couples', 200.00, 4, 'images/cottages/garden.jpg', 'available'),
('Ocean Villa', 'villa', 'Luxury villa with private pool and direct beach access', 450.00, 6, 'images/villas/ocean.jpg', 'available'),
('Grand Ballroom', 'hall', 'Elegant function hall for weddings and corporate events', 1000.00, 200, 'images/halls/grand.jpg', 'available'),
('Standard Room', 'room', 'Comfortable room with essential amenities', 100.00, 2, 'images/rooms/standard.jpg', 'available'),
('Mountain Villa', 'villa', 'Scenic villa with mountain views and outdoor deck', 380.00, 8, 'images/villas/mountain.jpg', 'available'),
('Beach Cottage', 'cottage', 'Beachfront cottage with stunning sunset views', 250.00, 4, 'images/cottages/beach.jpg', 'available'),
('Conference Hall', 'hall', 'Modern conference hall with AV equipment', 500.00, 100, 'images/halls/conference.jpg', 'available');
