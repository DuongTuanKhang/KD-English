<?php
// Start output buffering
ob_start();

require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");

// Handle login
if (isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Simple login - bạn có thể thay đổi logic này
    if ($username && $password) {
        $_SESSION['username'] = $username;
        $_SESSION['user_id'] = 1; // Default user ID
        
        header("location: " . BASE_URL("GrammarQuiz") . "?topic=12");
        exit();
    } else {
        $error = "Vui lòng nhập username và password";
    }
}

$title = 'Login | ' . $Database->site("TenWeb");
require_once(__DIR__ . "/../../public/client/header.php");
?>

<style>
body {
    font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0;
}

.login-container {
    background: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    max-width: 400px;
    width: 100%;
}

.login-header {
    text-align: center;
    margin-bottom: 30px;
}

.login-header h1 {
    color: #333;
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 10px;
}

.login-header p {
    color: #666;
    font-size: 1rem;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.form-group input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
    box-sizing: border-box;
}

.form-group input:focus {
    outline: none;
    border-color: #667eea;
}

.btn-login {
    width: 100%;
    background: linear-gradient(45deg, #667eea, #764ba2);
    color: white;
    border: none;
    padding: 12px;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.btn-login:hover {
    transform: translateY(-2px);
}

.error {
    background: #f8d7da;
    color: #721c24;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 20px;
    border: 1px solid #f5c6cb;
}

.demo-note {
    background: #d1ecf1;
    color: #0c5460;
    padding: 15px;
    border-radius: 8px;
    margin-top: 20px;
    border: 1px solid #bee5eb;
    font-size: 0.9rem;
}
</style>

<div class="login-container">
    <div class="login-header">
        <h1>Grammar Quiz</h1>
        <p>Đăng nhập để bắt đầu</p>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit" name="login" class="btn-login">
            Đăng nhập
        </button>
    </form>
    
    <div class="demo-note">
        <strong>Demo:</strong> Nhập bất kỳ username và password nào để truy cập
    </div>
</div>

<?php 
require_once(__DIR__ . "/../../public/client/footer.php"); 
if (ob_get_level()) {
    ob_end_flush();
}
?>
