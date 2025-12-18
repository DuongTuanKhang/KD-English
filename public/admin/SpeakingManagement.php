<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");

// Check admin permission
if (!isset($_SESSION["account"]) || $_SESSION["account"] != "admin") {
    // For AJAX requests, return JSON error
    if (isset($_POST['action'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
        exit();
    }
    // For normal requests, redirect to login
    header("Location: " . BASE_URL('Auth/DangNhap'));
    exit();
}

$title = 'Speaking Management - Admin';

// Handle AJAX requests  
if (isset($_POST['action'])) {
    // Clean any output buffer
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'add_topic':
            $maKhoaHoc = (int)$_POST['maKhoaHoc'];
            $maBaiHoc = (int)$_POST['maBaiHoc'];
            $capDo = $_POST['capDo'];
            $thuTu = (int)$_POST['thuTu'];
            
            // Get course and lesson info
            $course = $Database->get_row("SELECT * FROM khoahoc WHERE MaKhoaHoc = '$maKhoaHoc'");
            $lesson = $Database->get_row("SELECT * FROM baihoc WHERE MaBaiHoc = '$maBaiHoc'");
            
            if ($course && $lesson) {
                // Check if topic already exists
                $existing = $Database->get_row("SELECT * FROM speaking_topics WHERE MaKhoaHoc = '$maKhoaHoc' AND MaBaiHoc = '$maBaiHoc'");
                if ($existing) {
                    echo json_encode(['success' => false, 'message' => 'Chủ đề này đã tồn tại trong Speaking!']);
                    exit();
                }
                
                $tenChuDe = addslashes($lesson['TenBaiHoc']);
                $tenChuDeEng = addslashes($course['TenKhoaHoc']);
                $moTa = addslashes('Chủ đề Speaking: ' . $lesson['TenBaiHoc'] . ' - ' . $course['TenKhoaHoc']);
                
                $result = $Database->query("INSERT INTO speaking_topics (TenChuDe, TenChuDeEng, MoTa, MaKhoaHoc, MaBaiHoc, CapDo, ThuTu) VALUES ('$tenChuDe', '$tenChuDeEng', '$moTa', '$maKhoaHoc', '$maBaiHoc', '$capDo', '$thuTu')");
                
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Thêm chủ đề Speaking thành công!']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Lỗi database khi thêm!']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy khóa học hoặc bài học!']);
            }
            exit();
            
        case 'add_lesson':
            $maChuDe = $_POST['maChuDe'];
            $maKhoaHoc = (int)$_POST['maKhoaHoc'];
            $textMau = addslashes($_POST['textMau']);
            $capDo = $_POST['capDo'];
            $diemToiThieu = (int)$_POST['diemToiThieu'];
            $thoiGianUocTinh = (int)$_POST['thoiGianUocTinh'];
            $thuTu = (int)$_POST['thuTu'];
            
            // Check if this is a new topic (starts with 'new_')
            if (strpos($maChuDe, 'new_') === 0) {
                $maBaiHoc = (int)str_replace('new_', '', $maChuDe);
                
                // Get lesson info
                $lesson = $Database->get_row("SELECT * FROM baihoc WHERE MaBaiHoc = '$maBaiHoc'");
                $course = $Database->get_row("SELECT * FROM khoahoc WHERE MaKhoaHoc = '$maKhoaHoc'");
                
                if ($lesson && $course) {
                    // Create new speaking topic
                    $tenChuDe = addslashes($lesson['TenBaiHoc']);
                    $tenChuDeEng = addslashes($course['TenKhoaHoc']);
                    $moTa = addslashes('Chủ đề Speaking: ' . $lesson['TenBaiHoc'] . ' - ' . $course['TenKhoaHoc']);
                    
                    $topicResult = $Database->query("INSERT INTO speaking_topics (TenChuDe, TenChuDeEng, MoTa, MaKhoaHoc, MaBaiHoc, CapDo, ThuTu, TrangThai) VALUES ('$tenChuDe', '$tenChuDeEng', '$moTa', '$maKhoaHoc', '$maBaiHoc', '$capDo', '$thuTu', 1)");
                    
                    if ($topicResult) {
                        $maChuDe = $Database->insert_id();
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Không thể tạo chủ đề mới!']);
                        exit();
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Không tìm thấy bài học hoặc khóa học!']);
                    exit();
                }
            } else {
                $maChuDe = (int)$maChuDe;
            }
            
            // Get topic name for auto-generating title
            $topic = $Database->get_row("SELECT TenChuDe FROM speaking_topics WHERE MaChuDe = '$maChuDe'");
            $tieuDe = $topic ? addslashes($topic['TenChuDe']) : 'Speaking Lesson';
            $noiDung = 'Bài luyện speaking';
            
            // Add lesson to topic
            $result = $Database->query("INSERT INTO speaking_lessons (MaChuDe, TieuDe, NoiDung, TextMau, CapDo, DiemToiThieu, ThoiGianUocTinh, ThuTu, TrangThai) VALUES ('$maChuDe', '$tieuDe', '$noiDung', '$textMau', '$capDo', '$diemToiThieu', '$thoiGianUocTinh', '$thuTu', 1)");
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Thêm bài nói thành công!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi thêm bài nói!']);
            }
            exit();
            
        case 'delete_lesson':
            $maBaiSpeaking = $_POST['maBaiSpeaking'];
            // XÓA HẲNG KHỎI DATABASE (không chỉ đổi trạng thái)
            $result = $Database->query("DELETE FROM speaking_lessons WHERE MaBaiSpeaking = '$maBaiSpeaking'");
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Xóa bài học thành công!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra!']);
            }
            exit();
            
        case 'delete_topic':
            $maChuDe = $_POST['maChuDe'];
            $result = $Database->query("UPDATE speaking_topics SET TrangThai = 0 WHERE MaChuDe = '$maChuDe'");
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Xóa chủ đề thành công!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra!']);
            }
            exit();
            
        case 'get_lessons':
            $maChuDe = $_POST['maChuDe'];
            $lessons = $Database->get_list("
                SELECT * FROM speaking_lessons 
                WHERE MaChuDe = '$maChuDe' AND TrangThai = 1 
                ORDER BY ThuTu ASC, MaBaiSpeaking ASC
            ");
            echo json_encode(['success' => true, 'lessons' => $lessons]);
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
                // Redirect with success message
                $_SESSION['success_message'] = 'Cập nhật bài học thành công!';
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit();
            } else {
                // Redirect with error message  
                $_SESSION['error_message'] = 'Có lỗi xảy ra khi cập nhật!';
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit();
            }
    }
}

