<?php
// Kết nối database
require_once('../database/connect.php');
require_once('../database/query.php');

// ==== CẤU HÌNH EMAIL ====
$enable_email = false; // Tắt gửi email thông báo đến admin
$gmail_email = "ntquan2711@gmail.com";
$gmail_app_password = "frrqcrckauexzuhz"; // App Password (không có dấu cách)
// ==== HẾT CẤU HÌNH ====

// Kiểm tra nếu form được submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Lấy và làm sạch dữ liệu từ form
    $name = isset($_POST['con_name']) ? strip_tags(trim($_POST['con_name'])) : '';
    $email = isset($_POST['con_email']) ? filter_var(trim($_POST['con_email']), FILTER_SANITIZE_EMAIL) : '';
    $phone = isset($_POST['con_phone']) ? strip_tags(trim($_POST['con_phone'])) : '';
    $message = isset($_POST['con_message']) ? strip_tags(trim($_POST['con_message'])) : '';
    
    // Validate dữ liệu
    $errors = array();
    
    if (empty($name)) {
        $errors[] = "Vui lòng nhập họ tên";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Vui lòng nhập email hợp lệ";
    }
    
    if (empty($message)) {
        $errors[] = "Vui lòng nhập tin nhắn";
    }
    
    // Nếu không có lỗi, lưu vào database
    if (empty($errors)) {
        
        // Escape dữ liệu để tránh SQL injection
        $name_escaped = $conn->real_escape_string($name);
        $email_escaped = $conn->real_escape_string($email);
        $phone_escaped = $conn->real_escape_string($phone);
        $message_escaped = $conn->real_escape_string($message);
        
        // Lưu vào database
        $sql = "INSERT INTO lienhe (hoten, email, dienthoai, tinnhan, thoigian, trangthai) 
                VALUES ('$name_escaped', '$email_escaped', '$phone_escaped', '$message_escaped', NOW(), 0)";
        
        if ($conn->query($sql)) {
            // Gửi email thông báo cho admin (nếu đã cấu hình)
            if ($enable_email && !empty($gmail_app_password)) {
                $to = $gmail_email;
                $subject = "[Pustok] Phản hồi mới từ website - " . $name;
                $email_content = "<html><body>";
                $email_content .= "<h2>🔔 Bạn có phản hồi mới từ Pustok Bookstore!</h2>";
                $email_content .= "<p><strong>Họ tên:</strong> $name</p>";
                $email_content .= "<p><strong>Email:</strong> $email</p>";
                $email_content .= "<p><strong>Số điện thoại:</strong> $phone</p>";
                $email_content .= "<p><strong>Tin nhắn:</strong></p>";
                $email_content .= "<p>" . nl2br(htmlspecialchars($message)) . "</p>";
                $email_content .= "<hr>";
                $email_content .= "<p><small>Vui lòng đăng nhập vào admin để xem chi tiết.</small></p>";
                $email_content .= "</body></html>";
                
                $headers = "From: Website Pustok <noreply@pustok.com>\r\n";
                $headers .= "Reply-To: $email\r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                
                // Thử gửi email
                @mail($to, $subject, $email_content, $headers);
            }
            
            // Chuyển hướng với thông báo thành công
            header("Location: ../lien-he.php?status=success");
            exit();
        } else {
            // Lỗi database
            $errors[] = "Có lỗi xảy ra khi lưu dữ liệu. Vui lòng thử lại!";
        }
    }
    
    // Có lỗi, chuyển về trang liên hệ với thông báo lỗi
    if (!empty($errors)) {
        $error_message = implode(", ", $errors);
        header("Location: ../lien-he.php?status=error&message=" . urlencode($error_message));
        exit();
    }
    
} else {
    // Nếu không phải POST request, chuyển về trang chủ
    header("Location: ../index.php");
    exit();
}
?>
