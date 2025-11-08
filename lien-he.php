<?php require(__DIR__.'/layouts/header.php'); ?>

<!-- Breadcrumb Section -->
<section class="breadcrumb-section">
    <h2 class="sr-only">Site Breadcrumb</h2>
    <div class="container">
        <div class="breadcrumb-contents">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Trang Chủ</a></li>
                    <li class="breadcrumb-item active">Liên Hệ</li>
                </ol>
            </nav>
        </div>
    </div>
</section>

<!-- Contact Section -->
<main class="contact_area inner-page-sec-padding-bottom">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="contact-area-wrapper">
                    
                    <!-- Contact Info -->
                    <div class="row">
                        <div class="col-lg-12 col-md-12 mb--50">
                            <div class="section-title text-center mb--30">
                                <h2>Liên Hệ Với Chúng Tôi</h2>
                                <p>Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn. Hãy để lại thông tin, chúng tôi sẽ phản hồi trong thời gian sớm nhất!</p>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information Cards -->
                    <div class="row mb--60">
                        <div class="col-lg-4 col-md-6 mb--30">
                            <div class="contact-info-card text-center">
                                <div class="icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="content">
                                    <h4>Địa Chỉ</h4>
                                    <p>Hà Nội</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 mb--30">
                            <div class="contact-info-card text-center">
                                <div class="icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="content">
                                    <h4>Điện Thoại</h4>
                                    <p><a href="tel:0397172952">0397172952</a></p>
                                    <p><small>Hỗ trợ 24/7</small></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 mb--30">
                            <div class="contact-info-card text-center">
                                <div class="icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="content">
                                    <h4>Email</h4>
                                    <p><a href="mailto:ntquan2711@gmail.com">ntquan2711@gmail.com</a></p>
                                    <p><small>Phản hồi trong 24h</small></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Success/Error Messages -->
                    <?php if(isset($_GET['status'])): ?>
                        <?php if($_GET['status'] == 'success'): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert" id="statusAlert">
                                <i class="fas fa-check-circle"></i> <strong>Gửi thành công!</strong> Cảm ơn bạn đã liên hệ với chúng tôi. Chúng tôi sẽ phản hồi trong thời gian sớm nhất!
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        <?php elseif($_GET['status'] == 'error'): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert" id="statusAlert">
                                <i class="fas fa-exclamation-circle"></i> <strong>Lỗi!</strong> 
                                <?php echo isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'Có lỗi xảy ra. Vui lòng thử lại!'; ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Contact Form -->
                    <div class="row">
                        <div class="col-lg-8 offset-lg-2">
                            <div class="contact-form-wrapper">
                                <h3 class="contact-title mb--30">Gửi Tin Nhắn</h3>
                                <form action="php/mail.php" method="POST" class="contact-form">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="con_name">Họ Tên <span class="required">*</span></label>
                                                <input type="text" name="con_name" id="con_name" class="form-control" placeholder="Nhập họ tên của bạn" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="con_email">Email <span class="required">*</span></label>
                                                <input type="email" name="con_email" id="con_email" class="form-control" placeholder="Nhập email của bạn" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label for="con_phone">Số Điện Thoại</label>
                                                <input type="text" name="con_phone" id="con_phone" class="form-control" placeholder="Nhập số điện thoại (tùy chọn)">
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label for="con_message">Tin Nhắn <span class="required">*</span></label>
                                                <textarea name="con_message" id="con_message" class="form-control" rows="6" placeholder="Nhập tin nhắn của bạn..." required></textarea>
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <button type="submit" class="btn btn-outlined--primary btn-contact">
                                                <i class="fas fa-paper-plane"></i> Gửi Tin Nhắn
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Map Section -->
                    <div class="row mt--60">
                        <div class="col-lg-12">
                            <div class="google-map-area">
                                <h3 class="contact-title mb--30 text-center">Vị Trí Của Chúng Tôi</h3>
                                <div class="map-container">
                                    <iframe 
                                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3723.4737884515707!2d105.7461535!3d21.0277644!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x313454b991d80fd5%3A0x53cefc99d6b0bf6f!2zSMOgIE7hu5lpLCBWaeG7h3QgTmFt!5e0!3m2!1svi!2s!4v1234567890123!5m2!1svi!2s" 
                                        width="100%" 
                                        height="450" 
                                        style="border:0;" 
                                        allowfullscreen="" 
                                        loading="lazy">
                                    </iframe>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</main>

<style>
/* Contact Info Cards */
.contact-info-card {
    padding: 40px 20px;
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 5px;
    transition: all 0.3s ease;
    height: 100%;
}

.contact-info-card:hover {
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transform: translateY(-5px);
}

.contact-info-card .icon {
    font-size: 48px;
    color: #62ab00;
    margin-bottom: 20px;
}

.contact-info-card .content h4 {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 15px;
    color: #333;
}

.contact-info-card .content p {
    margin-bottom: 5px;
    color: #666;
}

.contact-info-card .content a {
    color: #62ab00;
    text-decoration: none;
}

.contact-info-card .content a:hover {
    text-decoration: underline;
}

/* Contact Form */
.contact-form-wrapper {
    background: #f8f8f8;
    padding: 40px;
    border-radius: 5px;
}

.contact-title {
    font-size: 24px;
    font-weight: 600;
    color: #333;
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #333;
}

.form-group label .required {
    color: #ff0000;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 3px;
    font-size: 14px;
    transition: border-color 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: #62ab00;
}

.btn-contact {
    padding: 12px 40px;
    font-size: 16px;
    font-weight: 600;
}

.btn-contact i {
    margin-right: 8px;
}

/* Alerts */
.alert {
    padding: 15px 20px;
    margin-bottom: 30px;
    border-radius: 5px;
    position: relative;
}

.alert-success {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.alert-danger {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.alert i {
    margin-right: 8px;
}

.alert .close {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    opacity: 0.5;
}

.alert .close:hover {
    opacity: 1;
}

/* Map Container */
.map-container {
    border-radius: 5px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* Responsive */
@media (max-width: 768px) {
    .contact-form-wrapper {
        padding: 25px;
    }
    
    .contact-info-card {
        margin-bottom: 20px;
    }
}
</style>

<script>
// Auto hide alert after 5 seconds and clean URL
document.addEventListener('DOMContentLoaded', function() {
    const alert = document.getElementById('statusAlert');
    if (alert) {
        // Auto hide after 5 seconds
        setTimeout(function() {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s ease';
            setTimeout(function() {
                alert.style.display = 'none';
                // Clean URL
                if (window.history.replaceState) {
                    const url = new URL(window.location);
                    url.searchParams.delete('status');
                    url.searchParams.delete('message');
                    window.history.replaceState({}, document.title, url);
                }
            }, 500);
        }, 5000);
    }
});

// Form validation
document.querySelector('.contact-form').addEventListener('submit', function(e) {
    const name = document.getElementById('con_name').value.trim();
    const email = document.getElementById('con_email').value.trim();
    const message = document.getElementById('con_message').value.trim();
    
    if (!name || !email || !message) {
        e.preventDefault();
        if (typeof window.showToast === 'function') {
            window.showToast('error', 'Vui lòng điền đầy đủ các trường bắt buộc!', 'Thiếu thông tin');
        }
        return false;
    }
    
    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        e.preventDefault();
        if (typeof window.showToast === 'function') {
            window.showToast('error', 'Vui lòng nhập email hợp lệ!', 'Email không đúng');
        }
        return false;
    }
});
</script>

</div>
<?php require(__DIR__.'/layouts/footer.php'); ?>
