<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");

// Check admin permission
if (!isset($_SESSION["account"]) || $_SESSION["account"] != "admin") {
    header("Location: " . BASE_URL('Auth/DangNhap'));
    exit();
}

// Get topic information
$topicId = isset($_GET['topic']) ? (int)$_GET['topic'] : 0;
$topicName = isset($_GET['name']) ? $_GET['name'] : '';

if (!$topicId) {
    header("Location: SpeakingManagement.php");
    exit();
}

// Get topic details
$topic = $Database->get_row("
    SELECT st.*, kh.TenKhoaHoc, bh.TenBaiHoc 
    FROM speaking_topics st 
    LEFT JOIN khoahoc kh ON st.MaKhoaHoc = kh.MaKhoaHoc
    LEFT JOIN baihoc bh ON st.MaBaiHoc = bh.MaBaiHoc 
    WHERE st.MaChuDe = '$topicId' AND st.TrangThai = 1
");

if (!$topic) {
    header("Location: SpeakingManagement.php");
    exit();
}

$title = 'Quản lý bài Speaking: ' . $topic['TenChuDe'];

// Handle AJAX requests
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'add_lesson':
            $maChuDe = $_POST['maChuDe'];
            $tieuDe = addslashes($_POST['tieuDe']);
            $noiDung = addslashes($_POST['noiDung']);
            $textMau = addslashes($_POST['textMau']);
            $capDo = $_POST['capDo'];
            $diemToiThieu = $_POST['diemToiThieu'];
            $thoiGianUocTinh = $_POST['thoiGianUocTinh'];
            $thuTu = $_POST['thuTu'];
            
            $result = $Database->query("INSERT INTO speaking_lessons (MaChuDe, TieuDe, NoiDung, TextMau, CapDo, DiemToiThieu, ThoiGianUocTinh, ThuTu) VALUES ('$maChuDe', '$tieuDe', '$noiDung', '$textMau', '$capDo', '$diemToiThieu', '$thoiGianUocTinh', '$thuTu')");
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Thêm bài học thành công!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra!']);
            }
            exit();
            
        case 'edit_lesson':
            $maBaiSpeaking = $_POST['maBaiSpeaking'];
            $tieuDe = addslashes($_POST['tieuDe']);
            $noiDung = addslashes($_POST['noiDung']);
            $textMau = addslashes($_POST['textMau']);
            $capDo = $_POST['capDo'];
            $diemToiThieu = $_POST['diemToiThieu'];
            $thoiGianUocTinh = $_POST['thoiGianUocTinh'];
            $thuTu = $_POST['thuTu'];
            
            $result = $Database->query("UPDATE speaking_lessons SET 
                TieuDe = '$tieuDe',
                NoiDung = '$noiDung', 
                TextMau = '$textMau',
                CapDo = '$capDo',
                DiemToiThieu = '$diemToiThieu',
                ThoiGianUocTinh = '$thoiGianUocTinh',
                ThuTu = '$thuTu'
                WHERE MaBaiSpeaking = '$maBaiSpeaking'
            ");
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Cập nhật bài học thành công!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra!']);
            }
            exit();
            
        case 'delete_lesson':
            $maBaiSpeaking = $_POST['maBaiSpeaking'];
            $result = $Database->query("UPDATE speaking_lessons SET TrangThai = 0 WHERE MaBaiSpeaking = '$maBaiSpeaking'");
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Xóa bài học thành công!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra!']);
            }
            exit();
            
        case 'get_lesson_detail':
            $maBaiSpeaking = $_POST['maBaiSpeaking'];
            $lesson = $Database->get_row("
                SELECT * FROM speaking_lessons 
                WHERE MaBaiSpeaking = '$maBaiSpeaking' AND TrangThai = 1
            ");
            if ($lesson) {
                echo json_encode(['success' => true, 'lesson' => $lesson]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy bài học']);
            }
            exit();
    }
}

