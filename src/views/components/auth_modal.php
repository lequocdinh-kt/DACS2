<!-- Auth Modal Component -->
<div id="authModal" class="auth-modal">
    <div class="auth-modal-overlay" onclick="closeAuthModal()"></div>
    <div class="auth-modal-content">
        <button class="auth-modal-close" onclick="closeAuthModal()">
            <i class="fas fa-times"></i>
        </button>
        
        <div class="auth-modal-container">
            <!-- Left Section - Slideshow -->
            <div class="auth-left-section">
                <div class="welcome-msg">
                    <h1 id="authWelcomeTitle">Chào mừng quay trở lại</h1>
                    <p class="brand">
                        <span class="brand-v">V</span><span class="brand-k">K</span><span class="brand-u">U</span> Cinema
                    </p>
                </div>

                <div class="slideshow-outer">
                    <div class="slideshow-frame">
                        <img id="auth-slideshow-current" class="slide-img" src="/src/img/posters/1.jpg" alt="Poster current" draggable="false" />
                        <img id="auth-slideshow-next" class="slide-img slide-next" src="/src/img/posters/2.jpg" alt="Poster next" draggable="false" />
                    </div>
                    <div class="slideshow-dots" id="auth-slideshow-dots"></div>
                </div>
            </div>

            <!-- Right Section - Forms -->
            <div class="auth-right-section">
                <!-- Login Form -->
                <div id="loginFormContainer" class="auth-form-container">
                    <div class="auth-header">
                        <h2>Đăng Nhập</h2>
                        <p>Nhập thông tin của bạn để tiếp tục</p>
                    </div>

                    <div id="loginAlert" class="alert" style="display: none;"></div>

                    <form id="loginForm" class="auth-form">
                        <div class="form-group">
                            <label for="login-email">
                                <i class="fas fa-envelope"></i>
                                Email
                            </label>
                            <input 
                                type="email" 
                                id="login-email" 
                                name="email" 
                                placeholder="Nhập email của bạn"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="login-password">
                                <i class="fas fa-lock"></i>
                                Mật khẩu
                            </label>
                            <div class="password-input">
                                <input 
                                    type="password" 
                                    id="login-password" 
                                    name="password" 
                                    placeholder="Nhập mật khẩu"
                                    required
                                >
                                <button type="button" class="toggle-password" onclick="togglePasswordModal('login-password', 'loginToggleIcon')">
                                    <i class="fas fa-eye" id="loginToggleIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-options">
                            <label class="remember-me">
                                <input type="checkbox" name="remember">
                                <span>Ghi nhớ đăng nhập</span>
                            </label>
                            <a href="#" class="forgot-password">Quên mật khẩu?</a>
                        </div>

                        <button type="submit" class="btn-submit">
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

                        <div class="switch-form">
                            Chưa có tài khoản? <a href="javascript:void(0)" onclick="switchAuthForm('register')">Đăng ký ngay</a>
                        </div>
                    </form>
                </div>

                <!-- Register Form -->
                <div id="registerFormContainer" class="auth-form-container" style="display: none;">
                    <div class="auth-header">
                        <h2>Đăng Ký</h2>
                        <p>Tạo tài khoản mới để bắt đầu</p>
                    </div>
                    
                    <div id="registerAlert" class="alert" style="display: none;"></div>

                    <form id="registerForm" class="auth-form">
                        <div class="form-group">
                            <label for="register-username">
                                <i class="fas fa-user"></i>
                                Tên người dùng
                            </label>
                            <input 
                                type="text" 
                                id="register-username" 
                                name="username" 
                                placeholder="Nhập tên người dùng"
                                required
                                minlength="3"
                                maxlength="50"
                            >
                        </div>

                        <div class="form-group">
                            <label for="register-email">
                                <i class="fas fa-envelope"></i>
                                Email
                            </label>
                            <input 
                                type="email" 
                                id="register-email" 
                                name="email" 
                                placeholder="Nhập email của bạn"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="register-password">
                                <i class="fas fa-lock"></i>
                                Mật khẩu
                            </label>
                            <div class="password-input">
                                <input 
                                    type="password" 
                                    id="register-password" 
                                    name="password" 
                                    placeholder="Nhập mật khẩu"
                                    required
                                    minlength="6"
                                >
                                <button type="button" class="toggle-password" onclick="togglePasswordModal('register-password', 'registerToggleIcon1')">
                                    <i class="fas fa-eye" id="registerToggleIcon1"></i>
                                </button>
                            </div>
                            <small style="color: #666; font-size: 0.85em; margin-top: 4px; display: block;">
                                Mật khẩu phải có ít nhất 6 ký tự
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="register-confirm-password">
                                <i class="fas fa-lock"></i>
                                Xác nhận mật khẩu
                            </label>
                            <div class="password-input">
                                <input 
                                    type="password" 
                                    id="register-confirm-password" 
                                    name="confirm_password" 
                                    placeholder="Nhập lại mật khẩu"
                                    required
                                    minlength="6"
                                >
                                <button type="button" class="toggle-password" onclick="togglePasswordModal('register-confirm-password', 'registerToggleIcon2')">
                                    <i class="fas fa-eye" id="registerToggleIcon2"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-options">
                            <label class="remember-me">
                                <input type="checkbox" name="agree_terms" required>
                                <span>Tôi đồng ý với <a href="#" style="color: #667eea;">điều khoản sử dụng</a></span>
                            </label>
                        </div>

                        <button type="submit" class="btn-submit">
                            <span>Đăng Ký</span>
                            <i class="fas fa-user-plus"></i>
                        </button>

                        <div class="switch-form">
                            Đã có tài khoản? <a href="javascript:void(0)" onclick="switchAuthForm('login')">Đăng nhập ngay</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
