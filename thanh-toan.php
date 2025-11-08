<?php require(__DIR__.'/layouts/header.php'); ?>  
<?php 
if(!isset($_SESSION['dangnhap'])){
	echo "<script>window.location.href = 'dang-nhap.php';</script>";
}

$taikhoan = $_SESSION['taikhoan'];
$sql_khachhang = "SELECT * FROM khachhang WHERE taikhoan = '".$taikhoan."'";
$rs_khachhang = queryResult($conn, $sql_khachhang);
if ($rs_khachhang && $rs_khachhang->num_rows > 0) {
    $khachhang = $rs_khachhang->fetch_assoc();
} else {
    echo "<script>window.location.href = 'dang-nhap.php';</script>";
    exit;
}
 ?>
		<section class="breadcrumb-section">
			<h2 class="sr-only">Site Breadcrumb</h2>
			<div class="container">
				<div class="breadcrumb-contents">
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb">
							<li class="breadcrumb-item"><a href="index.html">Trang Chủ</a></li>
							<li class="breadcrumb-item active">Thanh Toán</li>
						</ol>
					</nav>
				</div>
			</div>
		</section>
		<main id="content" class="page-section inner-page-sec-padding-bottom space-db--20">
			<form method="POST">
				<div class="container">
					<div class="row">
						<div class="col-12">
							<!-- Checkout Form s-->
							<div class="checkout-form">
								<div class="row row-40">
									<div class="col-lg-7 mb--20">
										<!-- Billing Address -->
										<div id="billing-form" class="mb-40">
											<h4 class="checkout-title">Thông Tin Thanh Toán
                                                <button type="button" class="btn btn-outlined--primary btn-sm" data-toggle="modal" data-target="#modalChonDiaChi" style="float:right;">Chọn địa chỉ đã lưu</button>
                                            </h4>
											<div class="row">
												<div class="col-md-6 col-12 mb--20">
													<label>Họ tên</label>
													<input type="text" value="<?php echo $khachhang["tenkhachhang"]; ?>" placeholder="Họ tên người nhận" required class="hoten" disabled>
												</div>
												<div class="col-md-6 col-12 mb--20">
													<label>Số điện thoại</label>
													<input type="text" placeholder="Số điện thoại" value="<?php echo $khachhang["sodienthoai"]; ?>" required class="sodienthoai" disabled>
												</div>
												<div class="col-12 mb--20">
													<label>Số nhà</label>
													<input type="text" placeholder="Số nhà" required class="sonha">
												</div>
												<div class="col-12 mb--20">
													<label>Thôn/Xóm</label>
													<input type="text" placeholder="Thôn hoặc xóm" required class="thonxom">
												</div>
												<div class="col-12 mb--20">
													<label>Phường/Xã</label>
													<input type="text" placeholder="Phường hoặc xã" required class="phuongxa">
												</div>
												<div class="col-12 mb--20">
													<label>Huyện</label>
													<input type="text" placeholder="Huyện" required class="huyen">
												</div>
												<div class="col-12 mb--20">
													<label>Thành phố</label>
													<input type="text" placeholder="Thành phố hoặc tỉnh" required class="tinhthanh">
												</div>
											</div>
										</div>

									</div>
									<div class="col-lg-5">
										<div class="row">
											<!-- Cart Total -->
											<div class="col-12">
												<div class="checkout-cart-total">
													<h2 class="checkout-title">THÔNG TIN ĐƠN HÀNG</h2>
													<h4>Sản phẩm <span>Tổng tiền</span></h4>
													<ul class="thongtinsanpham">
														
														
													</ul>
													<p>Số lượng sản phẩm <span class="sl">0</span></p>
													<p>Phí giao hàng <span>0</span></p>
													<h4>Tổng tiền <span class="tt">0</span></h4>
													<br>
													<div class="term-block">
														<input type="checkbox" id="accept_terms2">
														<label for="accept_terms2">Tôi đồng ý điều khoản & dịch vụ</label>
													</div>
													<button class="place-order w-100 dathang">ĐẶT HÀNG</button>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</form>
		</main>
	</div>

