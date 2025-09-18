<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");
$title = 'Quản lý Writing | ' . $Database->site('TenWeb');

// Load data directly with PHP instead of AJAX
$prompts_api_url = "http://localhost/webhocngoaingu/api/simple-prompts.php";
$prompts_json = @file_get_contents($prompts_api_url);
$prompts_data = $prompts_json ? json_decode($prompts_json, true) : [];

$ungraded_api_url = "http://localhost/webhocngoaingu/api/simple-submissions.php?status=ungraded";
$ungraded_json = @file_get_contents($ungraded_api_url);
$ungraded_response = $ungraded_json ? json_decode($ungraded_json, true) : [];
$ungraded_data = isset($ungraded_response['data']) ? $ungraded_response['data'] : [];

$graded_api_url = "http://localhost/webhocngoaingu/api/simple-submissions.php?status=graded";
$graded_json = @file_get_contents($graded_api_url);
$graded_response = $graded_json ? json_decode($graded_json, true) : [];
$graded_data = isset($graded_response['data']) ? $graded_response['data'] : [];

include_once(__DIR__ . "/Header.php");
include_once(__DIR__ . "/Sidebar.php");
?>

<!-- Add Toastr CSS and JS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Quản lý Writing</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL('admin/home'); ?>">Home</a></li>
                        <li class="breadcrumb-item active">Writing Management</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Tab Navigation -->
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs" id="writingTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="prompts-tab" data-toggle="tab" href="#prompts" role="tab">
                                <i class="fas fa-plus"></i> Thêm bài viết
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="ungraded-tab" data-toggle="tab" href="#ungraded" role="tab">
                                <i class="fas fa-clock"></i> Bài chưa chấm
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="graded-tab" data-toggle="tab" href="#graded" role="tab">
                                <i class="fas fa-check"></i> Đã chấm
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="writingTabsContent">
                        <!-- Prompts Tab - Thêm bài viết -->
                        <div class="tab-pane fade show active" id="prompts" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4>Quản lý đề bài viết</h4>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-primary" onclick="addNewPrompt()">
                                        <i class="fas fa-plus"></i> Thêm đề bài
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped" id="promptsTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Khóa học</th>
                                            <th>Chủ đề</th>
                                            <th>Tiêu đề</th>
                                            <th>Thời gian (phút)</th>
                                            <th>Giới hạn từ</th>
                                            <th>Mức độ</th>
                                            <th>Số bài nộp</th>
                                            <th>Trạng thái</th>
                                            <th>Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($prompts_data) && is_array($prompts_data)): ?>
                                            <?php foreach ($prompts_data as $prompt): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($prompt['MaDeBai']) ?></td>
                                                <td><?= htmlspecialchars($prompt['ten_khoa_hoc']) ?></td>
                                                <td><?= htmlspecialchars($prompt['ten_chu_de']) ?></td>
                                                <td><?= htmlspecialchars($prompt['TieuDe']) ?></td>
                                                <td><?= htmlspecialchars($prompt['ThoiGianLamBai']) ?> phút</td>
                                                <td><?= htmlspecialchars($prompt['GioiHanTu']) ?> từ</td>
                                                <td><span class="badge badge-primary"><?= htmlspecialchars($prompt['MucDo']) ?></span></td>
                                                <td><span class="badge badge-info"><?= htmlspecialchars($prompt['SoBaiNop'] ?? 0) ?></span></td>
                                                <td><span class="badge badge-success">Hoạt động</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-info" onclick="viewPrompt(<?= $prompt['MaDeBai'] ?>)" title="Xem chi tiết">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-warning" onclick="editPrompt(<?= $prompt['MaDeBai'] ?>)" title="Chỉnh sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="deletePrompt(<?= $prompt['MaDeBai'] ?>)" title="Xóa">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="10" class="text-center">Không có đề bài nào</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Ungraded Submissions Tab - Bài chưa chấm -->
                        <div class="tab-pane fade" id="ungraded" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4>Bài nộp chưa chấm</h4>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped" id="ungradedTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Hành động</th>
                                            <th>Học viên</th>
                                            <th>Đề bài</th>
                                            <th>Ngày nộp</th>
                                            <th>Số từ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($ungraded_data) && is_array($ungraded_data)): ?>
                                            <?php foreach ($ungraded_data as $sub): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($sub['MaBaiViet']) ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary" onclick="gradeSubmission(<?= $sub['MaBaiViet'] ?>)">
                                                        <i class="fas fa-check"></i> Chấm bài
                                                    </button>
                                                </td>
                                                <td><?= htmlspecialchars($sub['TenHienThi'] ?? $sub['TaiKhoan']) ?></td>
                                                <td><?= htmlspecialchars($sub['TieuDe']) ?></td>
                                                <td><?= htmlspecialchars($sub['ThoiGianNop']) ?></td>
                                                <td><?= htmlspecialchars($sub['SoTu']) ?> từ</td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="6" class="text-center">Không có bài chưa chấm</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Graded Submissions Tab - Đã chấm -->
                        <div class="tab-pane fade" id="graded" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4>Bài nộp đã chấm</h4>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped" id="gradedTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Hành động</th>
                                            <th>Học viên</th>
                                            <th>Đề bài</th>
                                            <th>Ngày nộp</th>
                                            <th>Điểm số</th>
                                            <th>Ngày chấm</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($graded_data) && is_array($graded_data)): ?>
                                            <?php foreach ($graded_data as $sub): ?>
                                            <?php 
                                                // Format ngày giờ
                                                $formattedDate = 'Chưa có';
                                                if (!empty($sub['ThoiGianCham'])) {
                                                    $date = new DateTime($sub['ThoiGianCham']);
                                                    $formattedDate = $date->format('d/m/Y H:i');
                                                }
                                            ?>
                                            <tr>
                                                <td><?= htmlspecialchars($sub['MaBaiViet']) ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-success" onclick="viewSubmission(<?= $sub['MaBaiViet'] ?>)">
                                                        <i class="fas fa-eye"></i> Xem
                                                    </button>
                                                </td>
                                                <td><?= htmlspecialchars($sub['TenHienThi'] ?? $sub['TaiKhoan']) ?></td>
                                                <td><?= htmlspecialchars($sub['TieuDe']) ?></td>
                                                <td><?= htmlspecialchars($sub['ThoiGianNop']) ?></td>
                                                <td><span class="badge badge-success"><?= htmlspecialchars($sub['DiemSo'] ?? 0) ?>/10</span></td>
                                                <td><?= htmlspecialchars($formattedDate) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="7" class="text-center">Không có bài đã chấm</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- View Prompt Modal -->
