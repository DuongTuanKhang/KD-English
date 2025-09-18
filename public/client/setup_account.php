<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");

// Kiểm tra đăng nhập
if (!isset($_SESSION["account"])) {
    redirect(BASE_URL("Auth/DangNhap"));
    exit;
}

// Lấy thông tin user
$userInfo = $Database->get_row("SELECT * FROM nguoidung WHERE TaiKhoan = '" . $_SESSION["account"] . "'");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khởi tạo tài khoản | <?= $Database->site("TenWeb") ?></title>
    <style>
    :root {
        --main-color: #3b82f6;
        --green-color: #58cc02;
        --green-shadow-color: #4aa002;
        --white-color: #ffffff;
        --grey-plus-1-color: #e5e7eb;
        --box-shadow-card: #f3f4f6;
        --header-height: 70px;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f5f5f5;
        line-height: 1.6;
    }

    .grid {
        width: 100%;
    }

    .wide {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 15px;
    }

    .header {
        background-color: var(--main-color);
        position: fixed;
        top: 0;
        width: 100%;
        z-index: 1000;
        height: var(--header-height);
    }

    .header_wrap {
        color: var(--white-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        height: var(--header-height);
    }

    .header__name {
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
    }

    .nav__statr {
        color: var(--white-color);
        background-color: var(--green-color);
        padding: 10px 20px;
        border-radius: 25px;
        font-weight: 600;
    }

    .container {
        min-height: 100vh;
        margin-top: var(--header-height);
        padding: 30px 0;
    }

    .content {
        margin: 0 auto;
        margin-top: 30px;
        max-width: 600px;
    }

    .content__heading {
        display: flex;
        justify-content: space-around;
        margin-bottom: 40px;
    }

    .content__switch {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .content__switch-number {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background-color: var(--green-color);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: bold;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }

    .content__switch-text {
        font-size: 1rem;
        font-weight: 600;
        text-align: center;
    }

    .content__choose {
        text-align: center;
    }

    .content__choose-heading {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 20px;
        color: #333;
    }

    .content__choose-wrap {
        display: flex;
        justify-content: space-around;
        gap: 20px;
        margin: 30px 0;
    }

    .content__choose-language {
        flex: 1;
        max-width: 200px;
        padding: 20px;
        border: 3px solid #e5e7eb;
        border-radius: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .content__choose-language:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
    }

    .content__choose-language.selected {
        border-color: var(--green-color);
        background-color: rgba(88, 204, 2, 0.1);
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(88, 204, 2, 0.3);
    }

    .content__choose-language-background {
        background: #f3f4f6;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 15px;
    }

    .content__choose-language-img {
        width: 80px;
        height: 60px;
        object-fit: contain;
    }

    .btn {
        padding: 15px 40px;
        font-size: 1.2rem;
        background-color: var(--green-color);
        color: white;
        border: none;
        border-radius: 25px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
        margin-top: 20px;
    }

    .btn:hover {
        background-color: var(--green-shadow-color);
        transform: translateY(-2px);
        box-shadow: 0 5px 10px rgba(0,0,0,0.2);
    }

    .btn:disabled {
        background-color: #ccc;
        cursor: not-allowed;
        transform: none;
    }

    select {
        width: 100%;
        padding: 15px;
        font-size: 1.1rem;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        margin: 15px 0;
        background: white;
    }

    .goal-options {
        display: flex;
        flex-direction: column;
        gap: 15px;
        max-width: 500px;
        margin: 0 auto;
    }

    .goal-option {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 30px;
        border: 3px solid #e5e7eb;
        border-radius: 15px;
        cursor: pointer;
        background: white;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .goal-option:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        border-color: #ccc;
    }

    .goal-option.selected {
        border-color: var(--green-color);
        background-color: rgba(88, 204, 2, 0.1);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(88, 204, 2, 0.3);
    }

    .goal-level {
        font-size: 1.3rem;
        font-weight: 600;
        color: #333;
    }

    .goal-words {
        font-size: 1.1rem;
        color: var(--green-color);
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .content__choose-wrap {
            flex-direction: column;
            align-items: center;
        }
        
        .content__choose-language {
            max-width: 250px;
        }
        
        .content__heading {
            flex-direction: column;
            gap: 20px;
        }

        .content__switch {
            flex-direction: row;
            gap: 15px;
        }
    }
    </style>
</head>
<body>

<div class="header">
    <div class="grid wide">
        <div class="header_wrap">
            <h2 class="header__name"><?= $Database->site("TenWeb") ?></h2>
            <div class="nav">
                <div class="nav__statr">Xin chào, <?= $userInfo['TenHienThi'] ?? $_SESSION["account"] ?>!</div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="grid wide">
        <div class="content">
            <!-- Step Progress -->
            <div class="content__heading">
                <div class="content__switch">
                    <div class="content__switch-number" id="step1">1</div>
                    <div class="content__switch-text">Chọn khóa học</div>
                </div>
                <div class="content__switch">
                    <div class="content__switch-number" id="step2" style="background-color: #ccc;">2</div>
                    <div class="content__switch-text">Đặt mục tiêu</div>
                </div>
                <div class="content__switch">
                    <div class="content__switch-number" id="step3" style="background-color: #ccc;">3</div>
                    <div class="content__switch-text">Bắt đầu học</div>
                </div>
            </div>

            <!-- Step 1: Choose Language -->
            <div class="content__choose" id="languageStep">
                <div class="content__choose-heading">🎉 Chào mừng!</div>
                <p style="text-align: center; margin-bottom: 30px; font-size: 1.6rem;">Hãy thiết lập mục tiêu học tập của bạn</p>
                
                <h3 style="margin-bottom: 20px; font-size: 2rem;">Tôi muốn học:</h3>
                
                <div class="content__choose-wrap">
                    <div class="content__choose-language" data-course="1" data-language="english">
                        <div class="content__choose-language-background">
                            <img src="<?= BASE_URL("assets/img/America.png") ?>" alt="Tiếng Anh" class="content__choose-language-img">
                        </div>
                        <div style="margin-top: 10px; font-size: 1.8rem; font-weight: bold;">Tiếng Anh</div>
                    </div>
                    
                    <div class="content__choose-language" data-course="2" data-language="japanese">
                        <div class="content__choose-language-background">
                            <img src="<?= BASE_URL("assets/img/Japan.png") ?>" alt="Tiếng Nhật" class="content__choose-language-img">
                        </div>
                        <div style="margin-top: 10px; font-size: 1.8rem; font-weight: bold;">Tiếng Nhật</div>
                    </div>
                </div>
                
                <button class="btn" id="continueBtn" style="display: none;">
                    Tiếp tục
                </button>
            </div>

            <!-- Step 2: Set Goal (Hidden initially) -->
            <div class="content__choose" id="goalStep" style="display: none;">
                <div class="content__choose-heading">Đặt mục tiêu học tập hàng ngày</div>
                
                <div style="margin-top: 40px;">
                    <div class="goal-options">
                        <div class="goal-option" data-goal="1">
                            <div class="goal-level">Thông thường</div>
                            <div class="goal-words">5 từ mới</div>
                        </div>
                        
                        <div class="goal-option" data-goal="2">
                            <div class="goal-level">Dễu dãn</div>
                            <div class="goal-words">10 từ mới</div>
                        </div>
                        
                        <div class="goal-option" data-goal="3">
                            <div class="goal-level">Nghiêm túc</div>
                            <div class="goal-words">15 từ mới</div>
                        </div>
                        
                        <div class="goal-option" data-goal="4">
                            <div class="goal-level">Cao độ</div>
                            <div class="goal-words">20 từ mới</div>
                        </div>
                    </div>
                    
                    <p style="text-align: center; margin-top: 30px; color: #666; font-size: 1.1rem;">
                        Bạn có thể thay đổi mục tiêu này trong cài đặt hồ sơ
                    </p>
                </div>
                
                <button class="btn" id="nextBtn" style="display: none; margin-top: 30px;">
                    Tiếp tục
                </button>
            </div>

            <!-- Step 3: Completion (Hidden initially) -->
            <div class="content__choose" id="completionStep" style="display: none;">
                <div class="content__choose-heading">🎉 Thiết lập hoàn tất!</div>
                <p style="text-align: center; margin: 30px 0; font-size: 1.4rem;">
                    Tuyệt vời! Bây giờ hãy bắt đầu hành trình học tập của bạn!
                </p>
                
                <button class="btn" id="startLearningBtn">
                    Bắt đầu học
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    let selectedCourse = 0;
    let selectedGoal = 0;

    // Language selection
    $('.content__choose-language').click(function() {
        $('.content__choose-language').removeClass('selected');
        $(this).addClass('selected');
        
        selectedCourse = $(this).data('course');
        console.log('Selected course:', selectedCourse);
        
        // Show continue button
        $('#continueBtn').show();
    });

    // Continue to step 2
    $('#continueBtn').click(function() {
        if (selectedCourse > 0) {
            // Update step indicators
            $('#step1').css('background-color', '#ccc');
            $('#step2').css('background-color', 'var(--green-color)');
            
            // Hide step 1, show step 2
            $('#languageStep').hide();
            $('#goalStep').show();
        }
    });

    // Goal selection
    $('.goal-option').click(function() {
        $('.goal-option').removeClass('selected');
        $(this).addClass('selected');
        
        selectedGoal = $(this).data('goal');
        console.log('Selected goal:', selectedGoal);
        
        // Show next button
        $('#nextBtn').show();
    });

    // Continue to step 3
    $('#nextBtn').click(function() {
        if (selectedGoal > 0) {
            // Update step indicators
            $('#step2').css('background-color', '#ccc');
            $('#step3').css('background-color', 'var(--green-color)');
            
            // Hide step 2, show step 3
            $('#goalStep').hide();
            $('#completionStep').show();
        }
    });

    // Start learning - redirect to course
    $('#startLearningBtn').click(function() {
        if (selectedCourse > 0 && selectedGoal > 0) {
            // Send data to server
            $.ajax({
                url: '<?= BASE_URL("assets/ajaxs/SetupCourse.php") ?>',
                method: 'POST',
                data: {
                    type: 'setupCourse',
                    maKhoaHoc: selectedCourse,
                    maMucTieu: selectedGoal
                },
                success: function(response) {
                    console.log('Setup success:', response);
                    
                    // Redirect to specific course page based on selection
                    let courseUrl = '';
                    if (selectedCourse == 1) {
                        // Tiếng Anh - sử dụng URL khóa học với ID
                        courseUrl = '<?= BASE_URL("Page/KhoaHoc/1") ?>';
                    } else if (selectedCourse == 2) {
                        // Tiếng Nhật - sử dụng URL khóa học với ID
                        courseUrl = '<?= BASE_URL("Page/KhoaHoc/2") ?>';
                    } else {
                        // Default to home
                        courseUrl = '<?= BASE_URL("Page/Home") ?>';
                    }
                    
                    window.location.href = courseUrl;
                },
                error: function(xhr, status, error) {
                    console.error('Setup error:', error);
                    alert('Có lỗi xảy ra. Vui lòng thử lại!');
                }
            });
        } else {
            alert('Vui lòng chọn đầy đủ thông tin!');
        }
    });
});
</script>

</body>
</html>