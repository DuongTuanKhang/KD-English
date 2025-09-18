<?php
header('Content-Type: application/json; charset=utf-8');
require_once(__DIR__ . "/../configs/config.php");

try {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception('ID bài viết không được cung cấp');
    }

    $submissionId = intval($_GET['id']);
    
    $mysqli = new mysqli('localhost', 'root', '', 'hocngoaingu');
    $mysqli->set_charset('utf8');
    
    $query = "SELECT 
        ws.MaBaiViet,
        ws.NoiDungBaiViet,
        ws.SoTu,
        ws.ThoiGianNop,
        ws.TrangThaiCham,
        ws.DiemSo,
        ws.DiemNguPhap,
        ws.DiemMachLac,
        ws.DiemTuVung,
        ws.NhanXet,
        ws.NguoiCham,
        ws.ThoiGianCham,
        wp.TieuDe as TieuDeDeBai,
        wp.NoiDungDeBai,
        wp.GioiHanTu,
        wp.ThoiGianLamBai,
        n.TenHienThi,
        n.TaiKhoan
    FROM writing_submissions ws
    JOIN writing_prompts wp ON ws.MaDeBai = wp.MaDeBai
    JOIN nguoidung n ON ws.TaiKhoan = n.TaiKhoan
    WHERE ws.MaBaiViet = ?";
    
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $submissionId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Không tìm thấy bài viết');
    }
    
    $submission = $result->fetch_assoc();
    
    // Format ngày giờ
    if ($submission['ThoiGianNop']) {
        $submission['ThoiGianNop'] = date('d/m/Y H:i:s', strtotime($submission['ThoiGianNop']));
    }
    if ($submission['ThoiGianCham']) {
        $submission['ThoiGianCham'] = date('d/m/Y H:i:s', strtotime($submission['ThoiGianCham']));
    }
    
    echo json_encode(['success' => true, 'data' => $submission], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
