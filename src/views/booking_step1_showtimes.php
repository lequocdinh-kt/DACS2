<?php
/**
 * BƯỚC 1: Chọn suất chiếu
 */
session_start();

// Lấy movieID
$movieID = $_GET['movieID'] ?? null;
if (!$movieID) {
    header('Location: /');
    exit();
}

// Kiểm tra đăng nhập - nếu chưa đăng nhập sẽ hiển thị modal
$requireLogin = !isset($_SESSION['userID']);

require_once __DIR__ . '/../models/movie_db.php';
require_once __DIR__ . '/../models/showtime_db.php';

$movie = get_movie_by_id($movieID);
if (!$movie) {
    header('Location: /');
    exit();
}

$dates = get_available_dates_by_movie($movieID, 7);
$selectedDate = $_GET['date'] ?? ($dates[0] ?? date('Y-m-d'));
$showtimes = get_showtimes_by_movie($movieID, $selectedDate);

// Helper functions
function format_date_vn($date) {
    $d = new DateTime($date);
    $today = new DateTime();
    $tomorrow = new DateTime('+1 day');
    
    if ($d->format('Y-m-d') === $today->format('Y-m-d')) {
        return 'Hôm nay, ' . $d->format('d/m');
    } else if ($d->format('Y-m-d') === $tomorrow->format('Y-m-d')) {
        return 'Ngày mai, ' . $d->format('d/m');
    }
    
    $days = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
    return $days[$d->format('w')] . ', ' . $d->format('d/m');
}

function format_time($time) {
    return date('H:i', strtotime($time));
}

