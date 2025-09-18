<?php
header('Content-Type: application/json; charset=utf-8');

try {
    $mysqli = new mysqli('localhost', 'root', '', 'hocngoaingu');
    $mysqli->set_charset('utf8');
    
    // Lấy tài khoản học viên từ session hoặc parameter
    $studentAccount = $_GET['account'] ?? 'admin'; // Test với admin
    
    $query = "SELECT 
        ws.MaBaiViet,
        ws.ThoiGianNop,
        ws.DiemNoiDung,
        ws.DiemToChuc,
        ws.DiemTuVung,
        ws.DiemNguPhap,
        ws.TongDiem,
        ws.NhanXet,
        ws.NgayCham,
        ws.NguoiCham,
        ws.TrangThaiCham,
        wp.TieuDe as TieuDeBai
    FROM writing_submissions ws 
    LEFT JOIN writing_prompts wp ON ws.MaDeBai = wp.MaDeBai 
    WHERE ws.TaiKhoan = ? AND ws.TrangThaiCham = 'Đã chấm'
    ORDER BY ws.NgayCham DESC";
    
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $studentAccount);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $gradedSubmissions = [];
    while ($row = $result->fetch_assoc()) {
        $gradedSubmissions[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $gradedSubmissions,
        'count' => count($gradedSubmissions)
    ], JSON_UNESCAPED_UNICODE);
    
    $mysqli->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