// Get statistics
$totalTopics = $Database->get_row("SELECT COUNT(*) as count FROM speaking_topics WHERE TrangThai = 1")['count'];
$totalLessons = $Database->get_row("SELECT COUNT(*) as count FROM speaking_lessons WHERE TrangThai = 1")['count'];
$totalResults = $Database->get_row("SELECT COUNT(*) as count FROM speaking_results")['count'];
$avgScore = $Database->get_row("SELECT AVG(TongDiem) as avg FROM speaking_results WHERE TongDiem IS NOT NULL")['avg'];

// Get topics with lesson count
$topics = $Database->get_list("
    SELECT st.*, 
           (SELECT COUNT(*) FROM speaking_lessons sl WHERE sl.MaChuDe = st.MaChuDe AND sl.TrangThai = 1) as SoLuongBai,
           kh.TenKhoaHoc,
           bh.TenBaiHoc
    FROM speaking_topics st 
    LEFT JOIN khoahoc kh ON st.MaKhoaHoc = kh.MaKhoaHoc
    LEFT JOIN baihoc bh ON st.MaBaiHoc = bh.MaBaiHoc AND st.MaKhoaHoc = bh.MaKhoaHoc
    WHERE st.TrangThai = 1 
    ORDER BY st.ThuTu ASC, st.MaChuDe ASC
");

// Get available courses and lessons from existing system
$courses = $Database->get_list("SELECT * FROM khoahoc WHERE TrangThaiKhoaHoc = 1 ORDER BY TenKhoaHoc");
$allLessons = $Database->get_list("SELECT * FROM baihoc WHERE TrangThaiBaiHoc = 1 ORDER BY MaBaiHoc");

// Get all active speaking topics for dropdown
$allSpeakingTopics = $Database->get_list("
    SELECT MaChuDe, TenChuDe, CapDo, MaKhoaHoc, MaBaiHoc
    FROM speaking_topics 
    WHERE TrangThai = 1
    ORDER BY TenChuDe ASC
");

require_once(__DIR__ . "/Header.php");
require_once(__DIR__ . "/Sidebar.php");

// Display session messages
if (isset($_SESSION['success_message'])) {
    echo '<script>
    document.addEventListener("DOMContentLoaded", function() {
        alert("✅ ' . addslashes($_SESSION['success_message']) . '");
    });
    </script>';
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    echo '<script>
    document.addEventListener("DOMContentLoaded", function() {
        alert("❌ ' . addslashes($_SESSION['error_message']) . '");
    });
    </script>';
    unset($_SESSION['error_message']);
}
?>

<!-- JavaScript Functions for Speaking Management -->
<script>
// === SPEAKING MANAGEMENT FUNCTIONS ===

// Helper functions
function showSuccess(message) {
    var notification = document.createElement('div');
    notification.innerHTML = '<i class="fas fa-check-circle"></i> ' + message;
    notification.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px 20px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.2); font-weight: 500; min-width: 250px;';
    document.body.appendChild(notification);
    setTimeout(() => { if (notification.parentNode) { notification.remove(); } }, 3000);
}

function showError(message) {
    var notification = document.createElement('div');
    notification.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + message;
    notification.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px 20px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.2); font-weight: 500; min-width: 250px;';
    document.body.appendChild(notification);
    setTimeout(() => { if (notification.parentNode) { notification.remove(); } }, 4000);
}

function updateSTT() {
    var rows = document.querySelectorAll('tbody tr[data-lesson-id]');
    rows.forEach((row, index) => {
        var sttCell = row.querySelector('td:first-child');
        if (sttCell) { sttCell.textContent = index + 1; }
    });
}

function checkEmptyTable() {
    var dataRows = document.querySelectorAll('tbody tr[data-lesson-id]');
    var tbody = document.querySelector('tbody');
    if (dataRows.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted">Không có dữ liệu</td></tr>';
    }
}

// Main functions
function deleteLesson(maBaiSpeaking) {
    if (!confirm('Bạn có chắc chắn muốn xóa bài này?')) { return; }
    
    var targetRow = document.querySelector('tr[data-lesson-id="' + maBaiSpeaking + '"]');
    if (!targetRow) { alert('Không tìm thấy bài cần xóa!'); return; }
    
    var originalHTML = targetRow.innerHTML;
    var originalStyle = targetRow.style.cssText;
    
    targetRow.style.backgroundColor = '#ffe6e6';
    targetRow.style.opacity = '0.7';
    
    var deleteButton = targetRow.querySelector('.btn-danger');
    if (deleteButton) {
        deleteButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
        deleteButton.disabled = true;
    }
    
    fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=delete_lesson&maBaiSpeaking=' + encodeURIComponent(maBaiSpeaking)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            targetRow.style.transition = 'all 0.3s ease';
            targetRow.style.transform = 'translateX(-100%)';
            targetRow.style.opacity = '0';
            setTimeout(() => {
                targetRow.remove();
                updateSTT();
                checkEmptyTable();
                showSuccess('Xóa bài học thành công!');
            }, 300);
        } else {
            targetRow.innerHTML = originalHTML;
            targetRow.style.cssText = originalStyle;
            showError(data.message || 'Có lỗi xảy ra khi xóa bài học!');
        }
    })
    .catch(error => {
        targetRow.innerHTML = originalHTML;
        targetRow.style.cssText = originalStyle;
        showError('Lỗi kết nối! Vui lòng thử lại.');
    });
}

