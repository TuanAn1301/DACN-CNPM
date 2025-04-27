<?php require(__DIR__.'/layouts/header.php'); ?>  
<?php 

$masanpham = $_GET['id'];

$sql_sanpham = "SELECT * FROM sanpham WHERE masanpham = ".$masanpham."";
$sanpham = queryResult($conn, $sql_sanpham)->fetch_assoc();


$sql_cm = "SELECT * FROM chuyenmuc WHERE machuyenmuc = ".$sanpham['machuyenmuc']."";
$cm = queryResult($conn, $sql_cm)->fetch_assoc();

$sql_sanphamlienquan = "SELECT * FROM sanpham WHERE machuyenmuc = ".$sanpham['machuyenmuc']."";
$sanphamlienquan = queryResult($conn, $sql_sanphamlienquan);

?>
        <section class="breadcrumb-section">
            <h2 class="sr-only">Site Breadcrumb</h2>
            <div class="container">
                <div class="breadcrumb-contents">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Trang Chủ</a></li>
                            <li class="breadcrumb-item"><a href="tat-ca-san-pham.php">Sản Phẩm</a></li>
                            <li class="breadcrumb-item active"><?php echo $sanpham['tensanpham']; ?></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </section>
        <main class="inner-page-sec-padding-bottom">
            <div class="container">
                <div class="row  mb--60">
                    <div class="col-lg-5 mb--30">
                        <div class="product-details-slider sb-slick-slider arrow-type-two" data-slick-setting='{
              "slidesToShow": 1,
              "arrows": false,
              "fade": true,
              "draggable": false,
              "swipe": false,
              "asNavFor": ".product-slider-nav"
              }'>
                            <div class="single-slide">
                                <img src="http://localhost/webbansach/<?php echo $sanpham['anhchinh']; ?>" alt="">
                            </div>
                            <div class="single-slide">
                                <img src="http://localhost/webbansach/<?php echo $sanpham['anhphu1']; ?>" alt="">
                            </div>
                            <div class="single-slide">
                                <img src="http://localhost/webbansach/<?php echo $sanpham['anhphu2']; ?>" alt="">
                            </div>
                            <div class="single-slide">
                                <img src="http://localhost/webbansach/<?php echo $sanpham['anhphu3']; ?>" alt="">
                            </div>
                            <div class="single-slide">
                                <img src="http://localhost/webbansach/<?php echo $sanpham['anhphu4']; ?>" alt="">
                            </div>
                        </div>
                        <!-- Product Details Slider Nav -->
                        <div class="mt--30 product-slider-nav sb-slick-slider arrow-type-two" data-slick-setting='{
            "infinite":true,
              "autoplay": true,
              "autoplaySpeed": 8000,
              "slidesToShow": 4,
              "arrows": true,
              "prevArrow":{"buttonClass": "slick-prev","iconClass":"fa fa-chevron-left"},
              "nextArrow":{"buttonClass": "slick-next","iconClass":"fa fa-chevron-right"},
              "asNavFor": ".product-details-slider",
              "focusOnSelect": true
              }'>
                            <div class="single-slide">
                                <img src="http://localhost/webbansach/<?php echo $sanpham['anhchinh']; ?>" alt="">
                            </div>
                            <div class="single-slide">
                                <img src="http://localhost/webbansach/<?php echo $sanpham['anhphu1']; ?>" alt="">
                            </div>
                            <div class="single-slide">
                                <img src="http://localhost/webbansach/<?php echo $sanpham['anhphu2']; ?>" alt="">
                            </div>
                            <div class="single-slide">
                                <img src="http://localhost/webbansach/<?php echo $sanpham['anhphu3']; ?>" alt="">
                            </div>
                            <div class="single-slide">
                                <img src="http://localhost/webbansach/<?php echo $sanpham['anhphu4']; ?>" alt="">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="product-details-info pl-lg--30 ">
                            <p class="tag-block">Tags: <?php echo $sanpham['tag']; ?></p>
                            <h3 class="product-title"><?php echo $sanpham['tensanpham']; ?></h3>
                            <ul class="list-unstyled">
                                <li>Giảm: <span class="list-value"> - <?php echo number_format($sanpham['giagoc'] - $sanpham['giaban']); ?></span></li>
                                <li>Chuyên mục: <a href="#" class="list-value font-weight-bold"> <?php echo $cm['tenchuyenmuc']; ?></a></li>
                                <li>Mã sản phẩm: <span class="list-value"> #sach<?php echo $sanpham['masanpham']; ?></span></li>
                                <li>Trạng thái: <span class="list-value"> Còn hàng</span></li>
                            </ul>
                            <div class="price-block">
                                <span class="price-new"><?php echo number_format($sanpham['giaban']); ?>đ</span>
                                <del class="price-old"><?php echo number_format($sanpham['giagoc']); ?>đ</del>
                            </div>
                            <div class="rating-widget">
                                <div class="rating-block">
                                    <span class="fas fa-star star_on"></span>
                                    <span class="fas fa-star star_on"></span>
                                    <span class="fas fa-star star_on"></span>
                                    <span class="fas fa-star star_on"></span>
                                    <span class="fas fa-star "></span>
                                </div>
                                
                            </div>
                            <article class="product-details-article">
                                <h4 class="sr-only">Mô tả</h4>
                                <p><?php echo $sanpham['mota']; ?></p>
                            </article>
                            <div class="add-to-cart-row">
                                <div class="count-input-block">
                                    <span class="widget-label">Số lượng</span>
                                    <input type="number" class="form-control text-center soluong" value="1">
                                </div>
                                <div class="add-cart-btn">
                                    <a href="" class="btn btn-outlined--primary themgiohang"><span class="plus-icon">+</span>Thêm Giỏ Hàng</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sb-custom-tab review-tab section-padding">
                    <ul class="nav nav-tabs nav-style-2" id="myTab2" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab1" data-toggle="tab" href="#tab-1" role="tab"
                                aria-controls="tab-1" aria-selected="true">
                                MÔ TẢ CHI TIẾT
                            </a>
                        </li>
                       
                    </ul>
                    <div class="tab-content space-db--20" id="myTabContent">
                        <div class="tab-pane fade show active" id="tab-1" role="tabpanel" aria-labelledby="tab1">
                            <article class="review-article">
                                <h1 class="sr-only">Tab Article</h1>
                                <p><?php echo $sanpham['motachitiet']; ?></p>
                            </article>
                        </div>
                        
                    </div>
                </div>
            </div>

            <section class="">
                <div class="container">
                    <div class="section-title section-title--bordered">
                        <h2>SẢN PHẨM LIÊN QUAN</h2>
                    </div>
                    <div class="product-slider sb-slick-slider slider-border-single-row" data-slick-setting='{
                "autoplay": true,
                "autoplaySpeed": 8000,
                "slidesToShow": 4,
                "dots":true
            }' data-slick-responsive='[
                {"breakpoint":1200, "settings": {"slidesToShow": 4} },
                {"breakpoint":992, "settings": {"slidesToShow": 3} },
                {"breakpoint":768, "settings": {"slidesToShow": 2} },
                {"breakpoint":480, "settings": {"slidesToShow": 1} }
            ]'>

                    <?php while($row = $sanphamlienquan->fetch_assoc()){ ?>
                        <div class="single-slide">
                            <div class="product-card">
                                <div class="product-header">
                                    <a href="" class="author">
                                        <?php echo $row['tag']; ?>
                                    </a>
                                    <h3><a href="san-pham.php?id=<?php echo $row['masanpham']; ?>"><?php echo substr($row['tensanpham'],0,28);?>...</a></h3>
                                </div>
                                <div class="product-card--body">
                                    <div class="card-image">
                                        <img src="http://localhost/webbansach/image/logo.png<?php echo $row['anhchinh']; ?>" alt="" style="height: 350px;">
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
                        
                    </div>
                </div>
            </section>
            <!-- Modal -->
            
        </main>
    </div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        $('.themgiohang').click(function(event) {
            event.preventDefault()
            var giohang = localStorage.getItem("giohang")

            var soluong = $('.soluong').val()
            if(giohang == null){
                var item = [
                    {
                        masanpham: '<?php echo $sanpham['masanpham'];?>',
                        tensanpham: '<?php echo $sanpham['tensanpham'];?>',
                        giaban: '<?php echo number_format($sanpham['giaban']);?>',
                        anhchinh: '<?php echo $sanpham['anhchinh'];?>',
                        soluong: soluong
                    }
                ]

                localStorage.setItem("giohang", JSON.stringify(item))

                alert("Đã thêm sách vào giỏ hàng")
                location.reload();

            }else{
                var giohang = JSON.parse(localStorage.getItem("giohang"))

                var check = 0;
                var masanpham = '<?php echo $sanpham['masanpham'];?>'
                for (var i = 0; i < giohang.length; i++) {
                    if (giohang[i].masanpham == masanpham){
                        check = check + 1
                    }
                }

                if(check != 0){
                    alert("Sách đã có trong giỏ hàng!")
                }else{
                    var book = {
                        masanpham: '<?php echo $sanpham['masanpham'];?>',
                        tensanpham: '<?php echo $sanpham['tensanpham'];?>',
                        giaban: '<?php echo number_format($sanpham['giaban']);?>',
                        anhchinh: '<?php echo $sanpham['anhchinh'];?>',
                        soluong: soluong
                    }

                    giohang.push(book)
           

                    localStorage.setItem("giohang", JSON.stringify(giohang))

                    alert('Thêm sách vào giỏ hàng thành công!')
                    location.reload();

                }

                
            }


        });
    });
</script>

<?php require(__DIR__.'/layouts/footer.php'); ?>    