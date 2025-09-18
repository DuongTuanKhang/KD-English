<?php
require_once(__DIR__ . "/../configs/config.php");
require_once(__DIR__ . "/../configs/function.php");

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Cho phép lấy topics và add_prompt mà không cần authentication nghiêm ngặt
if ($action === 'topics_by_course' || $action === 'add_prompt') {
    if ($action === 'topics_by_course') {
        getTopicsByCourse();
    } else {
        addPrompt();
    }
    exit;
}

// Các action khác cần authentication
if (empty($_SESSION['account'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$checkAdmin = $Database->get_row("SELECT * FROM nguoidung WHERE TaiKhoan = '" . $_SESSION['account'] . "' AND MaQuyenHan = 1");
if (!$checkAdmin) {
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
    case 'submissions':
        getSubmissions();
        break;
    case 'topics_by_course':
        getTopicsByCourse();
        break;
    case 'get_submission':
        getSubmission();
        break;
    case 'get_prompt':
        getPrompt();
        break;
    case 'add_topic':
        addTopic();
        break;
    case 'add_prompt':
        addPrompt();
        break;
    case 'update_prompt':
        updatePrompt();
        break;
    case 'grade_submission':
        gradeSubmission();
        break;
    case 'delete_topic':
        deleteTopic();
        break;
    case 'delete_prompt':
        deletePrompt();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function getTopics() {
    global $Database;
    
    $sql = "SELECT t.*, k.TenKhoaHoc,
            (SELECT COUNT(*) FROM writing_prompts p WHERE p.MaChuDe = t.MaChuDe AND p.TrangThai = 1) as SoDeBai
            FROM writing_topics t 
            LEFT JOIN khoahoc k ON t.MaKhoaHoc = k.MaKhoaHoc 
            ORDER BY t.ThoiGianTao DESC";
    
    $topics = $Database->get_list($sql);
    echo json_encode($topics);
}

function getPrompts() {
    global $Database;
    
    $sql = "SELECT p.*, k.TenKhoaHoc as ten_khoa_hoc, 
                   COALESCE(wt.TenChuDe, 'Chưa có chủ đề') as ten_chu_de,
                   p.ThoiGianLamBai as thoi_gian,
                   p.TieuDe as tieu_de, p.GioiHanTu as gioi_han_tu, 
                   p.MucDo as muc_do, p.TrangThai as trang_thai,
                   (SELECT COUNT(*) FROM writing_submissions s WHERE s.MaDeBai = p.MaDeBai) as so_bai_nop
            FROM writing_prompts p 
            LEFT JOIN writing_topics wt ON p.MaChuDe = wt.MaChuDe
            LEFT JOIN khoahoc k ON p.MaKhoaHoc = k.MaKhoaHoc
            ORDER BY p.ThoiGianTao DESC";
    
    $prompts = $Database->get_list($sql);
    echo json_encode($prompts);
}

function getSubmissions() {
    global $Database;
    
    $status = $_GET['status'] ?? '';
    
    // Simple query first
    if ($status == 'ungraded') {
        $sql = "SELECT s.MaBaiViet, s.MaDeBai, s.TaiKhoan, s.TrangThaiCham, s.SoTu, s.ThoiGianNop,
                       p.TieuDe, n.TenHienThi as TenHocVien
                FROM writing_submissions s 
                LEFT JOIN writing_prompts p ON s.MaDeBai = p.MaDeBai 
                LEFT JOIN nguoidung n ON s.TaiKhoan = n.TaiKhoan 
                WHERE s.TrangThaiCham = 'Chưa chấm'
                ORDER BY s.ThoiGianNop DESC";
    } else {
        $sql = "SELECT s.MaBaiViet, s.MaDeBai, s.TaiKhoan, s.TrangThaiCham, s.SoTu, s.ThoiGianNop, s.DiemSo, s.ThoiGianCham,
                       p.TieuDe, n.TenHienThi as TenHocVien
                FROM writing_submissions s 
                LEFT JOIN writing_prompts p ON s.MaDeBai = p.MaDeBai 
                LEFT JOIN nguoidung n ON s.TaiKhoan = n.TaiKhoan 
                WHERE s.TrangThaiCham = 'Đã chấm'
                ORDER BY s.ThoiGianNop DESC";
    }
    
    try {
        $submissions = $Database->get_list($sql);
        echo json_encode($submissions, JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function getTopicsByCourse() {
    global $Database;
    
    $courseId = $_GET['course_id'] ?? '';
    if (!$courseId) {
        echo json_encode([]);
        return;
    }
    
    // Lấy từ bảng baihoc (bài học) với khóa học tương ứng - 8 chủ đề
    $sql = "SELECT MaBaiHoc as MaChuDe, TenBaiHoc as TenChuDe FROM baihoc WHERE MaKhoaHoc = '$courseId' ORDER BY TenBaiHoc";
    $topics = $Database->get_list($sql);
    
    // Nếu không có trong baihoc, thử từ bảng writing_topics
    if (empty($topics)) {
        $sql_writing = "SELECT MaChuDe, TenChuDe FROM writing_topics WHERE MaKhoaHoc = '$courseId' ORDER BY TenChuDe";
        $topics = $Database->get_list($sql_writing);
    }
    
    echo json_encode($topics);
}

function getSubmission() {
    global $Database;
    
    $id = $_GET['id'] ?? '';
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Missing ID']);
        return;
    }
    
    $sql = "SELECT s.*, p.TieuDe, p.NoiDungDeBai, p.GioiHanTu, n.TenHienThi
            FROM writing_submissions s 
            LEFT JOIN writing_prompts p ON s.MaDeBai = p.MaDeBai 
            LEFT JOIN nguoidung n ON s.TaiKhoan = n.TaiKhoan 
            WHERE s.MaBaiViet = '$id'";
    
    $submission = $Database->get_row($sql);
    echo json_encode($submission);
}

function getPrompt() {
    global $Database;
    
    $id = $_GET['id'] ?? '';
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Missing ID']);
        return;
    }
    
    $sql = "SELECT p.*, wt.TenChuDe, k.TenKhoaHoc
            FROM writing_prompts p 
            LEFT JOIN writing_topics wt ON p.MaChuDe = wt.MaChuDe
            LEFT JOIN khoahoc k ON p.MaKhoaHoc = k.MaKhoaHoc
            WHERE p.MaDeBai = '$id'";
    
    $prompt = $Database->get_row($sql);
    
    if (!$prompt) {
        echo json_encode(['success' => false, 'message' => 'Prompt not found']);
        return;
    }
    
    echo json_encode($prompt);
}

function addTopic() {
    global $Database;
    
    $maKhoaHoc = $_POST['maKhoaHoc'] ?? '';
    $tenChuDe = $_POST['tenChuDe'] ?? '';
    $moTa = $_POST['moTa'] ?? '';
    
    if (!$maKhoaHoc || !$tenChuDe) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        return;
    }
    
    // Check if topic already exists
    $existing = $Database->get_row("SELECT * FROM writing_topics WHERE MaKhoaHoc = '$maKhoaHoc' AND TenChuDe = '$tenChuDe'");
    if ($existing) {
        echo json_encode(['success' => false, 'message' => 'Chủ đề này đã tồn tại trong khóa học']);
        return;
    }
    
    $sql = "INSERT INTO writing_topics (MaKhoaHoc, TenChuDe, MoTa) VALUES ('$maKhoaHoc', '$tenChuDe', '$moTa')";
    
    if ($Database->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Topic added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add topic']);
    }
}

function addPrompt() {
    global $Database;
    
    $maChuDe = $_POST['maChuDe'] ?? '';
    $maKhoaHoc = $_POST['maKhoaHoc'] ?? '';
    $tieuDe = $_POST['tieuDe'] ?? '';
    $noiDungDeBai = $_POST['noiDungDeBai'] ?? '';
    $gioiHanTu = $_POST['gioiHanTu'] ?? '';
    $mucDo = $_POST['mucDo'] ?? '';
    $nguoiTao = $_SESSION['account'] ?? 'admin';
    
    if (!$maChuDe || !$maKhoaHoc || !$tieuDe || !$noiDungDeBai || !$gioiHanTu || !$mucDo) {
        echo json_encode(['success' => false, 'message' => 'Thiếu thông tin bắt buộc']);
        return;
    }
    
    // Chỉ sử dụng các cột có trong bảng thực tế
    $sql = "INSERT INTO writing_prompts (MaChuDe, MaKhoaHoc, TieuDe, NoiDungDeBai, GioiHanTu, MucDo, NguoiTao) 
            VALUES ('$maChuDe', '$maKhoaHoc', '$tieuDe', '$noiDungDeBai', '$gioiHanTu', '$mucDo', '$nguoiTao')";
    
    if ($Database->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Đã thêm đề bài thành công']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không thể thêm đề bài']);
    }
}

function updatePrompt() {
    global $Database, $conn;
    
    // Debug: Log tất cả dữ liệu nhận được
    error_log("updatePrompt called with POST data: " . print_r($_POST, true));
    
    $maDeBai = $_POST['maDeBai'] ?? '';
    $maChuDe = $_POST['maChuDe'] ?? '1'; // Default value
    $maKhoaHoc = $_POST['maKhoaHoc'] ?? '1'; // Default value
    $tieuDe = $_POST['tieuDe'] ?? '';
    $noiDungDeBai = $_POST['noiDungDeBai'] ?? '';
    $gioiHanTu = $_POST['gioiHanTu'] ?? '';
    $mucDo = $_POST['mucDo'] ?? '';
    $thoiGianLamBai = $_POST['thoiGianLamBai'] ?? '30'; // Default value
    
    // Kiểm tra các trường bắt buộc
    if (!$maDeBai) {
        echo json_encode(['success' => false, 'message' => 'Thiếu mã đề bài', 'debug' => ['maDeBai' => $maDeBai]]);
        return;
    }
    
    if (!$tieuDe) {
        echo json_encode(['success' => false, 'message' => 'Thiếu tiêu đề', 'debug' => ['tieuDe' => $tieuDe]]);
        return;
    }
    
    if (!$noiDungDeBai) {
        echo json_encode(['success' => false, 'message' => 'Thiếu nội dung đề bài', 'debug' => ['noiDungDeBai' => $noiDungDeBai]]);
        return;
    }
    
    if (!$gioiHanTu) {
        echo json_encode(['success' => false, 'message' => 'Thiếu giới hạn từ', 'debug' => ['gioiHanTu' => $gioiHanTu]]);
        return;
    }
    
    if (!$mucDo) {
        echo json_encode(['success' => false, 'message' => 'Thiếu mức độ', 'debug' => ['mucDo' => $mucDo]]);
        return;
    }
    
    // Escape dữ liệu để tránh SQL injection
    $maDeBai = mysqli_real_escape_string($conn, $maDeBai);
    $maChuDe = mysqli_real_escape_string($conn, $maChuDe);
    $maKhoaHoc = mysqli_real_escape_string($conn, $maKhoaHoc);
    $tieuDe = mysqli_real_escape_string($conn, $tieuDe);
    $noiDungDeBai = mysqli_real_escape_string($conn, $noiDungDeBai);
    $gioiHanTu = mysqli_real_escape_string($conn, $gioiHanTu);
    $mucDo = mysqli_real_escape_string($conn, $mucDo);
    $thoiGianLamBai = mysqli_real_escape_string($conn, $thoiGianLamBai);
    
    $sql = "UPDATE writing_prompts SET 
            MaChuDe = '$maChuDe',
            MaKhoaHoc = '$maKhoaHoc',
            TieuDe = '$tieuDe',
            NoiDungDeBai = '$noiDungDeBai',
            GioiHanTu = '$gioiHanTu',
            MucDo = '$mucDo',
            ThoiGianLamBai = '$thoiGianLamBai'
            WHERE MaDeBai = '$maDeBai'";
    
    error_log("SQL query: " . $sql);
    
    $result = mysqli_query($conn, $sql);
    error_log("Query result: " . ($result ? 'SUCCESS' : 'FAILED'));
    
    if ($result) {
        $affected_rows = mysqli_affected_rows($conn);
        echo json_encode([
            'success' => true, 
            'message' => 'Đã cập nhật đề bài thành công',
            'debug' => [
                'sql' => $sql,
                'affected_rows' => $affected_rows
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Không thể cập nhật đề bài: ' . mysqli_error($conn),
            'debug' => [
                'sql' => $sql,
                'error' => mysqli_error($conn)
            ]
        ]);
    }
}

function gradeSubmission() {
    global $Database;
    
    $maBaiViet = $_POST['maBaiViet'] ?? '';
    $diemNguPhap = $_POST['diemNguPhap'] ?? '';
    $diemMachLac = $_POST['diemMachLac'] ?? '';
    $diemTuVung = $_POST['diemTuVung'] ?? '';
    $diemSo = $_POST['diemSo'] ?? '';
    $nhanXet = $_POST['nhanXet'] ?? '';
    $nguoiCham = $_POST['nguoiCham'] ?? '';
    
    if (!$maBaiViet || !$diemSo || !$nguoiCham) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        return;
    }
    
    // Debug: Log the values being updated
    error_log("Grading submission: MaBaiViet=$maBaiViet, DiemSo=$diemSo, NguoiCham=$nguoiCham");
    
    $sql = "UPDATE writing_submissions SET 
            TrangThaiCham = 'Đã chấm',
            DiemNguPhap = '$diemNguPhap',
            DiemMachLac = '$diemMachLac',
            DiemTuVung = '$diemTuVung',
            DiemSo = '$diemSo',
            NhanXet = '$nhanXet',
            NguoiCham = '$nguoiCham',
            ThoiGianCham = NOW()
            WHERE MaBaiViet = '$maBaiViet'";
    
    error_log("Executing SQL: $sql");
    
    if ($Database->query($sql)) {
        // Verify the update worked
        $check = $Database->get_row("SELECT TrangThaiCham, DiemSo FROM writing_submissions WHERE MaBaiViet = '$maBaiViet'");
        error_log("After update - Status: " . $check['TrangThaiCham'] . ", Score: " . $check['DiemSo']);
        
        echo json_encode(['success' => true, 'message' => 'Graded successfully']);
    } else {
        error_log("Database query failed: " . $Database->connect()->error);
        echo json_encode(['success' => false, 'message' => 'Failed to grade submission']);
    }
}

function deleteTopic() {
    global $Database;
    
    $id = $_POST['id'] ?? '';
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Missing ID']);
        return;
    }
    
    // Check if topic has prompts
    $prompts = $Database->get_row("SELECT COUNT(*) as count FROM writing_prompts WHERE MaChuDe = '$id'");
    if ($prompts['count'] > 0) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete topic with existing prompts']);
        return;
    }
    
    $sql = "DELETE FROM writing_topics WHERE MaChuDe = '$id'";
    
    if ($Database->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Topic deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete topic']);
    }
}

function deletePrompt() {
    global $Database;
    
    $id = $_POST['id'] ?? '';
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Missing ID']);
        return;
    }
    
    // Check if prompt has submissions
    $submissions = $Database->get_row("SELECT COUNT(*) as count FROM writing_submissions WHERE MaDeBai = '$id'");
    if ($submissions['count'] > 0) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete prompt with existing submissions']);
        return;
    }
    
    $sql = "DELETE FROM writing_prompts WHERE MaDeBai = '$id'";
    
    if ($Database->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Prompt deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete prompt']);
    }
}
?>
