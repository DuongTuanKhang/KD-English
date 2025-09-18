<?php
require_once(__DIR__ . "/../configs/config.php");
require_once(__DIR__ . "/../configs/function.php");

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set content type to JSON
header('Content-Type: application/json');

// Add error logging
error_log("API save_reading_result called");
error_log("Session data: " . print_r($_SESSION, true));

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    error_log("User not logged in - session missing");
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get JSON input
$input = file_get_contents('php://input');
error_log("Raw input: " . $input);

$data = json_decode($input, true);
error_log("Decoded data: " . print_r($data, true));

if (!$data) {
    error_log("Invalid JSON data received");
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

// Validate required fields
$requiredFields = ['score', 'correct', 'total', 'details', 'topic', 'course'];
foreach ($requiredFields as $field) {
    if (!isset($data[$field])) {
        echo json_encode(['success' => false, 'message' => "Missing field: $field"]);
        exit;
    }
}

// Save result details to session
$_SESSION['reading_result_details'] = $data['details'];

// Optionally save to database for future analytics
try {
    $userId = $_SESSION['user']['MaNguoiDung'];
    $score = (int)$data['score'];
    $correct = (int)$data['correct'];
    $total = (int)$data['total'];
    $topic = $data['topic'];
    $course = (int)$data['course'];
    
    // Insert into reading_results table (if exists) or create log
    $insertData = [
        'MaNguoiDung' => $userId,
        'MaKhoaHoc' => $course,
        'ChuDe' => $topic,
        'Diem' => $score,
        'SoCauDung' => $correct,
        'TongSoCau' => $total,
        'NgayLam' => date('Y-m-d H:i:s'),
        'ChiTiet' => json_encode($data['details'])
    ];
    
    // Try to insert into reading_results table (create if not exists)
    $Database->insert('reading_results', $insertData);
    
} catch (Exception $e) {
    // Log error but don't fail the request
    error_log("Failed to save reading result to database: " . $e->getMessage());
}

echo json_encode(['success' => true, 'message' => 'Result saved successfully']);
?>
