<?php
require(__DIR__.'/layouts/header.php');
require('../database/connect.php');

// Handle AJAX delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];
    
    try {
        if ($_POST['action'] === 'delete_employee' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            
            // Prevent deleting the last admin
            $checkAdmin = $conn->prepare('SELECT COUNT(*) as admin_count FROM nhanvien WHERE quyen = 1 AND manhanvien != ?');
            $checkAdmin->bind_param('i', $id);
            $checkAdmin->execute();
            $adminCount = $checkAdmin->get_result()->fetch_assoc()['admin_count'];
            
            // Check if this user is an admin
            $checkUserAdmin = $conn->prepare('SELECT quyen FROM nhanvien WHERE manhanvien = ?');
            $checkUserAdmin->bind_param('i', $id);
            $checkUserAdmin->execute();
            $userData = $checkUserAdmin->get_result()->fetch_assoc();
            $userIsAdmin = !empty($userData) && isset($userData['quyen']) && $userData['quyen'] == 1;
            
            if ($userIsAdmin && $adminCount <= 0) {
                $response['message'] = 'Không thể xóa tài khoản admin cuối cùng!';
                echo json_encode($response);
                exit;
            }
            
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Delete related records first if needed
                // Example: $conn->query("DELETE FROM related_table WHERE employee_id = $id");
                
                // Then delete the employee
                $stmt = $conn->prepare('DELETE FROM nhanvien WHERE manhanvien = ?');
                $stmt->bind_param('i', $id);
                $ok = $stmt->execute();
                
                if ($ok) {
                    $conn->commit();
                    $response['success'] = true;
                    $response['message'] = 'Xóa nhân viên thành công!';
                } else {
                    throw new Exception('Không thể xóa nhân viên');
                }
            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
        } else {
            $response['message'] = 'Hành động không hợp lệ';
        }
    } catch (Exception $e) {
        $response['message'] = 'Lỗi: ' . $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}

// Show message if any from regular form submission (fallback)
$msg = $_GET['msg'] ?? '';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Search
$search = $_GET['search'] ?? '';
$where = [];
$params = [];
$types = '';

