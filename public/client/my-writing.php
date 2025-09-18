<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");

$title = 'Bài Writing đã nộp | ' . $Database->site('TenWeb');

if (empty($_SESSION['account'])) {
    redirect(BASE_URL(''));
}

$maKhoaHoc = $_GET['maKhoaHoc'] ?? '';
$promptId = $_GET['promptId'] ?? '';

if (!$maKhoaHoc) {
    redirect(BASE_URL(''));
}

// Get course info
$khoaHoc = $Database->get_row("SELECT * FROM khoahoc WHERE MaKhoaHoc = '$maKhoaHoc' AND TrangThaiKhoaHoc = 1");
if (!$khoaHoc) {
    redirect(BASE_URL(''));
}

// Check if user is enrolled
$checkDangKy = $Database->get_row("SELECT * FROM dangkykhoahoc WHERE TaiKhoan = '" . $_SESSION['account'] . "' AND MaKhoaHoc = '$maKhoaHoc'");
if (!$checkDangKy) {
    redirect(BASE_URL(''));
}

include_once(__DIR__ . "/header.php");
?>

<link rel="stylesheet" href="<?= BASE_URL('assets/css/writing.css') ?>?id=<?= rand(0, 1000000) ?>">

<style>
/* Specific styles for my-writing page */
body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    margin: 0;
    padding: 0;
}

.container-fluid {
    padding: 0 !important;
    margin: 0 !important;
    max-width: 100% !important;
}

.writing-container {
    padding: 30px 40px !important;
    margin: 0 !important;
    width: 100% !important;
    max-width: none !important;
}

/* Improve prompt items display */
.writing-prompt-item {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 20px;
    padding: 25px;
    margin-bottom: 20px;
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;
}

.writing-prompt-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
}

.writing-prompt-title {
    color: white;
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 15px;
}

.writing-prompt-level {
    padding: 5px 15px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
}

