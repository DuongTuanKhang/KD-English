<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");

$title = 'AI Pronunciation Practice | ' . $Database->site("TenWeb");

if (!isset($_SESSION["account"])) {
    redirect(BASE_URL("Auth/DangNhap"));
}

$user = $Database->get_row("SELECT * FROM nguoidung WHERE taikhoan = '" . $_SESSION["account"] . "'");
if (!$user) {
    redirect(BASE_URL("Auth/DangNhap"));
}

$topicId = isset($_GET['topic']) ? (int)$_GET['topic'] : null;
if (!$topicId) {
    redirect(BASE_URL("Speaking"));
}

$topic = $Database->get_row("SELECT * FROM speaking_topics WHERE MaChuDe = '$topicId' AND TrangThai = 1");
if (!$topic) {
    redirect(BASE_URL("Speaking"));
}

$lessons = $Database->get_list("
    SELECT sl.*,
           (SELECT COUNT(*) FROM speaking_results sr WHERE sr.MaBaiSpeaking = sl.MaBaiSpeaking AND sr.TaiKhoan = '" . $user['TaiKhoan'] . "') as DaLam,
           (SELECT MAX(TongDiem) FROM speaking_results sr WHERE sr.MaBaiSpeaking = sl.MaBaiSpeaking AND sr.TaiKhoan = '" . $user['TaiKhoan'] . "') as DiemCaoNhat,
           (SELECT AVG(TongDiem) FROM speaking_results sr WHERE sr.MaBaiSpeaking = sl.MaBaiSpeaking AND sr.TaiKhoan = '" . $user['TaiKhoan'] . "') as DiemTrungBinh
    FROM speaking_lessons sl 
    WHERE sl.MaChuDe = '$topicId' AND sl.TrangThai = 1 
    ORDER BY sl.ThuTu ASC, sl.MaBaiSpeaking ASC
");

require_once(__DIR__ . "/header.php");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <style>
        :root {
            --primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            --danger: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--primary);
            min-height: 100vh;
            color: #1a202c;
            line-height: 1.6;
            font-size: 18px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            text-align: center;
            background: white;
            border-radius: 20px;
            padding: 3rem;
            margin-bottom: 2rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .page-title {
            font-size: 3.5rem;
            font-weight: 800;
            background: var(--primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
        }

        .subtitle {
            color: #4a5568;
            font-size: 1.4rem;
        }

        .lessons-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
        }

        .lesson-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
        }

        .lesson-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .lesson-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 30px 60px rgba(0,0,0,0.15);
        }

        .lesson-card:hover::before {
            transform: scaleX(1);
        }

        .lesson-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 1rem;
        }

        .lesson-text {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            padding: 1.5rem;
            margin: 1.5rem 0;
            font-size: 1.3rem;
            color: #4a5568;
            font-style: italic;
            line-height: 1.6;
        }

        .lesson-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .stat-item {
            text-align: center;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            background: var(--primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #718096;
            text-transform: uppercase;
            margin-top: 0.25rem;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 15px;
            font-size: 1rem;
            font-weight: 600;
            color: white;
        }

        .status-completed { background: var(--success); }
        .status-new { background: var(--warning); }

        .action-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.8);
            backdrop-filter: blur(8px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .modal-content {
            background: white;
            border-radius: 25px;
            width: 100%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 30px 60px rgba(0,0,0,0.3);
            animation: modalSlide 0.4s ease;
        }

        @keyframes modalSlide {
            from { opacity: 0; transform: translateY(100px) scale(0.9); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .modal-header {
            padding: 2rem 2rem 1rem;
            border-bottom: 1px solid #e2e8f0;
            position: relative;
        }

        .modal-close {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            width: 2.5rem;
            height: 2.5rem;
            border: none;
            background: #f8fafc;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #718096;
        }

        .modal-close:hover {
            background: var(--danger);
            color: white;
            transform: rotate(90deg);
        }

        .modal-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 0.5rem;
        }

        .modal-body {
            padding: 2rem;
        }

        .target-text-container {
            background: var(--primary);
            color: white;
            padding: 2rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            position: relative;
            box-shadow: 0 15px 30px rgba(102, 126, 234, 0.3);
        }

        .target-text {
            font-size: 1.375rem;
            font-weight: 500;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .text-controls {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .control-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            backdrop-filter: blur(10px);
        }

        .control-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }

        .recording-area {
            text-align: center;
            padding: 3rem 2rem;
            background: #f8fafc;
            border-radius: 20px;
            border: 2px dashed #e2e8f0;
            margin: 2rem 0;
            transition: all 0.3s ease;
        }

        .recording-area.active {
            border-color: #ef4444;
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
        }

        .mic-button {
            width: 120px;
            height: 120px;
            border: none;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            font-size: 2.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 15px 30px rgba(102, 126, 234, 0.4);
            margin-bottom: 1.5rem;
        }

        .mic-button:hover {
            transform: scale(1.05);
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.5);
        }

        .mic-button.recording {
            background: var(--danger);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .recording-status {
            font-size: 1.125rem;
            color: #4a5568;
            font-weight: 500;
        }

        .recording-status.active {
            color: #ef4444;
            font-weight: 600;
        }

        .results-section {
            display: none;
            margin-top: 2rem;
            padding: 2rem;
            background: #f8fafc;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
        }

        .score-display {
            text-align: center;
            margin-bottom: 2rem;
        }

        .score-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
            font-size: 2rem;
            font-weight: 800;
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }

        .score-excellent { background: var(--success); }
        .score-good { background: var(--primary); }
        .score-average { background: var(--warning); }
        .score-poor { background: var(--danger); }

        .score-feedback {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 1rem;
        }

        .comparison-grid {
            display: grid;
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .comparison-item {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            border: 1px solid #e2e8f0;
        }

        .comparison-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #718096;
            text-transform: uppercase;
            margin-bottom: 0.75rem;
        }

        .comparison-text {
            font-size: 1.125rem;
            color: #1a202c;
            line-height: 1.6;
            font-style: italic;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 1.75rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-secondary {
            background: white;
            color: #1a202c;
            border: 2px solid #e2e8f0;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .lessons-grid { grid-template-columns: 1fr; }
            .modal-content { margin: 1rem; }
            .mic-button { width: 100px; height: 100px; font-size: 2rem; }
            .page-title { font-size: 2rem; }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1 class="page-title"><?= htmlspecialchars($topic['TenChuDe']) ?></h1>
            <p class="subtitle">Luyện phát âm tiếng Anh với AI - Cải thiện kỹ năng nói hiệu quả</p>
        </div>

        <div class="lessons-grid">
            <?php foreach ($lessons as $index => $lesson): ?>
                <div class="lesson-card" onclick="window.location.href='<?= BASE_URL('SpeakingLesson?id=' . $lesson['MaBaiSpeaking']) ?>'">>
                    <h3 class="lesson-title"><?= htmlspecialchars($lesson['TieuDe']) ?></h3>
                    
                    <div class="lesson-text">
                        <?= htmlspecialchars($lesson['TextMau']) ?>
                    </div>

                    <div class="lesson-stats">
                        <div class="stat-item">
                            <div class="stat-value"><?= $lesson['DaLam'] ?></div>
                            <div class="stat-label">Lần thực hành</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?= $lesson['DiemCaoNhat'] ?: '---' ?></div>
                            <div class="stat-label">Điểm cao nhất</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?= $lesson['DiemTrungBinh'] ? round($lesson['DiemTrungBinh']) : '---' ?></div>
                            <div class="stat-label">Điểm TB</div>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem;">
                        <?php if ($lesson['DaLam'] > 0): ?>
                            <span class="status-badge status-completed">
                                <i class="fas fa-check-circle"></i> Đã hoàn thành
                            </span>
                        <?php else: ?>
                            <span class="status-badge status-new">
                                <i class="fas fa-play-circle"></i> Bài mới
                            </span>
                        <?php endif; ?>
                        
                        <button class="action-button" onclick="event.stopPropagation(); window.location.href='<?= BASE_URL('SpeakingLesson?id=' . $lesson['MaBaiSpeaking']) ?>'">>
                            <i class="fas fa-microphone"></i> Bắt đầu luyện
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
                </div>
    </div>

    <script>
        console.log('Speaking Practice Page Loaded');
        
        // Add hover effects
        $(document).ready(function() {
            $('.lesson-card').hover(
                function() {
                    $(this).addClass('hovered');
                },
                function() {
                    $(this).removeClass('hovered');
                }
            );
        });
    </script>

    <?php require_once(__DIR__ . "/footer.php"); ?>
</body>
</html>

    <script>
        let currentLessonId = null;
        let targetTextContent = '';
        let spokenResult = '';
        let recognition = null;
        let isRecording = false;
        let speechSpeed = 1.0;
        const speechSpeeds = [0.5, 0.8, 1.0, 1.2, 1.5];
        let currentSpeedIndex = 2;

        function initSpeechRecognition() {
            if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
                alert('Trình duyệt của bạn không hỗ trợ nhận diện giọng nói. Vui lòng sử dụng Chrome hoặc Edge.');
                return false;
            }

            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            recognition = new SpeechRecognition();
            
            recognition.continuous = false;
            recognition.interimResults = false;
            recognition.lang = 'en-US';
            recognition.maxAlternatives = 1;

            recognition.onstart = function() {
                updateRecordingStatus('Đang ghi âm... Hãy nói!', true);
                $('#recordingArea').addClass('active');
            };

            recognition.onresult = function(event) {
                if (event.results.length > 0) {
                    spokenResult = event.results[0][0].transcript;
                    processResult();
                }
            };

            recognition.onerror = function(event) {
                alert('Lỗi nhận diện giọng nói: ' + event.error);
                stopRecording();
            };

            recognition.onend = function() {
                stopRecording();
            };

            return true;
        }

        function openModal(lessonId, title, text) {
            currentLessonId = lessonId;
            targetTextContent = text;
            
            $('#modalTitle').text(title);
            $('#targetText').text(text);
            $('#practiceModal').css('display', 'flex');
            $('#resultsSection').hide();
            
            updateRecordingStatus('Nhấn vào micro để bắt đầu ghi âm', false);
            
            if (!recognition) {
                initSpeechRecognition();
            }
        }

        function closeModal() {
            $('#practiceModal').hide();
            if (isRecording && recognition) {
                recognition.stop();
            }
            resetModal();
        }

        function resetModal() {
            currentLessonId = null;
            targetTextContent = '';
            spokenResult = '';
            isRecording = false;
            $('#resultsSection').hide();
            $('#recordingArea').removeClass('active');
            updateRecordingStatus('Nhấn vào micro để bắt đầu ghi âm', false);
        }

        function toggleRecording() {
            if (!currentLessonId) {
                alert('Vui lòng chọn một bài học trước khi ghi âm.');
                return;
            }

            if (isRecording) {
                stopRecording();
            } else {
                startRecording();
            }
        }

        function startRecording() {
            if (!recognition) {
                alert('Chưa khởi tạo nhận diện giọng nói!');
                return;
            }

            isRecording = true;
            spokenResult = '';
            
            $('#micButton').addClass('recording');
            $('#micIcon').removeClass('fa-microphone').addClass('fa-stop');
            $('#resultsSection').hide();
            
            try {
                recognition.start();
            } catch (error) {
                alert('Lỗi khi bắt đầu ghi âm: ' + error.message);
                stopRecording();
            }
        }

        function stopRecording() {
            isRecording = false;
            
            if (recognition) {
                recognition.stop();
            }
            
            $('#micButton').removeClass('recording');
            $('#micIcon').removeClass('fa-stop').addClass('fa-microphone');
            $('#recordingArea').removeClass('active');
            
            updateRecordingStatus('Nhấn vào micro để bắt đầu ghi âm', false);
        }

        function retryRecording() {
            $('#resultsSection').hide();
            startRecording();
        }

        function updateRecordingStatus(message, active) {
            $('#recordingStatus').text(message);
            if (active) {
                $('#recordingStatus').addClass('active');
            } else {
                $('#recordingStatus').removeClass('active');
            }
        }

        function playAudio() {
            if (!targetTextContent) {
                alert('Không có văn bản để đọc.');
                return;
            }

            $('#playBtn').html('<i class="fas fa-spinner fa-spin"></i> Đang phát...');

            if ('speechSynthesis' in window) {
                speechSynthesis.cancel();
                const utterance = new SpeechSynthesisUtterance(targetTextContent);
                utterance.lang = 'en-US';
                utterance.rate = speechSpeed;
                utterance.pitch = 1;

                utterance.onend = function() {
                    $('#playBtn').html('<i class="fas fa-play"></i> Nghe mẫu');
                };

                speechSynthesis.speak(utterance);
            } else {
                $('#playBtn').html('<i class="fas fa-play"></i> Nghe mẫu');
                alert('Trình duyệt không hỗ trợ text-to-speech');
            }
        }

        function adjustSpeed() {
            currentSpeedIndex = (currentSpeedIndex + 1) % speechSpeeds.length;
            speechSpeed = speechSpeeds[currentSpeedIndex];
            
            const speedLabels = ['Rất chậm', 'Chậm', 'Bình thường', 'Nhanh', 'Rất nhanh'];
            $('#speedBtn').html(`<i class="fas fa-tachometer-alt"></i> Tốc độ: ${speedLabels[currentSpeedIndex]}`);
        }

        function processResult() {
            if (!spokenResult || !targetTextContent) {
                alert('Lỗi: Không có dữ liệu để xử lý');
                return;
            }

            updateRecordingStatus('Đang phân tích...', true);
            
            setTimeout(() => {
                const score = calculateScore(targetTextContent, spokenResult);
                const feedback = generateFeedback(score);
                
                displayResult(score, feedback);
                saveResult(score, spokenResult);
                updateRecordingStatus('Hoàn thành', false);
            }, 1500);
        }

        function calculateScore(target, spoken) {
            const normalize = (text) => {
                return text.toLowerCase()
                          .replace(/[^\w\s]/gi, '')
                          .replace(/\s+/g, ' ')
                          .trim();
            };
            
            const targetWords = normalize(target).split(' ');
            const spokenWords = normalize(spoken).split(' ');
            
            let matches = 0;
            for (let i = 0; i < Math.max(targetWords.length, spokenWords.length); i++) {
                if (targetWords[i] && spokenWords[i]) {
                    const similarity = getWordSimilarity(targetWords[i], spokenWords[i]);
                    matches += similarity;
                }
            }
            
            const score = Math.round((matches / targetWords.length) * 100);
            return Math.max(0, Math.min(100, score));
        }

        function getWordSimilarity(word1, word2) {
            if (word1 === word2) return 1;
            
            const maxLength = Math.max(word1.length, word2.length);
            const distance = getEditDistance(word1, word2);
            return Math.max(0, (maxLength - distance) / maxLength);
        }

        function getEditDistance(a, b) {
            const matrix = [];
            
            for (let i = 0; i <= b.length; i++) {
                matrix[i] = [i];
            }
            
            for (let j = 0; j <= a.length; j++) {
                matrix[0][j] = j;
            }
            
            for (let i = 1; i <= b.length; i++) {
                for (let j = 1; j <= a.length; j++) {
                    if (b.charAt(i - 1) === a.charAt(j - 1)) {
                        matrix[i][j] = matrix[i - 1][j - 1];
                    } else {
                        matrix[i][j] = Math.min(
                            matrix[i - 1][j - 1] + 1,
                            matrix[i][j - 1] + 1,
                            matrix[i - 1][j] + 1
                        );
                    }
                }
            }
            
            return matrix[b.length][a.length];
        }

        function generateFeedback(score) {
            if (score >= 95) {
                return '��� Hoàn hảo! Phát âm của bạn rất xuất sắc!';
            } else if (score >= 85) {
                return '⭐ Tuyệt vời! Phát âm rất tốt!';
            } else if (score >= 75) {
                return '��� Tốt! Phát âm khá chính xác.';
            } else if (score >= 65) {
                return '��� Khá tốt! Hãy chú ý phát âm một số từ.';
            } else if (score >= 50) {
                return '⚠️ Cần luyện tập thêm. Hãy nghe và lặp lại nhiều lần.';
            } else {
                return '�� Hãy thử lại. Nói chậm và rõ ràng hơn.';
            }
        }

        function getScoreLevel(score) {
            if (score >= 85) return 'score-excellent';
            if (score >= 70) return 'score-good';
            if (score >= 50) return 'score-average';
            return 'score-poor';
        }

        function displayResult(score, feedback) {
            $('#scoreValue').text(score);
            $('#scoreCircle').removeClass().addClass('score-circle ' + getScoreLevel(score));
            $('#scoreFeedback').text(feedback);
            
            $('#originalText').text(targetTextContent);
            $('#spokenText').text(spokenResult);
            
            $('#resultsSection').fadeIn(500);
        }

        function saveResult(score, spokenText) {
            if (!currentLessonId) {
                console.error('No lesson ID');
                return;
            }
            
            $.ajax({
                url: '<?= BASE_URL("api/speaking-api.php") ?>',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'save_result',
                    lesson_id: currentLessonId,
                    score: score,
                    spoken_text: spokenText
                },
                success: function(response) {
                    if (response.success) {
                        console.log('Result saved successfully');
                    } else {
                        console.error('Failed to save result:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                }
            });
        }

        $(document).ready(function() {
            console.log('AI Pronunciation Practice loaded');
            
            $('#practiceModal').on('click', function(e) {
                if (e.target === this) {
                    closeModal();
                }
            });
            
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $('#practiceModal').is(':visible')) {
                    closeModal();
                } else if (e.key === ' ' && $('#practiceModal').is(':visible')) {
                    e.preventDefault();
                    toggleRecording();
                }
            });
        });
    </script>

    <?php require_once(__DIR__ . "/footer.php"); ?>
</body>
</html>
