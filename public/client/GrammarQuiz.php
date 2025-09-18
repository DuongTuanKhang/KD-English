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

$title = 'Grammar Quiz | ' . $Database->site("TenWeb");

// Handle AJAX requests
if (isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] == 'submit_quiz') {
        try {
            $topicId = (int)$_POST['topic_id'];
            $answers = $_POST['answers'] ?? [];
            
            // Get questions for this topic
            $questions = $Database->get_list("
                SELECT MaCauHoi, DapAnDung 
                FROM grammar_questions 
                WHERE MaChuDe = $topicId AND TrangThai = 1
                ORDER BY ThuTu ASC, MaCauHoi ASC
            ");
            
            if (empty($questions)) {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy câu hỏi']);
                exit;
            }
        
        $totalQuestions = count($questions);
        $correctAnswers = 0;
        $results = [];
        
        // Calculate score first
        foreach ($questions as $question) {
            $questionId = $question['MaCauHoi'];
            $correctAnswer = $question['DapAnDung'];
            $userAnswer = $answers[$questionId] ?? '';
            
            $isCorrect = ($userAnswer == $correctAnswer);
            if ($isCorrect) $correctAnswers++;
            
            $results[] = [
                'question_id' => $questionId,
                'user_answer' => $userAnswer,
                'correct_answer' => $correctAnswer,
                'is_correct' => $isCorrect
            ];
        }
        
        $score = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 2) : 0;
        
        // Save quiz result first to get MaKetQua
        $insertSuccess = $Database->insert('grammar_results', [
            'TaiKhoan' => $user['TaiKhoan'],
            'MaChuDe' => $topicId,
            'TongSoCau' => $totalQuestions,
            'SoCauDung' => $correctAnswers,
            'DiemSo' => $score,
            'ThoiGianLam' => date('Y-m-d H:i:s')
        ]);
        
        if (!$insertSuccess) {
            echo json_encode(['success' => false, 'message' => 'Lỗi lưu kết quả']);
            exit;
        }
        
        // Get the inserted result ID
        $resultId = $Database->get_row("SELECT LAST_INSERT_ID() as id")['id'];
        
        // Now save individual answers
        foreach ($results as $result) {
            $Database->insert('grammar_answers', [
                'MaKetQua' => $resultId,
                'TaiKhoan' => $user['TaiKhoan'],
                'MaCauHoi' => $result['question_id'],
                'DapAnChon' => $result['user_answer'],
                'DungSai' => $result['is_correct'] ? 1 : 0,
                'ThoiGianLam' => date('Y-m-d H:i:s')
            ]);
        }
        
        echo json_encode([
            'success' => true,
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctAnswers,
            'score' => $score,
            'results' => $results
        ]);
        exit;
        
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi xử lý: ' . $e->getMessage()
            ]);
            exit;
        }
    }
    
    exit;
}

// Get topic ID from URL
$topicId = $_GET['topic'] ?? null;
if (!$topicId) {
    redirect(BASE_URL("Grammar"));
}

// Get topic info
$topic = $Database->get_row("SELECT * FROM grammar_topics WHERE MaChuDe = " . (int)$topicId . " AND TrangThai = 1");
if (!$topic) {
    redirect(BASE_URL("Grammar"));
}

// Get questions for this topic
$questions = $Database->get_list("
    SELECT * FROM grammar_questions 
    WHERE MaChuDe = " . (int)$topicId . " AND TrangThai = 1
    ORDER BY ThuTu ASC, MaCauHoi ASC
");

if (count($questions) == 0) {
    $noQuestions = true;
}

// Get user's recent results for this topic
$recentResults = $Database->get_list("
    SELECT * FROM grammar_results 
    WHERE TaiKhoan = '" . $user['TaiKhoan'] . "' AND MaChuDe = " . (int)$topicId . "
    ORDER BY ThoiGianLam DESC
    LIMIT 5
");

require_once(__DIR__ . "/../../public/client/header.php");
?>

<style>
body {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    min-height: 100vh;
}

.grammar-quiz-container {
    width: 100%;
    max-width: 100%;
    margin: 0;
    padding: 20px 40px;
    min-height: 100vh;
}

.quiz-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px 40px;
    border-radius: 15px;
    margin-bottom: 30px;
    text-align: center;
}

.quiz-header h1 {
    margin-bottom: 10px;
    font-size: 3.2rem;
    font-weight: 800;
}

.quiz-header .subtitle {
    font-size: 1.4rem;
    opacity: 0.95;
    font-weight: 500;
}

.quiz-stats {
    display: flex;
    justify-content: center;
    gap: 40px;
    margin-top: 30px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.stat-item {
    text-align: center;
}



.stat-item .number {
    font-size: 2.8rem;
    font-weight: 900;
    display: block;
    color: #ffffff;
    text-shadow: 0 2px 6px rgba(0, 0, 0, 0.5);
    background: linear-gradient(45deg, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0.25));
    padding: 8px 16px;
    border-radius: 15px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(10px);
}

.stat-item .label {
    font-size: 1.2rem;
    color: #ffffff;
    font-weight: 700;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
    margin-top: 8px;
    display: block;
}

.quiz-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    margin-bottom: 30px;
}

