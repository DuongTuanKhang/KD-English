<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");
$title = 'Trợ giúp từ ChatBot | ' . $Database->site('TenWeb') . '';
$locationPage = 'chatbot_page';
require_once(__DIR__ . "/../../public/client/header.php");

checkLogin();

// Lấy thông tin người dùng để hiển thị avatar/tên
$getTaiKhoan = $Database->get_row("select * from nguoidung where TaiKhoan = '" . $_SESSION["account"] . "'");

// Luôn hiển thị khung chat
$checkChatBotRoom = ['MaRoom' => 'local'];
?>
<style>
    <?= include_once(__DIR__ . "/../../assets/css/bxh.css"); ?>
    /* Thêm chút style cho bong bóng chat */
    .chatbot .chatbot-content {
        border-radius: 12px;
    }

    .chatbot.right .chatbot-content {
        background: #e8f8ee;
    }

    .chatbot.left .chatbot-content {
        background: #f3f3f3;
    }

    .chatbot-paragraph {
        white-space: pre-wrap;
    }
</style>

<div class="grid">
    <div class="row main-page">
        <div class="nav-container">
            <?php include_once(__DIR__ . "/../../public/client/navigation.php"); ?>
        </div>

        <div class="main_content-container">
            <div class="table-rating">
                <div class="page__title">Trợ giúp từ ChatBot</div>
                <div class="table-rating__header">
                    <img src="https://i.imgur.com/0J4kSSu.png" alt="" class="table-rating__header-img">
                </div>
                <div class="table-rating__content">
                    <!-- Luôn hiện khung chat vì-->
                    <div class="modal" id="modal-confirm-delete-messages">
                        <div class="modal-background"></div>
                        <div class="modal-content">
                            <div class="modal-content-body">
                                <div class="modal-header__text">Xác nhận xóa lịch sử đoạn chat</div>
                                <div class="modal-close modal-close-btn" aria-label="close"></div>
                                <div class="modal-content-body__text">
                                    Bạn muốn xóa lịch sử chat không? Các tin nhắn sẽ bị xóa trên trình duyệt này!
                                </div>

                                <div class="btn btn--primary" onclick="deleteMessages()">Xác nhận</div>
                            </div>
                        </div>
                    </div>

                    <div class="chatbot_content" id="chat_content"></div>

                    <div class="chatbot_form">
                        <textarea id="contentInput" class="chatbot_form_input-write"
                            placeholder="Hãy hỏi gì đó"></textarea>
                        <div class="btn btn--primary" id="btnSend" onclick="sendMessage()">Gửi</div>
                    </div>

                    <div style="display:flex; justify-content:center;">
                        <div class="btn btn--primary js-modal-trigger" data-target="modal-confirm-delete-messages">Xóa
                            đoạn chat</div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function getTimeCurrent() {
                try { return moment().format("YYYY-MM-DD HH:mm:ss"); }
                catch (e) {
                    const d = new Date();
                    const pad = n => String(n).padStart(2, '0');
                    return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds());
                }
            }

            function uuidv4() {
                return ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, c =>
                    (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
                );
            }

            function appendMessage(role, anhDaiDien, tenHienThi, noiDung, thoiGian, id) {
                const data = `
                    <div class="chatbot ${role === "user" ? "right" : "left"}">
                        <img src="${role === "user" ? anhDaiDien : "<?= BASE_URL("assets/img/robot-chatbot.svg") ?>"}" alt="" class="chatbot-avata ${role === "user" ? "right" : "left"}">
                        <div class="chatbot-content card">
                            <div class="chatbot-content-wrap">
                                <div class="chatbot-name">${role === "user" ? tenHienThi : "K&D English Group"}</div>
                                <div class="chatbot-paragraph" id="${id || ""}">${noiDung}</div>
                                <div class="chatbot-time">${thoiGian}</div>
                            </div>
                        </div>
                    </div>`;
                $("#chat_content").append(data);
                scrollToBottom();
            }

            function scrollToBottom() {
                const elem = document.getElementById('chat_content');
                elem.scrollTop = elem.scrollHeight;
            }

            function sendMessage() {
                const content = $("#contentInput").val().trim();
                if (!content) return;

                // Hiển thị tin nhắn user
                const timeUser = getTimeCurrent();
                appendMessage(
                    "user",
                    "<?= $getTaiKhoan["AnhDaiDien"] ?? BASE_URL('assets/img/default-avatar.png'); ?>",
                    "<?= $getTaiKhoan["TenHienThi"] ?? 'Bạn'; ?>",
                    content.replace(/(?:\r\n|\r|\n)/g, '<br>'),
                    timeUser
                );

                $("#contentInput").val("");
                $('#btnSend').html('Đang xử lý').addClass("disabled");
                $('#contentInput').addClass("disabled");

                // Tạo khung assistant rỗng trước
                const timeBot = getTimeCurrent();
                const uuid = uuidv4();
                appendMessage("assistant", "<?= BASE_URL("assets/img/logo.png") ?>", "K&D English Group", "", timeBot, uuid);
                const div = document.getElementById(uuid);

                // Gọi Gemini qua PHP
                $.ajax({
                    url: "<?= BASE_URL("public/gemini_bot.php"); ?>",
                    method: "POST",
                    data: { prompt: content },
                    dataType: "json",
                    success: function (res) {
                        if (res && res.ok) {
                            div.innerHTML = (res.text || "Xin lỗi, tôi chưa có câu trả lời.").replace(/(?:\r\n|\r|\n)/g, '<br>');
                        } else {
                            div.innerHTML = `<i>Lỗi Gemini: ${res && res.msg ? res.msg : 'Không xác định'}</i>`;
                        }
                    },
                    error: function (xhr) {
                        console.error(xhr?.responseText || xhr);
                        div.innerHTML = "<i>Không kết nối được tới ChatBot K&D English Group</i>";
                    },
                    complete: function () {
                        $('#btnSend').html('Gửi').removeClass("disabled");
                        $('#contentInput').removeClass("disabled");
                    }
                });
            }

            function deleteMessages() {
                $("#chat_content").empty();
                // đóng modal nếu có class điều khiển
                try {
                    document.querySelectorAll('.modal').forEach(m => m.classList.remove('is-active'));
                } catch (e) { }
            }

            // Kích hoạt modal
            document.addEventListener('click', function (e) {
                const trigger = e.target.closest('.js-modal-trigger');
                if (trigger) {
                    const target = trigger.dataset.target;
                    const modal = document.getElementById(target);
                    if (modal) modal.classList.add('is-active');
                }
                if (e.target.classList.contains('modal-background') || e.target.classList.contains('modal-close-btn')) {
                    const modal = e.target.closest('.modal');
                    if (modal) modal.classList.remove('is-active');
                }
            });

            // Hiển thị lời chào mặc định
            (function initEmptyState() {
                const welcome = `
                    <div class="chatbot-content-text">Bạn chưa có tin nhắn nào với ChatBot K&D EnglishGroup</div>
                    <div class="chatbot-content-text">Hãy thử hỏi ChatBot ở phía dưới</div>
                `;
                $("#chat_content").append(welcome);
            })();
        </script>

        <div class="menu_right-container">
            <div class="statistical">
                <div class="interact">
                    <div class="interact__question-congratulations">
                        <div class="interact__question-title">GIỚI THIỆU </div>
                        <div class="interact__question-text">
                            Dùng ChatBot K&D English Group để giải đáp bất kỳ câu hỏi nào, đặc biệt là liên quan đến bài
                            học
                        </div>
                        <img src="<?= BASE_URL("/") ?>/assets/img/congratulations.svg" alt=""
                            class="interact__question-img">
                    </div>
                </div>
            </div>
        </div>

        <?php include_once(__DIR__ . "/../../public/client/navigation_mobile.php"); ?>
        <?php require_once(__DIR__ . "/../../public/client/footer.php"); ?>