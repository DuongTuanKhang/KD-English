<?php
require_once(__DIR__ . "/../class/Database.php");

header('Content-Type: application/json');

try {
    $courses = $Database->get_list("SELECT MaKhoaHoc, TenKhoaHoc FROM khoahoc WHERE TrangThaiKhoaHoc = 1 ORDER BY TenKhoaHoc");
    
    echo json_encode([
        'success' => true,
        'data' => $courses
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
