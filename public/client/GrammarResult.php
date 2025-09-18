<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");
require_once(__DIR__ . "/../../public/client/header.php");

// Get user info if logged in
$user = null;
if (isset($_SESSION["account"])) {
    $user = $Database->get_row("SELECT * FROM nguoidung WHERE taikhoan = '" . $_SESSION["account"] . "'");
}

// Get result data from session or URL parameters
$score = $_GET['score'] ?? 0;
$correct_answers = $_GET['correct'] ?? 0;
$total_questions = $_GET['total'] ?? 0;
$topic_id = $_GET['topic'] ?? 0;

// Get topic info
$topic = null;
if ($topic_id) {
    $topicQuery = $Database->get_row("SELECT * FROM grammar_topics WHERE MaChuDe = " . (int)$topic_id);
    $topic = $topicQuery;
}

// Get detailed results from the latest quiz attempt
$detailedResults = [];
if ($topic_id) {
    // Try to get user account from session or use 'admin' for testing
    $userAccount = $_SESSION["account"] ?? 'admin';
    
    // Get the latest quiz results for this topic
    $latestResultQuery = $Database->get_row("
        SELECT * FROM grammar_results 
        WHERE TaiKhoan = '$userAccount' 
        AND MaChuDe = " . (int)$topic_id . " 
        ORDER BY ThoiGianLam DESC 
        LIMIT 1
    ");
    
    if ($latestResultQuery) {
        // Get the exact time of the latest quiz result
        $resultTime = $latestResultQuery['ThoiGianLam'];
        
        // Get answers for this specific quiz attempt (within 1 minute to handle slight time differences)
        $answersQuery = $Database->get_list("
            SELECT ga.*, gq.CauHoi, gq.DapAnA, gq.DapAnB, gq.DapAnC, gq.DapAnD, gq.DapAnDung, gq.GiaiThich
            FROM grammar_answers ga
            JOIN grammar_questions gq ON ga.MaCauHoi = gq.MaCauHoi
            WHERE ga.TaiKhoan = '$userAccount'
            AND gq.MaChuDe = " . (int)$topic_id . "
            AND ABS(TIMESTAMPDIFF(SECOND, ga.ThoiGianLam, '$resultTime')) <= 60
            ORDER BY gq.ThuTu ASC, ga.MaCauHoi ASC
        ");
        
        if ($answersQuery) {
            $processedQuestions = []; // Track processed questions to avoid duplicates
            
            foreach ($answersQuery as $answer) {
                $questionId = $answer['MaCauHoi'];
                
                // Skip if we already processed this question
                if (isset($processedQuestions[$questionId])) {
                    continue;
                }
                
                // Mark this question as processed
                $processedQuestions[$questionId] = true;
                
                // Build answers array from individual columns
                $answers = [
                    'A' => $answer['DapAnA'],
                    'B' => $answer['DapAnB'], 
                    'C' => $answer['DapAnC'],
                    'D' => $answer['DapAnD']
                ];
                
                $detailedResults[] = [
                    'question' => $answer['CauHoi'],
                    'answers' => $answers,
                    'user_answer' => $answer['DapAnChon'],
                    'correct_answer' => $answer['DapAnDung'],
                    'is_correct' => $answer['DungSai'],
                    'explanation' => $answer['GiaiThich'] ?? ''
                ];
            }
        }
    }
}

// Debug output - temporary
if (isset($_GET['show_debug'])) {
    echo "<div style='background: red; color: white; padding: 20px;'>";
    echo "<h3>DETAILED DEBUG:</h3>";
    echo "<p>Topic ID: $topic_id</p>";
    echo "<p>User Account: " . ($userAccount ?? 'Not set') . "</p>";
    echo "<p>Latest Result Found: " . (isset($latestResultQuery) && $latestResultQuery ? 'YES' : 'NO') . "</p>";
    echo "<p>Detailed Results Count: " . count($detailedResults) . "</p>";
    if (!empty($detailedResults)) {
        echo "<p>First Question: " . substr($detailedResults[0]['question'], 0, 100) . "...</p>";
    }
    echo "</div>";
}

// Determine feedback message
$feedback = '';
$emoji = '';
if ($score >= 90) {
    $feedback = 'Xuất sắc! Bạn đã làm rất tốt!';
    $emoji = '🎉';
} else if ($score >= 70) {
    $feedback = 'Tốt! Bạn đã hiểu khá tốt về chủ đề này!';
    $emoji = '👏';
} else if ($score >= 50) {
    $feedback = 'Được! Bạn có thể cải thiện thêm!';
    $emoji = '👍';
} else {
    $feedback = 'Hãy cố gắng thêm! Bạn có thể làm tốt hơn!';
    $emoji = '💪';
}
?>

<style>
body {
    margin: 0;
    padding: 0;
    overflow-x: hidden;
}

.results-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 0;
    margin: 0;
}

.results-container {
    background: white;
    margin: 0;
    padding: 40px 60px;
    width: 100%;
    min-height: 100vh;
    text-align: center;
    box-shadow: none;
    border-radius: 0;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.results-title {
    font-size: 3.5rem;
    color: #333;
    margin-bottom: 40px;
    font-weight: 800;
}

.results-score {
    font-size: 6rem;
    font-weight: 900;
    background: linear-gradient(45deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 30px;
    text-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.results-emoji {
    font-size: 5rem;
    margin-bottom: 30px;
    animation: bounce 1s infinite;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-10px);
    }
    60% {
        transform: translateY(-5px);
    }
}

.results-feedback {
    font-size: 2rem;
    color: #555;
    margin-bottom: 50px;
    font-weight: 600;
}

.results-stats {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 60px;
    margin: 40px auto;
    max-width: 800px;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 20px;
    padding: 40px 60px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.stat-item {
    text-align: center;
    padding: 20px 35px;
    border-radius: 15px;
    background: rgba(255, 255, 255, 0.8);
    transition: transform 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    min-width: 120px;
}

.stat-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.stat-number {
    font-size: 3.5rem;
    font-weight: 900;
    display: block;
    margin-bottom: 10px;
}

.stat-number.correct {
    color: #28a745;
}

.stat-number.wrong {
    color: #dc3545;
}

.stat-number.total {
    color: #007bff;
}

.stat-label {
    font-size: 1.4rem;
    color: #666;
    font-weight: 600;
}

.results-actions {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-action {
    padding: 15px 30px;
    border-radius: 50px;
    font-size: 1.1rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.btn-primary {
    background: linear-gradient(45deg, #667eea, #764ba2);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    color: white;
    text-decoration: none;
}

.btn-secondary {
    background: linear-gradient(45deg, #6c757d, #495057);
    color: white;
}

.btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(108, 117, 125, 0.4);
    color: white;
    text-decoration: none;
}

.topic-info {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 30px;
}

.topic-name {
    font-size: 1.3rem;
    font-weight: 700;
    color: #667eea;
    margin-bottom: 5px;
}

.topic-level {
    font-size: 1rem;
    color: #666;
    font-weight: 500;
}

@media (max-width: 768px) {
    .results-container {
        padding: 20px 15px;
        min-height: 100vh;
    }
    
    .results-score {
        font-size: 3.5rem;
    }
    
    .results-title {
        font-size: 2rem;
    }
    
    .results-stats {
        flex-direction: column;
        gap: 20px;
        margin: 30px auto;
        padding: 30px 20px;
        max-width: 100%;
    }
    
    .stat-item {
        min-width: auto;
        width: 100%;
        padding: 15px 25px;
    }
    
    .results-actions {
        flex-direction: column;
    }
    
    .btn-action {
        width: 100%;
        justify-content: center;
    }
}

/* Quiz Review Section trong Results Container */
.quiz-review-section {
    margin-top: 40px;
    text-align: left;
    width: 100%;
    max-width: none;
    margin-left: 0;
    margin-right: 0;
    padding: 0 40px;
}

.quiz-review-section .review-title {
    font-size: 2.8rem;
    color: #333;
    margin-bottom: 40px;
    text-align: center;
    font-weight: 700;
    border-bottom: 4px solid #667eea;
    padding-bottom: 25px;
}

.quiz-review-section .question-item {
    background: #f8f9fa;
    border-radius: 20px;
    padding: 40px 50px;
    margin-bottom: 30px;
    border-left: 6px solid #667eea;
    transition: all 0.3s ease;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
    width: 100%;
}

.quiz-review-section .question-item.correct {
    border-left-color: #28a745;
    background: linear-gradient(135deg, rgba(40, 167, 69, 0.05), rgba(40, 167, 69, 0.1));
}

.quiz-review-section .question-item.incorrect {
    border-left-color: #dc3545;
    background: linear-gradient(135deg, rgba(220, 53, 69, 0.05), rgba(220, 53, 69, 0.1));
}

.quiz-review-section .question-number {
    display: inline-block;
    background: #667eea;
    color: white;
    padding: 10px 18px;
    border-radius: 18px;
    font-weight: 700;
    font-size: 1.1rem;
    margin-bottom: 18px;
}

.quiz-review-section .question-item.correct .question-number {
    background: #28a745;
}

.quiz-review-section .question-item.incorrect .question-number {
    background: #dc3545;
}

.quiz-review-section .question-text {
    font-size: 1.6rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 25px;
    line-height: 1.6;
}

.quiz-review-section .answers-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 25px;
}

.quiz-review-section .answer-option {
    padding: 18px 25px;
    border-radius: 12px;
    font-size: 1.3rem;
    font-weight: 500;
    border: 3px solid #e9ecef;
    background: white;
    transition: all 0.3s ease;
}

.quiz-review-section .answer-option.correct {
    background: #d4edda;
    border-color: #28a745;
    color: #155724;
}

.quiz-review-section .answer-option.incorrect {
    background: #f8d7da;
    border-color: #dc3545;
    color: #721c24;
}

.quiz-review-section .answer-option.user-choice {
    position: relative;
}

.quiz-review-section .answer-option.user-choice::after {
    content: "👆 Bạn đã chọn";
    position: absolute;
    top: -30px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 1rem;
    color: #666;
    background: white;
    padding: 5px 12px;
    border-radius: 12px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
    font-weight: 600;
}

.quiz-review-section .explanation {
    background: linear-gradient(135deg, #e3f2fd, #bbdefb);
    border-radius: 12px;
    padding: 25px;
    margin-top: 20px;
    border-left: 5px solid #2196f3;
}

.quiz-review-section .explanation-title {
    font-weight: 700;
    color: #1976d2;
    margin-bottom: 12px;
    font-size: 1.3rem;
}

.quiz-review-section .explanation-text {
    color: #333;
    line-height: 1.6;
    font-size: 1.2rem;
}

.quiz-review-section .result-status {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    border-radius: 18px;
    font-weight: 600;
    font-size: 1.1rem;
    margin-bottom: 18px;
}

.quiz-review-section .result-status.correct {
    background: #d4edda;
    color: #155724;
}

.quiz-review-section .result-status.incorrect {
    background: #f8d7da;
    color: #721c24;
}

/* Quiz Review Styles */
.quiz-review {
    background: white;
    border-radius: 20px;
    padding: 30px;
    margin-top: 30px;
    max-width: 1000px;
    width: 100%;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
}

.review-title {
    font-size: 2rem;
    color: #333;
    margin-bottom: 30px;
    text-align: center;
    font-weight: 700;
    border-bottom: 3px solid #667eea;
    padding-bottom: 15px;
}

.question-item {
    background: #f8f9fa;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 25px;
    border-left: 5px solid #667eea;
    transition: all 0.3s ease;
}

.question-item.correct {
    border-left-color: #28a745;
    background: linear-gradient(135deg, rgba(40, 167, 69, 0.05), rgba(40, 167, 69, 0.1));
}

.question-item.incorrect {
    border-left-color: #dc3545;
    background: linear-gradient(135deg, rgba(220, 53, 69, 0.05), rgba(220, 53, 69, 0.1));
}

.question-number {
    display: inline-block;
    background: #667eea;
    color: white;
    padding: 8px 15px;
    border-radius: 20px;
    font-weight: 700;
    font-size: 0.9rem;
    margin-bottom: 15px;
}

.question-item.correct .question-number {
    background: #28a745;
}

.question-item.incorrect .question-number {
    background: #dc3545;
}

.question-text {
    font-size: 1.2rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 20px;
    line-height: 1.6;
}

.answers-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 20px;
}

.answer-option {
    padding: 12px 20px;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 500;
    border: 2px solid #e9ecef;
    background: white;
    transition: all 0.3s ease;
}

.answer-option.correct {
    background: #d4edda;
    border-color: #28a745;
    color: #155724;
}

.answer-option.incorrect {
    background: #f8d7da;
    border-color: #dc3545;
    color: #721c24;
}

.answer-option.user-choice {
    position: relative;
}

.answer-option.user-choice::after {
    content: "👆 Bạn đã chọn";
    position: absolute;
    top: -25px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 0.8rem;
    color: #666;
    background: white;
    padding: 2px 8px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.explanation {
    background: linear-gradient(135deg, #e3f2fd, #bbdefb);
    border-radius: 10px;
    padding: 20px;
    margin-top: 15px;
    border-left: 4px solid #2196f3;
}

.explanation-title {
    font-weight: 700;
    color: #1976d2;
    margin-bottom: 10px;
    font-size: 1.1rem;
}

.explanation-text {
    color: #333;
    line-height: 1.6;
    font-size: 1rem;
}

.result-status {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 15px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 15px;
}

.result-status.correct {
    background: #d4edda;
    color: #155724;
}

.result-status.incorrect {
    background: #f8d7da;
    color: #721c24;
}

@media (max-width: 768px) {
    .answers-grid {
        grid-template-columns: 1fr;
    }
    
    .quiz-review {
        margin: 20px 10px;
        padding: 20px;
    }
    
    .question-item {
        padding: 20px;
    }
    
    /* Quiz Review Section Responsive */
    .quiz-review-section {
        padding: 0 15px;
        width: 100%;
    }
    
    .quiz-review-section .review-title {
        font-size: 2.2rem;
        margin-bottom: 25px;
        padding-bottom: 20px;
    }
    
    .quiz-review-section .answers-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .quiz-review-section .question-item {
        padding: 25px 20px;
        margin-bottom: 20px;
    }
    
    .quiz-review-section .question-text {
        font-size: 1.3rem;
        margin-bottom: 20px;
    }
    
    .quiz-review-section .answer-option {
        padding: 15px 20px;
        font-size: 1.1rem;
    }
    
    .quiz-review-section .answer-option.user-choice::after {
        position: static;
        transform: none;
        display: block;
        margin-top: 8px;
        text-align: center;
        font-size: 0.9rem;
    }
}
</style>

<div class="results-page">
    <div class="results-container">
        <h1 class="results-title">Kết quả của bạn</h1>
        
        <?php if ($topic): ?>
        <div class="topic-info">
            <div class="topic-name"><?= htmlspecialchars($topic['TenChuDe']) ?></div>
            <div class="topic-level">Mô tả: <?= htmlspecialchars($topic['MoTa']) ?></div>
        </div>
        <?php endif; ?>
        
        <div class="results-emoji"><?= $emoji ?></div>
        <div class="results-score"><?= $score ?>%</div>
        <div class="results-feedback"><?= $feedback ?></div>
        
        <div class="results-stats">
            <div class="stat-item">
                <span class="stat-number correct"><?= $correct_answers ?></span>
                <span class="stat-label">Đúng</span>
            </div>
            <div class="stat-item">
                <span class="stat-number wrong"><?= $total_questions - $correct_answers ?></span>
                <span class="stat-label">Sai</span>
            </div>
            <div class="stat-item">
                <span class="stat-number total"><?= $total_questions ?></span>
                <span class="stat-label">Tổng</span>
            </div>
        </div>
        
        <?php if (!empty($detailedResults)): ?>
        <div class="quiz-review-section">
            <h2 class="review-title">
                <i class="fas fa-clipboard-list"></i>
                Chi tiết từng câu hỏi (<?= $total_questions ?> câu)
            </h2>
            
            <?php foreach ($detailedResults as $index => $result): ?>
            <div class="question-item <?= $result['is_correct'] ? 'correct' : 'incorrect' ?>">
                <span class="question-number">Câu <?= $index + 1 ?></span>
                
                <div class="result-status <?= $result['is_correct'] ? 'correct' : 'incorrect' ?>">
                    <?php if ($result['is_correct']): ?>
                        <i class="fas fa-check-circle"></i>
                        Đúng
                    <?php else: ?>
                        <i class="fas fa-times-circle"></i>
                        Sai
                    <?php endif; ?>
                </div>
                
                <div class="question-text">
                    <?= htmlspecialchars($result['question']) ?>
                </div>
                
                <div class="answers-grid">
                    <?php foreach ($result['answers'] as $key => $answer): ?>
                        <?php 
                        $isCorrect = ($key === $result['correct_answer']);
                        $isUserChoice = ($key === $result['user_answer']);
                        $classes = [];
                        
                        if ($isCorrect) {
                            $classes[] = 'correct';
                        } elseif ($isUserChoice && !$isCorrect) {
                            $classes[] = 'incorrect';
                        }
                        
                        if ($isUserChoice) {
                            $classes[] = 'user-choice';
                        }
                        ?>
                        <div class="answer-option <?= implode(' ', $classes) ?>">
                            <strong><?= strtoupper($key) ?>.</strong> <?= htmlspecialchars($answer) ?>
                            <?php if ($isCorrect): ?>
                                <i class="fas fa-check" style="color: #28a745; margin-left: 10px;"></i>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (!empty($result['explanation'])): ?>
                <div class="explanation">
                    <div class="explanation-title">
                        <i class="fas fa-lightbulb"></i>
                        Giải thích:
                    </div>
                    <div class="explanation-text">
                        <?= htmlspecialchars($result['explanation']) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <div class="results-actions">
            <a href="<?= BASE_URL("GrammarQuiz") ?>?topic=<?= $topic_id ?>" class="btn-action btn-primary">
                <i class="fas fa-redo"></i>
                Làm lại
            </a>
            <a href="<?= BASE_URL("Grammar") ?>" class="btn-action btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
            </a>
        </div>
    </div>
</div>

<script>
// Add some interactive effects
$(document).ready(function() {
    // Animate score counter
    let targetScore = <?= $score ?>;
    let currentScore = 0;
    let increment = targetScore / 50;
    
    let scoreAnimation = setInterval(function() {
        currentScore += increment;
        if (currentScore >= targetScore) {
            currentScore = targetScore;
            clearInterval(scoreAnimation);
        }
        $('.results-score').text(Math.round(currentScore) + '%');
    }, 20);
    
    // Add hover effects to buttons
    $('.btn-action').hover(
        function() {
            $(this).css('transform', 'translateY(-2px) scale(1.02)');
        },
        function() {
            $(this).css('transform', 'translateY(0) scale(1)');
        }
    );
    
    // Add smooth animations for question items
    $('.question-item').each(function(index) {
        $(this).css({
            'opacity': '0',
            'transform': 'translateY(30px)'
        }).delay(index * 100).animate({
            'opacity': '1'
        }, 500).css('transform', 'translateY(0)');
    });
    
    // Add click effect to question items
    $('.question-item').click(function() {
        $(this).addClass('clicked');
        setTimeout(() => {
            $(this).removeClass('clicked');
        }, 200);
    });
    
    // Add CSS for clicked effect
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .question-item.clicked {
                transform: scale(0.98);
                transition: transform 0.2s ease;
            }
            .question-item {
                transition: transform 0.2s ease, box-shadow 0.3s ease;
            }
            .question-item:hover {
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
                transform: translateY(-2px);
            }
        `)
        .appendTo('head');
    
    // Add scroll to top button
    if ($('.quiz-review').length > 0) {
        $('body').append('<button id="scrollToTop" style="position: fixed; bottom: 20px; right: 20px; background: #667eea; color: white; border: none; border-radius: 50%; width: 50px; height: 50px; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.2); display: none; z-index: 1000;"><i class="fas fa-arrow-up"></i></button>');
        
        $(window).scroll(function() {
            if ($(this).scrollTop() > 300) {
                $('#scrollToTop').fadeIn();
            } else {
                $('#scrollToTop').fadeOut();
            }
        });
        
        $('#scrollToTop').click(function() {
            $('html, body').animate({scrollTop: 0}, 600);
        });
    }
});
</script>

<?php require_once(__DIR__ . "/../../public/client/footer.php"); ?>
