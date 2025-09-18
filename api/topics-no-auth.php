<?php
require_once(__DIR__ . "/../configs/config.php");

header('Content-Type: application/json');

// Bypass authentication cho test
$courseId = $_GET['course_id'] ?? 1;

if ($courseId) {
    // Lấy từ bảng baihoc - 8 chủ đề
    $sql = "SELECT MaBaiHoc as MaChuDe, TenBaiHoc as TenChuDe FROM baihoc WHERE MaKhoaHoc = '$courseId' ORDER BY TenBaiHoc";
    $topics = $Database->get_list($sql);
    
    echo json_encode($topics);
} else {
    echo json_encode([]);
}
?>