<div class="modal fade" id="viewPromptModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Chi tiết đề bài</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="viewPromptContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Prompt Modal -->
<div class="modal fade" id="editPromptModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Chỉnh sửa đề bài</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="editPromptForm">
                <div class="modal-body">
                    <input type="hidden" id="editPromptId">
                    
                    <div class="form-group">
                        <label>Tiêu đề:</label>
                        <input type="text" class="form-control" id="editTieuDe" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Nội dung đề bài:</label>
                        <textarea class="form-control" id="editNoiDung" rows="4" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Mức độ:</label>
                                <select class="form-control" id="editMucDo" required>
                                    <option value="Dễ">Dễ</option>
                                    <option value="Trung bình">Trung bình</option>
                                    <option value="Khó">Khó</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Giới hạn từ:</label>
                                <input type="number" class="form-control" id="editGioiHanTu" min="50" max="1000" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Thời gian (phút):</label>
                                <input type="number" class="form-control" id="editThoiGian" min="5" max="120" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Configure toastr
toastr.options = {
    "closeButton": true,
    "debug": false,
    "newestOnTop": true,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "preventDuplicates": false,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
};

// GLOBAL FUNCTIONS - Available everywhere
function simpleLoadPrompts() {
    console.log('Loading prompts with simple API...');
    console.log('Request URL: /webhocngoaingu/api/simple-prompts.php');
    
    $.ajax({
        url: 'http://localhost/webhocngoaingu/api/simple-prompts.php',
        type: 'GET',
        dataType: 'json',
        crossDomain: true,
        beforeSend: function() {
            console.log('Sending AJAX request for prompts...');
            $('#promptsTable tbody').html('<tr><td colspan="10" class="text-center"><i class="fas fa-spinner fa-spin"></i> Đang tải...</td></tr>');
        },
        success: function(data) {
            console.log('✓ Prompts loaded:', data);
            let html = '';
            if (data && Array.isArray(data) && data.length > 0) {
                data.forEach(function(prompt) {
                    html += `<tr>
                        <td>${prompt.MaDeBai}</td>
                        <td>${prompt.ten_khoa_hoc}</td>
                        <td>${prompt.ten_chu_de}</td>
                        <td>${prompt.TieuDe}</td>
                        <td>${prompt.ThoiGianLamBai} phút</td>
                        <td>${prompt.GioiHanTu} từ</td>
                        <td><span class="badge badge-primary">${prompt.MucDo}</span></td>
                        <td><span class="badge badge-info">${prompt.SoBaiNop || 0}</span></td>
                        <td><span class="badge badge-success">Hoạt động</span></td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="viewPrompt(${prompt.MaDeBai})" title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="editPrompt(${prompt.MaDeBai})" title="Chỉnh sửa">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deletePrompt(${prompt.MaDeBai})" title="Xóa">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>`;
                });
                $('#promptsTable tbody').html(html);
                console.log('✓ Prompts table updated with', data.length, 'items');
            } else {
                $('#promptsTable tbody').html('<tr><td colspan="10" class="text-center">Không có đề bài nào</td></tr>');
                console.log('No prompts data');
            }
        },
        error: function(xhr, status, error) {
            console.error('✗ Prompts error:', error, xhr.responseText);
            console.error('Status:', xhr.status, 'Ready state:', xhr.readyState);
            $('#promptsTable tbody').html('<tr><td colspan="10" class="text-center text-danger">Lỗi tải dữ liệu: ' + error + ' (Status: ' + xhr.status + ')</td></tr>');
        }
    });
}

