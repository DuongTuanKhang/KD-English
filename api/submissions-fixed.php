<?php
require_once(__DIR__ . "/../configs/config.php");

header('Content-Type: application/json; charset=utf-8');

try {
    $action = $_GET['action'] ?? '';
    $status = $_GET['status'] ?? '';
    
    if ($action === 'submissions' && $status === 'ungraded') {
        
        // Get ALL submissions first, then filter in PHP to avoid encoding issues
        $sql = "SELECT 
                    s.MaBaiViet,
                    s.TaiKhoan,
                    s.SoTu,
                    s.ThoiGianNop,
                    s.TrangThaiCham,
                    s.MaDeBai,
                    p.TieuDe,
                    n.TenHienThi
                FROM writing_submissions s 
                LEFT JOIN writing_prompts p ON s.MaDeBai = p.MaDeBai 
                LEFT JOIN nguoidung n ON s.TaiKhoan = n.TaiKhoan 
                ORDER BY s.ThoiGianNop DESC";
        
        $all_submissions = $Database->get_list($sql);
        
        // Filter ungraded submissions in PHP
        $ungraded_submissions = [];
        foreach ($all_submissions as $submission) {
            $status = trim($submission['TrangThaiCham']);
            // Check multiple possible values for "ungraded"
            if ($status === 'Chưa chấm' || 
                $status === 'Chua cham' || 
                strpos($status, 'chư') !== false || 
                strpos($status, 'ch') !== false && strpos($status, 'm') !== false ||
                empty($status) ||
                $status === 'pending') {
                $ungraded_submissions[] = $submission;
            }
        }
        
        echo json_encode([
            'success' => true,
            'total_submissions' => count($all_submissions),
            'ungraded_count' => count($ungraded_submissions),
            'data' => $ungraded_submissions,
            'debug_first_status' => count($all_submissions) > 0 ? $all_submissions[0]['TrangThaiCham'] : 'none'
        ], JSON_UNESCAPED_UNICODE);
        
    } else {
        echo json_encode(['error' => 'Invalid action or status']);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>
