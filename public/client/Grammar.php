<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");

// Check login
if (!isset($_SESSION["account"])) {
    redirect(BASE_URL("Auth/DangNhap"));
}

$user = $Database->get_row("SELECT * FROM nguoidung WHERE taikhoan = '" . $_SESSION["account"] . "'");
if (!$user) {
    redirect(BASE_URL("Auth/DangNhap"));
}

$title = 'Grammar Practice | ' . $Database->site("TenWeb");

// Get lesson parameters
$maKhoaHoc = isset($_GET['maKhoaHoc']) ? (int) $_GET['maKhoaHoc'] : null;
$maBaiHoc = isset($_GET['maBaiHoc']) ? (int) $_GET['maBaiHoc'] : null;

// Get all grammar topics, filtered by lesson if parameters are provided
try {
    $whereClause = "gt.TrangThai = 1";

    // If we have lesson parameters, filter by them
    if ($maKhoaHoc && $maBaiHoc) {
        $whereClause .= " AND gt.MaKhoaHoc = $maKhoaHoc AND gt.MaBaiHoc = $maBaiHoc";
    }

    $topics = $Database->get_list("
        SELECT gt.*, 
               (SELECT COUNT(*) FROM grammar_questions WHERE MaChuDe = gt.MaChuDe AND TrangThai = 1) as SoLuongCauHoi,
               (SELECT COUNT(*) FROM grammar_results WHERE TaiKhoan = '" . $user['TaiKhoan'] . "' AND MaChuDe = gt.MaChuDe) as DaLam,
               (SELECT AVG(DiemSo) FROM grammar_results WHERE TaiKhoan = '" . $user['TaiKhoan'] . "' AND MaChuDe = gt.MaChuDe) as DiemTrungBinh
        FROM grammar_topics gt 
        WHERE $whereClause
        ORDER BY gt.ThuTu ASC, gt.MaChuDe ASC
    ");
} catch (Exception $e) {
    $topics = [];
}

require_once(__DIR__ . "/../../public/client/header.php");
?>

<style>
    .grammar-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .grammar-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 60px 0;
        text-align: center;
        margin-bottom: 40px;
        border-radius: 20px;
    }

    .grammar-header h1 {
        font-size: 3.5rem;
        margin-bottom: 20px;
        font-weight: 700;
    }

    .grammar-header .subtitle {
        font-size: 1.3rem;
        opacity: 0.9;
        max-width: 600px;
        margin: 0 auto;
        line-height: 1.6;
    }

    .topics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 25px;
        margin-bottom: 40px;
    }

    .topic-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        border: 1px solid #f0f0f0;
        position: relative;
    }

    .topic-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(102, 126, 234, 0.2);
    }

    .topic-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        position: relative;
        overflow: hidden;
    }

    .topic-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.1);
        transform: rotate(45deg);
    }

    .topic-level {
        position: absolute;
        top: 15px;
        right: 15px;
        background: rgba(255, 255, 255, 0.2);
        color: white;
        padding: 8px 16px;
        border-radius: 18px;
        font-size: 1.2rem;
        font-weight: 600;
        backdrop-filter: blur(10px);
    }

    .topic-title {
        font-size: 2.2rem;
        font-weight: 700;
        margin-bottom: 10px;
        line-height: 1.3;
    }

    .topic-subtitle {
        font-size: 1.4rem;
        opacity: 0.9;
        margin-bottom: 0;
    }

    .topic-body {
        padding: 30px;
    }

    .topic-description {
        color: #666;
        margin-bottom: 25px;
        line-height: 1.6;
        font-size: 1.3rem;
    }

    .topic-stats {
        display: flex;
        justify-content: space-between;
        margin-bottom: 25px;
    }

    .stat-item {
        text-align: center;
        flex: 1;
        padding: 0 10px;
    }
    }

    .stat-number {
        font-size: 2.4rem;
        font-weight: 700;
        color: #667eea;
        display: block;
    }

    .stat-label {
        font-size: 1.2rem;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .topic-footer {
        padding: 0 30px 30px;
    }

    .btn-start-quiz {
        width: 100%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 18px;
        border-radius: 12px;
        font-size: 1.4rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
        text-align: center;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .btn-start-quiz:hover {
        color: white;
        text-decoration: none;
        transform: translateY(-2px);
    }

    .btn-start-quiz::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s;
    }

    .btn-start-quiz:hover::before {
        left: 100%;
    }

    .progress-indicator {
        background: #f8f9ff;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
        border-left: 4px solid #667eea;
    }

    .progress-text {
        font-size: 0.9rem;
        color: #555;
        margin-bottom: 8px;
    }

    .progress-bar {
        background: #e1e5e9;
        height: 8px;
        border-radius: 4px;
        overflow: hidden;
    }

    .progress-fill {
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        height: 100%;
        border-radius: 4px;
        transition: width 0.3s ease;
    }

    .best-score {
        display: inline-flex;
        align-items: center;
        background: #fff3cd;
        color: #856404;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        margin-top: 10px;
    }

    .best-score i {
        margin-right: 5px;
    }

    .no-topics {
        text-align: center;
        padding: 80px 20px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .no-topics i {
        font-size: 5rem;
        color: #ddd;
        margin-bottom: 30px;
    }

    .no-topics h3 {
        color: #666;
        margin-bottom: 15px;
    }

    .no-topics p {
        color: #888;
        font-size: 1.1rem;
    }

    .grammar-features {
        background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
        border-radius: 25px;
        padding: 50px 40px;
        margin-bottom: 50px;
        box-shadow: 0 15px 40px rgba(102, 126, 234, 0.15);
        border: 1px solid rgba(102, 126, 234, 0.1);
        position: relative;
        overflow: hidden;
    }

    .grammar-features::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    }

    .grammar-features h3 {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 40px;
        text-align: center;
        font-size: 2.8rem;
        font-weight: 700;
        position: relative;
    }

    .grammar-features h3 i {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-right: 15px;
        font-size: 2.5rem;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.1);
        }
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 35px;
    }

    .feature-item {
        text-align: center;
        padding: 35px 25px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        transition: all 0.4s ease;
        border: 1px solid rgba(102, 126, 234, 0.1);
        position: relative;
        overflow: hidden;
    }

    .feature-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        transform: scaleX(0);
        transition: transform 0.4s ease;
    }

    .feature-item:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(102, 126, 234, 0.2);
        border-color: rgba(102, 126, 234, 0.2);
    }

    .feature-item:hover::before {
        transform: scaleX(1);
    }

    .feature-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        color: white;
        font-size: 2rem;
        transition: all 0.4s ease;
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    }

    .feature-item:hover .feature-icon {
        transform: scale(1.1) rotate(5deg);
        box-shadow: 0 12px 30px rgba(102, 126, 234, 0.4);
    }

    /* Feature Icons with different colors */
    .feature-item:nth-child(1) .feature-icon {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    }

    .feature-item:nth-child(1):hover .feature-icon {
        box-shadow: 0 12px 30px rgba(102, 126, 234, 0.4);
    }

    .feature-item:nth-child(2) .feature-icon {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
        box-shadow: 0 8px 20px rgba(255, 107, 107, 0.3);
    }

    .feature-item:nth-child(2):hover .feature-icon {
        box-shadow: 0 12px 30px rgba(255, 107, 107, 0.4);
    }

    .feature-item:nth-child(3) .feature-icon {
        background: linear-gradient(135deg, #feca57 0%, #ff9ff3 100%);
        box-shadow: 0 8px 20px rgba(254, 202, 87, 0.3);
    }

    .feature-item:nth-child(3):hover .feature-icon {
        box-shadow: 0 12px 30px rgba(254, 202, 87, 0.4);
    }

    .feature-item:nth-child(4) .feature-icon {
        background: linear-gradient(135deg, #48dbfb 0%, #0abde3 100%);
        box-shadow: 0 8px 20px rgba(72, 219, 251, 0.3);
    }

    .feature-item:nth-child(4):hover .feature-icon {
        box-shadow: 0 12px 30px rgba(72, 219, 251, 0.4);
    }

    .feature-title {
        font-size: 1.6rem;
        font-weight: 700;
        margin-bottom: 15px;
        color: #333;
        transition: color 0.3s ease;
    }

    .feature-item:hover .feature-title {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* Feature Title hover effects with matching colors */
    .feature-item:nth-child(1):hover .feature-title {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .feature-item:nth-child(2):hover .feature-title {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .feature-item:nth-child(3):hover .feature-title {
        background: linear-gradient(135deg, #feca57 0%, #ff9ff3 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .feature-item:nth-child(4):hover .feature-title {
        background: linear-gradient(135deg, #48dbfb 0%, #0abde3 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .feature-description {
        color: #666;
        font-size: 2.2rem;
        line-height: 1.6;
        transition: color 0.3s ease;
    }

    .feature-item:hover .feature-description {
        color: #555;
    }

    @media (max-width: 768px) {
        .grammar-container {
            padding: 10px;
        }

        .grammar-header {
            padding: 40px 20px;
            margin-bottom: 30px;
        }

        .grammar-header h1 {
            font-size: 2.5rem;
        }

        .topics-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .topic-stats {
            flex-direction: column;
            gap: 10px;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .features-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
    }
</style>

<div class="grammar-container">
    <!-- Header -->
    <div class="grammar-header">
        <h1><i class="fas fa-spell-check"></i> Grammar Practice</h1>
        <?php if ($maKhoaHoc && $maBaiHoc): ?>
            <?php
            // Map lesson to topic name
            $topicNames = [
                1 => 'Traffic',
                2 => 'Food',
                3 => 'Education',
                4 => 'Family',
                5 => 'Work',
                6 => 'Hobbie',
                7 => 'Technology',
                8 => 'Activities'
            ];
            $currentTopicName = $topicNames[$maBaiHoc] ?? 'General';
            ?>
            <p class="subtitle">
                <strong>Chủ đề: <?= $currentTopicName ?></strong> | Bài học: <?= $maBaiHoc ?><br>
                Thực hành ngữ pháp cho chủ đề này với các bài tập đa dạng và phong phú.
            </p>
        <?php else: ?>
            <p class="subtitle">
                Nâng cao kỹ năng ngữ pháp tiếng Anh với hệ thống bài tập trắc nghiệm đa dạng.
                Thực hành từ cơ bản đến nâng cao, theo dõi tiến độ học tập của bạn.
            </p>
        <?php endif; ?>
    </div>

    <!-- Features Section -->
    <div class="grammar-features">
        <h3><i class="fas fa-star"></i> Tính năng nổi bật</h3>
        <div class="features-grid">
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-brain"></i>
                </div>
                <div class="feature-title">Học theo từng chủ đề</div>
                <div class="feature-description">
                    Chia nhỏ ngữ pháp thành các chủ đề cụ thể, dễ hiểu và dễ nhớ
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="feature-title">Theo dõi tiến độ</div>
                <div class="feature-description">
                    Xem điểm số, số lần làm bài và cải thiện kết quả qua thời gian
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <div class="feature-title">Giải thích chi tiết</div>
                <div class="feature-description">
                    Mỗi câu hỏi đều có giải thích giúp bạn hiểu rõ quy tắc ngữ pháp
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-redo"></i>
                </div>
                <div class="feature-title">Luyện tập không giới hạn</div>
                <div class="feature-description">
                    Làm bài nhiều lần để củng cố kiến thức và đạt điểm cao
                </div>
            </div>
        </div>
    </div>

    <!-- Topics Grid -->
    <?php if (count($topics) > 0): ?>
        <div class="topics-grid">
            <?php foreach ($topics as $topic): ?>
                <div class="topic-card">
                    <div class="topic-header">
                        <div class="topic-level"><?= $topic['CapDo'] ?></div>
                        <h3 class="topic-title"><?= htmlspecialchars($topic['TenChuDe']) ?></h3>
                        <p class="topic-subtitle"><?= htmlspecialchars($topic['TenChuDeEng']) ?></p>
                    </div>

                    <div class="topic-body">
                        <?php if ($topic['MoTa']): ?>
                            <p class="topic-description"><?= htmlspecialchars($topic['MoTa']) ?></p>
                        <?php endif; ?>

                        <div class="topic-stats">
                            <div class="stat-item">
                                <span class="stat-number"><?= $topic['SoLuongCauHoi'] ?></span>
                                <span class="stat-label">Câu hỏi</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number"><?= $topic['DaLam'] ?></span>
                                <span class="stat-label">Đã làm</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number">
                                    <?= $topic['DiemTrungBinh'] ? round($topic['DiemTrungBinh'], 1) : '0' ?>%
                                </span>
                                <span class="stat-label">Điểm TB</span>
                            </div>
                        </div>

                        <?php if ($topic['DaLam'] > 0): ?>
                            <div class="progress-indicator">
                                <div class="progress-text">
                                    Đã hoàn thành <?= $topic['DaLam'] ?> lần •
                                    Điểm trung bình: <?= $topic['DiemTrungBinh'] ? round($topic['DiemTrungBinh'], 1) : '0' ?>%
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?= min($topic['DiemTrungBinh'] ?? 0, 100) ?>%"></div>
                                </div>
                                <?php if ($topic['DiemTrungBinh'] >= 80): ?>
                                    <div class="best-score">
                                        <i class="fas fa-trophy"></i>
                                        Điểm cao!
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="topic-footer">
                        <?php if ($topic['SoLuongCauHoi'] > 0): ?>
                            <a href="<?= BASE_URL("GrammarQuiz?topic=" . $topic['MaChuDe']) ?>" class="btn-start-quiz">
                                <i class="fas fa-play"></i>
                                <?= $topic['DaLam'] > 0 ? 'Làm lại' : 'Bắt đầu' ?>
                            </a>
                        <?php else: ?>
                            <div class="btn-start-quiz disabled" style="background: #ccc; cursor: not-allowed;">
                                <i class="fas fa-hourglass-half"></i>
                                Chưa có câu hỏi
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-topics">
            <i class="fas fa-book-open"></i>
            <h3>Chưa có chủ đề Grammar</h3>
            <p>Hệ thống đang được cập nhật. Vui lòng quay lại sau!</p>
        </div>
    <?php endif; ?>
</div>

<script>
    $(document).ready(function () {
        // Add subtle animations to cards
        $('.topic-card').each(function (index) {
            $(this).css({
                'opacity': '0',
                'transform': 'translateY(30px)'
            }).delay(index * 100).animate({
                'opacity': '1'
            }, 500, function () {
                $(this).css('transform', 'translateY(0)');
            });
        });

        // Add hover effects
        $('.topic-card').hover(
            function () {
                $(this).find('.topic-header').css('transform', 'scale(1.02)');
            },
            function () {
                $(this).find('.topic-header').css('transform', 'scale(1)');
            }
        );
    });
</script>

<?php require_once(__DIR__ . "/../../public/client/footer.php"); ?>