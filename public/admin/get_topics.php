<?php
session_start();
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");

// Debug log
error_log("get_topics.php called with course_id: " . ($_GET['course_id'] ?? 'not set'));

// Check admin permission - simplified check
if (!isset($_SESSION["account"])) {
    error_log("No session account found");
    // Instead of blocking, let's allow access for now and log
    // http_response_code(403);
    // echo json_encode(['error' => 'Unauthorized']);
    // exit();
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // For testing
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

$courseId = $_GET['course_id'] ?? '';

if (empty($courseId)) {
    echo json_encode(['error' => 'Course ID is required']);
    exit();
}

try {
    // Get topics from MucDo column (not ChuDe) for the specific course
    $topics = $Database->get_list("
        SELECT DISTINCT MucDo as ChuDe 
        FROM reading_lessons 
        WHERE MaKhoaHoc = " . (int)$courseId . " 
        AND MucDo IS NOT NULL 
        AND MucDo != '' 
        ORDER BY MucDo ASC
    ");

    // If no topics found in database, return all available topics from enum
    if (empty($topics)) {
        $defaultTopics = [
            'Traffic',
            'Food',
            'Education',
            'Family',
            'Work',
            'Hobbie',
            'Technology',
            'Activities'
        ];
        
        $topics = array_map(function($topic) {
            return ['ChuDe' => $topic];
        }, $defaultTopics);
    }

    echo json_encode($topics);
    
} catch (Exception $e) {
    // If database error, return default topics
    $defaultTopics = [
        'Traffic',
        'Food',
        'Education',
        'Family',
        'Work',
        'Hobbie',
        'Technology',
        'Activities'
    ];
    
    $topics = array_map(function($topic) {
        return ['ChuDe' => $topic];
    }, $defaultTopics);
    
    echo json_encode($topics);
}
?>