// View lesson với modal đẹp
function safeViewLesson(maBaiSpeaking) {
    console.log('Loading lesson details for ID:', maBaiSpeaking);
    showSuccess('Đang tải chi tiết bài học...');
    
    fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=get_lesson_detail&maBaiSpeaking=' + encodeURIComponent(maBaiSpeaking)
    })
    .then(response => {
        console.log('Response received:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Data received:', data);
        if (data.success && data.lesson) {
            createViewModal(data.lesson);
        } else {
            showError(data.message || 'Không thể tải thông tin bài học!');
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        // Fallback: lấy thông tin từ bảng hiện tại
        var targetRow = document.querySelector('tr[data-lesson-id="' + maBaiSpeaking + '"]');
        if (targetRow) {
            var cells = targetRow.querySelectorAll('td');
            var fallbackLesson = {
                MaBaiSpeaking: maBaiSpeaking,
                TieuDe: cells[3] ? cells[3].textContent.trim() : 'N/A',
                NoiDung: 'Thông tin chi tiết không khả dụng',
                TextMau: cells[4] ? cells[4].textContent.trim() : 'N/A',
                CapDo: cells[5] ? cells[5].querySelector('.badge').textContent.trim() : 'N/A',
                DiemToiThieu: cells[7] ? cells[7].textContent.trim() : 'N/A',
                ThoiGianUocTinh: cells[8] ? cells[8].textContent.replace(' phút', '').trim() : 'N/A'
            };
            createViewModal(fallbackLesson);
            showSuccess('Hiển thị thông tin cơ bản (chế độ offline)');
        } else {
            showError('Không thể tải dữ liệu bài học!');
        }
    });
}

// Edit lesson với modal thực tế
function safeEditLesson(maBaiSpeaking) {
    console.log('Loading lesson for edit ID:', maBaiSpeaking);
    showSuccess('Đang tải thông tin để chỉnh sửa...');
    
    fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=get_lesson_detail&maBaiSpeaking=' + encodeURIComponent(maBaiSpeaking)
    })
    .then(response => {
        console.log('Edit response:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Edit data:', data);
        if (data.success && data.lesson) {
            openEditModal(data.lesson);
        } else {
            showError(data.message || 'Không thể tải thông tin bài học để chỉnh sửa!');
        }
    })
    .catch(error => {
        console.error('Edit fetch error:', error);
        // Fallback: mở modal với dữ liệu mặc định
        var fallbackLesson = {
            MaBaiSpeaking: maBaiSpeaking,
            CapDo: 'Beginner',
            ThuTu: 1,
            TextMau: '',
            DiemToiThieu: 70,
            ThoiGianUocTinh: 5
        };
        openEditModal(fallbackLesson);
        showSuccess('Mở chế độ chỉnh sửa cơ bản (một số thông tin có thể chưa đầy đủ)');
    });
}

