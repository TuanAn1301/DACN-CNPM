<?php require(__DIR__.'/layouts/header.php'); ?>		

<?php 
require('../database/connect.php');	
require('../database/query.php');	

// Lấy danh sách sản phẩm
$sql_sanpham = "SELECT masanpham, tensanpham FROM sanpham ORDER BY tensanpham";
$result_sanpham = queryResult($conn, $sql_sanpham);

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
    
    // Upload ảnh
    if(isset($_FILES['hinhanh']) && $_FILES['hinhanh']['error'] == 0){
        $target_dir = "upload/";
        $file_extension = pathinfo($_FILES["hinhanh"]["name"], PATHINFO_EXTENSION);
        $new_filename = time() . '_' . uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if(move_uploaded_file($_FILES["hinhanh"]["tmp_name"], $target_file)){
            $hinhanh = "admin/" . $target_file;
            
            $sql = "INSERT INTO banner (tenbanner, hinhanh, duongdan, vitri, thutu, trangthai) 
                    VALUES ('$tenbanner', '$hinhanh', '$duongdan', '$vitri', '$thutu', '$trangthai')";
            
            if(queryExecute($conn, $sql)){
                echo "<script>alert('Thêm banner thành công!'); window.location.href='banner.php';</script>";
            } else {
                echo "<script>alert('Lỗi: Không thể thêm banner!');</script>";
            }
        } else {
            echo "<script>alert('Lỗi: Không thể upload hình ảnh!');</script>";
        }
    } else {
        echo "<script>alert('Vui lòng chọn hình ảnh!');</script>";
    }
}
?>

<div class="page-wrapper">
    <div class="page-breadcrumb">
        <div class="row align-items-center">
            <div class="col-5">
                <h4 class="page-title">Thêm Banner</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                            <li class="breadcrumb-item"><a href="banner.php">Banner</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Thêm Banner</li>
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
                        <h4 class="card-title">Thêm Banner Mới</h4>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label>Tên Banner <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="tenbanner" required placeholder="Nhập tên banner">
                            </div>
                            
                            <div class="form-group">
                                <label>Hình Ảnh <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" name="hinhanh" accept="image/*" required onchange="previewImage(event)">
                                <small class="form-text text-muted">Chọn hình ảnh cho banner (JPG, PNG, GIF)</small>
                                <div class="mt-3">
                                    <img id="preview" src="" style="max-width: 300px; display: none;" class="img-thumbnail">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Loại Đường Dẫn</label>
                                <select class="form-control" name="loai_link" id="loai_link" onchange="changeLoaiLink()">
                                    <option value="sanpham">Liên kết đến Sản phẩm</option>
                                    <option value="trang">Liên kết đến Trang</option>
                                    <option value="tuychinh">Đường dẫn tùy chỉnh</option>
                                </select>
                            </div>
                            
                            <div class="form-group" id="div_sanpham">
                                <label>Chọn Sản Phẩm (Sẽ thêm vào giỏ hàng khi click banner)</label>
                                <div style="max-height: 300px; overflow: auto; border: 1px solid #eee; padding: 10px; border-radius: 4px;">
                                    <?php 
                                    if($result_sanpham && $result_sanpham->num_rows > 0){
                                        while($sp = $result_sanpham->fetch_assoc()){
                                            echo '<div class="form-check">'
                                                .'<input class="form-check-input" type="checkbox" name="sanpham[]" id="sp_'.$sp['masanpham'].'" value="'.$sp['masanpham'].'">'
                                                .'<label class="form-check-label" for="sp_'.$sp['masanpham'].'">'.htmlspecialchars($sp['tensanpham']).'</label>'
                                            .'</div>';
                                        }
                                    } else {
                                        echo '<p>Chưa có sản phẩm.</p>';
                                    }
                                    ?>
                                </div>
                                <small class="form-text text-muted">Bạn có thể tích chọn nhiều sản phẩm.</small>
                            </div>
                            
                            <div class="form-group" id="div_trang" style="display:none;">
                                <label>Chọn Trang</label>
                                <select class="form-control" name="trang_chon">
                                    <option value="tat-ca-san-pham.php">Tất cả sản phẩm</option>
                                    <option value="san-pham-moi.php">Sản phẩm mới</option>
                                    <option value="giam-gia.php">Giảm giá</option>
                                    <option value="sieu-giam-gia.php">Siêu giảm giá</option>
                                    <option value="lien-he.php">Liên hệ</option>
                                    <option value="ve-chung-toi.php">Về chúng tôi</option>
                                    <option value="cua-hang.php">Cửa hàng</option>
                                </select>
                            </div>
                            
                            <div class="form-group" id="div_tuychinh" style="display:none;">
                                <label>Đường Dẫn Tùy Chỉnh</label>
                                <input type="text" class="form-control" name="duongdan_tuy_chinh" placeholder="VD: https://example.com hoặc danh-muc.php?id=1">
                                <small class="form-text text-muted">Nhập đường dẫn tùy chỉnh</small>
                            </div>
                            
                            <div class="form-group">
                                <label>Vị Trí <span class="text-danger">*</span></label>
                                <select class="form-control" name="vitri" required>
                                    <option value="slide">Banner Slide (Slider trang chủ)</option>
                                    <option value="promo1">Quảng cáo 1 (Banner ngang đầu)</option>
                                    <option value="promo2">Quảng cáo 2 (Banner ngang dưới)</option>
                                    <option value="promo3">Quảng cáo 3 (Banner promotion)</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Thứ Tự</label>
                                <input type="number" class="form-control" name="thutu" value="0" min="0">
                                <small class="form-text text-muted">Thứ tự hiển thị (số nhỏ hiển thị trước)</small>
                            </div>
                            
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="trangthai" id="trangthai" checked>
                                    <label class="form-check-label" for="trangthai">Hiển thị banner</label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-success">Thêm Banner</button>
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
