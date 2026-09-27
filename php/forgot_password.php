<?php
/**
 * Forgot Password API
 * YP4K Resort — handles 3 actions: send_code | verify_code | reset_password
 */

ob_start();
session_start();
require_once '../phpAuthentication/db_config.php';
require_once '../phpAuthentication/email_helper.php';

ini_set('display_errors', '0');
error_reporting(E_ALL);

header('Content-Type: application/json');

function fp_json($success, $message, $extra = []) {
    ob_clean();
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fp_json(false, 'Invalid request method');
}

$action = trim($_POST['action'] ?? '');

// ── 1. SEND CODE ────────────────────────────────────────────────────
if ($action === 'send_code') {
    $email = strtolower(trim($_POST['email'] ?? ''));

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        fp_json(false, 'Please enter a valid email address.');
    }

    // Check the user exists in users OR admins table
    $userRow = null;
    $userType = null;

    $stmt = $conn->prepare("SELECT id, email FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 1) {
        $userRow = $res->fetch_assoc();
        $userType = 'user';
    }
    $stmt->close();

    if (!$userRow) {
        $stmt = $conn->prepare("SELECT id, email FROM admins WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 1) {
            $userRow = $res->fetch_assoc();
            $userType = 'admin';
        }
        $stmt->close();
    }

    if (!$userRow) {
        // Generic response to prevent email enumeration
        fp_json(true, 'If that email is registered, a reset code has been sent.');
    }

    // Enforce 120-second cooldown per email
    $conn->query("CREATE TABLE IF NOT EXISTS `password_reset_otps` (
        `id` int NOT NULL AUTO_INCREMENT,
        `email` varchar(255) NOT NULL,
        `otp` varchar(6) NOT NULL,
        `user_type` varchar(20) NOT NULL DEFAULT 'user',
        `attempts` int NOT NULL DEFAULT 0,
        `expires_at` datetime NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `idx_email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $coolStmt = $conn->prepare("SELECT created_at FROM password_reset_otps WHERE email = ? AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1");
    $coolStmt->bind_param('s', $email);
    $coolStmt->execute();
    $coolRes = $coolStmt->get_result();
    if ($coolRes->num_rows === 1) {
        $row = $coolRes->fetch_assoc();
        $diff = time() - strtotime($row['created_at']);
        if ($diff < 120) {
            $wait = 120 - $diff;
            fp_json(false, "Please wait {$wait} second(s) before requesting another code.", ['cooldown' => $wait]);
        }
    }
    $coolStmt->close();

    // Generate & store OTP
    $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiresAt = date('Y-m-d H:i:s', time() + 300); // 5 minutes

    // Delete any old codes for this email, then insert fresh
    $delStmt = $conn->prepare("DELETE FROM password_reset_otps WHERE email = ?");
    $delStmt->bind_param('s', $email);
    $delStmt->execute();
    $delStmt->close();

    $insStmt = $conn->prepare("INSERT INTO password_reset_otps (email, otp, user_type, expires_at) VALUES (?, ?, ?, ?)");
    $insStmt->bind_param('ssss', $email, $otp, $userType, $expiresAt);
    $insStmt->execute();
    $insStmt->close();

    // Send email via EmailHelper
    $result = EmailHelper::sendPasswordResetOTPEmail($email, $otp, $userType);

    if (!$result['success']) {
        error_log('Forgot password email failed: ' . ($result['message'] ?? ''));
        fp_json(false, 'Failed to send reset code. Please try again later.');
    }

    fp_json(true, 'Reset code sent to your email. It expires in 5 minutes.');
}

// ── 2. VERIFY CODE ──────────────────────────────────────────────────
if ($action === 'verify_code') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $otp   = trim($_POST['otp'] ?? '');

    if (empty($email) || empty($otp)) {
        fp_json(false, 'Email and code are required.');
    }

    $stmt = $conn->prepare("SELECT id, otp, attempts, expires_at FROM password_reset_otps WHERE email = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        fp_json(false, 'No reset code found. Please request a new one.');
    }

    $row = $res->fetch_assoc();
    $stmt->close();

    // Expired?
    if (strtotime($row['expires_at']) < time()) {
        $conn->prepare("DELETE FROM password_reset_otps WHERE id = ?")->execute() ;
        fp_json(false, 'Your reset code has expired. Please request a new one.');
    }

    // Too many attempts?
    if ($row['attempts'] >= 5) {
        $d = $conn->prepare("DELETE FROM password_reset_otps WHERE id = ?");
        $d->bind_param('i', $row['id']); $d->execute(); $d->close();
        fp_json(false, 'Too many invalid attempts. Please request a new code.');
    }

    // Wrong code?
    if (!hash_equals((string)$row['otp'], (string)$otp)) {
        $upd = $conn->prepare("UPDATE password_reset_otps SET attempts = attempts + 1 WHERE id = ?");
        $upd->bind_param('i', $row['id']); $upd->execute(); $upd->close();
        $remaining = max(0, 4 - (int)$row['attempts']);
        fp_json(false, "Incorrect code. {$remaining} attempt(s) remaining.");
    }

    // Correct — issue a short-lived reset token stored in session (never in URL)
    $resetToken = bin2hex(random_bytes(32));
    $_SESSION['fp_reset_token']  = $resetToken;
    $_SESSION['fp_reset_email']  = $email;
    $_SESSION['fp_token_expiry'] = time() + 600; // 10 minutes to set new password

    // Mark OTP as used
    $d = $conn->prepare("DELETE FROM password_reset_otps WHERE id = ?");
    $d->bind_param('i', $row['id']); $d->execute(); $d->close();

    fp_json(true, 'Code verified. You may now reset your password.', ['reset_token' => $resetToken]);
}

// ── 3. RESET PASSWORD ───────────────────────────────────────────────
if ($action === 'reset_password') {
    $token       = trim($_POST['reset_token'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirm     = $_POST['confirm_password'] ?? '';

    if (
        empty($token) ||
        !isset($_SESSION['fp_reset_token']) ||
        !hash_equals($_SESSION['fp_reset_token'], $token) ||
        !isset($_SESSION['fp_token_expiry']) ||
        time() > $_SESSION['fp_token_expiry']
    ) {
        unset($_SESSION['fp_reset_token'], $_SESSION['fp_reset_email'], $_SESSION['fp_token_expiry']);
        fp_json(false, 'Reset session expired or invalid. Please start over.');
    }

    if (strlen($newPassword) < 8 || !preg_match('/[A-Z]/', $newPassword) || !preg_match('/\d/', $newPassword) || !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $newPassword)) {
        fp_json(false, 'Password must be at least 8 characters long and include an uppercase letter, a number, and a special character.');
    }

    if ($newPassword !== $confirm) {
        fp_json(false, 'Passwords do not match.');
    }

    $email    = $_SESSION['fp_reset_email'];
    $hashed   = password_hash($newPassword, PASSWORD_DEFAULT);

    // Try users first, then admins
    $updated = false;
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->bind_param('ss', $hashed, $email);
    $stmt->execute();
    if ($stmt->affected_rows > 0) $updated = true;
    $stmt->close();

    if (!$updated) {
        $stmt = $conn->prepare("UPDATE admins SET password = ? WHERE email = ?");
        $stmt->bind_param('ss', $hashed, $email);
        $stmt->execute();
        if ($stmt->affected_rows > 0) $updated = true;
        $stmt->close();
    }

    // Clear session token so it cannot be reused
    unset($_SESSION['fp_reset_token'], $_SESSION['fp_reset_email'], $_SESSION['fp_token_expiry']);

    if (!$updated) {
        fp_json(false, 'Failed to update password. Please try again.');
    }

    fp_json(true, 'Password reset successfully! You can now log in.');
}

fp_json(false, 'Unknown action.');
?>
