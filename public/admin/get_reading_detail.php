<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");

header('Content-Type: application/json');

// Check admin permission
if (!isset($_SESSION["account"]) || $_SESSION["account"] != "admin") {
    echo json_encode([
        'success' => false,
        'message' => 'Không có quyền truy cập'
    ]);
    exit;
}

// Get reading ID from GET or POST
$readingId = null;
if (isset($_GET['id'])) {
    $readingId = (int)$_GET['id'];
} else {
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['ma_bai_doc'])) {
        $readingId = (int)$input['ma_bai_doc'];
    }
}

if (!$readingId) {
    echo json_encode([
        'success' => false,
        'message' => 'Thiếu thông tin bài đọc'
    ]);
    exit;
}

try {
    // Get reading lesson details
    $lesson = $Database->get_row("
        SELECT 
            MaBaiDoc,
            TieuDe,
            NoiDungBaiDoc,
            MucDo,
            ThoiGianLam,
            ThuTu
        FROM reading_lessons 
        WHERE MaBaiDoc = $readingId
    ");

    if (!$lesson) {
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy bài đọc'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'lesson' => $lesson
    ]);

} catch (Exception $e) {
    error_log("Error in get_reading_detail.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi xảy ra khi tải thông tin bài đọc'
    ]);
}
?>
