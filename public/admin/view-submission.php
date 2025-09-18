<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");

// Lấy ID bài viết
$submissionId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($submissionId <= 0) {
    header("Location: " . BASE_URL("admin/writing-management"));
    exit();
}

$title = 'Xem chi tiết bài viết | ' . $Database->site('TenWeb');
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
                    <h1 class="m-0">Chi tiết bài viết đã chấm</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL('admin/home'); ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL('admin/writing-management'); ?>">Writing Management</a></li>
                        <li class="breadcrumb-item active">Chi tiết bài viết</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            
            <!-- Back Button -->
            <div class="row mb-3">
                <div class="col-12">
                    <a href="<?= BASE_URL('admin/writing-management'); ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>

            <!-- Loading State -->
            <div id="loadingState" class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                            <h4>Đang tải dữ liệu...</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div id="mainContent" style="display: none;">
                
                <!-- Thông tin đề bài và học viên -->
                <div class="row">
                    <!-- Thông tin đề bài -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h3 class="card-title"><i class="fas fa-file-alt"></i> Thông tin đề bài</h3>
                            </div>
                            <div class="card-body" id="promptInfo">
                                <!-- Sẽ được load từ JavaScript -->
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin học viên -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h3 class="card-title"><i class="fas fa-user"></i> Thông tin học viên</h3>
                            </div>
                            <div class="card-body" id="studentInfo">
                                <!-- Sẽ được load từ JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bài viết và kết quả chấm -->
                <div class="row mt-3">
                    <!-- Bài viết -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h3 class="card-title"><i class="fas fa-edit"></i> Bài viết của học viên</h3>
                            </div>
                            <div class="card-body">
                                <div id="essayContent" class="border p-3" style="min-height: 400px; background-color: #f8f9fa; font-size: 16px; line-height: 1.6;">
                                    <!-- Sẽ được load từ JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form chấm điểm -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-warning text-dark">
                                <h3 class="card-title"><i class="fas fa-star"></i> Kết quả chấm bài</h3>
                            </div>
                            <div class="card-body">
                                <form id="gradingForm">
                                    <input type="hidden" id="submissionId" value="<?= $submissionId ?>">
                                    
                                    <div class="form-group">
                                        <label>Điểm tổng (0-10):</label>
                                        <input type="number" class="form-control" id="diemSo" step="0.1" min="0" max="10">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Điểm ngữ pháp (0-10):</label>
                                        <input type="number" class="form-control" id="diemNguPhap" step="0.1" min="0" max="10">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Điểm mạch lạc (0-10):</label>
                                        <input type="number" class="form-control" id="diemMachLac" step="0.1" min="0" max="10">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Điểm từ vựng (0-10):</label>
                                        <input type="number" class="form-control" id="diemTuVung" step="0.1" min="0" max="10">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Nhận xét:</label>
                                        <textarea class="form-control" id="nhanXet" rows="4" placeholder="Nhập nhận xét cho bài viết..."></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Người chấm:</label>
                                        <input type="text" class="form-control" id="nguoiCham" value="<?= $_SESSION['account'] ?? 'admin'; ?>" readonly>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Thời gian chấm:</label>
                                        <div id="thoiGianCham" class="form-control-plaintext">
                                            <!-- Sẽ được cập nhật -->
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-save"></i> Lưu kết quả chấm
                                    </button>
                                    
                                    <button type="button" class="btn btn-secondary btn-block mt-2" onclick="window.location.href='<?= BASE_URL('admin/writing-management'); ?>'">
                                        <i class="fas fa-times"></i> Hủy
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Error State -->
            <div id="errorState" style="display: none;">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="fas fa-exclamation-triangle fa-2x text-danger mb-3"></i>
                                <h4>Có lỗi xảy ra</h4>
                                <p id="errorMessage">Không thể tải dữ liệu bài viết.</p>
                                <a href="<?= BASE_URL('admin/writing-management'); ?>" class="btn btn-primary">
                                    <i class="fas fa-arrow-left"></i> Quay lại
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    const submissionId = <?= $submissionId ?>;
    loadSubmissionDetail(submissionId);
    
    // Handle form submission
    $('#gradingForm').submit(function(e) {
        e.preventDefault();
        saveGradingResult();
    });
    
    // Tự động tính điểm tổng khi thay đổi điểm các tiêu chí
    $('#diemNguPhap, #diemMachLac, #diemTuVung').on('input', function() {
        calculateTotalScore();
    });
});

// Hàm tính điểm tổng
function calculateTotalScore() {
    const diemNguPhap = parseFloat($('#diemNguPhap').val()) || 0;
    const diemMachLac = parseFloat($('#diemMachLac').val()) || 0;
    const diemTuVung = parseFloat($('#diemTuVung').val()) || 0;
    
    // Tính điểm trung bình của 3 tiêu chí
    const diemTong = ((diemNguPhap + diemMachLac + diemTuVung) / 3).toFixed(1);
    
    // Cập nhật vào ô điểm tổng
    $('#diemSo').val(diemTong);
}

