<?php
header('Content-Type: application/json; charset=utf-8');
try {
    $mysqli = new mysqli('localhost', 'root', '', 'hocngoaingu');
    $mysqli->set_charset('utf8');
    
    $query = "SELECT 
        p.MaDeBai,
        p.TieuDe,
        p.NoiDungDeBai,
        'Tiếng Anh' as ten_khoa_hoc,
        COALESCE(wt.TenChuDe, 'Writing') as ten_chu_de,
        p.GioiHanTu,
        p.MucDo,
        p.ThoiGianLamBai,
        COUNT(ws.MaBaiViet) as SoBaiNop
    FROM writing_prompts p
    LEFT JOIN writing_topics wt ON p.MaChuDe = wt.MaChuDe
    LEFT JOIN writing_submissions ws ON p.MaDeBai = ws.MaDeBai
    GROUP BY p.MaDeBai, p.TieuDe, p.NoiDungDeBai, p.GioiHanTu, p.MucDo, p.ThoiGianLamBai, wt.TenChuDe
    ORDER BY p.MaDeBai DESC";
    
    $result = $mysqli->query($query);
    $prompts = [];
    while ($row = $result->fetch_assoc()) {
        $prompts[] = $row;
    }
    
    echo json_encode($prompts, JSON_UNESCAPED_UNICODE);
    $mysqli->close();
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
