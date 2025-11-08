<?php
session_start();
if(!isset($_SESSION["login"])){
    header("Location: dang-nhap.php");
    die();  
}

require('../database/connect.php'); 
require('../database/query.php');

// PHPMailer will be used only if the namespaced library is actually available
// (either via Composer vendor autoload or proper library files)

$vendorAutoload = __DIR__ . '/../vendor/autoload.php';
$localException = __DIR__ . '/../php/Exception.php';
$localPHPMailer = __DIR__ . '/../php/PHPMailer.php';
$localSMTP = __DIR__ . '/../php/SMTP.php';
$localAutoload = __DIR__ . '/../php/PHPMailerAutoload.php';
$use_phpmailer = false;
if (file_exists($vendorAutoload)) { require_once $vendorAutoload; $use_phpmailer = true; }
elseif (file_exists($localAutoload)) { require_once $localAutoload; $use_phpmailer = true; }
elseif (file_exists($localException) && file_exists($localPHPMailer) && file_exists($localSMTP)) {
    require_once $localException;
    require_once $localPHPMailer;
    require_once $localSMTP;
    $use_phpmailer = true;
}
// Ensure we only use PHPMailer when the namespaced class exists
if ($use_phpmailer && !class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
    $use_phpmailer = false;
}

// Cấu hình email
$admin_email = "ntquan2711@gmail.com";
$admin_password = "frrqcrckauexzuhz"; // App Password từ Gmail
$admin_name = "Pustok Bookstore";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $malienhe = (int)$_POST['malienhe'];
    $reply_message = trim($_POST['reply_message']);
    
    // Lấy thông tin phản hồi gốc
    $sql = "SELECT * FROM lienhe WHERE malienhe = $malienhe";
    $result = queryResult($conn, $sql);
    
    if ($result && $result->num_rows > 0) {
        $contact = $result->fetch_assoc();
        
        // Validate
        if (empty($reply_message)) {
            header("Location: lien-he.php?error=empty_message");
            exit();
        }
        
        $mail_sent = false;
        $error_message = '';
        
        if ($use_phpmailer) {
            $PHPMailerClass = '\\PHPMailer\\PHPMailer\\PHPMailer';
            $mail = new $PHPMailerClass(true);
            
            try {
                // Cấu hình SMTP
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = $admin_email;
                $mail->Password = $admin_password;
                $mail->SMTPSecure = constant($PHPMailerClass . '::ENCRYPTION_STARTTLS');
                $mail->Port = 587;
                $mail->CharSet = 'UTF-8';
                
                // Người gửi
                $mail->setFrom($admin_email, $admin_name);
                $mail->addReplyTo($admin_email, $admin_name);
                
                // Người nhận
                $mail->addAddress($contact['email'], $contact['hoten']);
                
                // Nội dung email
                $mail->isHTML(true);
                $mail->Subject = "Phản hồi từ Pustok Bookstore - " . $contact['hoten'];
                
                // Tạo nội dung HTML
                $email_content = "
                <html>
                <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <div style='max-width: 600px; margin: 0 auto; padding: 20px; background: #f8f8f8;'>
                        <div style='background: #62ab00; padding: 20px; text-align: center;'>
                            <h1 style='color: white; margin: 0;'>Pustok Bookstore</h1>
                        </div>
                        <div style='background: white; padding: 30px; margin-top: 20px; border-radius: 5px;'>
                            <h2 style='color: #62ab00;'>Xin chào " . htmlspecialchars($contact['hoten']) . ",</h2>
                            <p>Cảm ơn bạn đã liên hệ với chúng tôi. Dưới đây là phản hồi của chúng tôi:</p>
                            <div style='background: #f8f8f8; padding: 20px; border-left: 4px solid #62ab00; margin: 20px 0;'>
                                " . nl2br(htmlspecialchars($reply_message)) . "
                            </div>
                            <hr style='border: none; border-top: 1px solid #ddd; margin: 30px 0;'>
                            <p style='color: #666; font-size: 14px;'><strong>Tin nhắn của bạn:</strong></p>
                            <div style='background: #f8f8f8; padding: 15px; border-radius: 5px;'>
                                <p style='color: #666; font-size: 14px; margin: 0;'>" . nl2br(htmlspecialchars($contact['tinnhan'])) . "</p>
                            </div>
                            <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;'>
                                <p style='color: #666; font-size: 14px;'>Trân trọng,<br><strong>Pustok Bookstore Team</strong></p>
                                <p style='color: #999; font-size: 12px;'>
                                    📍 Hà Nội<br>
                                    📞 0397172952<br>
                                    📧 ntquan2711@gmail.com
                                </p>
                            </div>
                        </div>
                    </div>
                </body>
                </html>";
                
                $mail->Body = $email_content;
                $mail->AltBody = strip_tags($reply_message);
                
                // Gửi email
                $mail->send();
                $mail_sent = true;
                
            } catch (\Throwable $e) {
                $error_message = $e->getMessage();
                $mail_sent = false;
            }
            
        } else {
            // Fallback: Sử dụng hàm mail() thông thường (có thể không hoạt động trên localhost)
            $to = $contact['email'];
            $subject = "Phản hồi từ Pustok Bookstore - " . $contact['hoten'];
            
            $email_content = "<html><body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>";
            $email_content .= "<div style='max-width: 600px; margin: 0 auto; padding: 20px; background: #f8f8f8;'>";
            $email_content .= "<div style='background: #62ab00; padding: 20px; text-align: center;'>";
            $email_content .= "<h1 style='color: white; margin: 0;'>Pustok Bookstore</h1>";
            $email_content .= "</div>";
            $email_content .= "<div style='background: white; padding: 30px; margin-top: 20px; border-radius: 5px;'>";
            $email_content .= "<h2 style='color: #62ab00;'>Xin chào " . htmlspecialchars($contact['hoten']) . ",</h2>";
            $email_content .= "<p>Cảm ơn bạn đã liên hệ với chúng tôi. Dưới đây là phản hồi của chúng tôi:</p>";
            $email_content .= "<div style='background: #f8f8f8; padding: 20px; border-left: 4px solid #62ab00; margin: 20px 0;'>";
            $email_content .= nl2br(htmlspecialchars($reply_message));
            $email_content .= "</div>";
            $email_content .= "<hr style='border: none; border-top: 1px solid #ddd; margin: 30px 0;'>";
            $email_content .= "<p style='color: #666; font-size: 14px;'><strong>Tin nhắn của bạn:</strong></p>";
            $email_content .= "<div style='background: #f8f8f8; padding: 15px; border-radius: 5px;'>";
            $email_content .= "<p style='color: #666; font-size: 14px; margin: 0;'>" . nl2br(htmlspecialchars($contact['tinnhan'])) . "</p>";
            $email_content .= "</div>";
            $email_content .= "<div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;'>";
            $email_content .= "<p style='color: #666; font-size: 14px;'>Trân trọng,<br><strong>Pustok Bookstore Team</strong></p>";
            $email_content .= "<p style='color: #999; font-size: 12px;'>";
            $email_content .= "📍 Hà Nội<br>";
            $email_content .= "📞 0397172952<br>";
            $email_content .= "📧 ntquan2711@gmail.com";
            $email_content .= "</p>";
            $email_content .= "</div>";
            $email_content .= "</div>";
            $email_content .= "</div>";
            $email_content .= "</body></html>";
            
            $headers = "From: " . $admin_name . " <" . $admin_email . ">\r\n";
            $headers .= "Reply-To: " . $admin_email . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            
            $mail_sent = @mail($to, $subject, $email_content, $headers);
        }
        
        // Đánh dấu đã xử lý
        $update_sql = "UPDATE lienhe SET trangthai = 1 WHERE malienhe = $malienhe";
        $conn->query($update_sql);
        
        if ($mail_sent) {
            header("Location: lien-he.php?success=reply_sent&email=" . urlencode($contact['email']));
        } else {
            // Lưu thông tin để debug
            header("Location: lien-he.php?success=reply_saved&error_detail=" . urlencode($error_message));
        }
        exit();
    } else {
        header("Location: lien-he.php?error=not_found");
        exit();
    }
} else {
    header("Location: lien-he.php");
    exit();
}
?>
