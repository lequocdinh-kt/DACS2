<?php
session_start();
require_once __DIR__ . '/../models/user_db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/login.php');
    exit;
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if ($email === '' || $password === '') {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Vui lòng nhập đầy đủ email và mật khẩu!'];
    header('Location: ../views/login.php');
    exit;
}

$user = authenticate_user($email, $password);
if ($user) {
    $_SESSION['user'] = $user;
    $_SESSION['userID'] = $user['userID'];
    $_SESSION['roleID'] = $user['roleID'];

    // Update last login (optional)
    update_last_login($user['userID']);

    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Đăng nhập thành công!'];

    // Redirect based on role
    if ($user['roleID'] == 1) {
        header('Location: ../views/admin/dashboard.php');
    } else {
        header('Location: ../views/index.php');
    }
    exit;
} else {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Email hoặc mật khẩu không đúng!'];
    header('Location: ../views/login.php');
    exit;
}

?>