// Get lessons in this topic
$lessons = $Database->get_list("
    SELECT * FROM speaking_lessons 
    WHERE MaChuDe = '$topicId' AND TrangThai = 1 
    ORDER BY ThuTu ASC, MaBaiSpeaking ASC
");

// Get statistics
$totalLessons = count($lessons);
$totalResults = $Database->get_row("SELECT COUNT(*) as count FROM speaking_results sr JOIN speaking_lessons sl ON sr.MaBaiSpeaking = sl.MaBaiSpeaking WHERE sl.MaChuDe = '$topicId'")['count'];
$avgScore = $Database->get_row("SELECT AVG(sr.TongDiem) as avg FROM speaking_results sr JOIN speaking_lessons sl ON sr.MaBaiSpeaking = sl.MaBaiSpeaking WHERE sl.MaChuDe = '$topicId' AND sr.TongDiem IS NOT NULL")['avg'];

require_once(__DIR__ . "/Header.php");
require_once(__DIR__ . "/Sidebar.php");
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Quản lý bài Speaking</h1>
                    <p class="text-muted">Chủ đề: <strong><?= htmlspecialchars($topic['TenChuDe']) ?></strong></p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL('admin/home'); ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="SpeakingManagement.php">Speaking Management</a></li>
                        <li class="breadcrumb-item active"><?= htmlspecialchars($topic['TenChuDe']) ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Topic Info Card -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Thông tin chủ đề</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Tên chủ đề:</strong><br>
                            <?= htmlspecialchars($topic['TenChuDe']) ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Tên tiếng Anh:</strong><br>
                            <?= htmlspecialchars($topic['TenChuDeEng']) ?>
                        </div>
                        <div class="col-md-2">
                            <strong>Cấp độ:</strong><br>
                            <span class="badge badge-<?= $topic['CapDo'] == 'Beginner' ? 'success' : ($topic['CapDo'] == 'Intermediate' ? 'warning' : 'danger') ?>">
                                <?= $topic['CapDo'] ?>
                            </span>
                        </div>
                        <div class="col-md-2">
                            <strong>Khóa học:</strong><br>
                            <?= htmlspecialchars($topic['TenKhoaHoc'] ?: 'N/A') ?>
                        </div>
                        <div class="col-md-2">
                            <strong>Bài học:</strong><br>
                            <?= htmlspecialchars($topic['TenBaiHoc'] ?: 'N/A') ?>
                        </div>
                    </div>
                    <?php if ($topic['MoTa']): ?>
                    <div class="row mt-3">
                        <div class="col-12">
                            <strong>Mô tả:</strong><br>
                            <?= htmlspecialchars($topic['MoTa']) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= $totalLessons ?></h3>
                            <p>Bài luyện tập</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-microphone"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= $totalResults ?></h3>
                            <p>Lượt làm bài</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?= $avgScore ? round($avgScore, 1) : '0' ?></h3>
                            <p>Điểm trung bình</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lessons Management -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Danh sách bài Speaking</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addLessonModal">
                            <i class="fas fa-plus"></i> Thêm bài mới
                        </button>
                        <a href="SpeakingManagement.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tiêu đề</th>
                                    <th>Text mẫu</th>
                                    <th>Cấp độ</th>
                                    <th>Điểm tối thiểu</th>
                                    <th>Thời gian (giây)</th>
                                    <th>Thứ tự</th>
                                    <th>Ngày tạo</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($lessons) > 0): ?>
                                    <?php foreach ($lessons as $lesson): ?>
                                    <tr>
                                        <td><?= $lesson['MaBaiSpeaking'] ?></td>
                                        <td><?= htmlspecialchars($lesson['TieuDe']) ?></td>
                                        <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($lesson['TextMau']) ?>">
                                            <?= htmlspecialchars(mb_substr($lesson['TextMau'], 0, 50)) ?><?= mb_strlen($lesson['TextMau']) > 50 ? '...' : '' ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?= $lesson['CapDo'] == 'Beginner' ? 'success' : ($lesson['CapDo'] == 'Intermediate' ? 'warning' : 'danger') ?>">
                                                <?= $lesson['CapDo'] ?>
                                            </span>
                                        </td>
                                        <td><?= $lesson['DiemToiThieu'] ?></td>
                                        <td><?= $lesson['ThoiGianUocTinh'] ?></td>
                                        <td><?= $lesson['ThuTu'] ?></td>
                                        <td><?= date('d/m/Y', strtotime($lesson['ThoiGianTao'])) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="viewLesson(<?= $lesson['MaBaiSpeaking'] ?>)" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-primary" onclick="editLesson(<?= $lesson['MaBaiSpeaking'] ?>)" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteLesson(<?= $lesson['MaBaiSpeaking'] ?>)" title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center">Chưa có bài học nào trong chủ đề này</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Add Lesson Modal -->
