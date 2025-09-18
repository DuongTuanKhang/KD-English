<?php
header('Content-Type: application/json');

// Kết nối database
$conn = new mysqli('localhost', 'root', '', 'hocngoaingu');
if ($conn->connect_error) {
    echo '{"success":false,"message":"Lỗi database"}';
    exit;
}
$conn->set_charset("utf8");

// Lấy danh sách prompts
$sql = "SELECT 
    wp.MaDeBai as id,
    wp.TieuDe as tieu_de,
    wp.NoiDungDeBai,
    wp.GioiHanTu as gioi_han_tu,
    wp.MucDo as muc_do,
    wp.TrangThai as trang_thai,
    wp.ThoiGianTao,
    wp.NguoiTao,
    kh.TenKhoaHoc as ten_khoa_hoc,
    bl.TenBaiHoc as ten_chu_de
FROM writing_prompts wp
LEFT JOIN khoahoc kh ON wp.MaKhoaHoc = kh.MaKhoaHoc 
LEFT JOIN baihoc bl ON wp.MaChuDe = bl.MaBaiHoc
ORDER BY wp.ThoiGianTao DESC";

$result = $conn->query($sql);
$prompts = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $prompts[] = [
            'id' => $row['id'],
            'tieu_de' => $row['tieu_de'],
            'ten_khoa_hoc' => $row['ten_khoa_hoc'] ?: 'Chưa phân loại',
            'ten_chu_de' => $row['ten_chu_de'] ?: 'Chưa phân loại', 
            'gioi_han_tu' => $row['gioi_han_tu'],
            'muc_do' => $row['muc_do'],
            'trang_thai' => $row['trang_thai'] ?? 1,
            'thoi_gian' => 30,
            'so_bai_nop' => 0
        ];
    }
}

echo json_encode($prompts);
$conn->close();
?>
