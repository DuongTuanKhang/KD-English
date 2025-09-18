<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once(__DIR__ . '/../configs/config.php');
require_once(__DIR__ . '/../class/Database.php');

// Initialize Database if not exists
if (!isset($Database)) {
    $Database = new Database();
}

if (!isset($_GET['course_id']) || empty($_GET['course_id'])) {
    echo json_encode(['success' => false, 'message' => 'Course ID is required']);
    exit;
}

$courseId = (int)$_GET['course_id'];

try {
    $lessons = $Database->get_list("
        SELECT MaBaiHoc, TenBaiHoc
        FROM baihoc 
        WHERE MaKhoaHoc = $courseId
        AND TrangThaiBaiHoc = 1
        ORDER BY TenBaiHoc ASC
    ");
    
    echo json_encode([
        'success' => true,
        'data' => $lessons
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
