<?php
/**
 * BƯỚC 4: Xác nhận và hiển thị mã vé
 */
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['userID'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: /?openLogin=1');
    exit();
}

// Lấy bookingID
$bookingID = $_GET['bookingID'] ?? null;
if (!$bookingID) {
    header('Location: /');
    exit();
}

require_once __DIR__ . '/../models/booking_db.php';
require_once __DIR__ . '/../helpers/email_helper.php';

$booking = get_booking_with_details($bookingID);

if (!$booking || $booking['userID'] != $_SESSION['userID']) {
    header('Location: /');
    exit();
}

// Nếu chưa thanh toán
if ($booking['paymentStatus'] !== 'paid') {
    header('Location: /src/views/booking_step3_payment.php?bookingID=' . $bookingID);
    exit();
}

// 📧 GỬI EMAIL XÁC NHẬN (chỉ gửi 1 lần)
$emailSentKey = 'email_sent_' . $bookingID;
if (!isset($_SESSION[$emailSentKey])) {
    // Chuẩn bị dữ liệu booking với format đẹp
    $booking['totalPrice_formatted'] = number_format($booking['totalPrice']) . 'đ';
    $booking['confirmUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    
    // Gửi email (không chặn nếu thất bại)
    $emailSent = send_booking_confirmation_email($booking);
    
    // Đánh dấu đã gửi để không gửi lại khi refresh
    $_SESSION[$emailSentKey] = true;
    
    if ($emailSent) {
        // Log thành công (optional)
        error_log("[BOOKING] ✅ Email confirmation sent for booking: {$booking['bookingCode']}");
    } else {
        // Log lỗi nhưng vẫn cho user tiếp tục
        error_log("[BOOKING] ⚠️ Failed to send email for booking: {$booking['bookingCode']}");
    }
}

// Tạo QR code cho mã vé
$qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($booking['bookingCode']);

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
    <title>Đặt vé thành công - VKU Cinema</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/booking_confirm.css">
</head>
<body>
    <div class="booking-container">
        <!-- Header -->
        <div class="booking-header">
            <a href="/" class="btn-back" title="Về trang chủ">
                <i class="fas fa-home"></i><span class="btn-text"> Trang chủ</span>
            </a>
            <h1>Đặt vé thành công</h1>
            <div class="header-spacer"></div>
        </div>

        <!-- Success Animation -->
        <div class="success-animation">
            <div class="checkmark-circle">
                <div class="checkmark"></div>
            </div>
            <h2 class="success-title">Đặt vé thành công!</h2>
            <p class="success-subtitle">Cảm ơn bạn đã đặt vé tại VKU Cinema</p>
        </div>

        <!-- Booking Code Section -->
        <div class="booking-code-section">
            <h3>Mã đặt vé của bạn</h3>
            <div class="booking-code-display">
                <?php echo $booking['bookingCode']; ?>
            </div>
            <p class="code-instruction">
                <i class="fas fa-info-circle"></i> 
                Vui lòng xuất trình mã này tại quầy để nhận vé
            </p>
        </div>

        <!-- Main Content Grid -->
        <div class="content-grid">
            <!-- Left Column: QR Code -->
            <div class="qr-section">
                <div class="section-card">
                    <h3><i class="fas fa-qrcode"></i> QR Code Check-in</h3>
                    <div class="qr-container">
                        <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code" class="qr-image">
                        <p class="qr-instruction">Quét mã để check-in nhanh tại rạp</p>
                    </div>
                </div>

                <!-- Important Notes -->
                <div class="section-card notes-card">
                    <h3><i class="fas fa-exclamation-triangle"></i> Lưu ý quan trọng</h3>
                    <ul class="notes-list">
                        <li><i class="fas fa-check-circle"></i> Có mặt trước <strong>15 phút</strong></li>
                        <li><i class="fas fa-check-circle"></i> Mang <strong>CCCD/CMND</strong> để đối chiếu</li>
                        <li><i class="fas fa-check-circle"></i> Xuất trình <strong>mã vé</strong> hoặc <strong>QR code</strong></li>
                        <li><i class="fas fa-check-circle"></i> Vé <strong>không hoàn trả</strong> hoặc đổi</li>
                        <li><i class="fas fa-check-circle"></i> Không mang thức ăn từ bên ngoài</li>
                    </ul>
                </div>
            </div>

            <!-- Right Column: Ticket Details -->
            <div class="ticket-section">
                <div class="section-card ticket-card">
                    <h3><i class="fas fa-ticket-alt"></i> Thông tin vé</h3>
                    
                    <div class="movie-info">
                        <img src="<?php echo htmlspecialchars($booking['posterURL']); ?>" 
                             alt="<?php echo htmlspecialchars($booking['movieTitle']); ?>"
                             class="movie-poster">
                        <div class="movie-details">
                            <h2><?php echo htmlspecialchars($booking['movieTitle']); ?></h2>
                            <div class="movie-meta">
                                <span><i class="fas fa-clock"></i> <?php echo $booking['duration']; ?> phút</span>
                                <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($booking['genre']); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="ticket-divider"></div>

                    <div class="ticket-details">
                        <div class="detail-row">
                            <span class="label"><i class="fas fa-calendar-alt"></i> Ngày chiếu</span>
                            <span class="value"><?php echo format_date_vn($booking['showDate']); ?></span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="label"><i class="fas fa-clock"></i> Giờ chiếu</span>
                            <span class="value"><?php echo format_time($booking['showTime']); ?></span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="label"><i class="fas fa-door-open"></i> Phòng chiếu</span>
                            <span class="value"><?php echo $booking['roomName']; ?></span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="label"><i class="fas fa-couch"></i> Ghế ngồi</span>
                            <span class="value seats-value"><?php echo $booking['seats']; ?></span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="label"><i class="fas fa-users"></i> Số lượng</span>
                            <span class="value"><?php echo $booking['totalSeats']; ?> vé</span>
                        </div>
                    </div>

                    <div class="ticket-divider"></div>

                    <div class="ticket-total">
                        <span class="label">Tổng tiền</span>
                        <span class="value"><?php echo number_format($booking['totalPrice']); ?>đ</span>
                    </div>

                    <div class="payment-status">
                        <span class="status-badge paid">
                            <i class="fas fa-check-circle"></i> Đã thanh toán
                        </span>
                        <span class="payment-method">
                            <i class="fas fa-credit-card"></i>
                            <?php 
                            $methods = [
                                'qr' => 'Chuyển khoản QR',
                                'cash' => 'Tiền mặt',
                                'card' => 'Thẻ tín dụng',
                                'momo' => 'Ví MoMo',
                                'zalopay' => 'ZaloPay'
                            ];
                            echo $methods[$booking['paymentMethod']] ?? 'QR Code';
                            ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <button onclick="window.print()" class="btn-action btn-print">
                <i class="fas fa-print"></i> In vé
            </button>
            
            <a href="E:\school\hoc ki 1 2025-2026\DACS2\src\views\member.php" class="btn-action btn-history">
                <i class="fas fa-history"></i> Lịch sử đặt vé
            </a>
            
            <a href="/" class="btn-action btn-home">
                <i class="fas fa-home"></i> Về trang chủ
            </a>
        </div>

        <!-- Email Confirmation -->
        <div class="email-notice">
            <i class="fas fa-envelope"></i>
            <p>Thông tin vé đã được gửi đến email: <strong><?php echo htmlspecialchars($booking['email']); ?></strong></p>
        </div>
    </div>

    <script>
        // Success animation
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                document.querySelector('.checkmark').style.display = 'block';
            }, 200);
            
            // Confetti effect
            createConfetti();
        });

        // Confetti animation
        function createConfetti() {
            const colors = ['#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe'];
            for (let i = 0; i < 50; i++) {
                setTimeout(() => {
                    const confetti = document.createElement('div');
                    confetti.className = 'confetti';
                    confetti.style.left = Math.random() * 100 + '%';
                    confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                    confetti.style.animationDuration = (Math.random() * 3 + 2) + 's';
                    document.body.appendChild(confetti);
                    
                    setTimeout(() => confetti.remove(), 5000);
                }, i * 30);
            }
        }
    </script>
</body>
</html>
