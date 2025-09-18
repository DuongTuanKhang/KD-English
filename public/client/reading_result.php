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

// Create reverse mapping from topic to lesson ID - MUST match reading.php
$topicToLessonMapping = [
    'Traffic' => 1,
    'Food' => 2,
    'Education' => 3,
    'Family' => 4,
    'Work' => 5,
    'Hobbie' => 6,
    'Technology' => 7,
    'Activities' => 8
];

// Get lesson ID from topic for proper redirect
$maBaiHoc = isset($topicToLessonMapping[$topic]) ? $topicToLessonMapping[$topic] : 1;

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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .result-container {
            max-width: 1000px;
            margin: 0 auto;
            padding-bottom: 100px;
        }

        .result-header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .result-header h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 2.5em;
        }

        .score-display {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 20px;
            margin: 20px 0;
            text-align: center;
        }

        .score-display h2 {
            font-size: 4em;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .result-content {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .question-detail {
            border-left: 5px solid #007bff;
            padding: 20px;
            margin-bottom: 20px;
            background: #f8f9fa;
            border-radius: 0 10px 10px 0;
        }

        .question-detail.correct {
            border-left-color: #28a745;
            background: #d4edda;
        }

        .question-detail.incorrect {
            border-left-color: #dc3545;
            background: #f8d7da;
        }

        .question-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .status-icon {
            font-size: 1.5em;
            margin-right: 10px;
        }

        .answer-row {
            margin: 10px 0;
            padding: 10px;
            background: white;
            border-radius: 5px;
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

        .action-buttons {
            text-align: center;
            margin-top: 30px;
        }

        .btn-custom {
            display: inline-block;
            padding: 12px 24px;
            margin: 10px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
        }

        .btn-secondary-custom {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
        }

        .btn-success-custom {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
            color: white;
        }
    </style>
</head>

<body>
    <div class="result-container">
        <!-- Header -->
        <div class="result-header">
            <h1>🏆 Kết quả bài đọc</h1>
            <p style="color: #666;">Chủ đề: <strong><?= htmlspecialchars($topic) ?></strong></p>

            <div class="score-display">
                <h2><?= $score ?>%</h2>
                <p>Đúng <?= $correct ?>/<?= $total ?> câu</p>
            </div>
        </div>

        <!-- Detailed Results -->
        <div class="result-content">
            <h3 style="margin-bottom: 20px;">📋 Chi tiết từng câu hỏi</h3>

            <div id="questions-details-container">
                <div style="text-align: center; padding: 20px; color: #666;">
                    ⏳ Đang tải chi tiết kết quả...
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="<?= BASE_URL("public/client/reading.php?maKhoaHoc=$maKhoaHoc&maBaiHoc=$maBaiHoc&autostart=1") ?>"
                    class="btn-custom btn-primary-custom">
                    🔄 Làm lại bài này
                </a>

                <a href="<?= BASE_URL("public/client/reading.php?maKhoaHoc=$maKhoaHoc&maBaiHoc=$maBaiHoc") ?>"
                    class="btn-custom btn-secondary-custom">
                    📝 Danh sách bài đọc
                </a>

                <a href="<?= BASE_URL("Page/KhoaHoc/$maKhoaHoc") ?>" class="btn-custom btn-success-custom">
                    🏠 Về trang khóa học
                </a>
            </div>
        </div>

        <script>
            function loadResultDetails() {
                try {
                    const resultData = localStorage.getItem('readingResult');
                    if (!resultData) {
                        document.getElementById('questions-details-container').innerHTML =
                            '<div style="text-align: center; padding: 20px; color: #999;">Không có dữ liệu chi tiết.</div>';
                        return;
                    }

                    const details = JSON.parse(resultData);

                    if (!details.questionDetails || !Array.isArray(details.questionDetails)) {
                        document.getElementById('questions-details-container').innerHTML =
                            '<div style="text-align: center; padding: 20px; color: #999;">Không có chi tiết câu hỏi.</div>';
                        return;
                    }

                    displayQuestionDetails(details.questionDetails);
                } catch (error) {
                    console.error('Error loading result details:', error);
                    document.getElementById('questions-details-container').innerHTML =
                        '<div style="text-align: center; padding: 20px; color: #dc3545;">Lỗi tải dữ liệu.</div>';
                }
            }

            function displayQuestionDetails(questionDetails) {
                let html = '';

                questionDetails.forEach((detail, index) => {
                    const statusClass = detail.isCorrect ? 'correct' : 'incorrect';
                    const statusIcon = detail.isCorrect ? '✅' : '❌';

                    html += `
                    <div class="question-detail ${statusClass}">
                        <div class="question-header">
                            <span class="status-icon">${statusIcon}</span>
                            <h5 style="margin: 0;">Câu ${index + 1}</h5>
                        </div>
                        
                        <div class="question-content">
                            <h5 style="color: #333; margin-bottom: 15px;">${escapeHtml(detail.question)}</h5>
                            
                            <div class="answer-row">
                                <span style="font-weight: bold; color: #666;">Câu trả lời của bạn:</span>
                                <span class="answer-badge ${detail.isCorrect ? 'correct' : 'incorrect'}">
                                    ${escapeHtml(detail.userAnswer || 'Không trả lời')}
                                </span>
                            </div>
                            
                            <div class="answer-row">
                                <span style="font-weight: bold; color: #666;">Đáp án đúng:</span>
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
            document.addEventListener('DOMContentLoaded', function () {
                loadResultDetails();
            });
        </script>
</body>

</html>