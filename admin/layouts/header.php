<?php
session_start();
if(!isset($_SESSION["login"])){
    header("Location: dang-nhap.php");
    die();  
}

// Determine if current user is admin with manhanvien = 1
$__IS_EMP_ADMIN = false;
try {
    require_once(__DIR__ . '/../../database/connect.php');
    if(isset($_SESSION['user'])){
        $u = $_SESSION['user'];
        if($stmt = $conn->prepare('SELECT manhanvien FROM nhanvien WHERE taikhoan = ? LIMIT 1')){
            $stmt->bind_param('s', $u);
            $stmt->execute();
            $res = $stmt->get_result();
            if($row = $res->fetch_assoc()){
                $__IS_EMP_ADMIN = ((int)$row['manhanvien'] === 1);
            }
            $stmt->close();
        }
    }
} catch (Throwable $e) { $__IS_EMP_ADMIN = false; }
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="keywords"
        content="wrappixel, admin dashboard, html css dashboard, web dashboard, bootstrap 5 admin, bootstrap 5, css3 dashboard, bootstrap 5 dashboard, Xtreme lite admin bootstrap 5 dashboard, frontend, responsive bootstrap 5 admin template, Xtreme admin lite design, Xtreme admin lite dashboard bootstrap 5 dashboard template">
    <meta name="description"
        content="Xtreme Admin Lite is powerful and clean admin dashboard template, inpired from Bootstrap Framework">
    <meta name="robots" content="noindex,nofollow">
    <title>Trang Quản Trị - Admin</title>
    <link rel="canonical" href="https://www.wrappixel.com/templates/xtreme-admin-lite/" />
    <link rel="icon" type="image/png" sizes="16x16" href="/webbansach/admin/assets/images/favicon.png">
    <link href="/webbansach/admin/assets/libs/chartist/dist/chartist.min.css" rel="stylesheet">
    <link href="/webbansach/admin/dist/css/style.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="/webbansach/css/toast.css">
</head>

<body>
    <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div>
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">
        <header class="topbar" data-navbarbg="skin5">
            <nav class="navbar top-navbar navbar-expand-md navbar-dark">
                <div class="navbar-header" data-logobg="skin5">
                    <a class="navbar-brand" href="index.php">
                        <b class="logo-icon">
                            <img src="/webbansach/admin/assets/images/logo-icon.png" alt="homepage" class="dark-logo" />
                            <!-- Light Logo icon -->
                            <img src="/webbansach/admin/assets/images/logo-light-icon.png" alt="homepage" class="light-logo" />
                        </b>
                        <span class="logo-text">
                            <!-- dark Logo text -->
                            <img src="/webbansach/admin/assets/images/logo-text.png" alt="homepage" class="dark-logo" />
                            <!-- Light Logo text -->
                            <img src="/webbansach/admin/assets/images/logo-light-text.png" class="light-logo" alt="homepage" />
                        </span>
                    </a>
                    <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)"><i
                            class="ti-menu ti-close"></i></a>
                </div>
                <div class="navbar-collapse collapse" id="navbarSupportedContent" data-navbarbg="skin5">
                    <ul class="navbar-nav float-start me-auto">
                        <li class="nav-item search-box"> <a class="nav-link waves-effect waves-dark"
                                href="javascript:void(0)"><i class="ti-search"></i></a>
                            <form class="app-search position-absolute">
                                <input type="text" class="form-control" placeholder="Search &amp; enter"> <a
                                    class="srh-btn"><i class="ti-close"></i></a>
                            </form>
                        </li>
                    </ul>
                   
                </div>
            </nav>
        </header>
        <aside class="left-sidebar" data-sidebarbg="skin6">
            <!-- Sidebar scroll-->
            <div class="scroll-sidebar">
                <!-- Sidebar navigation-->
                <nav class="sidebar-nav">
                    <ul id="sidebarnav">
                        <!-- User Profile-->
                        <li>
                            <!-- User Profile-->
                            <div class="user-profile d-flex no-block dropdown m-t-20">
                                <div class="user-pic"><img src="/webbansach/admin/assets/images/users/1.jpg" alt="users"
                                        class="rounded-circle" width="40" /></div>
                                <div class="user-content hide-menu m-l-10">
                                    <a href="#" class="" id="Userdd" role="button"
                                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <h5 class="m-b-0 user-name font-medium"><?php echo $_SESSION['fullname']; ?> <i
                                                class="fa fa-angle-down"></i></h5>
                                        <span class="op-5 user-email"><?php echo $_SESSION['user']; ?></span>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="Userdd">
                                        
                                        <a class="dropdown-item" href="ca-nhan.php"><i
                                                class="ti-settings m-r-5 m-l-5"></i> Tài Khoản</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="dang-xuat.php"><i
                                                class="fa fa-power-off m-r-5 m-l-5"></i> Đăng Xuất</a>
                                    </div>
                                </div>
                            </div>
                            <!-- End User Profile-->
                        </li>
                        <!-- User Profile-->
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                href="index.php" aria-expanded="false"><i class="mdi mdi-view-dashboard"></i><span
                                    class="hide-menu">Dashboard</span></a></li>
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                href="san-pham.php" aria-expanded="false"><i class="mdi mdi-border-all"></i><span class="hide-menu">Sản Phẩm</span></a></li>
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                href="chuyen-muc.php" aria-expanded="false"><i class="mdi mdi-file"></i><span
                                    class="hide-menu">Chuyên Mục</span></a></li>
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                href="don-hang.php" aria-expanded="false"><i
                                    class="mdi mdi-account-network"></i><span
                                    class="hide-menu">Đơn Hàng</span></a></li>
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                href="khach-hang.php" aria-expanded="false"><i class="mdi mdi-face"></i><span
                                    class="hide-menu">Khách Hàng</span></a></li>
                        <?php if($__IS_EMP_ADMIN){ ?>
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                href="nhan-vien.php" aria-expanded="false"><i class="mdi mdi-account"></i><span
                                    class="hide-menu">Nhân Viên</span></a></li>
                        <?php } ?>
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                href="lien-he.php" aria-expanded="false"><i class="mdi mdi-email-outline"></i><span
                                    class="hide-menu">Phản Hồi</span></a></li>
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link"
                                href="banner.php" aria-expanded="false"><i class="mdi mdi-image-multiple"></i><span
                                    class="hide-menu">Banner</span></a></li>
                    </ul>

                </nav>
                <!-- End Sidebar navigation -->
            </div>
            <!-- End Sidebar scroll-->
        </aside>

        <div id="toast-container"></div>
        <script>
        // Override default alert
        window.originalAlert = window.alert;
        window.alert = function(message) {
            // Use our toast notification instead
            const toast = document.createElement('div');
            toast.className = 'toast info show';
            toast.innerHTML = `
                <span class="toast-icon"><i class="fas fa-info-circle"></i></span>
                <div class="toast-content">
                    <div class="toast-message">${message}</div>
                </div>
                <button class="toast-close">&times;</button>
                <div class="toast-progress"></div>
            `;
            
            const container = document.getElementById('toast-container') || document.body;
            container.appendChild(toast);
            
            // Start progress bar animation
            const progressBar = toast.querySelector('.toast-progress');
            if (progressBar) {
                progressBar.style.animation = 'progress 5000ms linear forwards';
            }
            
            // Auto hide after 5 seconds
            const hideTimer = setTimeout(() => {
                hideToast(toast);
            }, 5000);
            
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
                const remaining = getRemainingTime(toast, 5000);
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
        </script>