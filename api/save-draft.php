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
$content = $_POST['content'] ?? '';
$word_count = $_POST['word_count'] ?? 0;
$user_account = $_SESSION['account'];

if (!$prompt_id || !$content) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Verify prompt exists
$prompt = $Database->get_row("SELECT * FROM writing_prompts WHERE MaDeBai = '$prompt_id'");
if (!$prompt) {
    echo json_encode(['success' => false, 'message' => 'Prompt not found']);
    exit;
}

try {
    // Check if draft already exists
    $existing_draft = $Database->get_row("SELECT * FROM writing_drafts WHERE prompt_id = '$prompt_id' AND user_account = '$user_account'");
    
    if ($existing_draft) {
        // Update existing draft
        $escaped_content = mysqli_real_escape_string($Database->connect(), $content);
        $sql = "UPDATE writing_drafts SET 
                content = '$escaped_content',
                word_count = '$word_count',
                updated_at = NOW()
                WHERE prompt_id = '$prompt_id' AND user_account = '$user_account'";
    } else {
        // Create new draft
        $escaped_content = mysqli_real_escape_string($Database->connect(), $content);
        $sql = "INSERT INTO writing_drafts (prompt_id, user_account, content, word_count, created_at, updated_at) 
                VALUES ('$prompt_id', '$user_account', '$escaped_content', '$word_count', NOW(), NOW())";
    }
    
    if ($Database->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Draft saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save draft']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
