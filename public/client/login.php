<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");
require_once(__DIR__ . "/../../vendor/google-api/vendor/autoload.php");
$title = 'Đăng nhập tài khoản | ' . $Database->site("TenWeb");
$META_TITLE = "DungDepTrai - Đăng nhập tài khoản";
$META_IMAGE = "https://i.imgur.com/LMiRDR5.png";
$META_DESCRIPTION = "DungDepTrai - Đăng nhập tài khoản";
$META_SITE = BASE_URL("Auth/DangNhap");
require_once(__DIR__ . "/../../public/client/header.php");
if (isset($_SESSION["account"])) {
    $row = $Database->get_row("SELECT * FROM `dangkykhoahoc` WHERE `TaiKhoan` = '" . $_SESSION["account"] . "'  ");
    if (!$row) {
        return die('<script type="text/javascript">
            setTimeout(function(){ location.href = "' . BASE_URL('Page/KhoiTaoTaiKhoan') . '" }, 0);
            </script>
            ');
    }
    return die('<script type="text/javascript">
        setTimeout(function(){ location.href = "' . BASE_URL('') . '" }, 0);
        </script>
        ');
}

// Login with Google
try {
    // Kiểm tra xem có Client ID hợp lệ không
    if (empty(GOOGLE_APP_ID) || strpos(GOOGLE_APP_ID, '36428901594') !== false) {
        throw new Exception("Client ID không hợp lệ hoặc đã bị xóa");
    }
    
    // Kiểm tra class Google_Client có tồn tại không
    if (!class_exists('Google_Client')) {
        throw new Exception("Google Client library không được tìm thấy");
    }
    
    $client = new Google_Client();
    $client->setClientId(GOOGLE_APP_ID);
    $client->setClientSecret(GOOGLE_APP_SECRET);
    $client->setRedirectUri(GOOGLE_APP_CALLBACK_URL);
    $client->addScope("email");
    $client->addScope("profile");
    $loginGoogleUrl = $client->createAuthUrl();
} catch (Exception $e) {
    $loginGoogleUrl = "#";
    $googleError = "Google OAuth đang bảo trì. Vui lòng liên hệ admin để cập nhật.";
}


// Login with Facebook
try {
    // Kiểm tra xem có Facebook App ID hợp lệ không
    if (empty(FACEBOOK_APP_ID) || empty(FACEBOOK_APP_SECRET)) {
        throw new Exception("Facebook App không được cấu hình");
    }
    
    $fb = new Facebook\Facebook([
        'app_id' => FACEBOOK_APP_ID,
        'app_secret' => FACEBOOK_APP_SECRET,
        'default_graph_version' => 'v15.0', // Cập nhật version mới hơn
    ]);
    $helper = $fb->getRedirectLoginHelper();
    $permissions = ['email']; // optional
    $loginFacebookUrl = $helper->getLoginUrl(FACEBOOK_APP_CALLBACK_URL, $permissions);
} catch (Exception $e) {
    $loginFacebookUrl = "#";
    $facebookError = "Facebook Login đang bảo trì. Vui lòng liên hệ admin để cập nhật.";
}

?>
<style>
    <?= include_once(__DIR__ . "/../../assets/css/login.css");
    ?><?= include_once(__DIR__ . "/../../assets/css/main.css");
        ?>
</style>

<div class="header">
    <div class="grid wide">
        <div class="header_wrap">
            <a href="<?= BASE_URL("/") ?>">
                <h2 class="header__name"><?= $Database->site("TenWeb") ?></h2>
            </a>
            <div class="nav">
                <a href="" class="nav__course">Các khóa học</a>
                <a href="<?= BASE_URL("Auth/DangNhap") ?>" class="nav__statr btn">Bắt đầu học</a>
            </div>
        </div>
    </div>
</div>
<div class="container" style="
    margin: 150px auto;
">
    <div class="grid wide">
        <form class="form" action="" id="form">
            <div class="form__title">Đăng nhập</div>
            <div id="thongbao"></div>
            <input type="text" placeholder="Tên đăng nhập" class="form__account" id="account">
            <div class="form__password">
                <input type="password" class="input_password" placeholder="Mật khẩu" id="password" />
                <div id="show">Show</div>
            </div>

            <a href="<?= BASE_URL("Auth/QuenMatKhau") ?>" class="form__forget-password">Quên mật khẩu?</a>
            <button type="submit" id="btnLogin" class="form__login btn">ĐĂNG NHẬP</button>
            <span class="form__separate-text">HOẶC</span>
            <div class="form__separate"></div>
            <div class="wrap-social">
                <?php if (isset($loginFacebookUrl) && $loginFacebookUrl !== "#"): ?>
                    <a href="<?= $loginFacebookUrl  ?>" class="social__item"><img src="<?= BASE_URL("/") ?>/assets/img/facebook.svg" alt="" class="social__item-img"><span class="social__item-text">FACEBOOK</span></a>
                <?php else: ?>
                    <a href="#" onclick="alert('<?= isset($facebookError) ? $facebookError : 'Facebook Login tạm thời không khả dụng' ?>')" class="social__item"><img src="<?= BASE_URL("/") ?>/assets/img/facebook.svg" alt="" class="social__item-img"><span class="social__item-text">FACEBOOK</span></a>
                <?php endif; ?>
                <?php if (isset($loginGoogleUrl) && $loginGoogleUrl !== "#"): ?>
                    <a href="<?= $loginGoogleUrl ?>" class="social__item"><img src="<?= BASE_URL("/") ?>/assets/img/google.svg" alt="" class="social__item-img"><span class="social__item-text">GOOGLE</span></a>
                <?php else: ?>
                    <a href="#" onclick="alert('<?= isset($googleError) ? $googleError : 'Google Login tạm thời không khả dụng' ?>')" class="social__item"><img src="<?= BASE_URL("/") ?>/assets/img/google.svg" alt="" class="social__item-img"><span class="social__item-text">GOOGLE</span></a>
                <?php endif; ?>
            </div>
            <div class="form__not-account">
                Chưa có tài khoản? <a href="<?= BASE_URL("Auth/DangKy") ?>" class="form__not-account-link">Đăng kí ngay</a>
            </div>


        </form>
    </div>
</div>
<script src="<?= BASE_URL("/") ?>/assets/javascript/show-password.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $("#form").submit(function(e) {
            e.preventDefault();
        });
    });
    $("#btnLogin").on("click", function() {
        $.ajax({
            url: "<?= BASE_URL("assets/ajaxs/Auth.php"); ?>",
            method: "POST",
            data: {
                type: 'login',
                account: $("#account").val().trim(),
                password: $("#password").val().trim()
            },
            beforeSend: function() {
                $('#btnLogin').html('Đang xử lý').addClass("disabled");
                $('#loading_modal').addClass("loading--open");
            },
            success: function(response) {
                $("#thongbao").html(response);
                $('#btnLogin').html('Đăng nhập').removeClass("disabled");
                $('#loading_modal').removeClass("loading--open");
            }
        });
    });
</script>
<?php
require_once(__DIR__ . "/../../public/client/footer.php"); ?>