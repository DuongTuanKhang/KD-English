<?php
// Tắt tất cả output buffer và error
ob_clean();
error_reporting(0);

// Set header JSON
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache');

try {
    // Include files
    require_once(__DIR__ . "/../configs/config.php");
    require_once(__DIR__ . "/../class/Database.php");

    $Database = new Database();

    // Kiểm tra POST data
    if ($_POST['action'] !== 'add_prompt') {
        echo '{"success":false,"message":"Action không hợp lệ"}';
        exit;
    }

    $maChuDe = $_POST['maChuDe'] ?? '';
    $maKhoaHoc = $_POST['maKhoaHoc'] ?? '';
    $tieuDe = $_POST['tieuDe'] ?? '';
    $noiDungDeBai = $_POST['noiDungDeBai'] ?? '';
    $gioiHanTu = $_POST['gioiHanTu'] ?? '';
    $mucDo = $_POST['mucDo'] ?? '';

    // Validate
    if (empty($maChuDe) || empty($maKhoaHoc) || empty($tieuDe) || empty($noiDungDeBai) || empty($gioiHanTu) || empty($mucDo)) {
        echo '{"success":false,"message":"Thiếu thông tin bắt buộc"}';
        exit;
    }

    // Simple validation - không escape để test
    // $maChuDe = addslashes($maChuDe);

    // Insert
    $sql = "INSERT INTO writing_prompts (MaChuDe, MaKhoaHoc, TieuDe, NoiDungDeBai, GioiHanTu, MucDo, NguoiTao) 
            VALUES ('$maChuDe', '$maKhoaHoc', '$tieuDe', '$noiDungDeBai', '$gioiHanTu', '$mucDo', 'admin')";

    if ($Database->query($sql)) {
        echo '{"success":true,"message":"Thêm đề bài thành công"}';
    } else {
        echo '{"success":false,"message":"Lỗi database"}';
    }

} catch (Exception $e) {
    echo '{"success":false,"message":"Lỗi server"}';
}
exit;
?>
