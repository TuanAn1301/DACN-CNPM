<?php
session_start();
if(!isset($_SESSION["login"])){
    header("Location: dang-nhap.php");
    die();  
}

require('../database/connect.php'); 
require('../database/query.php');
require('email-config.php');
require('EmailSender.php');

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
        
        // Chuẩn bị nội dung email
        $to = $contact['email'];
        $to_name = $contact['hoten'];
        $subject = "Phản hồi từ Pustok Bookstore - " . $contact['hoten'];
        
        // Nội dung email HTML
        $html_body = "
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
        
        // Nội dung text thuần (fallback)
        $text_body = "Xin chào " . $contact['hoten'] . ",\n\n";
        $text_body .= "Cảm ơn bạn đã liên hệ với chúng tôi. Dưới đây là phản hồi của chúng tôi:\n\n";
        $text_body .= $reply_message . "\n\n";
        $text_body .= "---\n";
        $text_body .= "Tin nhắn của bạn:\n";
        $text_body .= $contact['tinnhan'] . "\n\n";
        $text_body .= "Trân trọng,\n";
        $text_body .= "Pustok Bookstore Team\n";
        $text_body .= "📍 Hà Nội\n";
        $text_body .= "📞 0397172952\n";
        $text_body .= "📧 ntquan2711@gmail.com";
        
        // Khởi tạo EmailSender
        $mailer = new EmailSender([
            'host' => SMTP_HOST,
            'port' => SMTP_PORT,
            'username' => SMTP_USERNAME,
            'password' => SMTP_PASSWORD,
            'from_email' => SMTP_FROM_EMAIL,
            'from_name' => SMTP_FROM_NAME,
            'debug' => SMTP_DEBUG
        ]);
        
        // Gửi email
        $mail_sent = $mailer->send($to, $to_name, $subject, $html_body, $text_body);
        
        // Đánh dấu đã xử lý
        $update_sql = "UPDATE lienhe SET trangthai = 1 WHERE malienhe = $malienhe";
        $conn->query($update_sql);
        
        if ($mail_sent) {
            header("Location: lien-he.php?success=reply_sent&email=" . urlencode($to));
        } else {
            $error_detail = $mailer->getError();
            header("Location: lien-he.php?error=email_failed&detail=" . urlencode($error_detail));
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