<!-- Modal chọn địa chỉ -->
<div class="modal fade" id="modalChonDiaChi" tabindex="-1" role="dialog" aria-labelledby="modalChonDiaChiLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalChonDiaChiLabel">Chọn địa chỉ đã lưu</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="danhSachDiaChi"></div>
      </div>
    </div>
  </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        var hotenPHP = '<?php echo addslashes($khachhang["tenkhachhang"]); ?>';
        var sdtPHP = '<?php echo addslashes($khachhang["sodienthoai"]); ?>';
        var macdinhPHP = '<?php echo addslashes($khachhang["diachi"] ?? ""); ?>';

        var giohang = localStorage.getItem('giohang')
        var tien = 0
        if(giohang == null){
        	window.location.href = 'index.php'
        }else{
            var giohang = JSON.parse(localStorage.getItem('giohang'))
            
            for (var i = 0; i < giohang.length; i++) {
            	var gia = parseInt(giohang[i].giaban) * parseInt(giohang[i].soluong) * 1000
                $('.thongtinsanpham').append('<li><span class="left">'+giohang[i].tensanpham+' X '+giohang[i].soluong+'</span> <span class="right">'+gia.toLocaleString('vi', {style : 'currency', currency : 'VND'})+'</span></li>')
                tien += parseInt(gia)
            }
            $('.sl').html(giohang.length + ' sản phẩm')
            $('.tt').html(tien.toLocaleString('vi', {style : 'currency', currency : 'VND'}))
        }

        var soluong = [];

        // Prefill from default address saved in khachhang.diachi
        if (macdinhPHP && macdinhPHP.indexOf(',') !== -1) {
            try {
                var parts = macdinhPHP.split(',').map(function(s){ return s.trim(); });
                // Expected order: số nhà, thôn/xóm, phường/xã, huyện, tỉnh/thành
                if (parts.length >= 5) {
                    $('.sonha').val(parts[0]);
                    $('.thonxom').val(parts[1]);
                    $('.phuongxa').val(parts[2]);
                    $('.huyen').val(parts[3]);
                    $('.tinhthanh').val(parts[4]);
                }
            } catch(e) {}
        }

        // Load danh sách địa chỉ khi mở modal
        $('#modalChonDiaChi').on('show.bs.modal', function(){
            $.get('luu-thanh-toan-info.php', {action:'get'}, function(res){
                try { if (typeof res === 'string') res = JSON.parse(res); } catch(e){}
                if (res && res.ok && res.data && res.data.length > 0) {
                    var html = '<div class="list-group">';
                    for (var i=0; i<res.data.length; i++) {
                        var item = res.data[i];
                        var addr = item.sonha + ', ' + item.thonxom + ', ' + item.phuongxa + ', ' + item.huyen + ', ' + item.tinhthanh;
                        var isDefault = (res.default && res.default === addr);
                        html += '<div class="list-group-item">';
                        html += '<div class="d-flex justify-content-between align-items-start">';
                        html += '<div class="flex-fill">';
                        html += '<a href="#" class="chon-dia-chi" data-idx="'+i+'">';
                        html += '<strong>'+item.hoten+'</strong> - '+item.sodienthoai+'<br>';
                        html += '<small>'+addr+'</small>';
                        html += '</a>';
                        html += '</div>';
                        html += '<div class="ml-2 text-right">';
                        if (isDefault) {
                            html += '<span class="badge badge-success">Mặc định</span>';
                        } else {
                            html += '<button class="btn btn-sm btn-outline-primary dat-mac-dinh" data-diachi="'+addr.replace(/"/g,'&quot;')+'">Đặt mặc định</button>';
                        }
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';
                    }
                    html += '</div>';
                    $('#danhSachDiaChi').html(html);
                    $('#danhSachDiaChi').data('list', res.data);
                } else {
                    $('#danhSachDiaChi').html('<p class="text-center">Chưa có địa chỉ nào được lưu.</p>');
                }
            });
        });

        // Khi chọn một địa chỉ
        $(document).on('click', '.chon-dia-chi', function(e){
            e.preventDefault();
            var idx = $(this).data('idx');
            var list = $('#danhSachDiaChi').data('list');
            if (list && list[idx]) {
                var item = list[idx];
                $('.sonha').val(item.sonha);
                $('.thonxom').val(item.thonxom);
                $('.phuongxa').val(item.phuongxa);
                $('.huyen').val(item.huyen);
                $('.tinhthanh').val(item.tinhthanh);
                $('#modalChonDiaChi').modal('hide');
            }
        });

        // Đặt một địa chỉ làm mặc định
        $(document).on('click', '.dat-mac-dinh', function(e){
            e.preventDefault();
            var diachi = $(this).data('diachi');
            $.post('luu-thanh-toan-info.php', {action:'set_default', diachi: diachi}, function(res){
                try { if (typeof res === 'string') res = JSON.parse(res); } catch(e){}
                if (res && res.ok) {
                    // Prefill ngay lập tức
                    if (diachi && diachi.indexOf(',') !== -1) {
                        var parts = diachi.split(',').map(function(s){ return s.trim(); });
                        if (parts.length >= 5) {
                            $('.sonha').val(parts[0]);
                            $('.thonxom').val(parts[1]);
                            $('.phuongxa').val(parts[2]);
                            $('.huyen').val(parts[3]);
                            $('.tinhthanh').val(parts[4]);
                        }
                    }
                    $('#modalChonDiaChi').modal('hide');
                }
            });
        });

        $('.dathang').click(function(event) {
            event.preventDefault()
            var diachi = $('.sonha').val() + ", " + $('.thonxom').val() + ", " + $('.phuongxa').val() + ", " + $('.huyen').val() + ", " + $('.tinhthanh').val()
            var makhachhang = '<?php echo $khachhang["makhachhang"] ?>'
            var sanpham = JSON.parse(localStorage.getItem('giohang'))

            soluong.length = 0;
            for (var i = 0; i < sanpham.length; i++) {
                soluong.push(sanpham[i].soluong)
            }

            // Lưu thông tin thanh toán (chỉ thêm nếu chưa trùng)
            $.post('luu-thanh-toan-info.php', {
                action:'save',
                hoten: hotenPHP,
                sodienthoai: sdtPHP,
                sonha: $('.sonha').val(),
                thonxom: $('.thonxom').val(),
                phuongxa: $('.phuongxa').val(),
                huyen: $('.huyen').val(),
                tinhthanh: $('.tinhthanh').val()
            });

            $.post('xu-ly-thanh-toan.php', {makhachhang: makhachhang, diachi:diachi, sanpham:sanpham, tongtien: tien, soluong: soluong}, function(data) {
                var madonhang = data
                var thoigian = '<?php echo date("d/m/Y") ?>'
                var thongtindonhang = localStorage.getItem('giohang')

                localStorage.clear()

                localStorage.setItem('madonhang',madonhang)
                localStorage.setItem('thoigian',thoigian)
                localStorage.setItem('thongtindonhang',thongtindonhang)
                localStorage.setItem('tt',tien.toLocaleString('vi', {style : 'currency', currency : 'VND'}))
       			localStorage.setItem('tt',tien.toLocaleString('vi', {style : 'currency', currency : 'VND'}))

       			window.location.href = 'hoan-thanh-thanh-toan.php'
       		});

        });

    });
</script>
<?php require(__DIR__.'/layouts/footer.php'); ?>  