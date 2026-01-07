/**
 * VKU Cinema - Authentication Modal
 * Xử lý đăng nhập, đăng ký và slideshow
 */

// ==================== AUTH MODAL FUNCTIONS ====================

function openAuthModal(formType) {
    // console.log('=== OPENING AUTH MODAL ===');
    // console.log('Form type:', formType);
    // console.log('Current URL:', window.location.href);
    
    const modal = document.getElementById('authModal');
    const loginContainer = document.getElementById('loginFormContainer');
    const registerContainer = document.getElementById('registerFormContainer');
    const welcomeTitle = document.getElementById('authWelcomeTitle');
    
    if (formType === 'login') {
        loginContainer.style.display = 'block';
        registerContainer.style.display = 'none';
        welcomeTitle.textContent = 'Chào mừng quay trở lại';
    } else {
        loginContainer.style.display = 'none';
        registerContainer.style.display = 'block';
        welcomeTitle.textContent = 'Tham gia cùng chúng tôi';
    }
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    // Initialize slideshow for modal
    initAuthSlideshow();
}

function closeAuthModal() {
    const modal = document.getElementById('authModal');
    modal.classList.remove('active');
    document.body.style.overflow = '';
    
    // Stop slideshow
    if (window.authSlideshowInterval) {
        clearInterval(window.authSlideshowInterval);
    }
    
    // Clear forms and alerts
    document.getElementById('loginForm')?.reset();
    document.getElementById('registerForm')?.reset();
    
    const loginAlert = document.getElementById('loginAlert');
    const registerAlert = document.getElementById('registerAlert');
    if (loginAlert) loginAlert.style.display = 'none';
    if (registerAlert) registerAlert.style.display = 'none';
}

function switchAuthForm(formType) {
    const loginContainer = document.getElementById('loginFormContainer');
    const registerContainer = document.getElementById('registerFormContainer');
    const welcomeTitle = document.getElementById('authWelcomeTitle');
    
    if (formType === 'login') {
        loginContainer.style.display = 'block';
        registerContainer.style.display = 'none';
        welcomeTitle.textContent = 'Chào mừng quay trở lại';
    } else {
        loginContainer.style.display = 'none';
        registerContainer.style.display = 'block';
        welcomeTitle.textContent = 'Tham gia cùng chúng tôi';
    }
    
    // Clear alerts
    const loginAlert = document.getElementById('loginAlert');
    const registerAlert = document.getElementById('registerAlert');
    if (loginAlert) loginAlert.style.display = 'none';
    if (registerAlert) registerAlert.style.display = 'none';
}

// ==================== AUTH SLIDESHOW ====================

function initAuthSlideshow() {
    const posters = [
        '/src/img/posters/1.jpg',
        '/src/img/posters/2.jpg',
        '/src/img/posters/3.jpg',
        '/src/img/posters/4.jpg',
        '/src/img/posters/5.jpg'
    ];
    
    let currentIndex = 0;
    const currentImg = document.getElementById('auth-slideshow-current');
    const nextImg = document.getElementById('auth-slideshow-next');
    const dotsContainer = document.getElementById('auth-slideshow-dots');
    
    if (!currentImg || !nextImg || !dotsContainer) return;
    
    // Create dots
    dotsContainer.innerHTML = '';
    posters.forEach((_, index) => {
        const dot = document.createElement('div');
        dot.className = 'dot' + (index === 0 ? ' active' : '');
        dot.addEventListener('click', () => goToSlide(index));
        dotsContainer.appendChild(dot);
    });
    
    function updateDots() {
        const dots = dotsContainer.querySelectorAll('.dot');
        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === currentIndex);
        });
    }
    
    function goToSlide(index) {
        if (index === currentIndex) return;
        
        currentIndex = index;
        nextImg.src = posters[currentIndex];
        
        currentImg.style.opacity = '0';
        nextImg.style.opacity = '1';
        
        setTimeout(() => {
            currentImg.src = posters[currentIndex];
            currentImg.style.opacity = '1';
            nextImg.style.opacity = '0';
            updateDots();
        }, 1000);
    }
    
    function nextSlide() {
        const nextIndex = (currentIndex + 1) % posters.length;
        goToSlide(nextIndex);
    }
    
    // Auto slide every 4 seconds
    if (window.authSlideshowInterval) {
        clearInterval(window.authSlideshowInterval);
    }
    window.authSlideshowInterval = setInterval(nextSlide, 4000);
}

