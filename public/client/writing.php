<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");

$title = 'Writing | ' . $Database->site('TenWeb');

if (empty($_SESSION['account'])) {
    redirect(BASE_URL(''));
}

$maKhoaHoc = $_GET['maKhoaHoc'] ?? '';
$maBaiHoc = $_GET['maBaiHoc'] ?? '';

if (!$maKhoaHoc) {
    redirect(BASE_URL(''));
}

// Get course info
$khoaHoc = $Database->get_row("SELECT * FROM khoahoc WHERE MaKhoaHoc = '$maKhoaHoc' AND TrangThaiKhoaHoc = 1");
if (!$khoaHoc) {
    redirect(BASE_URL(''));
}

// Check if user is enrolled
$checkDangKy = $Database->get_row("SELECT * FROM dangkykhoahoc WHERE TaiKhoan = '" . $_SESSION['account'] . "' AND MaKhoaHoc = '$maKhoaHoc'");
if (!$checkDangKy) {
    redirect(BASE_URL(''));
}

include_once(__DIR__ . "/header.php");
?>

<link rel="stylesheet" href="<?= BASE_URL('assets/css/writing.css') ?>?id=<?= rand(0, 1000000) ?>">

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="writing-container">
                <div class="writing-header">
                    <div>
                        <h1 class="writing-title">Luyện Writing</h1>
                        <p class="writing-subtitle">Khóa học: <?= $khoaHoc['TenKhoaHoc'] ?></p>
                    </div>
                    <div class="d-flex gap-3">
                        <a href="<?= BASE_URL("Page/KhoaHoc/$maKhoaHoc") ?>" class="writing-btn">
                            <i class="fas fa-home"></i> Trang chủ
                        </a>
                        <a href="<?= BASE_URL("public/client/my-writing.php?maKhoaHoc=$maKhoaHoc") ?>"
                            class="writing-btn">
                            <i class="fas fa-history"></i> Bài đã nộp
                        </a>
                    </div>
                </div>

                <!-- Writing Topics -->
                <div class="writing-topics-grid" id="writingTopics">
                    <div class="writing-loading">
                        <i class="fas fa-spinner"></i>
                        <p>Đang tải chủ đề...</p>
                    </div>
                </div>

                <!-- Writing Prompts (hidden initially) -->
                <div class="writing-prompts-container hidden" id="writingPrompts">
                    <div class="mb-4">
                        <h3 id="currentTopicTitle" style="color: white; font-size: 28px; font-weight: 800;">Chủ đề</h3>
                    </div>
                    <div id="promptsList"
                        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; width: 100%;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Writing Modal -->
<div class="writing-modal" id="writingModal">
    <div class="writing-modal-content">
        <button class="writing-modal-close" onclick="closeWritingModal()">&times;</button>
        <div id="writingModalContent"></div>
    </div>
</div>

