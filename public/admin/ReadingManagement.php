<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");

$title = 'Quản lý bài đọc | ' . $Database->site("TenWeb");

// Check admin permission
if (!isset($_SESSION["account"]) || $_SESSION["account"] != "admin") {
    header("Location: " . BASE_URL('Auth/DangNhap'));
    exit();
}

$message = '';
$messageType = '';

// Check for update success message
if (isset($_GET['updated']) && $_GET['updated'] == '1') {
    $message = 'Cập nhật bài đọc thành công!';
    $messageType = 'success';
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add_lesson':
                    $result = handleAddLesson();
                    break;
                case 'edit_lesson':
                    $result = handleEditLesson();
                    break;
                case 'delete_lesson':
                    $result = handleDeleteLesson();
                    break;
                default:
                    $result = ['success' => false, 'message' => 'Hành động không hợp lệ!'];
            }
            
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'danger';
        }
    } catch (Exception $e) {
        $message = 'Có lỗi xảy ra: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// Function to handle adding new lesson
function handleAddLesson() {
    global $Database;
    
    // Validate required fields
    if (empty($_POST['title']) || empty($_POST['content']) || empty($_POST['topic'])) {
        return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc!'];
    }
    
    // Check for duplicate title
    try {
        $existingLesson = $Database->get_list("
            SELECT MaBaiDoc FROM reading_lessons 
            WHERE TieuDe = '" . addslashes($_POST['title']) . "'
        ");
    } catch (Exception $e) {
        $existingLesson = [];
    }
    
    if (!empty($existingLesson)) {
        return ['success' => false, 'message' => 'Tiêu đề này đã tồn tại!'];
    }
    
    // Prepare data for insertion
    $selectedTopic = $_POST['topic'] ?? 'Traffic';
    $courseId = (int)($_POST['course_id'] ?? 1);
    
    // Find MaBaiHoc based on selected topic
    $topicToLessonMap = [
        'Traffic' => 1,
        'Food' => 2, 
        'Education' => 3,
        'Family' => 4,
        'Work' => 5,
        'Hobbie' => 6,
        'Technology' => 7,
        'Activities' => 8
    ];
    
    $maBaiHoc = $topicToLessonMap[$selectedTopic] ?? 1;
    
    $data = [
        'MaBaiHoc' => $maBaiHoc,
        'TieuDe' => $_POST['title'],
        'NoiDungBaiDoc' => $_POST['content'],
        'MucDo' => $_POST['level'] ?? 'Dễ', // MucDo lưu mức độ thực tế
        'ChuDe' => $selectedTopic, // ChuDe lưu chủ đề
        'ThoiGianLam' => (int)($_POST['duration'] ?? 15),
        'MaKhoaHoc' => $courseId,
        'ThuTu' => 1, // Auto-increment based on existing lessons
        'TrangThai' => 'active',
        'ThoiGianTao' => date('Y-m-d H:i:s')
    ];
    
    if ($Database->insert('reading_lessons', $data)) {
        // Get the newly inserted lesson ID
        $newLessonQuery = $Database->get_list("
            SELECT MaBaiDoc FROM reading_lessons 
            WHERE TieuDe = '" . addslashes($_POST['title']) . "' 
            ORDER BY ThoiGianTao DESC 
            LIMIT 1
        ");
        
        if (!empty($newLessonQuery)) {
            $newLessonId = $newLessonQuery[0]['MaBaiDoc'];
            
            // Save questions if any
            if (isset($_POST['questions']) && is_array($_POST['questions'])) {
                foreach ($_POST['questions'] as $index => $question) {
                    if (!empty($question['text']) && !empty($question['answer_a']) && !empty($question['correct'])) {
                        $questionData = [
                            'MaBaiDoc' => $newLessonId,
                            'CauHoi' => $question['text'],
                            'DapAnA' => $question['answer_a'],
                            'DapAnB' => $question['answer_b'] ?? '',
                            'DapAnC' => $question['answer_c'] ?? '',
                            'DapAnD' => $question['answer_d'] ?? '',
                            'DapAnDung' => $question['correct'],
                            'GiaiThich' => $question['explanation'] ?? '',
                            'ThuTu' => $index + 1
                        ];
                        $Database->insert('reading_questions', $questionData);
                    }
                }
            }
        }
        
        return ['success' => true, 'message' => 'Thêm bài đọc thành công!'];
    } else {
        return ['success' => false, 'message' => 'Có lỗi xảy ra khi thêm bài đọc!'];
    }
}

// Function to handle editing lesson
function handleEditLesson() {
    global $Database;
    
    if (empty($_POST['lesson_id']) || empty($_POST['title']) || empty($_POST['content'])) {
        return ['success' => false, 'message' => 'Dữ liệu không hợp lệ!'];
    }
    
    $selectedTopic = $_POST['topic'] ?? 'Traffic';
    $courseId = (int)($_POST['course_id'] ?? 1);
    
    // Find MaBaiHoc based on selected topic
    $topicToLessonMap = [
        'Traffic' => 1,
        'Food' => 2, 
        'Education' => 3,
        'Family' => 4,
        'Work' => 5,
        'Hobbie' => 6,
        'Technology' => 7,
        'Activities' => 8
    ];
    
    $maBaiHoc = $topicToLessonMap[$selectedTopic] ?? 1;
    
    $data = [
        'MaBaiHoc' => $maBaiHoc,
        'TieuDe' => $_POST['title'],
        'NoiDungBaiDoc' => $_POST['content'],
        'MucDo' => $_POST['level'] ?? 'Dễ', // MucDo lưu mức độ thực tế
        'ChuDe' => $selectedTopic, // ChuDe lưu chủ đề
        'ThoiGianLam' => (int)($_POST['duration'] ?? 15),
        'MaKhoaHoc' => $courseId,
        'ThuTu' => 1 // Keep existing order for edit
    ];
    
    if ($Database->update('reading_lessons', $data, "MaBaiDoc = " . (int)$_POST['lesson_id'])) {
        $lessonId = (int)$_POST['lesson_id'];
        
        // Delete existing questions first
        $Database->remove('reading_questions', "MaBaiDoc = $lessonId");
        
        // Save updated questions if any
        if (isset($_POST['questions']) && is_array($_POST['questions'])) {
            foreach ($_POST['questions'] as $index => $question) {
                if (!empty($question['text']) && !empty($question['answer_a']) && !empty($question['correct'])) {
                    $questionData = [
                        'MaBaiDoc' => $lessonId,
                        'CauHoi' => $question['text'],
                        'DapAnA' => $question['answer_a'],
                        'DapAnB' => $question['answer_b'] ?? '',
                        'DapAnC' => $question['answer_c'] ?? '',
                        'DapAnD' => $question['answer_d'] ?? '',
                        'DapAnDung' => $question['correct'],
                        'GiaiThich' => $question['explanation'] ?? '',
                        'ThuTu' => $index + 1
                    ];
                    $Database->insert('reading_questions', $questionData);
                }
            }
        }
        
        return ['success' => true, 'message' => 'Cập nhật bài đọc thành công!'];
    } else {
        return ['success' => false, 'message' => 'Có lỗi xảy ra khi cập nhật!'];
    }
}

// Function to handle deleting lesson
function handleDeleteLesson() {
    global $Database;
    
    if (empty($_POST['lesson_id'])) {
        return ['success' => false, 'message' => 'ID bài đọc không hợp lệ!'];
    }
    
    $lessonId = (int)$_POST['lesson_id'];
    
    // Delete related questions first
    $Database->remove('reading_questions', "MaBaiDoc = $lessonId");
    
    // Delete the lesson
    if ($Database->remove('reading_lessons', "MaBaiDoc = $lessonId")) {
        return ['success' => true, 'message' => 'Xóa bài đọc thành công!'];
    } else {
        return ['success' => false, 'message' => 'Có lỗi xảy ra khi xóa bài đọc!'];
    }
}

// Load data
try {
    $lessons = $Database->get_list("
        SELECT rl.*, kh.TenKhoaHoc,
               (SELECT COUNT(*) FROM reading_questions WHERE MaBaiDoc = rl.MaBaiDoc) as SoLuongCauHoi
        FROM reading_lessons rl
        LEFT JOIN khoahoc kh ON rl.MaKhoaHoc = kh.MaKhoaHoc
        ORDER BY rl.ThuTu ASC, rl.MaBaiDoc DESC
    ");
} catch (Exception $e) {
    $lessons = [];
}

try {
    $courses = $Database->get_list("SELECT MaKhoaHoc, TenKhoaHoc FROM khoahoc WHERE TrangThaiKhoaHoc = 1");
} catch (Exception $e) {
    $courses = [['MaKhoaHoc' => 1, 'TenKhoaHoc' => 'Khóa học mặc định']];
}

// Default topics array (will be replaced with AJAX loading)
$topics = [
    ['ChuDe' => 'Traffic', 'MaKhoaHoc' => 1],
    ['ChuDe' => 'Food', 'MaKhoaHoc' => 1],
    ['ChuDe' => 'Education', 'MaKhoaHoc' => 1],
    ['ChuDe' => 'Family', 'MaKhoaHoc' => 1],
    ['ChuDe' => 'Work', 'MaKhoaHoc' => 1],
    ['ChuDe' => 'Hobbie', 'MaKhoaHoc' => 1],
    ['ChuDe' => 'Technology', 'MaKhoaHoc' => 1],
    ['ChuDe' => 'Activities', 'MaKhoaHoc' => 1]
];

// Get lesson for editing if requested
$editLesson = null;
$editSuccessful = false;

// Check if we just had a successful edit to avoid reloading edit mode
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_lesson' && isset($result) && $result['success']) {
    $editSuccessful = true;
}

if (!$editSuccessful && isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    try {
        $editLessonData = $Database->get_list("
            SELECT * FROM reading_lessons WHERE MaBaiDoc = " . (int)$_GET['edit']
        );
        if (!empty($editLessonData)) {
            $editLesson = $editLessonData[0];
        }
    } catch (Exception $e) {
        $editLesson = null;
    }
}

require_once(__DIR__ . "/Header.php");
require_once(__DIR__ . "/Sidebar.php");
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">📚 Quản lý bài đọc</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/webhocngoaingu/public/admin/home.php">Admin</a></li>
                        <li class="breadcrumb-item active">Quản lý bài đọc</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Add/Edit Form -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-<?= $editLesson ? 'edit' : 'plus' ?>"></i> 
                        <?= $editLesson ? 'Chỉnh sửa bài đọc' : 'Thêm bài đọc mới' ?>
                    </h3>
                    <?php if ($editLesson): ?>
                        <div class="card-tools">
                            <a href="/webhocngoaingu/public/admin/ReadingManagement.php" class="btn btn-sm btn-secondary">
                                <i class="fas fa-times"></i> Hủy chỉnh sửa
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <form method="POST" action="" id="lessonForm">
                    <input type="hidden" name="action" value="<?= $editLesson ? 'edit_lesson' : 'add_lesson' ?>">
                    <?php if ($editLesson): ?>
                        <input type="hidden" name="lesson_id" value="<?= $editLesson['MaBaiDoc'] ?>">
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="title">Tiêu đề bài đọc <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="title" name="title" 
                                           value="<?= $editLesson ? htmlspecialchars($editLesson['TieuDe']) : '' ?>" 
                                           required>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="topic">Chủ đề <span class="text-danger">*</span></label>
                                    <select class="form-control" id="topic" name="topic" required>
                                        <option value="">Chọn chủ đề...</option>
                                        <option value="Traffic" <?= ($editLesson && $editLesson['MucDo'] == 'Traffic') ? 'selected' : '' ?>>Traffic</option>
                                        <option value="Food" <?= ($editLesson && $editLesson['MucDo'] == 'Food') ? 'selected' : '' ?>>Food</option>
                                        <option value="Education" <?= ($editLesson && $editLesson['MucDo'] == 'Education') ? 'selected' : '' ?>>Education</option>
                                        <option value="Family" <?= ($editLesson && $editLesson['MucDo'] == 'Family') ? 'selected' : '' ?>>Family</option>
                                        <option value="Work" <?= ($editLesson && $editLesson['MucDo'] == 'Work') ? 'selected' : '' ?>>Work</option>
                                        <option value="Hobbie" <?= ($editLesson && $editLesson['MucDo'] == 'Hobbie') ? 'selected' : '' ?>>Hobbie</option>
                                        <option value="Technology" <?= ($editLesson && $editLesson['MucDo'] == 'Technology') ? 'selected' : '' ?>>Technology</option>
                                        <option value="Activities" <?= ($editLesson && $editLesson['MucDo'] == 'Activities') ? 'selected' : '' ?>>Activities</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="duration">Thời gian (phút)</label>
                                    <input type="number" class="form-control" id="duration" name="duration" 
                                           value="<?= $editLesson ? $editLesson['ThoiGianLam'] : '15' ?>" 
                                           min="1" max="180">
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="course_id">Khóa học</label>
                                    <select class="form-control" id="course_id" name="course_id">
                                        <?php foreach($courses as $course): ?>
                                            <option value="<?= $course['MaKhoaHoc'] ?>" 
                                                    <?= ($editLesson && $editLesson['MaKhoaHoc'] == $course['MaKhoaHoc']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($course['TenKhoaHoc']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="content">Nội dung bài đọc <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="content" name="content" rows="8" required 
                                      placeholder="Nhập nội dung bài đọc ở đây..."><?= $editLesson ? htmlspecialchars($editLesson['NoiDungBaiDoc']) : '' ?></textarea>
                        </div>

                        <!-- Dynamic Questions Section -->
                        <div class="mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="text-primary mb-0">
                                    <i class="fas fa-question-circle"></i> Câu hỏi cho bài đọc
                                </h5>
                                <button type="button" class="btn btn-success btn-sm" onclick="addQuestion()">
                                    <i class="fas fa-plus"></i> Thêm câu hỏi mới
                                </button>
                            </div>
                            
                            <div id="questionsContainer">
                                <?php if ($editLesson): ?>
                                    <?php
                                    // Load existing questions for editing
                                    try {
                                        $existingQuestions = $Database->get_list("
                                            SELECT * FROM reading_questions 
                                            WHERE MaBaiDoc = " . (int)$editLesson['MaBaiDoc'] . "
                                            ORDER BY ThuTu ASC
                                        ");
                                    } catch (Exception $e) {
                                        $existingQuestions = [];
                                    }
                                    
                                    foreach ($existingQuestions as $index => $question):
                                    ?>
                                    <div class="card mb-3 border-success" id="question_<?= $index + 1 ?>">
                                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0"><i class="fas fa-question-circle text-primary"></i> Câu hỏi <?= $index + 1 ?></h6>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="removeQuestion(<?= $index + 1 ?>)">
                                                <i class="fas fa-trash"></i> Xóa
                                            </button>
                                        </div>
                                        <div class="card-body">
                                            <input type="hidden" name="questions[<?= $index ?>][id]" value="<?= $question['MaCauHoi'] ?>">
                                            
                                            <div class="form-group">
                                                <label><strong>Câu hỏi <span class="text-danger">*</span></strong></label>
                                                <textarea class="form-control" name="questions[<?= $index ?>][text]" rows="3" 
                                                          placeholder="Nhập câu hỏi..." required><?= htmlspecialchars($question['CauHoi']) ?></textarea>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label><strong>Đáp án A <span class="text-danger">*</span></strong></label>
                                                        <input type="text" class="form-control" name="questions[<?= $index ?>][answer_a]" 
                                                               placeholder="Nhập đáp án A" value="<?= htmlspecialchars($question['DapAnA']) ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label><strong>Đáp án B</strong></label>
                                                        <input type="text" class="form-control" name="questions[<?= $index ?>][answer_b]" 
                                                               placeholder="Nhập đáp án B" value="<?= htmlspecialchars($question['DapAnB']) ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label><strong>Đáp án C</strong></label>
                                                        <input type="text" class="form-control" name="questions[<?= $index ?>][answer_c]" 
                                                               placeholder="Nhập đáp án C" value="<?= htmlspecialchars($question['DapAnC']) ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label><strong>Đáp án D</strong></label>
                                                        <input type="text" class="form-control" name="questions[<?= $index ?>][answer_d]" 
                                                               placeholder="Nhập đáp án D" value="<?= htmlspecialchars($question['DapAnD']) ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label><strong>Đáp án đúng <span class="text-danger">*</span></strong></label>
                                                        <select class="form-control" name="questions[<?= $index ?>][correct]" required>
                                                            <option value="">Chọn đáp án đúng</option>
                                                            <option value="A" <?= $question['DapAnDung'] == 'A' ? 'selected' : '' ?>>A</option>
                                                            <option value="B" <?= $question['DapAnDung'] == 'B' ? 'selected' : '' ?>>B</option>
                                                            <option value="C" <?= $question['DapAnDung'] == 'C' ? 'selected' : '' ?>>C</option>
                                                            <option value="D" <?= $question['DapAnDung'] == 'D' ? 'selected' : '' ?>>D</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label><strong>Giải thích</strong></label>
                                                        <textarea class="form-control" name="questions[<?= $index ?>][explanation]" rows="2" 
                                                                  placeholder="Nhập giải thích (tùy chọn)"><?= htmlspecialchars($question['GiaiThich']) ?></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <!-- New questions will be added here dynamically -->
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-<?= $editLesson ? 'warning' : 'success' ?> btn-lg">
                            <i class="fas fa-<?= $editLesson ? 'save' : 'plus' ?>"></i>
                            <?= $editLesson ? 'Cập nhật bài đọc' : 'Thêm bài đọc' ?>
                        </button>
                        
                        <?php if ($editLesson): ?>
                            <a href="/webhocngoaingu/public/admin/ReadingManagement.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Lessons List -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i> Danh sách bài đọc 
                        <span class="badge badge-info"><?= count($lessons) ?> bài</span>
                    </h3>
                </div>
                
                <div class="card-body p-0">
                    <?php if (empty($lessons)): ?>
                        <div class="text-center p-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Chưa có bài đọc nào</h5>
                            <p class="text-muted">Hãy thêm bài đọc đầu tiên của bạn!</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th width="5%">ID</th>
                                        <th width="30%">Tiêu đề</th>
                                        <th width="15%">Khóa học</th>
                                        <th width="12%">Chủ đề</th>
                                        <th width="10%">Thời gian</th>
                                        <th width="8%">Câu hỏi</th>
                                        <th width="15%">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($lessons as $lesson): ?>
                                    <tr>
                                        <td><strong><?= $lesson['MaBaiDoc'] ?></strong></td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($lesson['TieuDe']) ?>">
                                                <?= htmlspecialchars($lesson['TieuDe']) ?>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($lesson['TenKhoaHoc'] ?? 'Chưa xác định') ?></td>
                                        <td>
                                            <span class="badge badge-info">
                                                <?= htmlspecialchars($lesson['ChuDe'] ?? 'Traffic') ?>
                                            </span>
                                        </td>
                                        <td><?= $lesson['ThoiGianLam'] ?> phút</td>
                                        <td>
                                            <span class="badge badge-primary">
                                                <?= $lesson['SoLuongCauHoi'] ?> câu
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="?edit=<?= $lesson['MaBaiDoc'] ?>" 
                                                   class="btn btn-outline-primary" 
                                                   title="Chỉnh sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-outline-danger" 
                                                        onclick="deleteLesson(<?= $lesson['MaBaiDoc'] ?>, '<?= htmlspecialchars($lesson['TieuDe']) ?>')"
                                                        title="Xóa">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
<?php if ($editLesson): ?>
    <?php
    // Count existing questions for edit mode
    try {
        $existingQuestionsCount = $Database->get_list("
            SELECT COUNT(*) as count FROM reading_questions 
            WHERE MaBaiDoc = " . (int)$editLesson['MaBaiDoc']
        );
        $questionCount = $existingQuestionsCount[0]['count'] ?? 0;
    } catch (Exception $e) {
        $questionCount = 0;
    }
    ?>
    let questionCounter = <?= $questionCount ?>;
<?php else: ?>
    let questionCounter = 0;
<?php endif; ?>

function addQuestion() {
    questionCounter++;
    const container = document.getElementById('questionsContainer');
    
    const questionHtml = `
        <div class="card mb-3 border-success" id="question_${questionCounter}">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-question-circle text-primary"></i> Câu hỏi ${questionCounter}</h6>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeQuestion(${questionCounter})">
                    <i class="fas fa-trash"></i> Xóa
                </button>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label><strong>Câu hỏi <span class="text-danger">*</span></strong></label>
                    <textarea class="form-control" name="questions[${questionCounter-1}][text]" rows="3" 
                              placeholder="Nhập câu hỏi..." required></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Đáp án A <span class="text-danger">*</span></strong></label>
                            <input type="text" class="form-control" name="questions[${questionCounter-1}][answer_a]" 
                                   placeholder="Nhập đáp án A" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Đáp án B</strong></label>
                            <input type="text" class="form-control" name="questions[${questionCounter-1}][answer_b]" 
                                   placeholder="Nhập đáp án B">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Đáp án C</strong></label>
                            <input type="text" class="form-control" name="questions[${questionCounter-1}][answer_c]" 
                                   placeholder="Nhập đáp án C">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Đáp án D</strong></label>
                            <input type="text" class="form-control" name="questions[${questionCounter-1}][answer_d]" 
                                   placeholder="Nhập đáp án D">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Đáp án đúng <span class="text-danger">*</span></strong></label>
                            <select class="form-control" name="questions[${questionCounter-1}][correct]" required>
                                <option value="">Chọn đáp án đúng</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Giải thích</strong></label>
                            <textarea class="form-control" name="questions[${questionCounter-1}][explanation]" rows="2" 
                                      placeholder="Giải thích tại sao đáp án này đúng..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', questionHtml);
    
    // Scroll to new question
    setTimeout(() => {
        document.getElementById(`question_${questionCounter}`).scrollIntoView({ 
            behavior: 'smooth', 
            block: 'center' 
        });
    }, 100);
}

function removeQuestion(id) {
    if (confirm('Bạn có chắc chắn muốn xóa câu hỏi này?')) {
        const questionElement = document.getElementById(`question_${id}`);
        if (questionElement) {
            questionElement.remove();
        }
    }
}

function deleteLesson(id, title) {
    if (confirm('Bạn có chắc chắn muốn xóa bài đọc "' + title + '"?\n\nHành động này không thể hoàn tác!')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_lesson">
            <input type="hidden" name="lesson_id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing...');
    
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            $(alert).fadeOut();
        }, 5000);
    });
    
    // Wait a bit for elements to be fully rendered
    setTimeout(function() {
        // Load topics when course changes
        const courseSelect = document.getElementById('course_id');
        const topicSelect = document.getElementById('topic');
        
        console.log('Checking elements after delay...');
        console.log('courseSelect found:', !!courseSelect);
        console.log('topicSelect found:', !!topicSelect);
        
        if (courseSelect && topicSelect) {
            console.log('Setting up course change listener');
            
            // Force load topics for course 1 (Tiếng Anh)
            console.log('Force loading topics for course 1');
            loadTopicsForCourse('1');
            
            courseSelect.addEventListener('change', function() {
                console.log('Course changed to:', this.value);
                if (this.value) {
                    loadTopicsForCourse(this.value);
                }
            });
        } else {
            console.log('Elements not found, will try to add default topics');
            // If elements not found, try to add default topics directly
            setTimeout(function() {
                const topicSelect2 = document.getElementById('topic');
                if (topicSelect2) {
                    addDefaultTopics(topicSelect2);
                }
            }, 1000);
        }
    }, 500); // Wait 500ms for DOM to stabilize
    
    // Form validation
    const form = document.getElementById('lessonForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const content = document.getElementById('content').value.trim();
            const topic = document.getElementById('topic').value;
            
            if (!title || !content || !topic) {
                e.preventDefault();
                alert('Vui lòng điền đầy đủ thông tin bắt buộc!');
                return false;
            }
        });
    }
});

function loadTopicsForCourse(courseId) {
    console.log('loadTopicsForCourse called with courseId:', courseId);
    
    const topicSelect = document.getElementById('topic');
    if (!topicSelect) {
        console.log('Topic select not found');
        return;
    }
    
    if (!courseId) {
        console.log('No courseId provided');
        return;
    }
    
    // Clear current options and show loading
    topicSelect.innerHTML = '<option value="">Đang tải chủ đề...</option>';
    topicSelect.disabled = true;
    
    console.log('Making fetch request to get_topics.php');
    
    // Make AJAX request to get topics for this course
    fetch('./get_topics.php?course_id=' + courseId)
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            
            if (!response.ok) {
                throw new Error('HTTP ' + response.status + ': ' + response.statusText);
            }
            return response.text(); // Get as text first to check
        })
        .then(text => {
            console.log('Raw response text:', text);
            
            try {
                const data = JSON.parse(text);
                console.log('Parsed data:', data);
                
                // Clear loading message
                topicSelect.innerHTML = '<option value="">Chọn chủ đề...</option>';
                topicSelect.disabled = false;
                
                if (data.error) {
                    console.error('API returned error:', data.error);
                    addDefaultTopics(topicSelect);
                    return;
                }
                
                // Add topics to select
                if (data && Array.isArray(data) && data.length > 0) {
                    console.log('Adding topics to select, count:', data.length);
                    data.forEach(function(topicData, index) {
                        console.log('Adding topic ' + index + ':', topicData);
                        const option = document.createElement('option');
                        option.value = topicData.ChuDe;
                        option.textContent = topicData.ChuDe;
                        topicSelect.appendChild(option);
                    });
                } else {
                    console.log('No valid topics array, using default');
                    addDefaultTopics(topicSelect);
                }
                
                // If editing, set the selected topic
                const editTopic = '<?= $editLesson ? $editLesson['MucDo'] : '' ?>';
                if (editTopic) {
                    console.log('Setting edit topic:', editTopic);
                    topicSelect.value = editTopic;
                }
                
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                console.error('Raw text that failed to parse:', text);
                addDefaultTopics(topicSelect);
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            addDefaultTopics(topicSelect);
        });
}

function addDefaultTopics(topicSelect) {
    console.log('Adding default topics');
    topicSelect.innerHTML = '<option value="">Chọn chủ đề...</option>';
    const defaultTopics = ['Traffic', 'Food', 'Education', 'Family', 'Work', 'Hobbie', 'Technology', 'Activities'];
    
    defaultTopics.forEach(function(topic) {
        const option = document.createElement('option');
        option.value = topic;
        option.textContent = topic;
        topicSelect.appendChild(option);
    });
    
    topicSelect.disabled = false;
}
</script>

<style>
/* CSS Fix for VS Code warnings - Font Awesome and Bootstrap classes */
.fa-edit, .fa-plus, .fa-times, .fa-question-circle, .fa-trash, .fa-save, 
.fa-arrow-left, .fa-list, .fa-inbox, .fa-3x, .edit, .plus, .save, .fa- {
    /* These classes are defined by Font Awesome library */
}

.alert-, .btn-, .alert-success, .alert-danger, .alert-warning, .alert-info,
.btn-success, .btn-warning, .btn-danger, .btn-primary, .btn-secondary {
    /* These classes are defined by Bootstrap library */
}

.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.8em;
}

.btn-group-sm > .btn {
    padding: 0.25rem 0.5rem;
}

.card-primary .card-header {
    background: linear-gradient(45deg, #007bff, #0056b3);
}

.content-wrapper {
    background-color: #f8f9fa;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.1);
}

.alert {
    border-radius: 8px;
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

#questionsContainer .card {
    transition: all 0.3s ease;
}

#questionsContainer .card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}
</style>

<?php
function getDifficultyColor($difficulty) {
    switch ($difficulty) {
        case 'Dễ': return 'success';
        case 'Trung bình': return 'warning';
        case 'Khó': return 'danger';
        default: return 'secondary';
    }
}

require_once(__DIR__ . "/Footer.php");
?>
