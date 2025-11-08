<?php require(__DIR__.'/layouts/header.php'); ?>  
		<section class="breadcrumb-section">
			<h2 class="sr-only">Site Breadcrumb</h2>
			<div class="container">
				<div class="breadcrumb-contents">
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb">
							<li class="breadcrumb-item"><a href="index.html">Trang Chủ</a></li>
							<li class="breadcrumb-item active">Giỏ Hàng</li>
						</ol>
					</nav>
				</div>
			</div>
		</section>
		<!-- Cart Page Start -->
		<main class="cart-page-main-block inner-page-sec-padding-bottom">
			<div class="cart_area cart-area-padding  ">
				<div class="container">
					<div class="page-section-title">
						<h1>Giỏ Hàng</h1>
					</div>
					<div class="row">
						<div class="col-12">
							<form action="#" class="">
								<!-- Cart Table -->
								<div class="cart-table table-responsive mb--40">
									<table class="table">
										<!-- Head Row -->
										<thead>
											<tr>
												<th class="pro-remove"></th>
												<th class="pro-thumbnail">Ảnh</th>
												<th class="pro-title">Sản phẩm</th>
												<th class="pro-price">Giá</th>
												<th class="pro-quantity">Số lượng</th>
												<th class="pro-subtotal">Tổng tiền</th>
											</tr>
										</thead>
										<tbody>

										</tbody>
									</table>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
			<div class="cart-section-2">
				<div class="container">
					<div class="row">
						
						<!-- Cart Summary -->
						<div class="col-lg-6 col-12 d-flex">
							<div class="cart-summary">
								<div class="cart-summary-wrap">
									<h4><span>Đơn Hàng</span></h4>
									<p>Số sản phẩm: <span class="text-primary soluongsanpham">0</span></p>
									<p>Phí ship: <span class="text-primary">0đ</span></p>
									<h2>Tổng tiền: <span class="text-primary tongtien">0đ</span></h2>
								</div>
								<div class="cart-summary-button">
									<a href="thanh-toan.php" class="checkout-btn c-btn btn--primary">Thanh Toán</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</main>
		<!-- Cart Page End -->
	</div>

<style>
/* Nút tăng/giảm số lượng */
.count-input-block {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}

.btn-quantity {
    width: 35px;
    height: 35px;
    border: 1px solid #ddd;
    background-color: #fff;
    color: #333;
    font-size: 18px;
    font-weight: bold;
    cursor: pointer;
    border-radius: 3px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    user-select: none;
}

.btn-quantity:hover {
    background-color: #62ab00;
    color: #fff;
    border-color: #62ab00;
}

.btn-quantity:active {
    transform: scale(0.95);
}

.btn-quantity:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.count-input-block input.number {
    width: 60px;
    text-align: center;
    margin: 0;
    padding: 8px;
    border: 1px solid #ddd;
}

