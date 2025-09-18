<?php
require_once(__DIR__ . "/../configs/config.php");
require_once(__DIR__ . "/../configs/function.php");

header('Content-Type: application/json');

try {
    $lessonId = isset($_GET['lesson_id']) ? intval($_GET['lesson_id']) : 0;
    $courseId = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
    
    $whereClause = "WHERE 1=1";
    if ($lessonId > 0) {
        $whereClause .= " AND rl.MaBaiHoc = $lessonId";
    }
    if ($courseId > 0) {
        $whereClause .= " AND rl.MaKhoaHoc = $courseId";
    }
    
    $readings = $Database->get_list("
        SELECT 
            rl.*,
            kh.TenKhoaHoc,
            bh.TenBaiHoc,
            (SELECT COUNT(*) FROM reading_questions WHERE MaBaiDoc = rl.MaBaiDoc) as question_count
        FROM reading_lessons rl
        LEFT JOIN khoahoc kh ON rl.MaKhoaHoc = kh.MaKhoaHoc
        LEFT JOIN baihoc bh ON rl.MaBaiHoc = bh.MaBaiHoc AND rl.MaKhoaHoc = bh.MaKhoaHoc
        $whereClause
        ORDER BY rl.MaKhoaHoc, rl.MaBaiHoc, rl.ThuTu
    ");
    
    echo json_encode([
        'success' => true,
        'data' => $readings
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
