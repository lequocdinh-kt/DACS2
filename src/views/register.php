<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký - Hệ Thống</title>
    <link rel="stylesheet" href="/src/styles/register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Phần bên trái - Hình ảnh -->
        <div class="left-section">
            <div class="welcome-msg">
                <h1>Tham gia cùng chúng tôi</h1>
                <p class="brand">VKU Cinema</p>
            </div>

            <div class="slideshow-outer">
                <div class="slideshow-frame">
                    <img id="slideshow-current" class="slide-img" src="/src/img/posters/1.jpg" alt="Poster current" draggable="false" />
                    <img id="slideshow-next" class="slide-img slide-next" src="/src/img/posters/2.jpg" alt="Poster next" draggable="false" />
                </div>
                <div class="slideshow-dots" id="slideshow-dots"></div>
            </div>
        </div>

        <!-- Phần bên phải - Form đăng ký -->
        <div class="right-section">
            <div class="login-box">
                <div class="login-header">
                    <h2>Đăng Ký</h2>
                    <p>Tạo tài khoản mới để bắt đầu</p>
                </div>

                <?php if(!empty($_SESSION['flash'])): ?>
                    <?php $flash = $_SESSION['flash']; unset($_SESSION['flash']); ?>
                    <div class="alert <?php echo $flash['type'] === 'success' ? 'alert-success' : 'alert-error'; ?>">
                        <i class="<?php echo $flash['type'] === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'; ?>"></i>
                        <span><?php echo htmlspecialchars($flash['message']); ?></span>
                    </div>
                <?php endif; ?>

                <form action="/src/controllers/registerController.php" method="POST" class="login-form" id="registerForm">
                    <div class="form-group">
                        <label for="username">
                            <i class="fas fa-user"></i>
                            Tên người dùng
                        </label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            placeholder="Nhập tên người dùng"
                            required
                            minlength="3"
                            maxlength="50"
                            autocomplete="username"
                        >
                    </div>

                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i>
                            Email
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="Nhập email của bạn"
                            required
                            autocomplete="email"
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i>
                            Mật khẩu
                        </label>
                        <div class="password-input">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                placeholder="Nhập mật khẩu"
                                required
                                minlength="6"
                                autocomplete="new-password"
                            >
                            <button type="button" class="toggle-password" onclick="togglePassword('password', 'toggleIcon1')">
                                <i class="fas fa-eye" id="toggleIcon1"></i>
                            </button>
                        </div>
                        <small style="color: #666; font-size: 0.85em; margin-top: 4px; display: block;">
                            Mật khẩu phải có ít nhất 6 ký tự
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">
                            <i class="fas fa-lock"></i>
                            Xác nhận mật khẩu
                        </label>
                        <div class="password-input">
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                placeholder="Nhập lại mật khẩu"
                                required
                                minlength="6"
                                autocomplete="new-password"
                            >
                            <button type="button" class="toggle-password" onclick="togglePassword('confirm_password', 'toggleIcon2')">
                                <i class="fas fa-eye" id="toggleIcon2"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="agree_terms" id="agree_terms" required>
                            <span>Tôi đồng ý với <a href="#" style="color: #667eea;">điều khoản sử dụng</a></span>
                        </label>
                    </div>

                    <button type="submit" class="btn-login">
                        <span>Đăng Ký</span>
                        <i class="fas fa-user-plus"></i>
                    </button>

                    <div class="register-link">
                        Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- load external JS -->
    <script src="/src/js/login.js"></script>
    <script src="/src/js/register.js"></script>
</body>
</html>