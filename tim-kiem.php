<?php require(__DIR__.'/layouts/header.php'); ?>  
<?php 

$keyword = isset($_GET['tensach']) ? trim($_GET['tensach']) : '';
$cm = isset($_GET['cm']) ? (int)$_GET['cm'] : 0;

$where = [];
if ($keyword !== '') {
    $safe = mysqli_real_escape_string($conn, $keyword);
    $where[] = "tensanpham LIKE '%$safe%'";
}
if ($cm > 0) {
    $where[] = "machuyenmuc = $cm";
}
$whereSql = count($where) ? ('WHERE ' . implode(' AND ', $where)) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';
$orderSql = '';
switch ($sort) {
    case 'name_asc':
        $orderSql = ' ORDER BY tensanpham ASC';
        break;
    case 'name_desc':
        $orderSql = ' ORDER BY tensanpham DESC';
        break;
    case 'price_asc':
        $orderSql = ' ORDER BY giaban ASC';
        break;
    case 'price_desc':
        $orderSql = ' ORDER BY giaban DESC';
        break;
    default:
        $orderSql = ' ORDER BY masanpham DESC';
}

$sql_sanpham = "SELECT * FROM sanpham $whereSql$orderSql";
$sanpham = queryResult($conn,$sql_sanpham);

 ?>  
		<section class="breadcrumb-section">
			<h2 class="sr-only">Site Breadcrumb</h2>
			<div class="container">
				<div class="breadcrumb-contents">
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb">
							<li class="breadcrumb-item"><a href="index.html">Trang Chủ</a></li>
							<li class="breadcrumb-item ">Sản Phẩm</li>
							<li class="breadcrumb-item active">Tìm Kiếm Sản Phẩm</li>
						</ol>
					</nav>
				</div>
			</div>
		</section>
		<main class="inner-page-sec-padding-bottom">
			<div class="container">
				<div class="shop-toolbar mb--30">
					<div class="row align-items-center">
						<div class="col-lg-2 col-md-2 col-sm-6">
							<!-- Product View Mode -->
							<div class="product-view-mode">
								<a href="#" class="sorting-btn active" data-target="grid"><i class="fas fa-th"></i></a>
								<a href="#" class="sorting-btn" data-target="grid-four">
									<span class="grid-four-icon">
										<i class="fas fa-grip-vertical"></i><i class="fas fa-grip-vertical"></i>
									</span>
								</a>
								<a href="#" class="sorting-btn" data-target="list "><i class="fas fa-list"></i></a>
							</div>
						</div>
						<div class="col-xl-4 col-md-4 col-sm-6  mt--10 mt-sm--0">
							
						</div>
						<div class="col-lg-1 col-md-2 col-sm-6  mt--10 mt-md--0">
							
						</div>
						<div class="col-xl-4 col-lg-4 col-md-4 col-sm-6 mt--10 mt-md--0 ">
							<div class="sorting-selection">
								<span>Sắp xếp theo:</span>
								<form method="GET" action="tim-kiem.php">
									<?php if ($keyword !== '') { ?>
										<input type="hidden" name="tensach" value="<?php echo htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); ?>">
									<?php } ?>
									<?php if ($cm > 0) { ?>
										<input type="hidden" name="cm" value="<?php echo (int)$cm; ?>">
									<?php } ?>
									<select name="sort" class="form-control nice-select sort-select mr-0" onchange="this.form.submit()">
										<option value="" <?php echo ($sort==''?'selected':''); ?>>Mặc Định</option>
										<option value="name_asc" <?php echo ($sort=='name_asc'?'selected':''); ?>>Theo tên: (A - Z)</option>
										<option value="name_desc" <?php echo ($sort=='name_desc'?'selected':''); ?>>Theo tên: (Z - A)</option>
										<option value="price_asc" <?php echo ($sort=='price_asc'?'selected':''); ?>>Theo giá: (Thấp &gt; Cao)</option>
										<option value="price_desc" <?php echo ($sort=='price_desc'?'selected':''); ?>>Theo giá: (Cao &gt; Thấp)</option>
									</select>
								</form>
							</div>
						</div>
					</div>
				</div>
				<div class="shop-toolbar d-none">
					<div class="row align-items-center">
						<div class="col-lg-2 col-md-2 col-sm-6">
							<!-- Product View Mode -->
							<div class="product-view-mode">
								<a href="#" class="sorting-btn active" data-target="grid"><i class="fas fa-th"></i></a>
								<a href="#" class="sorting-btn" data-target="grid-four">
									<span class="grid-four-icon">
										<i class="fas fa-grip-vertical"></i><i class="fas fa-grip-vertical"></i>
									</span>
								</a>
								<a href="#" class="sorting-btn" data-target="list "><i class="fas fa-list"></i></a>
							</div>
						</div>
						<div class="col-xl-5 col-md-4 col-sm-6  mt--10 mt-sm--0">
							<span class="toolbar-status">
								
							</span>
						</div>
						<div class="col-lg-2 col-md-2 col-sm-6  mt--10 mt-md--0">
							<div class="sorting-selection">
								
							</div>
						</div>
						<div class="col-xl-3 col-lg-4 col-md-4 col-sm-6 mt--10 mt-md--0 ">
							<div class="sorting-selection">
								<span>Sắp xếp theo:</span>
								<form method="GET" action="tim-kiem.php">
									<?php if ($keyword !== '') { ?>
										<input type="hidden" name="tensach" value="<?php echo htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); ?>">
									<?php } ?>
									<?php if ($cm > 0) { ?>
										<input type="hidden" name="cm" value="<?php echo (int)$cm; ?>">
									<?php } ?>
									<select name="sort" class="form-control nice-select sort-select mr-0" onchange="this.form.submit()">
										<option value="" <?php echo ($sort==''?'selected':''); ?>>Mặc Định</option>
										<option value="name_asc" <?php echo ($sort=='name_asc'?'selected':''); ?>>Theo tên: (A - Z)</option>
										<option value="name_desc" <?php echo ($sort=='name_desc'?'selected':''); ?>>Theo tên: (Z - A)</option>
										<option value="price_asc" <?php echo ($sort=='price_asc'?'selected':''); ?>>Theo giá: (Thấp &gt; Cao)</option>
										<option value="price_desc" <?php echo ($sort=='price_desc'?'selected':''); ?>>Theo giá: (Cao &gt; Thấp)</option>
									</select>
								</form>
							</div>
						</div>
					</div>
				</div>
				<div class="shop-product-wrap grid with-pagination row space-db--30 shop-border">

					<?php if(!isset($sanpham->num_rows)){ ?>
						<p style="text-align: center; width: 100%;">Không tìm thấy sản phẩm!</p>
					<?php }else{ ?>
						<?php while($row = $sanpham->fetch_assoc()){ ?>

							<div class="col-lg-4 col-sm-6">
								<div class="product-card">
									<div class="product-grid-content">
										<div class="product-header">
											<a href="" class="author">
												<?php echo $row['tag']; ?>
											</a>
											<h3><a href="san-pham.php?id=<?php echo $row['masanpham']; ?>"><?php echo substr($row['tensanpham'],0,28);?>...</a></h3>
										</div>
										<div class="product-card--body">
											<div class="card-image">
												<img src="http://localhost/webbansach/<?php echo $row['anhchinh']; ?>" alt="" style="height: 350px;">
												<div class="hover-contents">
													<a href="san-pham.php?id=<?php echo $row['masanpham']; ?>" class="hover-image">
														<img src="http://localhost/webbansach/<?php echo $row['anhphu1']; ?>" alt="" style="height: 350px;">
													</a>
												</div>
											</div>
											<div class="price-block">
	                                            <span class="price"><?php echo number_format($row['giaban']); ?>đ</span>
	                                            <del class="price-old"><?php echo number_format($row['giagoc']); ?>đ</del>
	                                            <span class="price-discount">-<?php echo number_format($row['giagoc'] - $row['giaban']); ?></span>
	                                        </div>
										</div>
									</div> <hr>



									<div class="product-list-content">
										<div class="card-image">
											<img src="http://localhost/webbansach/<?php echo $row['anhchinh']; ?>" alt="">
										</div>
										<div class="product-card--body">
											<div class="product-header">
												<a href="" class="author">
													<?php echo $row['tag']; ?>
												</a>
												<h3><a href="san-pham.php?id=<?php echo $row['masanpham']; ?>" tabindex="0"><?php echo $row['tensanpham'];?></a></h3>
											</div>
											<article>
												<h2 class="sr-only">Mô tả</h2>
												<p><?php echo substr($row['tensanpham'],0,200);?>...</p>
											</article>
											<div class="price-block">
	                                            <span class="price"><?php echo number_format($row['giaban']); ?>đ</span>
	                                            <del class="price-old"><?php echo number_format($row['giagoc']); ?>đ</del>
	                                            <span class="price-discount">-<?php echo number_format($row['giagoc'] - $row['giaban']); ?></span>
	                                        </div>
											
											<div class="btn-block">
												<a href="" class="btn btn-outlined">Thêm Giỏ Hàng</a>
											</div>
										</div>
									</div>


								</div>
							</div>

						<?php } ?>
					<?php } ?>
					
					
				</div>
				
			</div>
		</main>
	</div>

<?php require(__DIR__.'/layouts/footer.php'); ?>    