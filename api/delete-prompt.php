<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include('../configs/config.php');

// Nhận dữ liệu từ request
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    $input = $_POST;
}

$response = array('success' => false, 'message' => 'Có lỗi xảy ra');

try {
    if (!isset($input['promptId'])) {
        throw new Exception('Thiếu ID đề bài');
    }
    
    $promptId = intval($input['promptId']);
    
    // Kiểm tra xem prompt có tồn tại không
    $checkSql = "SELECT MaDeBai FROM writing_prompts WHERE MaDeBai = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $promptId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        throw new Exception('Không tìm thấy đề bài');
    }
    
    // Kiểm tra xem có bài nộp nào liên quan không
    $submissionsSql = "SELECT COUNT(*) as count FROM writing_submissions WHERE MaDeBai = ?";
    $submissionsStmt = $conn->prepare($submissionsSql);
    $submissionsStmt->bind_param("i", $promptId);
    $submissionsStmt->execute();
    $submissionsResult = $submissionsStmt->get_result();
    $submissionsRow = $submissionsResult->fetch_assoc();
    
    // Ghi log nếu có submissions sẽ bị ảnh hưởng
    if ($submissionsRow['count'] > 0) {
        error_log("Deleting prompt $promptId with {$submissionsRow['count']} existing submissions");
    }
    
    // Xóa prompt
    $deleteSql = "DELETE FROM writing_prompts WHERE MaDeBai = ?";
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->bind_param("i", $promptId);
    
    if ($deleteStmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Xóa đề bài thành công';
    } else {
        throw new Exception('Không thể xóa đề bài: ' . $deleteStmt->error);
    }
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("Delete prompt error: " . $e->getMessage());
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