// Tạo modal xem chi tiết đẹp
function createViewModal(lesson) {
    var oldModal = document.getElementById('viewLessonModal');
    if (oldModal) { oldModal.remove(); }
    
    var modalHTML = `
        <div class="modal fade" id="viewLessonModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-eye"></i> Chi tiết bài học #${lesson.MaBaiSpeaking}
                        </h5>
                        <button type="button" class="close text-white" onclick="closeViewModal()">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong><i class="fas fa-heading"></i> Tiêu đề:</strong></p>
                                <p class="text-primary">${lesson.TieuDe || 'Chưa có tiêu đề'}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong><i class="fas fa-layer-group"></i> Cấp độ:</strong></p>
                                <span class="badge badge-${lesson.CapDo === 'Beginner' ? 'success' : lesson.CapDo === 'Intermediate' ? 'warning' : 'danger'} badge-lg">${lesson.CapDo || 'N/A'}</span>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong><i class="fas fa-star"></i> Điểm tối thiểu:</strong></p>
                                <p class="text-success font-weight-bold">${lesson.DiemToiThieu || 'N/A'} điểm</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong><i class="fas fa-clock"></i> Thời gian ước tính:</strong></p>
                                <p class="text-info font-weight-bold">${lesson.ThoiGianUocTinh || 'N/A'} phút</p>
                            </div>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <p><strong><i class="fas fa-align-left"></i> Nội dung bài học:</strong></p>
                            <div class="border rounded p-3 bg-light" style="max-height: 200px; overflow-y: auto;">
                                ${lesson.NoiDung || 'Chưa có nội dung'}
                            </div>
                        </div>
                        <div class="mb-3">
                            <p><strong><i class="fas fa-file-alt"></i> Văn bản mẫu để luyện tập:</strong></p>
                            <div class="border rounded p-3 bg-warning text-dark" style="max-height: 200px; overflow-y: auto;">
                                <pre style="white-space: pre-wrap; margin: 0;">${lesson.TextMau || 'Chưa có văn bản mẫu'}</pre>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeViewModal()">
                            <i class="fas fa-times"></i> Đóng
                        </button>
                        <button type="button" class="btn btn-warning" onclick="closeViewModal(); safeEditLesson(${lesson.MaBaiSpeaking})">
                            <i class="fas fa-edit"></i> Chỉnh sửa
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" onclick="closeViewModal()"></div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    document.getElementById('viewLessonModal').style.display = 'block';
    document.getElementById('viewLessonModal').classList.add('show');
    document.body.classList.add('modal-open');
}

// Mở modal edit với dữ liệu
function openEditModal(lesson) {
    // Điền dữ liệu vào form edit
    document.getElementById('editLessonId').value = lesson.MaBaiSpeaking;
    document.getElementById('editCapDo').value = lesson.CapDo || 'Beginner';
    document.getElementById('editThuTu').value = lesson.ThuTu || 1;
    document.getElementById('editTextMau').value = lesson.TextMau || '';
    document.getElementById('editDiemToiThieu').value = lesson.DiemToiThieu || 70;
    document.getElementById('editThoiGianUocTinh').value = lesson.ThoiGianUocTinh || 5;
    
    // Hiển thị modal bằng jQuery (vì AdminLTE dùng jQuery)
    if (window.$ && window.$.fn.modal) {
        $('#editLessonModal').modal('show');
    } else {
        // Fallback nếu jQuery không có
        var modal = document.getElementById('editLessonModal');
        modal.style.display = 'block';
        modal.classList.add('show');
        document.body.classList.add('modal-open');
    }
}

// Đóng modal view
function closeViewModal() {
    var modal = document.getElementById('viewLessonModal');
    var backdrop = document.querySelector('.modal-backdrop');
    
    if (modal) {
        modal.classList.remove('show');
        modal.style.display = 'none';
        setTimeout(() => modal.remove(), 150);
    }
    
    if (backdrop) { backdrop.remove(); }
    document.body.classList.remove('modal-open');
}

// Xử lý form edit
// SIMPLE EDIT FORM HANDLER - NO FETCH, USE TRADITIONAL FORM SUBMIT
function setupSimpleEditForm() {
    const form = document.getElementById('editLessonForm');
    if (!form) return;
    
    // Set form action and method for traditional submit
    form.action = window.location.href;
    form.method = 'POST';
    
    // Add hidden action field
    let actionInput = form.querySelector('input[name="action"]');
    if (!actionInput) {
        actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'edit_lesson';
        form.appendChild(actionInput);
    }
    
    form.addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang cập nhật...';
        submitBtn.disabled = true;
        
        // Let the form submit naturally (no preventDefault)
        // Form will submit to server and reload page with response
    });
    
    console.log('Simple edit form setup complete');
}

// Document ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Speaking Management loaded successfully');
    
    // Kiểm tra functions có tồn tại không
    var functions = ['deleteLesson', 'safeViewLesson', 'safeEditLesson', 'showSuccess', 'showError'];
    functions.forEach(func => {
        if (typeof window[func] === 'function') {
            console.log(func + ': OK');
        } else {
            console.error(func + ': NOT FOUND');
        }
    });
    
    // Khởi tạo xử lý form
    handleEditForm();
    
    console.log('All systems ready!');
});

</script>

<style>
/* Animation cho slide in notification */
@keyframes slideIn {
    0% {
        transform: translateX(100%);
        opacity: 0;
    }
    100% {
        transform: translateX(0);
        opacity: 1;
    }
}

/* Style cho hàng đang bị xóa */
.table-danger {
    background-color: #f8d7da !important;
    transition: all 0.3s ease;
}

/* Loading spinner */
.fa-spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Button hover effects */
.btn-danger:hover {
    transform: scale(1.05);
    transition: all 0.2s ease;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
}

/* Smooth row delete animation */
.table tbody tr {
    transition: all 0.3s ease;
}

/* Success notification styles */
.success-notification, .error-notification {
    font-weight: 500;
    font-size: 14px;
    animation: slideInFromRight 0.3s ease-out;
}

@keyframes slideInFromRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* Custom modal styles */
.modal {
    background: rgba(0,0,0,0.5);
}

.modal.show {
    display: block !important;
}

.modal-backdrop {
    background: rgba(0,0,0,0.5);
}

.btn-close-white {
    background: none;
    border: none;
    color: white;
    font-size: 20px;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
}

.btn-close-white:before {
    content: '×';
}

/* Action buttons hover */
.btn-info:hover, .btn-warning:hover, .btn-danger:hover {
    transform: scale(1.1);
    transition: all 0.2s ease;
}

/* Custom view modal */
#viewLessonModal .modal-content {
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

#viewLessonModal .modal-header {
    border-radius: 10px 10px 0 0;
}

#viewLessonModal .badge-lg {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
}

#viewLessonModal pre {
    font-family: 'Arial', sans-serif;
    font-size: 14px;
    line-height: 1.5;
}

/* Loading states */
.btn:disabled {
    opacity: 0.6;
    pointer-events: none;
}
</style>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Quản lý Speaking</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL('admin/home'); ?>">Home</a></li>
                        <li class="breadcrumb-item active">Speaking Management</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= $totalTopics ?></h3>
                            <p>Chủ đề</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-list"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= $totalLessons ?></h3>
                            <p>Bài luyện tập</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-microphone"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?= $totalResults ?></h3>
                            <p>Lượt làm bài</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
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

            <!-- Add Lesson Button -->
            <div class="mb-4 text-right">
                <button type="button" class="btn btn-success btn-lg" data-toggle="modal" data-target="#addLessonModal">
                    <i class="fas fa-plus"></i> Thêm bài nói mới
                </button>
            </div>

            <!-- Speaking Lessons List -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list"></i> Danh sách bài nói đã tạo</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>ID</th>
                                    <th>Chủ đề</th>
                                    <th>Nội dung bài nói</th>
                                    <th>Cấp độ</th>
                                    <th>Trạng thái</th>
                                    <th>Điểm tối thiểu</th>
                                    <th>Thời gian</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $speakingLessons = $Database->get_list("
                                    SELECT sl.*, st.TenChuDe, st.TenChuDeEng
                                    FROM speaking_lessons sl
                                    LEFT JOIN speaking_topics st ON sl.MaChuDe = st.MaChuDe
                                    WHERE sl.TrangThai = 1
                                    ORDER BY sl.MaBaiSpeaking ASC
                                ");
                                
                                $stt = 1;
                                foreach ($speakingLessons as $lesson): 
                                ?>
                                <tr data-lesson-id="<?= $lesson['MaBaiSpeaking'] ?>">
                                    <td><?= $stt++ ?></td>
                                    <td><?= $lesson['MaBaiSpeaking'] ?></td>
                                    <td>
                                        <?= htmlspecialchars($lesson['TenChuDe'] ?? 'N/A') ?>
                                        <small class="text-muted d-block"><?= htmlspecialchars($lesson['TenChuDeEng'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <div style="max-width: 300px; max-height: 100px; overflow: hidden; text-overflow: ellipsis;">
                                            <?= htmlspecialchars(substr($lesson['TextMau'] ?? 'Chưa có nội dung', 0, 150)) ?>
                                            <?php if (strlen($lesson['TextMau'] ?? '') > 150): ?>
                                                <small class="text-muted">...</small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= strtolower($lesson['CapDo']) == 'beginner' ? 'success' : (strtolower($lesson['CapDo']) == 'intermediate' ? 'warning' : 'danger') ?>">
                                            <?= $lesson['CapDo'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $lesson['TrangThai'] == 1 ? 'success' : 'secondary' ?>">
                                            <?= $lesson['TrangThai'] == 1 ? 'Hoạt động' : 'Ẩn' ?>
                                        </span>
                                    </td>
                                    <td><?= $lesson['DiemToiThieu'] ?></td>
                                    <td><?= $lesson['ThoiGianUocTinh'] ?> phút</td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="safeViewLesson(<?= $lesson['MaBaiSpeaking'] ?>)" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning" onclick="safeEditLesson(<?= $lesson['MaBaiSpeaking'] ?>)" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteLesson(<?= $lesson['MaBaiSpeaking'] ?>)" title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
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
                <h4 class="modal-title">
                    <i class="fas fa-plus text-success"></i> 
                    Thêm bài nói mới
                </h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="addLessonForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-graduation-cap"></i> Chọn khóa học</label>
                                <select class="form-control" name="maKhoaHoc" required>
                                    <option value="">-- Chọn khóa học --</option>
                                    <?php foreach ($courses as $course): ?>
                                    <option value="<?= $course['MaKhoaHoc'] ?>">
                                        <?= htmlspecialchars($course['TenKhoaHoc']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-list"></i> Chọn chủ đề Speaking</label>
                                <select class="form-control" name="maChuDe" required>
                                    <option value="">-- Chọn chủ đề --</option>
                                    <?php foreach ($allSpeakingTopics as $topic): ?>
                                        <?php 
                                        $courseName = '';
                                        foreach ($courses as $course) {
                                            if ($course['MaKhoaHoc'] == $topic['MaKhoaHoc']) {
                                                $courseName = $course['TenKhoaHoc'];
                                                break;
                                            }
                                        }
                                        ?>
                                    <option value="<?= $topic['MaChuDe'] ?>" data-course="<?= $topic['MaKhoaHoc'] ?>">
                                        <?= htmlspecialchars($topic['TenChuDe']) ?> 
                                        (<?= $courseName ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-signal"></i> Cấp độ</label>
                                <select class="form-control" name="capDo" required>
                                    <option value="Beginner">Beginner - Người mới</option>
                                    <option value="Intermediate">Intermediate - Trung cấp</option>
                                    <option value="Advanced">Advanced - Nâng cao</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-sort-numeric-up"></i> Thứ tự</label>
                                <input type="number" class="form-control" name="thuTu" value="1" min="1">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-microphone"></i> Văn bản mẫu (từ hoặc đoạn nói để học viên luyện tập)</label>
                        <textarea class="form-control" name="textMau" rows="4" required placeholder="VD: Red, Blue, Green, Yellow, Orange, Purple, Pink, Brown, Black, White

Hoặc:
Hello, my name is John. I am from America. I like reading books and playing soccer."></textarea>
                        <small class="text-muted">Có thể nhập từ đơn lẻ hoặc cả đoạn văn</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-star"></i> Điểm tối thiểu để pass</label>
                                <input type="number" class="form-control" name="diemToiThieu" value="70" min="0" max="100">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-clock"></i> Thời gian ước tính (phút)</label>
                                <input type="number" class="form-control" name="thoiGianUocTinh" value="5" min="1" max="60">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-plus"></i> Thêm bài nói
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Lesson Modal -->
<div class="modal fade" id="editLessonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-edit text-warning"></i> 
                    Chỉnh sửa bài nói
                </h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="editLessonForm">
                <input type="hidden" id="editLessonId" name="maBaiSpeaking">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-signal"></i> Cấp độ</label>
                                <select class="form-control" id="editCapDo" name="capDo" required>
                                    <option value="Beginner">Beginner - Người mới</option>
                                    <option value="Intermediate">Intermediate - Trung cấp</option>
                                    <option value="Advanced">Advanced - Nâng cao</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-sort-numeric-up"></i> Thứ tự</label>
                                <input type="number" class="form-control" id="editThuTu" name="thuTu" value="1" min="1">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-microphone"></i> Văn bản mẫu (từ hoặc đoạn nói để học viên luyện tập)</label>
                        <textarea class="form-control" id="editTextMau" name="textMau" rows="4" required placeholder="VD: Red, Blue, Green, Yellow, Orange, Purple, Pink, Brown, Black, White

Hoặc:
Hello, my name is John. I am from America. I like reading books and playing soccer."></textarea>
                        <small class="text-muted">Có thể nhập từ đơn lẻ hoặc cả đoạn văn</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-star"></i> Điểm tối thiểu để pass</label>
                                <input type="number" class="form-control" id="editDiemToiThieu" name="diemToiThieu" value="70" min="0" max="100">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-clock"></i> Thời gian ước tính (phút)</label>
                                <input type="number" class="form-control" id="editThoiGianUocTinh" name="thoiGianUocTinh" value="5" min="1" max="60">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Load lessons for each topic
    <?php foreach ($topics as $topic): ?>
    loadTopicLessons(<?= $topic['MaChuDe'] ?>);
    <?php endforeach; ?>
});

// Load topics when course is selected (using PHP data)
function loadTopicsForCourse(maKhoaHoc) {
    alert('Function called with course ID: ' + maKhoaHoc);
    
    const topicSelect = document.getElementById('topicSelect');
    
    if (!maKhoaHoc || maKhoaHoc === '' || maKhoaHoc === '0') {
        topicSelect.innerHTML = '<option value="">-- Chọn khóa học trước --</option>';
        topicSelect.disabled = true;
        return;
    }
    
    // Simple hardcoded test first
    if (maKhoaHoc == '1') {
        topicSelect.innerHTML = `
            <option value="">-- Chọn chủ đề --</option>
            <option value="1">Activities</option>
            <option value="2">Family</option>
            <option value="3">Hobbie</option>
            <option value="4">Traffic</option>
            <option value="5">Technology</option>
        `;
        topicSelect.disabled = false;
        alert('Loaded topics for course 1');
    } else if (maKhoaHoc == '2') {
        topicSelect.innerHTML = `
            <option value="">-- Chọn chủ đề --</option>
            <option value="">Chưa có chủ đề cho Tiếng Nhật</option>
        `;
        topicSelect.disabled = false;
        alert('No topics available for course 2');
    } else {
        topicSelect.innerHTML = '<option value="">Khóa học không hợp lệ</option>';
        topicSelect.disabled = true;
        alert('Invalid course ID: ' + maKhoaHoc);
    }
}

// Add event listener when page loads
document.addEventListener('DOMContentLoaded', function() {
    const courseSelect = document.getElementById('courseSelect');
    if (courseSelect) {
        courseSelect.addEventListener('change', function() {
            console.log('Course changed to:', this.value);
            loadTopicsForCourse(this.value);
        });
        console.log('Event listener attached to courseSelect');
    } else {
        console.error('courseSelect not found!');
    }
});



// Edit lesson - Vanilla JavaScript
function editLesson(maBaiSpeaking) {
    // Hiển thị loading
    showSuccess('Đang tải thông tin bài học...');
    
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_lesson_detail&maBaiSpeaking=' + encodeURIComponent(maBaiSpeaking)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.lesson) {
            const lesson = data.lesson;
            
            // Điền dữ liệu vào form (nếu có modal)
            const editForm = document.getElementById('editLessonForm');
            if (editForm) {
                // Nếu có modal edit, điền dữ liệu
                document.getElementById('editLessonId').value = lesson.MaBaiSpeaking || '';
                document.getElementById('editCapDo').value = lesson.CapDo || '';
                document.getElementById('editThuTu').value = lesson.ThuTu || '';
                document.getElementById('editTextMau').value = lesson.TextMau || '';
                document.getElementById('editDiemToiThieu').value = lesson.DiemToiThieu || '';
                document.getElementById('editThoiGianUocTinh').value = lesson.ThoiGianUocTinh || '';
                
                // Hiển thị modal (Bootstrap 4)
                if (typeof bootstrap !== 'undefined') {
                    new bootstrap.Modal(document.getElementById('editLessonModal')).show();
                } else if (window.jQuery && window.jQuery.fn.modal) {
                    jQuery('#editLessonModal').modal('show');
                }
            } else {
                // Nếu không có modal, hiển thị thông tin đơn giản
                showSuccess(`Thông tin bài học ID ${maBaiSpeaking}:
                Tiêu đề: ${lesson.TieuDe || 'N/A'}
                Cấp độ: ${lesson.CapDo || 'N/A'}
                Điểm tối thiểu: ${lesson.DiemToiThieu || 'N/A'}
                Thời gian: ${lesson.ThoiGianUocTinh || 'N/A'} phút`);
            }
        } else {
            showError('Không thể tải thông tin bài học!');
        }
    })
    .catch(error => {
        showError('Có lỗi xảy ra khi tải dữ liệu!');
    });
}

// View lesson details - Vanilla JavaScript
function viewLesson(maBaiSpeaking) {
    // Hiển thị loading
    showSuccess('Đang tải chi tiết bài học...');
    
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_lesson_detail&maBaiSpeaking=' + encodeURIComponent(maBaiSpeaking)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.lesson) {
            const lesson = data.lesson;
            
            // Tạo modal xem chi tiết đẹp
            createViewModal(lesson);
            
        } else {
            showError('Không thể tải thông tin bài học!');
        }
    })
    .catch(error => {
        showError('Có lỗi xảy ra khi tải dữ liệu!');
    });
}

// Tạo modal xem chi tiết đẹp
function createViewModal(lesson) {
    // Xóa modal cũ nếu có
    var oldModal = document.getElementById('viewLessonModal');
    if (oldModal) {
        oldModal.remove();
    }
    
    // Tạo modal HTML
    var modalHTML = `
        <div class="modal fade" id="viewLessonModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-eye"></i> Chi tiết bài học #${lesson.MaBaiSpeaking}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" onclick="closeViewModal()"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong><i class="fas fa-heading"></i> Tiêu đề:</strong>
                                <p class="mb-3">${lesson.TieuDe || 'Chưa có tiêu đề'}</p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-layer-group"></i> Cấp độ:</strong>
                                <span class="badge badge-${lesson.CapDo === 'Beginner' ? 'success' : lesson.CapDo === 'Intermediate' ? 'warning' : 'danger'}">${lesson.CapDo || 'N/A'}</span>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <strong><i class="fas fa-star"></i> Điểm tối thiểu:</strong>
                                <p>${lesson.DiemToiThieu || 'N/A'}</p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-clock"></i> Thời gian ước tính:</strong>
                                <p>${lesson.ThoiGianUocTinh || 'N/A'} phút</p>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <strong><i class="fas fa-align-left"></i> Nội dung bài học:</strong>
                            <div class="border rounded p-3 bg-light" style="max-height: 200px; overflow-y: auto;">
                                ${lesson.NoiDung || 'Chưa có nội dung'}
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <strong><i class="fas fa-file-alt"></i> Văn bản mẫu:</strong>
                            <div class="border rounded p-3 bg-light" style="max-height: 200px; overflow-y: auto;">
                                ${lesson.TextMau || 'Chưa có văn bản mẫu'}
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeViewModal()">
                            <i class="fas fa-times"></i> Đóng
                        </button>
                        <button type="button" class="btn btn-warning" onclick="closeViewModal(); editLesson(${lesson.MaBaiSpeaking})">
                            <i class="fas fa-edit"></i> Chỉnh sửa
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Thêm vào body
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Hiển thị modal
    var modal = document.getElementById('viewLessonModal');
    modal.style.display = 'block';
    modal.classList.add('show');
    
    // Thêm backdrop
    var backdrop = document.createElement('div');
    backdrop.className = 'modal-backdrop fade show';
    backdrop.onclick = closeViewModal;
    document.body.appendChild(backdrop);
    document.body.classList.add('modal-open');
}

