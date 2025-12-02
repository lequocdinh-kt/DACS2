<?php
/**
 * BƯỚC 2: Chọn ghế ngồi
 */
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['userID'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: /src/views/login.php?message=' . urlencode('Vui lòng đăng nhập để đặt vé'));
    exit();
}

// Lấy showtimeID
$showtimeID = $_GET['showtimeID'] ?? null;
if (!$showtimeID) {
    header('Location: /');
    exit();
}

require_once __DIR__ . '/../models/showtime_db.php';
require_once __DIR__ . '/../models/seat_db.php';
require_once __DIR__ . '/../models/booking_db.php';

// Cleanup expired locks và bookings
cleanup_expired_locks();
cleanup_expired_bookings();

$showtime = get_showtime_by_id($showtimeID);
if (!$showtime || $showtime['status'] !== 'available') {
    header('Location: /');
    exit();
}

$seats = get_seats_by_showtime($showtimeID);

// Nhóm ghế theo hàng
$seatsByRow = [];
foreach ($seats as $seat) {
    $row = $seat['seatRow'];
    if (!isset($seatsByRow[$row])) {
        $seatsByRow[$row] = [];
    }
    $seatsByRow[$row][] = $seat;
}

ksort($seatsByRow);

function format_time($time) {
    return date('H:i', strtotime($time));
}

function format_date_vn($date) {
    $d = new DateTime($date);
    $days = ['Chủ nhật', 'Thứ hai', 'Thứ ba', 'Thứ tư', 'Thứ năm', 'Thứ sáu', 'Thứ bảy'];
    return $days[$d->format('w')] . ', ' . $d->format('d/m/Y');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chọn ghế - <?php echo htmlspecialchars($showtime['movieTitle']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/src/styles/booking_seats.css">
</head>
<body>
    <div class="booking-container">
        <!-- Header -->
        <div class="booking-header">
            <a href="/src/views/booking_step1_showtimes.php?movieID=<?php echo $showtime['movieID']; ?>" class="btn-back" title="Quay lại">
                <i class="fas fa-arrow-left"></i><span class="btn-text"> Quay lại</span>
            </a>
            <h1>Chọn ghế ngồi</h1>
            <div class="timer" id="countdown">
                <i class="fas fa-clock"></i>
                <span id="timer">15:00</span>
            </div>
        </div>

        <!-- Movie & Showtime Info -->
        <div class="showtime-info-card">
            <img src="<?php echo htmlspecialchars($showtime['posterURL']); ?>" 
                 alt="<?php echo htmlspecialchars($showtime['movieTitle']); ?>" 
                 class="mini-poster">
            <div class="info-details">
                <h3><?php echo htmlspecialchars($showtime['movieTitle']); ?></h3>
                <div class="info-meta">
                    <span><i class="fas fa-calendar"></i> <?php echo format_date_vn($showtime['showDate']); ?></span>
                    <span><i class="fas fa-clock"></i> <?php echo format_time($showtime['showTime']); ?></span>
                    <span><i class="fas fa-door-open"></i> <?php echo $showtime['roomName']; ?></span>
                </div>
            </div>
        </div>

        <!-- Progress Steps -->
        <div class="booking-steps">
            <div class="step completed">
                <div class="step-number"><i class="fas fa-check"></i></div>
                <div class="step-label">Chọn suất</div>
            </div>
            <div class="step-line completed"></div>
            <div class="step active">
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

        <!-- Cinema Screen -->
        <div class="screen-container">
            <div class="screen">
                <i class="fas fa-film"></i> MÀN HÌNH
            </div>
        </div>

        <!-- Seats Grid -->
        <div class="seats-container">
            <?php foreach ($seatsByRow as $row => $rowSeats): ?>
                <div class="seat-row">
                    <div class="row-label"><?php echo $row; ?></div>
                    <div class="seats-wrapper">
                        <?php foreach ($rowSeats as $seat): ?>
                            <div class="seat 
                                <?php echo $seat['status']; ?> 
                                <?php echo $seat['seatType']; ?> 
                                <?php echo ($seat['status'] === 'locked' && $seat['lockedByUserID'] == $_SESSION['userID']) ? 'my-lock' : ''; ?>"
                                data-seat-id="<?php echo $seat['seatID']; ?>"
                                data-seat-name="<?php echo $seat['seatRow'] . $seat['seatNumber']; ?>"
                                data-seat-type="<?php echo $seat['seatType']; ?>"
                                data-price="<?php echo $seat['price']; ?>"
                                <?php if ($seat['status'] !== 'available'): ?>data-disabled="true"<?php endif; ?>>
                                <span class="seat-number"><?php echo $seat['seatNumber']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="row-label"><?php echo $row; ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Legend -->
        <div class="seat-legend">
            <div class="legend-item">
                <div class="seat available" data-disabled="true"><span>1</span></div>
                <span>Còn trống</span>
            </div>
            <div class="legend-item">
                <div class="seat selected" data-disabled="true"><span>1</span></div>
                <span>Đang chọn</span>
            </div>
            <div class="legend-item">
                <div class="seat booked" data-disabled="true"><span>1</span></div>
                <span>Đã đặt</span>
            </div>
            <div class="legend-item">
                <div class="seat locked" data-disabled="true"><span>1</span></div>
                <span>Đang giữ</span>
            </div>
            <div class="legend-item">
                <div class="seat vip" data-disabled="true"><span>1</span></div>
                <span>VIP</span>
            </div>
        </div>

        <!-- Booking Summary -->
        <div class="booking-summary">
            <div class="summary-content">
                <div class="summary-row">
                    <span class="label">Ghế đã chọn:</span>
                    <span class="value" id="selectedSeatsText">Chưa chọn</span>
                </div>
                <div class="summary-row">
                    <span class="label">Số lượng:</span>
                    <span class="value" id="seatCount">0 ghế</span>
                </div>
                <div class="summary-row total">
                    <span class="label">Tổng tiền:</span>
                    <span class="value" id="totalPrice">0đ</span>
                </div>
            </div>
            <button id="btnContinue" class="btn-continue" disabled>
                <i class="fas fa-arrow-right"></i> Tiếp tục thanh toán
            </button>
        </div>

        <!-- Warning -->
        <div class="booking-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <p>Ghế sẽ được giữ trong <strong>15 phút</strong>. Vui lòng hoàn tất thanh toán trước khi hết thời gian!</p>
        </div>
    </div>

    <script>
        const showtimeID = <?php echo $showtimeID; ?>;
        const userID = <?php echo $_SESSION['userID']; ?>;
    </script>
    <script src="/src/js/booking_seats.js"></script>
</body>
</html>