function simpleLoadUngraded() {
    console.log('Loading ungraded with simple API...');
    console.log('Request URL: /webhocngoaingu/api/simple-submissions.php?status=ungraded');
    
    $.ajax({
        url: 'http://localhost/webhocngoaingu/api/simple-submissions.php?status=ungraded',
        type: 'GET',
        dataType: 'json',
        crossDomain: true,
        beforeSend: function() {
            console.log('Sending AJAX request for ungraded...');
            $('#ungradedTable tbody').html('<tr><td colspan="6" class="text-center"><i class="fas fa-spinner fa-spin"></i> Đang tải...</td></tr>');
        },
        success: function(response) {
            console.log('✓ Ungraded loaded:', response);
            const data = response.data || [];
            let html = '';
            if (data && Array.isArray(data) && data.length > 0) {
                data.forEach(function(sub) {
                    html += `<tr>
                        <td>${sub.MaBaiViet}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="gradeSubmission(${sub.MaBaiViet})">
                                <i class="fas fa-check"></i> Chấm bài
                            </button>
                        </td>
                        <td>${sub.TenHienThi || sub.TaiKhoan}</td>
                        <td>${sub.TieuDe}</td>
                        <td>${sub.ThoiGianNop}</td>
                        <td>${sub.SoTu} từ</td>
                    </tr>`;
                });
                $('#ungradedTable tbody').html(html);
                console.log('✓ Ungraded table updated with', data.length, 'items');
            } else {
                $('#ungradedTable tbody').html('<tr><td colspan="6" class="text-center">Không có bài chưa chấm</td></tr>');
                console.log('No ungraded submissions');
            }
        },
        error: function(xhr, status, error) {
            console.error('✗ Ungraded error:', error, xhr.responseText);
            $('#ungradedTable tbody').html('<tr><td colspan="6" class="text-center text-danger">Lỗi tải dữ liệu: ' + error + '</td></tr>');
        }
    });
}

