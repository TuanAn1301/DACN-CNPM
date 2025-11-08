<?php 

session_start(); 

require('./database/connect.php'); 
require('./database/query.php');

$sql_chuyenmuc = "SELECT * FROM chuyenmuc";
$chuyenmuc = queryResult($conn,$sql_chuyenmuc);


?>


<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Pustok - Cửa Hàng Bán Sách Trực Tuyến!</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Use Minified Plugins Version For Fast Page Load -->
    <link rel="stylesheet" type="text/css" media="screen" href="css/plugins.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="css/main.css" />
    <link rel="shortcut icon" type="image/x-icon" href="image/favicon.ico">
    <link rel="stylesheet" type="text/css" media="screen" href="css/toast.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <script>
    // Dọn các sản phẩm thêm từ banner trên mọi trang KHÔNG PHẢI giỏ hàng / thanh toán
    (function(){
        function removeBannerProductsFromCart(){
            try {
                var bannerItems = [];
                try { bannerItems = JSON.parse(localStorage.getItem('bannerItems') || '[]'); } catch(e) { bannerItems = []; }
                if(!Array.isArray(bannerItems) || bannerItems.length === 0) return;
                var cartStr = localStorage.getItem('giohang');
                if(!cartStr) return;
                var cart = JSON.parse(cartStr);
                var filtered = cart.filter(function(item){
                    return bannerItems.indexOf(String(item.masanpham)) === -1 && bannerItems.indexOf(Number(item.masanpham)) === -1;
                });
                localStorage.setItem('giohang', JSON.stringify(filtered));
                localStorage.setItem('bannerAdded','0');
                localStorage.removeItem('bannerItems');
                localStorage.removeItem('bannerTransition');
            } catch(e) {}
        }

        document.addEventListener('DOMContentLoaded', function(){
            try {
                var path = (location.pathname || '').toLowerCase();
                var isCart = path.indexOf('gio-hang.php') !== -1;
                var isCheckout = path.indexOf('thanh-toan.php') !== -1;
                var bannerAdded = localStorage.getItem('bannerAdded') === '1';
                if(bannerAdded && !(isCart || isCheckout)){
                    removeBannerProductsFromCart();
                }
            } catch(e) {}
        });
    })();
    </script>
    <div id="toast-container"></div>
    <script>
    (function(){
        // Icons for different toast types
        const icons = {
            success: '<i class="fas fa-check-circle"></i>',
            error: '<i class="fas fa-exclamation-circle"></i>',
            warning: '<i class="fas fa-exclamation-triangle"></i>',
            info: '<i class="fas fa-info-circle"></i>'
        };

        // Ensure toast container exists
        function ensureToastContainer() {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                document.body.appendChild(container);
            }
            return container;
        }

        // Show toast function
        window.showToast = function(type, message, title, duration = 5000) {
            const container = ensureToastContainer();
            const toast = document.createElement('div');
            
            // Set toast classes
            toast.className = `toast ${type} show`;
            
            // Create toast HTML
            toast.innerHTML = `
                <span class="toast-icon">${icons[type] || icons.info}</span>
                <div class="toast-content">
                    ${title ? `<div class="toast-title">${title}</div>` : ''}
                    <div class="toast-message">${message}</div>
                </div>
                <button class="toast-close">&times;</button>
                <div class="toast-progress"></div>
            `;
            
            // Add to container
            container.appendChild(toast);
            
            // Start progress bar animation
            const progressBar = toast.querySelector('.toast-progress');
            if (progressBar) {
                progressBar.style.animation = `progress ${duration}ms linear forwards`;
            }
            
            // Auto hide after duration
            const hideTimer = setTimeout(() => {
                hideToast(toast);
            }, duration);
            
            // Close button event
            const closeBtn = toast.querySelector('.toast-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    clearTimeout(hideTimer);
                    hideToast(toast);
                });
            }
            
            // Pause on hover
            toast.addEventListener('mouseenter', () => {
                clearTimeout(hideTimer);
                if (progressBar) {
                    const progress = getComputedStyle(progressBar).transform;
                    progressBar.style.transform = progress;
                    progressBar.style.animationPlayState = 'paused';
                }
            });
            
            toast.addEventListener('mouseleave', () => {
                const remaining = getRemainingTime(toast, duration);
                if (progressBar) {
                    progressBar.style.animation = `progress ${remaining}ms linear forwards`;
                }
                setTimeout(() => {
                    hideToast(toast);
                }, remaining);
            });
            
            // Remove toast after animation
            toast.addEventListener('animationend', (e) => {
                if (e.animationName === 'slideOutRight') {
                    toast.remove();
                }
            });
            
            return toast;
        };
        
        // Helper to get remaining time for progress bar
        function getRemainingTime(toast, totalDuration) {
            const startTime = parseInt(toast.getAttribute('data-start-time') || Date.now());
            const elapsed = Date.now() - startTime;
            return Math.max(0, totalDuration - elapsed);
        }
        
        // Hide toast with animation
        function hideToast(toast) {
            toast.classList.remove('show');
            toast.classList.add('hide');
        }
        
        // Queue toast across page reloads
        window.queueToast = function(type, message, title, duration = 5000) {
            try {
                sessionStorage.setItem('pendingToast', JSON.stringify({ 
                    type: type, 
                    message: message, 
                    title: title, 
                    duration: duration 
                }));
            } catch(e) {
                console.error('Failed to queue toast:', e);
            }
        };
        
        // Show pending toasts on page load
        document.addEventListener('DOMContentLoaded', function() {
            try {
                const pending = sessionStorage.getItem('pendingToast');
                if (pending) {
                    const data = JSON.parse(pending);
                    if (data && data.message) {
                        window.showToast(
                            data.type || 'info', 
                            data.message, 
                            data.title, 
                            data.duration
                        );
                    }
                    sessionStorage.removeItem('pendingToast');
                }
            } catch(e) {
                console.error('Error showing pending toast:', e);
            }
        });
    })();
    </script>
    <div class="site-wrapper" id="top">
        <div class="site-header d-none d-lg-block">
            <div class="header-middle pt--10 pb--10">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-3 ">
                            <a href="index.php" class="site-brand">
                                <img src="image/logo.png" alt="">
                            </a>
                        </div>
                        <div class="col-lg-3">
                            <div class="header-phone ">
                                <div class="icon">
                                    <i class="fas fa-headphones-alt"></i>
                                </div>
                                <div class="text">
                                    <p>Hỗ trợ 24/7</p>
                                    <p class="font-weight-bold number">0397172952</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="main-navigation flex-lg-right">
                                <ul class="main-menu menu-right " style="display: -webkit-inline-box;">
                                    <li class="menu-item ">
                                        <a href="index.php">Trang Chủ</a>
                                    </li>
                                    <!-- Shop -->
                                    <li class="menu-item">
                                        <a href="tat-ca-san-pham.php">Sản Phẩm</a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="lien-he.php">Liên Hệ</a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="tai-khoan.php">Tài Khoản</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="header-bottom pb--10">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-3">
                            <nav class="category-nav">
                                <div>
                                    <a href="javascript:void(0)" class="category-trigger"><i
                                            class="fa fa-bars"></i>Chuyên Mục</a>
                                    <ul class="category-menu">
                                        <?php while($row = $chuyenmuc->fetch_assoc()){ ?>
                                            <li class="cat-item "><a href="tim-kiem.php?cm=<?php echo $row['machuyenmuc']; ?>"><?php echo $row['tenchuyenmuc']; ?></a></li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </nav>
                        </div>
                        <div class="col-lg-5">
                            <form method="GET" action="tim-kiem.php">
                                <div class="header-search-block">
                                    <?php if (isset($_GET['cm'])) { ?>
                                        <input type="hidden" name="cm" value="<?php echo (int)$_GET['cm']; ?>">
                                    <?php } ?>
                                    <input type="text" placeholder="Tìm kiếm sản phẩm..." name="tensach">
                                    <button type="submit">Tìm Kiếm</button>
                                </div>
                            </form>
                            
                        </div>
                        <div class="col-lg-4">
                            <div class="main-navigation flex-lg-right">
                                <div class="cart-widget">
                                    <?php if(isset($_SESSION['dangnhap'])){ ?>
                                        <div class="login-block">
                                            <a href="#" class="font-weight-bold"><?php echo $_SESSION['taikhoan']; ?></a><br>
                                            <a href="dang-xuat.php">Đăng Xuất</a>
                                        </div>
                                    <?php }else{ ?>
                                        <div class="login-block">
                                            <a href="dang-nhap.php" class="font-weight-bold">Đăng Nhập</a> <br>
                                            <span>hoặc</span><a href="dang-nhap.php">Đăng Ký</a>
                                        </div>
                                    <?php } ?>
                                    <div class="cart-block">
                                        <div class="cart-total">
                                            <span class="text-number slgiohang">
                                                0
                                            </span>
                                            <span class="text-item">
                                                Giỏ Hàng
                                            </span>
                                            <span class="price tiengiohang">
                                                0
                                                <i class="fas fa-chevron-down"></i>
                                            </span>
                                        </div>
                                        <div class="cart-dropdown-block ">
                                            <div class="sanpham_giohang">
                                                
                                            </div>
                                            <div class=" single-cart-block dh">
                                                
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="site-mobile-menu">
            <header class="mobile-header d-block d-lg-none pt--10 pb-md--10">
                <div class="container">
                    <div class="row align-items-sm-end align-items-center">
                        <div class="col-md-4 col-7">
                            <a href="index.php" class="site-brand">
                                <img src="image/logo.png" alt="">
                            </a>
                        </div>
                        <div class="col-md-5 order-3 order-md-2">
                            <nav class="category-nav   ">
                                <div>
                                    <a href="javascript:void(0)" class="category-trigger"><i
                                            class="fa fa-bars"></i>Chuyên Mục</a>
                                    <ul class="category-menu">
                                        <?php while($row = $chuyenmuc->fetch_assoc()){ ?>
                                            <li class="cat-item "><a href="tim-kiem.php?cm=<?php echo $row['machuyenmuc']; ?>"><?php echo $row['tenchuyenmuc']; ?></a></li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </nav>
                        </div>
                        <div class="col-md-3 col-5  order-md-3 text-right">
                            <div class="mobile-header-btns header-top-widget">
                                <ul class="header-links">
                                    <li class="sin-link">
                                        <a href="cart.html" class="cart-link link-icon"><i class="ion-bag"></i></a>
                                    </li>
                                    <li class="sin-link">
                                        <a href="javascript:" class="link-icon hamburgur-icon off-canvas-btn"><i
                                                class="ion-navicon"></i></a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <!--Off Canvas Navigation Start-->
            <aside class="off-canvas-wrapper">
                <div class="btn-close-off-canvas">
                    <i class="ion-android-close"></i>
                </div>
                <div class="off-canvas-inner">
                    <!-- search box start -->
                    <div class="search-box offcanvas">
                        <form>
                            <input type="text" placeholder="Search Here">
                            <button class="search-btn"><i class="ion-ios-search-strong"></i></button>
                        </form>
                    </div>
                    <!-- search box end -->
                    <!-- mobile menu start -->
                    <div class="mobile-navigation">
                        <!-- mobile menu navigation start -->
                        <nav class="off-canvas-nav">
                            <ul class="mobile-menu main-mobile-menu">
                                <li class="menu-item ">
                                        <a href="index.php">Trang Chủ</a>
                                    </li>
                                    <!-- Shop -->
                                    <li class="menu-item">
                                        <a href="tat-ca-san-pham.php">Sản Phẩm</a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="lien-he.php">Liên Hệ</a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="contact.html">Tài Khoản</a>
                                </li>
                            </ul>
                        </nav>
                        <!-- mobile menu navigation end -->
                    </div>
                    <!-- mobile menu end -->
                    
                    <div class="off-canvas-bottom">
                        <div class="contact-list mb--10">
                            <a href="" class="sin-contact"><i class="fas fa-mobile-alt"></i>0397172952</a>
                            <a href="" class="sin-contact"><i class="fas fa-envelope"></i>ntquan2711@gmail.com</a>
                        </div>
                        <div class="off-canvas-social">
                            <a href="#" class="single-icon"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="single-icon"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="single-icon"><i class="fas fa-rss"></i></a>
                            <a href="#" class="single-icon"><i class="fab fa-youtube"></i></a>
                            <a href="#" class="single-icon"><i class="fab fa-google-plus-g"></i></a>
                            <a href="#" class="single-icon"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                </div>
            </aside>
            <!--Off Canvas Navigation End-->
        </div>

        <div class="sticky-init fixed-header common-sticky">
            <div class="container d-none d-lg-block">
                <div class="row align-items-center">
                    <div class="col-lg-4">
                        <a href="index.php" class="site-brand">
                            <img src="image/logo.png" alt="">
                        </a>
                    </div>
                    <div class="col-lg-8">
                        <div class="main-navigation flex-lg-right">
                            <ul class="main-menu menu-right ">
                                <li class="menu-item ">
                                        <a href="index.php">Trang Chủ</a>
                                    </li>
                                    <!-- Shop -->
                                    <li class="menu-item">
                                        <a href="tat-ca-san-pham.php">Sản Phẩm</a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="nhan-vien.php">Nhân Viên</a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="lien-he.php">Liên Hệ</a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="contact.html">Tài Khoản</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>