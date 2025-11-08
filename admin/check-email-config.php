<?php
/**
 * Kiểm tra cấu hình email
 * File này giúp kiểm tra xem hệ thống có sẵn sàng gửi email không
 */

session_start();
if(!isset($_SESSION["login"])){
    header("Location: dang-nhap.php");
    die();  
}

require('email-config.php');

$checks = [];
$all_ok = true;

// 1. Kiểm tra fsockopen
if (function_exists('fsockopen')) {
    $checks[] = ['name' => 'fsockopen function', 'status' => true, 'message' => 'Có sẵn'];
} else {
    $checks[] = ['name' => 'fsockopen function', 'status' => false, 'message' => 'Không có - Cần bật trong php.ini'];
    $all_ok = false;
}

// 2. Kiểm tra OpenSSL
if (extension_loaded('openssl')) {
    $checks[] = ['name' => 'OpenSSL extension', 'status' => true, 'message' => 'Đã bật'];
} else {
    $checks[] = ['name' => 'OpenSSL extension', 'status' => false, 'message' => 'Chưa bật - Cần bật trong php.ini'];
    $all_ok = false;
}

// 3. Kiểm tra cấu hình SMTP
if (!empty(SMTP_USERNAME)) {
    $checks[] = ['name' => 'SMTP Username', 'status' => true, 'message' => SMTP_USERNAME];
} else {
    $checks[] = ['name' => 'SMTP Username', 'status' => false, 'message' => 'Chưa cấu hình'];
    $all_ok = false;
}

// 4. Kiểm tra password
if (!empty(SMTP_PASSWORD) && strlen(SMTP_PASSWORD) >= 16) {
    $checks[] = ['name' => 'SMTP Password', 'status' => true, 'message' => 'Đã cấu hình (' . strlen(SMTP_PASSWORD) . ' ký tự)'];
} else {
    $checks[] = ['name' => 'SMTP Password', 'status' => false, 'message' => 'Chưa cấu hình hoặc không đúng (cần 16 ký tự App Password)'];
    $all_ok = false;
}

// 5. Kiểm tra file EmailSender
if (file_exists('EmailSender.php')) {
    $checks[] = ['name' => 'EmailSender.php', 'status' => true, 'message' => 'File tồn tại'];
} else {
    $checks[] = ['name' => 'EmailSender.php', 'status' => false, 'message' => 'File không tồn tại'];
    $all_ok = false;
}

// 6. Test kết nối SMTP (không gửi email)
$socket_test = @fsockopen(SMTP_HOST, SMTP_PORT, $errno, $errstr, 5);
if ($socket_test) {
    fclose($socket_test);
    $checks[] = ['name' => 'Kết nối SMTP', 'status' => true, 'message' => 'Có thể kết nối đến ' . SMTP_HOST . ':' . SMTP_PORT];
} else {
    $checks[] = ['name' => 'Kết nối SMTP', 'status' => false, 'message' => "Không thể kết nối: $errstr ($errno) - Kiểm tra Firewall"];
    $all_ok = false;
}

require(__DIR__.'/layouts/header.php');
?>

