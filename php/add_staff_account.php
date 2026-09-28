<?php
/**
 * YP4K Resort - Add Staff Account
 * Handles creating a new staff account from the Admin Dashboard
 */
session_start();
require_once '../phpAuthentication/db_config.php';
require_once '../phpAuthentication/blocked_email_helper.php';

// Ensure response is JSON
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

// Accept only POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
    exit();
}

$firstName = trim($input['first_name'] ?? '');
$lastName = trim($input['last_name'] ?? '');
$email = trim($input['email'] ?? '');
$phone = trim($input['phone'] ?? '');
$password = $input['password'] ?? '';

// Basic validation
if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
    exit();
}

if (is_email_blocked($conn, $email)) {
    echo json_encode(['success' => false, 'message' => blocked_email_message('register'), 'blocked' => true]);
    exit();
}

if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/\d/', $password) || !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long and include an uppercase letter, a number, and a special character.']);
    exit();
}

try {
    // Check if email already exists in admins table
    $stmt = $conn->prepare("SELECT id FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email is already registered as an admin or staff.']);
        exit();
    }
    $stmt->close();

    // Check if email already exists in users table (just to be safe)
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email is already registered as a customer account.']);
        exit();
    }
    $stmt->close();

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new staff account
    $insert_stmt = $conn->prepare("
        INSERT INTO admins (
            first_name, last_name, email, phone_number, password, 
            is_verified, role, created_at
        ) VALUES (?, ?, ?, ?, ?, 1, 'staff', NOW())
    ");
    
    $insert_stmt->bind_param(
        "sssss",
        $firstName,
        $lastName,
        $email,
        $phone,
        $hashedPassword
    );
    
    if ($insert_stmt->execute()) {
        $logUserId = $_SESSION['admin_id'] ?? null;
        if ($logUserId) {
            $logAction = 'add_staff';
            $logDesc = "Created new staff account for $firstName $lastName ($email).";
            $logStmt = $conn->prepare("INSERT INTO activity_logs (user_id, role, action_type, description) VALUES (?, 'admin', ?, ?)");
            $logStmt->bind_param('iss', $logUserId, $logAction, $logDesc);
            $logStmt->execute();
            $logStmt->close();
        }
        echo json_encode(['success' => true, 'message' => 'Staff account created successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create staff account. Please try again.']);
    }
    $insert_stmt->close();

} catch (Exception $e) {
    error_log("Error adding staff account: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
}

$conn->close();
?>
