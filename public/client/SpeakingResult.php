<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");

$title = 'Speaking Results | ' . $Database->site("TenWeb");

// Check login
if (!isset($_SESSION["account"])) {
    redirect(BASE_URL("Auth/DangNhap"));
}

$user = $Database->get_row("SELECT * FROM nguoidung WHERE taikhoan = '" . $_SESSION["account"] . "'");
if (!$user) {
    redirect(BASE_URL("Auth/DangNhap"));
}

// Get lesson ID
$lessonId = isset($_GET['lesson']) ? (int)$_GET['lesson'] : null;
if (!$lessonId) {
    redirect(BASE_URL("Speaking"));
}

// Get lesson info
$lesson = $Database->get_row("
    SELECT sl.*,
           st.TenChuDe,
           st.TenChuDeEng,
           st.CapDo as CapDoChuDe
    FROM speaking_lessons sl
    JOIN speaking_topics st ON sl.MaChuDe = st.MaChuDe
    WHERE sl.MaBaiSpeaking = '$lessonId' AND sl.TrangThai = 1
");

if (!$lesson) {
    redirect(BASE_URL("Speaking"));
}

// Get user results for this lesson
$results = $Database->get_list("
    SELECT * FROM speaking_results 
    WHERE MaBaiSpeaking = '$lessonId' AND TaiKhoan = '" . $user['TaiKhoan'] . "'
    ORDER BY ThoiGianNop DESC
");

// Calculate statistics
$stats = [
    'total_attempts' => count($results),
    'best_score' => 0,
    'average_score' => 0,
    'latest_score' => 0,
    'improvement' => 0,
    'completion_status' => false
];

if (!empty($results)) {
    $scores = array_column($results, 'TongDiem');
    $stats['best_score'] = max($scores);
    $stats['average_score'] = round(array_sum($scores) / count($scores), 1);
    $stats['latest_score'] = $results[0]['TongDiem'];
    $stats['completion_status'] = $stats['best_score'] >= $lesson['DiemToiThieu'];
    
    // Calculate improvement (compare first and latest)
    if (count($results) > 1) {
        $firstScore = end($scores);
        $stats['improvement'] = round($stats['latest_score'] - $firstScore, 1);
    }
}

require_once(__DIR__ . "/header.php");
?>

<style>
.speaking-results-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
}

.results-header {
    text-align: center;
    margin-bottom: 40px;
    color: white;
}

.results-title {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 10px;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.results-breadcrumb {
    background: rgba(255,255,255,0.1);
    padding: 15px 25px;
    border-radius: 25px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    backdrop-filter: blur(10px);
    margin-bottom: 20px;
}

.results-breadcrumb a {
    color: white;
    text-decoration: none;
    font-weight: 500;
}

.results-breadcrumb a:hover {
    color: #FFD700;
    text-decoration: none;
}

.lesson-info-card {
    background: white;
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.1);
}

.lesson-info-header {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 20px;
}

.lesson-icon {
    width: 80px;
    height: 80px;
    border-radius: 20px;
    background: linear-gradient(45deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
}

.lesson-info-content h2 {
    font-size: 2rem;
    color: #333;
    margin: 0 0 5px 0;
}

.lesson-topic {
    font-size: 1.1rem;
    color: #666;
    margin-bottom: 5px;
}

.lesson-level-badge {
    padding: 6px 12px;
    border-radius: 15px;
    font-weight: 600;
    color: white;
    display: inline-block;
    font-size: 0.9rem;
}

.lesson-level-badge.beginner {
    background: linear-gradient(45deg, #4CAF50, #8BC34A);
}

.lesson-level-badge.intermediate {
    background: linear-gradient(45deg, #FF9800, #FFC107);
}

.lesson-level-badge.advanced {
    background: linear-gradient(45deg, #F44336, #E91E63);
}

.target-text-display {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 15px;
    border-left: 5px solid #667eea;
    margin-top: 20px;
}

.target-text-display h3 {
    color: #333;
    margin-bottom: 15px;
    font-size: 1.2rem;
}

.target-text {
    font-size: 1.4rem;
    color: #333;
    line-height: 1.8;
    font-weight: 500;
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #667eea, #764ba2);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    margin: 0 auto 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.stat-icon.attempts {
    background: linear-gradient(45deg, #2196F3, #03A9F4);
}

.stat-icon.best {
    background: linear-gradient(45deg, #4CAF50, #8BC34A);
}

.stat-icon.average {
    background: linear-gradient(45deg, #FF9800, #FFC107);
}

.stat-icon.improvement {
    background: linear-gradient(45deg, #9C27B0, #E91E63);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: bold;
    color: #333;
    margin-bottom: 5px;
}

.stat-label {
    color: #666;
    font-weight: 500;
}

.improvement-positive {
    color: #4CAF50;
}

.improvement-negative {
    color: #F44336;
}

.completion-status {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    text-align: center;
}

.status-completed {
    color: #4CAF50;
}

.status-incomplete {
    color: #F44336;
}

.status-icon {
    font-size: 3rem;
    margin-bottom: 15px;
}

.status-text {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 10px;
}

.status-description {
    color: #666;
}

.results-history {
    background: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.1);
}

.results-history h3 {
    color: #333;
    margin-bottom: 25px;
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.no-results {
    text-align: center;
    color: #666;
    padding: 40px 20px;
}

.no-results i {
    font-size: 3rem;
    margin-bottom: 15px;
    opacity: 0.5;
}

.result-item {
    display: flex;
    align-items: center;
    padding: 20px;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    margin-bottom: 15px;
    transition: all 0.3s ease;
    background: #fafafa;
}

.result-item:hover {
    border-color: #667eea;
    background: white;
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.result-score {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    margin-right: 20px;
    flex-shrink: 0;
}

.result-score.excellent {
    background: linear-gradient(45deg, #4CAF50, #8BC34A);
}

.result-score.good {
    background: linear-gradient(45deg, #2196F3, #03A9F4);
}

.result-score.average {
    background: linear-gradient(45deg, #FF9800, #FFC107);
}

.result-score.poor {
    background: linear-gradient(45deg, #F44336, #E91E63);
}

.result-score-number {
    font-size: 1.3rem;
}

.result-score-text {
    font-size: 0.7rem;
    opacity: 0.9;
}

.result-content {
    flex: 1;
}

.result-meta {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 10px;
    color: #666;
    font-size: 0.9rem;
}

.result-meta i {
    width: 16px;
}

.result-spoken-text {
    font-style: italic;
    color: #555;
    background: white;
    padding: 12px 15px;
    border-radius: 8px;
    border-left: 3px solid #667eea;
    margin-top: 10px;
}

.action-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 30px;
    flex-wrap: wrap;
}

.btn-action {
    padding: 12px 25px;
    border: none;
    border-radius: 25px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 8px;
    color: white;
}

.btn-practice-again {
    background: linear-gradient(45deg, #667eea, #764ba2);
}

.btn-practice-again:hover {
    background: linear-gradient(45deg, #5a6fd8, #6a4190);
    transform: translateY(-2px);
    text-decoration: none;
    color: white;
}

.btn-back {
    background: linear-gradient(45deg, #6c757d, #5a6268);
}

.btn-back:hover {
    background: linear-gradient(45deg, #5a6268, #495057);
    transform: translateY(-2px);
    text-decoration: none;
    color: white;
}

.chart-container {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.chart-container h3 {
    color: #333;
    margin-bottom: 20px;
    text-align: center;
}

.progress-chart {
    height: 200px;
    position: relative;
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    display: flex;
    align-items: end;
    gap: 5px;
    overflow-x: auto;
}

.chart-bar {
    min-width: 30px;
    background: linear-gradient(0deg, #667eea, #764ba2);
    border-radius: 4px 4px 0 0;
    position: relative;
    transition: all 0.3s ease;
}

.chart-bar:hover {
    transform: scale(1.1);
    background: linear-gradient(0deg, #5a6fd8, #6a4190);
}

.chart-bar-value {
    position: absolute;
    top: -25px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 0.8rem;
    font-weight: bold;
    color: #333;
    background: rgba(255,255,255,0.9);
    padding: 2px 6px;
    border-radius: 4px;
}

@media (max-width: 768px) {
    .speaking-results-container {
        padding: 15px;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    
    .result-item {
        flex-direction: column;
        text-align: center;
    }
    
    .result-score {
        margin-right: 0;
        margin-bottom: 15px;
    }
    
    .action-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .btn-action {
        width: 100%;
        max-width: 250px;
        justify-content: center;
    }
}
</style>

<div class="speaking-results-container">
    <!-- Header -->
    <div class="results-header">
        <h1 class="results-title">📊 Kết quả Speaking</h1>
        <div class="results-breadcrumb">
            <a href="<?= BASE_URL("Page/Home") ?>"><i class="fas fa-home"></i></a>
            <span>/</span>
            <a href="<?= BASE_URL("Speaking") ?>">Speaking</a>
            <span>/</span>
            <a href="<?= BASE_URL("SpeakingPractice?topic=" . $lesson['MaChuDe']) ?>">Practice</a>
            <span>/</span>
            <span>Results</span>
        </div>
    </div>

    <!-- Lesson Info -->
    <div class="lesson-info-card">
        <div class="lesson-info-header">
            <div class="lesson-icon">
                <i class="fas fa-microphone"></i>
            </div>
            <div class="lesson-info-content">
                <h2><?= htmlspecialchars($lesson['TieuDe']) ?></h2>
                <div class="lesson-topic"><?= htmlspecialchars($lesson['TenChuDe']) ?> - <?= htmlspecialchars($lesson['TenChuDeEng']) ?></div>
                <div class="lesson-level-badge <?= strtolower($lesson['CapDo']) ?>">
                    <?= $lesson['CapDo'] ?> Level
                </div>
            </div>
        </div>
        
        <?php if ($lesson['NoiDung']): ?>
        <p style="color: #666; margin-bottom: 15px;">
            <?= htmlspecialchars($lesson['NoiDung']) ?>
        </p>
        <?php endif; ?>
        
        <div class="target-text-display">
            <h3>📖 Văn bản mẫu:</h3>
            <div class="target-text">
                <?= htmlspecialchars($lesson['TextMau']) ?>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon attempts">
                <i class="fas fa-redo"></i>
            </div>
            <div class="stat-number"><?= $stats['total_attempts'] ?></div>
            <div class="stat-label">Lần thực hiện</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon best">
                <i class="fas fa-trophy"></i>
            </div>
            <div class="stat-number"><?= $stats['best_score'] ?></div>
            <div class="stat-label">Điểm cao nhất</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon average">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-number"><?= $stats['average_score'] ?></div>
            <div class="stat-label">Điểm trung bình</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon improvement">
                <i class="fas fa-arrow-<?= $stats['improvement'] >= 0 ? 'up' : 'down' ?>"></i>
            </div>
            <div class="stat-number <?= $stats['improvement'] >= 0 ? 'improvement-positive' : 'improvement-negative' ?>">
                <?= $stats['improvement'] > 0 ? '+' : '' ?><?= $stats['improvement'] ?>
            </div>
            <div class="stat-label">Cải thiện</div>
        </div>
    </div>

    <!-- Completion Status -->
    <div class="completion-status">
        <?php if ($stats['completion_status']): ?>
            <div class="status-icon status-completed">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="status-text status-completed">🎉 Đã hoàn thành!</div>
            <div class="status-description">
                Bạn đã đạt điểm tối thiểu <?= $lesson['DiemToiThieu'] ?> để hoàn thành bài học này.
            </div>
        <?php else: ?>
            <div class="status-icon status-incomplete">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="status-text status-incomplete">⚠️ Chưa hoàn thành</div>
            <div class="status-description">
                Bạn cần đạt tối thiểu <?= $lesson['DiemToiThieu'] ?> điểm để hoàn thành bài học này.
                <?php if ($stats['best_score'] > 0): ?>
                    Còn <?= round($lesson['DiemToiThieu'] - $stats['best_score'], 1) ?> điểm nữa!
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Progress Chart -->
    <?php if (count($results) > 1): ?>
    <div class="chart-container">
        <h3><i class="fas fa-chart-area"></i> Biểu đồ tiến độ</h3>
        <div class="progress-chart">
            <?php 
            $maxScore = max(array_column($results, 'TongDiem'));
            $chartResults = array_reverse($results); // Show chronologically
            foreach ($chartResults as $index => $result):
                $height = ($result['TongDiem'] / 10) * 160; // Max height 160px
            ?>
            <div class="chart-bar" style="height: <?= $height ?>px;" title="Lần <?= $index + 1 ?>: <?= $result['TongDiem'] ?> điểm">
                <div class="chart-bar-value"><?= $result['TongDiem'] ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Results History -->
    <div class="results-history">
        <h3><i class="fas fa-history"></i> Lịch sử kết quả</h3>
        
        <?php if (empty($results)): ?>
            <div class="no-results">
                <i class="fas fa-clipboard-list"></i>
                <h4>Chưa có kết quả</h4>
                <p>Bạn chưa thực hiện bài luyện tập này. Hãy bắt đầu ngay!</p>
            </div>
        <?php else: ?>
            <?php foreach ($results as $index => $result): 
                $scoreClass = '';
                if ($result['TongDiem'] >= 9) $scoreClass = 'excellent';
                elseif ($result['TongDiem'] >= 7) $scoreClass = 'good';
                elseif ($result['TongDiem'] >= 5) $scoreClass = 'average';
                else $scoreClass = 'poor';
            ?>
            <div class="result-item">
                <div class="result-score <?= $scoreClass ?>">
                    <div class="result-score-number"><?= $result['TongDiem'] ?></div>
                    <div class="result-score-text">điểm</div>
                </div>
                
                <div class="result-content">
                    <div class="result-meta">
                        <span><i class="fas fa-calendar"></i> <?= date('d/m/Y H:i', strtotime($result['ThoiGianNop'])) ?></span>
                        <span><i class="fas fa-hashtag"></i> Lần thứ <?= count($results) - $index ?></span>
                        <?php if ($result['TongDiem'] >= $lesson['DiemToiThieu']): ?>
                        <span style="color: #4CAF50;"><i class="fas fa-check"></i> Đạt yêu cầu</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($result['TextNhanDien']): ?>
                    <div class="result-spoken-text">
                        "<?= htmlspecialchars($result['TextNhanDien']) ?>"
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
        <a href="<?= BASE_URL("SpeakingPractice?topic=" . $lesson['MaChuDe']) ?>" class="btn-action btn-practice-again">
            <i class="fas fa-redo"></i> Luyện tập lại
        </a>
        <a href="<?= BASE_URL("SpeakingPractice?topic=" . $lesson['MaChuDe']) ?>" class="btn-action btn-back">
            <i class="fas fa-arrow-left"></i> Quay lại bài tập
        </a>
        <a href="<?= BASE_URL("Speaking") ?>" class="btn-action btn-back">
            <i class="fas fa-list"></i> Tất cả chủ đề
        </a>
    </div>
</div>

<script>
$(document).ready(function() {
    // Animate stats on load
    $('.stat-number').each(function() {
        const $this = $(this);
        const target = parseFloat($this.text());
        $this.text('0');
        
        $({ counter: 0 }).animate({ counter: target }, {
            duration: 1500,
            easing: 'swing',
            step: function() {
                $this.text(Math.ceil(this.counter));
            },
            complete: function() {
                $this.text(target);
            }
        });
    });
    
    // Animate chart bars
    $('.chart-bar').each(function(index) {
        $(this).delay(index * 100).animate({
            opacity: 1
        }, 300);
    });
    
    // Add hover effects to result items
    $('.result-item').hover(
        function() {
            $(this).find('.result-score').css('transform', 'scale(1.1)');
        },
        function() {
            $(this).find('.result-score').css('transform', 'scale(1)');
        }
    );
});
</script>

<?php
require_once(__DIR__ . "/footer.php");
?>