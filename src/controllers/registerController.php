<?php
session_start();
require_once __DIR__ . '/../models/user_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validate inputs
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $_SESSION['flash'] = [
            'type' => 'error',
            'message' => 'Vui lòng điền đầy đủ thông tin!'
        ];
        header('Location: ../views/register.php');
        exit;
    }
    
    // Check username length
    if (strlen($username) < 3) {
        $_SESSION['flash'] = [
            'type' => 'error',
            'message' => 'Tên người dùng phải có ít nhất 3 ký tự!'
        ];
        header('Location: ../views/register.php');
        exit;
    }
    
    // Check password length
    if (strlen($password) < 6) {
        $_SESSION['flash'] = [
            'type' => 'error',
            'message' => 'Mật khẩu phải có ít nhất 6 ký tự!'
        ];
        header('Location: ../views/register.php');
        exit;
    }
    
    // Check if passwords match
    if ($password !== $confirmPassword) {
        $_SESSION['flash'] = [
            'type' => 'error',
            'message' => 'Mật khẩu xác nhận không khớp!'
        ];
        header('Location: ../views/register.php');
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['flash'] = [
            'type' => 'error',
            'message' => 'Email không hợp lệ!'
        ];
        header('Location: ../views/register.php');
        exit;
    }
    
    // Check if email or username already exists
    if (email_or_username_exists($email)) {
        $_SESSION['flash'] = [
            'type' => 'error',
            'message' => 'Email đã được sử dụng!'
        ];
        header('Location: ../views/register.php');
        exit;
    }
    
    if (email_or_username_exists($username)) {
        $_SESSION['flash'] = [
            'type' => 'error',
            'message' => 'Tên người dùng đã tồn tại!'
        ];
        header('Location: ../views/register.php');
        exit;
    }
    
    // Create user
    $userData = [
        'username' => $username,
        'email' => $email,
        'password' => $password,
        'roleID' => 1 // Default role: customer
    ];
    
    $userId = create_user($userData);
    
    if ($userId) {
        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => 'Đăng ký thành công! Vui lòng đăng nhập.'
        ];
        header('Location: ../views/login.php');
        exit;
    } else {
        $_SESSION['flash'] = [
            'type' => 'error',
            'message' => 'Đã có lỗi xảy ra. Vui lòng thử lại!'
        ];
        header('Location: ../views/register.php');
        exit;
    }
} else {
    header('Location: ../views/register.php');
    exit;
}
?>