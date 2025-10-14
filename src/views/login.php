<?php
// Bắt đầu phiên làm việc để dùng $_SESSION
session_start();

// Tạo CSRF token nếu chưa có trong session
// CSRF token dùng để chống tấn công CSRF: chèn vào form và kiểm tra khi submit
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(24)); // random_bytes tạo dữ liệu ngẫu nhiên an toàn -> bin2hex mã hóa thành chuỗi hex
}

// Lấy giá trị input 'identity' (số điện thoại hoặc email) từ POST nếu có
// htmlspecialchars để tránh XSS khi hiển thị lại vào ô input
$identityValue = htmlspecialchars($_POST['identity'] ?? '', ENT_QUOTES, 'UTF-8');

// Lấy lỗi đăng nhập (nếu backend lưu vào session trước đó) và escape trước khi hiển thị
$loginError = htmlspecialchars($_SESSION['login_error'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Đăng nhập</title>
    <!-- Link tới file CSS (đường dẫn tương đối) -->
    <link rel="stylesheet" href="../styles/login.css">
</head>

<body>
    <main class="login-container">
        <aside class="brand">
            <div class="brand-inner">
                <div class="brand-icon" aria-hidden="true">
                    <!--
                        Biểu tượng / hình ảnh cinema:
                        - SVG hiện tại là hình mẫu (có thể thay bằng <img src="...">).
                        - fill="#ffffff" có thể đổi để hiển thị tốt trên nền tối.
                    -->
                    <svg fill="#ffffff" height="80px" width="80px" version="1.1" id="Icons" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                        viewBox="0 0 32 32" xml:space="preserve" role="img" aria-label="cinema icon">
                        <g>
                            <path d="M26,16H6c-1.7,0-3-1.3-3-3s1.3-3,3-3h20c1.7,0,3,1.3,3,3S27.7,16,26,16z" />
                        </g>
                        <path d="M26.7,14.3C26.6,14.1,26.3,14,26,14H6c-0.3,0-0.6,0.1-0.7,0.3C5.1,14.6,5,14.8,5,15.1l2,16C7.1,31.6,7.5,32,8,32h5
                            c-0.5,0-1-0.4-1-0.9l-1-14c0-0.6,0.4-1,0.9-1.1c0.6,0,1,0.4,1.1,0.9l1,14c0,0.6-0.4,1-0.9,1.1c0,0,0,0-0.1,0h6c0,0,0,0-0.1,0
                            c-0.6,0-1-0.5-0.9-1.1l1-14c0-0.6,0.5-1,1.1-0.9c0.6,0,1,0.5,0.9,1.1l-1,14c0,0.5-0.5,0.9-1,0.9h5c0.5,0,0.9-0.4,1-0.9l2-16
                            C27,14.8,26.9,14.6,26.7,14.3z" />
                        <g>
                            <path d="M25.8,12L25.8,12L6.2,12c-0.4,0-0.8-0.3-0.9-0.7C5.1,10.9,5,10.5,5,10c0-1.5,0.8-2.8,2-3.5C7,6.4,7,6.2,7,6
                                c0-2.2,1.8-4,4-4c0.5,0,1,0.1,1.4,0.3C13.1,0.9,14.4,0,16,0s2.9,0.9,3.6,2.3C20,2.1,20.5,2,21,2c2.2,0,4,1.8,4,4c0,0.2,0,0.4,0,0.5
                                c1.2,0.7,2,2,2,3.5c0,0.5-0.1,0.9-0.2,1.3C26.6,11.7,26.3,12,25.8,12z M7,10l18,0c0,0,0,0,0,0c0-0.9-0.6-1.7-1.5-1.9
                                C23.2,8,23,7.8,22.9,7.6c-0.1-0.3-0.1-0.6,0-0.8C23,6.5,23,6.2,23,6c0-1.1-0.9-2-2-2c-0.5,0-1,0.2-1.3,0.5c-0.3,0.3-0.7,0.3-1,0.2
                                C18.3,4.6,18,4.2,18,3.9C17.9,2.8,17,2,16,2s-1.9,0.8-2,1.9c0,0.4-0.3,0.7-0.6,0.9c-0.4,0.1-0.8,0.1-1-0.2C12,4.2,11.5,4,11,4
                                C9.9,4,9,4.9,9,6c0,0.2,0,0.5,0.1,0.7c0.1,0.3,0.1,0.6,0,0.8C9,7.8,8.8,8,8.5,8.1C7.6,8.3,7,9.1,7,10L7,10z" />
                        </g>
                    </svg>
                </div>

                <div class="brand-content">
                    <!--
                        Tiêu đề lớn VKU Cinema
                        - Các chữ V,K,U dùng span để đổi màu riêng (theo logo VKU)
                        - .cinema-text là chữ "Cinema" màu trắng
                    -->
                    <h2 class="brand-title">
                        <span class="vku v">V</span><span class="vku k">K</span><span class="vku u">U</span>
                        <span class="cinema-text">Cinema</span>
                    </h2>
                    <p class="brand-sub">Đặt vé nhanh — Trải nghiệm tốt</p>
                </div>
            </div>

            <!--
                Form được đặt trong .form-card (góc phải của brand)
                Khi submit, form gửi POST về cùng trang (action="") — backend cần kiểm tra CSRF token và dữ liệu
            -->
            <section class="form-card">
                <form class="login-form" method="post" action="">
                    <h1>Đăng nhập</h1>
                    <p class="lead">Sử dụng số điện thoại hoặc email và mật khẩu để tiếp tục</p>

                    <?php if ($loginError): ?>
                        <!-- Hiển thị lỗi từ backend nếu có -->
                        <div class="alert alert-error"><?php echo $loginError; ?></div>
                    <?php endif; ?>

                    <div class="form-field">
                        <input id="identity" name="identity" type="text" inputmode="email" autocomplete="username" placeholder=" " value="<?php echo $identityValue; ?>" required>
                        <label class="floating" for="identity">Số điện thoại hoặc Email</label>
                    </div>

                    <div class="form-field pwd-field">
                        <input id="password" name="password" type="password" autocomplete="current-password" placeholder=" " required>
                        <label class="floating" for="password">Mật khẩu</label>
                        <button type="button" id="togglePassword" class="show-pass" aria-label="Hiện mật khẩu" aria-pressed="false">
                            <!-- eye-closed (mặc định) -->
                            <svg class="eye-close" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                                <path d="M73 39.1C63.6 29.7 48.4 29.7 39.1 39.1C29.8 48.5 29.7 63.7 39 73.1L567 601.1C576.4 610.5 591.6 610.5 600.9 601.1C610.2 591.7 610.3 576.5 600.9 567.2L504.5 470.8C507.2 468.4 509.9 466 512.5 463.6C559.3 420.1 590.6 368.2 605.5 332.5C608.8 324.6 608.8 315.8 605.5 307.9C590.6 272.2 559.3 220.2 512.5 176.8C465.4 133.1 400.7 96.2 319.9 96.2C263.1 96.2 214.3 114.4 173.9 140.4L73 39.1zM236.5 202.7C260 185.9 288.9 176 320 176C399.5 176 464 240.5 464 320C464 351.1 454.1 379.9 437.3 403.5L402.6 368.8C415.3 347.4 419.6 321.1 412.7 295.1C399 243.9 346.3 213.5 295.1 227.2C286.5 229.5 278.4 232.9 271.1 237.2L236.4 202.5zM357.3 459.1C345.4 462.3 332.9 464 320 464C240.5 464 176 399.5 176 320C176 307.1 177.7 294.6 180.9 282.7L101.4 203.2C68.8 240 46.4 279 34.5 307.7C31.2 315.6 31.2 324.4 34.5 332.3C49.4 368 80.7 420 127.5 463.4C174.6 507.1 239.3 544 320.1 544C357.4 544 391.3 536.1 421.6 523.4L357.4 459.2z" />
                            </svg>

                            <!-- eye-open (hiện) -->
                            <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" preserveAspectRatio="xMidYMid meet" width="20" height="20" aria-hidden="true" style="display:none">
                                <path d="M320 96C239.2 96 174.5 132.8 127.4 176.6C80.6 220.1 49.3 272 34.4 307.7C31.1 315.6 31.1 324.4 34.4 332.3C49.3 368 80.6 420 127.4 463.4C174.5 507.1 239.2 544 320 544C400.8 544 465.5 507.2 512.6 463.4C559.4 419.9 590.7 368 605.6 332.3C608.9 324.4 608.9 315.6 605.6 307.7C590.7 272 559.4 220 512.6 176.6C465.5 132.9 400.8 96 320 96zM176 320C176 240.5 240.5 176 320 176C399.5 176 464 240.5 464 320C464 399.5 399.5 464 320 464C240.5 464 176 399.5 176 320zM320 256C320 291.3 291.3 320 256 320C244.5 320 233.7 317 224.3 311.6C223.3 322.5 224.2 333.7 227.2 344.8C240.9 396 293.6 426.4 344.8 412.7C396 399 426.4 346.3 412.7 295.1C400.5 249.4 357.2 220.3 311.6 224.3C316.9 233.6 320 244.4 320 256z" fill="currentColor" />
                            </svg>
                        </button>
                    </div>

                    <div class="misc">
                        <label class="remember"><input type="checkbox" name="remember"> Ghi nhớ đăng nhập</label>
                        <a class="forgot" href="/forgot.php">Quên mật khẩu?</a>
                    </div>

                    <!-- Chèn CSRF token vào form để backend xác thực -->
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES); ?>">

                    <button class="btn primary" type="submit">Đăng nhập</button>

                    <div class="divider"><span>hoặc</span></div>

                    <p class="signup">Chưa có tài khoản? <a href="/register.php">Đăng ký</a></p>
                </form>
            </section>
        </aside>
    </main>

    <script>
        // xử lý nút hiện/ẩn mật khẩu
        (function() {
            const toggleBtn = document.getElementById('togglePassword');
            const pwd = document.getElementById('password');
            // nếu không tìm thấy phần tử thì dừng, tránh lỗi JS
            if (!toggleBtn || !pwd) return;

            toggleBtn.addEventListener('click', () => {
                // toggle class 'is-open' để CSS/JS biết trạng thái
                const opened = toggleBtn.classList.toggle('is-open');

                // đổi kiểu input giữa 'text' và 'password' để hiện/ẩn mật khẩu
                pwd.type = opened ? 'text' : 'password';

                // cập nhật aria để hỗ trợ trợ năng (screen reader)
                toggleBtn.setAttribute('aria-label', opened ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
                toggleBtn.setAttribute('aria-pressed', opened ? 'true' : 'false');

                // thay đổi hiển thị 2 SVG (eye-open / eye-close)
                const eyeOpen = toggleBtn.querySelector('.eye-open');
                const eyeClose = toggleBtn.querySelector('.eye-close');
                if (eyeOpen && eyeClose) {
                    eyeOpen.style.display = opened ? 'block' : 'none';
                    eyeClose.style.display = opened ? 'none' : 'block';
                }
            });
        })();

        // sự kiện input cho ô identity (có thể thêm kiểm tra client-side)
        const identity = document.getElementById('identity');
        identity && identity.addEventListener('input', () => {
            // placeholder: xử lý gợi ý hoặc validate nhanh nếu cần
        });
    </script>
</body>

</html>