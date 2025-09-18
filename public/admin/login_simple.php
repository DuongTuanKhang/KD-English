<?php
session_start();
require_once(__DIR__ . "/../../configs/config.php");

// Simple login for testing
if (isset($_POST['login'])) {
    $_SESSION["account"] = "admin";
    $_SESSION["accountType"] = "admin"; 
    header("Location: home.php");
    exit;
}

if (isset($_SESSION["account"])) {
    header("Location: home.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login - Writing Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Admin Login - Writing Management</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group">
                                <label>Username:</label>
                                <input type="text" class="form-control" value="admin" readonly>
                            </div>
                            <div class="form-group">
                                <label>Password:</label>
                                <input type="password" class="form-control" value="admin" readonly>
                            </div>
                            <button type="submit" name="login" class="btn btn-primary">Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
