<?php
header('Content-Type: application/json');
require('../database/connect.php');
require('../database/query.php');

if(!isset($_POST['product_ids'])) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin sản phẩm']);
    exit;
}

$product_ids_json = $_POST['product_ids'];
$product_ids = json_decode($product_ids_json, true);

if(!is_array($product_ids) || empty($product_ids)) {
    echo json_encode(['success' => false, 'message' => 'Danh sách sản phẩm không hợp lệ']);
    exit;
}

// Tạo placeholders cho SQL IN clause
$placeholders = implode(',', array_fill(0, count($product_ids), '?'));
$sql = "SELECT masanpham, tensanpham, giaban, anhchinh FROM sanpham WHERE masanpham IN ($placeholders)";

// Chuẩn bị statement
$stmt = $conn->prepare($sql);

if(!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Lỗi database']);
    exit;
}

// Bind parameters
$types = str_repeat('i', count($product_ids)); // 'i' for integer
$stmt->bind_param($types, ...$product_ids);

// Execute
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while($row = $result->fetch_assoc()) {
    $products[] = [
        'masanpham' => $row['masanpham'],
        'tensanpham' => $row['tensanpham'],
        'giaban' => $row['giaban'],
        'anhchinh' => $row['anhchinh']
    ];
}

$stmt->close();
$conn->close();

if(empty($products)) {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm']);
} else {
    echo json_encode(['success' => true, 'products' => $products]);
}
?>