.count-input-block input.number:focus {
    outline: none;
    border-color: #62ab00;
}
</style>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
<script>

    $(document).ready(function() {
        var giohang = localStorage.getItem('giohang')

        var id = 0;

        if(giohang == null){
            window.location.href = 'index.php'
        }else{
            var giohang = JSON.parse(localStorage.getItem('giohang'))
            var tien = 0
            var tongSoLuong = 0
            // Chuẩn hóa đường dẫn ảnh: hỗ trợ cả URL tuyệt đối và đường dẫn tương đối từ DB
            function resolveImageUrl(path){
                if(!path) return '';
                try {
                    if(/^https?:\/\//i.test(path)) return path; // đã là URL tuyệt đối
                } catch(e) {}
                path = String(path).replace(/^\/+/, '');
                var base = window.location.origin + '/webbansach/';
                return base + path;
            }
            for (var i = 0; i < giohang.length; i++) {
                // Remove any non-numeric characters from price and convert to number
                var donGia = parseFloat(giohang[i].giaban.toString().replace(/[^\d]/g, ''))
                var soLuong = parseInt(giohang[i].soluong)
                var gia = donGia * soLuong
                var imgPath = giohang[i].anhchinh || giohang[i].hinhanh || '';
                var imgSrc = resolveImageUrl(imgPath);
                $('tbody').append('<tr data-masp="'+giohang[i].masanpham+'"> <td class="pro-remove"><a href="#" class="xoa" value="'+giohang[i].masanpham+'"><i class="far fa-trash-alt"></i></a> </td> <td class="pro-thumbnail"><a href="#"><img src="'+imgSrc+'" alt="Product"></a></td> <td class="pro-title"><a href="#">'+giohang[i].tensanpham+'</a></td> <td class="pro-price"><span class="don-gia">'+donGia.toLocaleString('vi-VN')+'đ</span></td> <td class="pro-quantity"> <div class="pro-qty"> <div class="count-input-block"> <button type="button" class="btn-quantity btn-minus" data-masp="'+giohang[i].masanpham+'">-</button> <input type="number" min="1" class="form-control text-center number" data="'+giohang[i].masanpham+'" value="'+giohang[i].soluong+'"> <button type="button" class="btn-quantity btn-plus" data-masp="'+giohang[i].masanpham+'">+</button> </div> </div> </td> <td class="pro-subtotal"><span class="tong-tien-sp">'+gia.toLocaleString('vi-VN')+'đ</span></td> </tr>')
                tien += gia
                tongSoLuong += soLuong
            }

            $('.soluongsanpham').html(tongSoLuong + ' sản phẩm')

            $('.tongtien').html(tien.toLocaleString('vi-VN') + 'đ')

            $('.xoa').click(function(e) {
            	e.preventDefault();
            	var masanpham = $(this).attr('value')
            	var confirmDelete = confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?');
            	
            	if(confirmDelete){
	            	var giohangmoi = giohang.filter(function(item) {
		            	return item.masanpham != masanpham;
		            });

		            localStorage.setItem("giohang", JSON.stringify(giohangmoi))
                    if (typeof window.queueToast === 'function') {
                        window.queueToast('success', 'Đã xóa sản phẩm khỏi giỏ hàng', 'Thành công', 3000);
                    }
                    location.reload();
            	}

            })

        }


        // Hàm cập nhật số lượng và tổng tiền
        function updateQuantity(masp, newQuantity) {
            if(newQuantity < 1) {
                newQuantity = 1;
            }
            
            var giohang = JSON.parse(localStorage.getItem('giohang'));
            for (var i = 0; i < giohang.length; i++) {
                if(giohang[i].masanpham == masp){
                    giohang[i].soluong = parseInt(newQuantity);
                    localStorage.setItem('giohang', JSON.stringify(giohang));
                    
                    // Cập nhật UI không cần reload
                    updateCartRow(masp);
                    updateCartSummary();
                    return;
                }
            }
        }
        
        // Hàm cập nhật dòng sản phẩm trong bảng
        function updateCartRow(masp) {
            var giohang = JSON.parse(localStorage.getItem('giohang'));
            var row = $('tr[data-masp="' + masp + '"]');
            
            for (var i = 0; i < giohang.length; i++) {
                if(giohang[i].masanpham == masp){
                    var donGia = parseFloat(giohang[i].giaban.toString().replace(/[^\d]/g, ''));
                    var soLuong = parseInt(giohang[i].soluong);
                    var tongTien = donGia * soLuong;
                    
                    row.find('.number').val(soLuong);
                    row.find('.tong-tien-sp').text(tongTien.toLocaleString('vi-VN') + 'đ');
                    break;
                }
            }
        }
        
        // Hàm cập nhật tổng tiền và số lượng sản phẩm
        function updateCartSummary() {
            var giohang = JSON.parse(localStorage.getItem('giohang'));
            var tongTien = 0;
            var tongSoLuong = 0;
            
            for (var i = 0; i < giohang.length; i++) {
                var donGia = parseFloat(giohang[i].giaban.toString().replace(/[^\d]/g, ''));
                var soLuong = parseInt(giohang[i].soluong);
                tongTien += donGia * soLuong;
                tongSoLuong += soLuong;
            }
            
            $('.soluongsanpham').html(tongSoLuong + ' sản phẩm');
            $('.tongtien').html(tongTien.toLocaleString('vi-VN') + 'đ');
        }
        
        // Xử lý khi người dùng nhập trực tiếp vào input
        $(".number").on("input change", function() {
            var sl = parseInt($(this).val());
            var msp = $(this).attr('data');
            
            if(isNaN(sl) || sl < 1) {
                sl = 1;
                $(this).val(1);
            }
            
            updateQuantity(msp, sl);
        });
        
        // Xử lý nút tăng số lượng
        $(document).on('click', '.btn-plus', function(e) {
            e.preventDefault();
            var msp = $(this).data('masp');
            var input = $('input.number[data="' + msp + '"]');
            var currentQty = parseInt(input.val()) || 1;
            updateQuantity(msp, currentQty + 1);
        });
        
        // Xử lý nút giảm số lượng
        $(document).on('click', '.btn-minus', function(e) {
            e.preventDefault();
            var msp = $(this).data('masp');
            var input = $('input.number[data="' + msp + '"]');
            var currentQty = parseInt(input.val()) || 1;
            if(currentQty > 1) {
                updateQuantity(msp, currentQty - 1);
            }
        });


        
    });
</script>

<?php require(__DIR__.'/layouts/footer.php'); ?>   