// Đóng modal xem chi tiết
function closeViewModal() {
    var modal = document.getElementById('viewLessonModal');
    var backdrop = document.querySelector('.modal-backdrop');
    
    if (modal) {
        modal.classList.remove('show');
        modal.style.display = 'none';
        modal.remove();
    }
    
    if (backdrop) {
        backdrop.remove();
    }
    
    document.body.classList.remove('modal-open');
}

// Delete lesson - Logic đơn giản và chắc chắn (với ít log)
// FUNCTION XÓA MỚI - ĐƠN GIẢN VÀ HIỆU QUẢ
function deleteLesson(maBaiSpeaking) {
    if (!confirm('Bạn có chắc chắn muốn xóa bài này?')) {
        return;
    }
    
    // Tìm hàng cần xóa (đơn giản nhất)
    var targetRow = document.querySelector('tr[data-lesson-id="' + maBaiSpeaking + '"]');
    if (!targetRow) {
        alert('Không tìm thấy bài cần xóa!');
        return;
    }
    
    // Lưu trạng thái ban đầu để khôi phục nếu lỗi
    var originalHTML = targetRow.innerHTML;
    var originalStyle = targetRow.style.cssText;
    
    // Thay đổi giao diện ngay lập tức
    targetRow.style.backgroundColor = '#ffe6e6';
    targetRow.style.opacity = '0.7';
    
    var deleteButton = targetRow.querySelector('.btn-danger');
    if (deleteButton) {
        deleteButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
        deleteButton.disabled = true;
    }
    
    // Gửi request
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=delete_lesson&maBaiSpeaking=' + encodeURIComponent(maBaiSpeaking)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // XÓA THÀNH CÔNG - REMOVE NGAY KHỎI DOM
            targetRow.style.transition = 'all 0.3s ease';
            targetRow.style.transform = 'translateX(-100%)';
            targetRow.style.opacity = '0';
            
            setTimeout(() => {
                // Xóa hàng khỏi DOM
                targetRow.remove();
                
                // Cập nhật lại STT
                updateSTT();
                
                // Kiểm tra bảng trống
                checkEmptyTable();
                
                // Thông báo thành công
                showSuccess('Xóa bài học thành công!');
            }, 300);
            
        } else {
            // LỖI - KHÔI PHỤC TRẠNG THÁI BAN ĐẦU
            targetRow.innerHTML = originalHTML;
            targetRow.style.cssText = originalStyle;
            showError(data.message || 'Có lỗi xảy ra khi xóa bài học!');
        }
    })
    .catch(error => {
        // NETWORK ERROR - KHÔI PHỤC TRẠNG THÁI BAN ĐẦU
        targetRow.innerHTML = originalHTML;
        targetRow.style.cssText = originalStyle;
        showError('Lỗi kết nối! Vui lòng thử lại.');
    });
}

