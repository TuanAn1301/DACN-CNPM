<?php require(__DIR__.'/layouts/header.php'); ?>	
<?php 
require('../database/connect.php');	
require('../database/query.php');	
$sql = "SELECT * FROM chuyenmuc";
$result = queryResult($conn,$sql);

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	$tensanpham = $_POST['tensanpham'];
	$giagoc = $_POST['giagoc'];
	$giaban = $_POST['giaban'];
	$motangan = $_POST['motangan'];
	$motachitiet = $_POST['motachitiet'];
	$machuyenmuc = $_POST['machuyenmuc'];
	$tag = $_POST['tag'];
    $loaisanpham = $_POST['loaisanpham'];

	// Ensure upload dir exists (admin/upload)
	$uploadDir = __DIR__ . '/upload';
	if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0777, true); }

	function make_unique_name($prefix, $originalName) {
		$ext = pathinfo($originalName, PATHINFO_EXTENSION);
		$base = pathinfo($originalName, PATHINFO_FILENAME);
		$base = preg_replace('/[^a-zA-Z0-9_-]+/', '-', $base);
		return $prefix . $base . '-' . uniqid() . ($ext ? ('.' . $ext) : '');
	}

	// Build unique filenames and move files into admin/upload
	$fn_main = make_unique_name('a-', $_FILES['anhchinh']['name']);
	$fn_1   = make_unique_name('b-', $_FILES['anhphu1']['name']);
	$fn_2   = make_unique_name('c-', $_FILES['anhphu2']['name']);
	$fn_3   = make_unique_name('d-', $_FILES['anhphu3']['name']);
	$fn_4   = make_unique_name('e-', $_FILES['anhphu4']['name']);

	move_uploaded_file($_FILES['anhchinh']['tmp_name'], $uploadDir . '/' . $fn_main);
	move_uploaded_file($_FILES['anhphu1']['tmp_name'], $uploadDir . '/' . $fn_1);
	move_uploaded_file($_FILES['anhphu2']['tmp_name'], $uploadDir . '/' . $fn_2);
	move_uploaded_file($_FILES['anhphu3']['tmp_name'], $uploadDir . '/' . $fn_3);
	move_uploaded_file($_FILES['anhphu4']['tmp_name'], $uploadDir . '/' . $fn_4);

	// Store relative web paths in DB (relative to web root)
	$anhchinh = 'admin/upload/' . $fn_main;
	$anhphu1  = 'admin/upload/' . $fn_1;
	$anhphu2  = 'admin/upload/' . $fn_2;
	$anhphu3  = 'admin/upload/' . $fn_3;
	$anhphu4  = 'admin/upload/' . $fn_4;

	$sql_insert = "INSERT INTO `sanpham`(`tensanpham`, `giagoc`, `giaban`, `machuyenmuc`, `tag`, `mota`, `anhchinh`, `anhphu1`, `anhphu2`, `anhphu3`, `anhphu4`, `motachitiet`, `loaisanpham`) VALUES ('".$tensanpham."','".$giagoc."','".$giaban."','".$machuyenmuc."','".$tag."','".$motangan."','".$anhchinh."','".$anhphu1."','".$anhphu2."','".$anhphu3."','".$anhphu4."','".$motachitiet."',".$loaisanpham.")";
	
	if(queryExecute($conn,$sql_insert)){
		// Lấy mã sản phẩm vừa thêm
		$masanpham = $conn->insert_id;
		
		// Gửi thông báo đến tất cả email đăng ký
		require_once('gui-thong-bao-sach-moi.php');
		$result = guiThongBaoSachMoi($masanpham, $tensanpham, $giaban, $anhchinh, $motangan);
		
		// Log kết quả (có thể bỏ qua nếu không muốn hiển thị)
		// echo "<script>console.log('Email sent: " . json_encode($result) . "');</script>";
	}
}


?>
<div class="page-wrapper">
            <div class="page-breadcrumb">
                <div class="row align-items-center">
                    <div class="col-5">
                        <h4 class="page-title">Quản Lý Sản Phẩm</h4>
                        <div class="d-flex align-items-center">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="#">Trang chủ</a></li>
                                    <li class="breadcrumb-item active" aria-current="page"><a href="#">Sản phẩm</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Thêm sản phẩm</li>
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
                                <form class="form-horizontal form-material mx-2" method="POST" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label class="col-md-12">Tên sản phẩm</label>
                                        <div class="col-md-12">
                                            <input type="text" placeholder="Nhập tên sản phẩm" class="form-control form-control-line" required name="tensanpham">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12">Giá gốc</label>
                                        <div class="col-md-12">
                                            <input type="text" placeholder="Nhập giá gốc" class="form-control form-control-line" required name="giagoc"> 
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12">Giá bán</label>
                                        <div class="col-md-12">
                                            <input type="text" placeholder="Nhập giá bán" class="form-control form-control-line" required name="giaban">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12">Mô tả ngắn</label>
                                        <div class="col-md-12">
                                            <input type="text" placeholder="Nhập mô tả" class="form-control form-control-line" required name="motangan">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12">Mô tả chi tiết</label>
                                        <div class="col-md-12">
                                            <textarea type="text" placeholder="Nhập mô tả chi tiết" class="form-control form-control-line" required name="motachitiet" rows="5"></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-12">Chuyên Mục</label>
                                        <div class="col-sm-12">
                                            <select class="form-select shadow-none form-control-line" required name="machuyenmuc">
                                            	<?php while($row = $result->fetch_assoc()){ ?>
                                                	<option value="<?php echo $row['machuyenmuc']; ?>"><?php echo $row['tenchuyenmuc']; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-12">Loại Sản Phẩm</label>
                                        <div class="col-sm-12">
                                            <select class="form-select shadow-none form-control-line" required name="loaisanpham">
                                                <option value="0">Slide</option>
                                                <option value="1">Banner</option>
                                                <option value="2">Nổi Bật</option>
                                                <option value="3">Mới</option>
                                                <option value="4">Banner 2</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12">Thẻ</label>
                                        <div class="col-md-12">
                                            <input type="text" placeholder="Thẻ cách nhau bởi dấu ," class="form-control form-control-line" required name="tag" >
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12">Ảnh chính</label>
                                        <div class="col-md-12">
                                            <input type="file" class="form-control form-control-line" required name="anhchinh">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12">Ảnh phụ 1</label>
                                        <div class="col-md-12">
                                            <input type="file" class="form-control form-control-line"  required name="anhphu1">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12">Ảnh phụ 2</label>
                                        <div class="col-md-12">
                                            <input type="file" class="form-control form-control-line" required name="anhphu2">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12">Ảnh phụ 3</label>
                                        <div class="col-md-12">
                                            <input type="file" class="form-control form-control-line" required name="anhphu3">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12">Ảnh phụ 4</label>
                                        <div class="col-md-12">
                                            <input type="file" class="form-control form-control-line" required name="anhphu4">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-12">
                                            <button class="btn btn-success text-white" type="submit">Thêm Sản Phẩm</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

<?php require(__DIR__.'/layouts/footer.php'); ?>		
