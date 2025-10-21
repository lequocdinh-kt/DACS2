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
    <title>Đăng Nhập - Hệ Thống</title>
    <link rel="stylesheet" href="/src/styles/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Phần bên trái - Hình ảnh -->
        <div class="left-section">
            <div class="welcome-msg">
                <h1>Chào mừng quay trở lại</h1>
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

        <!-- Phần bên phải - Form đăng nhập -->
        <div class="right-section">
            <div class="login-box">
                <div class="login-header">
                    <h2>Đăng Nhập</h2>
                    <p>Nhập thông tin của bạn để tiếp tục</p>
                </div>

                <?php if(!empty($_SESSION['flash'])): ?>
                    <?php $flash = $_SESSION['flash']; unset($_SESSION['flash']); ?>
                    <div class="alert <?php echo $flash['type'] === 'success' ? 'alert-success' : 'alert-error'; ?>">
                        <i class="<?php echo $flash['type'] === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'; ?>"></i>
                        <span><?php echo htmlspecialchars($flash['message']); ?></span>
                    </div>
                <?php endif; ?>

                <form action="/src/controllers/loginController.php" method="POST" class="login-form">
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
                                autocomplete="current-password"
                            >
                            <button type="button" class="toggle-password" onclick="togglePassword()">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember" id="remember">
                            <span>Ghi nhớ đăng nhập</span>
                        </label>
                        <a href="forgot_password.php" class="forgot-password">Quên mật khẩu?</a>
                    </div>

                    <button type="submit" class="btn-login">
                        <span>Đăng Nhập</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>

                    <div class="divider">
                        <span>hoặc</span>
                    </div>

                    <div class="social-login">
                        <button type="button" class="btn-social btn-google">
                            <i class="fab fa-google"></i>
                            Google
                        </button>
                        <button type="button" class="btn-social btn-facebook">
                            <i class="fab fa-facebook-f"></i>
                            Facebook
                        </button>
                    </div>

                    <div class="register-link">
                        Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- load external JS -->
    <script src="/src/js/login.js"></script>
</body>
</html>
