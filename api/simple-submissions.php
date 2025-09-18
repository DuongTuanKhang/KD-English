<?php
header('Content-Type: application/json; charset=utf-8');
try {
    $mysqli = new mysqli('localhost', 'root', '', 'hocngoaingu');
    $mysqli->set_charset('utf8');
    
    $status = isset($_GET['status']) ? $_GET['status'] : 'ungraded';
    
    if ($status == 'ungraded') {
        $where = "(ws.TrangThaiCham = 'Chưa chấm' OR ws.TrangThaiCham = 'Ch?a ch?m' OR ws.TrangThaiCham = '' OR ws.TrangThaiCham IS NULL)";
    } else {
        $where = "(ws.TrangThaiCham = 'Đã chấm' OR ws.TrangThaiCham = '?╞ ch?m')";
    }
    
    // Simplified query first
    $query = "SELECT 
        ws.MaBaiViet,
        ws.TaiKhoan,
        ws.NoiDungBaiViet,
        ws.SoTu,
        ws.ThoiGianNop,
        ws.DiemSo,
        ws.ThoiGianCham,
        ws.NhanXet,
        ws.TrangThaiCham,
        wp.TieuDe,
        nd.TenHienThi
    FROM writing_submissions ws 
    LEFT JOIN writing_prompts wp ON ws.MaDeBai = wp.MaDeBai 
    LEFT JOIN nguoidung nd ON ws.TaiKhoan = nd.TaiKhoan 
    WHERE $where
    ORDER BY ws.ThoiGianNop DESC";
    
    $result = $mysqli->query($query);
    if (!$result) {
        throw new Exception('Query failed: ' . $mysqli->error);
    }
    
    $submissions = [];
    while ($row = $result->fetch_assoc()) {
        $submissions[] = $row;
    }
    
    echo json_encode(['data' => $submissions, 'debug' => ['where' => $where, 'count' => count($submissions)]], JSON_UNESCAPED_UNICODE);
    $mysqli->close();
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