// ==================== PASSWORD TOGGLE ====================

function togglePasswordModal(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    
    if (!input || !icon) return;
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// ==================== ALERT HELPER ====================

function showAlert(alertId, message, type) {
    const alert = document.getElementById(alertId);
    if (!alert) return;
    
    alert.className = 'alert alert-' + type;
    alert.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + '"></i><span>' + message + '</span>';
    alert.style.display = 'flex';
}

// ==================== LOGIN FORM HANDLER ====================

document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(loginForm);
            
            // Show loading state
            const submitBtn = loginForm.querySelector('.btn-submit');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span>Đang xử lý...</span>';
            submitBtn.disabled = true;
            
            fetch('/src/controllers/loginControllerAjax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    
                    // Restore button
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    
                    if (data.success) {
                        showAlert('loginAlert', 'Đăng nhập thành công! Đang chuyển hướng...', 'success');
                        
                        // Debug log
                        // console.log('=== LOGIN SUCCESS ===');
                        // console.log('Response data:', data);
                        // console.log('Redirect URL:', data.redirect);
                        // console.log('Current URL:', window.location.href);
                        
                        // Dispatch loginSuccess event for member page
                        window.dispatchEvent(new Event('loginSuccess'));
                        
                        setTimeout(() => {
                            // Nếu đang ở trang member, không redirect mà chỉ đóng modal
                            if (window.location.href.includes('page=member')) {
                                // console.log('On member page, just close modal and reload content');
                                closeAuthModal();
                                // Member page sẽ tự động reload content qua event listener
                            } else if (data.redirect) {
                                // console.log('Redirecting to:', data.redirect);
                                window.location.href = data.redirect;
                            } else {
                                // console.log('No redirect URL, reloading page');
                                window.location.reload();
                            }
                        }, 1000);
                    } else {
                        showAlert('loginAlert', data.message || 'Đăng nhập thất bại!', 'error');
                    }
                } catch (e) {
                    // console.error('JSON Parse Error:', e);
                    // console.error('Response text:', text);
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    showAlert('loginAlert', 'Lỗi xử lý dữ liệu. Vui lòng kiểm tra console!', 'error');
                }
            })
            .catch(error => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                showAlert('loginAlert', 'Đã xảy ra lỗi kết nối. Vui lòng thử lại!', 'error');
                // console.error('Error:', error);
            });
        });
    }
});

// ==================== REGISTER FORM HANDLER ====================

document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('registerForm');
    
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const password = document.getElementById('register-password').value;
            const confirmPassword = document.getElementById('register-confirm-password').value;
            
            if (password !== confirmPassword) {
                showAlert('registerAlert', 'Mật khẩu xác nhận không khớp!', 'error');
                return;
            }
            
            const formData = new FormData(registerForm);
            
            // Show loading state
            const submitBtn = registerForm.querySelector('.btn-submit');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span>Đang xử lý...</span>';
            submitBtn.disabled = true;
            
            fetch('/src/controllers/registerControllerAjax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    
                    // Restore button
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    
                    if (data.success) {
                        showAlert('registerAlert', 'Đăng ký thành công! Đang chuyển sang đăng nhập...', 'success');
                        setTimeout(() => {
                            switchAuthForm('login');
                        }, 2000);
                    } else {
                        showAlert('registerAlert', data.message || 'Đăng ký thất bại!', 'error');
                    }
                } catch (e) {
                    // console.error('JSON Parse Error:', e);
                    // console.error('Response text:', text);
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    showAlert('registerAlert', 'Lỗi xử lý dữ liệu. Vui lòng kiểm tra console!', 'error');
                }
            })
            .catch(error => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                showAlert('registerAlert', 'Đã xảy ra lỗi kết nối. Vui lòng thử lại!', 'error');
                // console.error('Error:', error);
            });
        });
    }
});

// ==================== MODAL CLOSE EVENTS ====================

document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('authModal');
    const overlay = document.querySelector('.auth-modal-overlay');
    
    // Close modal with overlay click
    if (overlay) {
        overlay.addEventListener('click', closeAuthModal);
    }
    
    // Close modal with ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && modal.classList.contains('active')) {
            closeAuthModal();
        }
    });
});

// ==================== EXPORT TO GLOBAL SCOPE ====================

window.openAuthModal = openAuthModal;
window.closeAuthModal = closeAuthModal;
window.switchAuthForm = switchAuthForm;
window.togglePasswordModal = togglePasswordModal;
