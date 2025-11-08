<?php
session_start();
if(!isset($_SESSION["login"])){
    header("Location: ../admin/dang-nhap.php");
    die();  
}

require('../database/connect.php'); 
require('../database/query.php');

// Validate parameters
if (!isset($_GET['id']) || !isset($_GET['action'])) {
    header("Location: don-hang.php?error=missing_params");
    exit();
}

$madonhang = (int)$_GET['id'];
$trangthai = (int)$_GET['action'];

if ($madonhang <= 0) {
    header("Location: don-hang.php?error=invalid_id");
    exit();
}

// Use prepared statement for safety
if ($stmt = $conn->prepare("UPDATE `donhang` SET `trangthai` = ? WHERE `madonhang` = ?")) {
    $stmt->bind_param('ii', $trangthai, $madonhang);
    $stmt->execute();
    $stmt->close();
} else {
    // Fallback (should rarely happen)
    $sql = "UPDATE `donhang` SET `trangthai` = ".$trangthai." WHERE `madonhang` = ".$madonhang;
    queryExecute($conn, $sql);
}

header("Location: don-hang.php");
exit();

?>