<div class="modal fade" id="addLessonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Thêm bài speaking mới</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="addLessonForm">
                <input type="hidden" name="maChuDe" value="<?= $topicId ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Tiêu đề bài học</label>
                        <input type="text" class="form-control" name="tieuDe" required>
                    </div>
                    <div class="form-group">
                        <label>Mô tả nội dung</label>
                        <textarea class="form-control" name="noiDung" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Văn bản mẫu (để học viên đọc)</label>
                        <textarea class="form-control" name="textMau" rows="4" required placeholder="Ví dụ: Hello, how are you today? Nice to meet you."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Cấp độ</label>
                                <select class="form-control" name="capDo" required>
                                    <option value="<?= $topic['CapDo'] ?>" selected><?= $topic['CapDo'] ?></option>
                                    <option value="Beginner">Beginner</option>
                                    <option value="Intermediate">Intermediate</option>
                                    <option value="Advanced">Advanced</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Điểm tối thiểu</label>
                                <input type="number" class="form-control" name="diemToiThieu" value="6.0" min="0" max="10" step="0.1">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Thời gian ước tính (giây)</label>
                                <input type="number" class="form-control" name="thoiGianUocTinh" value="30" min="10">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Thứ tự</label>
                        <input type="number" class="form-control" name="thuTu" value="<?= $totalLessons + 1 ?>" min="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Thêm bài học</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Lesson Modal -->
<div class="modal fade" id="viewLessonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Chi tiết bài speaking</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="viewLessonContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Lesson Modal -->
<div class="modal fade" id="editLessonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Chỉnh sửa bài speaking</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="editLessonForm">
                <input type="hidden" name="maBaiSpeaking" id="editLessonId">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Tiêu đề</label>
                        <input type="text" class="form-control" name="tieuDe" id="editTieuDe" required>
                    </div>
                    <div class="form-group">
                        <label>Nội dung hướng dẫn</label>
                        <textarea class="form-control" name="noiDung" id="editNoiDung" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Text mẫu (cho học viên đọc)</label>
                        <textarea class="form-control" name="textMau" id="editTextMau" rows="4" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Cấp độ</label>
                                <select class="form-control" name="capDo" id="editCapDo" required>
                                    <option value="Beginner">Beginner</option>
                                    <option value="Intermediate">Intermediate</option>
                                    <option value="Advanced">Advanced</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Điểm tối thiểu để qua</label>
                                <input type="number" class="form-control" name="diemToiThieu" id="editDiemToiThieu" min="0" max="10" step="0.1" value="6.0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Thời gian ước tính (giây)</label>
                                <input type="number" class="form-control" name="thoiGianUocTinh" id="editThoiGianUocTinh" min="10" value="30">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Thứ tự</label>
                        <input type="number" class="form-control" name="thuTu" id="editThuTu" min="1" value="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Add Lesson
