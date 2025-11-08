<?php
require(__DIR__.'/layouts/header.php');
error_reporting(E_ALL & ~E_NOTICE);
ini_set('error_reporting', E_ALL & ~E_NOTICE);
require('../database/connect.php'); 
require('../database/query.php');

// Đếm tổng số phản hồi
$sql_total = "SELECT COUNT(*) AS total FROM lienhe";
$total_result = queryResult($conn, $sql_total);
$total = $total_result->fetch_assoc()['total'];

// Đếm phản hồi chưa xử lý
$sql_pending = "SELECT COUNT(*) AS pending FROM lienhe WHERE trangthai = 0";
$pending_result = queryResult($conn, $sql_pending);
$pending = $pending_result->fetch_assoc()['pending'];

// Lấy danh sách phản hồi
$sql = "SELECT * FROM lienhe ORDER BY thoigian DESC";
$result = queryResult($conn, $sql);
?>

<div class="page-wrapper">
    <div class="page-breadcrumb">
        <div class="row align-items-center">
            <div class="col-5">
                <h4 class="page-title">Quản Lý Phản Hồi</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Trang Chủ</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Phản Hồi</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Success/Error Messages -->
        <?php if(isset($_GET['success'])): ?>
            <?php if($_GET['success'] == 'reply_sent'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-check-circle"></i> <strong>Thành công!</strong> Email trả lời đã được gửi đến 
                    <?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : 'khách hàng'; ?> 
                    và đánh dấu đã xử lý.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif($_GET['success'] == 'reply_saved'): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-information"></i> <strong>Đã lưu!</strong> Phản hồi đã được đánh dấu xử lý. (Email có thể không gửi được trên localhost)
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php else: ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-check-circle"></i> <strong>Thành công!</strong> Đã đánh dấu phản hồi là đã xử lý.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
        <?php elseif(isset($_GET['error'])): ?>
            <?php if($_GET['error'] == 'empty_message'): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-alert-circle"></i> <strong>Lỗi!</strong> Vui lòng nhập nội dung trả lời.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif($_GET['error'] == 'not_found'): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-alert-circle"></i> <strong>Lỗi!</strong> Không tìm thấy phản hồi.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif($_GET['error'] == 'email_failed'): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-alert-circle"></i> <strong>Lỗi gửi email!</strong> 
                    <?php 
                    if(isset($_GET['detail'])) {
                        echo htmlspecialchars($_GET['detail']);
                    } else {
                        echo "Không thể gửi email. Vui lòng kiểm tra cấu hình SMTP.";
                    }
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php else: ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-alert-circle"></i> <strong>Lỗi!</strong> Có lỗi xảy ra khi xử lý phản hồi.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <!-- Thống kê -->
        <div class="row">
            <div class="col-md-6 col-lg-6">
                <div class="card card-hover">
                    <div class="box bg-info text-center">
                        <h1 class="font-light text-white">
                            <i class="mdi mdi-email-outline"></i>
                        </h1>
                        <h6 class="text-white">Tổng Phản Hồi</h6>
                        <h2 class="text-white"><?php echo $total; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-6">
                <div class="card card-hover">
                    <div class="box bg-warning text-center">
                        <h1 class="font-light text-white">
                            <i class="mdi mdi-clock-alert"></i>
                        </h1>
                        <h6 class="text-white">Chưa Xử Lý</h6>
                        <h2 class="text-white"><?php echo $pending; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Danh sách phản hồi -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Danh Sách Phản Hồi Từ Khách Hàng</h5>
                        
                        <?php if ($result && $result->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th><b>ID</b></th>
                                            <th><b>Họ Tên</b></th>
                                            <th><b>Email</b></th>
                                            <th><b>Điện Thoại</b></th>
                                            <th><b>Tin Nhắn</b></th>
                                            <th><b>Thời Gian</b></th>
                                            <th><b>Trạng Thái</b></th>
                                            <th><b>Thao Tác</b></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $row['malienhe']; ?></td>
                                            <td><?php echo htmlspecialchars($row['hoten']); ?></td>
                                            <td>
                                                <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>">
                                                    <?php echo htmlspecialchars($row['email']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['dienthoai']); ?></td>
                                            <td>
                                                <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis;">
                                                    <?php echo nl2br(htmlspecialchars(substr($row['tinnhan'], 0, 100))); ?>
                                                    <?php if(strlen($row['tinnhan']) > 100): ?>...<?php endif; ?>
                                                </div>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($row['thoigian'])); ?></td>
                                            <td>
                                                <?php if ($row['trangthai'] == 0): ?>
                                                    <span class="badge bg-warning">Chưa xử lý</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Đã xử lý</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modal<?php echo $row['malienhe']; ?>">
                                                    <i class="mdi mdi-eye"></i> Xem
                                                </button>
                                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#replyModal<?php echo $row['malienhe']; ?>">
                                                    <i class="mdi mdi-reply"></i> Trả lời
                                                </button>
                                                <?php if ($row['trangthai'] == 0): ?>
                                                    <a href="xu-ly-lien-he.php?id=<?php echo $row['malienhe']; ?>&action=done" class="btn btn-sm btn-success">
                                                        <i class="mdi mdi-check"></i> Xử lý
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>

                                        <!-- Modal chi tiết -->
                                        <div class="modal fade" id="modal<?php echo $row['malienhe']; ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Chi Tiết Phản Hồi #<?php echo $row['malienhe']; ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <table class="table">
                                                            <tr>
                                                                <th width="150">Họ tên:</th>
                                                                <td><?php echo htmlspecialchars($row['hoten']); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Email:</th>
                                                                <td><a href="mailto:<?php echo htmlspecialchars($row['email']); ?>"><?php echo htmlspecialchars($row['email']); ?></a></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Điện thoại:</th>
                                                                <td><?php echo htmlspecialchars($row['dienthoai']); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Thời gian:</th>
                                                                <td><?php echo date('d/m/Y H:i:s', strtotime($row['thoigian'])); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Trạng thái:</th>
                                                                <td>
                                                                    <?php if ($row['trangthai'] == 0): ?>
                                                                        <span class="badge bg-warning">Chưa xử lý</span>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-success">Đã xử lý</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th>Tin nhắn:</th>
                                                                <td><?php echo nl2br(htmlspecialchars($row['tinnhan'])); ?></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#replyModal<?php echo $row['malienhe']; ?>">
                                                            <i class="mdi mdi-reply"></i> Trả lời Email
                                                        </button>
                                                        <?php if ($row['trangthai'] == 0): ?>
                                                            <a href="xu-ly-lien-he.php?id=<?php echo $row['malienhe']; ?>&action=done" class="btn btn-success">
                                                                <i class="mdi mdi-check"></i> Đánh dấu đã xử lý
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal trả lời -->
                                        <div class="modal fade" id="replyModal<?php echo $row['malienhe']; ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title">
                                                            <i class="mdi mdi-reply"></i> Trả Lời Phản Hồi #<?php echo $row['malienhe']; ?>
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="tra-loi-lien-he.php" method="POST">
                                                        <input type="hidden" name="malienhe" value="<?php echo $row['malienhe']; ?>">
                                                        <div class="modal-body">
                                                            <!-- Thông tin khách hàng -->
                                                            <div class="alert alert-info">
                                                                <h6><i class="mdi mdi-information"></i> Thông Tin Khách Hàng</h6>
                                                                <p class="mb-1"><strong>Họ tên:</strong> <?php echo htmlspecialchars($row['hoten']); ?></p>
                                                                <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?></p>
                                                                <p class="mb-0"><strong>Điện thoại:</strong> <?php echo htmlspecialchars($row['dienthoai']); ?></p>
                                                            </div>

                                                            <!-- Tin nhắn gốc -->
                                                            <div class="mb-3">
                                                                <label class="form-label"><strong>Tin nhắn của khách hàng:</strong></label>
                                                                <div class="p-3 bg-light border rounded">
                                                                    <?php echo nl2br(htmlspecialchars($row['tinnhan'])); ?>
                                                                </div>
                                                            </div>

                                                            <!-- Form trả lời -->
                                                            <div class="mb-3">
                                                                <label for="reply_message<?php echo $row['malienhe']; ?>" class="form-label">
                                                                    <strong>Nội dung trả lời: <span class="text-danger">*</span></strong>
                                                                </label>
                                                                <textarea 
                                                                    class="form-control" 
                                                                    id="reply_message<?php echo $row['malienhe']; ?>" 
                                                                    name="reply_message" 
                                                                    rows="8" 
                                                                    placeholder="Nhập nội dung trả lời cho khách hàng..." 
                                                                    required></textarea>
                                                                <small class="text-muted">
                                                                    <i class="mdi mdi-information"></i> Email sẽ được gửi từ: <strong>ntquan2711@gmail.com</strong>
                                                                </small>
                                                            </div>

                                                            <div class="alert alert-warning">
                                                                <i class="mdi mdi-alert"></i> <strong>Lưu ý:</strong> 
                                                                Email sẽ được gửi đến <strong><?php echo htmlspecialchars($row['email']); ?></strong> 
                                                                và phản hồi sẽ tự động được đánh dấu là đã xử lý.
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                                <i class="mdi mdi-close"></i> Hủy
                                                            </button>
                                                            <button type="submit" class="btn btn-primary">
                                                                <i class="mdi mdi-send"></i> Gửi Email Trả Lời
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="mdi mdi-information"></i> Chưa có phản hồi nào từ khách hàng.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require(__DIR__.'/layouts/footer.php'); ?>
</div>