@media (max-width: 1200px) {
    .quiz-content {
        grid-template-columns: 1fr;
        gap: 20px;
    }
}

.question-card {
    background: white;
    border-radius: 20px;
    padding: 35px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    border: 1px solid #f0f3ff;
    transition: all 0.3s ease;
    height: fit-content;
    margin-bottom: 25px;
    position: relative;
    overflow: hidden;
}

.question-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
}

.question-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
}

.question-card:hover {
    transform: translateY(-2px);
}

.question-number {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 20px;
    font-size: 1.2rem;
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    flex-shrink: 0;
}

.question-text {
    font-size: 2.5rem;
    font-weight: 600;
    margin-bottom: 25px;
    line-height: 1.7;
    color: #2c3e50;
}

.answer-option {
    background: linear-gradient(145deg, #f8f9ff 0%, #ffffff 100%);
    border: 3px solid #e8ecff;
    border-radius: 12px;
    padding: 20px 25px;
    margin-bottom: 15px;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    font-size: 1.1rem;
    font-weight: 500;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.08);
    position: relative;
    overflow: hidden;
    z-index: 1;
    user-select: none;
}

.answer-option::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.1), transparent);
    transition: left 0.5s;
}

.answer-option:hover::before {
    left: 100%;
}

.answer-option:hover {
    background: linear-gradient(145deg, #e8ecff 0%, #f0f3ff 100%);
    border-color: #667eea;
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.2);
}

.hidden-radio {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
    pointer-events: none;
}

.answer-option.selected {
    background: linear-gradient(145deg, #667eea 0%, #764ba2 100%);
    border-color: #667eea;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.answer-option input[type="radio"] {
    margin-right: 12px;
    transform: scale(1.2);
}

.option-letter {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 18px;
    font-size: 1.1rem;
    box-shadow: 0 3px 10px rgba(102, 126, 234, 0.3);
    transition: all 0.3s ease;
}

.answer-option.selected .option-letter {
    background: white;
    color: #667eea;
    transform: scale(1.1);
    box-shadow: 0 5px 15px rgba(255, 255, 255, 0.3);
}

.answer-option span {
    font-size: 2.2rem;
    line-height: 1.5;
    color: #2c3e50;
}

.answer-option.selected span {
    color: white;
    font-weight: 600;
}

.quiz-navigation {
    background: white;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-top: 30px;
    text-align: center;
}

.sidebar-column {
    position: sticky;
    top: 20px;
    height: fit-content;
}

.sidebar-column h4 {
    color: #2c3e50;
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.sidebar-column h4 i {
    color: #667eea;
    font-size: 1.2rem;
}

.question-navigator {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 10px;
    margin: 15px 0;
}

.nav-question {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 2rem;
    cursor: pointer;
    transition: all 0.3s ease;
    background: linear-gradient(145deg, #f8f9ff 0%, #ffffff 100%);
    border: 2px solid #e8ecff;
    color: #666;
    box-shadow: 0 3px 10px rgba(102, 126, 234, 0.1);
}

.nav-question.answered {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border-color: #28a745;
    color: white;
    box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
}

.nav-question.current {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: #667eea;
    color: white;
    transform: scale(1.1);
    box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
}

.nav-question:hover {
    transform: scale(1.05);
    border-color: #667eea;
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
}

.navigator-legend {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #e1e5e9;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 2rem;
    font-weight: 500;
    color: #2c3e50;
}

.legend-dot {
    width: 20px;
    height: 20px;
    border-radius: 6px;
    border: 2px solid #e1e5e9;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.legend-dot.answered {
    background: #28a745;
    border-color: #28a745;
}

.legend-dot.current {
    background: #667eea;
    border-color: #667eea;
}

.legend-dot.unanswered {
    background: #f8f9ff;
    border-color: #e1e5e9;
}

.quiz-info {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #f0f3ff;
    font-size: 2.0rem;
}

.info-item:last-child {
    border-bottom: none;
}

.info-item strong {
    color: #2c3e50;
    font-weight: 600;
}

.info-item span {
    color: #667eea;
    font-weight: 700;
    font-size: 2rem;
}

.btn-submit-quiz {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 18px 50px;
    border-radius: 30px;
    font-size: 1.3rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.4s ease;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    position: relative;
    overflow: hidden;
}

.btn-submit-quiz::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.btn-submit-quiz:hover::before {
    left: 100%;
}

.btn-submit-quiz:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
}

.btn-submit-quiz:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
}

.progress-bar {
    width: 100%;
    height: 8px;
    background: #e1e5e9;
    border-radius: 4px;
    margin: 20px 0;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    width: 0%;
    transition: width 0.3s ease;
}

.recent-results {
    background: linear-gradient(135deg, #ffffff, #f8f9fc);
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(102, 126, 234, 0.1);
}

.recent-results h3 {
    color: #667eea;
    margin-bottom: 20px;
}

.result-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid #e8ecf0;
    font-size: 2.1rem;
    font-weight: 500;
}

.result-item:last-child {
    border-bottom: none;
}

.result-score-badge {
    background: linear-gradient(45deg, #667eea, #764ba2);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 700;
    font-size: 1.1rem;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.4);
    border: 2px solid rgba(255, 255, 255, 0.2);
}

.no-questions {
    text-align: center;
    padding: 60px 20px;
}

.no-questions i {
    font-size: 4rem;
    color: #ccc;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .grammar-quiz-container {
        padding: 10px;
    }
    
    .quiz-header {
        padding: 20px;
    }
    
    .quiz-header h1 {
        font-size: 2rem;
    }
    
    .question-card {
        padding: 20px;
    }
    
    .quiz-stats {
        flex-direction: column;
        gap: 10px;
    }
}
</style>

