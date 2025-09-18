<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");

// Check admin permission
if (!isset($_SESSION["account"]) || $_SESSION["account"] != "admin") {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

header('Content-Type: application/json');

if (!isset($_GET['reading_id'])) {
    die(json_encode(['success' => false, 'message' => 'Missing reading ID']));
}

$readingId = (int)$_GET['reading_id'];

try {
    // Get reading info
    $reading = $Database->get_row("SELECT * FROM reading_lessons WHERE MaBaiDoc = $readingId");
    if (!$reading) {
        die(json_encode(['success' => false, 'message' => 'Reading not found']));
    }
    
    // Get questions
    $questions = $Database->get_list("SELECT * FROM reading_questions WHERE MaBaiDoc = $readingId ORDER BY ThuTu");
    
    // Generate HTML for questions management
    ob_start();
    ?>
    
    <div class="reading-info mb-4">
        <h5><?= htmlspecialchars($reading['TieuDe']) ?></h5>
        <p class="text-muted">Khóa học: <?= $reading['MaKhoaHoc'] ?> | Chủ đề: <?= $reading['MucDo'] ?></p>
    </div>
    
    <!-- Add New Question Form -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h6 class="mb-0"><i class="fas fa-plus"></i> Thêm câu hỏi mới</h6>
        </div>
        <div class="card-body">
            <form id="addQuestionForm" data-reading-id="<?= $readingId ?>">
                <div class="form-group">
                    <label>Nội dung câu hỏi <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="cau_hoi" rows="3" required placeholder="Nhập nội dung câu hỏi..."></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Đáp án A <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="dap_an_a" required placeholder="Nhập đáp án A...">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Đáp án B <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="dap_an_b" required placeholder="Nhập đáp án B...">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Đáp án C <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="dap_an_c" required placeholder="Nhập đáp án C...">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Đáp án D <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="dap_an_d" required placeholder="Nhập đáp án D...">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Đáp án đúng <span class="text-danger">*</span></label>
                            <select class="form-control" name="dap_an_dung" required>
                                <option value="">Chọn đáp án đúng</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Thứ tự câu hỏi</label>
                            <input type="number" class="form-control" name="thu_tu" min="1" value="<?= count($questions) + 1 ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Giải thích đáp án đúng <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="giai_thich" rows="3" required placeholder="Nhập giải thích tại sao đáp án này đúng..."></textarea>
                    <small class="form-text text-muted">Giải thích này sẽ hiển thị cho học viên sau khi nộp bài</small>
                </div>
                
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-plus"></i> Thêm câu hỏi
                </button>
            </form>
        </div>
    </div>
    
    <!-- Existing Questions -->
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-list"></i> Danh sách câu hỏi (<?= count($questions) ?> câu)</h6>
        </div>
        <div class="card-body">
            <?php if (empty($questions)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Chưa có câu hỏi nào cho bài đọc này</p>
                </div>
            <?php else: ?>
                <?php foreach ($questions as $index => $question): ?>
                    <div class="question-item border rounded p-3 mb-3" data-question-id="<?= $question['MaCauHoi'] ?>">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="text-primary">Câu <?= $index + 1 ?>: (Thứ tự: <?= $question['ThuTu'] ?>)</h6>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-warning" onclick="editQuestion(<?= $question['MaCauHoi'] ?>)">
                                    <i class="fas fa-edit"></i> Sửa
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteQuestion(<?= $question['MaCauHoi'] ?>)">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </div>
                        </div>
                        
                        <div class="question-content">
                            <p><strong>Câu hỏi:</strong> <?= htmlspecialchars($question['CauHoi']) ?></p>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>A:</strong> <?= htmlspecialchars($question['DapAnA']) ?></p>
                                    <p><strong>B:</strong> <?= htmlspecialchars($question['DapAnB']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>C:</strong> <?= htmlspecialchars($question['DapAnC']) ?></p>
                                    <p><strong>D:</strong> <?= htmlspecialchars($question['DapAnD']) ?></p>
                                </div>
                            </div>
                            
                            <p><strong>Đáp án đúng:</strong> 
                                <span class="badge badge-success"><?= $question['DapAnDung'] ?></span>
                            </p>
                            
                            <p><strong>Giải thích:</strong> <?= htmlspecialchars($question['GiaiThich']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Handle add question form
        document.getElementById('addQuestionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'add_question');
            formData.append('ma_bai_doc', this.dataset.readingId);
            
            fetch('../admin/ReadingManagement.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(result => {
                // Reload questions list
                manageQuestions(this.dataset.readingId);
                toastr.success('Thêm câu hỏi thành công!');
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('Có lỗi xảy ra khi thêm câu hỏi');
            });
        });
        
        function editQuestion(questionId) {
            // Implementation for editing question
            console.log('Edit question:', questionId);
        }
        
        function deleteQuestion(questionId) {
            if (confirm('Bạn có chắc chắn muốn xóa câu hỏi này?')) {
                const formData = new FormData();
                formData.append('action', 'delete_question');
                formData.append('ma_cau_hoi', questionId);
                
                fetch('../admin/ReadingManagement.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(result => {
                    // Reload questions list
                    manageQuestions(<?= $readingId ?>);
                    toastr.success('Xóa câu hỏi thành công!');
                })
                .catch(error => {
                    console.error('Error:', error);
                    toastr.error('Có lỗi xảy ra khi xóa câu hỏi');
                });
            }
        }
    </script>
    
    <?php
    $html = ob_get_clean();
    
    echo json_encode([
        'success' => true,
        'html' => $html,
        'reading' => $reading,
        'questions' => $questions
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
    }

    // Get questions for this reading lesson
    $questions = $Database->get_list("
        SELECT 
            MaCauHoi,
            CauHoi,
            DapAnA,
            DapAnB,
            DapAnC,
            DapAnD,
            DapAnDung,
            GiaiThich,
            ThuTu
        FROM reading_questions 
        WHERE MaBaiDoc = $maBaiDoc
        ORDER BY ThuTu ASC, MaCauHoi ASC
    ");

    echo json_encode([
        'success' => true,
        'lesson' => $lesson,
        'questions' => $questions
    ]);

} catch (Exception $e) {
    error_log("Error in get_reading_questions.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi xảy ra khi tải danh sách câu hỏi'
    ]);
}
?>
