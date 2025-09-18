<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");
require_once(__DIR__ . "/../../class/Database.php");

// Initialize Database if not exists
if (!isset($Database)) {
    $Database = new Database();
}

// Check admin permission
if (!isset($_SESSION["account"]) || $_SESSION["account"] != "admin") {
    redirect(BASE_URL("Auth/DangNhap"));
}

$title = 'Quản lý Grammar Quiz | ' . $Database->site("TenWeb");

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_topic':
                $result = handleAddTopic();
                break;
            case 'edit_topic':
                $result = handleEditTopic();
                break;
            case 'delete_topic':
                $result = handleDeleteTopic();
                break;
            case 'add_question':
                $result = handleAddQuestion();
                break;
            case 'edit_question':
                $result = handleEditQuestion();
                break;
            case 'delete_question':
                $result = handleDeleteQuestion();
                break;
        }
        
        if (isset($result)) {
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
        }
    }
}

// Function to handle adding topic
function handleAddTopic() {
    global $Database;
    
    if (empty($_POST['topic_name']) || empty($_POST['topic_name_eng']) || empty($_POST['course_id']) || empty($_POST['lesson_id'])) {
        return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin!'];
    }
    
    // Check if this grammar topic already exists for this lesson
    $existing = $Database->get_row("
        SELECT * FROM grammar_topics 
        WHERE TenChuDe = '" . trim($_POST['topic_name']) . "' 
        AND MaKhoaHoc = " . (int)$_POST['course_id'] . "
        AND MaBaiHoc = " . (int)$_POST['lesson_id'] . "
        AND TrangThai = 1
    ");
    
    if ($existing) {
        return ['success' => false, 'message' => 'Chủ đề ngữ pháp này đã tồn tại trong bài học này!'];
    }
    
    $data = [
        'MaKhoaHoc' => (int)$_POST['course_id'],
        'MaBaiHoc' => (int)$_POST['lesson_id'],
        'TenChuDe' => trim($_POST['topic_name']),
        'TenChuDeEng' => trim($_POST['topic_name_eng']),
        'MoTa' => trim($_POST['description'] ?? ''),
        'CapDo' => $_POST['level'] ?? 'Beginner',
        'ThuTu' => (int)($_POST['order'] ?? 0)
    ];
    
    try {
        if ($Database->insert('grammar_topics', $data)) {
            return ['success' => true, 'message' => 'Thêm chủ đề thành công!'];
        } else {
            return ['success' => false, 'message' => 'Có lỗi xảy ra khi thêm chủ đề!'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Lỗi database: ' . $e->getMessage()];
    }
}

// Function to handle editing topic
function handleEditTopic() {
    global $Database;
    
    if (empty($_POST['topic_id']) || empty($_POST['topic_name']) || empty($_POST['topic_name_eng']) || empty($_POST['course_id']) || empty($_POST['lesson_id'])) {
        return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin!'];
    }
    
    $data = [
        'MaKhoaHoc' => (int)$_POST['course_id'],
        'MaBaiHoc' => (int)$_POST['lesson_id'],
        'TenChuDe' => trim($_POST['topic_name']),
        'TenChuDeEng' => trim($_POST['topic_name_eng']),
        'MoTa' => trim($_POST['description'] ?? ''),
        'CapDo' => $_POST['level'] ?? 'Beginner',
        'ThuTu' => (int)($_POST['order'] ?? 0)
    ];
    
    if ($Database->update('grammar_topics', $data, "MaChuDe = " . (int)$_POST['topic_id'])) {
        return ['success' => true, 'message' => 'Cập nhật chủ đề thành công!'];
    } else {
        return ['success' => false, 'message' => 'Có lỗi xảy ra khi cập nhật!'];
    }
}

// Function to handle deleting topic
function handleDeleteTopic() {
    global $Database;
    
    if (empty($_POST['topic_id'])) {
        return ['success' => false, 'message' => 'ID chủ đề không hợp lệ!'];
    }
    
    $topicId = (int)$_POST['topic_id'];
    
    // Check if topic has questions
    $questionCount = $Database->get_row("SELECT COUNT(*) as count FROM grammar_questions WHERE MaChuDe = $topicId")['count'];
    
    if ($questionCount > 0) {
        return ['success' => false, 'message' => 'Không thể xóa chủ đề này vì đã có câu hỏi!'];
    }
    
    if ($Database->update('grammar_topics', ['TrangThai' => 0], "MaChuDe = $topicId")) {
        return ['success' => true, 'message' => 'Xóa chủ đề thành công!'];
    } else {
        return ['success' => false, 'message' => 'Có lỗi xảy ra khi xóa!'];
    }
}

// Function to handle adding question
function handleAddQuestion() {
    global $Database;
    
    if (empty($_POST['topic_id']) || empty($_POST['question_text'])) {
        return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin!'];
    }
    
    $data = [
        'MaChuDe' => (int)$_POST['topic_id'],
        'CauHoi' => trim($_POST['question_text']),
        'DapAnA' => trim($_POST['option_a']),
        'DapAnB' => trim($_POST['option_b']),
        'DapAnC' => trim($_POST['option_c']),
        'DapAnD' => trim($_POST['option_d']),
        'DapAnDung' => $_POST['correct_answer'],
        'GiaiThich' => trim($_POST['explanation'] ?? '')
    ];
    
    if ($Database->insert('grammar_questions', $data)) {
        return ['success' => true, 'message' => 'Thêm câu hỏi thành công!'];
    } else {
        return ['success' => false, 'message' => 'Có lỗi xảy ra khi thêm câu hỏi!'];
    }
}

// Function to handle editing question
function handleEditQuestion() {
    global $Database;
    
    if (empty($_POST['question_id']) || empty($_POST['question_text'])) {
        return ['success' => false, 'message' => 'Thông tin không hợp lệ!'];
    }
    
    $data = [
        'CauHoi' => trim($_POST['question_text']),
        'DapAnA' => trim($_POST['option_a']),
        'DapAnB' => trim($_POST['option_b']),
        'DapAnC' => trim($_POST['option_c']),
        'DapAnD' => trim($_POST['option_d']),
        'DapAnDung' => $_POST['correct_answer'],
        'GiaiThich' => trim($_POST['explanation'] ?? '')
    ];
    
    if ($Database->update('grammar_questions', $data, "MaCauHoi = " . (int)$_POST['question_id'])) {
        return ['success' => true, 'message' => 'Cập nhật câu hỏi thành công!'];
    } else {
        return ['success' => false, 'message' => 'Có lỗi xảy ra khi cập nhật!'];
    }
}

// Function to handle deleting question
function handleDeleteQuestion() {
    global $Database;
    
    if (empty($_POST['question_id'])) {
        return ['success' => false, 'message' => 'ID câu hỏi không hợp lệ!'];
    }
    
    $questionId = (int)$_POST['question_id'];
    
    if ($Database->remove('grammar_questions', "MaCauHoi = $questionId")) {
        return ['success' => true, 'message' => 'Xóa câu hỏi thành công!'];
    } else {
        return ['success' => false, 'message' => 'Có lỗi xảy ra khi xóa câu hỏi!'];
    }
}

// Load data
try {
    // Get courses using API approach for consistency
    $courses = $Database->get_list("
        SELECT MaKhoaHoc, TenKhoaHoc
        FROM khoahoc 
        WHERE TrangThaiKhoaHoc = 1
        ORDER BY TenKhoaHoc ASC
    ");
    
    // Get topics with course and lesson info
    $topics = $Database->get_list("
        SELECT gt.*,
               k.TenKhoaHoc,
               bh.TenBaiHoc,
               (SELECT COUNT(*) FROM grammar_questions WHERE MaChuDe = gt.MaChuDe AND TrangThai = 1) as SoLuongCauHoi
        FROM grammar_topics gt 
        LEFT JOIN khoahoc k ON gt.MaKhoaHoc = k.MaKhoaHoc
        LEFT JOIN baihoc bh ON gt.MaBaiHoc = bh.MaBaiHoc AND gt.MaKhoaHoc = bh.MaKhoaHoc
        WHERE gt.TrangThai = 1
        ORDER BY k.TenKhoaHoc ASC, bh.TenBaiHoc ASC, gt.ThuTu ASC
    ");
} catch (Exception $e) {
    $topics = [];
    $courses = [];
    error_log("Grammar topics query error: " . $e->getMessage());
}

// Get topic for editing if specified
$editTopic = null;
if (isset($_GET['edit_topic'])) {
    $editTopic = $Database->get_row("SELECT * FROM grammar_topics WHERE MaChuDe = " . (int)$_GET['edit_topic']);
}

// Get questions for selected topic
$selectedTopicId = $_GET['topic_id'] ?? null;
$questions = [];
if ($selectedTopicId) {
    $questions = $Database->get_list("
        SELECT * FROM grammar_questions 
        WHERE MaChuDe = " . (int)$selectedTopicId . " AND TrangThai = 1 
        ORDER BY MaCauHoi ASC
    ");
}

// Get question for editing if specified
$editQuestion = null;
if (isset($_GET['edit_question'])) {
    $editQuestion = $Database->get_row("SELECT * FROM grammar_questions WHERE MaCauHoi = " . (int)$_GET['edit_question']);
}

include_once(__DIR__ . '/Header.php');
include_once(__DIR__ . '/Sidebar.php');
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Grammar Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL('admin/') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Grammar Management</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType == 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
                    <?= $message ?>
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Topics List -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list text-primary"></i> Danh sách chủ đề ngữ pháp
                            </h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($topics)): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th width="50">#</th>
                                                <th>Khóa học</th>
                                                <th>Bài học</th>
                                                <th>Tên chủ đề</th>
                                                <th>Tên tiếng Anh</th>
                                                <th>Cấp độ</th>
                                                <th width="120">Số câu hỏi</th>
                                                <th width="120">Hành động</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($topics as $index => $topic): ?>
                                                <tr>
                                                    <td><?= $index + 1 ?></td>
                                                    <td>
                                                        <span class="badge badge-primary"><?= htmlspecialchars($topic['TenKhoaHoc'] ?? 'Chưa phân loại') ?></span>
                                                    </td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($topic['TenBaiHoc'] ?? 'Chưa phân loại') ?></strong>
                                                    </td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($topic['TenChuDe']) ?></strong>
                                                        <?php if ($topic['MoTa']): ?>
                                                            <br><small class="text-muted"><?= htmlspecialchars($topic['MoTa']) ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($topic['TenChuDeEng']) ?></td>
                                                    <td>
                                                        <span class="badge badge-<?= $topic['CapDo'] == 'Beginner' ? 'success' : ($topic['CapDo'] == 'Intermediate' ? 'warning' : 'danger') ?>">
                                                            <?= $topic['CapDo'] ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="?topic_id=<?= $topic['MaChuDe'] ?>" class="badge badge-info">
                                                            <?= $topic['SoLuongCauHoi'] ?> câu hỏi
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="?edit_topic=<?= $topic['MaChuDe'] ?>" 
                                                               class="btn btn-outline-primary" title="Chỉnh sửa">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <button type="button" 
                                                                    class="btn btn-outline-danger" 
                                                                    onclick="deleteTopic(<?= $topic['MaChuDe'] ?>, '<?= htmlspecialchars($topic['TenChuDe']) ?>')"
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
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Chưa có chủ đề Grammar nào. Hãy thêm chủ đề đầu tiên!</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Add/Edit Topic Form -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-plus text-success"></i> 
                                <?= $editTopic ? 'Chỉnh sửa chủ đề' : 'Thêm chủ đề mới' ?>
                            </h3>
                        </div>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="<?= $editTopic ? 'edit_topic' : 'add_topic' ?>">
                            <?php if ($editTopic): ?>
                                <input type="hidden" name="topic_id" value="<?= $editTopic['MaChuDe'] ?>">
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="course_id">Chọn khóa học <span class="text-danger">*</span></label>
                                    <select class="form-control" id="course_id" name="course_id" required onchange="loadLessons()">
                                        <option value="">-- Chọn khóa học --</option>
                                        <?php if (!empty($courses)): ?>
                                            <?php foreach ($courses as $course): ?>
                                                <option value="<?= $course['MaKhoaHoc'] ?>" 
                                                        <?= ($editTopic && $editTopic['MaKhoaHoc'] == $course['MaKhoaHoc']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($course['TenKhoaHoc']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="">-- Không có khóa học nào --</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="lesson_id">Chọn bài học <span class="text-danger">*</span></label>
                                    <select class="form-control" id="lesson_id" name="lesson_id" required>
                                        <option value="">-- Chọn bài học --</option>
                                    </select>
                                    <small class="text-muted">Chọn khóa học trước để hiển thị danh sách bài học</small>
                                </div>
                                
                                <?php if (!$editTopic): ?>
                                <div class="form-group">
                                    <label for="predefined_topic">Chọn chủ đề ngữ pháp <span class="text-danger">*</span></label>
                                    <select class="form-control" id="predefined_topic" name="predefined_topic" onchange="fillTopicFields()">
                                        <option value="">-- Chọn chủ đề ngữ pháp --</option>
                                        <option value="Thì hiện tại đơn|Present Simple">Thì hiện tại đơn (Present Simple)</option>
                                        <option value="Thì hiện tại tiếp diễn|Present Continuous">Thì hiện tại tiếp diễn (Present Continuous)</option>
                                        <option value="Thì hiện tại hoàn thành|Present Perfect">Thì hiện tại hoàn thành (Present Perfect)</option>
                                        <option value="Thì quá khứ đơn|Past Simple">Thì quá khứ đơn (Past Simple)</option>
                                        <option value="Thì quá khứ tiếp diễn|Past Continuous">Thì quá khứ tiếp diễn (Past Continuous)</option>
                                        <option value="Thì quá khứ hoàn thành|Past Perfect">Thì quá khứ hoàn thành (Past Perfect)</option>
                                        <option value="Thì tương lai đơn|Future Simple">Thì tương lai đơn (Future Simple)</option>
                                        <option value="Thì tương lai tiếp diễn|Future Continuous">Thì tương lai tiếp diễn (Future Continuous)</option>
                                        <option value="Mạo từ|Articles">Mạo từ (Articles: a, an, the)</option>
                                        <option value="Danh từ|Nouns">Danh từ (Nouns)</option>
                                        <option value="Đại từ|Pronouns">Đại từ (Pronouns)</option>
                                        <option value="Tính từ|Adjectives">Tính từ (Adjectives)</option>
                                        <option value="Trạng từ|Adverbs">Trạng từ (Adverbs)</option>
                                        <option value="Giới từ|Prepositions">Giới từ (Prepositions)</option>
                                        <option value="Liên từ|Conjunctions">Liên từ (Conjunctions)</option>
                                        <option value="Động từ khuyết thiếu|Modal Verbs">Động từ khuyết thiếu (Modal Verbs)</option>
                                        <option value="Câu điều kiện|Conditional Sentences">Câu điều kiện (Conditional Sentences)</option>
                                        <option value="Câu bị động|Passive Voice">Câu bị động (Passive Voice)</option>
                                        <option value="Câu trực tiếp và gián tiếp|Direct and Indirect Speech">Câu trực tiếp và gián tiếp (Direct & Indirect Speech)</option>
                                        <option value="So sánh|Comparatives and Superlatives">So sánh (Comparatives & Superlatives)</option>
                                    </select>
                                </div>
                                <?php endif; ?>
                                
                                <div class="form-group">
                                    <label for="topic_name">Tên chủ đề (Tiếng Việt) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="topic_name" name="topic_name" 
                                           value="<?= $editTopic ? htmlspecialchars($editTopic['TenChuDe']) : '' ?>" 
                                           placeholder="Ví dụ: Thì hiện tại đơn" required 
                                           <?= !$editTopic ? 'readonly' : '' ?>>
                                    <?php if (!$editTopic): ?>
                                        <small class="text-muted">Tự động điền khi chọn chủ đề từ danh sách bên trên</small>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group">
                                    <label for="topic_name_eng">Tên tiếng Anh <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="topic_name_eng" name="topic_name_eng" 
                                           value="<?= $editTopic ? htmlspecialchars($editTopic['TenChuDeEng']) : '' ?>" 
                                           placeholder="Ví dụ: Present Simple" required 
                                           <?= !$editTopic ? 'readonly' : '' ?>>
                                    <?php if (!$editTopic): ?>
                                        <small class="text-muted">Tự động điền khi chọn chủ đề từ danh sách bên trên</small>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group">
                                    <label for="description">Mô tả</label>
                                    <textarea class="form-control" id="description" name="description" rows="3" 
                                              placeholder="Thì hiện tại đơn dùng để diễn tả hành động thường xuyên, sự thật hiển nhiên"><?= $editTopic ? htmlspecialchars($editTopic['MoTa']) : '' ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="level">Cấp độ <span class="text-danger">*</span></label>
                                    <select class="form-control" id="level" name="level" required>
                                        <option value="Beginner" <?= ($editTopic && $editTopic['CapDo'] == 'Beginner') ? 'selected' : '' ?>>Beginner</option>
                                        <option value="Intermediate" <?= ($editTopic && $editTopic['CapDo'] == 'Intermediate') ? 'selected' : '' ?>>Intermediate</option>
                                        <option value="Advanced" <?= ($editTopic && $editTopic['CapDo'] == 'Advanced') ? 'selected' : '' ?>>Advanced</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="order">Thứ tự hiển thị</label>
                                    <input type="number" class="form-control" id="order" name="order" 
                                           value="<?= $editTopic ? $editTopic['ThuTu'] : '' ?>" 
                                           placeholder="0" min="0">
                                </div>
                            </div>
                            
                            <div class="card-footer">
                                <button type="submit" class="btn btn-<?= $editTopic ? 'warning' : 'success' ?> btn-block">
                                    <i class="fas fa-<?= $editTopic ? 'save' : 'plus' ?>"></i> 
                                    <?= $editTopic ? 'Cập nhật' : 'Thêm chủ đề' ?>
                                </button>
                                <?php if ($editTopic): ?>
                                    <a href="?" class="btn btn-secondary btn-block mt-2">
                                        <i class="fas fa-times"></i> Hủy
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Questions Management (only show if topic is selected) -->
            <?php if ($selectedTopicId): ?>
                <?php 
                $selectedTopic = $Database->get_row("SELECT * FROM grammar_topics WHERE MaChuDe = " . (int)$selectedTopicId);
                ?>
                <div class="row mt-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-question-circle text-info"></i> 
                                    Câu hỏi cho chủ đề: <?= htmlspecialchars($selectedTopic['TenChuDe']) ?>
                                </h3>
                                <div class="card-tools">
                                    <a href="?" class="btn btn-tool">
                                        <i class="fas fa-times"></i> Đóng
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($questions)): ?>
                                    <?php foreach ($questions as $index => $question): ?>
                                        <div class="card mb-3">
                                            <div class="card-header">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span><strong>Câu <?= $index + 1 ?>:</strong> <?= htmlspecialchars($question['CauHoi']) ?></span>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="?topic_id=<?= $selectedTopicId ?>&edit_question=<?= $question['MaCauHoi'] ?>" 
                                                           class="btn btn-outline-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" 
                                                                class="btn btn-outline-danger" 
                                                                onclick="deleteQuestion(<?= $question['MaCauHoi'] ?>, '<?= addslashes($question['CauHoi']) ?>')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input type="radio" disabled <?= $question['DapAnDung'] == 'A' ? 'checked' : '' ?>>
                                                            <label class="<?= $question['DapAnDung'] == 'A' ? 'text-success font-weight-bold' : '' ?>">
                                                                A. <?= htmlspecialchars($question['DapAnA']) ?>
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input type="radio" disabled <?= $question['DapAnDung'] == 'B' ? 'checked' : '' ?>>
                                                            <label class="<?= $question['DapAnDung'] == 'B' ? 'text-success font-weight-bold' : '' ?>">
                                                                B. <?= htmlspecialchars($question['DapAnB']) ?>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input type="radio" disabled <?= $question['DapAnDung'] == 'C' ? 'checked' : '' ?>>
                                                            <label class="<?= $question['DapAnDung'] == 'C' ? 'text-success font-weight-bold' : '' ?>">
                                                                C. <?= htmlspecialchars($question['DapAnC']) ?>
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input type="radio" disabled <?= $question['DapAnDung'] == 'D' ? 'checked' : '' ?>>
                                                            <label class="<?= $question['DapAnDung'] == 'D' ? 'text-success font-weight-bold' : '' ?>">
                                                                D. <?= htmlspecialchars($question['DapAnD']) ?>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php if ($question['GiaiThich']): ?>
                                                    <div class="mt-3">
                                                        <strong>Giải thích:</strong> <?= htmlspecialchars($question['GiaiThich']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Chưa có câu hỏi nào cho chủ đề này. Hãy thêm câu hỏi đầu tiên!</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Add/Edit Question Form -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-plus text-success"></i> 
                                    <?= $editQuestion ? 'Chỉnh sửa câu hỏi' : 'Thêm câu hỏi' ?>
                                </h3>
                            </div>
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="<?= $editQuestion ? 'edit_question' : 'add_question' ?>">
                                <input type="hidden" name="topic_id" value="<?= $selectedTopicId ?>">
                                <?php if ($editQuestion): ?>
                                    <input type="hidden" name="question_id" value="<?= $editQuestion['MaCauHoi'] ?>">
                                <?php endif; ?>
                                
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="question_text">Nội dung câu hỏi <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="question_text" name="question_text" rows="3" 
                                                  placeholder="Nhập nội dung câu hỏi..." required><?= $editQuestion ? htmlspecialchars($editQuestion['CauHoi']) : '' ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="option_a">Phương án A <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="option_a" name="option_a" 
                                               value="<?= $editQuestion ? htmlspecialchars($editQuestion['DapAnA']) : '' ?>" 
                                               placeholder="Phương án A" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="option_b">Phương án B <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="option_b" name="option_b" 
                                               value="<?= $editQuestion ? htmlspecialchars($editQuestion['DapAnB']) : '' ?>" 
                                               placeholder="Phương án B" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="option_c">Phương án C <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="option_c" name="option_c" 
                                               value="<?= $editQuestion ? htmlspecialchars($editQuestion['DapAnC']) : '' ?>" 
                                               placeholder="Phương án C" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="option_d">Phương án D <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="option_d" name="option_d" 
                                               value="<?= $editQuestion ? htmlspecialchars($editQuestion['DapAnD']) : '' ?>" 
                                               placeholder="Phương án D" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="correct_answer">Đáp án đúng <span class="text-danger">*</span></label>
                                        <select class="form-control" id="correct_answer" name="correct_answer" required>
                                            <option value="A" <?= ($editQuestion && $editQuestion['DapAnDung'] == 'A') ? 'selected' : '' ?>>A</option>
                                            <option value="B" <?= ($editQuestion && $editQuestion['DapAnDung'] == 'B') ? 'selected' : '' ?>>B</option>
                                            <option value="C" <?= ($editQuestion && $editQuestion['DapAnDung'] == 'C') ? 'selected' : '' ?>>C</option>
                                            <option value="D" <?= ($editQuestion && $editQuestion['DapAnDung'] == 'D') ? 'selected' : '' ?>>D</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="explanation">Giải thích</label>
                                        <textarea class="form-control" id="explanation" name="explanation" rows="3" 
                                                  placeholder="Giải thích đáp án (tùy chọn)"><?= $editQuestion ? htmlspecialchars($editQuestion['GiaiThich']) : '' ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-<?= $editQuestion ? 'warning' : 'success' ?> btn-block">
                                        <i class="fas fa-<?= $editQuestion ? 'save' : 'plus' ?>"></i> 
                                        <?= $editQuestion ? 'Cập nhật câu hỏi' : 'Thêm câu hỏi' ?>
                                    </button>
                                    <?php if ($editQuestion): ?>
                                        <a href="?topic_id=<?= $selectedTopicId ?>" class="btn btn-secondary btn-block mt-2">
                                            <i class="fas fa-times"></i> Hủy
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
// Load lessons based on selected course
function loadLessons() {
    const courseId = document.getElementById('course_id').value;
    const lessonSelect = document.getElementById('lesson_id');
    
    // Clear lesson options
    lessonSelect.innerHTML = '<option value="">-- Đang tải... --</option>';
    
    if (!courseId) {
        lessonSelect.innerHTML = '<option value="">-- Chọn bài học --</option>';
        return;
    }
    
    // Fetch lessons via AJAX
    fetch(`/webhocngoaingu/api/get-lessons.php?course_id=${courseId}`)
        .then(response => response.json())
        .then(data => {
            lessonSelect.innerHTML = '<option value="">-- Chọn bài học --</option>';
            
            if (data.success && data.data.length > 0) {
                data.data.forEach(lesson => {
                    const option = document.createElement('option');
                    option.value = lesson.MaBaiHoc;
                    option.textContent = lesson.TenBaiHoc;
                    <?php if ($editTopic): ?>
                    if (lesson.MaBaiHoc == <?= $editTopic['MaBaiHoc'] ?? 'null' ?>) {
                        option.selected = true;
                    }
                    <?php endif; ?>
                    lessonSelect.appendChild(option);
                });
            } else {
                lessonSelect.innerHTML = '<option value="">-- Không có bài học nào --</option>';
            }
        })
        .catch(error => {
            console.error('Error loading lessons:', error);
            lessonSelect.innerHTML = '<option value="">-- Lỗi tải dữ liệu --</option>';
        });
}

// Load lessons if editing topic
<?php if ($editTopic && !empty($editTopic['MaKhoaHoc'])): ?>
    document.addEventListener('DOMContentLoaded', function() {
        loadLessons();
    });
<?php endif; ?>

function fillTopicFields() {
    const select = document.getElementById('predefined_topic');
    const topicName = document.getElementById('topic_name');
    const topicNameEng = document.getElementById('topic_name_eng');
    
    if (select.value) {
        const parts = select.value.split('|');
        topicName.value = parts[0];
        topicNameEng.value = parts[1];
        topicName.readOnly = true;
        topicNameEng.readOnly = true;
    } else {
        topicName.value = '';
        topicNameEng.value = '';
        topicName.readOnly = false;
        topicNameEng.readOnly = false;
    }
}

function deleteTopic(topicId, topicName) {
    if (confirm('Bạn có chắc chắn muốn xóa chủ đề "' + topicName + '"?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_topic">
            <input type="hidden" name="topic_id" value="${topicId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteQuestion(questionId, questionText) {
    if (confirm('Bạn có chắc chắn muốn xóa câu hỏi này?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_question">
            <input type="hidden" name="question_id" value="${questionId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include_once(__DIR__ . '/Footer.php'); ?>