// Helper functions đơn giản
function updateSTT() {
    var rows = document.querySelectorAll('tbody tr[data-lesson-id]');
    rows.forEach((row, index) => {
        var sttCell = row.querySelector('td:first-child');
        if (sttCell) {
            sttCell.textContent = index + 1;
        }
    });
}

function checkEmptyTable() {
    var dataRows = document.querySelectorAll('tbody tr[data-lesson-id]');
    var tbody = document.querySelector('tbody');
    
    if (dataRows.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted">Không có dữ liệu</td></tr>';
    }
}

function showSuccess(message) {
    // Tạo thông báo thành công đơn giản
    var notification = document.createElement('div');
    notification.innerHTML = '<i class="fas fa-check-circle"></i> ' + message;
    notification.style.cssText = `
        position: fixed; top: 20px; right: 20px; z-index: 9999;
        background: #d4edda; border: 1px solid #c3e6cb; color: #155724;
        padding: 15px 20px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        font-weight: 500; min-width: 250px;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}

function showError(message) {
    // Tạo thông báo lỗi đơn giản
    var notification = document.createElement('div');
    notification.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + message;
    notification.style.cssText = `
        position: fixed; top: 20px; right: 20px; z-index: 9999;
        background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24;
        padding: 15px 20px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        font-weight: 500; min-width: 250px;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 4000);
}

