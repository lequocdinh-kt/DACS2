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

    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($email === '' || $password === '') {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ email và mật khẩu!']);
        exit;
    }

    $user = authenticate_user($email, $password);
    if ($user) {
        $_SESSION['user'] = $user;
        $_SESSION['userID'] = $user['userID'];
        $_SESSION['roleID'] = $user['roleID'];

        // Update last login (optional)
        update_last_login($user['userID']);

        echo json_encode([
            'success' => true, 
            'message' => 'Đăng nhập thành công!', 
            'user' => [
                'username' => $user['username'], 
                'roleID' => $user['roleID']
            ]
        ]);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Email hoặc mật khẩu không đúng!']);
        exit;
    }
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Đã xảy ra lỗi hệ thống. Vui lòng thử lại!']);
    exit;
}
?>
