<?php
/**
 * Database Connection
 * Kết nối PDO MySQL - Tự động phát hiện localhost/hosting
 */

// Load config nếu chưa có
if (!defined('DB_HOST')) {
    require_once __DIR__ . '/../../config.php';
}

// Thông tin kết nối (dùng từ config.php hoặc hardcode cho localhost)
$host = defined('DB_HOST') ? DB_HOST : 'localhost';
$dbname = defined('DB_NAME') ? DB_NAME : 'dacs2';
$username = defined('DB_USER') ? DB_USER : 'root';
$password = defined('DB_PASS') ? DB_PASS : '';

try {
    // Tạo kết nối PDO
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $db = new PDO($dsn, $username, $password);
    
    // Cấu hình PDO
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
} catch (PDOException $e) {
    // Log lỗi
    error_log("Database Error: " . $e->getMessage());
    
    // Hiển thị lỗi đơn giản
    if (defined('DB_HOST') && DB_HOST === 'localhost') {
        die('<div style="padding:20px;background:#ffebee;border-left:4px solid #f44336;">
            <h3>Database Connection Error</h3>
            <p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>
            <p><strong>Host:</strong> ' . htmlspecialchars($host) . '</p>
            <p><strong>Database:</strong> ' . htmlspecialchars($dbname) . '</p>
            <p><strong>User:</strong> ' . htmlspecialchars($username) . '</p>
        </div>');
    } else {
        die('Database connection error. Please contact administrator.');
    }
}



