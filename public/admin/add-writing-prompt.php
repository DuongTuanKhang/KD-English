<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");

$title = 'Thêm đề bài viết | ' . $Database->site('TenWeb');

// Check login with better error handling
if (empty($_SESSION['account'])) {
    echo '<script>alert("Bạn cần đăng nhập để truy cập trang này!"); window.location.href = "' . BASE_URL('') . '";</script>';
    exit;
}

// Get writing topics for dropdown with error handling
try {
    $topics = $Database->get_list("SELECT * FROM writing_topics WHERE TrangThai = 1 ORDER BY TenChuDe");
    if (!$topics) {
        $topics = []; // Default empty array if no topics found
    }
} catch (Exception $e) {
    echo '<script>alert("Lỗi kết nối database: ' . $e->getMessage() . '"); window.history.back();</script>';
    exit;
}

include_once(__DIR__ . "/Header.php");
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Thêm đề bài viết mới</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL('public/admin/') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL('public/admin/WritingManagement.php') ?>">Quản lý Writing</a></li>
                        <li class="breadcrumb-item active">Thêm đề bài</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Thông tin đề bài</h3>
                        </div>
                        
                        <form id="addPromptForm">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="tieuDe">Tiêu đề đề bài <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="tieuDe" name="tieuDe" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="maChuDe">Chủ đề <span class="text-danger">*</span></label>
                                            <select class="form-control" id="maChuDe" name="maChuDe" required>
                                                <option value="">-- Chọn chủ đề --</option>
                                                <?php foreach ($topics as $topic): ?>
                                                <option value="<?= $topic['MaChuDe'] ?>"><?= htmlspecialchars($topic['TenChuDe']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="maKhoaHoc">Khóa học <span class="text-danger">*</span></label>
                                            <select class="form-control" id="maKhoaHoc" name="maKhoaHoc" required>
                                                <option value="1">Khóa học tiếng Anh</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="noiDungDeBai">Nội dung đề bài <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="noiDungDeBai" name="noiDungDeBai" rows="8" required placeholder="Nhập nội dung đề bài, yêu cầu và hướng dẫn cho học viên..."></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="gioiHanTu">Giới hạn từ <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="gioiHanTu" name="gioiHanTu" min="50" max="1000" value="200" required>
                                            <small class="form-text text-muted">Số từ tối thiểu yêu cầu</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="thoiGianLamBai">Thời gian làm bài (phút) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="thoiGianLamBai" name="thoiGianLamBai" min="15" max="120" value="30" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="mucDo">Mức độ <span class="text-danger">*</span></label>
                                            <select class="form-control" id="mucDo" name="mucDo" required>
                                                <option value="">-- Chọn mức độ --</option>
                                                <option value="Dễ">Dễ</option>
                                                <option value="Trung bình">Trung bình</option>
                                                <option value="Khó">Khó</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="nguoiTao">Người tạo</label>
                                    <input type="text" class="form-control" id="nguoiTao" name="nguoiTao" value="<?= $_SESSION['account'] ?>" readonly>
                                </div>
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Lưu đề bài
                                </button>
                                <a href="<?= BASE_URL('public/admin/WritingManagement.php') ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Quay lại
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    $('#addPromptForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            tieuDe: $('#tieuDe').val().trim(),
            maChuDe: $('#maChuDe').val(),
            maKhoaHoc: $('#maKhoaHoc').val(),
            noiDungDeBai: $('#noiDungDeBai').val().trim(),
            gioiHanTu: $('#gioiHanTu').val(),
            thoiGianLamBai: $('#thoiGianLamBai').val(),
            mucDo: $('#mucDo').val(),
            nguoiTao: $('#nguoiTao').val()
        };

        // Validation
        if (!formData.tieuDe || !formData.maChuDe || !formData.noiDungDeBai || !formData.mucDo) {
            alert('Vui lòng điền đầy đủ thông tin bắt buộc!');
            return;
        }

        // Disable submit button
        $('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang lưu...');

        $.ajax({
            url: '/webhocngoaingu/api/add-writing-prompt.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                console.log('Add prompt response:', response);
                if (response.success) {
                    alert('Thêm đề bài thành công!');
                    window.location.href = '/webhocngoaingu/public/admin/WritingManagement.php';
                } else {
                    alert('Lỗi: ' + (response.error || response.message || 'Không thể thêm đề bài'));
                    $('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save"></i> Lưu đề bài');
                }
            },
            error: function(xhr, status, error) {
                console.error('Add prompt error:', error, xhr.responseText);
                alert('Có lỗi xảy ra khi thêm đề bài: ' + error);
                $('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save"></i> Lưu đề bài');
            }
        });
    });
});
</script>

<?php include_once(__DIR__ . "/Footer.php"); ?>