.level-easy { background: #28a745; color: white; }
.level-medium { background: #ffc107; color: #000; }
.level-hard { background: #dc3545; color: white; }

.writing-prompt-meta div {
    color: rgba(255, 255, 255, 0.9);
    font-size: 14px;
    margin-bottom: 5px;
}

.writing-prompt-meta strong {
    color: white;
}
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="writing-container">
                <div class="writing-header">
                    <div>
                        <h1 class="writing-title">Bài Writing đã nộp</h1>
                        <p class="writing-subtitle">Khóa học: <?= $khoaHoc['TenKhoaHoc'] ?></p>
                    </div>
                    <a href="<?= BASE_URL("Page/KhoaHoc/$maKhoaHoc") ?>" class="writing-btn">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>

                <!-- Submissions List -->
                <div class="writing-prompts-container">
                    <div id="submissionsList">
                        <div class="writing-loading">
                            <i class="fas fa-spinner"></i>
                            <p>Đang tải bài đã nộp...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Submission Detail Modal -->
<div class="writing-modal" id="submissionModal">
    <div class="writing-modal-content">
        <button class="writing-modal-close" onclick="closeSubmissionModal()">&times;</button>
        <div id="submissionModalContent"></div>
    </div>
</div>

<script>
let currentCourseId = <?= $maKhoaHoc ?>;
let highlightPromptId = <?= $promptId ?: 'null' ?>;

$(document).ready(function() {
    loadMySubmissions();
});

function loadMySubmissions() {
    console.log('Loading my submissions for course:', currentCourseId);
    
    $.ajax({
        url: '<?= BASE_URL("api/writing-client.php?action=my_submissions") ?>',
        type: 'GET',
        data: {course: currentCourseId},
        dataType: 'json',
        beforeSend: function() {
            console.log('Sending request to API...');
        },
        success: function(data) {
            console.log('✓ API Response:', data);
            
            if (!Array.isArray(data)) {
                console.error('Invalid data format:', data);
                showError('Dữ liệu không hợp lệ');
                return;
            }
            
            let html = '';
            
            if (data.length === 0) {
                html = `
                    <div class="writing-empty">
                        <i class="fas fa-file-alt"></i>
                        <h3>Chưa có bài nào</h3>
                        <p>Bạn chưa nộp bài writing nào cho khóa học này.</p>
                        <a href="<?= BASE_URL("Page/KhoaHoc/$maKhoaHoc") ?>" class="writing-btn">
                            <i class="fas fa-arrow-left"></i> Quay lại khóa học
                        </a>
                    </div>
                `;
            } else {
                data.forEach(submission => {
                    const isGraded = submission.TrangThaiCham === 'Đã chấm';
                    const statusClass = isGraded ? 'status-graded' : 'status-ungraded';
                    const levelClass = submission.MucDo === 'Dễ' ? 'level-easy' : 
                                     submission.MucDo === 'Trung bình' ? 'level-medium' : 'level-hard';
                    
                    const highlightClass = highlightPromptId && submission.MaDeBai == highlightPromptId ? 'bg-warning' : '';
                    
                    html += `
                        <div class="writing-prompt-item ${highlightClass}">
                            <div class="writing-prompt-header">
                                <h4 class="writing-prompt-title">${submission.TieuDe}</h4>
                                <span class="writing-prompt-level ${levelClass}">${submission.MucDo}</span>
                            </div>
                            <div class="writing-prompt-meta mb-3">
                                <div><strong>Chủ đề:</strong> ${submission.TenChuDe}</div>
                                <div><strong>Ngày nộp:</strong> ${new Date(submission.ThoiGianNop).toLocaleDateString('vi-VN')}</div>
                                <div><strong>Số từ:</strong> ${submission.SoTu}</div>
                            </div>
                            <div class="submission-status ${statusClass}">
                                <i class="fas fa-${isGraded ? 'check-circle' : 'clock'}"></i>
                                ${submission.TrangThaiCham}
                                ${isGraded ? ` - Điểm: ${submission.DiemSo}/10` : ''}
                            </div>
                            <div class="text-right mt-3">
                                <button class="writing-btn" onclick="viewSubmissionDetail(${submission.MaBaiViet})">
                                    <i class="fas fa-eye"></i> Xem chi tiết
                                </button>
                            </div>
                        </div>
                    `;
                });
            }
            
            $('#submissionsList').html(html);
            
            // Scroll to highlighted submission if any
            if (highlightPromptId) {
                setTimeout(() => {
                    const highlightedElement = $('.bg-warning')[0];
                    if (highlightedElement) {
                        highlightedElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }, 500);
            }
        },
        error: function(xhr, status, error) {
            console.error('✗ API Error:', error);
            console.error('Status:', status);
            console.error('Response:', xhr.responseText);
            
            showError('Không thể tải danh sách bài đã nộp: ' + error);
        }
    });
}

function showError(message) {
    $('#submissionsList').html(`
        <div class="writing-empty">
            <i class="fas fa-exclamation-triangle text-danger"></i>
            <h3>Lỗi tải dữ liệu</h3>
            <p>${message}</p>
            <button class="writing-btn" onclick="loadMySubmissions()">
                <i class="fas fa-refresh"></i> Thử lại
            </button>
        </div>
    `);
}

function viewSubmissionDetail(submissionId) {
    $.get('<?= BASE_URL("api/writing-client.php?action=get_submission") ?>', {id: submissionId}, function(data) {
        const submission = JSON.parse(data);
        const isGraded = submission.TrangThaiCham === 'Đã chấm';
        
        let html = `
            <div class="writing-form-container">
                <h2 class="writing-form-title">${submission.TieuDe}</h2>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Chủ đề:</strong> ${submission.TenChuDe}
                    </div>
                    <div class="col-md-6">
                        <strong>Mức độ:</strong> 
                        <span class="writing-prompt-level ${submission.MucDo === 'Dễ' ? 'level-easy' : submission.MucDo === 'Trung bình' ? 'level-medium' : 'level-hard'}">
                            ${submission.MucDo}
                        </span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <strong>Đề bài:</strong>
                    <div class="p-3 bg-light rounded">${submission.NoiDungDeBai}</div>
                </div>
                
                <div class="mb-3">
                    <strong>Yêu cầu số từ:</strong> ${submission.GioiHanTu} | 
                    <strong>Số từ thực tế:</strong> ${submission.SoTu}
                </div>
                
                <div class="mb-3">
                    <strong>Bài viết của bạn:</strong>
                    <div class="p-3 border rounded" style="max-height: 300px; overflow-y: auto; background: #f8f9fa;">
                        ${submission.NoiDungBaiViet.replace(/\n/g, '<br>')}
                    </div>
                </div>
                
                <div class="submission-status ${isGraded ? 'status-graded' : 'status-ungraded'} mb-3">
                    <i class="fas fa-${isGraded ? 'check-circle' : 'clock'}"></i>
                    ${submission.TrangThaiCham}
                    <div class="mt-2">
                        <strong>Ngày nộp:</strong> ${new Date(submission.ThoiGianNop).toLocaleDateString('vi-VN', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        })}
                    </div>
                </div>
        `;
        
        if (isGraded) {
            html += `
                <div class="score-container">
                    <h5>Kết quả chấm bài</h5>
                    <div class="score-item">
                        <span class="score-label">Điểm tổng:</span>
                        <span class="score-value">${submission.DiemSo}/10</span>
                    </div>
                    ${submission.DiemNguPhap ? `
                        <div class="score-item">
                            <span class="score-label">Điểm ngữ pháp:</span>
                            <span class="score-value">${submission.DiemNguPhap}/10</span>
                        </div>
                    ` : ''}
                    ${submission.DiemMachLac ? `
                        <div class="score-item">
                            <span class="score-label">Điểm mạch lạc:</span>
                            <span class="score-value">${submission.DiemMachLac}/10</span>
                        </div>
                    ` : ''}
                    ${submission.DiemTuVung ? `
                        <div class="score-item">
                            <span class="score-label">Điểm từ vựng:</span>
                            <span class="score-value">${submission.DiemTuVung}/10</span>
                        </div>
                    ` : ''}
                    ${submission.ThoiGianCham ? `
                        <div class="score-item">
                            <span class="score-label">Ngày chấm:</span>
                            <span class="score-value">${new Date(submission.ThoiGianCham).toLocaleDateString('vi-VN')}</span>
                        </div>
                    ` : ''}
                </div>
            `;
            
            if (submission.NhanXet) {
                html += `
                    <div class="comment-section">
                        <div class="comment-title">Nhận xét từ giáo viên:</div>
                        <div class="comment-content">${submission.NhanXet.replace(/\n/g, '<br>')}</div>
                    </div>
                `;
            }
        }
        
        html += `
                <div class="text-center mt-4">
                    <button class="writing-btn writing-btn-secondary" onclick="closeSubmissionModal()">
                        Đóng
                    </button>
                </div>
            </div>
        `;
        
        $('#submissionModalContent').html(html);
        $('#submissionModal').addClass('active');
    }).fail(function() {
        alert('Không thể tải chi tiết bài viết. Vui lòng thử lại.');
    });
}

function closeSubmissionModal() {
    $('#submissionModal').removeClass('active');
}

// Close modal when clicking outside
$(document).on('click', '.writing-modal', function(e) {
    if (e.target === this) {
        closeSubmissionModal();
    }
});
</script>

<?php include_once(__DIR__ . "/footer.php"); ?>
