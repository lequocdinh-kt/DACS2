<?php
/**
 * BƯỚC 3: Thanh toán QR
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
require_once __DIR__ . '/../models/payment_db.php';

$booking = get_booking_with_details($bookingID);

if (!$booking || $booking['userID'] != $_SESSION['userID']) {
    header('Location: /');
    exit();
}

// Nếu đã thanh toán rồi
if ($booking['paymentStatus'] === 'paid') {
    header('Location: /src/views/booking_step4_confirm.php?bookingID=' . $bookingID);
    exit();
}

// Tắt kiểm tra hết hạn
// if ($booking['paymentStatus'] === 'expired' || strtotime($booking['expiredAt']) < time()) {
//     echo '<script>alert("Đơn hàng đã hết hạn thanh toán!"); window.location.href="/";</script>';
//     exit();
// }

// Tạo thông tin QR
$qrInfo = generate_payment_qr_info($booking['bookingCode'], $booking['totalPrice']);

// Tạo payment record nếu chưa có
$payment = get_payment_by_booking($bookingID);
if (!$payment) {
    create_payment($bookingID, $booking['totalPrice'], 'qr', 'Thanh toán vé ' . $booking['bookingCode']);
}

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
    <title>Thanh toán - <?php echo htmlspecialchars($booking['movieTitle']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/src/styles/booking_payment.css">
</head>
<body>
    <div class="payment-container">
        <!-- Header -->
        <div class="payment-header">
            <h1>Thanh toán đặt vé</h1>
            <div class="timer-warning" id="countdown">
                <i class="fas fa-clock"></i>
                Thời gian còn lại: <span id="timer"></span>
            </div>
        </div>

        <!-- Progress Steps -->
        <div class="booking-steps">
            <div class="step completed">
                <div class="step-number"><i class="fas fa-check"></i></div>
                <div class="step-label">Chọn suất</div>
            </div>
            <div class="step-line completed"></div>
            <div class="step completed">
                <div class="step-number"><i class="fas fa-check"></i></div>
                <div class="step-label">Chọn ghế</div>
            </div>
            <div class="step-line completed"></div>
            <div class="step active">
                <div class="step-number">3</div>
                <div class="step-label">Thanh toán</div>
            </div>
            <div class="step-line"></div>
            <div class="step">
                <div class="step-number">4</div>
                <div class="step-label">Xác nhận</div>
            </div>
        </div>

        <div class="payment-content">
            <!-- Left: Booking Info -->
            <div class="booking-info">
                <h2><i class="fas fa-ticket-alt"></i> Thông tin đặt vé</h2>
                
                <div class="info-section">
                    <div class="info-item">
                        <img src="<?php echo htmlspecialchars($booking['posterURL']); ?>" 
                             alt="<?php echo htmlspecialchars($booking['movieTitle']); ?>" 
                             class="movie-thumb">
                    </div>
                    
                    <div class="info-item">
                        <label>Mã đặt vé:</label>
                        <strong class="booking-code"><?php echo $booking['bookingCode']; ?></strong>
                    </div>
                    
                    <div class="info-item">
                        <label>Phim:</label>
                        <span><?php echo htmlspecialchars($booking['movieTitle']); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <label>Ngày chiếu:</label>
                        <span><?php echo format_date_vn($booking['showDate']); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <label>Giờ chiếu:</label>
                        <span><?php echo format_time($booking['showTime']); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <label>Phòng:</label>
                        <span><?php echo $booking['roomName']; ?></span>
                    </div>
                    
                    <div class="info-item">
                        <label>Ghế:</label>
                        <span class="seats-list"><?php echo $booking['seats']; ?></span>
                    </div>
                    
                    <div class="info-item">
                        <label>Số lượng:</label>
                        <span><?php echo $booking['totalSeats']; ?> ghế</span>
                    </div>
                    
                    <div class="info-item total">
                        <label>Tổng tiền:</label>
                        <strong class="total-amount"><?php echo number_format($booking['totalPrice']); ?>đ</strong>
                    </div>
                </div>
            </div>

            <!-- Right: QR Payment -->
            <div class="payment-method" data-amount="<?php echo $booking['totalPrice']; ?>">
                <h2><i class="fas fa-qrcode"></i> Quét mã QR để thanh toán</h2>
                
                <div class="qr-section">
                    <div class="qr-code-wrapper">
                        <img src="<?php echo $qrInfo['qrUrl']; ?>" 
                             alt="QR Code" 
                             class="qr-code"
                             id="qrCode">
                        <!-- Đã tắt overlay loading
                        <div class="qr-overlay" id="qrOverlay" style="display: none;">
                            <div class="loading-spinner">
                                <i class="fas fa-spinner fa-spin"></i>
                                <p>Đang chờ thanh toán...</p>
                            </div>
                        </div>
                        -->
                    </div>
                    
                    <div class="bank-info">
                        <h3>Thông tin chuyển khoản</h3>
                        <div class="bank-detail">
                            <label>Ngân hàng:</label>
                            <strong><?php echo $qrInfo['bankName']; ?></strong>
                        </div>
                        <div class="bank-detail">
                            <label>Số tài khoản:</label>
                            <strong><?php echo $qrInfo['bankAccount']; ?></strong>
                            <button class="btn-copy" onclick="copyToClipboard('<?php echo $qrInfo['bankAccount']; ?>')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <div class="bank-detail">
                            <label>Chủ tài khoản:</label>
                            <strong><?php echo $qrInfo['accountName']; ?></strong>
                        </div>
                        <div class="bank-detail">
                            <label>Số tiền:</label>
                            <strong><?php echo number_format($qrInfo['amount']); ?>đ</strong>
                            <button class="btn-copy" onclick="copyToClipboard('<?php echo $qrInfo['amount']; ?>')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <div class="bank-detail important">
                            <label>Nội dung CK:</label>
                            <strong><?php echo $qrInfo['description']; ?></strong>
                            <button class="btn-copy" onclick="copyToClipboard('<?php echo $qrInfo['description']; ?>')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="payment-instructions">
                        <h3><i class="fas fa-info-circle"></i> Hướng dẫn thanh toán</h3>
                        <ol>
                            <li>Mở app ngân hàng của bạn</li>
                            <li>Chọn chức năng quét mã QR</li>
                            <li>Quét mã QR phía trên</li>
                            <li>Kiểm tra thông tin và xác nhận chuyển khoản</li>
                            <li>Chờ hệ thống xác nhận (1-3 phút)</li>
                        </ol>
                    </div>
                    
                    <div class="payment-note">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Lưu ý quan trọng:</strong>
                        <ul>
                            <li>Vui lòng chuyển khoản <strong>ĐÚNG số tiền</strong> và <strong>ĐÚNG nội dung</strong></li>
                            <li><strong>📝 Nội dung chuyển khoản:</strong> <code style="background: #fff3cd; padding: 5px 10px; border-radius: 4px; font-size: 16px; font-weight: bold; color: #856404;">VKU CINEMA <?php echo $booking['bookingCode']; ?></code></li>
                            <li>⚡ <strong>Tự động xác nhận</strong> - Hệ thống sẽ tự động chuyển trang sau khi phát hiện chuyển khoản (5-30 giây)</li>
                            <li>❌ <strong>Không tắt trang</strong> này trong quá trình chờ xác nhận</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Status -->
        <div class="payment-status" id="paymentStatus" style="display: none;">
            <div class="status-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2>Thanh toán thành công!</h2>
            <p>Đang chuyển hướng...</p>
        </div>
    </div>

    <script>
        // Export biến bookingID ra global scope để JavaScript sử dụng
        window.bookingID = <?php echo $bookingID; ?>;
        window.bookingCode = '<?php echo $booking['bookingCode']; ?>';
        window.bookingAmount = <?php echo $booking['totalPrice']; ?>;
        
        // Log để debug
        console.log('=' .repeat(50));
        console.log('📋 THÔNG TIN BOOKING:');
        console.log('   Booking ID:', window.bookingID);
        console.log('   Booking Code:', window.bookingCode);
        console.log('   Amount:', window.bookingAmount);
        console.log('📝 NỘI DUNG CHUYỂN KHOẢN:');
        console.log('   VKU CINEMA ' + window.bookingCode);
        console.log('=' .repeat(50));
        
        <?php 
        // Tính lại thời gian hết hạn - luôn có 15 phút từ bây giờ
        $expiredTime = $booking['expiredAt'] ? $booking['expiredAt'] : date('Y-m-d H:i:s', strtotime('+15 minutes'));
        ?>
        const expiredAt = '<?php echo $expiredTime; ?>';
        console.log('Booking expiredAt:', expiredAt);
        console.log('Current time:', new Date().toISOString());
    </script>
    <script src="/src/js/booking_payment.js"></script>
</body>
</html>
