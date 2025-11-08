<?php require(__DIR__.'/layouts/header.php'); ?>		

<?php 
require('../database/connect.php');	
require('../database/query.php');	

// Lấy danh sách sản phẩm
$sql_sanpham = "SELECT masanpham, tensanpham FROM sanpham ORDER BY tensanpham";
$result_sanpham = queryResult($conn, $sql_sanpham);

if(!isset($_GET['id'])){
    echo "<script>alert('Không tìm thấy banner!'); window.location.href='banner.php';</script>";
    exit;
}

$mabanner = $_GET['id'];
$sql = "SELECT * FROM banner WHERE mabanner = $mabanner";
$result = queryResult($conn, $sql);

if($result->num_rows == 0){
    echo "<script>alert('Banner không tồn tại!'); window.location.href='banner.php';</script>";
    exit;
}

$banner = $result->fetch_assoc();

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $tenbanner = $_POST['tenbanner'];
    $loai_link = $_POST['loai_link'];
    $vitri = $_POST['vitri'];
    $thutu = $_POST['thutu'];
    $trangthai = isset($_POST['trangthai']) ? 1 : 0;
    
    // Xử lý đường dẫn dựa trên loại link
    if($loai_link == 'sanpham'){
        $sanpham_ids = isset($_POST['sanpham']) ? $_POST['sanpham'] : [];
        if(!empty($sanpham_ids)){
            // Lưu danh sách ID sản phẩm dạng JSON để thêm vào giỏ hàng
            $duongdan = 'cart:' . json_encode($sanpham_ids);
        } else {
            $duongdan = '';
        }
    } elseif($loai_link == 'trang'){
        $duongdan = $_POST['trang_chon'];
    } else {
        $duongdan = $_POST['duongdan_tuy_chinh'];
    }
    
    $hinhanh = $banner['hinhanh']; // Giữ ảnh cũ
    
    // Upload ảnh mới nếu có
    if(isset($_FILES['hinhanh']) && $_FILES['hinhanh']['error'] == 0){
        $target_dir = "upload/";
        $file_extension = pathinfo($_FILES["hinhanh"]["name"], PATHINFO_EXTENSION);
        $new_filename = time() . '_' . uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if(move_uploaded_file($_FILES["hinhanh"]["tmp_name"], $target_file)){
            // Xóa ảnh cũ nếu tồn tại
            if(file_exists('../' . $banner['hinhanh'])){
                unlink('../' . $banner['hinhanh']);
            }
            $hinhanh = "admin/" . $target_file;
        }
    }
    
    $sql = "UPDATE banner SET 
            tenbanner = '$tenbanner',
            hinhanh = '$hinhanh',
            duongdan = '$duongdan',
            vitri = '$vitri',
            thutu = '$thutu',
            trangthai = '$trangthai'
            WHERE mabanner = $mabanner";
    
    if(queryExecute($conn, $sql)){
        echo "<script>alert('Cập nhật banner thành công!'); window.location.href='banner.php';</script>";
    } else {
        echo "<script>alert('Lỗi: Không thể cập nhật banner!');</script>";
    }
}
?>

