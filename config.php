<?php
/**
 * Cấu hình toàn hệ thống
 * File này giúp hệ thống hoạt động trên cả localhost và hosting
 */

// Phát hiện môi trường (localhost hay hosting)
$isLocalhost = in_array($_SERVER['HTTP_HOST'], ['localhost', 'localhost:3000', '127.0.0.1', 'localhost:8080', 'localhost:80']);

if ($isLocalhost) {
    // Cấu hình cho LOCALHOST (XAMPP)
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'dacs2');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    
    define('BASE_URL', 'http://localhost');
    define('BASE_PATH', '/');
    
    // Hiển thị lỗi khi dev
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    
} else {
    // Cấu hình cho HOSTING (cPanel)
    // LƯU Ý: Kiểm tra thông tin chính xác trong cPanel → MySQL Databases
    define('DB_HOST', 'localhost');  // Thường là 'localhost' trên hầu hết hosting
    define('DB_NAME', 'slrnkpifhosting_DACS2');
    define('DB_USER', 'slrnkpifhosting_xiaoying');
    define('DB_PASS', 'cCQ!FyTtD;)n1IN');
    
    define('BASE_URL', 'https://lequocdinh.id.vn');
    define('BASE_PATH', '/');
    
    // Bật lỗi tạm thời để debug (TẮT sau khi fix xong!)
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/error.log');
}

// Đường dẫn vật lý
define('ROOT_PATH', __DIR__);

// ===== CẤU HÌNH EMAIL (PHPMailer) =====
// Sử dụng Gmail SMTP - Cần bật "App Password" từ Google Account
define('MAIL_HOST', 'smtp.gmail.com');              // SMTP server
define('MAIL_PORT', 587);                            // Port (587 cho TLS, 465 cho SSL)
define('MAIL_USERNAME', 'xiaoying1805@gmail.com');     // 🔥 THAY ĐỔI: Email của bạn
define('MAIL_PASSWORD', 'drqp waeh onet tvey');        // 🔥 THAY ĐỔI: App Password (không phải mật khẩu Gmail)
define('MAIL_FROM_EMAIL', 'xiaoying1805@gmail.com');   // Email gửi đi
define('MAIL_FROM_NAME', 'VKU Cinema');              // Tên hiển thị
define('MAIL_ENCRYPTION', 'tls');                    // tls hoặc ssl

// Chỉ định nghĩa functions nếu chưa tồn tại
if (!function_exists('url')) {
    /**
     * Helper function: Tạo URL tuyệt đối
     */
    function url($path = '') {
        $path = ltrim($path, '/');
        return BASE_URL . BASE_PATH . $path;
    }
}

if (!function_exists('path')) {
    /**
     * Helper function: Tạo đường dẫn vật lý
     */
    function path($path = '') {
        $path = ltrim($path, '/');
        return ROOT_PATH . '/' . $path;
    }
}

if (!function_exists('redirect')) {
    /**
     * Helper function: Redirect an toàn
     */
    function redirect($path = '/') {
        if (strpos($path, 'http') === 0) {
            header("Location: $path");
        } else {
            header("Location: " . url($path));
        }
        exit();
    }
}

if (!function_exists('asset')) {
    /**
     * Helper function: Asset URL (CSS, JS, Images)
     */
    function asset($path) {
        $path = ltrim($path, '/');
        return BASE_URL . BASE_PATH . $path;
    }
}
?>
