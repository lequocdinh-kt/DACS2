<?php
/**
 * Logout Controller
 * Xử lý đăng xuất người dùng
 */

// Khởi động session nếu chưa được khởi động
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lưu thông tin trước khi xóa (để redirect về đúng trang)
$redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/index.php';

// Xóa tất cả session variables
$_SESSION = array();

// Xóa session cookie nếu có
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Hủy session
session_destroy();

// Khởi tạo session mới để hiển thị flash message
session_start();
$_SESSION['flash'] = [
    'type' => 'success',
    'message' => 'Đăng xuất thành công!'
];

// Redirect về trang chủ hoặc trang trước đó
header('Location: /index.php');
exit;
?>
