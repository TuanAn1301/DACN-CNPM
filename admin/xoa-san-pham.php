<?php 
require('../database/connect.php'); 	
require('../database/query.php'); 	

$masanpham = $_GET['id'];

// Fetch product to get file paths
$sp = queryResult($conn, "SELECT anhchinh, anhphu1, anhphu2, anhphu3, anhphu4 FROM sanpham WHERE masanpham=".$masanpham);
if ($sp && $sp->num_rows > 0) {
    $row = $sp->fetch_assoc();
    $paths = [ $row['anhchinh'], $row['anhphu1'], $row['anhphu2'], $row['anhphu3'], $row['anhphu4'] ];
    foreach ($paths as $p) {
        if (!$p) continue;
        // Only delete files within admin/upload for safety
        if (strpos($p, 'admin/upload/') === 0) {
            $abs = realpath(__DIR__ . '/../' . $p);
            $uploadRoot = realpath(__DIR__ . '/upload');
            if ($abs && $uploadRoot && strpos($abs, $uploadRoot) === 0 && file_exists($abs)) {
                @unlink($abs);
            }
        }
    }
}

// Delete DB row
$sql = "DELETE FROM sanpham WHERE masanpham=".$masanpham;
$result = queryResult($conn,$sql);

header("Location: san-pham.php");

?>