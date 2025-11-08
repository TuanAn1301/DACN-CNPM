<?php
/**
 * Cấu hình Email cho hệ thống
 * File này chứa thông tin cấu hình SMTP để gửi email
 */

// Cấu hình Gmail SMTP
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls'); // tls hoặc ssl
define('SMTP_USERNAME', 'ntquan2711@gmail.com'); // Email Gmail của bạn
define('SMTP_PASSWORD', 'frrqcrckauexzuhz'); // App Password từ Gmail (16 ký tự)
define('SMTP_FROM_EMAIL', 'ntquan2711@gmail.com');
define('SMTP_FROM_NAME', 'PusTok');

// Debug mode (bật để xem lỗi chi tiết)
define('SMTP_DEBUG', 0); // 0 = tắt, 1 = bật (chỉ dùng khi test)

/**
 * Hướng dẫn lấy App Password từ Gmail:
 * 1. Đăng nhập Gmail
 * 2. Vào https://myaccount.google.com/security
 * 3. Bật "2-Step Verification" (Xác minh 2 bước)
 * 4. Vào "App passwords" (Mật khẩu ứng dụng)
 * 5. Chọn "Mail" và "Other (Custom name)"
 * 6. Nhập tên (ví dụ: "Pustok Website")
 * 7. Copy mật khẩu 16 ký tự và dán vào SMTP_PASSWORD ở trên
 */
?>
