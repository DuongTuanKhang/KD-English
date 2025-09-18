<?php
require_once(__DIR__ . "/../configs/config.php");
require_once(__DIR__ . "/../configs/function.php");

header('Content-Type: application/json');

if (empty($_SESSION['account'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'topics':
        getTopics();
        break;
    case 'prompts':
        getPrompts();
        break;
    case 'get_prompt':
        getPrompt();
        break;
    case 'submit_writing':
        submitWriting();
        break;
    case 'my_submissions':
        getMySubmissions();
        break;
    case 'get_submission':
        getSubmission();
        break;
    case 'find_topic_by_lesson':
        findTopicByLesson();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function getTopics()
{
    global $Database;

    $courseId = $_GET['course'] ?? '';
    if (!$courseId) {
        echo json_encode([]);
        return;
    }

    // Cải tiến: Lấy các chủ đề có prompts từ writing_prompts với mapping đúng
    $sql = "SELECT 
        wp.MaChuDe as MaChuDe,
        wt.TenChuDe as TenChuDe,
        wt.MoTa as MoTa,
        COUNT(wp.MaDeBai) as SoDeBai
    FROM writing_prompts wp 
    LEFT JOIN writing_topics wt ON wp.MaChuDe = wt.MaChuDe
    WHERE wp.TrangThai = 1 AND wt.MaKhoaHoc = '$courseId'
    GROUP BY wp.MaChuDe, wt.TenChuDe, wt.MoTa
    ORDER BY wt.TenChuDe";

    $topics = $Database->get_list($sql);

    // Chỉ lấy những topic có tên
    $validTopics = [];
    foreach ($topics as $topic) {
        if ($topic['TenChuDe']) {
            $validTopics[] = $topic;
        }
    }

    echo json_encode($validTopics);
}

function getPrompts()
{
    global $Database;

    $topicId = $_GET['topic'] ?? '';
    if (!$topicId) {
        echo json_encode([]);
        return;
    }

    $userAccount = $_SESSION['account'];

    // Cải tiến: Lấy prompts với thông tin submission đầy đủ hơn
    $sql = "SELECT 
        p.MaDeBai,
        p.TieuDe,
        p.NoiDungDeBai,
        p.GioiHanTu,
        p.MucDo,
        p.ThoiGianTao,
        p.ThoiGianLamBai,
        CASE 
            WHEN s.MaBaiViet IS NOT NULL THEN 1 
            ELSE 0 
        END as isSubmitted,
        s.DiemSo as score,
        s.TrangThaiCham as gradeStatus
    FROM writing_prompts p
    LEFT JOIN writing_submissions s ON p.MaDeBai = s.MaDeBai AND s.TaiKhoan = '$userAccount'
    WHERE p.MaChuDe = '$topicId' AND p.TrangThai = 1 
    ORDER BY p.ThoiGianTao DESC";

    $prompts = $Database->get_list($sql);

    // Convert isSubmitted to boolean
    foreach ($prompts as &$prompt) {
        $prompt['isSubmitted'] = $prompt['isSubmitted'] > 0;
    }

    echo json_encode($prompts);
}

function getPrompt()
{
    global $Database;

    $promptId = $_GET['id'] ?? '';
    if (!$promptId) {
        echo json_encode(['success' => false, 'message' => 'Missing ID']);
        return;
    }

    $sql = "SELECT * FROM writing_prompts WHERE MaDeBai = '$promptId' AND TrangThai = 1";
    $prompt = $Database->get_row($sql);

    if (!$prompt) {
        echo json_encode(['success' => false, 'message' => 'Prompt not found']);
        return;
    }

    echo json_encode($prompt);
}

function submitWriting()
{
    global $Database;

    $maDeBai = $_POST['maDeBai'] ?? '';
    $noiDung = $_POST['noiDung'] ?? '';
    $taiKhoan = $_SESSION['account'];

    if (!$maDeBai || !$noiDung) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        return;
    }

    // Check if user already submitted for this prompt
    $existing = $Database->get_row("SELECT * FROM writing_submissions WHERE MaDeBai = '$maDeBai' AND TaiKhoan = '$taiKhoan'");
    if ($existing) {
        echo json_encode(['success' => false, 'message' => 'Bạn đã nộp bài cho đề này rồi']);
        return;
    }

    // Count words
    $soTu = count(array_filter(preg_split('/\s+/', trim($noiDung))));

    // Use proper insert method
    $data = [
        'MaDeBai' => $maDeBai,
        'TaiKhoan' => $taiKhoan,
        'NoiDungBaiViet' => $noiDung,
        'SoTu' => $soTu,
        'TrangThaiCham' => 'Chưa chấm'
    ];

    if ($Database->insert('writing_submissions', $data)) {
        echo json_encode(['success' => true, 'message' => 'Submission successful']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit writing']);
    }
}

function getMySubmissions()
{
    global $Database;

    $courseId = $_GET['course'] ?? '';
    $taiKhoan = $_SESSION['account'];

    $where = "WHERE s.TaiKhoan = '$taiKhoan'";
    if ($courseId) {
        $where .= " AND p.MaKhoaHoc = '$courseId'";
    }

    $sql = "SELECT s.*, p.TieuDe, p.GioiHanTu, p.MucDo, t.TenChuDe,
            CASE 
                WHEN s.DiemSo IS NOT NULL THEN 'Đã chấm'
                ELSE 'Chưa chấm'
            END as TrangThaiCham
            FROM writing_submissions s 
            LEFT JOIN writing_prompts p ON s.MaDeBai = p.MaDeBai 
            LEFT JOIN writing_topics t ON p.MaChuDe = t.MaChuDe 
            $where 
            ORDER BY s.ThoiGianNop DESC";

    $submissions = $Database->get_list($sql);

    // Log for debugging
    error_log("My submissions query: " . $sql);
    error_log("Found " . count($submissions) . " submissions for user: " . $taiKhoan);

    echo json_encode($submissions);
}

function getSubmission()
{
    global $Database;

    $id = $_GET['id'] ?? '';
    $taiKhoan = $_SESSION['account'];

    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Missing ID']);
        return;
    }

    $sql = "SELECT s.*, p.TieuDe, p.NoiDungDeBai, p.GioiHanTu, p.MucDo, t.TenChuDe
            FROM writing_submissions s 
            LEFT JOIN writing_prompts p ON s.MaDeBai = p.MaDeBai 
            LEFT JOIN writing_topics t ON p.MaChuDe = t.MaChuDe 
            WHERE s.MaBaiViet = '$id' AND s.TaiKhoan = '$taiKhoan'";

    $submission = $Database->get_row($sql);

    if (!$submission) {
        echo json_encode(['success' => false, 'message' => 'Submission not found']);
        return;
    }

    echo json_encode($submission);
}

function findTopicByLesson()
{
    global $Database;

    $courseId = $_GET['course'] ?? '';
    $lessonId = $_GET['lesson'] ?? '';

    if (!$courseId || !$lessonId) {
        echo json_encode(['success' => false, 'message' => 'Missing parameters']);
        return;
    }

    // Lấy tên bài học
    $lesson = $Database->get_row("SELECT * FROM baihoc WHERE MaBaiHoc = '$lessonId' AND MaKhoaHoc = '$courseId'");

    if (!$lesson) {
        echo json_encode(['success' => false, 'message' => 'Lesson not found']);
        return;
    }

    $lessonName = strtolower($lesson['TenBaiHoc']);

    // Tìm topic có tên khớp
    $topics = $Database->get_list("SELECT * FROM writing_topics WHERE MaKhoaHoc = '$courseId'");

    $matchedTopic = null;
    foreach ($topics as $topic) {
        $topicName = strtolower($topic['TenChuDe']);

        // Kiểm tra tên khớp
        if (strpos($lessonName, $topicName) !== false || strpos($topicName, $lessonName) !== false) {
            $matchedTopic = $topic;
            break;
        }

        // Kiểm tra các từ khóa đặc biệt
        $keywords = [
            'traffic' => ['traffic', 'giao thong'],
            'food' => ['food', 'thuc an', 'an uong'],
            'education' => ['education', 'giao duc', 'hoc tap'],
            'family' => ['family', 'gia dinh'],
            'work' => ['work', 'cong viec', 'lam viec'],
            'hobby' => ['hobby', 'so thich', 'hobbies'],
            'technology' => ['technology', 'cong nghe'],
            'activity' => ['activity', 'hoat dong', 'activities']
        ];

        foreach ($keywords as $key => $variations) {
            foreach ($variations as $variation) {
                if (strpos($lessonName, $variation) !== false && strpos($topicName, $variation) !== false) {
                    $matchedTopic = $topic;
                    break 3;
                }
            }
        }
    }

    if ($matchedTopic) {
        echo json_encode(['success' => true, 'topic' => $matchedTopic]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No matching topic found']);
    }
}
?>