<?php
session_start();
if(!isset($_SESSION["login"])){
    header("Location: dang-nhap.php");
    die();  
}

require('../database/connect.php'); 

if(isset($_GET['id']) && isset($_GET['action'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if($action == 'done') {
        // Đánh dấu đã xử lý
        $sql = "UPDATE lienhe SET trangthai = 1 WHERE malienhe = $id";
        if($conn->query($sql)) {
            header("Location: lien-he.php?success=1");
        } else {
            header("Location: lien-he.php?error=1");
        }
    }
} else {
    header("Location: lien-he.php");
}

$conn->close();
?>
