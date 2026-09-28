<?php
/*
 * Admin Registration Process
 * YP4K Resort - Booking & Trail Guide System
 * Handles admin registration with middle name and suffix support
 */

session_start();
require_once '../phpAuthentication/db_config.php';
require_once '../phpAuthentication/email_helper.php';
require_once '../phpAuthentication/blocked_email_helper.php';

header('Content-Type: application/json');

// Function to send JSON response and exit
function sendJsonResponse($success, $message, $data = []) {
    $response = array_merge(['success' => $success, 'message' => $message], $data);
    echo json_encode($response);
    exit();
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    try {
        // Get form data - support both old and new field names
        $firstName = trim($_POST['first_name'] ?? $_POST['firstName'] ?? '');
        $middleName = trim($_POST['middle_name'] ?? $_POST['middleName'] ?? '');
        $lastName = trim($_POST['last_name'] ?? $_POST['lastName'] ?? '');
        $suffix = trim($_POST['suffix'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phoneNumber = trim($_POST['phone_number'] ?? $_POST['phone'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? $_POST['confirmPassword'] ?? '');
        $userType = 'admin'; // Fixed to admin
        $agreeTerms = isset($_POST['agree_terms']) || isset($_POST['agreeTerms']);
        
        // Validation
        $errors = array();
        
        // Basic field validation
        if (empty($firstName) || empty($lastName) || empty($email) || empty($phoneNumber) || empty($password)) {
            $errors[] = "Required fields are missing!";
        }
        
        // Validate names
        if (!empty($firstName) && !preg_match("/^[a-zA-Z\s'-]+$/", $firstName)) {
            $errors[] = "First name contains invalid characters!";
        }
        
        if (!empty($middleName) && !preg_match("/^[a-zA-Z\s'-]*$/", $middleName)) {
            $errors[] = "Middle name contains invalid characters!";
        }
        
        if (!empty($lastName) && !preg_match("/^[a-zA-Z\s'-]+$/", $lastName)) {
            $errors[] = "Last name contains invalid characters!";
        }
        
        if (!empty($suffix) && !preg_match("/^[a-zA-Z\s\.-]*$/", $suffix)) {
            $errors[] = "Suffix contains invalid characters!";
        }
        
        // Validate email
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format!";
        }
        
        // Validate phone number (Philippine format)
        if (!empty($phoneNumber) && !preg_match("/^09[0-9]{9}$/", $phoneNumber)) {
            $errors[] = "Invalid phone number format! Use 09XXXXXXXXX format.";
        }
        
        // Validate password
        if (!empty($password)) {
            if (strlen($password) < 8) {
                $errors[] = "Password must be at least 8 characters long!";
            }
            if (!preg_match("/[A-Z]/", $password) || !preg_match("/\d/", $password) || !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
                $errors[] = "Password must contain at least one uppercase letter, one number, and one special character!";
            }
        }
        
        // Check password match
        if ($password !== $confirmPassword) {
            $errors[] = "Passwords do not match!";
        }
        
        // Check terms agreement
        if (!$agreeTerms) {
            $errors[] = "You must agree to terms and conditions!";
        }
        
        // If there are errors, return them
        if (!empty($errors)) {
            sendJsonResponse(false, implode(" ", $errors));
        }

        if (is_email_blocked($conn, $email)) {
            sendJsonResponse(false, blocked_email_message('register'), ['blocked' => true]);
        }

        // Check if email already exists in admins table
        $check_stmt = $conn->prepare("SELECT id FROM admins WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            sendJsonResponse(false, "Email already registered!");
        }
        
        // Check if phone number already exists in admins table
        $phone_check_stmt = $conn->prepare("SELECT id FROM admins WHERE phone_number = ?");
        $phone_check_stmt->bind_param("s", $phoneNumber);
        $phone_check_stmt->execute();
        $phone_result = $phone_check_stmt->get_result();
        
        if ($phone_result->num_rows > 0) {
            sendJsonResponse(false, "Phone number already registered!");
        }
        
        // Generate OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $otpExpiry = date('Y-m-d H:i:s', time() + 300); // 5 minutes
        
        // Prepare user data for storage
        $userData = [
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'suffix' => $suffix,
            'email' => $email,
            'phone_number' => $phoneNumber,
            'password' => $password
        ];
        
        // Store OTP in database
        $otp_stmt = $conn->prepare("
            INSERT INTO otp_verifications (email, otp, user_type, user_data, expires_at) 
            VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE))
        ");
        
        $userDataJson = json_encode($userData);
        $otp_stmt->bind_param("ssss", $email, $otp, $userType, $userDataJson);
        
        if ($otp_stmt->execute()) {
            // Set session variables for verification page
            $_SESSION['verification_email'] = $email;
            $_SESSION['verification_code'] = $otp;
            $_SESSION['user_type'] = $userType;
            $_SESSION['user_data'] = $userDataJson;
            
            // Send OTP email
            $emailResult = EmailHelper::sendOTPEmail($email, $otp, $userType, $firstName);
            
            if ($emailResult['success']) {
                sendJsonResponse(true, 'Registration data received! Please check your email for the verification code.', [
                    'redirect' => '../Authentication/verify.php?email=' . urlencode($email),
                    'email' => $email
                ]);
            } else {
                // If email fails, delete the OTP record
                $delete_stmt = $conn->prepare("DELETE FROM otp_verifications WHERE email = ? AND otp = ?");
                $delete_stmt->bind_param("ss", $email, $otp);
                $delete_stmt->execute();
                
                sendJsonResponse(false, 'Failed to send verification email. Please try again.');
            }
        } else {
            throw new Exception("Failed to store OTP: " . $otp_stmt->error);
        }
        
        $otp_stmt->close();
        $check_stmt->close();
        $phone_check_stmt->close();
        
    } catch (Exception $e) {
        error_log("Admin registration error: " . $e->getMessage());
        sendJsonResponse(false, "An error occurred. Please try again!");
    }
} else {
    sendJsonResponse(false, "Invalid request method!");
}

$conn->close();
?>
