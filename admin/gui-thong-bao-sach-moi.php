<?php
/**
 * Hàm gửi thông báo sách mới đến tất cả email đăng ký
 * Gọi hàm này khi admin thêm sách mới
 */
if (!isset($conn)) {
    require_once('../database/connect.php');
}
require_once('../database/query.php');
require_once('email-config.php');
require_once('EmailSender.php');

function guiThongBaoSachMoi($masanpham, $tensanpham, $giaban, $anhchinh, $mota = '') {
    global $conn;
    try {
        // Lấy danh sách email đã đăng ký (chỉ những email đang kích hoạt)
        $sql = "SELECT email FROM dangkynhantin WHERE trangthai = 1";
        $result = queryResult($conn, $sql);
        
        if (!$result || $result->num_rows == 0) {
            return ['success' => true, 'sent' => 0, 'message' => 'Không có email nào đăng ký'];
        }
        
        // Khởi tạo EmailSender
        $emailConfig = [
            'host' => SMTP_HOST,
            'port' => SMTP_PORT,
            'username' => SMTP_USERNAME,
            'password' => SMTP_PASSWORD,
            'from_email' => SMTP_FROM_EMAIL,
            'from_name' => 'PusTok', // Tên người gửi là PusTok
            'debug' => SMTP_DEBUG
        ];
        
        $emailSender = new EmailSender($emailConfig);
        
        // Xây dựng nội dung email
        $subject = "📚 Sách Mới: " . $tensanpham;
        
        $html_body = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #62ab00; color: white; padding: 20px; text-align: center; }
                .content { background-color: #f9f9f9; padding: 20px; }
                .product { background-color: white; padding: 20px; margin: 20px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
                .product-img { max-width: 100%; height: auto; border-radius: 5px; }
                .product-title { font-size: 24px; color: #62ab00; margin: 15px 0; }
                .product-price { font-size: 20px; font-weight: bold; color: #c62828; }
                .btn { display: inline-block; padding: 12px 30px; background-color: #62ab00; color: white; text-decoration: none; border-radius: 5px; margin-top: 15px; }
                .btn:hover { background-color: #4d8700; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>📚 PusTok - Thông Báo Sách Mới</h1>
                </div>
                <div class='content'>
                    <h2>Xin chào!</h2>
                    <p>Chúng tôi vui mừng thông báo cho bạn biết về một cuốn sách mới được thêm vào cửa hàng PusTok:</p>
                    
                    <div class='product'>
                        <img src='http://localhost/webbansach/" . htmlspecialchars($anhchinh) . "' alt='" . htmlspecialchars($tensanpham) . "' class='product-img'>
                        <h3 class='product-title'>" . htmlspecialchars($tensanpham) . "</h3>
                        <p class='product-price'>Giá: " . number_format($giaban) . "đ</p>";
        
        if (!empty($mota)) {
            $html_body .= "<p>" . nl2br(htmlspecialchars($mota)) . "</p>";
        }
        
        $html_body .= "
                        <a href='http://localhost/webbansach/san-pham.php?id=" . $masanpham . "' class='btn'>Xem Chi Tiết</a>
                    </div>
                    
                    <p>Cảm ơn bạn đã quan tâm đến PusTok!</p>
                </div>
                <div class='footer'>
                    <p>Email này được gửi từ PusTok Bookstore</p>
                    <p>Để hủy đăng ký, vui lòng liên hệ: ntquan2711@gmail.com</p>
                </div>
            </div>
        </body>
        </html>";
        
        $text_body = "Sách Mới: " . $tensanpham . "\n";
        $text_body .= "Giá: " . number_format($giaban) . "đ\n";
        if (!empty($mota)) {
            $text_body .= "Mô tả: " . strip_tags($mota) . "\n";
        }
        $text_body .= "\nXem chi tiết: http://localhost/webbansach/san-pham.php?id=" . $masanpham;
        
        // Gửi email đến tất cả người đăng ký
        $sent_count = 0;
        $error_count = 0;
        
        while ($row = $result->fetch_assoc()) {
            $to_email = $row['email'];
            if ($emailSender->send($to_email, $to_email, $subject, $html_body, $text_body)) {
                $sent_count++;
            } else {
                $error_count++;
                error_log("Lỗi gửi email đến " . $to_email . ": " . $emailSender->getError());
            }
        }
        
        return [
            'success' => true,
            'sent' => $sent_count,
            'errors' => $error_count,
            'message' => "Đã gửi thông báo đến $sent_count email" . ($error_count > 0 ? " ($error_count lỗi)" : "")
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'sent' => 0,
            'message' => 'Lỗi: ' . $e->getMessage()
        ];
    }
}
?>