<div class="grammar-quiz-container">
    <?php if (isset($noQuestions)): ?>
        <div class="no-questions">
            <i class="fas fa-question-circle"></i>
            <h3>Chưa có câu hỏi</h3>
            <p>Chủ đề này chưa có câu hỏi nào. Vui lòng quay lại sau!</p>
            <a href="<?= BASE_URL("Grammar") ?>" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    <?php else: ?>
        <!-- Quiz Header -->
        <div class="quiz-header">
            <h1><?= htmlspecialchars($topic['TenChuDe']) ?></h1>
            <p class="subtitle"><?= htmlspecialchars($topic['TenChuDeEng']) ?></p>
            <?php if ($topic['MoTa']): ?>
                <p class="subtitle"><?= htmlspecialchars($topic['MoTa']) ?></p>
            <?php endif; ?>
            
            <div class="quiz-stats">
                <div class="stat-item">
                    <span class="number"><?= count($questions) ?></span>
                    <span class="label">Câu hỏi</span>
                </div>
                <div class="stat-item">
                    <span class="number"><?= $topic['CapDo'] ?></span>
                    <span class="label">Cấp độ</span>
                </div>
                <div class="stat-item">
                    <span class="number"><?= count($recentResults) ?></span>
                    <span class="label">Lần đã làm</span>
                </div>
            </div>
        </div>

        <!-- Recent Results -->
        <?php if (count($recentResults) > 0): ?>
            <div class="recent-results">
                <h3><i class="fas fa-history"></i> Kết quả gần đây</h3>
                <?php foreach ($recentResults as $index => $result): ?>
                    <div class="result-item">
                        <div>
                            <strong>Lần <?= $index + 1 ?></strong>
                            <small class="text-muted d-block"><?= date('d/m/Y H:i', strtotime($result['ThoiGianLam'])) ?></small>
                        </div>
                        <div>
                            <span class="result-score-badge"><?= $result['DiemSo'] ?>%</span>
                            <small class="text-muted">(<?= $result['SoCauDung'] ?>/<?= $result['TongSoCau'] ?>)</small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Quiz Form -->
        <form id="grammarQuizForm">
            <input type="hidden" name="topic_id" value="<?= $topic['MaChuDe'] ?>">
            
            <!-- Progress Bar -->
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>
            
            <!-- Quiz Content Grid -->
            <div class="quiz-content">
                <div class="questions-column">
                    <!-- Questions -->
                    <?php foreach ($questions as $index => $question): ?>
                        <div class="question-card" data-question="<?= $index + 1 ?>">
                            <div class="d-flex align-items-start">
                                <div class="question-number"><?= $index + 1 ?></div>
                                <div class="flex-grow-1">
                                    <div class="question-text"><?= htmlspecialchars($question['CauHoi']) ?></div>
                                    
                                    <div class="answer-options">
                                        <label class="answer-option" data-option="A" onclick="selectAnswer(this)">
                                            <div class="option-letter">A</div>
                                            <input type="radio" name="answers[<?= $question['MaCauHoi'] ?>]" value="A" class="hidden-radio">
                                            <span><?= htmlspecialchars($question['DapAnA']) ?></span>
                                        </label>
                                        
                                        <label class="answer-option" data-option="B" onclick="selectAnswer(this)">
                                            <div class="option-letter">B</div>
                                            <input type="radio" name="answers[<?= $question['MaCauHoi'] ?>]" value="B" class="hidden-radio">
                                            <span><?= htmlspecialchars($question['DapAnB']) ?></span>
                                        </label>
                                
                                <?php if ($question['DapAnC']): ?>
                                    <label class="answer-option" data-option="C" onclick="selectAnswer(this)">
                                        <div class="option-letter">C</div>
                                        <input type="radio" name="answers[<?= $question['MaCauHoi'] ?>]" value="C" class="hidden-radio">
                                        <span><?= htmlspecialchars($question['DapAnC']) ?></span>
                                    </label>
                                <?php endif; ?>
                                
                                <?php if ($question['DapAnD']): ?>
                                    <label class="answer-option" data-option="D" onclick="selectAnswer(this)">
                                        <div class="option-letter">D</div>
                                        <input type="radio" name="answers[<?= $question['MaCauHoi'] ?>]" value="D" class="hidden-radio">
                                        <span><?= htmlspecialchars($question['DapAnD']) ?></span>
                                    </label>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
                </div>
                
                <!-- Sidebar Column -->
                <div class="sidebar-column">
                    <!-- Quiz Navigator -->
                    <div class="question-card">
                        <h4><i class="fas fa-map"></i> Điều hướng bài thi</h4>
                        <div class="question-navigator">
                            <?php foreach ($questions as $index => $question): ?>
                                <div class="nav-question" data-question="<?= $index + 1 ?>">
                                    <?= $index + 1 ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="navigator-legend">
                            <div class="legend-item">
                                <div class="legend-dot answered"></div>
                                <span>Đã trả lời</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-dot current"></div>
                                <span>Hiện tại</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-dot unanswered"></div>
                                <span>Chưa trả lời</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quiz Info -->
                    <div class="question-card">
                        <h4><i class="fas fa-info-circle"></i> Thông tin bài thi</h4>
                        <div class="quiz-info">
                            <div class="info-item">
                                <strong>Tổng số câu:</strong> <?= count($questions) ?>
                            </div>
                            <div class="info-item">
                                <strong>Đã trả lời:</strong> <span id="answeredCount">0</span>
                            </div>
                            <div class="info-item">
                                <strong>Còn lại:</strong> <span id="remainingCount"><?= count($questions) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Submit Button -->
            <div class="quiz-navigation">
                <button type="submit" class="btn-submit-quiz" id="submitBtn" disabled>
                    <i class="fas fa-paper-plane"></i> Nộp bài
                </button>
                <p class="text-muted mt-2">Vui lòng trả lời tất cả câu hỏi trước khi nộp bài</p>
            </div>
        </form>
        
    <?php endif; ?>
