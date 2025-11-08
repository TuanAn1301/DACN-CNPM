<?php require(__DIR__.'/layouts/header.php'); 

$sql_moi = "SELECT * FROM sanpham ORDER BY masanpham DESC LIMIT 20";
$sp_moi = queryResult($conn, $sql_moi);
?>

<section class="breadcrumb-section">
    <h2 class="sr-only">Site Breadcrumb</h2>
    <div class="container">
        <div class="breadcrumb-contents">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Trang Chủ</a></li>
                    <li class="breadcrumb-item active">Sản Phẩm Mới</li>
                </ol>
            </nav>
        </div>
    </div>
</section>

<main class="inner-page-sec-padding-bottom">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-title text-center mb--30">
                    <h2>Sản Phẩm Mới Nhất</h2>
                    <p>Cập nhật những đầu sách mới nhất tại cửa hàng!</p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="shop-product-wrap grid with-pagination row space-db--30 shop-border">
                    <?php 
                    if($sp_moi != 0 && $sp_moi->num_rows > 0) {
                        while($row = $sp_moi->fetch_assoc()){ 
                            $giaban = $row['giaban'];
                            $giagoc = $row['giagoc'];
                            $giamgia = round((($giagoc - $giaban) / $giagoc) * 100);
                    ?>
                        <div class="col-lg-4 col-sm-6">
                            <div class="product-card">
                                <div class="product-grid-content">
                                    <div class="product-header">
                                        <span class="badge badge-success">Mới</span>
                                        <?php if($giamgia > 0){ ?>
                                            <span class="badge badge-danger">-<?php echo $giamgia; ?>%</span>
                                        <?php } ?>
                                        <a href="san-pham.php?id=<?php echo $row['masanpham']; ?>" class="author">
                                            <?php echo $row['tentacgia']; ?>
                                        </a>
                                        <h3><a href="san-pham.php?id=<?php echo $row['masanpham']; ?>"><?php echo $row['tensanpham']; ?></a></h3>
                                    </div>
                                    <div class="product-card--body">
                                        <div class="card-image">
                                            <img src="<?php echo $row['anhchinh']; ?>" alt="<?php echo $row['tensanpham']; ?>">
                                            <div class="hover-contents">
                                                <a href="san-pham.php?id=<?php echo $row['masanpham']; ?>" class="hover-image">
                                                    <img src="<?php echo $row['anhchinh']; ?>" alt="">
                                                </a>
                                                <div class="hover-btns">
                                                    <a href="san-pham.php?id=<?php echo $row['masanpham']; ?>" class="single-btn">
                                                        <i class="fas fa-shopping-basket"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="price-block">
                                            <span class="price"><?php echo number_format($giaban, 0, ',', '.'); ?>đ</span>
                                            <?php if($giamgia > 0){ ?>
                                                <del class="price-old"><?php echo number_format($giagoc, 0, ',', '.'); ?>đ</del>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php 
                        }
                    } else {
                        echo '<div class="col-12"><p class="text-center">Chưa có sản phẩm nào.</p></div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</main>

</div>
<?php require(__DIR__.'/layouts/footer.php'); ?>
