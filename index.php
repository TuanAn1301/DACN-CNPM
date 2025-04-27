<?php require(__DIR__.'/layouts/header.php'); ?>    
<?php 
$sql_slide = "SELECT * FROM sanpham WHERE loaisanpham = 0 ORDER BY masanpham DESC";
$slide = queryResult($conn,$sql_slide);

$sql_noibat = "SELECT * FROM sanpham WHERE loaisanpham = 2 ORDER BY masanpham DESC";
$noibat = queryResult($conn,$sql_noibat);

$sql_moi = "SELECT * FROM sanpham WHERE loaisanpham = 3 ORDER BY masanpham DESC";
$moi = queryResult($conn,$sql_moi);

$sql_danhchoban = "SELECT * FROM sanpham ORDER BY RAND() LIMIT 7";
$danhchoban = queryResult($conn,$sql_danhchoban);
?>

<!--=================================
Hero Area
===================================== -->
<section class="hero-area hero-slider-1">
    <div class="sb-slick-slider" data-slick-setting='{
                    "autoplay": true,
                    "fade": true,
                    "autoplaySpeed": 3000,
                    "speed": 3000,
                    "slidesToShow": 1,
                    "dots":true
                    }'>
        <?php while($row = $slide->fetch_assoc()){ ?>

            <div class="single-slide bg-shade-whisper  ">
                <div class="container">
                    <div class="home-content text-center text-sm-left position-relative">
                        <div class="hero-partial-image image-right">
                            <img src="http://localhost/webbansach/<?php echo $row['anhchinh'] ?>" alt="" style="width: 720px; height: 509px;">
                        </div>
                        <div class="row no-gutters ">
                            <div class="col-xl-6 col-md-6 col-sm-7">
                                <div class="home-content-inner content-left-side">
                                    <h1 style="line-height: 60px;"><?php echo $row["tensanpham"]; ?></h1>
                                    <a href="san-pham.php?id=<?php echo $row['masanpham']; ?>" class="btn btn-outlined--primary">
                                        Mua Ngay
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        <?php } ?>
    </div>
</section>
<!--=================================
Home Features Section
===================================== -->
<section class="mb--30">
    <div class="container">
        <div class="row">
            <div class="col-xl-3 col-md-6 mt--30">
                <div class="feature-box h-100">
                    <div class="icon">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <div class="text">
                        <h5>Giao Hàng</h5>
                        <p> Miễn phí giao hàng</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mt--30">
                <div class="feature-box h-100">
                    <div class="icon">
                        <i class="fas fa-redo-alt"></i>
                    </div>
                    <div class="text">
                        <h5>Đổi Trả</h5>
                        <p>Trong vòng 1 tuần</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mt--30">
                <div class="feature-box h-100">
                    <div class="icon">
                        <i class="fas fa-piggy-bank"></i>
                    </div>
                    <div class="text">
                        <h5>Thanh Toán </h5>
                        <p>Thanh toán khi nhận hàng</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mt--30">
                <div class="feature-box h-100">
                    <div class="icon">
                        <i class="fas fa-life-ring"></i>
                    </div>
                    <div class="text">
                        <h5>Hỗ Trợ</h5>
                        <p>Tổng đài: 0988.888.999</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--=================================
Promotion Section One
===================================== -->
<section class="section-margin">
    <h2 class="sr-only">Quảng Cáo</h2>
    <div class="container">
        <div class="row space-db--30">
            <div class="col-lg-6 col-md-6 mb--30">
                <a href="" class="promo-image promo-overlay">
                    <img src="http://localhost/webbansach/admin/upload/ms_banner_img2.png" alt="">
                </a>
            </div>
            <div class="col-lg-6 col-md-6 mb--30">
                <a href="" class="promo-image promo-overlay">
                    <img src="http://localhost/webbansach/admin/upload/ms_banner_img3.png" alt="">
                </a>
            </div>
        </div>
    </div>
