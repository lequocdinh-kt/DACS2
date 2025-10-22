<?php
/**
 * BƯỚC 4: Xác nhận và hiển thị mã vé
 */
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['userID'])) {
    header('Location: /src/views/login.php');
    exit();
}

// Lấy bookingID
$bookingID = $_GET['bookingID'] ?? null;
if (!$bookingID) {
    header('Location: /');
    exit();
}

require_once __DIR__ . '/../models/booking_db.php';

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
    <link rel="stylesheet" href="/src/styles/booking_confirm.css">
</head>
<body>
    <div class="confirm-container">
        <!-- Success Animation -->
        <div class="success-animation">
            <div class="checkmark-circle">
                <div class="checkmark"></div>
            </div>
        </div>

        <h1 class="success-title">Đặt vé thành công!</h1>
        <p class="success-subtitle">Cảm ơn bạn đã đặt vé tại VKU Cinema</p>

        <!-- Booking Code -->
        <div class="booking-code-section">
            <h2>Mã vé của bạn</h2>
            <div class="booking-code-display">
                <?php echo $booking['bookingCode']; ?>
            </div>
            <p class="code-instruction">Vui lòng xuất trình mã này tại quầy để nhận vé</p>
        </div>

        <!-- QR Code -->
        <div class="qr-ticket-section">
            <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code" class="qr-ticket">
            <p>Quét mã QR để check-in nhanh</p>
        </div>

        <!-- Ticket Details -->
        <div class="ticket-card">
            <div class="ticket-header">
                <h3><i class="fas fa-ticket-alt"></i> Thông tin vé</h3>
            </div>
            
            <div class="ticket-body">
                <div class="ticket-row">
                    <div class="ticket-poster">
                        <img src="<?php echo htmlspecialchars($booking['posterURL']); ?>" 
                             alt="<?php echo htmlspecialchars($booking['movieTitle']); ?>">
                    </div>
                    <div class="ticket-info">
                        <h2><?php echo htmlspecialchars($booking['movieTitle']); ?></h2>
                        <div class="ticket-meta">
                            <span><i class="fas fa-clock"></i> <?php echo $booking['duration']; ?> phút</span>
                            <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($booking['genre']); ?></span>
                        </div>
                    </div>
                </div>

                <div class="ticket-divider"></div>

                <div class="ticket-details">
                    <div class="detail-row">
                        <span class="label"><i class="fas fa-calendar"></i> Ngày chiếu:</span>
                        <span class="value"><?php echo format_date_vn($booking['showDate']); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="label"><i class="fas fa-clock"></i> Giờ chiếu:</span>
                        <span class="value"><?php echo format_time($booking['showTime']); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="label"><i class="fas fa-door-open"></i> Phòng chiếu:</span>
                        <span class="value"><?php echo $booking['roomName']; ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="label"><i class="fas fa-chair"></i> Ghế ngồi:</span>
                        <span class="value seats-display"><?php echo $booking['seats']; ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="label"><i class="fas fa-users"></i> Số lượng:</span>
                        <span class="value"><?php echo $booking['totalSeats']; ?> vé</span>
                    </div>
                </div>

                <div class="ticket-divider"></div>

                <div class="ticket-total">
                    <span class="label">Tổng tiền:</span>
                    <span class="value"><?php echo number_format($booking['totalPrice']); ?>đ</span>
                </div>

                <div class="payment-info">
                    <span class="paid-badge"><i class="fas fa-check-circle"></i> Đã thanh toán</span>
                    <span class="payment-method">
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

        <!-- Important Notes -->
        <div class="important-notes">
            <h3><i class="fas fa-exclamation-circle"></i> Lưu ý quan trọng</h3>
            <ul>
                <li><i class="fas fa-check"></i> Vui lòng có mặt trước giờ chiếu <strong>15 phút</strong></li>
                <li><i class="fas fa-check"></i> Mang theo <strong>CCCD/CMND</strong> để đối chiếu thông tin</li>
                <li><i class="fas fa-check"></i> Xuất trình <strong>mã vé</strong> hoặc <strong>QR code</strong> tại quầy</li>
                <li><i class="fas fa-check"></i> Vé đã mua <strong>không thể hoàn trả</strong> hoặc đổi</li>
                <li><i class="fas fa-check"></i> Không mang thức ăn, đồ uống từ bên ngoài vào rạp</li>
            </ul>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <button onclick="window.print()" class="btn-action btn-print">
                <i class="fas fa-print"></i> In vé
            </button>
            
            <button onclick="downloadTicket()" class="btn-action btn-download">
                <i class="fas fa-download"></i> Tải vé
            </button>
            
            <a href="/src/views/my_bookings.php" class="btn-action btn-history">
                <i class="fas fa-history"></i> Lịch sử đặt vé
            </a>
            
            <a href="/" class="btn-action btn-home">
                <i class="fas fa-home"></i> Về trang chủ
            </a>
        </div>

        <!-- Email Confirmation -->
        <div class="email-sent">
            <i class="fas fa-envelope-circle-check"></i>
            <p>Thông tin vé đã được gửi đến email: <strong><?php echo htmlspecialchars($booking['email']); ?></strong></p>
        </div>
    </div>

    <script>
        // Lưu vào localStorage để có thể xem offline
        const bookingData = <?php echo json_encode($booking); ?>;
        localStorage.setItem('lastBooking_<?php echo $bookingID; ?>', JSON.stringify(bookingData));
        
        // Hiệu ứng success
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                document.querySelector('.checkmark').style.display = 'block';
            }, 200);
        });

        // Download ticket
        function downloadTicket() {
            // Tạo canvas và vẽ ticket để download
            window.print();
        }

        // Show confetti effect
        function createConfetti() {
            for (let i = 0; i < 50; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + '%';
                confetti.style.animationDelay = Math.random() * 3 + 's';
                confetti.style.backgroundColor = ['#ff6b6b', '#4ecdc4', '#45b7d1', '#f9ca24', '#6c5ce7'][Math.floor(Math.random() * 5)];
                document.body.appendChild(confetti);
                
                setTimeout(() => confetti.remove(), 3000);
            }
        }

        createConfetti();
    </script>
</body>
</html>
