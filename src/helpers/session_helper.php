<?php
/**
 * Session Helper
 * Khởi tạo session một cách an toàn
 */

// Khởi động session nếu chưa được khởi động
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Get session value
 */
function get_session($key, $default = null) {
    return $_SESSION[$key] ?? $default;
}

/**
 * Set session value
 */
function set_session($key, $value) {
    $_SESSION[$key] = $value;
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['userID']) && !empty($_SESSION['userID']);
}

/**
 * Get current logged in user data
 */
function get_logged_user() {
    return $_SESSION['user'] ?? null;
}

/**
 * Flash message
 */
function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
?>