</div>



<script>
let totalQuestions = <?= count($questions) ?>;
let answeredQuestions = 0;

// Vanilla JavaScript approach for better compatibility
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM ready');
    
    // Find all answer options
    const answerOptions = document.querySelectorAll('.answer-option');
    console.log('Found answer options:', answerOptions.length);
    
    // Add click event to each answer option
    answerOptions.forEach(function(option) {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Answer option clicked!');
            
            // Find the question card
            const questionCard = this.closest('.question-card');
            if (!questionCard) return;
            
            // Remove selected class from all options in this question
            const allOptions = questionCard.querySelectorAll('.answer-option');
            allOptions.forEach(opt => opt.classList.remove('selected'));
            
            // Add selected class to clicked option
            this.classList.add('selected');
            
            // Check the radio button
            const radioBtn = this.querySelector('input[type="radio"]');
            if (radioBtn) {
                radioBtn.checked = true;
                console.log('Radio checked:', radioBtn.value);
            }
            
            // Update progress
            updateProgress();
        });
    });
    
    // Also handle direct radio button changes
    const radioButtons = document.querySelectorAll('input[type="radio"]');
    radioButtons.forEach(function(radio) {
        radio.addEventListener('change', function() {
            console.log('Radio changed directly');
            const answerOption = this.closest('.answer-option');
            const questionCard = answerOption.closest('.question-card');
            
            // Remove selected class from other options in this question
            const allOptions = questionCard.querySelectorAll('.answer-option');
            allOptions.forEach(opt => opt.classList.remove('selected'));
            
            // Add selected class to current option
            answerOption.classList.add('selected');
            
            // Update progress
            updateProgress();
        });
    });
    
    // jQuery part for existing functionality
    if (typeof $ !== 'undefined') {
        // Add entrance animations
        $('.question-card').each(function(index) {
            $(this).css({
                'opacity': '0',
                'transform': 'translateY(30px)'
            }).delay(index * 150).animate({
                'opacity': '1'
            }, 600, function() {
                $(this).css('transform', 'translateY(0)');
            });
        });
        
        $('.sidebar-column .question-card').css({
            'opacity': '0',
            'transform': 'translateX(30px)'
        }).delay(300).animate({
            'opacity': '1'
        }, 600, function() {
            $(this).css('transform', 'translateX(0)');
        });
        
        // Handle form submission
        $('#grammarQuizForm').submit(function(e) {
            e.preventDefault();
            submitQuiz();
        });
        
        // Handle question navigator clicks
        $('.nav-question').click(function() {
            const questionNum = $(this).data('question');
            const targetQuestion = $(`.question-card[data-question="${questionNum}"]`);
            
            // Scroll to question
            $('html, body').animate({
                scrollTop: targetQuestion.offset().top - 100
            }, 500);
            
            // Update current indicator
            $('.nav-question').removeClass('current');
            $(this).addClass('current');
        });
        
        // Initialize first question as current
        $('.nav-question[data-question="1"]').addClass('current');
    }
});

