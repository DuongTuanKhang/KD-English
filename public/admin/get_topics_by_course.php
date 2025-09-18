<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");

// Check admin permission
if (!isset($_SESSION["account"]) || $_SESSION["account"] != "admin") {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get course_id from request
$courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

if ($courseId <= 0) {
    echo json_encode([]);
    exit;
}

try {
    // Get topics for the selected course from baihoc table (main course topics)
    $topics = $Database->get_list("
        SELECT MaBaiHoc as MaChuDe, TenBaiHoc as TenChuDe, TenBaiHoc as TenChuDeEng,
               (SELECT COUNT(*) FROM grammar_topics WHERE MaKhoaHoc = " . $courseId . ") as SoLuongCauHoi
        FROM baihoc 
        WHERE TrangThaiBaiHoc = 1 AND MaKhoaHoc = " . $courseId . "
        ORDER BY MaBaiHoc ASC
    ");
    
    // Return as JSON
    header('Content-Type: application/json');
    echo json_encode($topics ?: []);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([]);
}
?>
