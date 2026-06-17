<?php
/*
 * Email Helper Class using PHPMailer
 * YP4K Resort - Booking & Trail Guide System
 * 
 * IMPORTANT: Configure your email settings below
 */

// Include PHPMailer
require_once __DIR__ . '/../phpAuthentication/PHPMailer/srs/PHPMailer.php';
require_once __DIR__ . '/../phpAuthentication/PHPMailer/srs/SMTP.php';
require_once __DIR__ . '/../phpAuthentication/PHPMailer/srs/Exception.php';

class EmailHelper {
    
    // Email configuration - UPDATE THESE VALUES
    private static $emailConfig = [
        'host' => 'smtp.gmail.com',           // Your SMTP server
        'username' => 'yp4kvillaberderesort@gmail.com', // Your email address
        'password' => 'nlujincybvvryxhq',    // Your app password (not regular password)
        'from_email' => 'yp4kvillaberderesort@gmail.com', // From email address
        'from_name' => 'YP4K Resort',         // From name
        'port' => 587,                        // SMTP port
        'encryption' => 'tls'                 // 'tls' or 'ssl'
    ];
    
    /**
     * Send OTP verification email
     */
    public static function sendOTPEmail($email, $otp, $userType = 'user', $firstName = '') {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = self::$emailConfig['host'];
            $mail->SMTPAuth = true;
            $mail->Username = self::$emailConfig['username'];
            $mail->Password = self::$emailConfig['password'];
            $mail->SMTPSecure = self::$emailConfig['encryption'] === 'ssl' ? 
                               PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : 
                               PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = self::$emailConfig['port'];
            
            // Recipients
            $mail->setFrom(self::$emailConfig['from_email'], self::$emailConfig['from_name']);
            $mail->addAddress($email);
            
            // Content
            $subject = ($userType === 'admin') ? 'Admin Registration - Email Verification' : 'User Registration - Email Verification';
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body = self::generateOTPEmailTemplate($otp, $userType, $firstName);
            $mail->AltBody = "Your verification code is: $otp\n\nThis code will expire in 5 minutes.";
            
            $mail->send();
            
            return ['success' => true, 'message' => 'Verification code sent successfully'];
            
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to send verification code: ' . $e->getMessage()];
        }
    }
    
    /**
     * Send password reset email
     */
    public static function sendPasswordResetEmail($email, $resetToken, $userType = 'user', $firstName = '') {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = self::$emailConfig['host'];
            $mail->SMTPAuth = true;
            $mail->Username = self::$emailConfig['username'];
            $mail->Password = self::$emailConfig['password'];
            $mail->SMTPSecure = self::$emailConfig['encryption'] === 'ssl' ? 
                               PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : 
                               PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = self::$emailConfig['port'];
            
            // Recipients
            $mail->setFrom(self::$emailConfig['from_email'], self::$emailConfig['from_name']);
            $mail->addAddress($email);
            
            // Content
            $subject = ($userType === 'admin') ? 'Admin Password Reset' : 'User Password Reset';
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body = self::generatePasswordResetEmailTemplate($resetToken, $userType, $firstName);
            $mail->AltBody = "Click the following link to reset your password: http://your-domain.com/reset_password.php?token=" . $resetToken;
            
            $mail->send();
            
            return ['success' => true, 'message' => 'Password reset link sent successfully'];
            
        } catch (Exception $e) {
            error_log("Password reset email failed: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to send password reset email: ' . $e->getMessage()];
        }
    }
    
    /**
     * Generate OTP email template
     */
    private static function generateOTPEmailTemplate($otp, $userType, $firstName) {
        $accountType = ucfirst($userType);
        $title = ($userType === 'admin') ? 'Admin Account' : 'User Account';
        $greeting = $firstName ? "Dear $firstName," : "Hello,";
        
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f8f9fa;'>
            <div style='background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='margin: 0; font-size: 24px;'>YP4K Resort</h1>
                <p style='margin: 5px 0 0; opacity: 0.9;'>{$title} Verification</p>
            </div>
            
            <div style='background: white; padding: 30px; border-radius: 0 0 10px 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                <p style='color: #333; margin-bottom: 20px;'>{$greeting}</p>
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
    
    /**
     * Generate password reset email template
     */
    private static function generatePasswordResetEmailTemplate($resetToken, $userType, $firstName) {
        $accountType = ucfirst($userType);
        $greeting = $firstName ? "Dear $firstName," : "Hello,";
        $resetLink = "http://your-domain.com/reset_password.php?token=" . urlencode($resetToken);
        
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f8f9fa;'>
            <div style='background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='margin: 0; font-size: 24px;'>YP4K Resort</h1>
                <p style='margin: 5px 0 0; opacity: 0.9;'>Password Reset</p>
            </div>
            
            <div style='background: white; padding: 30px; border-radius: 0 0 10px 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                <p style='color: #333; margin-bottom: 20px;'>{$greeting}</p>
                <p style='color: #666; line-height: 1.6;'>We received a request to reset the password for your {$accountType} account at YP4K Resort. Click the button below to reset your password:</p>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$resetLink}' style='background: #3b82f6; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>Reset Password</a>
                </div>
                
                <p style='color: #666; line-height: 1.6; margin-bottom: 20px;'>Or copy and paste this link into your browser:</p>
                <p style='background: #f8f9fa; padding: 10px; word-break: break-all; font-size: 12px; color: #333;'>{$resetLink}</p>
                
                <p style='color: #666; line-height: 1.6; margin-bottom: 20px;'>This link will expire in <strong>1 hour</strong> for security reasons.</p>
                
                <div style='background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px;'>
                    <p style='margin: 0; color: #856404; font-size: 14px;'>
                        <strong>Security Notice:</strong> If you didn't request this password reset, please ignore this email. Your password will remain unchanged.
                    </p>
                </div>
                
                <hr style='border: none; border-top: 1px solid #eee; margin: 30px 0;'>
                
                <p style='color: #999; font-size: 12px; text-align: center; margin: 0;'>
                    This is an automated message from YP4K Resort. Please do not reply to this email.
                </p>
            </div>
        </div>";
    }
    
    /**
     * Test email configuration
     */
    public static function testEmailConfig() {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = self::$emailConfig['host'];
            $mail->SMTPAuth = true;
            $mail->Username = self::$emailConfig['username'];
            $mail->Password = self::$emailConfig['password'];
            $mail->SMTPSecure = self::$emailConfig['encryption'] === 'ssl' ? 
                               PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : 
                               PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = self::$emailConfig['port'];
            
            return ['success' => true, 'message' => 'Email configuration is valid'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Email configuration error: ' . $e->getMessage()];
        }
    }
}
?>
