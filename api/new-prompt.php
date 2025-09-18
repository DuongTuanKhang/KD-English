<?php
// Bắt đầu output buffering
ob_start();

// Include các file cần thiết
include_once(__DIR__ . "/../configs/config.php");
include_once(__DIR__ . "/../class/Database.php");

// Xóa buffer và set header
ob_clean();
header('Content-Type: application/json; charset=utf-8');

$db = new Database();

// Kiểm tra method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '{"success":false,"message":"Method not allowed"}';
    exit;
}

// Lấy dữ liệu từ form
$maChuDe = isset($_POST['maChuDe']) ? $_POST['maChuDe'] : '';
$maKhoaHoc = isset($_POST['maKhoaHoc']) ? $_POST['maKhoaHoc'] : '';
$tieuDe = isset($_POST['tieuDe']) ? $_POST['tieuDe'] : '';
$noiDungDeBai = isset($_POST['noiDungDeBai']) ? $_POST['noiDungDeBai'] : '';
$gioiHanTu = isset($_POST['gioiHanTu']) ? $_POST['gioiHanTu'] : '';
$mucDo = isset($_POST['mucDo']) ? $_POST['mucDo'] : '';

// Validate dữ liệu
if (empty($maChuDe) || empty($maKhoaHoc) || empty($tieuDe) || empty($noiDungDeBai) || empty($gioiHanTu) || empty($mucDo)) {
    echo '{"success":false,"message":"Vui lòng điền đầy đủ thông tin"}';
    exit;
}

// Tạo câu SQL
$sql = "INSERT INTO writing_prompts (MaChuDe, MaKhoaHoc, TieuDe, NoiDungDeBai, GioiHanTu, MucDo, NguoiTao, TrangThai, ThoiGianTao) 
        VALUES ('$maChuDe', '$maKhoaHoc', '$tieuDe', '$noiDungDeBai', '$gioiHanTu', '$mucDo', 'admin', 1, NOW())";

// Thực hiện insert
$result = $db->query($sql);

if ($result) {
    echo '{"success":true,"message":"Thêm đề bài thành công!"}';
} else {
    echo '{"success":false,"message":"Có lỗi xảy ra khi thêm đề bài"}';
}

exit;
?>
