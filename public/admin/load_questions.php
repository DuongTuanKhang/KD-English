<?php
require_once(__DIR__ . "/../../configs/config.php");

// Check admin permission
if (!isset($_SESSION["account"]) || $_SESSION["account"] != "admin") {
    die('Unauthorized');
}
?>

<style>
/* CSS Fix for VS Code warnings - Font Awesome and Bootstrap classes */
.fa-info-circle, .fa-book, .fa-check, .fa-lightbulb, .fa-edit, .fa-trash, 
.border-left-primary {
    /* These classes are defined by Font Awesome and Bootstrap libraries */
}
</style>

<?php

$lesson_id = isset($_GET['lesson_id']) ? (int)$_GET['lesson_id'] : 0;

if ($lesson_id <= 0) {
    echo '<p class="text-danger">ID bài học không hợp lệ</p>';
    exit;
}

try {
    // Get lesson info
    $lesson = $Database->get_list("SELECT TieuDe FROM reading_lessons WHERE MaBaiDoc = " . $lesson_id);
    if (empty($lesson)) {
        echo '<p class="text-danger">Không tìm thấy bài học</p>';
        exit;
    }

    // Get questions
    $questions = $Database->get_list("
        SELECT * FROM reading_questions 
        WHERE MaBaiDoc = " . $lesson_id . " 
        ORDER BY ThuTu ASC, MaCauHoi ASC
    ");

    if (empty($questions)) {
        echo '<div class="alert alert-info">
            <i class="fas fa-info-circle"></i> 
            Chưa có câu hỏi nào cho bài: <strong>' . htmlspecialchars($lesson[0]['TieuDe']) . '</strong>
            <br>Hãy thêm câu hỏi bằng form phía trên.
        </div>';
    } else {
        echo '<div class="alert alert-success">
            <i class="fas fa-book"></i> 
            Bài: <strong>' . htmlspecialchars($lesson[0]['TieuDe']) . '</strong> 
            (' . count($questions) . ' câu hỏi)
        </div>';

        foreach ($questions as $index => $q) {
            $correctAnswer = '';
            switch ($q['DapAnDung']) {
                case 'A': $correctAnswer = $q['DapAnA']; break;
                case 'B': $correctAnswer = $q['DapAnB']; break;
                case 'C': $correctAnswer = $q['DapAnC']; break;
                case 'D': $correctAnswer = $q['DapAnD']; break;
            }

            echo '<div class="card mb-2 border-left-primary">';
            echo '<div class="card-body p-3">';
            echo '<div class="d-flex justify-content-between align-items-start">';
            echo '<div class="flex-grow-1">';
            echo '<h6 class="mb-2">Câu ' . ($index + 1) . ':</h6>';
            echo '<p class="mb-2"><strong>' . htmlspecialchars($q['CauHoi']) . '</strong></p>';
            
            echo '<div class="row mb-2">';
            if (!empty($q['DapAnA'])) {
                $class = ($q['DapAnDung'] == 'A') ? 'text-success font-weight-bold' : '';
                echo '<div class="col-md-6"><small class="' . $class . '">A. ' . htmlspecialchars($q['DapAnA']) . '</small></div>';
            }
            if (!empty($q['DapAnB'])) {
                $class = ($q['DapAnDung'] == 'B') ? 'text-success font-weight-bold' : '';
                echo '<div class="col-md-6"><small class="' . $class . '">B. ' . htmlspecialchars($q['DapAnB']) . '</small></div>';
            }
            if (!empty($q['DapAnC'])) {
                $class = ($q['DapAnDung'] == 'C') ? 'text-success font-weight-bold' : '';
                echo '<div class="col-md-6"><small class="' . $class . '">C. ' . htmlspecialchars($q['DapAnC']) . '</small></div>';
            }
            if (!empty($q['DapAnD'])) {
                $class = ($q['DapAnDung'] == 'D') ? 'text-success font-weight-bold' : '';
                echo '<div class="col-md-6"><small class="' . $class . '">D. ' . htmlspecialchars($q['DapAnD']) . '</small></div>';
            }
            echo '</div>';

            echo '<p class="mb-2"><small><i class="fas fa-check text-success"></i> <strong>Đáp án đúng:</strong> ' . $q['DapAnDung'] . ' - ' . htmlspecialchars($correctAnswer) . '</small></p>';
            
            if (!empty($q['GiaiThich'])) {
                echo '<p class="mb-2"><small><i class="fas fa-lightbulb text-warning"></i> <strong>Giải thích:</strong> ' . htmlspecialchars($q['GiaiThich']) . '</small></p>';
            }

            echo '</div>';
            echo '<div class="flex-shrink-0">';
            echo '<button class="btn btn-sm btn-outline-primary mr-1" onclick="editQuestion(' . $q['MaCauHoi'] . ')" title="Sửa">';
            echo '<i class="fas fa-edit"></i>';
            echo '</button>';
            echo '<button class="btn btn-sm btn-outline-danger" onclick="deleteQuestion(' . $q['MaCauHoi'] . ')" title="Xóa">';
            echo '<i class="fas fa-trash"></i>';
            echo '</button>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    }

} catch (Exception $e) {
    echo '<p class="text-danger">Lỗi: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>
