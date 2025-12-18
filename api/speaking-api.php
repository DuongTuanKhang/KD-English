<?php
require_once(__DIR__ . "/../configs/config.php");
require_once(__DIR__ . "/../configs/function.php");

header('Content-Type: application/json; charset=utf-8');

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check authentication
if (!isset($_SESSION["account"])) {
    response_json(false, 'Unauthorized', null);
    exit();
}

$user = $Database->get_row("SELECT * FROM nguoidung WHERE taikhoan = '" . $_SESSION["account"] . "'");
if (!$user) {
    response_json(false, 'User not found', null);
    exit();
}

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

switch ($action) {
    case 'get_topics':
        get_topics();
        break;
    case 'get_lessons':
        get_lessons();
        break;
    case 'get_lesson_detail':
        get_lesson_detail();
        break;
    case 'save_result':
        save_result();
        break;
    case 'get_progress':
        get_progress();
        break;
    case 'get_statistics':
        get_statistics();
        break;
    default:
        response_json(false, 'Invalid action', null);
        break;
}

// Get all topics with user progress
function get_topics() {
    global $Database, $user;
    
    $topics = $Database->get_list("
        SELECT st.*,
               COUNT(sl.MaBaiSpeaking) as TongBaiHoc,
               COUNT(CASE WHEN sr.TongDiem >= sl.DiemToiThieu THEN 1 END) as BaiHoanThanh,
               AVG(sr.TongDiem) as DiemTrungBinh
        FROM speaking_topics st
        LEFT JOIN speaking_lessons sl ON st.MaChuDe = sl.MaChuDe AND sl.TrangThai = 1
        LEFT JOIN speaking_results sr ON sl.MaBaiSpeaking = sr.MaBaiSpeaking AND sr.TaiKhoan = '" . $user['TaiKhoan'] . "'
        WHERE st.TrangThai = 1
        GROUP BY st.MaChuDe
        ORDER BY st.ThuTu ASC, st.MaChuDe ASC
    ");
    
    foreach ($topics as &$topic) {
        $topic['TyLeHoanThanh'] = $topic['TongBaiHoc'] > 0 ? round(($topic['BaiHoanThanh'] / $topic['TongBaiHoc']) * 100, 1) : 0;
        $topic['DiemTrungBinh'] = $topic['DiemTrungBinh'] ? round($topic['DiemTrungBinh'], 1) : 0;
    }
    
    response_json(true, 'Success', $topics);
}

// Get lessons for a topic
function get_lessons() {
    global $Database, $user;
    
    $topicId = isset($_GET['topic_id']) ? (int)$_GET['topic_id'] : 0;
    if (!$topicId) {
        response_json(false, 'Topic ID required', null);
        return;
    }
    
    $lessons = $Database->get_list("
        SELECT sl.*,
               COUNT(sr.MaKetQua) as SoLanLam,
               MAX(sr.TongDiem) as DiemCaoNhat,
               MAX(sr.ThoiGianNop) as LanCuoi
        FROM speaking_lessons sl
        LEFT JOIN speaking_results sr ON sl.MaBaiSpeaking = sr.MaBaiSpeaking AND sr.TaiKhoan = '" . $user['TaiKhoan'] . "'
        WHERE sl.MaChuDe = $topicId AND sl.TrangThai = 1
        GROUP BY sl.MaBaiSpeaking
        ORDER BY sl.ThuTu ASC, sl.MaBaiSpeaking ASC
    ");
    
    foreach ($lessons as &$lesson) {
        $lesson['HoanThanh'] = $lesson['DiemCaoNhat'] >= $lesson['DiemToiThieu'];
        $lesson['DiemCaoNhat'] = $lesson['DiemCaoNhat'] ?: 0;
        $lesson['SoLanLam'] = $lesson['SoLanLam'] ?: 0;
    }
    
    response_json(true, 'Success', $lessons);
}

// Get lesson detail
function get_lesson_detail() {
    global $Database, $user;
    
    $lessonId = isset($_GET['lesson_id']) ? (int)$_GET['lesson_id'] : 0;
    if (!$lessonId) {
        response_json(false, 'Lesson ID required', null);
        return;
    }
    
    $lesson = $Database->get_row("
        SELECT sl.*,
               st.TenChuDe,
               st.TenChuDeEng,
               COUNT(sr.MaKetQua) as SoLanLam,
               MAX(sr.TongDiem) as DiemCaoNhat
        FROM speaking_lessons sl
        JOIN speaking_topics st ON sl.MaChuDe = st.MaChuDe
        LEFT JOIN speaking_results sr ON sl.MaBaiSpeaking = sr.MaBaiSpeaking AND sr.TaiKhoan = '" . $user['TaiKhoan'] . "'
        WHERE sl.MaBaiSpeaking = $lessonId AND sl.TrangThai = 1
        GROUP BY sl.MaBaiSpeaking
    ");
    
    if (!$lesson) {
        response_json(false, 'Lesson not found', null);
        return;
    }
    
    $lesson['HoanThanh'] = $lesson['DiemCaoNhat'] >= $lesson['DiemToiThieu'];
    $lesson['DiemCaoNhat'] = $lesson['DiemCaoNhat'] ?: 0;
    $lesson['SoLanLam'] = $lesson['SoLanLam'] ?: 0;
    
    // Get recent results
    $lesson['KetQuaGanDay'] = $Database->get_list("
        SELECT TongDiem, ThoiGianNop, TextNhanDien
        FROM speaking_results 
        WHERE MaBaiSpeaking = $lessonId AND TaiKhoan = '" . $user['TaiKhoan'] . "'
        ORDER BY ThoiGianNop DESC
        LIMIT 5
    ");
    
    response_json(true, 'Success', $lesson);
}

// Save pronunciation result
function save_result() {
    global $Database, $user;
    
    $lessonId = isset($_POST['lesson_id']) ? (int)$_POST['lesson_id'] : 0;
    $score = isset($_POST['score']) ? (float)$_POST['score'] : 0;
    $spokenText = isset($_POST['spoken_text']) ? $_POST['spoken_text'] : '';
    $audioData = isset($_POST['audio_data']) ? $_POST['audio_data'] : '';
    
    if (!$lessonId || $score < 0 || $score > 100) {
        response_json(false, 'Invalid data', null);
        return;
    }
    
    // Verify lesson exists
    $lesson = $Database->get_row("SELECT * FROM speaking_lessons WHERE MaBaiSpeaking = $lessonId AND TrangThai = 1");
    if (!$lesson) {
        response_json(false, 'Lesson not found', null);
        return;
    }
    
    // Scale score to 0-10
    $finalScore = round($score / 10, 1);
    
    // Insert result
    $insertData = [
        'MaBaiSpeaking' => $lessonId,
        'TaiKhoan' => $user['TaiKhoan'],
        'TongDiem' => $finalScore,
        'TextNhanDien' => $spokenText,
        'ThoiGianNop' => date('Y-m-d H:i:s')
    ];
    
    if ($audioData) {
        // Save audio file (placeholder)
        $audioFileName = 'speaking_' . $lessonId . '_' . $user['TaiKhoan'] . '_' . time() . '.wav';
        $insertData['DuongDanAudio'] = $audioFileName;
        
        // Here you would save the actual audio file
        // file_put_contents(__DIR__ . '/../uploads/speaking/' . $audioFileName, base64_decode($audioData));
    }
    
    $result = $Database->insert("speaking_results", $insertData);
    
    if ($result) {
        // Update progress
        update_user_progress($user['TaiKhoan'], $lesson['MaChuDe']);
        
        // Get updated lesson info
        $updatedInfo = $Database->get_row("
            SELECT COUNT(*) as SoLanLam,
                   MAX(TongDiem) as DiemCaoNhat,
                   AVG(TongDiem) as DiemTrungBinh
            FROM speaking_results 
            WHERE MaBaiSpeaking = $lessonId AND TaiKhoan = '" . $user['TaiKhoan'] . "'
        ");
        
        $responseData = [
            'lesson_id' => $lessonId,
            'score' => $finalScore,
            'attempts' => $updatedInfo['SoLanLam'],
            'best_score' => $updatedInfo['DiemCaoNhat'],
            'average_score' => round($updatedInfo['DiemTrungBinh'], 1),
            'completed' => $updatedInfo['DiemCaoNhat'] >= $lesson['DiemToiThieu']
        ];
        
        response_json(true, 'Result saved successfully', $responseData);
    } else {
        response_json(false, 'Failed to save result', null);
    }
}

// Update user progress for a topic
function update_user_progress($taiKhoan, $maChuDe) {
    global $Database;
    
    // Check if progress record exists
    $progress = $Database->get_row("
        SELECT * FROM speaking_progress 
        WHERE TaiKhoan = '$taiKhoan' AND MaChuDe = $maChuDe
    ");
    
    // Calculate progress stats
    $stats = $Database->get_row("
        SELECT COUNT(DISTINCT sl.MaBaiSpeaking) as TongBaiHoc,
               COUNT(DISTINCT CASE WHEN sr.TongDiem >= sl.DiemToiThieu THEN sl.MaBaiSpeaking END) as BaiHoanThanh,
               AVG(CASE WHEN sr.TongDiem IS NOT NULL THEN sr.TongDiem END) as DiemTrungBinh,
               MAX(sr.ThoiGianNop) as ThoiGianCapNhat
        FROM speaking_lessons sl
        LEFT JOIN speaking_results sr ON sl.MaBaiSpeaking = sr.MaBaiSpeaking AND sr.TaiKhoan = '$taiKhoan'
        WHERE sl.MaChuDe = $maChuDe AND sl.TrangThai = 1
    ");
    
    $tyLeHoanThanh = $stats['TongBaiHoc'] > 0 ? ($stats['BaiHoanThanh'] / $stats['TongBaiHoc']) * 100 : 0;
    
    $progressData = [
        'TaiKhoan' => $taiKhoan,
        'MaChuDe' => $maChuDe,
        'TongBaiHoc' => $stats['TongBaiHoc'],
        'BaiHoanThanh' => $stats['BaiHoanThanh'],
        'TyLeHoanThanh' => round($tyLeHoanThanh, 1),
        'DiemTrungBinh' => $stats['DiemTrungBinh'] ? round($stats['DiemTrungBinh'], 1) : 0,
        'ThoiGianCapNhat' => $stats['ThoiGianCapNhat'] ?: date('Y-m-d H:i:s')
    ];
    
    if ($progress) {
        // Update existing progress
        $Database->update("speaking_progress", $progressData, "TaiKhoan = '$taiKhoan' AND MaChuDe = $maChuDe");
    } else {
        // Insert new progress
        $progressData['ThoiGianTao'] = date('Y-m-d H:i:s');
        $Database->insert("speaking_progress", $progressData);
    }
}

// Get user progress
function get_progress() {
    global $Database, $user;
    
    $topicId = isset($_GET['topic_id']) ? (int)$_GET['topic_id'] : null;
    
    if ($topicId) {
        // Get progress for specific topic
        $progress = $Database->get_row("
            SELECT sp.*,
                   st.TenChuDe,
                   st.TenChuDeEng
            FROM speaking_progress sp
            JOIN speaking_topics st ON sp.MaChuDe = st.MaChuDe
            WHERE sp.TaiKhoan = '" . $user['TaiKhoan'] . "' AND sp.MaChuDe = $topicId
        ");
        
        response_json(true, 'Success', $progress);
    } else {
        // Get all progress
        $progress = $Database->get_list("
            SELECT sp.*,
                   st.TenChuDe,
                   st.TenChuDeEng,
                   st.CapDo
            FROM speaking_progress sp
            JOIN speaking_topics st ON sp.MaChuDe = st.MaChuDe
            WHERE sp.TaiKhoan = '" . $user['TaiKhoan'] . "'
            ORDER BY sp.ThoiGianCapNhat DESC
        ");
        
        response_json(true, 'Success', $progress);
    }
}

// Get user statistics
function get_statistics() {
    global $Database, $user;
    
    // Overall stats
    $overallStats = $Database->get_row("
        SELECT COUNT(DISTINCT sr.MaBaiSpeaking) as TongBaiDaLam,
               COUNT(DISTINCT CASE WHEN sr.TongDiem >= sl.DiemToiThieu THEN sr.MaBaiSpeaking END) as TongBaiHoanThanh,
               AVG(sr.TongDiem) as DiemTrungBinh,
               MAX(sr.TongDiem) as DiemCaoNhat,
               COUNT(sr.MaKetQua) as TongLuotThi,
               COUNT(DISTINCT sr.MaChuDe) as ChuDeHoanThanh
        FROM speaking_results sr
        JOIN speaking_lessons sl ON sr.MaBaiSpeaking = sl.MaBaiSpeaking
        WHERE sr.TaiKhoan = '" . $user['TaiKhoan'] . "'
    ");
    
    // Recent activity
    $recentActivity = $Database->get_list("
        SELECT sr.TongDiem,
               sr.ThoiGianNop,
               sl.TieuDe,
               st.TenChuDe
        FROM speaking_results sr
        JOIN speaking_lessons sl ON sr.MaBaiSpeaking = sl.MaBaiSpeaking
        JOIN speaking_topics st ON sl.MaChuDe = st.MaChuDe
        WHERE sr.TaiKhoan = '" . $user['TaiKhoan'] . "'
        ORDER BY sr.ThoiGianNop DESC
        LIMIT 10
    ");
    
    // Progress by topic
    $topicProgress = $Database->get_list("
        SELECT st.TenChuDe,
               st.CapDo,
               COUNT(DISTINCT sl.MaBaiSpeaking) as TongBaiHoc,
               COUNT(DISTINCT CASE WHEN sr.TongDiem >= sl.DiemToiThieu THEN sl.MaBaiSpeaking END) as BaiHoanThanh,
               AVG(sr.TongDiem) as DiemTrungBinh
        FROM speaking_topics st
        LEFT JOIN speaking_lessons sl ON st.MaChuDe = sl.MaChuDe AND sl.TrangThai = 1
        LEFT JOIN speaking_results sr ON sl.MaBaiSpeaking = sr.MaBaiSpeaking AND sr.TaiKhoan = '" . $user['TaiKhoan'] . "'
        WHERE st.TrangThai = 1
        GROUP BY st.MaChuDe
        ORDER BY st.ThuTu ASC
    ");
    
    // Learning streak
    $streak = calculate_learning_streak($user['TaiKhoan']);
    
    $statistics = [
        'overall' => $overallStats,
        'recent_activity' => $recentActivity,
        'topic_progress' => $topicProgress,
        'learning_streak' => $streak
    ];
    
    response_json(true, 'Success', $statistics);
}

// Calculate learning streak
function calculate_learning_streak($taiKhoan) {
    global $Database;
    
    $dates = $Database->get_list("
        SELECT DISTINCT DATE(ThoiGianNop) as NgayHoc
        FROM speaking_results
        WHERE TaiKhoan = '$taiKhoan'
        ORDER BY NgayHoc DESC
        LIMIT 30
    ");
    
    $streak = 0;
    $currentDate = date('Y-m-d');
    
    foreach ($dates as $index => $date) {
        $expectedDate = date('Y-m-d', strtotime($currentDate . ' -' . $index . ' days'));
        if ($date['NgayHoc'] === $expectedDate) {
            $streak++;
        } else {
            break;
        }
    }
    
    return $streak;
}

// Response helper function
function response_json($success, $message, $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
}
?>