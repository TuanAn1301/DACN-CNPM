<?php require(__DIR__.'/layouts/header.php'); ?>		

<?php 
require('../database/connect.php');	
require('../database/query.php');	
$sql = "SELECT * FROM banner ORDER BY vitri, thutu";
$result = queryResult($conn,$sql);

?>

<div class="page-wrapper">
    <div class="page-breadcrumb">
        <div class="row align-items-center">
            <div class="col-5">
                <h4 class="page-title">Quản Lý Banner</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Banner</li>
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
                        <h4 class="card-title">
                            Banner Quảng Cáo
                            <a class="btn btn-success text-white" style="float: right;" href="them-banner.php">Thêm Banner</a>
                        </h4>
                        <h6 class="card-subtitle">Quản lý các banner quảng cáo trên trang chủ</h6>
                        <h6 class="card-title m-t-40"><i class="m-r-5 font-18 mdi mdi-image-multiple"></i> Danh sách banner</h6>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Hình Ảnh</th>
                                        <th scope="col">Tên Banner</th>
                                        <th scope="col">Vị Trí</th>
                                        <th scope="col">Đường Dẫn</th>
                                        <th scope="col">Thứ Tự</th>
                                        <th scope="col">Trạng Thái</th>
                                        <th scope="col">Hành Động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(!isset($result->num_rows)){ ?>
                                        <tr><td colspan="8" class="text-center">Không có banner nào để hiển thị</td></tr>
                                    <?php } else { ?>
                                        <?php 
                                        $i = 1;
                                        ?>
                                        <?php while($row = $result->fetch_assoc()) { ?>
                                            <tr>
                                                <th scope="row"><?php echo $i; ?></th>
                                                <td>
                                                    <img src="../<?php echo $row['hinhanh']; ?>" alt="<?php echo $row['tenbanner']; ?>" style="width: 100px; height: 60px; object-fit: cover;">
                                                </td>
                                                <td><?php echo $row['tenbanner']; ?></td>
                                                <td>
                                                    <?php 
                                                    $vitri_text = '';
                                                    switch($row['vitri']) {
                                                        case 'slide': $vitri_text = 'Banner Slide'; break;
                                                        case 'promo1': $vitri_text = 'Quảng cáo 1'; break;
                                                        case 'promo2': $vitri_text = 'Quảng cáo 2'; break;
                                                        case 'promo3': $vitri_text = 'Quảng cáo 3'; break;
                                                        default: $vitri_text = $row['vitri'];
                                                    }
                                                    echo $vitri_text;
                                                    ?>
                                                </td>
                                                <td><?php echo $row['duongdan'] ? $row['duongdan'] : '-'; ?></td>
                                                <td><?php echo $row['thutu']; ?></td>
                                                <td>
                                                    <?php if($row['trangthai'] == 1){ ?>
                                                        <span class="badge bg-success">Hiển thị</span>
                                                    <?php } else { ?>
                                                        <span class="badge bg-secondary">Ẩn</span>
                                                    <?php } ?>
                                                </td>
                                                <td>
                                                    <a href="sua-banner.php?id=<?php echo $row['mabanner']; ?>" style="margin-right: 10px;" title="Sửa"><i class="fa-solid fa-pen-to-square"></i></a>
                                                    <a href="xoa-banner.php?id=<?php echo $row['mabanner']; ?>" onclick="return confirm('Bạn có chắc muốn xóa banner này?')" title="Xóa"><i class="fa-solid fa-trash"></i></a>
                                                </td>
                                            </tr>
                                        <?php $i++; } } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php require(__DIR__.'/layouts/footer.php'); ?>		
