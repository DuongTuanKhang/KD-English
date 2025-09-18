<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Kết nối database trực tiếp
    $mysqli = new mysqli('localhost', 'root', '', 'hocngoaingu');
    
    if ($mysqli->connect_error) {
        die(json_encode(['error' => 'Database connection failed: ' . $mysqli->connect_error]));
    }
    
    $mysqli->set_charset('utf8');
    
    // Query với JOIN để lấy tên chủ đề đúng (chỉ lấy 1 record đầu tiên cho mỗi MaBaiHoc)
    $query = "SELECT 
        p.MaDeBai,
        p.TieuDe,
        'Tiếng Anh' as ten_khoa_hoc,
        COALESCE(
            (SELECT TenBaiHoc FROM baihoc WHERE MaBaiHoc = p.MaChuDe LIMIT 1), 
            'Writing'
        ) as ten_chu_de,
        p.GioiHanTu,
        p.MucDo,
        p.ThoiGianTao,
        p.ThoiGianLamBai
    FROM writing_prompts p
    ORDER BY p.MaDeBai DESC";
    
    $result = $mysqli->query($query);
    
    if (!$result) {
        die(json_encode(['error' => 'Query failed: ' . $mysqli->error]));
    }
    
    $prompts = [];
    while ($row = $result->fetch_assoc()) {
        $prompts[] = $row;
    }
    
    $mysqli->close();
    
    echo json_encode($prompts, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
