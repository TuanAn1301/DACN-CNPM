<?php
session_start();
if(!isset($_SESSION["login"])){
    header("Location: dang-nhap.php");
    die();  
}

require('../database/connect.php');	
require('../database/query.php');	

if(!isset($_GET['id'])){
    echo "<script>alert('Không tìm thấy banner!'); window.location.href='banner.php';</script>";
    exit;
}

$mabanner = $_GET['id'];
$sql = "SELECT * FROM banner WHERE mabanner = $mabanner";
$result = queryResult($conn, $sql);

if($result->num_rows == 0){
    echo "<script>alert('Banner không tồn tại!'); window.location.href='banner.php';</script>";
    exit;
}

$banner = $result->fetch_assoc();

// Xóa file ảnh
if(file_exists('../' . $banner['hinhanh'])){
    unlink('../' . $banner['hinhanh']);
}

// Xóa banner trong database
$sql = "DELETE FROM banner WHERE mabanner = $mabanner";
if(queryExecute($conn, $sql)){
    echo "<script>alert('Xóa banner thành công!'); window.location.href='banner.php';</script>";
} else {
    echo "<script>alert('Lỗi: Không thể xóa banner!'); window.location.href='banner.php';</script>";
}
?>
