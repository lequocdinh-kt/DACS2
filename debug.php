<?php
/**
 * DEBUG TOOL - CHỈ DÙNG ĐỂ KIỂM TRA LỖI
 * XÓA FILE NÀY SAU KHI FIX XONG!
 */

// Bật hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Kiểm tra hệ thống</h1>";

// 1. Kiểm tra phiên bản PHP
echo "<h2>1. Phiên bản PHP</h2>";
echo "<p>PHP Version: <strong>" . phpversion() . "</strong></p>";
echo "<p>Required: PHP 7.4 hoặc cao hơn</p>";
if (version_compare(phpversion(), '7.4', '<')) {
    echo "<p style='color:red;'>⚠️ PHP version quá thấp!</p>";
} else {
    echo "<p style='color:green;'>✅ PHP version OK</p>";
}

// 2. Kiểm tra extensions
echo "<h2>2. PHP Extensions</h2>";
$required_extensions = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'session'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p style='color:green;'>✅ $ext: Đã cài đặt</p>";
    } else {
        echo "<p style='color:red;'>❌ $ext: CHƯA cài đặt</p>";
    }
}

// 3. Kiểm tra config.php
echo "<h2>3. Config File</h2>";
if (file_exists(__DIR__ . '/config.php')) {
    echo "<p style='color:green;'>✅ config.php tồn tại</p>";
    require_once __DIR__ . '/config.php';
    echo "<p>BASE_URL: <strong>" . BASE_URL . "</strong></p>";
    echo "<p>DB_HOST: <strong>" . DB_HOST . "</strong></p>";
    echo "<p>DB_NAME: <strong>" . DB_NAME . "</strong></p>";
} else {
    echo "<p style='color:red;'>❌ config.php KHÔNG tồn tại</p>";
}

// 4. Kiểm tra kết nối database
echo "<h2>4. Kết nối Database</h2>";
try {
    require_once __DIR__ . '/src/models/database.php';
    echo "<p style='color:green;'>✅ Kết nối database thành công</p>";
    
    // Test query
    $stmt = $db->query("SELECT COUNT(*) as total FROM movies");
    $result = $stmt->fetch();
    echo "<p>Số lượng phim trong database: <strong>" . $result['total'] . "</strong></p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Lỗi kết nối database:</p>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}

// 5. Kiểm tra các file quan trọng
echo "<h2>5. Các File Quan Trọng</h2>";
$important_files = [
    'index.php',
    'config.php',
    'src/models/database.php',
    'src/models/movie_db.php',
    'src/models/showtime_db.php',
    'src/views/booking_step1_showtimes.php',
    'src/controllers/showtimeController.php'
];

foreach ($important_files as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "<p style='color:green;'>✅ $file</p>";
    } else {
        echo "<p style='color:red;'>❌ $file - KHÔNG TỒN TẠI</p>";
    }
}

// 6. Kiểm tra quyền file
echo "<h2>6. Quyền Thư Mục</h2>";
$dirs_to_check = [
    '.',
    './src',
    './src/models',
    './src/views',
    './src/controllers'
];

foreach ($dirs_to_check as $dir) {
    if (is_writable($dir)) {
        echo "<p style='color:green;'>✅ $dir - Có quyền ghi</p>";
    } else {
        echo "<p style='color:orange;'>⚠️ $dir - KHÔNG có quyền ghi (có thể không cần)</p>";
    }
}

// 7. Kiểm tra session
echo "<h2>7. Session</h2>";
session_start();
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<p style='color:green;'>✅ Session hoạt động</p>";
    echo "<p>Session ID: " . session_id() . "</p>";
} else {
    echo "<p style='color:red;'>❌ Session KHÔNG hoạt động</p>";
}

// 8. Kiểm tra $_SERVER
echo "<h2>8. Server Info</h2>";
echo "<p>Server Software: <strong>" . $_SERVER['SERVER_SOFTWARE'] . "</strong></p>";
echo "<p>Document Root: <strong>" . $_SERVER['DOCUMENT_ROOT'] . "</strong></p>";
echo "<p>Script Filename: <strong>" . $_SERVER['SCRIPT_FILENAME'] . "</strong></p>";
echo "<p>HTTP Host: <strong>" . $_SERVER['HTTP_HOST'] . "</strong></p>";

// 9. Test require một file
echo "<h2>9. Test Require File</h2>";
try {
    require_once __DIR__ . '/src/models/movie_db.php';
    echo "<p style='color:green;'>✅ movie_db.php load thành công</p>";
    
    // Test function
    $movies = get_all_movies();
    echo "<p>Số phim lấy được: <strong>" . count($movies) . "</strong></p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Lỗi:</p>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

// 10. Test Booking URL
echo "<h2>10. Test Booking Step 1</h2>";
echo "<p>Kiểm tra xem trang booking có lỗi không...</p>";
try {
    $_GET['movieID'] = 1; // Giả lập movieID
    ob_start();
    include __DIR__ . '/src/views/booking_step1_showtimes.php';
    $output = ob_get_clean();
    
    if (strlen($output) > 100) {
        echo "<p style='color:green;'>✅ Trang booking load thành công</p>";
        echo "<p>Output length: " . strlen($output) . " bytes</p>";
    } else {
        echo "<p style='color:orange;'>⚠️ Trang booking load nhưng output ngắn</p>";
        echo "<pre>" . htmlspecialchars(substr($output, 0, 500)) . "</pre>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Lỗi khi load booking:</p>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<h2>⚠️ LƯU Ý: XÓA FILE NÀY SAU KHI DEBUG XONG!</h2>";
echo "<p>File này để lộ thông tin hệ thống, không nên để trên hosting lâu dài.</p>";
?>

<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
        background: #f5f5f5;
    }
    h1 {
        color: #333;
        border-bottom: 3px solid #007bff;
        padding-bottom: 10px;
    }
    h2 {
        color: #555;
        border-bottom: 1px solid #ddd;
        padding-bottom: 5px;
        margin-top: 30px;
    }
    p {
        line-height: 1.6;
    }
    pre {
        background: #fff;
        border: 1px solid #ddd;
        padding: 10px;
        overflow-x: auto;
    }
</style>
