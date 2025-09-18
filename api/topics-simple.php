<?php
require_once(__DIR__ . "/../configs/config.php");
require_once(__DIR__ . "/../configs/function.php");

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    $courseId = $_GET['course_id'] ?? '';
    
    if (!$courseId) {
        echo json_encode([]);
        exit;
    }
    
    // Lấy topics từ bảng baihoc (8 bài học chính)
    $sql = "SELECT MaBaiHoc as MaChuDe, TenBaiHoc as TenChuDe FROM baihoc WHERE MaKhoaHoc = '$courseId' ORDER BY TenBaiHoc";
    $topics = $Database->get_list($sql);
    
    // Nếu không có, thử từ writing_topics
    if (empty($topics)) {
        $sql = "SELECT MaChuDe, TenChuDe FROM writing_topics WHERE MaKhoaHoc = '$courseId' ORDER BY TenChuDe";
        $topics = $Database->get_list($sql);
    }
    
    // Nếu vẫn không có, tạo topics mẫu cho Tiếng Anh
    if (empty($topics) && $courseId == 1) {
        $topics = [
            ['MaChuDe' => 1, 'TenChuDe' => 'Family and Friends'],
            ['MaChuDe' => 2, 'TenChuDe' => 'Daily Activities'],
            ['MaChuDe' => 3, 'TenChuDe' => 'Education and School'],
            ['MaChuDe' => 4, 'TenChuDe' => 'Work and Career'],
            ['MaChuDe' => 5, 'TenChuDe' => 'Travel and Vacation'],
            ['MaChuDe' => 6, 'TenChuDe' => 'Health and Lifestyle'],
            ['MaChuDe' => 7, 'TenChuDe' => 'Technology and Communication'],
            ['MaChuDe' => 8, 'TenChuDe' => 'Environment and Nature']
        ];
    }
    
    echo json_encode($topics, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
