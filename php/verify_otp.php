<?php
/*
 * OTP Verification Process
 * YP4K Resort - Booking & Trail Guide System
 * Handles OTP verification for both users and admins
 */

// Start output buffering to prevent HTML errors
ob_start();

session_start();
require_once '../phpAuthentication/db_config.php';

// Never emit PHP warnings/notices into JSON responses
ini_set('display_errors', '0');
error_reporting(E_ALL);

header('Content-Type: application/json');

// Function to send JSON response and exit
function sendJsonResponse($success, $message, $data = []) {
    // Clean output buffer
    ob_clean();
    $response = array_merge(['success' => $success, 'message' => $message], $data);
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? ($_SESSION['verification_email'] ?? ''));
    $otp = trim($_POST['otp'] ?? '');
    
    if (empty($otp)) {
        echo json_encode(['success' => false, 'message' => 'Verification code is required']);
        exit;
    }

    if (empty($email)) {
        echo json_encode([
            'success' => false,
            'message' => 'Verification session expired. Please register again.',
            'error_type' => 'session_expired'
        ]);
        exit;
    }
    
    try {
        // Get latest active OTP record for this email
        $stmt = $conn->prepare("
            SELECT * FROM otp_verifications 
            WHERE email = ? AND expires_at > NOW() 
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid or incorrect verification code.',
                'error_type' => 'invalid_otp'
            ]);
            exit;
        }
        
        $otpRecord = $result->fetch_assoc();
        
        // Check attempts
        if ($otpRecord['attempts'] >= 3) {
            // Delete the OTP record
            $delete_stmt = $conn->prepare("DELETE FROM otp_verifications WHERE id = ?");
            $delete_stmt->bind_param("i", $otpRecord['id']);
            $delete_stmt->execute();
            
            echo json_encode([
                'success' => false,
                'message' => 'Too many invalid attempts. Please register again.',
                'error_type' => 'invalid_otp'
            ]);
            exit;
        }
        
        // Wrong OTP: increment attempts and return a clear validation message
        if (!hash_equals((string)$otpRecord['otp'], (string)$otp)) {
            $update_attempts_stmt = $conn->prepare("UPDATE otp_verifications SET attempts = attempts + 1 WHERE id = ?");
            $update_attempts_stmt->bind_param("i", $otpRecord['id']);
            $update_attempts_stmt->execute();
            $update_attempts_stmt->close();

            echo json_encode([
                'success' => false,
                'message' => 'Invalid or incorrect verification code.',
                'error_type' => 'invalid_otp'
            ]);
            exit;
        }
        
        // OTP is valid, now create the user/admin account
        $userData = json_decode($otpRecord['user_data'] ?? '', true);
        if (!is_array($userData) || empty($userData)) {
            $sessionUserData = json_decode($_SESSION['user_data'] ?? '', true);
            if (is_array($sessionUserData) && !empty($sessionUserData)) {
                $userData = $sessionUserData;
            }
        }

        $userType = $otpRecord['user_type'];
        if (empty($userType)) {
            $userType = $_SESSION['user_type'] ?? 'user';
        }

        if (!is_array($userData) || empty($userData['password']) || empty($userData['email'])) {
            sendJsonResponse(false, 'Verification session expired. Please register again.', [
                'error_type' => 'session_expired'
            ]);
        }
        
        // Hash password
        $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
        $verificationToken = bin2hex(random_bytes(32));
        
        if ($userType === 'admin') {
            // Insert admin data
            $insert_stmt = $conn->prepare("
                INSERT INTO admins (
                    first_name, middle_name, last_name, suffix, 
                    email, phone_number, password, is_verified, 
                    verification_token, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?, NOW())
            ");
            
            $middleName = $userData['middle_name'] ?? '';
            $suffix = $userData['suffix'] ?? '';
            $insert_stmt->bind_param(
                "ssssssss",
                $userData['first_name'],
                $middleName,
                $userData['last_name'],
                $suffix,
                $userData['email'],
                $userData['phone_number'],
                $hashedPassword,
                $verificationToken
            );
            
            $redirectPage = '../Authentication/admin-login.php';
            $successMessage = 'Admin account created successfully! Please login with your credentials.';
            
        } else {
            // Insert user data
            $insert_stmt = $conn->prepare("
                INSERT INTO users (
                    first_name, middle_name, last_name, suffix,
                    email, phone_number, region, province, 
                    city_municipality, barangay, password, is_verified,
                    verification_token, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, NOW())
            ");
            
            $middleName = $userData['middle_name'] ?? '';
            $suffix = $userData['suffix'] ?? '';
            $region = $userData['region'] ?? '';
            $province = $userData['province'] ?? '';
            $cityMunicipality = $userData['city_municipality'] ?? '';
            $barangay = $userData['barangay'] ?? '';
            $insert_stmt->bind_param(
                "ssssssssssss",
                $userData['first_name'],
                $middleName,
                $userData['last_name'],
                $suffix,
                $userData['email'],
                $userData['phone_number'],
                $region,
                $province,
                $cityMunicipality,
                $barangay,
                $hashedPassword,
                $verificationToken
            );
            
            $redirectPage = '../Authentication/login.php';
            $successMessage = 'User account created successfully! Please login with your credentials.';
        }
        
        if ($insert_stmt->execute()) {
            // Delete OTP record after successful registration
            $delete_otp_stmt = $conn->prepare("DELETE FROM otp_verifications WHERE id = ?");
            $delete_otp_stmt->bind_param("i", $otpRecord['id']);
            $delete_otp_stmt->execute();
            
            echo json_encode([
                'success' => true,
                'message' => $successMessage,
                'redirect' => $redirectPage
            ]);
            
        } else {
            throw new Exception("Failed to create account: " . $insert_stmt->error);
        }
        
        $insert_stmt->close();
        if (isset($delete_otp_stmt)) {
            $delete_otp_stmt->close();
        }
        $stmt->close();
        
    } catch (Throwable $e) {
        error_log("OTP verification error: " . $e->getMessage());
        sendJsonResponse(false, 'Something went wrong. Please try again.', [
            'error_type' => 'server_error'
        ]);
    }
} else {
    sendJsonResponse(false, 'Invalid request method');
}

$conn->close();
?>
