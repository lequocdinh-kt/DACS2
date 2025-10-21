// Header Mobile Menu Toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const mobileMenu = document.getElementById('mobileMenu');
    const mobileMenuClose = document.getElementById('mobileMenuClose');

    if (mobileMenuToggle && mobileMenu) {
        mobileMenuToggle.addEventListener('click', function() {
            mobileMenu.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }

    if (mobileMenuClose && mobileMenu) {
        mobileMenuClose.addEventListener('click', function() {
            mobileMenu.classList.remove('active');
            document.body.style.overflow = '';
        });
    }

    // Click outside to close
    mobileMenu?.addEventListener('click', function(e) {
        if (e.target === mobileMenu) {
            mobileMenu.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
});

// Auth Modal Functions
function openAuthModal(formType) {
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
    document.getElementById('loginForm').reset();
    document.getElementById('registerForm').reset();
    document.getElementById('loginAlert').style.display = 'none';
    document.getElementById('registerAlert').style.display = 'none';
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
    document.getElementById('loginAlert').style.display = 'none';
    document.getElementById('registerAlert').style.display = 'none';
}

// Auth Slideshow Functions
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


function togglePasswordModal(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    
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

function showAlert(alertId, message, type) {
    const alert = document.getElementById(alertId);
    alert.className = 'alert alert-' + type;
    alert.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + '"></i><span>' + message + '</span>';
    alert.style.display = 'flex';
}

// Handle Login Form Submit
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
                // Check if response is ok
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text(); // Get as text first
            })
            .then(text => {
                // Try to parse as JSON
                try {
                    const data = JSON.parse(text);
                    
                    // Restore button
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    
                    if (data.success) {
                        showAlert('loginAlert', data.message, 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showAlert('loginAlert', data.message, 'error');
                    }
                } catch (e) {
                    console.error('JSON Parse Error:', e);
                    console.error('Response text:', text);
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    showAlert('loginAlert', 'Lỗi xử lý dữ liệu. Vui lòng kiểm tra console!', 'error');
                }
            })
            .catch(error => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                showAlert('loginAlert', 'Đã xảy ra lỗi kết nối. Vui lòng thử lại!', 'error');
                console.error('Error:', error);
            });
        });
    }
});

// Handle Register Form Submit
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
                        showAlert('registerAlert', data.message, 'success');
                        setTimeout(() => {
                            switchAuthForm('login');
                            registerForm.reset();
                        }, 1500);
                    } else {
                        showAlert('registerAlert', data.message, 'error');
                    }
                } catch (e) {
                    console.error('JSON Parse Error:', e);
                    console.error('Response text:', text);
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    showAlert('registerAlert', 'Lỗi xử lý dữ liệu. Vui lòng kiểm tra console!', 'error');
                }
            })
            .catch(error => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                showAlert('registerAlert', 'Đã xảy ra lỗi kết nối. Vui lòng thử lại!', 'error');
                console.error('Error:', error);
            });
        });
    }
});

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('authModal');
    const overlay = document.querySelector('.auth-modal-overlay');
    
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
