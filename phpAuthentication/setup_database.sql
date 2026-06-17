-- YP4K Resort Registration and Login Database Setup
-- Compatible with XAMPP and MySQL

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS yp4k_resort
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use the database
USE yp4k_resort;

-- Drop table if it exists (for fresh installation)
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) NULL,
    last_name VARCHAR(100) NOT NULL,
    suffix VARCHAR(50) NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone_number VARCHAR(20) NOT NULL UNIQUE,
    region VARCHAR(100) NOT NULL,
    province VARCHAR(100) NOT NULL,
    city_municipality VARCHAR(100) NOT NULL,
    barangay VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Add indexes for better performance
    INDEX idx_email (email),
    INDEX idx_phone (phone_number),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create admins table for admin functionality
DROP TABLE IF EXISTS admins;

CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Add indexes
    INDEX idx_email (email),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create user_sessions table for session management
DROP TABLE IF EXISTS user_sessions;

CREATE TABLE user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_id VARCHAR(255) NOT NULL UNIQUE,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    
    -- Add indexes
    INDEX idx_user_id (user_id),
    INDEX idx_session_id (session_id),
    INDEX idx_expires_at (expires_at),
    
    -- Foreign key constraint
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create password_resets table for password recovery
DROP TABLE IF EXISTS password_resets;

CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_used BOOLEAN DEFAULT FALSE,
    
    -- Add indexes
    INDEX idx_user_id (user_id),
    INDEX idx_email (email),
    INDEX idx_token (token),
    INDEX idx_expires_at (expires_at),
    
    -- Foreign key constraint
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create otp_verifications table for email verification
DROP TABLE IF EXISTS otp_verifications;

CREATE TABLE otp_verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    otp VARCHAR(6) NOT NULL,
    user_type ENUM('user', 'admin') NOT NULL,
    user_data JSON NULL,
    expires_at TIMESTAMP NOT NULL,
    attempts INT DEFAULT 0,
    last_resend TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Add indexes
    INDEX idx_email (email),
    INDEX idx_otp (otp),
    INDEX idx_expires_at (expires_at),
    INDEX idx_user_type (user_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Success message
SELECT 'Database and tables created successfully!' as message;