function get_room_type_label($type) {
    $labels = [
        'standard' => 'Phòng thường',
        'vip' => 'Phòng VIP',
        'imax' => 'Phòng IMAX'
    ];
    return $labels[$type] ?? 'Phòng chiếu';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chọn suất chiếu - <?php echo htmlspecialchars($movie['title']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/src/styles/booking_showtimes.css">
    <?php if ($requireLogin): ?>
    <link rel="stylesheet" href="/src/styles/login.css">
    <?php endif; ?>
</head>
<body>
    <?php if ($requireLogin): ?>
    <!-- Login Modal -->
    <div id="loginModal" class="modal active">
        <div class="modal-content">
            <span class="close" onclick="window.location.href='/';">&times;</span>
            <h2>Đăng nhập để đặt vé</h2>
            <p style="text-align: center; color: #666; margin-bottom: 20px;">
                Vui lòng đăng nhập để tiếp tục đặt vé xem phim
            </p>
            <form id="loginForm" action="/src/controllers/loginControllerAjax.php" method="POST">
                <div class="input-group">
                    <label for="username">Tên đăng nhập hoặc Email</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="input-group">
                    <label for="password">Mật khẩu</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <input type="hidden" name="redirect_url" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                <button type="submit" class="btn-submit">Đăng nhập</button>
            </form>
            <div class="form-footer">
                <p>Chưa có tài khoản? <a href="#" onclick="openRegisterModal(); return false;">Đăng ký ngay</a></p>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div id="registerModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeRegisterModal()">&times;</span>
            <h2>Đăng ký tài khoản</h2>
            <form id="registerForm" action="/src/controllers/registerControllerAjax.php" method="POST">
                <div class="input-group">
                    <label for="reg_fullName">Họ và tên</label>
                    <input type="text" id="reg_fullName" name="fullName" required>
                </div>
                <div class="input-group">
                    <label for="reg_email">Email</label>
                    <input type="email" id="reg_email" name="email" required>
                </div>
                <div class="input-group">
                    <label for="reg_username">Tên đăng nhập</label>
                    <input type="text" id="reg_username" name="username" required>
                </div>
                <div class="input-group">
                    <label for="reg_password">Mật khẩu</label>
                    <input type="password" id="reg_password" name="password" required>
                </div>
                <div class="input-group">
                    <label for="reg_confirmPassword">Xác nhận mật khẩu</label>
                    <input type="password" id="reg_confirmPassword" name="confirmPassword" required>
                </div>
                <button type="submit" class="btn-submit">Đăng ký</button>
            </form>
            <div class="form-footer">
                <p>Đã có tài khoản? <a href="#" onclick="closeRegisterModal(); return false;">Đăng nhập</a></p>
            </div>
        </div>
    </div>
    <script src="/src/js/login.js"></script>
    <?php endif; ?>
    
    <div class="booking-container"<?php if ($requireLogin): ?> style="filter: blur(5px); pointer-events: none;"<?php endif; ?>>
        <!-- Header -->
        <div class="booking-header">
            <a href="javascript:history.back()" class="btn-back">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <h1>Đặt vé xem phim</h1>
        </div>

        <!-- Movie Info -->
        <div class="movie-info-card">
            <img src="<?php echo htmlspecialchars($movie['posterURL']); ?>" 
                 alt="<?php echo htmlspecialchars($movie['title']); ?>" 
                 class="movie-poster">
            <div class="movie-details">
                <h2><?php echo htmlspecialchars($movie['title']); ?></h2>
                <div class="movie-meta">
                    <span><i class="fas fa-clock"></i> <?php echo $movie['duration']; ?> phút</span>
                    <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($movie['genre']); ?></span>
                    <?php if (!empty($movie['ageRating'])): ?>
                        <span class="age-rating"><?php echo $movie['ageRating']; ?>+</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Progress Steps -->
        <div class="booking-steps">
            <div class="step active">
                <div class="step-number">1</div>
                <div class="step-label">Chọn suất</div>
            </div>
            <div class="step-line"></div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-label">Chọn ghế</div>
            </div>
            <div class="step-line"></div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-label">Thanh toán</div>
            </div>
            <div class="step-line"></div>
            <div class="step">
                <div class="step-number">4</div>
                <div class="step-label">Xác nhận</div>
            </div>
        </div>

        <!-- Date Selector -->
        <div class="section">
            <h3><i class="fas fa-calendar-alt"></i> Chọn ngày chiếu</h3>
            <div class="date-selector">
                <?php foreach ($dates as $date): ?>
                    <a href="?movieID=<?php echo $movieID; ?>&date=<?php echo $date; ?>" 
                       class="date-btn <?php echo $date === $selectedDate ? 'active' : ''; ?>">
                        <?php echo format_date_vn($date); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Showtimes -->
        <div class="section">
            <h3><i class="fas fa-film"></i> Chọn suất chiếu</h3>
            
            <?php if (empty($showtimes)): ?>
                <div class="no-showtimes">
                    <i class="fas fa-calendar-times"></i>
                    <p>Không có suất chiếu nào trong ngày này</p>
                    <p class="sub-text">Vui lòng chọn ngày khác</p>
                </div>
            <?php else: ?>
                <div class="showtimes-list">
                    <?php foreach ($showtimes as $showtime): ?>
                        <div class="showtime-card <?php echo $showtime['availableSeats'] == 0 ? 'full' : ''; ?>">
                            <div class="showtime-info">
                                <div class="showtime-time">
                                    <i class="fas fa-clock"></i>
                                    <?php echo format_time($showtime['showTime']); ?>
                                </div>
                                <div class="showtime-room">
                                    <?php echo $showtime['roomName']; ?> - 
                                    <?php echo get_room_type_label($showtime['roomType']); ?>
                                </div>
                                <div class="showtime-seats">
                                    <i class="fas fa-chair"></i>
                                    <?php echo $showtime['availableSeats']; ?>/<?php echo $showtime['totalSeats']; ?> ghế trống
                                </div>
                                <div class="showtime-price">
                                    <?php echo number_format($showtime['basePrice']); ?>đ
                                </div>
                            </div>
                            
                            <?php if ($showtime['availableSeats'] > 0): ?>
                                <a href="/src/views/booking_step2_seats.php?showtimeID=<?php echo $showtime['showtimeID']; ?>" 
                                   class="btn-select-showtime">
                                    <i class="fas fa-arrow-right"></i> Chọn
                                </a>
                            <?php else: ?>
                                <button class="btn-full" disabled>
                                    <i class="fas fa-times"></i> Hết chỗ
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Note -->
        <div class="booking-note">
            <h4><i class="fas fa-info-circle"></i> Lưu ý:</h4>
            <ul>
                <li>Vé đã mua không thể hoàn trả hoặc đổi</li>
                <li>Vui lòng có mặt trước giờ chiếu 15 phút</li>
                <li>Ghế sẽ được giữ trong 15 phút sau khi chọn</li>
                <li>Thanh toán trước khi hết thời gian giữ ghế</li>
            </ul>
        </div>
    </div>

    <?php if ($requireLogin): ?>
    <script>
        function openRegisterModal() {
            document.getElementById('loginModal').classList.remove('active');
            document.getElementById('registerModal').classList.add('active');
        }
        
        function closeRegisterModal() {
            document.getElementById('registerModal').classList.remove('active');
            document.getElementById('loginModal').classList.add('active');
        }
    </script>
    <?php else: ?>
    <script src="/src/js/booking_showtimes.js"></script>
    <?php endif; ?>
</body>
</html>
