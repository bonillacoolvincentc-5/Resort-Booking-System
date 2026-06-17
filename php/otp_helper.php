<?php
session_start();
require_once 'db_config.php';

// OTP Helper Functions
class OTPHelper {
    private static $otp_length = 6;
    private static $otp_expiry = 300; // 5 minutes in seconds
    private static $resend_cooldown = 60; // 60 seconds cooldown
    
    public static function generateOTP() {
        return str_pad(random_int(0, 999999), self::$otp_length, '0', STR_PAD_LEFT);
    }
    
    public static function storeOTP($email, $otp, $userData, $userType = 'user') {
        // Clear any existing OTP for this email
        self::clearOTP($email);
        
        $expiryTime = time() + self::$otp_expiry;
        
        // Store in session
        $_SESSION['otp_data'] = [
            'email' => $email,
            'otp' => $otp,
            'expiry' => $expiryTime,
            'user_data' => $userData,
            'user_type' => $userType,
            'attempts' => 0,
            'last_resend' => 0
        ];
        
        return true;
    }
    
    public static function validateOTP($email, $otp) {
        if (!isset($_SESSION['otp_data'])) {
            return ['success' => false, 'message' => 'No verification code found'];
        }
        
        $otpData = $_SESSION['otp_data'];
        
        // Check if OTP has expired
        if (time() > $otpData['expiry']) {
            self::clearOTP($email);
            return ['success' => false, 'message' => 'Code has expired'];
        }
        
        // Check email match
        if ($otpData['email'] !== $email) {
            return ['success' => false, 'message' => 'Invalid email'];
        }
        
        // Check OTP match
        if ($otpData['otp'] !== $otp) {
            $otpData['attempts']++;
            $_SESSION['otp_data'] = $otpData;
            
            if ($otpData['attempts'] >= 3) {
                self::clearOTP($email);
                return ['success' => false, 'message' => 'Too many attempts. Please register again.'];
            }
            
            return ['success' => false, 'message' => 'Invalid code. Attempts remaining: ' . (3 - $otpData['attempts'])];
        }
        
        return ['success' => true, 'message' => 'Code verified successfully'];
    }
    
    public static function canResendOTP() {
        if (!isset($_SESSION['otp_data'])) {
            return true;
        }
        
        $otpData = $_SESSION['otp_data'];
        $timeSinceLastResend = time() - $otpData['last_resend'];
        
        return $timeSinceLastResend >= self::$resend_cooldown;
    }
    
    public static function getResendCooldown() {
        if (!isset($_SESSION['otp_data'])) {
            return 0;
        }
        
        $otpData = $_SESSION['otp_data'];
        $timeSinceLastResend = time() - $otpData['last_resend'];
        $remainingCooldown = self::$resend_cooldown - $timeSinceLastResend;
        
        return max(0, $remainingCooldown);
    }
    
    public static function resendOTP($email, $userData, $userType = 'user') {
        if (!self::canResendOTP()) {
            return ['success' => false, 'message' => 'Please wait before resending'];
        }
        
        $newOTP = self::generateOTP();
        $expiryTime = time() + self::$otp_expiry;
        
        // Update existing OTP data
        $_SESSION['otp_data']['otp'] = $newOTP;
        $_SESSION['otp_data']['expiry'] = $expiryTime;
        $_SESSION['otp_data']['user_data'] = $userData;
        $_SESSION['otp_data']['user_type'] = $userType;
        $_SESSION['otp_data']['last_resend'] = time();
        $_SESSION['otp_data']['attempts'] = 0;
        
        return ['success' => true, 'otp' => $newOTP];
    }
    
    public static function clearOTP($email = null) {
        if ($email && isset($_SESSION['otp_data']) && $_SESSION['otp_data']['email'] === $email) {
            unset($_SESSION['otp_data']);
        } elseif (!$email) {
            unset($_SESSION['otp_data']);
        }
    }
    
    public static function getOTPData() {
        return isset($_SESSION['otp_data']) ? $_SESSION['otp_data'] : null;
    }
}

// Email Helper Functions
class EmailHelper {
    public static function sendOTPEmail($email, $otp, $userType = 'user') {
        try {
            // Create PHPMailer instance
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Server settings (configure these for your email server)
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; // Change to your SMTP server
            $mail->SMTPAuth   = true;
            $mail->Username   = 'your-email@gmail.com'; // Change to your email
            $mail->Password   = 'your-app-password'; // Change to your app password
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            
            // Recipients
            $mail->setFrom('your-email@gmail.com', 'YP4K Resort');
            $mail->addAddress($email);
            
            // Content
            $subject = ($userType === 'admin') ? 'Admin Registration - Email Verification' : 'User Registration - Email Verification';
            $mail->Subject = $subject;
            
            $mail->isHTML(true);
            $mail->Body = self::generateOTPEmailTemplate($otp, $userType);
            $mail->AltBody = "Your verification code is: $otp\n\nThis code will expire in 5 minutes.";
            
            $mail->send();
            return ['success' => true, 'message' => 'Verification code sent successfully'];
            
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to send verification code'];
        }
    }
    
    private static function generateOTPEmailTemplate($otp, $userType) {
        $accountType = ucfirst($userType);
        $title = ($userType === 'admin') ? 'Admin Account' : 'User Account';
        
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f8f9fa;'>
            <div style='background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='margin: 0; font-size: 24px;'>YP4K Resort</h1>
                <p style='margin: 5px 0 0; opacity: 0.9;'>{$title} Verification</p>
            </div>
            
            <div style='background: white; padding: 30px; border-radius: 0 0 10px 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                <h2 style='color: #333; margin-bottom: 20px;'>Email Verification Required</h2>
                <p style='color: #666; line-height: 1.6;'>Thank you for registering for a {$accountType} account at YP4K Resort. To complete your registration, please use the verification code below:</p>
                
                <div style='background: #f8f9fa; border: 2px dashed #3b82f6; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px;'>
                    <p style='margin: 0 0 10px; color: #666; font-size: 14px;'>VERIFICATION CODE</p>
                    <h1 style='margin: 0; font-size: 32px; color: #3b82f6; letter-spacing: 5px; font-weight: bold;'>{$otp}</h1>
                </div>
                
                <p style='color: #666; line-height: 1.6; margin-bottom: 20px;'>This code will expire in <strong>5 minutes</strong> for security reasons.</p>
                
                <div style='background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px;'>
                    <p style='margin: 0; color: #856404; font-size: 14px;'>
                        <strong>Security Notice:</strong> Never share this code with anyone. Our staff will never ask for your verification code.
                    </p>
                </div>
                
                <hr style='border: none; border-top: 1px solid #eee; margin: 30px 0;'>
                
                <p style='color: #999; font-size: 12px; text-align: center; margin: 0;'>
                    This is an automated message from YP4K Resort. Please do not reply to this email.
                </p>
            </div>
        </div>";
    }
}
?>