<script>
    let currentCourseId = <?= $maKhoaHoc ?>;
    let currentTopicId = null;

    // Function to find correct topic based on lesson name (dynamic mapping)
    function findTopicByLesson(lessonId, callback) {
        $.get('<?= BASE_URL("api/writing-client.php?action=find_topic_by_lesson&course=$maKhoaHoc&lesson=") ?>' + lessonId, function (data) {
            const result = typeof data === 'string' ? JSON.parse(data) : data;
            if (result.success && result.topic) {
                callback(result.topic.MaChuDe);
            } else {
                console.error('Cannot find topic for lesson:', lessonId);
                // Fallback to default topic or show error
                callback(null);
            }
        }).fail(function () {
            console.error('API call failed for lesson:', lessonId);
            callback(null);
        });
    }

    $(document).ready(function () {
        const urlParams = new URLSearchParams(window.location.search);
        const maBaiHoc = urlParams.get('maBaiHoc');

        if (maBaiHoc) {
            // Sử dụng API để tìm đúng topic cho lesson
            findTopicByLesson(parseInt(maBaiHoc), function (topicId) {
                if (topicId) {
                    // Ẩn topics và hiển thị prompts ngay
                    $('#writingTopics').hide();
                    loadSpecificTopicPrompts(topicId);
                } else {
                    // Nếu không tìm được topic, hiển thị thông báo lỗi
                    $('#writingTopics').html(`
                    <div class="writing-empty col-12">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h3>Không tìm thấy chủ đề</h3>
                        <p>Không thể tìm thấy chủ đề writing cho bài học này.</p>
                        <button class="writing-btn" onclick="location.reload()">Thử lại</button>
                    </div>
                `);
                }
            });
        } else {
            // Nếu không có maBaiHoc, hiển thị danh sách topics
            $('#writingPrompts').addClass('hidden');
            loadWritingTopics();
        }
    });

    function loadSpecificTopicPrompts(topicId) {
        // Lấy tên topic trước
        $.get('<?= BASE_URL("api/writing-client.php?action=topics&course=$maKhoaHoc") ?>', function (data) {
            const topics = typeof data === 'string' ? JSON.parse(data) : data;
            const topic = topics.find(t => t.MaChuDe == topicId);
            const topicTitle = topic ? topic.TenChuDe : 'Chủ đề';

            // Load prompts và hiển thị ngay
            currentTopicId = topicId;
            $('#currentTopicTitle').text(topicTitle);

            $.get('<?= BASE_URL("api/writing-client.php?action=prompts&topic=") ?>' + topicId, function (data) {
                console.log('Prompts loaded:', data);
                const prompts = typeof data === 'string' ? JSON.parse(data) : data;
                let html = '';

                if (prompts.length === 0) {
                    html = `
                    <div class="writing-empty">
                        <i class="fas fa-file-alt"></i>
                        <h3>Chưa có đề bài nào</h3>
                        <p>Chủ đề này chưa có đề bài nào.</p>
                    </div>
                `;
                } else {
                    prompts.forEach(prompt => {
                        const levelClass = prompt.MucDo === 'Dễ' ? 'level-easy' :
                            prompt.MucDo === 'Trung bình' ? 'level-medium' : 'level-hard';

                        html += `
                        <div class="writing-prompt-item">
                            <div class="writing-prompt-header">
                                <h4 class="writing-prompt-title">${prompt.TieuDe}</h4>
                                <span class="writing-prompt-level ${levelClass}">${prompt.MucDo || 'Dễ'}</span>
                            </div>
                            <div class="writing-prompt-content">${prompt.NoiDungDeBai}</div>
                            <div class="writing-prompt-meta">
                                <div class="writing-prompt-meta-info">
                                    <div class="writing-prompt-wordcount">Số từ: ${prompt.GioiHanTu}</div>
                                    <div class="writing-prompt-time">
                                        <i class="fas fa-clock"></i> ${prompt.ThoiGianLamBai || 30} phút
                                    </div>
                                </div>
                                <div class="writing-prompt-meta-buttons">
                                    ${prompt.isSubmitted ?
                                (prompt.gradeStatus === 'Đã chấm' ?
                                    `<button class="writing-btn writing-btn-success" onclick="viewResult(${prompt.MaDeBai})">
                                                <i class="fas fa-star"></i> Xem kết quả (${prompt.score || 'N/A'})
                                            </button>` :
                                    `<button class="writing-btn writing-btn-warning" onclick="viewSubmission(${prompt.MaDeBai})">
                                                <i class="fas fa-clock"></i> Chờ chấm bài
                                            </button>`
                                ) :
                                `<button class="writing-btn" onclick="startWriting(${prompt.MaDeBai})">
                                            <i class="fas fa-pen"></i> Viết bài
                                        </button>`
                            }
                                </div>
                            </div>
                        </div>
                    `;
                    });
                }

                $('#promptsList').html(html);
                $('#promptsList').css({
                    'display': 'grid',
                    'grid-template-columns': 'repeat(auto-fit, minmax(300px, 1fr))',
                    'gap': '20px',
                    'width': '100%'
                });
                $('#writingPrompts').removeClass('hidden');
            }).fail(function (xhr, status, error) {
                console.error('Load prompts error:', xhr, status, error);
                alert('Không thể tải danh sách đề bài. Vui lòng thử lại.');
            });
        });
    }

    function loadWritingTopics() {
        $.get('<?= BASE_URL("api/writing-client.php?action=topics&course=$maKhoaHoc") ?>', function (data) {
            console.log('Topics loaded:', data);
            const topics = typeof data === 'string' ? JSON.parse(data) : data;
            let html = '';

            if (topics.length === 0) {
                html = `
                <div class="writing-empty col-12">
                    <i class="fas fa-clipboard-list"></i>
                    <h3>Chưa có chủ đề nào</h3>
                    <p>Hiện tại chưa có chủ đề writing nào cho khóa học này.</p>
                </div>
            `;
            } else {
                topics.forEach(topic => {
                    html += `
                    <div class="writing-topic-card" onclick="loadTopicPrompts(${topic.MaChuDe}, '${topic.TenChuDe}')">
                        <div class="writing-topic-title">${topic.TenChuDe}</div>
                        <div class="writing-topic-desc">${topic.MoTa || ''}</div>
                        <div class="writing-topic-count">${topic.SoDeBai} đề bài</div>
                    </div>
                `;
                });
            }

            $('#writingTopics').html(html);
        }).fail(function (xhr, status, error) {
            console.error('Load topics error:', xhr, status, error);
            $('#writingTopics').html(`
            <div class="writing-empty col-12">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Lỗi tải dữ liệu</h3>
                <p>Không thể tải danh sách chủ đề. Vui lòng thử lại.</p>
            </div>
        `);
        });
    }

    function loadTopicPrompts(topicId, topicTitle) {
        console.log('Loading prompts for topic:', topicId, topicTitle);
        // Update URL to include maBaiHoc and redirect
        const url = new URL(window.location);
        url.searchParams.set('maBaiHoc', topicId);
        window.location.href = url.toString();
    }

    // Removed showTopics() and backToTopics() functions as they are no longer needed

    function startWriting(promptId) {
        console.log('startWriting called with promptId:', promptId);

        // Simple approach: tìm prompt từ danh sách hiện tại trong DOM
        let prompt = null;

        // Tìm trong HTML hiện tại để lấy data
        $('.writing-prompt-item').each(function () {
            const buttonElement = $(this).find('button[onclick*="startWriting(' + promptId + ')"]');
            if (buttonElement.length > 0) {
                prompt = {
                    MaDeBai: promptId,
                    TieuDe: $(this).find('.writing-prompt-title').text(),
                    NoiDungDeBai: $(this).find('.writing-prompt-content').text(),
                    GioiHanTu: $(this).find('.writing-prompt-wordcount').text().replace('Số từ: ', ''),
                    ThoiGianLamBai: $(this).find('.writing-prompt-time').text().replace(/[^\d]/g, '') || 30
                };
                return false; // break
            }
        });

        console.log('Found prompt:', prompt);

        if (!prompt) {
            alert('Không tìm thấy đề bài!');
            return;
        }

        const html = `
        <div class="writing-form-container">
            <h2 class="writing-form-title">${prompt.TieuDe}</h2>
            <div class="writing-form-group">
                <div class="alert alert-info">
                    <strong>Đề bài:</strong><br>
                    ${prompt.NoiDungDeBai}
                </div>
                <div class="alert alert-warning">
                    <strong>Yêu cầu:</strong> ${prompt.GioiHanTu} từ | 
                    <strong>Thời gian:</strong> ${prompt.ThoiGianLamBai} phút
                </div>
            </div>
            <form id="writingForm">
                <input type="hidden" name="maDeBai" value="${prompt.MaDeBai}">
                <div class="writing-form-group">
                    <label class="writing-form-label">Bài viết của bạn:</label>
                    <textarea class="writing-form-textarea writing-essay-textarea" 
                              name="noiDung" 
                              placeholder="Nhập bài viết của bạn tại đây..."
                              oninput="countWords(this)"></textarea>
                    <div class="word-counter" id="wordCounter">0 từ</div>
                </div>
                <div class="writing-buttons">
                    <button type="button" class="writing-btn writing-btn-secondary" onclick="closeWritingModal()">
                        Hủy
                    </button>
                    <button type="button" class="writing-btn writing-btn-success" onclick="submitWriting()">
                        <i class="fas fa-paper-plane"></i> Nộp bài
                    </button>
                </div>
                </form>
            </div>
        `;

        console.log('Setting modal content and showing modal');
        $('#writingModalContent').html(html);
        $('#writingModal').addClass('active');
        console.log('Modal should be visible now');
    }

    function closeWritingModal() {
        $('#writingModal').removeClass('active');
    }

    function countWords(textarea) {
        const text = textarea.value.trim();
        const words = text === '' ? 0 : text.split(/\s+/).length;
        const counter = document.getElementById('wordCounter');
        counter.textContent = words + ' từ';

        // Add warning/error classes based on word count
        const minWords = 50; // Minimum reasonable word count
        const maxWords = 500; // Maximum reasonable word count

        counter.className = 'word-counter';
        if (words < minWords) {
            counter.classList.add('warning');
        } else if (words > maxWords) {
            counter.classList.add('error');
        }
    }

    function submitWriting() {
        const formData = new FormData($('#writingForm')[0]);
        formData.append('action', 'submit_writing');

        const content = formData.get('noiDung').trim();
        if (!content) {
            alert('Vui lòng nhập nội dung bài viết!');
            return;
        }

        const wordCount = content.split(/\s+/).length;
        if (wordCount < 50) {
            if (!confirm('Bài viết của bạn có ít từ. Bạn có chắc muốn nộp bài không?')) {
                return;
            }
        }

        $.ajax({
            url: '<?= BASE_URL("api/writing-client.php") ?>',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                const result = JSON.parse(response);
                if (result.success) {
                    alert('Nộp bài thành công! Admin sẽ chấm điểm và phản hồi sớm.');
                    closeWritingModal();
                    // Reload prompts to update status
                    loadTopicPrompts(currentTopicId, $('#currentTopicTitle').text());
                } else {
                    alert(result.message || 'Có lỗi xảy ra. Vui lòng thử lại.');
                }
            },
            error: function () {
                alert('Có lỗi xảy ra. Vui lòng thử lại.');
            }
        });
    }

    function viewSubmission(promptId) {
        window.location.href = '<?= BASE_URL("public/client/my-writing.php?maKhoaHoc=") ?>' + currentCourseId + '&promptId=' + promptId;
    }

    function viewResult(promptId) {
        // Redirect to detailed result page
        window.location.href = '<?= BASE_URL("public/client/writing-result.php?maKhoaHoc=") ?>' + currentCourseId + '&promptId=' + promptId;
    }

    function startWriting(promptId) {
        console.log('Starting writing for prompt:', promptId);

        // Redirect to write-essay page
        const maKhoaHoc = new URLSearchParams(window.location.search).get('maKhoaHoc');
        window.location.href = `<?= BASE_URL("public/client/write-essay.php") ?>?maKhoaHoc=${maKhoaHoc}&promptId=${promptId}`;
    }

    // Close modal when clicking outside
    $(document).on('click', '.writing-modal', function (e) {
        if (e.target === this) {
            closeWritingModal();
        }
    });
</script>

<?php include_once(__DIR__ . "/footer.php"); ?>