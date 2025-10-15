<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - Hệ Thống</title>
    <link rel="stylesheet" href="../styles/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Phần bên trái - Hình ảnh -->
        <div class="left-section">
            <div class="welcome-content">
                <h1>Chào Mừng Trở Lại!</h1>
                <p>Đăng nhập để tiếp tục sử dụng hệ thống</p>
                <div class="illustration">
                    <i class="fas fa-user-shield"></i>
                </div>
            </div>
        </div>

        <!-- Phần bên phải - Form đăng nhập -->
        <div class="right-section">
            <div class="login-box">
                <div class="login-header">
                    <h2>Đăng Nhập</h2>
                    <p>Nhập thông tin của bạn để tiếp tục</p>
                </div>

                <?php if(isset($_GET['error'])): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>Tên đăng nhập h ha ba e oặc mật khẩu không đúng!</span>
                    </div>
                <?php endif; ?>

                <?php if(isset($_GET['success'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span>Đăng ký thành công! Vui lòng đăng nhập.</span>
                    </div>
                <?php endif; ?>

                <form action="../models/process_login.php" method="POST" class="login-form">
                    <div class="form-group">
                        <label for="username">
                            <i class="fas fa-user"></i>
                            Tên đăng nhập
                        </label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            placeholder="Nhập tên đăng nhập"
                            required
                            autocomplete="username"
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

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Animation khi load trang
        window.addEventListener('load', function() {
            document.querySelector('.login-box').classList.add('fade-in');
        });
    </script>
</body>
</html>
