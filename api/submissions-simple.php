<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output

require_once(__DIR__ . "/../configs/config.php");

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$action = $_GET['action'] ?? '';
$status = $_GET['status'] ?? '';

try {
    if ($action === 'submissions' && $status === 'ungraded') {
        
        // Simple direct query
        $sql = "SELECT 
                    s.MaBaiViet,
                    s.TaiKhoan,
                    s.SoTu,
                    s.ThoiGianNop,
                    s.TrangThaiCham,
                    p.TieuDe,
                    n.TenHienThi
                FROM writing_submissions s 
                LEFT JOIN writing_prompts p ON s.MaDeBai = p.MaDeBai 
                LEFT JOIN nguoidung n ON s.TaiKhoan = n.TaiKhoan 
                WHERE s.TrangThaiCham = 'Chưa chấm'
                ORDER BY s.ThoiGianNop DESC";
        
        $result = $Database->get_list($sql);
        
        // Return clean JSON
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        
    } else {
        echo json_encode(['error' => 'Invalid action or status']);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'action' => $action,
        'status' => $status
    ]);
}
?>
