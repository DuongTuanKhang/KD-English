<?php
header('Content-Type: application/json; charset=utf-8');
require_once(__DIR__ . "/../configs/config.php");

try {
    // Kiểm tra phương thức POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Phương thức không được hỗ trợ');
    }
    
    // Kiểm tra dữ liệu đầu vào
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    
    // Debug input
    error_log("Raw input: " . $rawInput);
    error_log("Parsed input: " . print_r($input, true));
    
    if ($input !== null && json_last_error() === JSON_ERROR_NONE) {
        // Nếu có JSON input, sử dụng JSON
        $submissionId = isset($input['submission_id']) ? intval($input['submission_id']) : 0;
        $diemSo = isset($input['diem_so']) ? floatval($input['diem_so']) : null;
        $diemNguPhap = isset($input['diem_ngu_phap']) ? floatval($input['diem_ngu_phap']) : null;
        $diemMachLac = isset($input['diem_mach_lac']) ? floatval($input['diem_mach_lac']) : null;
        $diemTuVung = isset($input['diem_tu_vung']) ? floatval($input['diem_tu_vung']) : null;
        $nhanXet = isset($input['nhan_xet']) ? trim($input['nhan_xet']) : '';
        $nguoiCham = isset($input['nguoi_cham']) ? trim($input['nguoi_cham']) : 'admin';
    } else {
        // Fallback to POST data
        $submissionId = isset($_POST['submissionId']) ? intval($_POST['submissionId']) : 0;
        $diemSo = isset($_POST['diemSo']) ? floatval($_POST['diemSo']) : null;
        $diemNguPhap = isset($_POST['diemNguPhap']) ? floatval($_POST['diemNguPhap']) : null;
        $diemMachLac = isset($_POST['diemMachLac']) ? floatval($_POST['diemMachLac']) : null;
        $diemTuVung = isset($_POST['diemTuVung']) ? floatval($_POST['diemTuVung']) : null;
        $nhanXet = isset($_POST['nhanXet']) ? trim($_POST['nhanXet']) : '';
        $nguoiCham = isset($_POST['nguoiCham']) ? trim($_POST['nguoiCham']) : 'admin';
    }
    
    if ($submissionId <= 0) {
        throw new Exception('ID bài viết không hợp lệ. Received: ' . $submissionId);
    }
    
    // Kiểm tra điểm hợp lệ
    if ($diemSo !== null && ($diemSo < 0 || $diemSo > 10)) {
        throw new Exception('Điểm tổng phải từ 0 đến 10');
    }
    if ($diemNguPhap !== null && ($diemNguPhap < 0 || $diemNguPhap > 10)) {
        throw new Exception('Điểm ngữ pháp phải từ 0 đến 10');
    }
    if ($diemMachLac !== null && ($diemMachLac < 0 || $diemMachLac > 10)) {
        throw new Exception('Điểm mạch lạc phải từ 0 đến 10');
    }
    if ($diemTuVung !== null && ($diemTuVung < 0 || $diemTuVung > 10)) {
        throw new Exception('Điểm từ vựng phải từ 0 đến 10');
    }
    
    $mysqli = new mysqli('localhost', 'root', '', 'hocngoaingu');
    $mysqli->set_charset('utf8');
    
    // Kiểm tra bài viết có tồn tại không
    $checkQuery = "SELECT MaBaiViet FROM writing_submissions WHERE MaBaiViet = ?";
    $checkStmt = $mysqli->prepare($checkQuery);
    $checkStmt->bind_param('i', $submissionId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        throw new Exception('Không tìm thấy bài viết');
    }
    
    // Cập nhật kết quả chấm
    $updateQuery = "UPDATE writing_submissions SET 
        TrangThaiCham = 'Đã chấm',
        DiemSo = ?,
        NhanXet = ?,
        ThoiGianCham = NOW()
        WHERE MaBaiViet = ?";
    
    $updateStmt = $mysqli->prepare($updateQuery);
    $updateStmt->bind_param('dsi', 
        $diemSo, 
        $nhanXet, 
        $submissionId
    );
    
    if ($updateStmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Lưu kết quả chấm thành công',
            'data' => [
                'submissionId' => $submissionId,
                'diemSo' => $diemSo,
                'diemNguPhap' => $diemNguPhap,
                'diemMachLac' => $diemMachLac,
                'diemTuVung' => $diemTuVung,
                'nguoiCham' => $nguoiCham,
                'thoiGianCham' => date('Y-m-d H:i:s')
            ]
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('Lỗi cập nhật database: ' . $updateStmt->error);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