// ĐÃ CHUYỂN SANG FUNCTION MỚI Ở TRÊN

// Load lessons for a topic
function loadTopicLessons(topicId) {
    $.ajax({
        url: '',
        type: 'POST',
        data: {
            action: 'get_lessons',
            maChuDe: topicId
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayLessons(topicId, response.lessons);
            } else {
                $(`#lessons-${topicId}`).html('<div class="text-center text-muted">Chưa có bài học nào</div>');
            }
        },
        error: function() {
            $(`#lessons-${topicId}`).html('<div class="text-center text-danger">Lỗi tải dữ liệu</div>');
        }
    });
}

// Display lessons
function displayLessons(topicId, lessons) {
    let html = '';
    if (lessons.length === 0) {
        html = '<div class="text-center text-muted">Chưa có bài học nào</div>';
    } else {
        lessons.forEach((lesson, index) => {
            html += `
                <div class="lesson-item border-left border-primary pl-3 mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${lesson.TieuDe}</strong>
                            <span class="badge badge-${lesson.CapDo === 'Beginner' ? 'success' : (lesson.CapDo === 'Intermediate' ? 'warning' : 'danger')} ml-2">${lesson.CapDo}</span>
                            <br>
                            <small class="text-muted">${lesson.NoiDung || 'Không có mô tả'}</small>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-outline-primary" onclick="editLesson(${lesson.MaBaiSpeaking})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteLesson(${lesson.MaBaiSpeaking}, ${topicId})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
    }
    $(`#lessons-${topicId}`).html(html);
}

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
                // Reload page to show the new lesson
                window.location.reload();
            } else {
                alert(response.message);
            }
        },
        error: function() {
            alert('Có lỗi xảy ra!');
        }
    });
});

