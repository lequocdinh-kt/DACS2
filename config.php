<?php
/**
 * Cấu hình toàn hệ thống
 * File này giúp hệ thống hoạt động trên cả localhost và hosting
 */

// Phát hiện môi trường (localhost hay hosting)
$isLocalhost = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', 'localhost:8080']);

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
    define('DB_HOST', 'onehost-webhn072403.000nethost.com');  // Thường là localhost trên cPanel
    define('DB_NAME', 'slrnkpifhosting_DACS2');  // Đổi thành tên database thực tế
    define('DB_USER', 'slrnkpifhosting_xiaoying');  // Đổi thành user database
    define('DB_PASS', '2D3i$>?+ZZ!`_bc');  // Đổi thành password
    
    define('BASE_URL', 'https://lequocdinh.id.vn');
    define('BASE_PATH', '/');
    
    // Ẩn lỗi trên production
    ini_set('display_errors', 0);
    error_reporting(0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/error.log');
}

// Đường dẫn vật lý
define('ROOT_PATH', __DIR__);

/**
 * Helper function: Tạo URL tuyệt đối
 */
function url($path = '') {
    $path = ltrim($path, '/');
    return BASE_URL . BASE_PATH . $path;
}

/**
 * Helper function: Tạo đường dẫn vật lý
 */
function path($path = '') {
    $path = ltrim($path, '/');
    return ROOT_PATH . '/' . $path;
}

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

/**
 * Helper function: Asset URL (CSS, JS, Images)
 */
function asset($path) {
    $path = ltrim($path, '/');
    return BASE_URL . BASE_PATH . $path;
}
?>
