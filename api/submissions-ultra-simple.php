<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once '../configs/database.php';
    
    $pdo = new PDO(
        "mysql:host=$servername;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );

    // Raw query để lấy tất cả submissions
    $sql = "SELECT * FROM writing_submissions ORDER BY ThoiGianNop DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $all_submissions = $stmt->fetchAll();
    
    // Filter trong PHP thay vì SQL
    $ungraded_submissions = [];
    foreach ($all_submissions as $submission) {
        // Kiểm tra nhiều cách khác nhau
        $status = trim($submission['TrangThaiCham'] ?? '');
        if (empty($status) || 
            $status === 'Chưa chấm' || 
            $status === 'Chua cham' ||
            mb_strtolower($status) === 'chưa chấm' ||
            mb_strtolower($status) === 'chua cham') {
            $ungraded_submissions[] = $submission;
        }
    }
    
    echo json_encode([
        'success' => true,
        'total_in_db' => count($all_submissions),
        'ungraded_count' => count($ungraded_submissions),
        'data' => $ungraded_submissions,
        'all_statuses' => array_unique(array_column($all_submissions, 'TrangThaiCham')),
        'debug_info' => [
            'charset' => 'utf8mb4',
            'method' => 'PHP filtering',
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_UNESCAPED_UNICODE);
}
?>
