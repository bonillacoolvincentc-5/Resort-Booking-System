<?php
/**
 * YP4K Resort - User Registration
 * Handles user registration with secure password hashing
 */

// Include database configuration
require_once 'db_config.php';

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input data
    $firstName = trim($_POST['first_name'] ?? '');
    $middleName = trim($_POST['middle_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $suffix = trim($_POST['suffix'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phoneNumber = trim($_POST['phone_number'] ?? '');
    $region = trim($_POST['region'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $cityMunicipality = trim($_POST['city_municipality'] ?? '');
    $barangay = trim($_POST['barangay'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validation
    $errors = [];
    
    // Required fields validation
    if (empty($firstName)) $errors[] = 'First name is required';
    if (empty($lastName)) $errors[] = 'Last name is required';
    if (empty($email)) $errors[] = 'Email is required';
    if (empty($phoneNumber)) $errors[] = 'Phone number is required';
    if (empty($region)) $errors[] = 'Region is required';
    if (empty($province)) $errors[] = 'Province is required';
    if (empty($cityMunicipality)) $errors[] = 'City/Municipality is required';
    if (empty($barangay)) $errors[] = 'Barangay is required';
    if (empty($password)) $errors[] = 'Password is required';
    
    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    // Password validation
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }
    
    // Phone number validation (basic)
    if (!preg_match('/^[0-9+\-\s\(\)]+$/', $phoneNumber)) {
        $errors[] = 'Invalid phone number format';
    }
    
    // If there are validation errors, return them
    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors
        ]);
        exit;
    }
    
    try {
        // Check if email already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'Email is already registered'
            ]);
            exit;
        }
        
        // Check if phone number already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE phone_number = ?");
        $stmt->execute([$phoneNumber]);
        
        if ($stmt->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'Phone number is already registered'
            ]);
            exit;
        }
        
        // Hash the password securely
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user data into database
        $sql = "INSERT INTO users (
            first_name, middle_name, last_name, suffix, email, phone_number,
            region, province, city_municipality, barangay, password, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            $firstName,
            $middleName,
            $lastName,
            $suffix,
            $email,
            $phoneNumber,
            $region,
            $province,
            $cityMunicipality,
            $barangay,
            $hashedPassword
        ]);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Registration successful! Please login.',
                'user_id' => $db->lastInsertId()
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Registration failed. Please try again.'
            ]);
        }
        
    } catch (PDOException $e) {
        // Log error for debugging
        error_log("Registration error: " . $e->getMessage());
        
        echo json_encode([
            'success' => false,
            'message' => 'Database error. Please try again later.'
        ]);
    }
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>
