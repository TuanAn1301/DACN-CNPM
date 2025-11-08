<?php require(__DIR__.'/layouts/header.php'); 

// Lấy danh sách chuyên mục
$sql_chuyenmuc_all = "SELECT * FROM chuyenmuc ORDER BY tenchuyenmuc";
$chuyenmuc_all = queryResult($conn, $sql_chuyenmuc_all);
?>

<section class="breadcrumb-section">
    <h2 class="sr-only">Site Breadcrumb</h2>
    <div class="container">
        <div class="breadcrumb-contents">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Trang Chủ</a></li>
                    <li class="breadcrumb-item active">Sơ Đồ Trang</li>
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
                    <h2>Sơ Đồ Trang Web</h2>
                    <p>Tìm kiếm nhanh các trang bạn cần</p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="sitemap-section">
                    <h3>Trang Chính</h3>
                    <ul class="sitemap-list">
                        <li><a href="index.php"><i class="fas fa-angle-right"></i> Trang Chủ</a></li>
                        <li><a href="tat-ca-san-pham.php"><i class="fas fa-angle-right"></i> Tất Cả Sản Phẩm</a></li>
                        <li><a href="lien-he.php"><i class="fas fa-angle-right"></i> Liên Hệ</a></li>
                        <li><a href="tai-khoan.php"><i class="fas fa-angle-right"></i> Tài Khoản</a></li>
                        <li><a href="gio-hang.php"><i class="fas fa-angle-right"></i> Giỏ Hàng</a></li>
                        <li><a href="thanh-toan.php"><i class="fas fa-angle-right"></i> Thanh Toán</a></li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="sitemap-section">
                    <h3>Sản Phẩm</h3>
                    <ul class="sitemap-list">
                        <li><a href="giam-gia.php"><i class="fas fa-angle-right"></i> Sản Phẩm Giảm Giá</a></li>
                        <li><a href="san-pham-moi.php"><i class="fas fa-angle-right"></i> Sản Phẩm Mới</a></li>
                        <li><a href="sieu-giam-gia.php"><i class="fas fa-angle-right"></i> Siêu Giảm Giá</a></li>
                    </ul>

                    <h3 class="mt--30">Chuyên Mục</h3>
                    <ul class="sitemap-list">
                        <?php while($cm = $chuyenmuc_all->fetch_assoc()){ ?>
                            <li><a href="chuyen-muc.php?id=<?php echo $cm['machuyenmuc']; ?>"><i class="fas fa-angle-right"></i> <?php echo $cm['tenchuyenmuc']; ?></a></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="sitemap-section">
                    <h3>Thông Tin</h3>
                    <ul class="sitemap-list">
                        <li><a href="ve-chung-toi.php"><i class="fas fa-angle-right"></i> Về Chúng Tôi</a></li>
                        <li><a href="cua-hang.php"><i class="fas fa-angle-right"></i> Cửa Hàng</a></li>
                        <li><a href="giao-hang.php"><i class="fas fa-angle-right"></i> Chính Sách Giao Hàng</a></li>
                        <li><a href="lien-he.php"><i class="fas fa-angle-right"></i> Liên Hệ</a></li>
                        <li><a href="so-do-trang.php"><i class="fas fa-angle-right"></i> Sơ Đồ Trang</a></li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="sitemap-section">
                    <h3>Tài Khoản</h3>
                    <ul class="sitemap-list">
                        <li><a href="dang-nhap.php"><i class="fas fa-angle-right"></i> Đăng Nhập</a></li>
                        <li><a href="dang-nhap.php"><i class="fas fa-angle-right"></i> Đăng Ký</a></li>
                        <li><a href="tai-khoan.php"><i class="fas fa-angle-right"></i> Thông Tin Tài Khoản</a></li>
                        <li><a href="tai-khoan.php"><i class="fas fa-angle-right"></i> Lịch Sử Đơn Hàng</a></li>
                    </ul>

                    <h3 class="mt--30">Hỗ Trợ</h3>
                    <ul class="sitemap-list">
                        <li><i class="fas fa-phone"></i> Hotline: 0397172952</li>
                        <li><i class="fas fa-envelope"></i> ntquan2711@gmail.com</li>
                        <li><i class="fas fa-clock"></i> 8:00 - 22:00</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row mt--50">
            <div class="col-12">
                <div class="text-center">
                    <h3>Không Tìm Thấy Trang Bạn Cần?</h3>
                    <p>Hãy liên hệ với chúng tôi để được hỗ trợ tốt nhất!</p>
                    <a href="lien-he.php" class="btn btn-primary">Liên Hệ Ngay</a>
                </div>
            </div>
        </div>
    </div>
</main>

</div>
<?php require(__DIR__.'/layouts/footer.php'); ?>