function updateProgress() {
    // Count using vanilla JS for better compatibility
    answeredQuestions = document.querySelectorAll('input[type="radio"]:checked').length;
    
    const progressPercent = (answeredQuestions / totalQuestions) * 100;
    
    // Update progress bar
    const progressFill = document.getElementById('progressFill');
    if (progressFill) {
        progressFill.style.width = progressPercent + '%';
    }
    
    // Update sidebar info
    const answeredCount = document.getElementById('answeredCount');
    const remainingCount = document.getElementById('remainingCount');
    
    if (answeredCount) answeredCount.textContent = answeredQuestions;
    if (remainingCount) remainingCount.textContent = totalQuestions - answeredQuestions;
    
    // Update question navigator
    document.querySelectorAll('.nav-question').forEach(function(navBtn) {
        const questionNum = navBtn.getAttribute('data-question');
        const questionCard = document.querySelector(`.question-card[data-question="${questionNum}"]`);
        
        if (questionCard) {
            const isAnswered = questionCard.querySelector('input[type="radio"]:checked') !== null;
            
            navBtn.classList.remove('answered');
            if (isAnswered) {
                navBtn.classList.add('answered');
            }
        }
    });
    
    // Enable/disable submit button
    const submitBtn = document.getElementById('submitBtn');
    if (submitBtn) {
        if (answeredQuestions === totalQuestions) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Nộp bài';
        } else {
            submitBtn.disabled = true;
            submitBtn.textContent = `Đã trả lời ${answeredQuestions}/${totalQuestions} câu`;
        }
    }
}

function submitQuiz() {
    if (answeredQuestions < totalQuestions) {
        alert('Vui lòng trả lời tất cả câu hỏi!');
        return;
    }
    
    $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang chấm bài...');
    
    const formData = new FormData($('#grammarQuizForm')[0]);
    formData.append('ajax', '1');
    formData.append('action', 'submit_quiz');
    
    $.ajax({
        url: window.location.href,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showResults(response);
            } else {
                alert('Có lỗi xảy ra khi chấm bài!');
                $('#submitBtn').prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Nộp bài');
            }
        },
        error: function() {
            alert('Có lỗi xảy ra khi chấm bài!');
            $('#submitBtn').prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Nộp bài');
        }
    });
}

