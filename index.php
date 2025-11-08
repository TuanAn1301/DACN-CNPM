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

// Lấy banner từ database
$sql_banner_slide = "SELECT * FROM banner WHERE vitri = 'slide' AND trangthai = 1 ORDER BY thutu";
$banner_slide = queryResult($conn, $sql_banner_slide);

$sql_banner_promo1 = "SELECT * FROM banner WHERE vitri = 'promo1' AND trangthai = 1 ORDER BY thutu LIMIT 2";
$banner_promo1 = queryResult($conn, $sql_banner_promo1);

$sql_banner_promo2 = "SELECT * FROM banner WHERE vitri = 'promo2' AND trangthai = 1 ORDER BY thutu";
$banner_promo2 = queryResult($conn, $sql_banner_promo2);

$sql_banner_promo3 = "SELECT * FROM banner WHERE vitri = 'promo3' AND trangthai = 1 ORDER BY thutu LIMIT 2";
$banner_promo3 = queryResult($conn, $sql_banner_promo3);

// Helper function để xử lý link banner
function processBannerLink($duongdan, $banner_id) {
    if(strpos($duongdan, 'cart:') === 0) {
        // Link dạng cart: - thêm sản phẩm vào giỏ hàng
        $payload = trim(str_replace('cart:', '', $duongdan));
        $product_ids = [];
        // Hỗ trợ 3 dạng: JSON [1,2,3], chuỗi "1,2,3" hoặc một ID đơn lẻ "1"
        if($payload !== '') {
            if($payload[0] === '[') {
                $decoded = json_decode($payload, true);
                if(is_array($decoded)) { $product_ids = $decoded; }
            } else if(strpos($payload, ',') !== false) {
                $parts = explode(',', $payload);
                foreach($parts as $p) { $product_ids[] = (int)trim($p); }
            } else {
                $product_ids[] = (int)$payload;
            }
        }
        // Lọc các ID hợp lệ và loại trùng
        $product_ids = array_values(array_unique(array_filter(array_map('intval', $product_ids), function($v){ return $v > 0; })));
        $onclick = 'addBannerProductsToCart(' . json_encode($product_ids, JSON_UNESCAPED_UNICODE) . '); return false;';
        return [
            'type' => 'cart',
            'products' => $product_ids,
            'href' => '#',
            'onclick' => $onclick
        ];
    } else {
        // Link thông thường
        return [
            'type' => 'link',
            'href' => $duongdan ? $duongdan : '#',
            'onclick' => ''
        ];
    }
}
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
        <?php if ($banner_slide && $banner_slide->num_rows > 0) { while($bn = $banner_slide->fetch_assoc()){ 
                $linkData = processBannerLink($bn['duongdan'], $bn['mabanner']);
        ?>
            <div class="single-slide bg-shade-whisper">
                <div class="container">
                    <div class="home-content text-center text-sm-left position-relative">
                        <div class="hero-partial-image image-right">
                            <a href="<?php echo isset($linkData['href']) ? $linkData['href'] : '#'; ?>" 
                               class="banner-link"
                               <?php if(!empty($linkData['onclick'])) echo 'onclick="' . $linkData['onclick'] . '"'; ?>>
                                <img src="http://localhost/webbansach/<?php echo $bn['hinhanh']; ?>" alt="<?php echo $bn['tenbanner']; ?>" style="width: 100%; max-width: 100%; height: auto;">
                            </a>
                        </div>
                        <div class="row no-gutters ">
                            <div class="col-xl-6 col-md-6 col-sm-7">
                                <div class="home-content-inner content-left-side">
                                    <h1 style="line-height: 60px;"><?php echo $bn['tenbanner']; ?></h1>
                                    <?php if($linkData['type'] === 'link' && !empty($linkData['href']) && $linkData['href'] !== '#') { ?>
                                        <a href="<?php echo $linkData['href']; ?>" class="btn btn-outlined--primary">Mua Ngay</a>
                                    <?php } else { ?>
                                        <a href="#" class="btn btn-outlined--primary" <?php if(!empty($linkData['onclick'])) echo 'onclick="' . $linkData['onclick'] . '"'; ?>>Mua Ngay</a>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } } ?>
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
                        <p>Tổng đài: 0397172952</p>
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
            <?php 
            if($banner_promo1 && $banner_promo1->num_rows > 0) {
                while($banner = $banner_promo1->fetch_assoc()){ 
                    $linkData = processBannerLink($banner['duongdan'], $banner['mabanner']);
            ?>
                <div class="col-lg-6 col-md-6 mb--30">
                    <a href="<?php echo isset($linkData['href']) ? $linkData['href'] : '#'; ?>" 
                       class="promo-image promo-overlay banner-link"
                       <?php if(!empty($linkData['onclick'])) echo 'onclick="' . $linkData['onclick'] . '"'; ?>>
                        <img src="http://localhost/webbansach/<?php echo $banner['hinhanh']; ?>" alt="<?php echo $banner['tenbanner']; ?>">
                    </a>
                </div>
            <?php 
                }
            } else { 
                // Hiển thị banner mặc định nếu chưa có trong database
            ?>
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
            <?php } ?>
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
            <?php 
            if($banner_promo3 && $banner_promo3->num_rows > 0) {
                $promo_banners = [];
                while($banner = $banner_promo3->fetch_assoc()){ 
                    $promo_banners[] = $banner;
                }
                
                // Banner đầu tiên (col-lg-8)
                if(isset($promo_banners[0])){
                    $linkData0 = processBannerLink($promo_banners[0]['duongdan'], $promo_banners[0]['mabanner']);
            ?>
                <div class="col-lg-8 mb--30">
                    <div class="promo-wrapper promo-type-one">
                        <a href="<?php echo isset($linkData0['href']) ? $linkData0['href'] : '#'; ?>" 
                           class="promo-image promo-overlay bg-image banner-link"
                           <?php if(!empty($linkData0['onclick'])) echo 'onclick="' . $linkData0['onclick'] . '"'; ?>>
                            <img src="http://localhost/webbansach/<?php echo $promo_banners[0]['hinhanh']; ?>" alt="<?php echo $promo_banners[0]['tenbanner']; ?>">
                        </a>
                        <div class="promo-text">
                            <div class="promo-text-inner">
                                <h2>Mua 3. Tặng miễn phí 1</h2>
                                <h3>Giảm giá lên đến 50% cho khách hàng</h3>
                                <a href="tat-ca-san-pham.php" class="btn btn-outlined--red-faded">XEM THÊM</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php 
                }
                
                // Banner thứ hai (col-lg-4)
                if(isset($promo_banners[1])){
                    $linkData1 = processBannerLink($promo_banners[1]['duongdan'], $promo_banners[1]['mabanner']);
            ?>
                <div class="col-lg-4 mb--30">
                    <div class="promo-wrapper promo-type-two ">
                        <a href="<?php echo isset($linkData1['href']) ? $linkData1['href'] : '#'; ?>" 
                           class="promo-image promo-overlay bg-image banner-link"
                           <?php if(!empty($linkData1['onclick'])) echo 'onclick="' . $linkData1['onclick'] . '"'; ?>>
                            <img src="http://localhost/webbansach/<?php echo $promo_banners[1]['hinhanh']; ?>" alt="<?php echo $promo_banners[1]['tenbanner']; ?>" style="height: 245px;">
                        </a>
                    </div>
                </div>
            <?php 
                }
            } else { 
                // Hiển thị banner mặc định nếu chưa có trong database
            ?>
                <div class="col-lg-8 mb--30">
                    <div class="promo-wrapper promo-type-one">
                        <a href="#" class="promo-image  promo-overlay bg-image">
                            <img src="http://localhost/webbansach/admin/upload/breadcrumb_bg2.png" alt="">
                        </a>
                        <div class="promo-text">
                            <div class="promo-text-inner">
                                <h2>Mua 3. Tặng miễn phí 1</h2>
                                <h3>Giảm giá lên đến 50% cho khách hàng</h3>
                                <a href="tat-ca-san-pham.php" class="btn btn-outlined--red-faded">XEM THÊM</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb--30">
                    <div class="promo-wrapper promo-type-two ">
                        <a href="tat-ca-san-pham.php" class="promo-image promo-overlay bg-image">
                            <img src="http://localhost/webbansach/admin/upload/hbanner_img3.png" alt="" style="height: 245px;">
                        </a>
                    </div>
                </div>
            <?php } ?>
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

<style>
 .toast-container {
   position: fixed;
   top: 16px;
   right: 16px;
   z-index: 9999;
   display: flex;
   flex-direction: column;
   gap: 10px;
 }
 .toast {
   min-width: 260px;
   max-width: 360px;
   padding: 12px 14px;
   background: #2e7d32; /* default success */
   color: #fff;
   box-shadow: 0 8px 24px rgba(0,0,0,0.15);
   border-radius: 0; /* square corners */
   transform: translateX(120%);
   opacity: 0;
   transition: transform 0.35s ease, opacity 0.35s ease;
   font-size: 14px;
   line-height: 1.4;
 }
 .toast.toast-error { background: #c62828; }
 .toast.show { transform: translateX(0); opacity: 1; }
 .toast.hide { transform: translateX(120%); opacity: 0; }
 .toast-title { font-weight: 600; margin-bottom: 4px; }
 .toast-message { margin: 0; }
 </style>

<script>
// Toast notifications (top-right, slide in/out, auto hide 3s)
function ensureToastContainer(){
    var c = document.querySelector('.toast-container');
    if(!c){
        c = document.createElement('div');
        c.className = 'toast-container';
        document.body.appendChild(c);
    }
    return c;
}

function showToast(type, message, title){
    var container = ensureToastContainer();
    var toast = document.createElement('div');
    toast.className = 'toast' + (type === 'error' ? ' toast-error' : '');
    var inner = '';
    if(title){ inner += '<div class="toast-title">'+title+'</div>'; }
    inner += '<div class="toast-message">'+message+'</div>';
    toast.innerHTML = inner;
    container.appendChild(toast);
    // trigger show
    requestAnimationFrame(function(){ toast.classList.add('show'); });
    // auto hide after 3s
    setTimeout(function(){
        toast.classList.remove('show');
        toast.classList.add('hide');
        setTimeout(function(){ toast.remove(); }, 400);
    }, 3000);
}

// Function để thêm sản phẩm vào giỏ hàng từ banner
function addBannerProductsToCart(productIds) {
    if(!Array.isArray(productIds) || productIds.length === 0) {
        showToast('error', 'Không có sản phẩm nào được chọn!', 'Thất bại');
        return;
    }
    
    // Lấy thông tin sản phẩm từ database qua AJAX
    $.ajax({
        url: 'php/get-product-info.php',
        type: 'POST',
        data: { product_ids: JSON.stringify(productIds) },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                var products = response.products;
                var giohang = localStorage.getItem('giohang');
                var cart = giohang ? JSON.parse(giohang) : [];
                var addedCount = 0;
                
                // Thêm từng sản phẩm vào giỏ hàng
                products.forEach(function(product) {
                    // Kiểm tra xem sản phẩm đã có trong giỏ chưa
                    var existingIndex = cart.findIndex(function(item) {
                        return item.masanpham == product.masanpham;
                    });
                    
                    if(existingIndex >= 0) {
                        // Nếu đã có, tăng số lượng
                        cart[existingIndex].soluong = parseInt(cart[existingIndex].soluong) + 1;
                    } else {
                        // Nếu chưa có, thêm mới
                        cart.push({
                            masanpham: product.masanpham,
                            tensanpham: product.tensanpham,
                            giaban: product.giaban,
                            hinhanh: product.anhchinh,
                            soluong: 1
                        });
                    }
                    addedCount++;
                });
                
                // Lưu lại vào localStorage
                localStorage.setItem('giohang', JSON.stringify(cart));
                // Đánh dấu giỏ hàng được thêm từ banner (để dọn khi thoát giỏ nếu không thanh toán)
                try {
                    localStorage.setItem('bannerAdded', '1');
                    localStorage.setItem('bannerItems', JSON.stringify(productIds));
                    localStorage.setItem('bannerCheckout', '0');
                } catch(e) {}
                
                // Cập nhật số lượng giỏ hàng trên header
                updateCartCount();
                
                // Lưu thông báo để hiển thị sau khi chuyển trang (tồn tại 3s)
                try {
                    sessionStorage.setItem('pendingToast', JSON.stringify({
                        type: 'success',
                        title: 'Thành công',
                        message: 'Đã thêm ' + addedCount + ' sản phẩm vào giỏ hàng!'
                    }));
                } catch(e) {}

                // Chuyển đến trang giỏ hàng
                window.location.href = 'gio-hang.php';
            } else {
                showToast('error', (response.message || 'Không thể lấy thông tin sản phẩm'), 'Thất bại');
            }
        },
        error: function() {
            showToast('error', 'Lỗi kết nối! Vui lòng thử lại.', 'Thất bại');
        }
    });
}

// Function cập nhật số lượng giỏ hàng
function updateCartCount() {
    var giohang = localStorage.getItem('giohang');
    if(giohang) {
        var cart = JSON.parse(giohang);
        var count = cart.length;
        // Cập nhật badge số lượng nếu có
        $('.cart-count, .minicart-count').text(count);
    }
}
</script>

<?php require(__DIR__.'/layouts/footer.php'); ?>