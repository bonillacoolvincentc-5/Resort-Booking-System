-- YP4K Resort Registration and Login Database Setup - Updated
-- Compatible with XAMPP and MySQL

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS yp4k_resort_db
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use the database
USE yp4k_resort_db;

-- Drop table if it exists (for fresh installation)
DROP TABLE IF EXISTS users;

-- Create users table with all required fields
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
    is_verified TINYINT(1) DEFAULT 0,
    verification_token VARCHAR(255) NULL,
    reset_token VARCHAR(255) NULL,
    reset_token_expiry TIMESTAMP NULL,
    remember_token VARCHAR(255) NULL,
    token_expiry TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Add indexes for better performance
    INDEX idx_email (email),
    INDEX idx_phone (phone_number),
    INDEX idx_verification_token (verification_token),
    INDEX idx_reset_token (reset_token),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create admins table for admin functionality
DROP TABLE IF EXISTS admins;

CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) NULL,
    last_name VARCHAR(100) NOT NULL,
    suffix VARCHAR(50) NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone_number VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_verified TINYINT(1) DEFAULT 0,
    verification_token VARCHAR(255) NULL,
    reset_token VARCHAR(255) NULL,
    reset_token_expiry TIMESTAMP NULL,
    remember_token VARCHAR(255) NULL,
    token_expiry TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Add indexes
    INDEX idx_email (email),
    INDEX idx_phone (phone_number),
    INDEX idx_verification_token (verification_token),
    INDEX idx_reset_token (reset_token),
    INDEX idx_created_at (created_at)
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

-- Create password_resets table for password recovery
DROP TABLE IF EXISTS password_resets;

CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    user_type ENUM('user', 'admin') NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    is_used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Add indexes
    INDEX idx_email (email),
    INDEX idx_token (token),
    INDEX idx_expires_at (expires_at),
    INDEX idx_user_type (user_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create login_logs table for security auditing
DROP TABLE IF EXISTS login_logs;

CREATE TABLE login_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_type ENUM('user', 'admin') NOT NULL,
    email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Add indexes
    INDEX idx_user_id (user_id),
    INDEX idx_email (email),
    INDEX idx_login_time (login_time),
    INDEX idx_user_type (user_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create password_reset_attempts table for security monitoring
DROP TABLE IF EXISTS password_reset_attempts;

CREATE TABLE password_reset_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Add indexes
    INDEX idx_email (email),
    INDEX idx_ip_address (ip_address),
    INDEX idx_attempted_at (attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create password_reset_logs table for audit trail
DROP TABLE IF EXISTS password_reset_logs;

CREATE TABLE password_reset_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_type ENUM('user', 'admin') NOT NULL,
    email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    reset_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Add indexes
    INDEX idx_user_id (user_id),
    INDEX idx_email (email),
    INDEX idx_reset_time (reset_time),
    INDEX idx_user_type (user_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Success message
SELECT 'Database and tables created successfully!' as message;
