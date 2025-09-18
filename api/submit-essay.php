<?php
require_once(__DIR__ . "/../configs/config.php");
require_once(__DIR__ . "/../configs/function.php");

header('Content-Type: application/json');

if (empty($_SESSION['account'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$prompt_id = $_POST['prompt_id'] ?? '';
$ma_khoa_hoc = $_POST['ma_khoa_hoc'] ?? '';
$content = $_POST['content'] ?? '';
$word_count = $_POST['word_count'] ?? 0;
$time_spent = $_POST['time_spent'] ?? 0;
$user_account = $_SESSION['account'];

if (!$prompt_id || !$ma_khoa_hoc || !$content) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Verify prompt exists
$prompt = $Database->get_row("SELECT * FROM writing_prompts WHERE MaDeBai = '$prompt_id'");
if (!$prompt) {
    echo json_encode(['success' => false, 'message' => 'Prompt not found']);
    exit;
}

// Check if user is enrolled in the course
$checkDangKy = $Database->get_row("SELECT * FROM dangkykhoahoc WHERE TaiKhoan = '$user_account' AND MaKhoaHoc = '$ma_khoa_hoc'");
if (!$checkDangKy) {
    echo json_encode(['success' => false, 'message' => 'Not enrolled in this course']);
    exit;
}

// Check if already submitted
$existing_submission = $Database->get_row("SELECT * FROM writing_submissions WHERE MaDeBai = '$prompt_id' AND TaiKhoan = '$user_account'");
if ($existing_submission) {
    echo json_encode(['success' => false, 'message' => 'Already submitted this essay']);
    exit;
}

try {
    // Insert submission - using safe escaping
    $escaped_content = addslashes($content);
    $sql = "INSERT INTO writing_submissions 
            (MaDeBai, TaiKhoan, NoiDungBaiViet, SoTu, ThoiGianNop) 
            VALUES ('$prompt_id', '$user_account', '$escaped_content', '$word_count', NOW())";
    
    if ($Database->query($sql)) {
        // Delete draft if exists
        $Database->query("DELETE FROM writing_drafts WHERE prompt_id = '$prompt_id' AND user_account = '$user_account'");
        
        echo json_encode(['success' => true, 'message' => 'Essay submitted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit essay']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
