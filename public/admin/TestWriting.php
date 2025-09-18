<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");
$title = 'Test Writing Management';

// Không kiểm tra quyền admin để test
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</head>
<body>

<div class="container-fluid mt-4">
    <h1>Test Writing Management</h1>
    
    <!-- Tab Navigation -->
    <ul class="nav nav-tabs" id="writingTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="topics-tab" data-toggle="tab" href="#topics" role="tab">
                <i class="fas fa-tags"></i> Chủ đề
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="prompts-tab" data-toggle="tab" href="#prompts" role="tab">
                <i class="fas fa-clipboard-list"></i> Đề bài
            </a>
        </li>
    </ul>
    
    <div class="tab-content mt-3" id="writingTabsContent">
        <!-- Topics Tab -->
        <div class="tab-pane fade show active" id="topics" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Quản lý chủ đề</h4>
                <button class="btn btn-primary" data-toggle="modal" data-target="#addTopicModal">
                    <i class="fas fa-plus"></i> Thêm chủ đề
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-striped" id="topicsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Khóa học</th>
                            <th>Tên chủ đề</th>
                            <th>Mô tả</th>
                            <th>Số đề bài</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <!-- Prompts Tab -->
        <div class="tab-pane fade" id="prompts" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Quản lý đề bài</h4>
            </div>
            <div class="table-responsive">
                <table class="table table-striped" id="promptsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Chủ đề</th>
                            <th>Tiêu đề</th>
                            <th>Giới hạn từ</th>
                            <th>Mức độ</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Topic Modal -->
<div class="modal fade" id="addTopicModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Thêm chủ đề mới</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addTopicForm">
                    <div class="form-group">
                        <label>Khóa học:</label>
                        <select class="form-control" name="maKhoaHoc" required>
                            <?php
                            $courses = $Database->get_list("SELECT * FROM khoahoc WHERE TrangThaiKhoaHoc = 1");
                            foreach ($courses as $course) {
                                echo "<option value='{$course['MaKhoaHoc']}'>{$course['TenKhoaHoc']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tên chủ đề:</label>
                        <input type="text" class="form-control" name="tenChuDe" required>
                    </div>
                    <div class="form-group">
                        <label>Mô tả:</label>
                        <textarea class="form-control" name="moTa" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="addTopic()">Thêm</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    loadTopics();
    loadPrompts();
});

function loadTopics() {
    $.get('<?= BASE_URL("api/writing-admin.php?action=topics") ?>', function(data) {
        try {
            const topics = JSON.parse(data);
            let html = '';
            topics.forEach(topic => {
                html += `
                    <tr>
                        <td>${topic.MaChuDe}</td>
                        <td>${topic.TenKhoaHoc || 'N/A'}</td>
                        <td>${topic.TenChuDe}</td>
                        <td>${topic.MoTa || ''}</td>
                        <td>${topic.SoDeBai}</td>
                        <td>
                            <span class="badge badge-${topic.TrangThai == 1 ? 'success' : 'danger'}">
                                ${topic.TrangThai == 1 ? 'Hoạt động' : 'Tắt'}
                            </span>
                        </td>
                    </tr>
                `;
            });
            $('#topicsTable tbody').html(html);
        } catch (e) {
            console.error('Error parsing topics:', e);
            console.log('Raw data:', data);
            $('#topicsTable tbody').html('<tr><td colspan="6">Error loading data</td></tr>');
        }
    }).fail(function(xhr, status, error) {
        console.error('AJAX Error:', error);
        $('#topicsTable tbody').html('<tr><td colspan="6">AJAX Error: ' + error + '</td></tr>');
    });
}

function loadPrompts() {
    $.get('<?= BASE_URL("api/writing-admin.php?action=prompts") ?>', function(data) {
        try {
            const prompts = JSON.parse(data);
            let html = '';
            prompts.forEach(prompt => {
                html += `
                    <tr>
                        <td>${prompt.MaDeBai}</td>
                        <td>${prompt.TenChuDe || 'N/A'}</td>
                        <td>${prompt.TieuDe}</td>
                        <td>${prompt.GioiHanTu}</td>
                        <td>
                            <span class="badge badge-${prompt.MucDo == 'Dễ' ? 'success' : prompt.MucDo == 'Trung bình' ? 'warning' : 'danger'}">
                                ${prompt.MucDo}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-${prompt.TrangThai == 1 ? 'success' : 'danger'}">
                                ${prompt.TrangThai == 1 ? 'Hoạt động' : 'Tắt'}
                            </span>
                        </td>
                    </tr>
                `;
            });
            $('#promptsTable tbody').html(html);
        } catch (e) {
            console.error('Error parsing prompts:', e);
            $('#promptsTable tbody').html('<tr><td colspan="6">Error loading data</td></tr>');
        }
    }).fail(function(xhr, status, error) {
        console.error('AJAX Error:', error);
        $('#promptsTable tbody').html('<tr><td colspan="6">AJAX Error: ' + error + '</td></tr>');
    });
}

function addTopic() {
    const formData = new FormData($('#addTopicForm')[0]);
    formData.append('action', 'add_topic');
    
    $.ajax({
        url: '<?= BASE_URL("api/writing-admin.php") ?>',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            try {
                const result = JSON.parse(response);
                if (result.success) {
                    $('#addTopicModal').modal('hide');
                    $('#addTopicForm')[0].reset();
                    loadTopics();
                    toastr.success('Thêm chủ đề thành công!');
                } else {
                    toastr.error(result.message);
                }
            } catch (e) {
                console.error('Error parsing response:', e);
                toastr.error('Có lỗi xảy ra');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            toastr.error('AJAX Error: ' + error);
        }
    });
}
</script>

</body>
</html>
