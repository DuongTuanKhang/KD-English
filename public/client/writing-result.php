<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");

$title = 'Kết quả bài viết | ' . $Database->site('TenWeb');

if (empty($_SESSION['account'])) {
    redirect(BASE_URL(''));
}

$maKhoaHoc = $_GET['maKhoaHoc'] ?? '';
$promptId = $_GET['promptId'] ?? '';

if (!$maKhoaHoc || !$promptId) {
    redirect(BASE_URL("public/client/writing.php?maKhoaHoc=$maKhoaHoc"));
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

// Get submission with grade
$submission = $Database->get_row("
    SELECT s.*, p.TieuDe, p.NoiDungDeBai, p.GioiHanTu, p.MucDo, p.ThoiGianLamBai, p.MaChuDe
    FROM writing_submissions s
    JOIN writing_prompts p ON s.MaDeBai = p.MaDeBai
    WHERE s.MaDeBai = '$promptId' AND s.TaiKhoan = '" . $_SESSION['account'] . "'
");

if (!$submission) {
    redirect(BASE_URL("public/client/writing.php?maKhoaHoc=$maKhoaHoc"));
}

// Function to map writing topic IDs back to lesson IDs
function mapWritingTopicToLesson($topicId) {
    $topicToLessonMap = [
        1 => 1, // Traffic (Topic ID 1) -> Lesson 1
        6 => 2, // Food (Topic ID 6) -> Lesson 2  
        5 => 3, // Education (Topic ID 5) -> Lesson 3
        3 => 4, // Family (Topic ID 3) -> Lesson 4
        4 => 5, // Work (Topic ID 4) -> Lesson 5
        2 => 6, // Hobbies (Topic ID 2) -> Lesson 6
        7 => 7, // Technology (Topic ID 7) -> Lesson 7
        8 => 8  // Activities (Topic ID 8) -> Lesson 8
    ];
    return $topicToLessonMap[$topicId] ?? $topicId;
}

include_once(__DIR__ . "/header.php");
?>

<style>
.result-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.result-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 15px;
    margin-bottom: 30px;
}

.result-title {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 10px;
}

.result-meta {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.result-meta span {
    background: rgba(255,255,255,0.2);
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 16px;
}

.result-section {
    margin-bottom: 30px;
    padding: 25px;
    border: 1px solid #e9ecef;
    border-radius: 10px;
}

.result-section h3 {
    color: #2c3e50;
    margin-bottom: 15px;
    font-weight: 600;
    font-size: 20px;
}

.score-display {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.score-item {
    text-align: center;
    padding: 20px;
    border-radius: 10px;
    flex: 1;
}

.score-item.excellent {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
}

.score-item.good {
    background: linear-gradient(135deg, #ffc107, #fd7e14);
    color: white;
}

.score-item.average {
    background: linear-gradient(135deg, #fd7e14, #dc3545);
    color: white;
}

.score-item.poor {
    background: linear-gradient(135deg, #dc3545, #6f42c1);
    color: white;
}

.score-value {
    font-size: 36px;
    font-weight: 700;
    margin-bottom: 5px;
}

.score-label {
    font-size: 16px;
    opacity: 0.9;
}

.essay-content {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #007bff;
    line-height: 1.8;
    font-size: 16px;
}

.feedback-box {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    padding: 20px;
    border-radius: 8px;
    margin-top: 15px;
    font-size: 16px;
}

.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 25px;
    background: linear-gradient(135deg, #6c757d, #495057);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 16px;
    transition: all 0.3s ease;
    margin-top: 20px;
}

.back-btn:hover {
    background: linear-gradient(135deg, #495057, #343a40);
    color: white;
    text-decoration: none;
    transform: translateY(-2px);
}

.submission-time {
    margin-top: 20px;
    padding: 15px 20px;
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    border-left: 5px solid #2196f3;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    color: #1565c0;
    box-shadow: 0 2px 8px rgba(33, 150, 243, 0.15);
}

.submission-time i {
    margin-right: 10px;
    font-size: 16px;
    color: #2196f3;
}
</style>

<div class="container-fluid">
    <div class="result-container">
        <!-- Header -->
        <div class="result-header">
            <div class="result-title"><?= htmlspecialchars($submission['TieuDe']) ?></div>
            <div class="result-meta">
                <span><i class="fas fa-book"></i> <?= htmlspecialchars($khoaHoc['TenKhoaHoc']) ?></span>
                <span><i class="fas fa-signal"></i> <?= htmlspecialchars($submission['MucDo']) ?></span>
                <span><i class="fas fa-clock"></i> <?= htmlspecialchars($submission['ThoiGianLamBai']) ?> phút</span>
                <span><i class="fas fa-pen"></i> <?= htmlspecialchars($submission['SoTu']) ?> từ</span>
            </div>
        </div>

        <!-- Scores Section -->
        <?php if ($submission['TrangThaiCham'] === 'Đã chấm'): ?>
        <div class="result-section">
            <h3><i class="fas fa-star text-warning"></i> Điểm số</h3>
            <div class="score-display">
                <?php
                $totalScore = floatval($submission['DiemSo']);
                $scoreClass = $totalScore >= 8 ? 'excellent' : ($totalScore >= 6 ? 'good' : ($totalScore >= 4 ? 'average' : 'poor'));
                ?>
                <div class="score-item <?= $scoreClass ?>">
                    <div class="score-value"><?= number_format($totalScore, 1) ?></div>
                    <div class="score-label">Điểm tổng</div>
                </div>
            </div>
            
            <?php if ($submission['NhanXet']): ?>
            <div class="feedback-box">
                <h4><i class="fas fa-comment-alt"></i> Nhận xét từ giáo viên:</h4>
                <p><?= nl2br(htmlspecialchars($submission['NhanXet'])) ?></p>
                <small class="text-muted">
                    Ngày chấm: <?= date('d/m/Y H:i', strtotime($submission['ThoiGianCham'])) ?>
                </small>
            </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="result-section">
            <div class="alert alert-info">
                <i class="fas fa-clock"></i>
                Bài viết của bạn đang được chấm. Vui lòng quay lại sau để xem kết quả.
            </div>
        </div>
        <?php endif; ?>

        <!-- Essay Content -->
        <div class="result-section">
            <h3><i class="fas fa-file-text text-primary"></i> Đề bài</h3>
            <div class="essay-content">
                <?= nl2br(htmlspecialchars($submission['NoiDungDeBai'])) ?>
            </div>
        </div>

        <div class="result-section">
            <h3><i class="fas fa-edit text-success"></i> Bài viết của bạn</h3>
            <div class="essay-content">
                <?= nl2br(htmlspecialchars($submission['NoiDungBaiViet'])) ?>
            </div>
            <div class="submission-time">
                <i class="fas fa-clock"></i>
                Nộp lúc: <?= date('d/m/Y H:i', strtotime($submission['ThoiGianNop'])) ?>
            </div>
        </div>

        <!-- Back Button -->
        <a href="<?= BASE_URL("Page/KhoaHoc/$maKhoaHoc") ?>" class="back-btn">
            <i class="fas fa-arrow-left"></i> Quay lại khóa học
        </a>
    </div>
</div>

<?php include_once(__DIR__ . "/footer.php"); ?>
