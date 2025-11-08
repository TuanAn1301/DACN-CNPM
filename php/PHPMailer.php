<?php
/**
 * PHPMailer Simple - Gửi email qua Gmail SMTP
 */
class SimpleMailer {
    
    public static function send($to, $subject, $message, $from_email, $from_name = '') {
        
        // Cấu hình Gmail SMTP
        $smtp_host = 'smtp.gmail.com';
        $smtp_port = 587;
        $smtp_username = 'ntquan2711@gmail.com'; // Email Gmail của bạn
        $smtp_password = ''; // App Password của Gmail (không phải mật khẩu đăng nhập)
        
        // Headers
        $headers = "From: " . ($from_name ? $from_name : $from_email) . " <" . $smtp_username . ">\r\n";
        $headers .= "Reply-To: " . $from_email . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        // Thử gửi bằng mail() function
        // Lưu ý: Trên localhost thường không hoạt động
        $result = @mail($to, $subject, $message, $headers);
        
        return $result;
    }
}
?>
