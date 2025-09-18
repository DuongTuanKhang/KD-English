<?php
require_once(__DIR__ . "/../configs/config.php");
require_once(__DIR__ . "/../configs/function.php");

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Kiểm tra quyền admin
if (empty($_SESSION['account'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$checkAdmin = $Database->get_row("SELECT * FROM nguoidung WHERE TaiKhoan = '" . $_SESSION['account'] . "' AND MaQuyenHan = 1");
if (!$checkAdmin) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'grade') {
    try {
        $submissionId = $_POST['submission_id'] ?? '';
        $contentScore = $_POST['content_score'] ?? 0;
        $structureScore = $_POST['structure_score'] ?? 0;
        $vocabularyScore = $_POST['vocabulary_score'] ?? 0;
        $grammarScore = $_POST['grammar_score'] ?? 0;
        
        // Tính điểm trung bình (tổng/4)
        $totalScore = round(($contentScore + $structureScore + $vocabularyScore + $grammarScore) / 4, 1);
        $comments = $_POST['comments'] ?? '';
        
        if (!$submissionId) {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID bài nộp']);
            exit;
        }
        
        // Cập nhật điểm số và trạng thái
        $sql = "UPDATE writing_submissions SET 
                DiemNoiDung = '$contentScore',
                DiemToChuc = '$structureScore', 
                DiemTuVung = '$vocabularyScore',
                DiemNguPhap = '$grammarScore',
                TongDiem = '$totalScore',
                NhanXet = '" . $Database->real_escape_string($comments) . "',
                TrangThaiCham = 'Đã chấm',
                NgayCham = NOW(),
                NguoiCham = '" . $_SESSION['account'] . "'
                WHERE MaBaiViet = '$submissionId'";
                
        $result = $Database->query($sql);
        
        if ($result) {
            echo json_encode([
                'success' => true, 
                'message' => 'Chấm điểm thành công',
                'total_score' => $totalScore
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật database']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
}
?>