<div class="page-wrapper">
    <div class="page-breadcrumb">
        <div class="row align-items-center">
            <div class="col-5">
                <h4 class="page-title">Sửa Banner</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                            <li class="breadcrumb-item"><a href="banner.php">Banner</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Sửa Banner</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Chỉnh Sửa Banner</h4>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label>Tên Banner <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="tenbanner" required value="<?php echo $banner['tenbanner']; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Hình Ảnh Hiện Tại</label>
                                <div class="mb-2">
                                    <img src="../<?php echo $banner['hinhanh']; ?>" style="max-width: 300px;" class="img-thumbnail">
                                </div>
                                <label>Thay Đổi Hình Ảnh</label>
                                <input type="file" class="form-control" name="hinhanh" accept="image/*" onchange="previewImage(event)">
                                <small class="form-text text-muted">Để trống nếu không muốn thay đổi hình ảnh</small>
                                <div class="mt-3">
                                    <img id="preview" src="" style="max-width: 300px; display: none;" class="img-thumbnail">
                                </div>
                            </div>
                            
                            <?php 
                            // Xác định loại link hiện tại
                            $current_loai = 'tuychinh';
                            $current_sanpham_ids = [];
                            if(strpos($banner['duongdan'], 'cart:') === 0){
                                // Link dạng cart:["1","2","3"]
                                $current_loai = 'sanpham';
                                $json_str = str_replace('cart:', '', $banner['duongdan']);
                                $current_sanpham_ids = json_decode($json_str, true);
                                if(!is_array($current_sanpham_ids)){
                                    $current_sanpham_ids = [];
                                }
                            } elseif(in_array($banner['duongdan'], ['tat-ca-san-pham.php', 'san-pham-moi.php', 'giam-gia.php', 'sieu-giam-gia.php', 'lien-he.php', 've-chung-toi.php', 'cua-hang.php'])){
                                $current_loai = 'trang';
                            }
                            ?>
                            
                            <div class="form-group">
                                <label>Loại Đường Dẫn</label>
                                <select class="form-control" name="loai_link" id="loai_link" onchange="changeLoaiLink()">
                                    <option value="sanpham" <?php echo $current_loai == 'sanpham' ? 'selected' : ''; ?>>Liên kết đến Sản phẩm</option>
                                    <option value="trang" <?php echo $current_loai == 'trang' ? 'selected' : ''; ?>>Liên kết đến Trang</option>
                                    <option value="tuychinh" <?php echo $current_loai == 'tuychinh' ? 'selected' : ''; ?>>Đường dẫn tùy chỉnh</option>
                                </select>
                            </div>
                            
                            <div class="form-group" id="div_sanpham" style="display:<?php echo $current_loai == 'sanpham' ? 'block' : 'none'; ?>;">
                                <label>Chọn Sản Phẩm (Sẽ thêm vào giỏ hàng khi click banner)</label>
                                <div style="max-height: 300px; overflow: auto; border: 1px solid #eee; padding: 10px; border-radius: 4px;">
                                    <?php 
                                    if($result_sanpham && $result_sanpham->num_rows > 0){
                                        mysqli_data_seek($result_sanpham, 0); // Reset pointer
                                        while($sp = $result_sanpham->fetch_assoc()){
                                            $checked = in_array($sp['masanpham'], $current_sanpham_ids) ? 'checked' : '';
                                            echo '<div class="form-check">'
                                                .'<input class="form-check-input" type="checkbox" name="sanpham[]" id="sp_'.$sp['masanpham'].'" value="'.$sp['masanpham'].'" '.$checked.'>'
                                                .'<label class="form-check-label" for="sp_'.$sp['masanpham'].'">'.htmlspecialchars($sp['tensanpham']).'</label>'
                                            .'</div>';
                                        }
                                    } else {
                                        echo '<p>Chưa có sản phẩm.</p>';
                                    }
                                    ?>
                                </div>
                                <small class="form-text text-muted">Bạn có thể tích chọn nhiều sản phẩm. Các sản phẩm sẽ tự động thêm vào giỏ hàng khi click banner.</small>
                            </div>
                            
                            <div class="form-group" id="div_trang" style="display:<?php echo $current_loai == 'trang' ? 'block' : 'none'; ?>;">
                                <label>Chọn Trang</label>
                                <select class="form-control" name="trang_chon">
                                    <option value="tat-ca-san-pham.php" <?php echo $banner['duongdan'] == 'tat-ca-san-pham.php' ? 'selected' : ''; ?>>Tất cả sản phẩm</option>
                                    <option value="san-pham-moi.php" <?php echo $banner['duongdan'] == 'san-pham-moi.php' ? 'selected' : ''; ?>>Sản phẩm mới</option>
                                    <option value="giam-gia.php" <?php echo $banner['duongdan'] == 'giam-gia.php' ? 'selected' : ''; ?>>Giảm giá</option>
                                    <option value="sieu-giam-gia.php" <?php echo $banner['duongdan'] == 'sieu-giam-gia.php' ? 'selected' : ''; ?>>Siêu giảm giá</option>
                                    <option value="lien-he.php" <?php echo $banner['duongdan'] == 'lien-he.php' ? 'selected' : ''; ?>>Liên hệ</option>
                                    <option value="ve-chung-toi.php" <?php echo $banner['duongdan'] == 've-chung-toi.php' ? 'selected' : ''; ?>>Về chúng tôi</option>
                                    <option value="cua-hang.php" <?php echo $banner['duongdan'] == 'cua-hang.php' ? 'selected' : ''; ?>>Cửa hàng</option>
                                </select>
                            </div>
                            
                            <div class="form-group" id="div_tuychinh" style="display:<?php echo $current_loai == 'tuychinh' ? 'block' : 'none'; ?>;">
                                <label>Đường Dẫn Tùy Chỉnh</label>
                                <input type="text" class="form-control" name="duongdan_tuy_chinh" value="<?php echo $current_loai == 'tuychinh' ? $banner['duongdan'] : ''; ?>" placeholder="VD: https://example.com hoặc danh-muc.php?id=1">
                                <small class="form-text text-muted">Nhập đường dẫn tùy chỉnh</small>
                            </div>
                            
                            <div class="form-group">
                                <label>Vị Trí <span class="text-danger">*</span></label>
                                <select class="form-control" name="vitri" required>
                                    <option value="slide" <?php echo $banner['vitri'] == 'slide' ? 'selected' : ''; ?>>Banner Slide (Slider trang chủ)</option>
                                    <option value="promo1" <?php echo $banner['vitri'] == 'promo1' ? 'selected' : ''; ?>>Quảng cáo 1 (Banner ngang đầu)</option>
                                    <option value="promo2" <?php echo $banner['vitri'] == 'promo2' ? 'selected' : ''; ?>>Quảng cáo 2 (Banner ngang dưới)</option>
                                    <option value="promo3" <?php echo $banner['vitri'] == 'promo3' ? 'selected' : ''; ?>>Quảng cáo 3 (Banner promotion)</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Thứ Tự</label>
                                <input type="number" class="form-control" name="thutu" value="<?php echo $banner['thutu']; ?>" min="0">
                                <small class="form-text text-muted">Thứ tự hiển thị (số nhỏ hiển thị trước)</small>
                            </div>
                            
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="trangthai" id="trangthai" <?php echo $banner['trangthai'] == 1 ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="trangthai">Hiển thị banner</label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-success">Cập Nhật</button>
                                <a href="banner.php" class="btn btn-secondary">Hủy</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
function previewImage(event) {
    var reader = new FileReader();
    reader.onload = function(){
        var preview = document.getElementById('preview');
        preview.src = reader.result;
        preview.style.display = 'block';
    }
    reader.readAsDataURL(event.target.files[0]);
}

function changeLoaiLink() {
    var loaiLink = document.getElementById('loai_link').value;
    
    // Ẩn tất cả
    document.getElementById('div_sanpham').style.display = 'none';
    document.getElementById('div_trang').style.display = 'none';
    document.getElementById('div_tuychinh').style.display = 'none';
    
    // Hiện div tương ứng
    if(loaiLink == 'sanpham'){
        document.getElementById('div_sanpham').style.display = 'block';
    } else if(loaiLink == 'trang'){
        document.getElementById('div_trang').style.display = 'block';
    } else if(loaiLink == 'tuychinh'){
        document.getElementById('div_tuychinh').style.display = 'block';
    }
}
</script>

<?php require(__DIR__.'/layouts/footer.php'); ?>		
