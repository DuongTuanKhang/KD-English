<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");

$submissionId = $_GET['id'] ?? 0;
if (!$submissionId) {
    header("Location: /webhocngoaingu/public/admin/WritingManagement.php");
    exit;
}

// Lấy thông tin bài nộp (không JOIN để tránh lỗi)
$submission = $Database->get_row("SELECT * FROM writing_submissions WHERE MaBaiViet = '$submissionId'");

if (!$submission) {
    echo "<h3>Không tìm thấy bài nộp</h3>";
    echo "<p>ID: $submissionId</p>";
    echo "<a href='/webhocngoaingu/public/admin/WritingManagement.php'>← Quay lại</a>";
    exit;
}

// Debug: In ra thông tin để kiểm tra
// echo "<pre>Submission data: " . print_r($submission, true) . "</pre>";

// Lấy thông tin đề bài riêng
$prompt = null;
if ($submission['MaDeBai']) {
    $prompt = $Database->get_row("SELECT * FROM writing_prompts WHERE MaDeBai = '" . $submission['MaDeBai'] . "'");
}

// Lấy thông tin user riêng
$user = $Database->get_row("SELECT * FROM nguoidung WHERE TaiKhoan = '" . $submission['TaiKhoan'] . "'");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chấm bài - <?= htmlspecialchars($submission['TieuDe'] ?? 'Không có tiêu đề') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .grading-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .submission-content {
            background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid #e3e6f0;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            font-family: 'Times New Roman', serif;
            line-height: 1.8;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        
        .submission-content:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }
        
        .prompt-section {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border-left: 4px solid #2196f3;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 3px 10px rgba(33, 150, 243, 0.15);
            transition: all 0.3s ease;
        }
        
        .prompt-section:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 20px rgba(33, 150, 243, 0.25);
        }
        
        .grading-form {
            background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid #e3e6f0;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        
        .grading-form:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }
        
        .word-count {
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
            border: 1px solid #ffcc80;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 3px 10px rgba(255, 152, 0, 0.15);
            transition: all 0.3s ease;
        }
        
        .word-count:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(255, 152, 0, 0.25);
        }
        
        .criteria-section {
            background: linear-gradient(145deg, #ffffff 0%, #f1f3f4 100%);
            border: 1px solid #e8eaed;
            border-radius: 10px;
            padding: 18px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .criteria-section:hover {
            border-color: #4285f4;
            box-shadow: 0 4px 12px rgba(66, 133, 244, 0.15);
            transform: translateY(-1px);
        }
        
        .criteria-section::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: linear-gradient(to bottom, #4285f4, #34a853);
            border-radius: 3px 0 0 3px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .criteria-section:hover::before {
            opacity: 1;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-bottom: none;
            padding: 15px 20px;
        }
        
        .card-body {
            background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #4285f4;
            box-shadow: 0 0 0 0.2rem rgba(66, 133, 244, 0.25);
            transform: scale(1.02);
        }
        
        .btn {
            border-radius: 8px;
            padding: 12px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        }
        
        .btn-secondary:hover {
            background: linear-gradient(135deg, #5a6268 0%, #3d4043 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
        }
        
        .score-input {
            width: 80px;
            text-align: center;
        }
        
        .back-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
        }
        
        .text-primary {
            color: #4285f4 !important;
        }
        
        .text-success {
            color: #34a853 !important;
        }
        
        .text-warning {
            color: #fbbc04 !important;
        }
        
        .fw-bold {
            font-weight: 700 !important;
        }
        
        /* Hiệu ứng cho icon */
        .fas, .far {
            transition: transform 0.3s ease;
        }
        
        .card-header:hover .fas,
        .criteria-section:hover .fas,
        .btn:hover .fas {
            transform: scale(1.1);
        }
        
        /* Animation cho trang */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .grading-container > .row > div {
            animation: fadeInUp 0.6s ease forwards;
        }
        
        .grading-container > .row > div:nth-child(2) {
            animation-delay: 0.1s;
        }
    </style>
</head>
<body>
    <!-- Nút quay lại -->
    <a href="/webhocngoaingu/public/admin/WritingManagement.php" class="btn btn-secondary back-btn">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>

    <div class="grading-container">
        <div class="row">
            <!-- Cột trái: Đề bài và bài làm -->
            <div class="col-md-8">
                <!-- Thông tin đề bài -->
                <div class="prompt-section">
                    <h4><i class="fas fa-question-circle"></i> Đề bài: <?= htmlspecialchars($prompt['TieuDe'] ?? $submission['TieuDe'] ?? 'Không có tiêu đề') ?></h4>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($prompt['MoTa'] ?? 'Không có mô tả đề bài')) ?></p>
                    <small class="text-muted">
                        <i class="fas fa-clock"></i> Thời gian: <?= $prompt['GioiHanThoiGian'] ?? 30 ?> phút |
                        <i class="fas fa-pen"></i> Giới hạn: <?= $prompt['GioiHanSoTu'] ?? 250 ?> từ
                    </small>
                </div>

                <!-- Thông tin học viên -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5><i class="fas fa-user"></i> Thông tin học viên</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Tên:</strong> <?= htmlspecialchars($user['TenHienThi'] ?? $submission['TaiKhoan']) ?><br>
                                <strong>Email:</strong> <?= htmlspecialchars($user['Email'] ?? 'N/A') ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Thời gian nộp:</strong> <?= date('d/m/Y H:i', strtotime($submission['ThoiGianNop'])) ?><br>
                                <strong>Số từ:</strong> <span class="text-primary fw-bold"><?= $submission['SoTu'] ?></span> từ
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bài làm của học viên -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-file-alt"></i> Bài làm của học viên</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="submission-content">
                            <?php 
                            // Thử nhiều tên cột có thể có
                            $content = '';
                            if (!empty($submission['NoiDung'])) {
                                $content = $submission['NoiDung'];
                            } elseif (!empty($submission['NoiDungBaiViet'])) {
                                $content = $submission['NoiDungBaiViet'];
                            } elseif (!empty($submission['BaiViet'])) {
                                $content = $submission['BaiViet'];
                            } elseif (!empty($submission['Content'])) {
                                $content = $submission['Content'];
                            } elseif (!empty($submission['Text'])) {
                                $content = $submission['Text'];
                            }
                            
                            if ($content): 
                            ?>
                                <?= nl2br(htmlspecialchars($content)) ?>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <strong>Không tìm thấy nội dung bài làm!</strong><br>
                                    <small>Debug info: <?= implode(', ', array_keys($submission)) ?></small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cột phải: Form chấm điểm -->
            <div class="col-md-4">
                <!-- Thống kê từ -->
                <div class="word-count">
                    <h6><i class="fas fa-chart-bar"></i> Thống kê</h6>
                    <div class="d-flex justify-content-between">
                        <span>Số từ hiện tại:</span>
                        <span class="fw-bold text-primary"><?= $submission['SoTu'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Yêu cầu:</span>
                        <span class="fw-bold"><?= $prompt['GioiHanSoTu'] ?? 250 ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Tỷ lệ:</span>
                        <span class="fw-bold <?= $submission['SoTu'] >= ($prompt['GioiHanSoTu'] ?? 250) ? 'text-success' : 'text-warning' ?>">
                            <?= round(($submission['SoTu'] / ($prompt['GioiHanSoTu'] ?? 250)) * 100, 1) ?>%
                        </span>
                    </div>
                </div>

                <!-- Form chấm điểm -->
                <div class="grading-form">
                    <h5><i class="fas fa-star"></i> Chấm điểm bài viết</h5>
                    
                    <form id="gradingForm">
                        <input type="hidden" id="submissionId" value="<?= $submissionId ?>">
                        
                        <!-- Điểm tổng -->
                        <div class="criteria-section">
                            <label>Điểm tổng (0-10):</label>
                            <input type="number" class="form-control" id="diemSo" step="0.1" min="0" max="10" value="<?= $submission['DiemSo'] ?? 0 ?>">
                        </div>

                        <!-- Điểm ngữ pháp -->
                        <div class="criteria-section">
                            <label>Điểm ngữ pháp (0-10):</label>
                            <input type="number" class="form-control" id="diemNguPhap" step="0.1" min="0" max="10" value="<?= $submission['DiemNguPhap'] ?? 0 ?>">
                        </div>

                        <!-- Điểm mạch lạc -->
                        <div class="criteria-section">
                            <label>Điểm mạch lạc (0-10):</label>
                            <input type="number" class="form-control" id="diemMachLac" step="0.1" min="0" max="10" value="<?= $submission['DiemMachLac'] ?? 0 ?>">
                        </div>

                        <!-- Điểm từ vựng -->
                        <div class="criteria-section">
                            <label>Điểm từ vựng (0-10):</label>
                            <input type="number" class="form-control" id="diemTuVung" step="0.1" min="0" max="10" value="<?= $submission['DiemTuVung'] ?? 0 ?>">
                        </div>

                        <!-- Nhận xét -->
                        <div class="criteria-section">
                            <label>Nhận xét:</label>
                            <textarea class="form-control" id="nhanXet" rows="4" placeholder="Nhập nhận xét cho bài viết..."><?= htmlspecialchars($submission['NhanXet'] ?? '') ?></textarea>
                        </div>

                        <!-- Nút hành động -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> Lưu kết quả chấm
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Hàm tính điểm tổng tự động
        function calculateTotalScore() {
            const diemNguPhap = parseFloat($('#diemNguPhap').val()) || 0;
            const diemMachLac = parseFloat($('#diemMachLac').val()) || 0;
            const diemTuVung = parseFloat($('#diemTuVung').val()) || 0;
            
            // Tính điểm trung bình của 3 tiêu chí
            const diemTong = ((diemNguPhap + diemMachLac + diemTuVung) / 3).toFixed(1);
            
            // Cập nhật vào ô điểm tổng
            $('#diemSo').val(diemTong);
        }
        
        // Tự động tính điểm tổng khi thay đổi điểm các tiêu chí
        $(document).ready(function() {
            $('#diemNguPhap, #diemMachLac, #diemTuVung').on('input', function() {
                calculateTotalScore();
            });
            
            // Tính điểm tổng lần đầu khi load trang
            calculateTotalScore();
        });

        // Lưu điểm
        $('#gradingForm').on('submit', function(e) {
            e.preventDefault();
            
            const submissionId = $('#submissionId').val();
            const diemSo = $('#diemSo').val();
            const diemNguPhap = $('#diemNguPhap').val();
            const diemMachLac = $('#diemMachLac').val();
            const diemTuVung = $('#diemTuVung').val();
            const nhanXet = $('#nhanXet').val();
            
            console.log('Form data:', {submissionId, diemSo, diemNguPhap, diemMachLac, diemTuVung, nhanXet});
            
            // Validation
            if (diemSo === '' || diemNguPhap === '' || diemMachLac === '' || diemTuVung === '') {
                alert('Vui lòng nhập đầy đủ điểm số!');
                return;
            }
            
            if (parseFloat(diemSo) < 0 || parseFloat(diemSo) > 10 ||
                parseFloat(diemNguPhap) < 0 || parseFloat(diemNguPhap) > 10 ||
                parseFloat(diemMachLac) < 0 || parseFloat(diemMachLac) > 10 ||
                parseFloat(diemTuVung) < 0 || parseFloat(diemTuVung) > 10) {
                alert('Điểm số phải từ 0 đến 10!');
                return;
            }
            
            const requestData = {
                submission_id: submissionId,
                diem_so: parseFloat(diemSo),
                diem_ngu_phap: parseFloat(diemNguPhap),
                diem_mach_lac: parseFloat(diemMachLac),
                diem_tu_vung: parseFloat(diemTuVung),
                nhan_xet: nhanXet
            };
            
            console.log('Sending request:', requestData);
            
            $.ajax({
                url: '/webhocngoaingu/api/save-grading-result.php',
                method: 'POST',
                data: JSON.stringify(requestData),
                contentType: 'application/json',
                success: function(response) {
                    console.log('Grade response:', response);
                    if (response.success) {
                        alert('Đã chấm điểm thành công!');
                        window.location.href = '/webhocngoaingu/public/admin/WritingManagement.php';
                    } else {
                        alert('Lỗi: ' + (response.message || response.error || 'Không xác định'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Grade error:', error);
                    console.error('Response text:', xhr.responseText);
                    alert('Có lỗi xảy ra khi lưu điểm: ' + error);
                }
            });
        });
    </script>
</body>
</html>
