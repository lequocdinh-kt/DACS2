<?php
/**
 * BƯỚC 1: Chọn suất chiếu
 */

// Bắt lỗi toàn bộ file
try {
    session_start();

    // Lấy movieID
    $movieID = $_GET['movieID'] ?? null;
    if (!$movieID) {
        header('Location: /');
        exit();
    }

    // Kiểm tra đăng nhập - nếu chưa đăng nhập sẽ hiển thị modal
    $requireLogin = !isset($_SESSION['userID']);

    // Load config first
    if (!defined('DB_HOST')) {
        require_once __DIR__ . '/../../config.php';
    }

    require_once __DIR__ . '/../models/movie_db.php';
    require_once __DIR__ . '/../models/showtime_db.php';

    $movie = get_movie_by_id($movieID);
    if (!$movie) {
        header('Location: /');
        exit();
    }

    // Tạo 20 ngày từ hôm nay
    $dates = [];
    for ($i = 0; $i < 20; $i++) {
        $dates[] = date('Y-m-d', strtotime("+$i days"));
    }
    
    $selectedDate = $_GET['date'] ?? $dates[0];
    $showtimes = get_showtimes_by_movie($movieID, $selectedDate);
    
    // Group showtimes by room
    $groupedShowtimes = [];
    foreach ($showtimes as $showtime) {
        $roomKey = $showtime['roomName'];
        if (!isset($groupedShowtimes[$roomKey])) {
            $groupedShowtimes[$roomKey] = [
                'roomName' => $showtime['roomName'],
                'roomType' => $showtime['roomType'],
                'times' => []
            ];
        }
        $groupedShowtimes[$roomKey]['times'][] = $showtime;
    }
} catch (Exception $e) {
    error_log("Error in booking_step1_showtimes.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    die("Đã xảy ra lỗi. Vui lòng thử lại sau. (Error: " . $e->getMessage() . ")");
}

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

function format_date_calendar($date) {
    $d = new DateTime($date);
    $daysEn = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    return [
        'dayName' => $daysEn[$d->format('w')],
        'dayNumber' => $d->format('d'),
        'month' => $d->format('m')
    ];
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
            <a href="javascript:history.back()" class="btn-back" title="Quay lại">
                <i class="fas fa-arrow-left"></i><span class="btn-text"> Quay lại</span>
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
            <div class="date-calendar">
                <?php foreach ($dates as $date): ?>
                    <?php $dateInfo = format_date_calendar($date); ?>
                    <a href="?movieID=<?php echo $movieID; ?>&date=<?php echo $date; ?>" 
                       class="date-btn <?php echo $date === $selectedDate ? 'active' : ''; ?>">
                        <span class="day-name"><?php echo $dateInfo['dayName']; ?></span>
                        <span class="day-number"><?php echo $dateInfo['dayNumber']; ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Room Type Filter -->
        <div class="section">
            <h3><i class="fas fa-filter"></i> Loại phòng chiếu</h3>
            <div class="room-filter">
                <button class="filter-btn active" data-type="all">Tất cả</button>
                <button class="filter-btn" data-type="2D">2D</button>
                <button class="filter-btn" data-type="3D">3D</button>
                <button class="filter-btn" data-type="IMAX">IMAX</button>
            </div>
        </div>

        <!-- Showtimes -->
        <div class="section">
            <h3><i class="fas fa-film"></i> Chọn suất chiếu</h3>
            
            <?php if (empty($showtimes)): ?>
                <div class="no-showtimes">
                    <i class="fas fa-calendar-times"></i>
                    <p>Không có suất chiếu nào trong ngày <strong><?php echo format_date_vn($selectedDate); ?></strong></p>
                    <p class="sub-text">Rạp chưa lên lịch chiếu cho ngày này. Vui lòng chọn ngày khác hoặc quay lại sau.</p>
                </div>
            <?php else: ?>
                <div class="showtimes-by-cinema">
                    <div class="cinema-block">
                        <h4 class="cinema-name">
                            <i class="fas fa-map-marker-alt"></i> VKU Cinema Complex
                        </h4>
                        
                        <?php foreach ($groupedShowtimes as $room): ?>
                            <div class="room-showtimes" data-room-type="<?php echo strtoupper($room['roomType']); ?>">
                                <div class="room-label">
                                    <i class="fas fa-door-open"></i>
                                    <?php echo $room['roomName']; ?> - 
                                    <span class="room-type-badge"><?php echo strtoupper($room['roomType']); ?></span>
                                </div>
                                <div class="time-slots">
                                    <?php foreach ($room['times'] as $time): ?>
                                        <?php if ($time['availableSeats'] > 0): ?>
                                            <a href="/src/views/booking_step2_seats.php?showtimeID=<?php echo $time['showtimeID']; ?>" 
                                               class="time-btn">
                                                <span class="time-text"><?php echo format_time($time['showTime']); ?></span>
                                                <span class="seats-info"><?php echo $time['availableSeats']; ?> ghế</span>
                                            </a>
                                        <?php else: ?>
                                            <span class="time-btn full">
                                                <span class="time-text"><?php echo format_time($time['showTime']); ?></span>
                                                <span class="seats-info">Hết chỗ</span>
                                            </span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
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
    <script>
        // Filter showtimes by room type
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const type = this.dataset.type;
                const rooms = document.querySelectorAll('.room-showtimes');
                
                rooms.forEach(room => {
                    if (type === 'all' || room.dataset.roomType === type) {
                        room.style.display = 'block';
                    } else {
                        room.style.display = 'none';
                    }
                });
            });
        });
    </script>
    <?php endif; ?>
</body>
</html>
