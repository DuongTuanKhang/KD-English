<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");
checkLogin();

// Get parameters from URL
$maKhoaHoc = $_GET['course'] ?? 1;
$topic = $_GET['topic'] ?? 'Traffic';
$score = $_GET['score'] ?? 0;
$correct = $_GET['correct'] ?? 0;
$total = $_GET['total'] ?? 0;

// Get course info
$courseInfo = $Database->get_row("SELECT * FROM khoahoc WHERE MaKhoaHoc = '$maKhoaHoc'");
if (!$courseInfo) {
    header("Location: " . BASE_URL('Page'));
    exit;
}

$title = "Kết quả Reading - " . $topic;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - WebNgoaiNgu</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            background: #f0f0f0;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
        }
        
        .score-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .question-detail {
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            background: #f9f9f9;
        }
        
        .question-detail.correct {
            border-color: #28a745;
            background: #d4edda;
        }
        
        .question-detail.incorrect {
            border-color: #dc3545;
            background: #f8d7da;
        }
        
        .back-btn {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        
        /* Thêm content dài để test scroll */
        .test-area {
            height: 1500px;
            background: linear-gradient(to bottom, #e9ecef, #28a745, #007bff);
            margin: 30px 0;
            padding: 20px;
            text-align: center;
            border-radius: 10px;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="score-box">
            <h1>Điểm số: <?= $score ?>%</h1>
            <p>Bạn đã trả lời đúng <?= $correct ?>/<?= $total ?> câu hỏi</p>
            <p>Chủ đề: <?= htmlspecialchars($topic) ?></p>
        </div>
        
        <h2>Chi tiết kết quả:</h2>
        <div id="questions-details-container">
            <!-- Details will be loaded here -->
        </div>
        
        <div class="test-area">
            <h3>Khu vực test scroll</h3>
            <p>Nếu bạn thấy được phần này nghĩa là scroll đã hoạt động!</p>
            <p>Đây là nội dung dài để kiểm tra khả năng cuộn trang.</p>
            <p>DEBUG: Course = <?= $maKhoaHoc ?>, Topic = <?= $topic ?></p>
            <br><br><br><br><br><br><br><br><br><br>
            <p style="font-size: 24px; color: red;">Nội dung ở giữa...</p>
            <br><br><br><br><br><br><br><br><br><br>
            <p style="font-size: 24px; color: blue;">Cuối trang test!</p>
            <br><br><br><br><br><br><br><br><br><br>
        </div>
        
        <a href="<?= BASE_URL('public/client/reading.php?course=' . $maKhoaHoc) ?>" class="back-btn">
            ← Quay lại danh sách bài đọc
        </a>
    </div>

    <script>
        function loadResultDetails() {
            try {
                const resultData = localStorage.getItem('readingResult');
                if (!resultData) {
                    console.log('No result data found');
                    return;
                }

                const details = JSON.parse(resultData);
                console.log('Loading result details:', details);
                
                if (!details.questionDetails || !Array.isArray(details.questionDetails)) {
                    console.log('No question details found');
                    return;
                }

                displayQuestionDetails(details.questionDetails);
            } catch (error) {
                console.error('Error loading result details:', error);
            }
        }

        function displayQuestionDetails(questionDetails) {
            let html = '';
            
            questionDetails.forEach((detail, index) => {
                const statusClass = detail.isCorrect ? 'correct' : 'incorrect';
                const statusIcon = detail.isCorrect ? '✓' : '✗';
                
                html += `
                    <div class="question-detail ${statusClass}">
                        <h4>${statusIcon} Câu ${index + 1}</h4>
                        <p><strong>Câu hỏi:</strong> ${escapeHtml(detail.question)}</p>
                        <p><strong>Trả lời của bạn:</strong> ${escapeHtml(detail.userAnswer || 'Không trả lời')}</p>
                        <p><strong>Đáp án đúng:</strong> ${escapeHtml(detail.correctAnswer)}</p>
                        ${detail.explanation ? `<p><strong>Giải thích:</strong> ${escapeHtml(detail.explanation)}</p>` : ''}
                    </div>
                `;
            });
            
            document.getElementById('questions-details-container').innerHTML = html;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Load details when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadResultDetails();
            console.log('Page loaded, scroll should work now');
        });
    </script>
</body>
</html>
