<?php require(__DIR__.'/layouts/header.php'); ?>	
<?php 
require('../database/connect.php');	
require('../database/query.php');	

$masanpham = $_GET['id'];

$sql = "SELECT * FROM sanpham WHERE masanpham = ".$masanpham;
$result = queryResult($conn,$sql);

$sanpham = $result->fetch_assoc();

$sql_chuyenmuc = "SELECT * FROM chuyenmuc";
$result_chuyenmuc = queryResult($conn,$sql_chuyenmuc);


if($_SERVER['REQUEST_METHOD'] == 'POST'){
	$tensanpham = $_POST['tensanpham'];
	$giagoc = $_POST['giagoc'];
	$giaban = $_POST['giaban'];
	$motangan = $_POST['motangan'];
	$motachitiet = $_POST['motachitiet'];
	$machuyenmuc = $_POST['machuyenmuc'];
	$tag = $_POST['tag'];
	$anhchinh = $sanpham['anhchinh'];
	$anhphu1 = $sanpham['anhphu1'];
	$anhphu2 = $sanpham['anhphu2'];
	$anhphu3 = $sanpham['anhphu3'];
	$anhphu4 = $sanpham['anhphu4'];

	// Ensure upload dir exists
	$uploadDir = __DIR__ . '/upload';
	if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0777, true); }

	function make_unique_name($prefix, $originalName) {
		$ext = pathinfo($originalName, PATHINFO_EXTENSION);
		$base = pathinfo($originalName, PATHINFO_FILENAME);
		$base = preg_replace('/[^a-zA-Z0-9_-]+/', '-', $base);
		return $prefix . $base . '-' . uniqid() . ($ext ? ('.' . $ext) : '');
	}

	function safe_delete_upload($relativePath) {
		if (!$relativePath) return;
		if (strpos($relativePath, 'admin/upload/') !== 0) return;
		$abs = realpath(__DIR__ . '/../' . $relativePath);
		$uploadRoot = realpath(__DIR__ . '/upload');
		if ($abs && $uploadRoot && strpos($abs, $uploadRoot) === 0 && file_exists($abs)) { @unlink($abs); }
	}

	// Replace if new files provided
	if (!empty($_FILES['anhchinh']['name'])) {
		$new = make_unique_name('a-', $_FILES['anhchinh']['name']);
		if (move_uploaded_file($_FILES['anhchinh']['tmp_name'], $uploadDir . '/' . $new)) {
			safe_delete_upload($anhchinh);
			$anhchinh = 'admin/upload/' . $new;
		}
	}
	if (!empty($_FILES['anhphu1']['name'])) {
		$new = make_unique_name('b-', $_FILES['anhphu1']['name']);
		if (move_uploaded_file($_FILES['anhphu1']['tmp_name'], $uploadDir . '/' . $new)) {
			safe_delete_upload($anhphu1);
			$anhphu1 = 'admin/upload/' . $new;
		}
	}
	if (!empty($_FILES['anhphu2']['name'])) {
		$new = make_unique_name('c-', $_FILES['anhphu2']['name']);
		if (move_uploaded_file($_FILES['anhphu2']['tmp_name'], $uploadDir . '/' . $new)) {
			safe_delete_upload($anhphu2);
			$anhphu2 = 'admin/upload/' . $new;
		}
	}
	if (!empty($_FILES['anhphu3']['name'])) {
		$new = make_unique_name('d-', $_FILES['anhphu3']['name']);
		if (move_uploaded_file($_FILES['anhphu3']['tmp_name'], $uploadDir . '/' . $new)) {
			safe_delete_upload($anhphu3);
			$anhphu3 = 'admin/upload/' . $new;
		}
	}
	if (!empty($_FILES['anhphu4']['name'])) {
		$new = make_unique_name('e-', $_FILES['anhphu4']['name']);
		if (move_uploaded_file($_FILES['anhphu4']['tmp_name'], $uploadDir . '/' . $new)) {
			safe_delete_upload($anhphu4);
			$anhphu4 = 'admin/upload/' . $new;
		}
	}

	$sql_update = "UPDATE `sanpham` SET `tensanpham`='".$tensanpham."',`giagoc`='".$giagoc."',`giaban`='".$giaban."',`machuyenmuc`=".$machuyenmuc.",`tag`='".$tag."',`mota`='".$motangan."',`anhchinh`='".$anhchinh."',`anhphu1`='".$anhphu1."',`anhphu2`='".$anhphu2."',`anhphu3`='".$anhphu3."',`anhphu4`='".$anhphu4."',`motachitiet`='".$motachitiet."' WHERE `masanpham`= ".$masanpham;
	queryExecute($conn,$sql_update);
    echo '<script type="text/JavaScript"> window.location.href = "http://localhost/webbansach/admin/san-pham.php"; </script>';
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
                                            <input type="text" placeholder="Nhập tên sản phẩm" class="form-control form-control-line"  name="tensanpham" value="<?php echo $sanpham['tensanpham']; ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12">Giá gốc</label>
                                        <div class="col-md-12">
                                            <input type="text" placeholder="Nhập giá gốc" class="form-control form-control-line"  name="giagoc" value="<?php echo $sanpham['giagoc']; ?>" required> 
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12">Giá bán</label>
                                        <div class="col-md-12">
                                            <input type="text" placeholder="Nhập giá bán" class="form-control form-control-line"  name="giaban" value="<?php echo $sanpham['giaban']; ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12">Mô tả ngắn</label>
                                        <div class="col-md-12">
                                            <input type="text" placeholder="Nhập mô tả" class="form-control form-control-line"  name="motangan" value="<?php echo $sanpham['mota']; ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12">Mô tả chi tiết</label>
                                        <div class="col-md-12">
                                            <textarea type="text" placeholder="Nhập mô tả chi tiết" class="form-control form-control-line"  name="motachitiet" rows="5" required><?php echo $sanpham['motachitiet']; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-12">Chuyên Mục</label>
                                        <div class="col-sm-12">
                                            <select class="form-select shadow-none form-control-line"  name="machuyenmuc">
                                            	<?php while($row = $result_chuyenmuc->fetch_assoc()){ ?>
                                                    <?php if($sanpham['machuyenmuc'] == $row['machuyenmuc']){ ?>
                                                	   <option value="<?php echo $row['machuyenmuc']; ?>" selected><?php echo $row['tenchuyenmuc']; ?></option>
                                                    <?php }else{ ?>
                                                       <option value="<?php echo $row['machuyenmuc']; ?>"><?php echo $row['tenchuyenmuc']; ?></option>
                                                    <?php } ?>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12">Thẻ</label>
                                        <div class="col-md-12">
                                            <input type="text" placeholder="Thẻ cách nhau bởi dấu ," class="form-control form-control-line"  name="tag" value="<?php echo $sanpham['tag']; ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12">Ảnh chính</label>
                                        <div class="col-md-12">
                                            <input type="file" class="form-control form-control-line"  name="anhchinh">
                                        </div>
                                        <br>
                                        <img src="<?php echo 'http://localhost/webbansach/'.$sanpham['anhchinh']; ?>" alt="" style="width: 200px; height: 200px;">
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12">Ảnh phụ 1</label>
                                        <div class="col-md-12">
                                            <input type="file" class="form-control form-control-line"  name="anhphu1">
                                        </div>
                                        <br>
                                        <img src="<?php echo 'http://localhost/webbansach/'.$sanpham['anhphu1']; ?>" alt="" style="width: 200px; height: 200px;">
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12">Ảnh phụ 2</label>
                                        <div class="col-md-12">
                                            <input type="file" class="form-control form-control-line"  name="anhphu2">
                                        </div>
                                        <br>
                                        <img src="<?php echo 'http://localhost/webbansach/'.$sanpham['anhphu2']; ?>" alt="" style="width: 200px; height: 200px;">
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12">Ảnh phụ 3</label>
                                        <div class="col-md-12">
                                            <input type="file" class="form-control form-control-line"  name="anhphu3">
                                        </div>
                                        <br>
                                        <img src="<?php echo 'http://localhost/webbansach/'.$sanpham['anhphu3']; ?>" alt="" style="width: 200px; height: 200px;">
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12">Ảnh phụ 4</label>
                                        <div class="col-md-12">
                                            <input type="file" class="form-control form-control-line"  name="anhphu4">
                                        </div>
                                        <br>
                                        <img src="<?php echo 'http://localhost/webbansach/'.$sanpham['anhphu4']; ?>" alt="" style="width: 200px; height: 200px;">
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-12">
                                            <button class="btn btn-success text-white" type="submit">Sửa Sản Phẩm</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

<?php require(__DIR__.'/layouts/footer.php'); ?>		