function loadSubmissionDetail(submissionId) {
    console.log('Loading submission detail for ID:', submissionId);
    
    $.ajax({
        url: '/webhocngoaingu/api/get-submission-detail.php?id=' + submissionId,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('✓ Submission detail loaded:', response);
            
            if (response.success && response.data) {
                const data = response.data;
                displaySubmissionDetail(data);
                $('#loadingState').hide();
                $('#mainContent').show();
            } else {
                showError(response.error || 'Không thể tải chi tiết bài viết');
            }
        },
        error: function(xhr, status, error) {
            console.error('✗ Submission detail error:', error, xhr.responseText);
            showError('Lỗi tải dữ liệu: ' + error);
        }
    });
}

function displaySubmissionDetail(data) {
    // Hiển thị thông tin đề bài
    $('#promptInfo').html(`
        <p><strong>Tiêu đề:</strong> ${data.TieuDeDeBai}</p>
        <p><strong>Nội dung đề bài:</strong></p>
        <div class="border p-3 bg-light" style="max-height: 200px; overflow-y: auto;">
            ${data.NoiDungDeBai.replace(/\n/g, '<br>')}
        </div>
        <p class="mt-2"><strong>Giới hạn từ:</strong> ${data.GioiHanTu} từ</p>
        <p><strong>Thời gian làm bài:</strong> ${data.ThoiGianLamBai} phút</p>
    `);
    
    // Hiển thị thông tin học viên
    $('#studentInfo').html(`
        <p><strong>Tên hiển thị:</strong> ${data.TenHienThi}</p>
        <p><strong>Tài khoản:</strong> ${data.TaiKhoan}</p>
        <p><strong>Thời gian nộp:</strong> ${data.ThoiGianNop}</p>
        <p><strong>Số từ thực tế:</strong> ${data.SoTu} từ</p>
        <p><strong>Trạng thái:</strong> 
            <span class="badge badge-${data.TrangThaiCham === 'Đã chấm' ? 'success' : 'warning'}">${data.TrangThaiCham}</span>
        </p>
    `);
    
    // Hiển thị bài viết
    $('#essayContent').html(data.NoiDungBaiViet.replace(/\n/g, '<br>'));
    
    // Điền thông tin chấm điểm
    $('#diemSo').val(data.DiemSo || '');
    $('#diemNguPhap').val(data.DiemNguPhap || '');
    $('#diemMachLac').val(data.DiemMachLac || '');
    $('#diemTuVung').val(data.DiemTuVung || '');
    $('#nhanXet').val(data.NhanXet || '');
    
    // Tính điểm tổng sau khi load dữ liệu (nếu chưa có điểm tổng hoặc muốn tự động tính lại)
    if (!data.DiemSo || data.DiemSo == 0) {
        calculateTotalScore();
    }
    
    // Hiển thị thời gian chấm
    if (data.ThoiGianCham) {
        $('#thoiGianCham').text(data.ThoiGianCham);
    } else {
        $('#thoiGianCham').text('Chưa chấm');
    }
}

function saveGradingResult() {
    const formData = {
        submissionId: $('#submissionId').val(),
        diemSo: $('#diemSo').val(),
        diemNguPhap: $('#diemNguPhap').val(),
        diemMachLac: $('#diemMachLac').val(),
        diemTuVung: $('#diemTuVung').val(),
        nhanXet: $('#nhanXet').val(),
        nguoiCham: $('#nguoiCham').val()
    };
    
    console.log('Saving grading result:', formData);
    
    // Disable submit button
    $('#gradingForm button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang lưu...');
    
    $.ajax({
        url: '/webhocngoaingu/api/save-grading-result.php',
        type: 'POST',
        dataType: 'json',
        data: formData,
        success: function(response) {
            console.log('✓ Grading result saved:', response);
            
            if (response.success) {
                toastr.success('Lưu kết quả chấm thành công!');
                
                // Cập nhật thời gian chấm
                const now = new Date();
                $('#thoiGianCham').text(now.toLocaleString('vi-VN'));
                
                // Redirect after 2 seconds
                setTimeout(function() {
                    window.location.href = '/webhocngoaingu/admin/writing-management';
                }, 2000);
                
            } else {
                toastr.error('Lỗi: ' + (response.error || 'Không thể lưu kết quả'));
                $('#gradingForm button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save"></i> Lưu kết quả chấm');
            }
        },
        error: function(xhr, status, error) {
            console.error('✗ Save grading error:', error, xhr.responseText);
            toastr.error('Lỗi lưu dữ liệu: ' + error);
            $('#gradingForm button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save"></i> Lưu kết quả chấm');
        }
    });
}

function showError(message) {
    $('#errorMessage').text(message);
    $('#loadingState').hide();
    $('#errorState').show();
}
</script>

<?php include_once(__DIR__ . "/Footer.php"); ?>
