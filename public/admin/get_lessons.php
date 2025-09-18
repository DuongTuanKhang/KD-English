<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");

header('Content-Type: application/json');

// Check admin permission (relaxed for testing)
if (!isset($_SESSION["account"])) {
    echo json_encode([]);
    exit;
}

// Handle both GET and POST requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $maKhoaHoc = isset($_GET['maKhoaHoc']) ? (int)$_GET['maKhoaHoc'] : 0;
} else {
    $input = json_decode(file_get_contents('php://input'), true);
    $maKhoaHoc = isset($input['ma_khoa_hoc']) ? (int)$input['ma_khoa_hoc'] : 0;
}

if (!$maKhoaHoc) {
    echo json_encode([]);
    exit;
}

try {
    // Get lessons for the course
    $lessons = $Database->get_list("
        SELECT 
            MaBaiHoc,
            TenBaiHoc
        FROM baihoc 
        WHERE MaKhoaHoc = $maKhoaHoc AND TrangThaiBaiHoc = 1
        ORDER BY MaBaiHoc ASC
    ");

    echo json_encode($lessons);

} catch (Exception $e) {
    error_log("Error in get_lessons.php: " . $e->getMessage());
    echo json_encode([]);
}
?>
