<?php
// Xử lý đăng ký nhận thông báo sách mới
require_once('../database/connect.php');
require_once('../database/query.php');

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
    
    // Validate email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Email không hợp lệ!'
        ]);
        exit;
    }
    
    // Escape để tránh SQL injection
    $email_escaped = $conn->real_escape_string($email);
    
    // Kiểm tra xem email đã đăng ký chưa
    $sql_check = "SELECT * FROM dangkynhantin WHERE email = '$email_escaped'";
    $result_check = queryResult($conn, $sql_check);
    
    if ($result_check && $result_check->num_rows > 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'Email này đã được đăng ký rồi!'
        ]);
        exit;
    }
    
    // Lưu email vào database
    $sql_insert = "INSERT INTO dangkynhantin (email, ngaydangky, trangthai) 
                   VALUES ('$email_escaped', NOW(), 1)";
    
    if (queryExecute($conn, $sql_insert)) {
        echo json_encode([
            'success' => true, 
            'message' => 'Đăng ký nhận thông báo thành công!'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Có lỗi xảy ra! Vui lòng thử lại.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Phương thức không hợp lệ!'
    ]);
}
?>

