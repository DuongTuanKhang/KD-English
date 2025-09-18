<?php
require_once(__DIR__ . "/../configs/config.php");
require_once(__DIR__ . "/../configs/function.php");

header('Content-Type: application/json');

if (empty($_SESSION['account'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$prompt_id = $_POST['prompt_id'] ?? '';
$ma_khoa_hoc = $_POST['ma_khoa_hoc'] ?? '';
$content = $_POST['content'] ?? '';
$word_count = $_POST['word_count'] ?? 0;
$user_account = $_SESSION['account'];

if (!$prompt_id || !$content) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Check if already submitted
$existing = $Database->get_row("SELECT MaBaiViet FROM writing_submissions WHERE MaDeBai = '$prompt_id' AND TaiKhoan = '$user_account'");
if ($existing) {
    echo json_encode(['success' => false, 'message' => 'Already submitted this essay']);
    exit;
}

// Insert submission
$content_safe = str_replace("'", "''", $content); // Simple SQL escape
$sql = "INSERT INTO writing_submissions (MaDeBai, TaiKhoan, NoiDungBaiViet, SoTu, ThoiGianNop, TrangThaiCham) 
        VALUES ('$prompt_id', '$user_account', '$content_safe', '$word_count', NOW(), 'Chưa chấm')";

if ($Database->query($sql)) {
    echo json_encode(['success' => true, 'message' => 'Essay submitted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit essay']);
}
?>
