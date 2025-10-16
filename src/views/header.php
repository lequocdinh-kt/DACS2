<!-- Header Section -->
<header class="main-header">
    <div class="header-container">
        <!-- Logo bên trái -->
        <div class="header-logo">
            <a href="index.php">
                <img src="../img/logovku.png" alt="VKU Cinema Logo">
               <div class="header-logo">
                <h2>
                    <span class="logo-v">V</span></span><span class="logo-k">K</span><span class="logo-u">U</span>
                </h2>
                <p>CINEMA</p>
            </div>
            </a>
        </div>

        <!-- Menu chính giữa -->
        <nav class="header-nav">
            <ul class="nav-menu">
                <li><a href="index.php">LỊCH CHIẾU</a></li>
                <li><a href="movies.php">PHIM</a></li>
                <li><a href="deals.php">ƯU ĐÃI</a></li>
                <li><a href="news.php">TIN TỨC PHIM</a></li>
                <li><a href="member.php">THÀNH VIÊN</a></li>
            </ul>
        </nav>

        <!-- Thông tin liên hệ + Đăng nhập/Đăng ký bên phải -->
        <div class="header-right">
            <div class="header-info">
                <div class="hotline">
                    <i class="fas fa-phone-alt"></i>
                    <span>HOTLINE: 0236 3630 689</span>
                </div>
                <div class="opening-hours">
                    <i class="fas fa-clock"></i>
                    <span>GIỜ MỞ CỬA: 9:00 - 22:00</span>
                </div>
            </div>
            <div class="header-auth">
                <?php if(isset($_SESSION['userID'])): ?>
                    <a href="profile.php" class="user-profile">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo htmlspecialchars($_SESSION['user']['username'] ?? 'User'); ?></span>
                    </a>
                    <a href="../controllers/logoutController.php" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i>
                        ĐĂNG XUẤT
                    </a>
                <?php else: ?>
                    <a href="login.php" class="auth-link">ĐĂNG NHẬP</a>
                    <span class="auth-separator">/</span>
                    <a href="register.php" class="auth-link">ĐĂNG KÝ</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" id="mobileMenuToggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <div class="mobile-menu-header">
            <h3>Menu</h3>
            <button class="mobile-menu-close" id="mobileMenuClose">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <ul class="mobile-nav-menu">
            <li><a href="index.php">LỊCH CHIẾU</a></li>
            <li><a href="movies.php">PHIM</a></li>
            <li><a href="deals.php">ƯU ĐÃI</a></li>
            <li><a href="news.php">TIN TỨC PHIM</a></li>
            <li><a href="member.php">THÀNH VIÊN</a></li>
        </ul>
        <div class="mobile-menu-footer">
            <div class="mobile-info">
                <p><i class="fas fa-phone-alt"></i> HOTLINE: 0236 3630 689</p>
                <p><i class="fas fa-clock"></i> GIỜ MỞ CỬA: 9:00 - 22:00</p>
            </div>
            <?php if(isset($_SESSION['userID'])): ?>
                <a href="profile.php" class="mobile-user">
                    <i class="fas fa-user-circle"></i>
                    <?php echo htmlspecialchars($_SESSION['user']['username'] ?? 'User'); ?>
                </a>
                <a href="../controllers/logoutController.php" class="mobile-logout">
                    <i class="fas fa-sign-out-alt"></i> ĐĂNG XUẤT
                </a>
            <?php else: ?>
                <div class="mobile-auth">
                    <a href="login.php">ĐĂNG NHẬP</a>
                    <a href="register.php">ĐĂNG KÝ</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>
<script src="../js/header.js"></script>