function simpleLoadGraded() {
    console.log('Loading graded with simple API...');
    console.log('Request URL: /webhocngoaingu/api/simple-submissions.php?status=graded');
    
    $.ajax({
        url: 'http://localhost/webhocngoaingu/api/simple-submissions.php?status=graded',
        type: 'GET',
        dataType: 'json',
        crossDomain: true,
        beforeSend: function() {
            console.log('Sending AJAX request for graded...');
            $('#gradedTable tbody').html('<tr><td colspan="7" class="text-center"><i class="fas fa-spinner fa-spin"></i> Đang tải...</td></tr>');
        },
        success: function(response) {
            console.log('✓ Graded loaded:', response);
            const data = response.data || [];
            let html = '';
            if (data && Array.isArray(data) && data.length > 0) {
                data.forEach(function(sub) {
                    // Format ngày giờ
                    let formattedDate = 'Chưa có';
                    if (sub.ThoiGianCham) {
                        const date = new Date(sub.ThoiGianCham);
                        formattedDate = date.toLocaleString('vi-VN');
                    }
                    
                    html += `<tr>
                        <td>${sub.MaBaiViet}</td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="viewSubmission(${sub.MaBaiViet})">
                                <i class="fas fa-eye"></i> Xem
                            </button>
                        </td>
                        <td>${sub.TenHienThi || sub.TaiKhoan}</td>
                        <td>${sub.TieuDe}</td>
                        <td>${sub.ThoiGianNop}</td>
                        <td><span class="badge badge-success">${sub.DiemSo || 0}/10</span></td>
                        <td>${formattedDate}</td>
                    </tr>`;
                });
                $('#gradedTable tbody').html(html);
                console.log('✓ Graded table updated with', data.length, 'items');
            } else {
                $('#gradedTable tbody').html('<tr><td colspan="7" class="text-center">Không có bài đã chấm</td></tr>');
                console.log('No graded submissions');
            }
        },
        error: function(xhr, status, error) {
            console.error('✗ Graded error:', error, xhr.responseText);
            $('#gradedTable tbody').html('<tr><td colspan="7" class="text-center text-danger">Lỗi tải dữ liệu: ' + error + '</td></tr>');
        }
    });
}

// Function để chuyển đến trang chấm bài
function gradeSubmission(submissionId) {
    if (!submissionId) {
        toastr.error('ID bài nộp không hợp lệ!');
        return;
    }
    
    console.log('Redirecting to grade submission:', submissionId);
    const gradeUrl = '/webhocngoaingu/public/admin/grade-submission-fixed.php?id=' + submissionId;
    window.location.href = gradeUrl;
}

function viewSubmission(submissionId) {
    console.log('View submission:', submissionId);
    
    // Chuyển hướng đến trang xem chi tiết
    window.location.href = '/webhocngoaingu/admin/view-submission?id=' + submissionId;
}