function showResults(data) {
    const score = data.score;
    const correctAnswers = data.correct_answers;
    const totalQuestions = data.total_questions;
    const topicId = <?= $topic_id ?>;
    
    // Redirect to results page instead of showing modal
    window.location.href = `${window.location.origin}/webhocngoaingu/public/client/GrammarResult.php?score=${score}&correct=${correctAnswers}&total=${totalQuestions}&topic=${topicId}`;
}


</script>

<script>
let totalQuestions = <?= count($questions) ?>;

function selectAnswer(element) {
    console.log('Answer clicked!', element);
    
    const radio = element.querySelector('input[type="radio"]');
    if (!radio) return;
    
    const questionCard = element.closest('.question-card');
    if (!questionCard) return;
    
    // Clear selections
    questionCard.querySelectorAll('.answer-option').forEach(opt => opt.classList.remove('selected'));
    
    // Select this option
    element.classList.add('selected');
    radio.checked = true;
    
    console.log('Selected:', radio.value);
    updateProgress();
}

function updateProgress() {
    const answeredQuestions = document.querySelectorAll('input[type="radio"]:checked').length;
    console.log('Progress:', answeredQuestions, '/', totalQuestions);
    
    // Update navigation indicators  
    document.querySelectorAll('.nav-question').forEach(function(navBtn) {
        const questionNum = navBtn.getAttribute('data-question');
        const questionCard = document.querySelector(`.question-card[data-question="${questionNum}"]`);
        
        if (questionCard) {
            const isAnswered = questionCard.querySelector('input[type="radio"]:checked') !== null;
            
            navBtn.classList.remove('answered');
            if (isAnswered) {
                navBtn.classList.add('answered'); // Màu xanh lá cây
            }
        }
    });
    
    const submitBtn = document.getElementById('submitBtn');
    if (submitBtn) {
        if (answeredQuestions === totalQuestions) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Nộp bài';
            submitBtn.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
            submitBtn.style.color = 'white';
        } else {
            submitBtn.disabled = true;
            submitBtn.textContent = `Đã trả lời ${answeredQuestions}/${totalQuestions} câu`;
            submitBtn.style.background = '#e2e8f0';
            submitBtn.style.color = '#64748b';
        }
    }
}

function submitQuiz() {
    console.log('Submitting quiz...');
    
    const answeredQuestions = document.querySelectorAll('input[type="radio"]:checked').length;
    if (answeredQuestions < totalQuestions) {
        alert('Vui lòng trả lời tất cả câu hỏi!');
        return;
    }
    
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Đang chấm bài...';
    
    // Get form data
    const form = document.getElementById('grammarQuizForm');
    const formData = new FormData(form);
    formData.append('ajax', '1');
    formData.append('action', 'submit_quiz');
    formData.append('topic_id', '<?= $topicId ?>');
    
    // Submit via fetch
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log('Response:', data);
        if (data.success) {
            // Redirect to results page
            const topicId = <?= $topicId ?>;
            window.location.href = `/webhocngoaingu/public/client/GrammarResult.php?score=${data.score}&correct=${data.correct_answers}&total=${data.total_questions}&topic=${topicId}`;
        } else {
            alert('Có lỗi xảy ra khi chấm bài!');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Nộp bài';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi chấm bài!');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Nộp bài';
    });
}

// Setup form submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('grammarQuizForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitQuiz();
        });
    }
    
    // Setup navigation click handlers
    document.querySelectorAll('.nav-question').forEach(function(navBtn) {
        navBtn.addEventListener('click', function() {
            const questionNum = this.getAttribute('data-question');
            const targetQuestion = document.querySelector(`.question-card[data-question="${questionNum}"]`);
            
            if (targetQuestion) {
                // Smooth scroll to question
                targetQuestion.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                
                // Update current indicator
                document.querySelectorAll('.nav-question').forEach(btn => btn.classList.remove('current'));
                this.classList.add('current');
                
                console.log('Navigated to question', questionNum);
            }
        });
    });
    
    // Set first question as current initially
    const firstNavBtn = document.querySelector('.nav-question[data-question="1"]');
    if (firstNavBtn) {
        firstNavBtn.classList.add('current');
    }
});

console.log('Script loaded! Functions ready.');
</script>

<?php require_once(__DIR__ . "/../../public/client/footer.php"); ?>