$('#addLessonForm').on('submit', function(e) {
    e.preventDefault();
    
    var formData = $(this).serialize();
    formData += '&action=add_lesson';
    
    $.ajax({
        url: '',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert(response.message);
                location.reload();
            } else {
                alert(response.message);
            }
        },
        error: function() {
            alert('Có lỗi xảy ra!');
        }
    });
});

// View lesson details
function viewLesson(lessonId) {
    $.ajax({
        url: '',
        type: 'POST',
        data: {
            action: 'get_lesson_detail',
            maBaiSpeaking: lessonId
        },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.lesson) {
                const lesson = response.lesson;
                const levelBadge = lesson.CapDo === 'Beginner' ? 'success' : 
                                 lesson.CapDo === 'Intermediate' ? 'warning' : 'danger';
                
                $('#viewLessonContent').html(`
                    <div class="row">
                        <div class="col-md-6">
                            <strong>ID:</strong> ${lesson.MaBaiSpeaking}<br>
                            <strong>Tiêu đề:</strong> ${lesson.TieuDe}<br>
                            <strong>Cấp độ:</strong> <span class="badge badge-${levelBadge}">${lesson.CapDo}</span><br>
                            <strong>Điểm tối thiểu:</strong> ${lesson.DiemToiThieu}<br>
                        </div>
                        <div class="col-md-6">
                            <strong>Thời gian:</strong> ${lesson.ThoiGianUocTinh} giây<br>
                            <strong>Thứ tự:</strong> ${lesson.ThuTu}<br>
                            <strong>Ngày tạo:</strong> ${new Date(lesson.ThoiGianTao).toLocaleDateString('vi-VN')}<br>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <strong>Nội dung hướng dẫn:</strong><br>
                            <p class="text-muted">${lesson.NoiDung || 'Không có'}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <strong>Text mẫu:</strong><br>
                            <div class="border p-3 bg-light">
                                ${lesson.TextMau}
                            </div>
                        </div>
                    </div>
                `);
                $('#viewLessonModal').modal('show');
            } else {
                alert('Không thể tải thông tin bài học');
            }
        },
        error: function() {
            alert('Lỗi kết nối khi tải thông tin bài học');
        }
    });
}

// Edit lesson
function editLesson(lessonId) {
    $.ajax({
        url: '',
        type: 'POST',
        data: {
            action: 'get_lesson_detail',
            maBaiSpeaking: lessonId
        },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.lesson) {
                const lesson = response.lesson;
                $('#editLessonId').val(lesson.MaBaiSpeaking);
                $('#editTieuDe').val(lesson.TieuDe);
                $('#editNoiDung').val(lesson.NoiDung || '');
                $('#editTextMau').val(lesson.TextMau);
                $('#editCapDo').val(lesson.CapDo);
                $('#editDiemToiThieu').val(lesson.DiemToiThieu);
                $('#editThoiGianUocTinh').val(lesson.ThoiGianUocTinh);
                $('#editThuTu').val(lesson.ThuTu);
                $('#editLessonModal').modal('show');
            } else {
                alert('Không thể tải thông tin bài học');
            }
        },
        error: function() {
            alert('Lỗi kết nối khi tải thông tin bài học');
        }
    });
}

// Handle edit lesson form submission
$('#editLessonForm').on('submit', function(e) {
    e.preventDefault();
    
    var formData = $(this).serialize();
    formData += '&action=edit_lesson';
    
    $.ajax({
        url: '',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert(response.message);
                $('#editLessonModal').modal('hide');
                location.reload();
            } else {
                alert(response.message);
            }
        },
        error: function() {
            alert('Có lỗi xảy ra!');
        }
    });
});

// Delete lesson
function deleteLesson(lessonId) {
    if (confirm('Bạn có chắc chắn muốn xóa bài học này?')) {
        $.ajax({
            url: '',
            type: 'POST',
            data: {
                action: 'delete_lesson',
                maBaiSpeaking: lessonId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Có lỗi xảy ra!');
            }
        });
    }
}
</script>

<?php
require_once(__DIR__ . "/Footer.php");
?>