if ($search) {
    $where[] = '(hoten LIKE ? OR taikhoan LIKE ? OR sodienthoai LIKE ? OR diachi LIKE ?)';
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $types .= 'ssss';
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total count for pagination
$countStmt = $conn->prepare("SELECT COUNT(*) as total FROM nhanvien $whereClause");
if ($params) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$total = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($total / $perPage);

// Get data with pagination
$stmt = $conn->prepare("SELECT * FROM nhanvien $whereClause ORDER BY manhanvien DESC LIMIT ? OFFSET ?");
$params[] = $perPage;
$params[] = $offset;
$types .= 'ii';

if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$nhanvien = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>
<div class="page-wrapper">
  <div class="page-breadcrumb">
    <div class="row align-items-center">
      <div class="col-5">
        <h4 class="page-title"><i class="mdi mdi-account-tie me-2"></i>Nhân Viên</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
              <li class="breadcrumb-item active" aria-current="page">Nhân Viên</li>
            </ol>
          </nav>
        </div>
      </div>
      <div class="col-7 text-end">
        <a href="them-nhan-vien.php" class="btn btn-primary text-white"><i class="mdi mdi-account-plus me-1"></i> Thêm Mới</a>
        <a href="#" id="refresh-btn" class="btn btn-info text-white ms-2"><i class="mdi mdi-refresh me-1"></i> Làm mới</a>
      </div>
    </div>
  </div>
  <div class="container-fluid">
    <?php if(isset($_GET['msg'])) { ?>
      <div class="alert alert-info"><?php echo htmlspecialchars($_GET['msg'], ENT_QUOTES); ?></div>
    <?php } ?>
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Danh sách nhân viên</h4>
            
            <!-- Search Section -->
            <div class="card border m-t-20 m-b-20">
              <div class="card-body">
                <h6 class="card-title"><i class="mdi mdi-magnify"></i> Tìm kiếm nhân viên</h6>
                <form method="GET" action="" class="form-inline">
                  <div class="form-group flex-fill m-b-10">
                    <div class="input-group w-100">
                      <input type="text" name="search" class="form-control" 
                             placeholder="Tìm theo tên, mã NV, tài khoản, địa chỉ hoặc SĐT..." 
                             value="<?php echo htmlspecialchars($search); ?>">
                      <div class="input-group-append">
                        <button type="submit" class="btn btn-primary">
                          <i class="mdi mdi-magnify"></i> Tìm kiếm
                        </button>
                      </div>
                    </div>
                  </div>
                </form>
              </div>
            </div>
            <div class="table-responsive">
              <table id="employeeTable" class="table table-hover">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Tài Khoản</th>
                    <th>Họ Tên</th>
                    <th>Địa Chỉ</th>
                    <th>Số Điện Thoại</th>
                    <th class="text-center">Hành Động</th>
                  </tr>
                </thead>
                <tbody>
                <?php if(empty($nhanvien)) { ?>
                  <tr><td colspan="6" class="text-center">Chưa có nhân viên nào.</td></tr>
                <?php } else { foreach($nhanvien as $i => $nv) { ?>
                  <tr data-employee-id="<?php echo $nv['manhanvien']; ?>">
                    <td><?php echo $nv['manhanvien']; ?></td>
                    <td><?php echo htmlspecialchars($nv['taikhoan'], ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars($nv['hoten'], ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars($nv['diachi'], ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars($nv['sodienthoai'], ENT_QUOTES); ?></td>
                    <td class="text-nowrap text-center">
                      <a href="them-nhan-vien.php?id=<?php echo $nv['manhanvien']; ?>" class="btn btn-sm btn-warning text-white me-1" title="Sửa">
                        <i class="mdi mdi-pencil"></i>
                      </a>
                      <button type="button" class="btn btn-sm btn-danger text-white delete-employee" 
                              data-id="<?php echo $nv['manhanvien']; ?>" 
                              data-name="<?php echo htmlspecialchars($nv['hoten'], ENT_QUOTES); ?>"
                              title="Xóa">
                        <i class="mdi mdi-delete"></i>
                      </button>
                      <?php if(isset($nv['quyen']) && $nv['quyen'] == 1): ?>
                        <span class="badge bg-success ms-1" title="Quản trị viên">Admin</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php } } ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require(__DIR__.'/layouts/footer.php'); ?>

<!-- Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteModalLabel"><i class="mdi mdi-alert-circle me-2"></i>Xác nhận xóa</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex align-items-center mb-3">
          <div class="me-3">
            <link href="../assets/plugins/sweetalert/sweetalert.css" rel="stylesheet" type="text/css">
<style>
.fade-out {
    opacity: 0;
    transition: opacity 0.5s ease-out;
}
</style>
            <i class="mdi mdi-alert-circle-outline text-danger" style="font-size: 3rem;"></i>
          </div>
          <div>
            <h5>Bạn có chắc chắn muốn xóa nhân viên này?</h5>
            <p class="mb-0">Tên nhân viên: <strong id="employeeName"></strong></p>
            <p class="mb-0">Mã nhân viên: <strong id="employeeId"></strong></p>
            <p class="text-danger mt-2"><i class="mdi mdi-alert"></i> Hành động này không thể hoàn tác!</p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><i class="mdi mdi-close me-1"></i> Hủy</button>
        <button type="button" class="btn btn-danger" id="confirmDelete">
          <i class="mdi mdi-delete me-1"></i> Xác nhận xóa
        </button>
      </div>
    </div>
  </div>
</div>

<style>
  .table-hover > tbody > tr:hover {
    background-color: rgba(0, 0, 0, 0.03);
  }
  .action-buttons .btn {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }
</style>

<script>
// Initialize DataTable if available
if ($.fn.DataTable) {
    $('#employeeTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Vietnamese.json"
        },
        "columnDefs": [
            { "orderable": false, "targets": [5] } // Disable sorting on action column
        ],
        "responsive": true
    });
}

// Handle delete employee
$(document).ready(function() {
    let employeeToDelete = null;
    
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Refresh button
    $('#refresh-btn').on('click', function(e) {
        e.preventDefault();
        window.location.reload();
    });
    
    // Show delete confirmation modal
    $(document).on('click', '.delete-employee', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const id = $btn.data('id');
        const name = $btn.data('name');
        employeeToDelete = id;
        
        // Update modal content
        $('#employeeName').text(name);
        $('#employeeId').text(id);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    });
    
    // Handle delete confirmation
    $('#confirmDelete').on('click', function() {
        if (!employeeToDelete) return;
        
        const $deleteBtn = $(`button[data-id="${employeeToDelete}"]`);
        const $modal = $('#deleteModal');
        const $row = $(`tr[data-employee-id="${employeeToDelete}"]`);
        
        // Show loading state on modal button
        const $confirmBtn = $('#confirmDelete');
        const originalBtnHtml = $confirmBtn.html();
        $confirmBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Đang xóa...');
        
        // Add fade effect to the row being deleted
        $row.addClass('fade-out');
        
        // Send AJAX request
        $.ajax({
            url: 'nhan-vien.php',
            type: 'POST',
            data: {
                action: 'delete_employee',
                id: employeeToDelete
            },
            dataType: 'json',
            success: function(response) {
                // Reset button state
                $confirmBtn.prop('disabled', false).html(originalBtnHtml);
                
                if (response.success) {
                    // Show success message
                    if (typeof showToast === 'function') {
                        showToast('success', response.message, 'Thành công');
                    }
                    
                    // Close the modal
                    const modal = bootstrap.Modal.getInstance($modal[0]);
                    if (modal) modal.hide();
                    
                    // Remove the row after animation
                    setTimeout(() => {
                        $row.remove();
                        
                        // If no more rows, show empty message
                        if ($('#employeeTable tbody tr').length === 0) {
                            $('#employeeTable tbody').html('<tr><td colspan="6" class="text-center">Không có dữ liệu</td></tr>');
                        }
                    }, 500);
                } else {
                    // Show error message
                    if (typeof showToast === 'function') {
                        showToast('error', response.message || 'Có lỗi xảy ra!', 'Lỗi');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                $confirmBtn.prop('disabled', false).html(originalBtnHtml);
                
                if (typeof showToast === 'function') {
                    showToast('error', 'Lỗi kết nối! Vui lòng thử lại.', 'Lỗi');
                }
            }
        });
    });
    
    // Reset the employeeToDelete when modal is hidden
    $('#deleteModal').on('hidden.bs.modal', function() {
        employeeToDelete = null;
        const $confirmBtn = $('#confirmDelete');
        $confirmBtn.prop('disabled', false).html('<i class="mdi mdi-delete me-1"></i> Xác nhận xóa');
    });
    
    // Refresh button handler
    $('#refresh-btn').on('click', function(e) {
        e.preventDefault();
        window.location.reload();
    });
    
    // Show message from URL parameter if exists (fallback)
    <?php if (!empty($msg)): ?>
    if (typeof showToast === 'function') {
        showToast('info', '<?php echo addslashes($msg); ?>', 'Thông báo');
    }
    <?php endif; ?>

    // Initialize delete modal
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    
    // Handle delete button click
    $(document).on('click', '.delete-employee', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const name = $(this).data('name');
        
        // Update modal content
        $('#employeeName').text(name);
        $('#employeeId').text(id);
        
        // Store the ID for later use
        employeeToDelete = id;
        
        // Show the modal
        deleteModal.show();
    });
});
</script>
