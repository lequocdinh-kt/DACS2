<?php
session_start();

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output
ini_set('log_errors', 1); // Log errors instead

header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../models/user_db.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }

    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // Validate input
    if ($username === '' || $email === '' || $password === '' || $confirmPassword === '') {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin!']);
        exit;
    }

    if (strlen($username) < 3 || strlen($username) > 50) {
        echo json_encode(['success' => false, 'message' => 'Tên người dùng phải từ 3-50 ký tự!']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email không hợp lệ!']);
        exit;
    }

    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự!']);
        exit;
    }

    if ($password !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'Mật khẩu xác nhận không khớp!']);
        exit;
    }

    // Check if email already exists
    if (email_exists($email)) {
        echo json_encode(['success' => false, 'message' => 'Email đã được sử dụng!']);
        exit;
    }

    // Check if username already exists
    if (username_exists($username)) {
        echo json_encode(['success' => false, 'message' => 'Tên người dùng đã được sử dụng!']);
        exit;
    }

    // Register user
    $result = register_user($username, $email, $password);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Đăng ký thành công! Vui lòng đăng nhập.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Đã xảy ra lỗi. Vui lòng thử lại!']);
    }
} catch (Exception $e) {
    error_log("Register error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Đã xảy ra lỗi hệ thống. Vui lòng thử lại!']);
    exit;
}
?>
