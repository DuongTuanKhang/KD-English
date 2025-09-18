<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include('../configs/config.php');
include('../configs/function.php');

// Nhận dữ liệu từ request
$inputRaw = file_get_contents('php://input');
$inputRaw = trim($inputRaw); // Loại bỏ khoảng trắng
$input = json_decode($inputRaw, true);

// Nếu json_decode thất bại, thử parse manual hoặc sử dụng POST
if (json_last_error() !== JSON_ERROR_NONE || !$input) {
    if (!empty($_POST)) {
        $input = $_POST;
    } else {
        // Thử parse manual đơn giản
        parse_str($inputRaw, $input);
    }
}

$response = array('success' => false, 'message' => 'Có lỗi xảy ra');

try {
    if (!isset($input['submission_id']) || empty($input['submission_id'])) {
        throw new Exception('Thiếu ID bài nộp');
    }
    
    $submissionId = intval($input['submission_id']);
    $contentScore = floatval($input['content_score'] ?? 0);
    $structureScore = floatval($input['structure_score'] ?? 0);
    $vocabularyScore = floatval($input['vocabulary_score'] ?? 0);
    $grammarScore = floatval($input['grammar_score'] ?? 0);
    $totalScore = floatval($input['total_score'] ?? 0);
    $comments = $input['comments'] ?? '';
    $grader = 'admin'; // Hoặc lấy từ session
    
    // Kiểm tra xem submission có tồn tại không
    $checkResult = $Database->get_row("SELECT MaBaiViet FROM writing_submissions WHERE MaBaiViet = '$submissionId'");
    
    if (!$checkResult) {
        throw new Exception('Không tìm thấy bài nộp');
    }
    
    // Cập nhật điểm số và trạng thái
    $escapedComments = addslashes($comments);
    $updateSql = "UPDATE writing_submissions SET 
                  DiemNoiDung = '$contentScore', 
                  DiemToChuc = '$structureScore', 
                  DiemTuVung = '$vocabularyScore', 
                  DiemNguPhap = '$grammarScore', 
                  TongDiem = '$totalScore', 
                  NhanXet = '$escapedComments', 
                  TrangThaiCham = 'Đã chấm', 
                  NgayCham = NOW(), 
                  NguoiCham = '$grader'
                  WHERE MaBaiViet = '$submissionId'";
    
    $updateResult = $Database->query($updateSql);
    
    if ($updateResult) {
        $response['success'] = true;
        $response['message'] = 'Chấm điểm thành công';
        $response['data'] = [
            'submission_id' => $submissionId,
            'total_score' => $totalScore,
            'status' => 'Đã chấm'
        ];
    } else {
        throw new Exception('Không thể cập nhật điểm');
    }
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("Grade submission error: " . $e->getMessage());
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
