<?php
// Tắt tất cả lỗi và output
error_reporting(0);
ob_start();

// Kết nối database đơn giản
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'webhocngoaingu';

$conn = new mysqli($host, $user, $pass, $dbname);
$conn->set_charset("utf8");

if ($conn->connect_error) {
    ob_clean();
    header('Content-Type: application/json');
    echo '{"success":false,"message":"Lỗi kết nối database"}';
    exit;
}

// Lấy dữ liệu POST
$maChuDe = $_POST['maChuDe'] ?? '';
$maKhoaHoc = $_POST['maKhoaHoc'] ?? '';
$tieuDe = $_POST['tieuDe'] ?? '';
$noiDungDeBai = $_POST['noiDungDeBai'] ?? '';
$gioiHanTu = $_POST['gioiHanTu'] ?? '';
$mucDo = $_POST['mucDo'] ?? '';

// Validate đơn giản
if (empty($tieuDe) || empty($noiDungDeBai)) {
    ob_clean();
    header('Content-Type: application/json');
    echo '{"success":false,"message":"Vui lòng nhập đủ tiêu đề và nội dung"}';
    exit;
}

// Insert vào database
$sql = "INSERT INTO writing_prompts (MaChuDe, MaKhoaHoc, TieuDe, NoiDungDeBai, GioiHanTu, MucDo, NguoiTao) 
        VALUES (1, 1, '$tieuDe', '$noiDungDeBai', '$gioiHanTu', '$mucDo', 'admin')";

if ($conn->query($sql)) {
    ob_clean();
    header('Content-Type: application/json');
    echo '{"success":true,"message":"Thêm bài viết thành công!"}';
} else {
    ob_clean();
    header('Content-Type: application/json');
    echo '{"success":false,"message":"Lỗi thêm bài viết"}';
}

$conn->close();
exit;
?>
