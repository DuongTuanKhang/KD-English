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
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả Reading - <?= $topic ?></title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            overflow-y: auto !important;
            height: auto !important;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .score-box {
            text-align: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .score-box h1 {
            font-size: 3em;
            margin: 0 0 10px 0;
        }
        
        .score-box p {
            font-size: 1.3em;
            margin: 5px 0;
        }
        
        .question-detail {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            background: #f8f9fa;
        }
        
        .question-detail.correct {
            border-color: #28a745;
            background: #d4edda;
        }
        
        .question-detail.incorrect {
            border-color: #dc3545;
            background: #f8d7da;
        }
        
        .question-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .question-header h3 {
            margin: 0 0 0 10px;
            color: #333;
        }
        
        .status-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .status-icon.correct {
            background: #28a745;
        }
        
        .status-icon.incorrect {
            background: #dc3545;
        }
        
        .question-content h4 {
            color: #333;
            margin-bottom: 15px;
        }
        
        .answer-row {
            margin: 10px 0;
            padding: 10px;
            background: white;
            border-radius: 5px;
        }
        
        .answer-label {
            font-weight: bold;
            color: #666;
        }
        
        .answer-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            margin-left: 10px;
            font-weight: bold;
        }
        
        .answer-badge.correct {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .answer-badge.incorrect {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .explanation {
            margin-top: 15px;
            padding: 15px;
            background: #e7f3ff;
            border-left: 4px solid #0066cc;
            border-radius: 5px;
        }
        
        .back-button {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 30px;
        }
        
        .back-button:hover {
            background: #0056b3;
            color: white;
            text-decoration: none;
        }
        
        /* Add extra content to test scroll */
        .test-content {
            height: 500px;
            background: linear-gradient(to bottom, #f8f9fa, #e9ecef);
            border-radius: 10px;
            padding: 20px;
            margin: 30px 0;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5em;
            color: #666;
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
        
        <div class="test-content">
            <p>Đây là nội dung test để kiểm tra scroll.<br>
            Nếu bạn thấy phần này thì scroll đã hoạt động!</p>
        </div>
        
        <a href="<?= BASE_URL('public/client/reading.php?course=' . $maKhoaHoc) ?>" class="back-button">
            ← Quay lại danh sách bài đọc
        </a>
    </div>

    <script>
        function loadResultDetails() {
            try {
                const resultData = localStorage.getItem('readingResult');
                if (!resultData) {
                    console.log('No result data found in localStorage');
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
                        <div class="question-header">
                            <span class="status-icon ${statusClass}">${statusIcon}</span>
                            <h3>Câu ${index + 1}</h3>
                        </div>
                        
                        <div class="question-content">
                            <h4>${escapeHtml(detail.question)}</h4>
                            
                            <div class="answer-row">
                                <span class="answer-label">Câu trả lời của bạn:</span>
                                <span class="answer-badge ${detail.isCorrect ? 'correct' : 'incorrect'}">
                                    ${escapeHtml(detail.userAnswer || 'Không trả lời')}
                                </span>
                            </div>
                            
                            <div class="answer-row">
                                <span class="answer-label">Đáp án đúng:</span>
                                <span class="answer-badge correct">
                                    ${escapeHtml(detail.correctAnswer)}
                                </span>
                            </div>
                            
                            ${detail.explanation ? `
                                <div class="explanation">
                                    <strong>💡 Giải thích:</strong>
                                    ${escapeHtml(detail.explanation)}
                                </div>
                            ` : ''}
                        </div>
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
            
            // Force scroll to be enabled
            document.body.style.overflow = 'auto';
            document.documentElement.style.overflow = 'auto';
            
            // Log page dimensions for debugging
            setTimeout(() => {
                console.log('Page dimensions:');
                console.log('Body scrollHeight:', document.body.scrollHeight);
                console.log('Window innerHeight:', window.innerHeight);
                console.log('Document height:', document.documentElement.scrollHeight);
                console.log('Can scroll:', document.body.scrollHeight > window.innerHeight);
            }, 500);
        });
    </script>
</body>
</html>