// Edit Lesson
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
                // Reload page to show updated lesson
                window.location.reload();
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
function deleteLesson(lessonId, topicId) {
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
                    loadTopicLessons(topicId);
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

// Override window.onerror để bắt và ẩn lỗi jQuery
window.onerror = function(msg, url, lineNo, columnNo, error) {
    // Ẩn các lỗi liên quan đến jQuery conflicts
    if (msg.includes('datepicker') || 
        msg.includes('Deferred') || 
        msg.includes('undefined') && msg.includes('function')) {
// === END JAVASCRIPT ===

</script>

<!-- SIMPLE ADD LESSON SCRIPT -->
<script>
// Global flag to prevent multiple form handlers
let formHandlerAttached = false;

// Handle add lesson form submission
function handleAddLessonForm() {
    const form = document.getElementById('addLessonForm');
    if (!form) return;
    
    // Prevent multiple event listeners on same form
    if (form.hasAttribute('data-handler-attached')) {
        console.log('Form handler already attached, skipping');
        return;
    }
    
    console.log('Attaching form handler to addLessonForm');
    form.setAttribute('data-handler-attached', 'true');
    
    let isSubmitting = false; // Prevent double submission
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        console.log('Form submit triggered, isSubmitting:', isSubmitting);
        
        // Prevent double submission
        if (isSubmitting) {
            console.log('Form already submitting, ignoring duplicate request');
            return;
        }
        
        isSubmitting = true;
        console.log('Starting form submission...');
        
        const formData = new FormData(this);
        formData.append('action', 'add_lesson');
        
        // Show loading
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang thêm...';
        submitBtn.disabled = true;
        
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ Thêm bài học thành công!');
                $('#addLessonModal').modal('hide');
                location.reload(); // Reload to show new lesson
            } else {
                alert('❌ Lỗi: ' + (data.message || 'Không thể thêm bài học'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Lỗi kết nối. Vui lòng thử lại!');
        })
        .finally(() => {
            console.log('Form submission completed, resetting flags');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            isSubmitting = false; // Reset submission flag
        });
    });
}

// Handle edit lesson form submission
function handleEditLessonForm() {
    const form = document.getElementById('editLessonForm');
    if (!form) return;
    
    // Prevent multiple event listeners on same form
    if (form.hasAttribute('data-handler-attached')) {
        console.log('Edit form handler already attached, skipping');
        return;
    }
    
    console.log('Attaching form handler to editLessonForm');
    form.setAttribute('data-handler-attached', 'true');
    
    let isSubmitting = false; // Prevent double submission
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        console.log('Edit form submit triggered, isSubmitting:', isSubmitting);
        
        // Prevent double submission
        if (isSubmitting) {
            console.log('Edit form already submitting, ignoring duplicate request');
            return;
        }
        
        isSubmitting = true;
        console.log('Starting edit form submission...');
        
        const formData = new FormData(this);
        formData.append('action', 'edit_lesson');
        
        // Show loading
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang cập nhật...';
        submitBtn.disabled = true;
        
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Edit form response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Edit form response data:', data);
            if (data.success) {
                alert('✅ Cập nhật bài học thành công!');
                $('#editLessonModal').modal('hide');
                location.reload(); // Reload to show changes
            } else {
                alert('❌ Lỗi: ' + (data.message || 'Không thể cập nhật bài học'));
            }
        })
        .catch(error => {
            console.error('Edit form error:', error);
            alert('❌ Lỗi kết nối. Vui lòng thử lại!');
        })
        .finally(() => {
            console.log('Edit form submission completed, resetting flags');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            isSubmitting = false; // Reset submission flag
        });
    });
}

// Initialize only once when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM ready, initializing form handlers');
    handleAddLessonForm();
    setupSimpleEditForm();
});

// Remove duplicate initialization on modal show
$(document).ready(function() {
    $('#addLessonModal').on('shown.bs.modal', function() {
        console.log('Add modal shown - form handler should already be attached');
        // Don't call handleAddLessonForm() again to avoid duplicate listeners
    });
    
    $('#editLessonModal').on('shown.bs.modal', function() {
        console.log('Edit modal shown - form handler should already be attached');
        // Don't call handleEditLessonForm() again to avoid duplicate listeners
    });
});
</script>

<?php
require_once(__DIR__ . "/Footer.php");
?>