</section>
<!--=================================
Home Slider Tab
===================================== -->
<section class="section-padding">
    <h2 class="sr-only"></h2>
    <div class="container">
        <div class="sb-custom-tab">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="shop-tab" data-toggle="tab" href="#shop" role="tab"
                        aria-controls="shop" aria-selected="true">
                        Sách Nổi Bật
                    </a>
                    <span class="arrow-icon"></span>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane show active" id="shop" role="tabpanel" aria-labelledby="shop-tab">
                    <div class="product-slider multiple-row  slider-border-multiple-row  sb-slick-slider"
                        data-slick-setting='{
                    "autoplay": true,
                    "autoplaySpeed": 8000,
                    "slidesToShow": 5,
                    "rows":2,
                    "dots":true
                }' data-slick-responsive='[
                    {"breakpoint":1200, "settings": {"slidesToShow": 3} },
                    {"breakpoint":768, "settings": {"slidesToShow": 2} },
                    {"breakpoint":480, "settings": {"slidesToShow": 1} },
                    {"breakpoint":320, "settings": {"slidesToShow": 1} }
                ]'>
                    <?php if ($noibat && $noibat->num_rows > 0) { ?>
                        <?php while($row = $noibat->fetch_assoc()){ ?>
                            <div class="single-slide">
                                <div class="product-card">
                                    <div class="product-header">
                                        <a href="" class="author">
                                            <?php echo $row['tag']; ?>
                                        </a>
                                        <h3><a href="san-pham.php?id=<?php echo $row['masanpham']; ?>"><?php echo $row['tensanpham']; ?></a></h3>
                                    </div>
                                    <div class="product-card--body">
                                        <div class="card-image">
                                            <img src="http://localhost/webbansach/<?php echo $row['anhchinh']; ?>" alt="" style="width: 220px; height:220px; ">
                                            <div class="hover-contents">
                                                <a href="san-pham.php?id=<?php echo $row['masanpham']; ?>" class="hover-image">
                                                    <img src="http://localhost/webbansach/<?php echo $row['anhphu1']; ?>" alt="" style="width: 220px; height:220px; ">
                                                </a>
                                            </div>
                                        </div>
                                        <div class="price-block">
                                            <span class="price"><?php echo number_format($row['giaban']); ?>đ</span>
                                            <del class="price-old"><?php echo number_format($row['giagoc']); ?>đ</del>
                                            <span class="price-discount">-<?php echo number_format($row['giagoc'] - $row['giaban']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <p>Không có sản phẩm nổi bật nào.</p>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-margin">
    <div class="container">
        <div class="section-title section-title--bordered">
            <h2>Sản Phẩm Mới</h2>
        </div>
        <div class="product-list-slider slider-two-column product-slider multiple-row sb-slick-slider slider-border-multiple-row"
            data-slick-setting='{
                                    "autoplay": true,
                                    "autoplaySpeed": 8000,
                                    "slidesToShow":3,
                                    "rows":2,
                                    "dots":true
                                }' data-slick-responsive='[
                                    {"breakpoint":1200, "settings": {"slidesToShow": 2} },
                                    {"breakpoint":992, "settings": {"slidesToShow": 2} },
                                    {"breakpoint":768, "settings": {"slidesToShow": 1} },
                                    {"breakpoint":575, "settings": {"slidesToShow": 1} },
                                    {"breakpoint":490, "settings": {"slidesToShow": 1} }
                                ]'>
            <?php if ($moi && $moi->num_rows > 0) { ?>
                <?php while($row = $moi->fetch_assoc()){ ?>
                    <div class="single-slide">
                        <div class="product-card card-style-list">
                            <div class="card-image">
                                <img src="<?php echo $row['anhchinh']; ?>" alt="" style="width: 150px; height: 150px; image-rendering: -webkit-optimize-contrast;">
                            </div>
                            <div class="product-card--body">
                                <div class="product-header">
                                    <a href="#" class="author">
                                        <?php echo $row['tag']; ?>
                                    </a>
                                    <h3><a href="san-pham.php?id=<?php echo $row['masanpham']; ?>"><?php echo $row['tensanpham']; ?></a></h3>
                                </div>
                                <div class="price-block">
                                    <span class="price"><?php echo number_format($row['giaban']); ?>đ</span>
                                    <del class="price-old"><?php echo number_format($row['giagoc']); ?>đ</del>
                                    <span class="price-discount">-<?php echo number_format($row['giagoc'] - $row['giaban']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <p>Không có sản phẩm mới nào.</p>
            <?php } ?>
        </div>
    </div>
</section>
<!--=================================
Promotion Section Two
===================================== -->
<div class="section-margin">
    <div class="container">
        <div class="row space-db--30">
            <div class="col-lg-8 mb--30">
                <div class="promo-wrapper promo-type-one">
                    <a href="#" class="promo-image  promo-overlay bg-image"
                        data-bg="">
                        <img src="http://localhost/webbansach/admin/upload/breadcrumb_bg2.png" alt="">
                    </a>
                    <div class="promo-text">
                        <div class="promo-text-inner">
                            <h2>Mua 3. Tăng miễn phí 1</h2>
                            <h3>Giảm giá lên đến 50% cho khác hàng</h3>
                            <a href="tat-ca-san-pham.php" class="btn btn-outlined--red-faded">XEM THÊM</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb--30">
                <div class="promo-wrapper promo-type-two ">
                    <a href="tat-ca-san-pham.php" class="promo-image promo-overlay bg-image"
                        data-bg="">
                        <img src="http://localhost/webbansach/admin/upload/hbanner_img3.png" alt="" style="height: 245px;">
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="section-margin">
    <div class="container">
        <div class="section-title section-title--bordered">
            <h2>SÁCH DÀNH CHO BẠN</h2>
        </div>
        <div class="product-slider sb-slick-slider slider-border-single-row" data-slick-setting='{
        "autoplay": true,
        "autoplaySpeed": 8000,
        "slidesToShow": 5,
        "dots":true
    }' data-slick-responsive='[
        {"breakpoint":1500, "settings": {"slidesToShow": 4} },
        {"breakpoint":992, "settings": {"slidesToShow": 3} },
        {"breakpoint":768, "settings": {"slidesToShow": 2} },
        {"breakpoint":480, "settings": {"slidesToShow": 1} },
        {"breakpoint":320, "settings": {"slidesToShow": 1} }
    ]'>
            <?php if ($danhchoban && $danhchoban->num_rows > 0) { ?>
                <?php while($row = $danhchoban->fetch_assoc()){ ?>
                    <div class="single-slide">
                        <div class="product-card">
                            <div class="product-header">
                                <a href="#" class="author">
                                    <?php echo $row['tag']; ?>
                                </a>
                                <h3><a href="san-pham.php?id=<?php echo $row['masanpham']; ?>"><?php echo substr($row['tensanpham'],0,20);?>...
                                  </a></h3>
                            </div>
                            <div class="product-card--body">
                                <div class="card-image">
                                    <img src="<?php echo $row['anhchinh']; ?>" alt="" style="width: 300px; height: 200px;">
                                    <div class="hover-contents">
                                        <a href="san-pham.php?id=<?php echo $row['masanpham']; ?>" class="hover-image">
                                            <img src="<?php echo $row['anhphu1']; ?>" alt="" style="width: 300px; height: 200px;">
                                        </a>
                                        
                                    </div>
                                </div>
                                <div class="price-block">
                                        <span class="price"><?php echo number_format($row['giaban']); ?>đ</span>
                                        <del class="price-old"><?php echo number_format($row['giagoc']); ?>đ</del>
                                        <span class="price-discount">-<?php echo number_format($row['giagoc'] - $row['giaban']); ?></span>
                                    </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <p>Không có sản phẩm dành cho bạn.</p>
            <?php } ?>
        </div>
    </div>
</section>

</div>

<section class="section-margin">
    <h2 class="sr-only">Brand Slider</h2>
    <div class="container">
        <div class="brand-slider sb-slick-slider border-top border-bottom" data-slick-setting='{
                                        "autoplay": true,
                                        "autoplaySpeed": 8000,
                                        "slidesToShow": 6
                                        }' data-slick-responsive='[
            {"breakpoint":992, "settings": {"slidesToShow": 4} },
            {"breakpoint":768, "settings": {"slidesToShow": 3} },
            {"breakpoint":575, "settings": {"slidesToShow": 3} },
            {"breakpoint":480, "settings": {"slidesToShow": 2} },
            {"breakpoint":320, "settings": {"slidesToShow": 1} }
        ]'>
            <div class="single-slide">
                <img src="image/others/brand-1.jpg" alt="">
            </div>
            <div class="single-slide">
                <img src="image/others/brand-2.jpg" alt="">
            </div>
            <div class="single-slide">
                <img src="image/others/brand-3.jpg" alt="">
            </div>
            <div class="single-slide">
                <img src="image/others/brand-4.jpg" alt="">
            </div>
            <div class="single-slide">
                <img src="image/others/brand-5.jpg" alt="">
            </div>
            <div class="single-slide">
                <img src="image/others/brand-6.jpg" alt="">
            </div>
            <div class="single-slide">
                <img src="image/others/brand-1.jpg" alt="">
            </div>
            <div class="single-slide">
                <img src="image/others/brand-2.jpg" alt="">
            </div>
        </div>
    </div>
</section>
<!--=================================
Footer Area
===================================== -->
<?php require(__DIR__.'/layouts/footer.php'); ?>