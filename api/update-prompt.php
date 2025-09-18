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
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    $input = $_POST;
}

$response = array('success' => false, 'message' => 'Có lỗi xảy ra');

try {
    // Log received data for debugging
    error_log("Received input data: " . json_encode($input));
    
    if (!isset($input['promptId']) || !isset($input['tieuDe']) || !isset($input['noiDungDeBai'])) {
        throw new Exception('Thiếu thông tin cần thiết');
    }
    
    $promptId = intval($input['promptId']);
    $tieuDe = trim($input['tieuDe']);
    $noiDungDeBai = trim($input['noiDungDeBai']);
    $gioiHanTu = isset($input['gioiHanTu']) ? intval($input['gioiHanTu']) : 500;
    $mucDo = isset($input['mucDo']) ? trim($input['mucDo']) : 'Trung bình';
    $thoiGianLamBai = isset($input['thoiGianLamBai']) ? intval($input['thoiGianLamBai']) : 60;
    
    error_log("Parsed data - ID: $promptId, Title: $tieuDe, Content length: " . strlen($noiDungDeBai));
    
    // Validate data
    if (empty($tieuDe) || empty($noiDungDeBai)) {
        throw new Exception('Tiêu đề và nội dung không được để trống');
    }
    
    if ($promptId <= 0) {
        throw new Exception('ID đề bài không hợp lệ');
    }
    
    // Kiểm tra xem prompt có tồn tại không
    $checkSql = "SELECT MaDeBai FROM writing_prompts WHERE MaDeBai = ?";
    $checkStmt = $conn->prepare($checkSql);
    if (!$checkStmt) {
        throw new Exception('Lỗi prepare statement: ' . $conn->error);
    }
    
    $checkStmt->bind_param("i", $promptId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        throw new Exception('Không tìm thấy đề bài với ID: ' . $promptId);
    }
    
    // Cập nhật prompt
    $updateSql = "UPDATE writing_prompts SET 
                  TieuDe = ?, 
                  NoiDungDeBai = ?, 
                  GioiHanTu = ?, 
                  MucDo = ?, 
                  ThoiGianLamBai = ?
                  WHERE MaDeBai = ?";
    
    $updateStmt = $conn->prepare($updateSql);
    if (!$updateStmt) {
        throw new Exception('Lỗi prepare update statement: ' . $conn->error);
    }
    
    $updateStmt->bind_param("ssissi", $tieuDe, $noiDungDeBai, $gioiHanTu, $mucDo, $thoiGianLamBai, $promptId);
    
    if ($updateStmt->execute()) {
        if ($updateStmt->affected_rows > 0) {
            $response['success'] = true;
            $response['message'] = 'Cập nhật đề bài thành công';
            error_log("Successfully updated prompt ID: $promptId");
        } else {
            throw new Exception('Không có dữ liệu nào được cập nhật. Có thể dữ liệu không thay đổi.');
        }
    } else {
        throw new Exception('Không thể cập nhật đề bài: ' . $updateStmt->error);
    }
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("Update prompt error: " . $e->getMessage());
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