// Document ready handler
$(document).ready(function() {
    console.log('Document ready - initializing...');
    
    // DON'T auto-load prompts since PHP already loaded them
    // simpleLoadPrompts(); // REMOVED - Let PHP handle initial data
    
    // Only load via AJAX if PHP didn't load data
    const ungradedRows = $('#ungradedTable tbody tr').length;
    const gradedRows = $('#gradedTable tbody tr').length;
    
    console.log('Current ungraded rows:', ungradedRows);
    console.log('Current graded rows:', gradedRows);
    
    // Only load if no data or just "no data" message
    if (ungradedRows === 0 || $('#ungradedTable tbody').text().includes('Không có bài chưa chấm')) {
        console.log('Loading ungraded via AJAX...');
        simpleLoadUngraded();
    } else {
        console.log('Using PHP-rendered ungraded data');
    }
    
    if (gradedRows === 0 || $('#gradedTable tbody').text().includes('Không có bài đã chấm')) {
        console.log('Loading graded via AJAX...');
        simpleLoadGraded();
    } else {
        console.log('Using PHP-rendered graded data');
    }
    
    // Edit prompt form submission
    $('#editPromptForm').submit(function(e) {
        e.preventDefault();
        
        console.log('Form submitted!');
        
        // Get form data
        const promptId = $('#editPromptId').val();
        const formData = {
            promptId: promptId,
            tieuDe: $('#editTieuDe').val().trim(),
            noiDungDeBai: $('#editNoiDung').val().trim(),
            mucDo: $('#editMucDo').val(),
            gioiHanTu: parseInt($('#editGioiHanTu').val()),
            thoiGianLamBai: parseInt($('#editThoiGian').val())
        };
        
        console.log('Form data:', formData);
        console.log('Title:', formData.tieuDe, 'Length:', formData.tieuDe ? formData.tieuDe.length : 0);
        console.log('Content:', formData.noiDungDeBai, 'Length:', formData.noiDungDeBai ? formData.noiDungDeBai.length : 0);
        console.log('ID:', formData.promptId, 'Type:', typeof formData.promptId);
        
        // Validation
        if (!formData.promptId || formData.promptId === '' || formData.promptId === 'undefined') {
            console.log('Validation failed - missing ID');
            toastr.warning('Thiếu ID đề bài');
            return;
        }
        
        if (!formData.tieuDe || !formData.noiDungDeBai) {
            console.log('Validation failed - empty fields');
            toastr.warning('Vui lòng điền đầy đủ thông tin');
            return;
        }
        
        if (formData.gioiHanTu < 50 || formData.gioiHanTu > 1000) {
            toastr.warning('Giới hạn từ phải từ 50 đến 1000');
            return;
        }
        
        if (formData.thoiGianLamBai < 5 || formData.thoiGianLamBai > 120) {
            toastr.warning('Thời gian phải từ 5 đến 120 phút');
            return;
        }
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Đang lưu...').prop('disabled', true);
        
        // Send update request
        $.ajax({
            url: '/webhocngoaingu/api/update-prompt.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                promptId: formData.promptId,
                tieuDe: formData.tieuDe,
                noiDungDeBai: formData.noiDungDeBai,
                gioiHanTu: formData.gioiHanTu,
                mucDo: formData.mucDo,
                thoiGianLamBai: formData.thoiGianLamBai
            }),
            beforeSend: function() {
                console.log('Sending AJAX request to update prompt...');
                console.log('Request data:', JSON.stringify(formData));
            },
            success: function(response) {
                console.log('Update response:', response);
                
                if (response.success) {
                    toastr.success(response.message || 'Cập nhật đề bài thành công!');
                    $('#editPromptModal').modal('hide');
                    
                    // Reload the prompts immediately
                    simpleLoadPrompts();
                } else {
                    toastr.error(response.message || 'Có lỗi xảy ra khi cập nhật');
                }
                
                // Show debug info in console
                if (response.debug) {
                    console.log('Debug info:', response.debug);
                }
            },
            error: function(xhr, status, error) {
                console.error('Update error details:');
                console.error('- Status:', status);
                console.error('- Error:', error);
                console.error('- Response text:', xhr.responseText);
                console.error('- Status code:', xhr.status);
                
                try {
                    const response = JSON.parse(xhr.responseText);
                    toastr.error(response.message || 'Có lỗi xảy ra khi cập nhật');
                    
                    // Show debug info if available
                    if (response.debug) {
                        console.log('Error debug info:', response.debug);
                    }
                } catch(e) {
                    toastr.error('Có lỗi xảy ra khi cập nhật: ' + error);
                }
            },
            complete: function() {
                // Reset button state
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
});

// GLOBAL FUNCTIONS for button actions
function viewPrompt(promptId) {
    console.log('View prompt:', promptId);
    
    // Load prompt details using the simple API
    $.ajax({
        url: '/webhocngoaingu/api/simple-prompts.php',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            const prompt = data.find(p => p.MaDeBai == promptId);
            if (prompt) {
                let content = `
                    <h5>${prompt.TieuDe}</h5>
                    <p><strong>Nội dung:</strong> ${prompt.NoiDungDeBai}</p>
                    <p><strong>Mức độ:</strong> ${prompt.MucDo}</p>
                    <p><strong>Giới hạn từ:</strong> ${prompt.GioiHanTu} từ</p>
                    <p><strong>Thời gian:</strong> ${prompt.ThoiGianLamBai} phút</p>
                `;
                
                $('#viewPromptContent').html(content);
                $('#viewPromptModal').modal('show');
            }
        },
        error: function() {
            toastr.error('Không thể tải thông tin đề bài');
        }
    });
}

function editPrompt(promptId) {
    console.log('Edit prompt function called with ID:', promptId);
    
    if (!promptId) {
        toastr.error('ID đề bài không hợp lệ');
        return;
    }
    
    // Try to get data from current table row first (faster)
    const row = $(`button[onclick="editPrompt(${promptId})"]`).closest('tr');
    if (row.length > 0) {
        // Get data directly from table row
        const cells = row.find('td');
        if (cells.length >= 8) {
            const promptData = {
                MaDeBai: promptId,
                TieuDe: cells.eq(3).text().trim(),
                NoiDungDeBai: 'Loading...', // Will get from API
                MucDo: cells.eq(6).text().trim(),
                GioiHanTu: parseInt(cells.eq(5).text().replace(/\D/g, '')),
                ThoiGianLamBai: parseInt(cells.eq(4).text().replace(/\D/g, ''))
            };
            
            // Fill form with table data first
            $('#editPromptId').val(promptData.MaDeBai);
            $('#editTieuDe').val(promptData.TieuDe);
            $('#editMucDo').val(promptData.MucDo);
            $('#editGioiHanTu').val(promptData.GioiHanTu);
            $('#editThoiGian').val(promptData.ThoiGianLamBai);
            
            console.log('Pre-filled form with table data:', promptData);
            
            // Show modal immediately
            $('#editPromptModal').modal('show');
            
            // Then load full content from simple API
            $.ajax({
                url: '/webhocngoaingu/api/simple-prompts.php',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    const prompt = data.find(p => p.MaDeBai == promptId);
                    if (prompt) {
                        $('#editNoiDung').val(prompt.NoiDungDeBai);
                        console.log('Loaded full content from API:', prompt);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading full prompt data:', error);
                    $('#editNoiDung').val('Nội dung đề bài...');
                }
            });
            return;
        }
    }
    
    // Fallback: Load from simple API if table method fails
    $.ajax({
        url: '/webhocngoaingu/api/simple-prompts.php',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            console.log('API response:', data);
            const prompt = data.find(p => p.MaDeBai == promptId);
            console.log('Found prompt:', prompt);
            
            if (prompt) {
                // Fill the edit form
                $('#editPromptId').val(prompt.MaDeBai);
                $('#editTieuDe').val(prompt.TieuDe);
                $('#editNoiDung').val(prompt.NoiDungDeBai);
                $('#editMucDo').val(prompt.MucDo);
                $('#editGioiHanTu').val(prompt.GioiHanTu);
                $('#editThoiGian').val(prompt.ThoiGianLamBai);
                
                console.log('Loaded prompt data:', prompt);
                
                // Show the modal
                $('#editPromptModal').modal('show');
            } else {
                toastr.error('Không tìm thấy đề bài');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading prompt:', error);
            toastr.error('Không thể tải thông tin đề bài');
        }
    });
}

function deletePrompt(promptId) {
    console.log('Delete prompt called with ID:', promptId);
    
    if (!promptId || promptId === 'undefined' || promptId === '') {
        toastr.error('ID đề bài không hợp lệ');
        return;
    }
    
    if (confirm('Bạn có chắc chắn muốn xóa đề bài này?')) {
        console.log('Deleting prompt:', promptId);
        
        $.ajax({
            url: '/webhocngoaingu/api/delete-prompt.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ promptId: parseInt(promptId) }),
            success: function(response) {
                console.log('Delete response:', response);
                if (response.success) {
                    toastr.success('Xóa đề bài thành công!');
                    // Reload prompts
                    simpleLoadPrompts();
                } else {
                    toastr.error(response.message || 'Có lỗi xảy ra khi xóa');
                }
            },
            error: function(xhr, status, error) {
                console.error('Delete error:', xhr.responseText);
                try {
                    const response = JSON.parse(xhr.responseText);
                    toastr.error(response.message || 'Có lỗi xảy ra khi xóa');
                } catch(e) {
                    toastr.error('Có lỗi xảy ra khi xóa');
                }
            }
        });
    }
}

function addNewPrompt() {
    console.log('Redirecting to add new prompt page');
    window.location.href = '/webhocngoaingu/admin/add-writing-prompt';
}

function gradeSubmission(submissionId) {
    if (!submissionId) {
        toastr.error('ID bài nộp không hợp lệ!');
        return;
    }
    
    console.log('Redirecting to grade submission:', submissionId);
    const gradeUrl = '/webhocngoaingu/public/admin/grade-submission-fixed.php?id=' + submissionId;
    window.location.href = gradeUrl;
}

function viewSubmission(submissionId) {
    console.log('View submission:', submissionId);
    
    // Chuyển hướng đến trang xem chi tiết
    window.location.href = '/webhocngoaingu/admin/view-submission?id=' + submissionId;
}

// Make sure functions are globally available
window.viewPrompt = viewPrompt;
window.editPrompt = editPrompt;
window.deletePrompt = deletePrompt;
window.addNewPrompt = addNewPrompt;
window.gradeSubmission = gradeSubmission;
window.viewSubmission = viewSubmission;

</script>
<?php include_once(__DIR__ . "/Footer.php"); ?>