<div class="page-wrapper">
    <div class="page-breadcrumb">
        <div class="row align-items-center">
            <div class="col-5">
                <h4 class="page-title">Kiểm Tra Cấu Hình Email</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Trang Chủ</a></li>
                            <li class="breadcrumb-item"><a href="lien-he.php">Phản Hồi</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Kiểm Tra Email</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Kết Quả Kiểm Tra</h5>
                        
                        <?php if ($all_ok): ?>
                            <div class="alert alert-success">
                                <h5><i class="mdi mdi-check-circle"></i> Tất cả đều OK!</h5>
                                <p class="mb-0">Hệ thống đã sẵn sàng gửi email. Bạn có thể test bằng cách:</p>
                                <ul class="mb-0">
                                    <li>Vào <a href="test-email.php">trang test email</a></li>
                                    <li>Hoặc trả lời tin nhắn thật trong <a href="lien-he.php">Quản Lý Phản Hồi</a></li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <h5><i class="mdi mdi-alert-circle"></i> Có vấn đề cần khắc phục!</h5>
                                <p class="mb-0">Vui lòng kiểm tra các mục bên dưới và sửa lỗi.</p>
                            </div>
                        <?php endif; ?>
                        
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="40">Trạng thái</th>
                                    <th width="250">Mục kiểm tra</th>
                                    <th>Chi tiết</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($checks as $check): ?>
                                <tr>
                                    <td class="text-center">
                                        <?php if ($check['status']): ?>
                                            <i class="mdi mdi-check-circle text-success" style="font-size: 24px;"></i>
                                        <?php else: ?>
                                            <i class="mdi mdi-close-circle text-danger" style="font-size: 24px;"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?php echo $check['name']; ?></strong></td>
                                    <td><?php echo $check['message']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <div class="mt-3">
                            <a href="test-email.php" class="btn btn-primary">
                                <i class="mdi mdi-send"></i> Test Gửi Email
                            </a>
                            <a href="lien-he.php" class="btn btn-secondary">
                                <i class="mdi mdi-arrow-left"></i> Quản Lý Phản Hồi
                            </a>
                            <button onclick="location.reload()" class="btn btn-info">
                                <i class="mdi mdi-refresh"></i> Kiểm tra lại
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Hướng Dẫn Khắc Phục</h5>
                        
                        <div class="accordion" id="fixAccordion">
                            <!-- fsockopen -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#fix1">
                                        Bật fsockopen
                                    </button>
                                </h2>
                                <div id="fix1" class="accordion-collapse collapse" data-bs-parent="#fixAccordion">
                                    <div class="accordion-body">
                                        <small>
                                            1. Mở file <code>php.ini</code><br>
                                            2. Tìm: <code>allow_url_fopen</code><br>
                                            3. Đổi thành: <code>allow_url_fopen = On</code><br>
                                            4. Restart Apache
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- OpenSSL -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#fix2">
                                        Bật OpenSSL
                                    </button>
                                </h2>
                                <div id="fix2" class="accordion-collapse collapse" data-bs-parent="#fixAccordion">
                                    <div class="accordion-body">
                                        <small>
                                            1. Mở file <code>php.ini</code><br>
                                            2. Tìm: <code>;extension=openssl</code><br>
                                            3. Bỏ dấu <code>;</code> ở đầu<br>
                                            4. Restart Apache
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- App Password -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#fix3">
                                        Lấy App Password
                                    </button>
                                </h2>
                                <div id="fix3" class="accordion-collapse collapse" data-bs-parent="#fixAccordion">
                                    <div class="accordion-body">
                                        <small>
                                            1. Vào: <a href="https://myaccount.google.com/security" target="_blank">Google Security</a><br>
                                            2. Bật "2-Step Verification"<br>
                                            3. Vào: <a href="https://myaccount.google.com/apppasswords" target="_blank">App Passwords</a><br>
                                            4. Tạo mật khẩu cho "Mail"<br>
                                            5. Copy 16 ký tự<br>
                                            6. Dán vào <code>email-config.php</code>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Firewall -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#fix4">
                                        Sửa lỗi Firewall
                                    </button>
                                </h2>
                                <div id="fix4" class="accordion-collapse collapse" data-bs-parent="#fixAccordion">
                                    <div class="accordion-body">
                                        <small>
                                            1. Tắt tạm Firewall/Antivirus<br>
                                            2. Test lại<br>
                                            3. Nếu OK, thêm rule cho phép:<br>
                                            - Program: <code>php.exe</code><br>
                                            - Port: <code>587</code><br>
                                            - Protocol: <code>TCP</code>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-body">
                        <h5 class="card-title">Thông Tin Hệ Thống</h5>
                        <table class="table table-sm">
                            <tr>
                                <th>PHP Version:</th>
                                <td><?php echo phpversion(); ?></td>
                            </tr>
                            <tr>
                                <th>Server:</th>
                                <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></td>
                            </tr>
                            <tr>
                                <th>OS:</th>
                                <td><?php echo PHP_OS; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require(__DIR__.'/layouts/footer.php'); ?>
</div>
