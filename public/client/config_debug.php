<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== CONFIG DEBUG ===<br>";

// Test các file config step by step
echo "1. Loading config.php...<br>";
try {
    require_once(__DIR__ . "/../../configs/config.php");
    echo "   ✓ config.php loaded<br>";
} catch (Exception $e) {
    echo "   ✗ Error loading config.php: " . $e->getMessage() . "<br>";
    exit;
}

echo "2. Testing Database object...<br>";
if (isset($Database)) {
    echo "   ✓ Database object exists<br>";
    try {
        $test = $Database->site('TenWeb');
        echo "   ✓ Database->site() works: " . $test . "<br>";
    } catch (Exception $e) {
        echo "   ✗ Database->site() error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "   ✗ Database object not found<br>";
}

echo "3. Testing BASE_URL constant...<br>";
if (defined('BASE_URL')) {
    echo "   ✓ BASE_URL constant defined: " . BASE_URL . "<br>";
} else {
    echo "   ✗ BASE_URL constant not defined<br>";
}

echo "4. Loading function.php...<br>";
try {
    require_once(__DIR__ . "/../../configs/function.php");
    echo "   ✓ function.php loaded<br>";
} catch (Exception $e) {
    echo "   ✗ Error loading function.php: " . $e->getMessage() . "<br>";
}

echo "5. Testing BASE_URL function...<br>";
if (function_exists('BASE_URL')) {
    echo "   ✓ BASE_URL function exists<br>";
    try {
        $url = BASE_URL('test');
        echo "   ✓ BASE_URL function works: " . $url . "<br>";
    } catch (Exception $e) {
        echo "   ✗ BASE_URL function error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "   ✗ BASE_URL function not found<br>";
}

echo "6. Testing Session...<br>";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "   ✓ Session is active<br>";
    echo "   Session ID: " . session_id() . "<br>";
} else {
    echo "   ✗ Session not active<br>";
}

echo "<br>=== DEBUG COMPLETED ===<br>";
?>
