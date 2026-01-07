<?php
/**
 * Session Helper
 * Khởi tạo session một cách an toàn
 */

// Configure session cookie settings BEFORE starting session
if (session_status() === PHP_SESSION_NONE) {
    // Set session cookie parameters
    ini_set('session.cookie_httponly', 1); // Prevent JavaScript access
    ini_set('session.cookie_samesite', 'Lax'); // CSRF protection
    ini_set('session.use_only_cookies', 1); // Only use cookies, not URL
    ini_set('session.cookie_path', '/'); // Available for entire domain
    ini_set('session.cookie_lifetime', 0); // Until browser closes
    
    // Start session
    session_start();
    
    // Debug logging
    error_log("=== Session Helper Debug ===");
    error_log("Session ID: " . session_id());
    error_log("Session userID: " . (isset($_SESSION['userID']) ? $_SESSION['userID'] : 'NOT SET'));
    error_log("Session user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET'));
    error_log("Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A'));
    error_log("===========================");
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
