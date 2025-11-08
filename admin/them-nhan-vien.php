<?php
require(__DIR__.'/layouts/header.php');
require('../database/connect.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$editing = $id > 0;
$nv = [
  'taikhoan' => '',
  'hoten' => '',
  'sodienthoai' => '',
  'diachi' => ''
];

if ($editing) {
    $stmt = $conn->prepare('SELECT manhanvien, taikhoan, hoten, sodienthoai, diachi FROM nhanvien WHERE manhanvien = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) { $nv = $row; } else { $editing = false; }
}

// Handle AJAX form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];
    
    try {
        $taikhoan = trim($_POST['taikhoan'] ?? '');
        $matkhau = trim($_POST['matkhau'] ?? '');
        $hoten = trim($_POST['hoten'] ?? '');
        $sodienthoai = trim($_POST['sodienthoai'] ?? '');
        $diachi = trim($_POST['diachi'] ?? '');

        // Validation
        if ($taikhoan === '' || $hoten === '' || $sodienthoai === '' || $diachi === '' || (!$editing && $matkhau === '')) {
            $response['message'] = 'Vui lòng nhập đầy đủ thông tin (khi thêm mới cần có mật khẩu).';
            echo json_encode($response);
            exit;
        }

        // Check duplicate username
        if ($editing) {
            $stmt = $conn->prepare('SELECT COUNT(*) c FROM nhanvien WHERE taikhoan=? AND manhanvien<>?');
            $stmt->bind_param('si', $taikhoan, $id);
        } else {
            $stmt = $conn->prepare('SELECT COUNT(*) c FROM nhanvien WHERE taikhoan=?');
            $stmt->bind_param('s', $taikhoan);
        }
        $stmt->execute();
        $c = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
        
        if ($c > 0) {
            $response['message'] = 'Tài khoản đã tồn tại.';
            echo json_encode($response);
            exit;
        }

        // Process update or insert
        if ($editing) {
            if ($matkhau !== '') {
                $stmt = $conn->prepare('UPDATE nhanvien SET taikhoan=?, matkhau=?, hoten=?, sodienthoai=?, diachi=? WHERE manhanvien=?');
                $stmt->bind_param('sssssi', $taikhoan, $matkhau, $hoten, $sodienthoai, $diachi, $id);
            } else {
                $stmt = $conn->prepare('UPDATE nhanvien SET taikhoan=?, hoten=?, sodienthoai=?, diachi=? WHERE manhanvien=?');
                $stmt->bind_param('ssssi', $taikhoan, $hoten, $sodienthoai, $diachi, $id);
            }
            $ok = $stmt->execute();
            
            if ($ok) {
                $response['success'] = true;
                $response['message'] = 'Cập nhật thông tin nhân viên thành công!';
                $response['redirect'] = 'nhan-vien.php';
            } else {
                $response['message'] = 'Có lỗi xảy ra khi cập nhật thông tin.';
            }
        } else {
            $stmt = $conn->prepare('INSERT INTO nhanvien (taikhoan, matkhau, hoten, sodienthoai, diachi) VALUES (?,?,?,?,?)');
            $stmt->bind_param('sssss', $taikhoan, $matkhau, $hoten, $sodienthoai, $diachi);
            $ok = $stmt->execute();
            
            if ($ok) {
                $response['success'] = true;
                $response['message'] = 'Thêm nhân viên mới thành công!';
                $response['redirect'] = 'nhan-vien.php';
            } else {
                $response['message'] = 'Có lỗi xảy ra khi thêm nhân viên mới.';
            }
        }
    } catch (Exception $e) {
        $response['message'] = 'Lỗi: ' . $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}

// For regular page load, show the form with any existing data
$err = '';
?>
<div class="page-wrapper">
  <div class="page-breadcrumb">
    <div class="row align-items-center">
      <div class="col-5">
        <h4 class="page-title"><i class="mdi mdi-account-tie me-2"></i><?php echo $editing ? 'Sửa Nhân Viên' : 'Thêm Nhân Viên'; ?></h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
              <li class="breadcrumb-item"><a href="nhan-vien.php">Nhân Viên</a></li>
              <li class="breadcrumb-item active" aria-current="page"><?php echo $editing ? 'Sửa' : 'Thêm'; ?></li>
            </ol>
          </nav>
        </div>
      </div>
      <div class="col-7 text-end">
        <a href="nhan-vien.php" class="btn btn-secondary">Quay lại</a>
      </div>
    </div>
  </div>
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title"><i class="mdi mdi-file-document-edit-outline me-2"></i><?php echo $editing ? 'Cập nhật thông tin' : 'Thông tin nhân viên'; ?></h4>
            <?php if($err){ ?><div class="alert alert-danger"><?php echo htmlspecialchars($err, ENT_QUOTES); ?></div><?php } ?>
            <form id="employee-form" method="post" onsubmit="return handleEmployeeFormSubmit(event)">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Tài khoản</label>
                    <input type="text" name="taikhoan" class="form-control" value="<?php echo htmlspecialchars($nv['taikhoan'], ENT_QUOTES); ?>" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Mật khẩu <?php echo $editing ? '(để trống nếu không đổi)' : ''; ?></label>
                    <input type="text" name="matkhau" class="form-control" value="">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Họ tên</label>
                    <input type="text" name="hoten" class="form-control" value="<?php echo htmlspecialchars($nv['hoten'], ENT_QUOTES); ?>" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="text" name="sodienthoai" class="form-control" value="<?php echo htmlspecialchars($nv['sodienthoai'], ENT_QUOTES); ?>" required>
                  </div>
                </div>
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Địa chỉ</label>
                    <input type="text" name="diachi" class="form-control" value="<?php echo htmlspecialchars($nv['diachi'], ENT_QUOTES); ?>" required>
                  </div>
                </div>
              </div>
              <button type="submit" class="btn btn-success text-white"><i class="mdi mdi-content-save me-1"></i><?php echo $editing ? 'Cập nhật' : 'Thêm mới'; ?></button>
              <a href="nhan-vien.php" class="btn btn-light"><i class="mdi mdi-close-circle-outline me-1"></i>Hủy</a>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require(__DIR__.'/layouts/footer.php'); ?>

<script>
// Handle employee form submission
function handleEmployeeFormSubmit(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Đang xử lý...';
    
    // Send AJAX request
    fetch('them-nhan-vien.php<?php echo $editing ? '?id='.$id : ''; ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            if (typeof showToast === 'function') {
                showToast('success', data.message, 'Thành công');
            }
            
            // Redirect to employee list after a short delay
            setTimeout(() => {
                window.location.href = data.redirect || 'nhan-vien.php';
            }, 1500);
        } else {
            // Show error message
            if (typeof showToast === 'function') {
                showToast('error', data.message || 'Có lỗi xảy ra!', 'Lỗi');
            }
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof showToast === 'function') {
            showToast('error', 'Lỗi kết nối! Vui lòng thử lại.', 'Lỗi');
        }
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    });
    
    return false;
}
</script>
