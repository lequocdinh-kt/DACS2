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
    
    // Kết nối thành công (không echo để tránh làm hỏng output)
    // error_log("✅ Database connected: $dbname");
    
} catch (PDOException $e) {
    // Log lỗi
    error_log("❌ Database connection error: " . $e->getMessage());
    
    // Hiển thị lỗi thân thiện (chỉ khi development)
    if (defined('DB_HOST') && DB_HOST === 'localhost') {
        die("
            <div style='font-family: Arial; padding: 20px; background: #ffebee; border-left: 4px solid #f44336;'>
                <h3>❌ Lỗi Kết Nối Database</h3>
                <p><strong>Message:</strong> {$e->getMessage()}</p>
                <p><strong>Host:</strong> $host</p>
                <p><strong>Database:</strong> $dbname</p>
                <p><strong>User:</strong> $username</p>
                <hr>
                <h4>✅ Giải pháp:</h4>
                <ol>
                    <li>Kiểm tra XAMPP/WAMP đã chạy chưa?</li>
                    <li>Database '<strong>$dbname</strong>' đã tạo chưa?</li>
                    <li>Import file <code>database_core.sql</code></li>
                    <li>Kiểm tra username/password MySQL</li>
                </ol>
            </div>
        ");
    } else {
        // Production: Không hiển thị chi tiết lỗi
        die("Database connection error. Please contact administrator.");
    }
}
?>



