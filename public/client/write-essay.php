<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");

$title = 'Write Essay | ' . $Database->site('TenWeb');

if (empty($_SESSION['account'])) {
    redirect(BASE_URL(''));
}

$maKhoaHoc = $_GET['maKhoaHoc'] ?? '';
$maBaiHoc = $_GET['maBaiHoc'] ?? '';
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

// Get prompt info
$prompt = $Database->get_row("SELECT * FROM writing_prompts WHERE MaDeBai = '$promptId'");
if (!$prompt) {
    redirect(BASE_URL("public/client/writing.php?maKhoaHoc=$maKhoaHoc"));
}

// Check if user has already submitted this prompt
$existingSubmission = $Database->get_row("SELECT * FROM writing_submissions WHERE MaDeBai = '$promptId' AND TaiKhoan = '" . $_SESSION['account'] . "'");

// Check for existing draft
$existingDraft = null;
if (!$existingSubmission) {
    $existingDraft = $Database->get_row("SELECT * FROM writing_drafts WHERE prompt_id = '$promptId' AND user_account = '" . $_SESSION['account'] . "'");
}

include_once(__DIR__ . "/header.php");
?>

<script>
document.documentElement.classList.add('write-essay-page');
document.body.classList.add('write-essay-page');
</script>

<link rel="stylesheet" href="<?= BASE_URL('assets/css/write-essay.css') ?>?id=<?= rand(0, 1000000) ?>">

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="write-essay-container">
                <!-- Header -->
                <div class="write-essay-header">
                    <div class="header-info">
                        <h1 class="essay-title"><?= htmlspecialchars($prompt['TieuDe']) ?></h1>
                        <div class="essay-meta">
                            <span class="course-name">
                                <i class="fas fa-book"></i>
                                <?= htmlspecialchars($khoaHoc['TenKhoaHoc']) ?>
                            </span>
                            <span class="word-count">
                                <i class="fas fa-pencil-alt"></i>
                                Yêu cầu: <?= $prompt['GioiHanTu'] ?> từ
                            </span>
                            <span class="time-limit">
                                <i class="fas fa-clock"></i>
                                Thời gian: <?= $prompt['ThoiGianLamBai'] ?> phút
                            </span>
                        </div>
                    </div>
                    <div class="header-actions">
                        <a href="<?= BASE_URL("public/client/writing.php?maKhoaHoc=$maKhoaHoc&maBaiHoc={$prompt['MaChuDe']}") ?>" class="btn-back">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>

                <!-- Prompt Content -->
                <div class="prompt-section">
                    <h3 class="prompt-label">Đề bài:</h3>
                    <div class="prompt-content">
                        <?= nl2br(htmlspecialchars($prompt['NoiDungDeBai'])) ?>
                    </div>
                </div>

                <!-- Writing Section -->
                <div class="writing-section">
                    <form id="essayForm" method="POST">
                        <input type="hidden" name="prompt_id" value="<?= $promptId ?>">
                        <input type="hidden" name="ma_khoa_hoc" value="<?= $maKhoaHoc ?>">
                        <input type="hidden" name="ma_chu_de" value="<?= $prompt['MaChuDe'] ?>">
                        
                        <div class="writing-controls">
                            <div class="controls-left">
                                <h3 class="writing-label">Bài viết của bạn:</h3>
                                <div class="writing-stats">
                                    <span class="word-counter">
                                        <span id="currentWordCount">0</span> / <?= $prompt['GioiHanTu'] ?> từ
                                    </span>
                                    <span class="time-remaining" id="timeRemaining">
                                        <?= $prompt['ThoiGianLamBai'] ?>:00
                                    </span>
                                </div>
                            </div>
                            <div class="controls-right">
                                <!-- Buttons moved below writing area -->
                            </div>
                        </div>

                        <div class="writing-area">
                            <textarea 
                                id="essayContent" 
                                name="content" 
                                placeholder="Nhập bài viết của bạn tại đây..."
                                <?= $existingSubmission ? 'readonly' : '' ?>
                            ><?= $existingSubmission ? htmlspecialchars($existingSubmission['NoiDungBaiViet']) : 
                                 ($existingDraft ? htmlspecialchars($existingDraft['content']) : '') ?></textarea>
                        </div>

                        <div class="writing-buttons">
                            <button type="button" id="saveBtn" class="btn-save">
                                <i class="fas fa-save"></i> Lưu nháp
                            </button>
                            <button type="submit" id="submitBtn" class="btn-submit" <?= $existingSubmission ? 'disabled' : '' ?>>
                                <i class="fas fa-paper-plane"></i> 
                                <?= $existingSubmission ? 'Đã nộp bài' : 'Nộp bài' ?>
                            </button>
                        </div>

                        <?php if ($existingSubmission): ?>
                        <div class="submission-info">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                Bạn đã nộp bài này vào lúc: <?= date('d/m/Y H:i', strtotime($existingSubmission['ThoiGianNop'])) ?>
                            </div>
                        </div>
                        <?php elseif ($existingDraft): ?>
                        <div class="submission-info">
                            <div class="alert alert-warning">
                                <i class="fas fa-save"></i>
                                Đã khôi phục bản nháp từ: <?= date('d/m/Y H:i', strtotime($existingDraft['updated_at'])) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL('assets/js/write-essay.js') ?>?id=<?= rand(0, 1000000) ?>"></script>

<?php include_once(__DIR__ . "/footer.php"); ?>
