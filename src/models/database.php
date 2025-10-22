
<?php
/**
 * Database Connection
 * Sử dụng config.php để tự động chọn cấu hình phù hợp
 */

// Load config
require_once __DIR__ . '/../../config.php';

try {
    // Chuỗi DSN
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    
    // Tạo đối tượng PDO
    $db = new PDO($dsn, DB_USER, DB_PASS);

    // Cấu hình chế độ lỗi
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    // Kết nối thành công (không echo để tránh làm hỏng JSON response)
    // error_log("✅ Kết nối MySQL thành công!");
} catch (PDOException $e) {
    // Log lỗi vào file
    error_log("❌ Lỗi kết nối CSDL: " . $e->getMessage());
    
    // Hiển thị thông báo thân thiện
    if (ini_get('display_errors')) {
        die("Lỗi kết nối database: " . $e->getMessage());
    } else {
        die("Không thể kết nối đến cơ sở dữ liệu. Vui lòng kiểm tra cấu hình.");
    }
}
?>
