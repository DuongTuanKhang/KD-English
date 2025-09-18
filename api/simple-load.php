<?php
header('Content-Type: application/json');

// Kết nối database
$conn = new mysqli('localhost', 'root', '', 'hocngoaingu');
if ($conn->connect_error) {
    echo '[]';
    exit;
}
$conn->set_charset("utf8");

// Query đơn giản
$sql = "SELECT 
    MaDeBai as id,
    TieuDe as tieu_de,
    NoiDungDeBai,
    GioiHanTu as gioi_han_tu,
    MucDo as muc_do,
    NguoiTao,
    ThoiGianTao
FROM writing_prompts 
ORDER BY ThoiGianTao DESC 
LIMIT 50";

$result = $conn->query($sql);
$prompts = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $prompts[] = [
            'id' => $row['id'],
            'tieu_de' => $row['tieu_de'],
            'ten_khoa_hoc' => 'Tiếng Anh',
            'ten_chu_de' => 'Writing',
            'gioi_han_tu' => $row['gioi_han_tu'],
            'muc_do' => $row['muc_do'] ?: 'Dễ',
            'nguoi_tao' => $row['NguoiTao'],
            'thoi_gian_tao' => $row['ThoiGianTao']
        ];
    }
}

echo json_encode($prompts);
$conn->close();
?>
