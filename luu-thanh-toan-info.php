<?php
session_start();
require(__DIR__.'/database/connect.php');
require(__DIR__.'/database/query.php');

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['dangnhap']) || !isset($_SESSION['taikhoan'])) {
    http_response_code(401);
    echo json_encode(['ok'=>false,'msg'=>'Chưa đăng nhập']);
    exit;
}

$taikhoan = $_SESSION['taikhoan'];
$kh = queryResult($conn, "SELECT * FROM khachhang WHERE taikhoan='".$conn->real_escape_string($taikhoan)."'");
if (!$kh || $kh->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['ok'=>false,'msg'=>'Không tìm thấy khách hàng']);
    exit;
}
$khachhang = $kh->fetch_assoc();
$makhachhang = (int)$khachhang['makhachhang'];

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if ($method === 'GET' && $action === 'get') {
    $rs = queryResult($conn, "SELECT * FROM thongtinthanhtoan WHERE makhachhang=".$makhachhang." ORDER BY created_at DESC");
    $default = isset($khachhang['diachi']) ? $khachhang['diachi'] : '';
    if ($rs && $rs->num_rows > 0) {
        $list = [];
        while ($row = $rs->fetch_assoc()) { $list[] = $row; }
        echo json_encode(['ok'=>true,'data'=>$list,'default'=>$default]);
    } else {
        echo json_encode(['ok'=>true,'data'=>[],'default'=>$default]);
    }
    exit;
}

if ($method === 'POST' && $action === 'save') {
    // Read fields
    $hoten = isset($_POST['hoten']) ? trim($_POST['hoten']) : '';
    $sodienthoai = isset($_POST['sodienthoai']) ? trim($_POST['sodienthoai']) : '';
    $sonha = isset($_POST['sonha']) ? trim($_POST['sonha']) : '';
    $thonxom = isset($_POST['thonxom']) ? trim($_POST['thonxom']) : '';
    $phuongxa = isset($_POST['phuongxa']) ? trim($_POST['phuongxa']) : '';
    $huyen = isset($_POST['huyen']) ? trim($_POST['huyen']) : '';
    $tinhthanh = isset($_POST['tinhthanh']) ? trim($_POST['tinhthanh']) : '';

    // Basic validation
    if ($hoten === '' || $sodienthoai === '' || $sonha === '' || $thonxom === '' || $phuongxa === '' || $huyen === '' || $tinhthanh === '') {
        http_response_code(400);
        echo json_encode(['ok'=>false,'msg'=>'Thiếu thông tin']);
        exit;
    }

    // Escape helper
    function _e($conn, $v) { return $conn->real_escape_string($v); }

    // Insert ignore duplicate (unique composite)
    $sql = "INSERT INTO thongtinthanhtoan (makhachhang, hoten, sodienthoai, sonha, thonxom, phuongxa, huyen, tinhthanh) VALUES (".$makhachhang.", '"._e($conn,$hoten)."', '"._e($conn,$sodienthoai)."', '"._e($conn,$sonha)."', '"._e($conn,$thonxom)."', '"._e($conn,$phuongxa)."', '"._e($conn,$huyen)."', '"._e($conn,$tinhthanh)."') ON DUPLICATE KEY UPDATE hoten=VALUES(hoten)";

    $ok = queryExecute($conn, $sql);
    echo json_encode(['ok'=>$ok]);
    exit;
}

// Set default address into khachhang.diachi
if ($method === 'POST' && $action === 'set_default') {
    $diachi = isset($_POST['diachi']) ? trim($_POST['diachi']) : '';
    if ($diachi === '') {
        http_response_code(400);
        echo json_encode(['ok'=>false,'msg'=>'Thiếu địa chỉ']);
        exit;
    }
    $ok = queryExecute($conn, "UPDATE khachhang SET diachi='".$conn->real_escape_string($diachi)."' WHERE makhachhang=".$makhachhang);
    echo json_encode(['ok'=>$ok]);
    exit;
}

http_response_code(400);
echo json_encode(['ok'=>false,'msg'=>'Yêu cầu không hợp lệ']);
