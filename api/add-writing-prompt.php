<?php
require_once(__DIR__ . "/../configs/config.php");
require_once(__DIR__ . "/../configs/function.php");

header('Content-Type: application/json; charset=utf-8');

// Kiểm tra đăng nhập
if (empty($_SESSION['account'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    // Lấy dữ liệu từ form
    $tieuDe = trim($_POST['tieuDe'] ?? '');
    $maChuDe = intval($_POST['maChuDe'] ?? 0);
    $maKhoaHoc = intval($_POST['maKhoaHoc'] ?? 1); // Default to course 1
    $noiDungDeBai = trim($_POST['noiDungDeBai'] ?? '');
    $gioiHanTu = intval($_POST['gioiHanTu'] ?? 0);
    $thoiGianLamBai = intval($_POST['thoiGianLamBai'] ?? 0);
    $mucDo = trim($_POST['mucDo'] ?? '');
    $nguoiTao = trim($_POST['nguoiTao'] ?? '');

    // Validation
    if (empty($tieuDe)) {
        echo json_encode(['success' => false, 'error' => 'Tiêu đề không được để trống']);
        exit;
    }

    if ($maChuDe <= 0) {
        echo json_encode(['success' => false, 'error' => 'Vui lòng chọn chủ đề']);
        exit;
    }

    if (empty($noiDungDeBai)) {
        echo json_encode(['success' => false, 'error' => 'Nội dung đề bài không được để trống']);
        exit;
    }

    if ($gioiHanTu < 50 || $gioiHanTu > 1000) {
        echo json_encode(['success' => false, 'error' => 'Giới hạn từ phải từ 50 đến 1000']);
        exit;
    }

    if ($thoiGianLamBai < 15 || $thoiGianLamBai > 120) {
        echo json_encode(['success' => false, 'error' => 'Thời gian làm bài phải từ 15 đến 120 phút']);
        exit;
    }

    if (!in_array($mucDo, ['Dễ', 'Trung bình', 'Khó'])) {
        echo json_encode(['success' => false, 'error' => 'Mức độ không hợp lệ']);
        exit;
    }

    if (empty($nguoiTao)) {
        echo json_encode(['success' => false, 'error' => 'Người tạo không được để trống']);
        exit;
    }

    // Kiểm tra chủ đề có tồn tại không
    $topic = $Database->get_row("SELECT * FROM writing_topics WHERE MaChuDe = '$maChuDe' AND TrangThai = 1");
    if (!$topic) {
        echo json_encode(['success' => false, 'error' => 'Chủ đề không tồn tại']);
        exit;
    }

    // Thêm đề bài vào database
    $result = $Database->insert("writing_prompts", [
        'TieuDe' => $tieuDe,
        'MaChuDe' => $maChuDe,
        'MaKhoaHoc' => $maKhoaHoc,
        'NoiDungDeBai' => $noiDungDeBai,
        'GioiHanTu' => $gioiHanTu,
        'ThoiGianLamBai' => $thoiGianLamBai,
        'MucDo' => $mucDo,
        'NguoiTao' => $nguoiTao,
        'ThoiGianTao' => date('Y-m-d H:i:s'),
        'TrangThai' => 1
    ]);

    if ($result) {
        echo json_encode([
            'success' => true, 
            'message' => 'Thêm đề bài thành công'
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Không thể thêm đề bài vào database']);
    }

} catch (Exception $e) {
    error_log("Add writing prompt error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
}
?>
