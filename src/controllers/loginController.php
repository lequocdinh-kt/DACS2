<?php
session_start();

// Lấy dữ liệu từ form
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

// Thực hiện validate cơ bản (thay bằng logic thực tế, DB, model...)
if ($username === '' || $password === '') {
    // missing fields
    header('Location: ../views/login.php?error=1');
    exit;
}

// Ví dụ kiểm tra tạm: user demo 'admin' / 'password'
// Thay bằng kiểm tra trong DB hoặc gọi model
if ($username === 'admin' && $password === 'password') {
    $_SESSION['user'] = $username;
    header('Location: ../views/login.php?success=1');
    exit;
} else {
    header('Location: ../views/login.php?error=1');
    exit;
}
?>