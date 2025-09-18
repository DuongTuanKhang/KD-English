<?php
require_once(__DIR__ . "/../class/Database.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

try {
    // Insert reading lesson
    $insertData = [
        'MaBaiHoc' => 1, // Default value since we're using topic-based approach
        'MaKhoaHoc' => (int)$input['course_id'],
        'TieuDe' => $input['title'],
        'NoiDungBaiDoc' => $input['content'],
        'MucDo' => $input['topic'],
        'ThoiGianLam' => (int)$input['time_limit'],
        'ThuTu' => 1,
        'TrangThai' => 1,
        'ThoiGianTao' => date('Y-m-d H:i:s')
    ];
    
    if ($Database->insert('reading_lessons', $insertData)) {
        $readingId = $Database->insert_id();
        
        // Insert questions
        foreach ($input['questions'] as $index => $question) {
            $questionData = [
                'MaBaiDoc' => $readingId,
                'CauHoi' => $question['question_text'],
                'DapAnA' => $question['answer_a'],
                'DapAnB' => $question['answer_b'],
                'DapAnC' => $question['answer_c'],
                'DapAnD' => $question['answer_d'],
                'DapAnDung' => $question['correct_answer'],
                'GiaiThich' => $question['explanation'] ?: null,
                'ThuTu' => $index + 1,
                'Diem' => (int)$question['score']
            ];
            
            $Database->insert('reading_questions', $questionData);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Thêm bài Reading thành công!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Có lỗi xảy ra khi thêm bài Reading'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
