<?php
// Simple delete API - chỉ để test
error_reporting(E_ALL);
ini_set('display_errors', 0); // Tắt display errors để tránh output bị lẫn
header('Content-Type: application/json; charset=utf-8');

try {
    // Kết nối database
    $conn = new mysqli('localhost', 'root', '', 'hocngoaingu');
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8");
    
    // Lấy dữ liệu POST
    $action = $_POST['action'] ?? '';
    $promptId = $_POST['promptId'] ?? '';
    
    if ($action !== 'delete_prompt') {
        throw new Exception("Invalid action: " . $action);
    }
    
    if (empty($promptId)) {
        throw new Exception("Missing promptId");
    }
    
    // Escape input
    $promptId = $conn->real_escape_string($promptId);
    
    // Kiểm tra xem prompt có tồn tại không
    $checkSql = "SELECT MaDeBai FROM writing_prompts WHERE MaDeBai = '$promptId' LIMIT 1";
    $checkResult = $conn->query($checkSql);
    
    if (!$checkResult) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    if ($checkResult->num_rows === 0) {
        echo json_encode([
            "success" => false, 
            "message" => "Không tìm thấy đề bài với ID: " . $promptId
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Thực hiện xóa
    $deleteSql = "DELETE FROM writing_prompts WHERE MaDeBai = '$promptId'";
    $deleteResult = $conn->query($deleteSql);
    
    if (!$deleteResult) {
        throw new Exception("Delete failed: " . $conn->error);
    }
    
    if ($conn->affected_rows > 0) {
        echo json_encode([
            "success" => true,
            "message" => "Xóa thành công đề bài ID: " . $promptId
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Không có đề bài nào được xóa"
        ], JSON_UNESCAPED_UNICODE);
    }
    
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Lỗi: " . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
