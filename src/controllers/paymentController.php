<?php
/**
 * Controller: Xử lý AJAX cho chức năng thanh toán
 */

session_start();
header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized', 'redirect' => '/src/views/login.php']);
    exit();
}

require_once __DIR__ . '/../models/database.php';
require_once __DIR__ . '/../models/payment_db.php';
require_once __DIR__ . '/../models/booking_db.php';

$action = $_POST['action'] ?? $_GET['action'] ?? null;

if (!$action) {
    echo json_encode(['success' => false, 'message' => 'No action specified']);
    exit();
}

switch ($action) {
    case 'check_payment':
        check_payment_status();
        break;
    
    case 'confirm_payment':
        confirm_payment_action();
        break;
    
    case 'confirm_payment_manual':
        confirm_payment_manual();
        break;
    
    case 'generate_qr':
        generate_qr_action();
        break;
    
    case 'verify_transaction':
        verify_transaction_action();
        break;
    
    case 'verify_bank_transaction':
        verify_bank_transaction();
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

/**
 * Kiểm tra trạng thái thanh toán
 */
function check_payment_status() {
    $bookingID = $_GET['bookingID'] ?? null;
    $userID = $_SESSION['userID'];
    
    if (!$bookingID) {
        echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
        return;
    }
    
    try {
        $booking = get_booking_with_details($bookingID);
        
        if (!$booking || $booking['userID'] != $userID) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        $payment = get_payment_by_booking($bookingID);
        
        echo json_encode([
            'success' => true,
            'paymentStatus' => $booking['paymentStatus'],
            'payment' => $payment,
            'expired' => strtotime($booking['expiredAt']) < time()
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error checking payment: ' . $e->getMessage()
        ]);
    }
}

/**
 * Xác nhận thanh toán (Admin hoặc auto)
 */
function confirm_payment_action() {
    $bookingID = $_POST['bookingID'] ?? null;
    $transactionCode = $_POST['transactionCode'] ?? null;
    $userID = $_SESSION['userID'];
    
    if (!$bookingID) {
        echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
        return;
    }
    
    try {
        $booking = get_booking_with_details($bookingID);
        
        if (!$booking || $booking['userID'] != $userID) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        // Kiểm tra đã hết hạn chưa
        if (strtotime($booking['expiredAt']) < time()) {
            echo json_encode([
                'success' => false,
                'message' => 'Đơn hàng đã hết hạn thanh toán'
            ]);
            return;
        }
        
        $payment = get_payment_by_booking($bookingID);
        
        if (!$payment) {
            echo json_encode(['success' => false, 'message' => 'Payment not found']);
            return;
        }
        
        // Xác nhận thanh toán
        $confirmed = confirm_payment($payment['paymentID'], $transactionCode);
        
        if ($confirmed) {
            echo json_encode([
                'success' => true,
                'message' => 'Thanh toán thành công',
                'bookingCode' => $booking['bookingCode']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Không thể xác nhận thanh toán'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error confirming payment: ' . $e->getMessage()
        ]);
    }
}

/**
 * Tạo QR code thanh toán
 */
function generate_qr_action() {
    $bookingID = $_GET['bookingID'] ?? null;
    $userID = $_SESSION['userID'];
    
    if (!$bookingID) {
        echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
        return;
    }
    
    try {
        $booking = get_booking_with_details($bookingID);
        
        if (!$booking || $booking['userID'] != $userID) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        $qrInfo = generate_payment_qr_info($booking['bookingCode'], $booking['totalPrice']);
        
        echo json_encode([
            'success' => true,
            'qrInfo' => $qrInfo
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error generating QR: ' . $e->getMessage()
        ]);
    }
}

/**
 * Verify giao dịch từ ngân hàng (webhook hoặc polling)
 * Trong thực tế cần tích hợp API ngân hàng
 */
function verify_transaction_action() {
    $bookingCode = $_POST['bookingCode'] ?? null;
    $amount = $_POST['amount'] ?? null;
    $transactionCode = $_POST['transactionCode'] ?? null;
    
    if (!$bookingCode || !$amount) {
        echo json_encode(['success' => false, 'message' => 'Missing parameters']);
        return;
    }
    
    try {
        // TODO: Tích hợp API ngân hàng để verify giao dịch thực
        // Hiện tại chỉ là mock
        
        // Tìm booking theo mã - Sử dụng $db từ database.php
        global $db;
        $stmt = $db->prepare("SELECT bookingID, totalPrice, paymentStatus FROM Bookings WHERE bookingCode = ?");
        $stmt->execute([$bookingCode]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$booking) {
            echo json_encode(['success' => false, 'message' => 'Booking not found']);
            return;
        }
        
        // Kiểm tra số tiền
        if ($amount != $booking['totalPrice']) {
            echo json_encode([
                'success' => false,
                'message' => 'Số tiền không khớp'
            ]);
            return;
        }
        
        // Kiểm tra đã thanh toán chưa
        if ($booking['paymentStatus'] === 'paid') {
            echo json_encode([
                'success' => true,
                'message' => 'Đã thanh toán rồi',
                'alreadyPaid' => true
            ]);
            return;
        }
        
        // Lấy payment
        $payment = get_payment_by_booking($booking['bookingID']);
        
        if ($payment) {
            // Xác nhận thanh toán
            confirm_payment($payment['paymentID'], $transactionCode);
            
            echo json_encode([
                'success' => true,
                'message' => 'Xác thực giao dịch thành công'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Payment record not found'
            ]);
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error verifying transaction: ' . $e->getMessage()
        ]);
    }
}

/**
 * Xác nhận thanh toán thủ công (người dùng tự xác nhận đã chuyển khoản)
 */
function confirm_payment_manual() {
    $bookingID = $_POST['bookingID'] ?? $_POST['booking_id'] ?? null;
    $userID = $_SESSION['userID'];
    
    if (!$bookingID) {
        echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
        return;
    }
    
    try {
        // Kiểm tra booking thuộc về user này
        $booking = get_booking_with_details($bookingID);
        
        if (!$booking || $booking['userID'] != $userID) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        // Kiểm tra trạng thái hiện tại
        if ($booking['paymentStatus'] === 'paid') {
            echo json_encode([
                'success' => true,
                'message' => 'Booking đã được thanh toán'
            ]);
            return;
        }
        
        // Lấy payment record
        $payment = get_payment_by_booking($bookingID);
        
        if (!$payment) {
            echo json_encode(['success' => false, 'message' => 'Payment record not found']);
            return;
        }
        
        // Xác nhận thanh toán
        $transactionCode = 'DEV_MANUAL_' . time() . '_' . $bookingID;
        confirm_payment($payment['paymentID'], $transactionCode);
        
        // Cập nhật trạng thái booking
        update_booking_payment_status($bookingID, 'paid');
        
        echo json_encode([
            'success' => true,
            'message' => 'Xác nhận thanh toán thành công (DEV MODE)',
            'bookingID' => $bookingID,
            'bookingCode' => $booking['bookingCode']
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error confirming payment: ' . $e->getMessage()
        ]);
    }
}

/**
 * Kiểm tra giao dịch ngân hàng tự động (polling)
 * Trong thực tế sẽ gọi API ngân hàng (Casso.vn, VietQR, hoặc API bank)
 */
function verify_bank_transaction() {
    $bookingID = $_POST['booking_id'] ?? null;
    $expectedAmount = $_POST['amount'] ?? null;
    $userID = $_SESSION['userID'];
    
    // Log để debug
    error_log("🔵 verify_bank_transaction called - BookingID: $bookingID, Amount: $expectedAmount, UserID: $userID");
    
    if (!$bookingID || !$expectedAmount) {
        error_log("❌ Missing parameters");
        echo json_encode(['success' => false, 'message' => 'Missing parameters']);
        return;
    }
    
    try {
        // Kiểm tra booking thuộc về user này
        $booking = get_booking_with_details($bookingID);
        
        if (!$booking || $booking['userID'] != $userID) {
            error_log("❌ Unauthorized - Booking not found or wrong user");
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        error_log("📋 Booking found - Status: " . $booking['paymentStatus']);
        
        // Kiểm tra đã thanh toán chưa
        if ($booking['paymentStatus'] === 'paid') {
            error_log("✅ Already paid");
            echo json_encode([
                'success' => true,
                'transaction_found' => true,
                'message' => 'Payment already confirmed'
            ]);
            return;
        }
        
        // ==== PHẦN NÀY CẦN TÍCH HỢP API NGÂN HÀNG THỰC ====
        // Demo: Giả lập kiểm tra qua API
        // LƯU Ý: Truyền bookingCode chứ không phải bookingID
        error_log("🔍 Calling checkBankAPI with bookingCode: " . $booking['bookingCode']);
        $transactionFound = checkBankAPI($booking['bookingCode'], $expectedAmount);
        
        error_log("📊 Transaction found: " . ($transactionFound ? 'YES' : 'NO'));
        
        if ($transactionFound) {
            // Nếu tìm thấy giao dịch khớp, tự động xác nhận
            $payment = get_payment_by_booking($bookingID);
            
            if ($payment) {
                $transactionCode = 'AUTO_' . time() . '_' . substr(md5($bookingID), 0, 8);
                confirm_payment($payment['paymentID'], $transactionCode);
                update_booking_payment_status($bookingID, 'paid');
                
                echo json_encode([
                    'success' => true,
                    'transaction_found' => true,
                    'message' => 'Payment verified successfully',
                    'transaction_code' => $transactionCode
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Payment record not found'
                ]);
            }
        } else {
            // Chưa tìm thấy giao dịch
            echo json_encode([
                'success' => true,
                'transaction_found' => false,
                'message' => 'No matching transaction found yet'
            ]);
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error verifying bank transaction: ' . $e->getMessage()
        ]);
    }
}

/**
 * Kiểm tra giao dịch qua API ngân hàng
 * 
 * CÁC LỰA CHỌN TÍCH HỢP:
 * 1. Casso.vn - API miễn phí cho sinh viên: https://casso.vn
 * 2. VietQR Pro - API kiểm tra giao dịch
 * 3. API trực tiếp từ ngân hàng (Vietcombank, Techcombank, etc.)
 * 
 * @param string $bookingID - Mã booking (dùng làm nội dung chuyển khoản)
 * @param float $expectedAmount - Số tiền cần thanh toán
 * @return bool - True nếu tìm thấy giao dịch khớp
 */
function checkBankAPI($bookingID, $expectedAmount) {
    // ==== TEST MODE - Uncomment dòng này để test tự động confirm sau 20 giây ====
    // Mỗi 40 giây sẽ có 20 giây trả về true (giả lập tìm thấy giao dịch)
    // return (time() % 40) > 20;
    
    // ==== API THẬT - CASSO.VN ====
    $cassoApiKey = 'AK_CS.b7c0fc30be0411f0a73fcb966f33aa53.kSGJs2qRSV16vbwLf0cqKQfdVz4ymIfDYHJzTc9JAs0dQbFQQIBbrZ4TWsOWQ5B45j9MjKzR'; // Lấy từ Casso Dashboard
    
    // Log để debug
    error_log("🔍 Checking bank API for booking: $bookingID, amount: $expectedAmount");
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://oauth.casso.vn/v2/transactions',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Apikey ' . $cassoApiKey,
            'Content-Type: application/json'
        ]
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Log response để debug
    error_log("📡 Casso API Response - HTTP Code: $httpCode");
    
    if ($httpCode !== 200) {
        error_log("❌ Casso API Error: HTTP $httpCode - Response: " . substr($response, 0, 200));
        return false;
    }
    
    $data = json_decode($response, true);
    
    // Log số lượng giao dịch nhận được
    $recordCount = isset($data['data']['records']) ? count($data['data']['records']) : 0;
    error_log("📊 Found $recordCount transactions from Casso");
    
    if (isset($data['data']['records'])) {
        foreach ($data['data']['records'] as $transaction) {
            $amount = $transaction['amount'] ?? 0;
            $description = strtoupper($transaction['description'] ?? '');
            $bookingCode = strtoupper($bookingID);
            
            // Log mỗi giao dịch để check
            error_log("💳 Transaction: Amount=$amount, Description=$description");
            
            // Format nội dung chuyển khoản: "VKU CINEMA {bookingCode}"
            // Ví dụ: "VKU CINEMA BK20241022001"
            $expectedDescription = "VKU CINEMA " . $bookingCode;
            error_log("🔍 Looking for: '$expectedDescription' in '$description'");
            
            // Kiểm tra:
            // 1. Số tiền khớp hoặc lớn hơn
            // 2. Nội dung chuyển khoản chứa bookingCode HOẶC chứa full "VKU CINEMA {bookingCode}"
            // 3. Giao dịch trong vòng 1 giờ gần đây
            $transactionTime = strtotime($transaction['when']);
            $isRecent = (time() - $transactionTime) < 3600; // 1 hour
            
            $hasBookingCode = strpos($description, $bookingCode) !== false;
            $hasFullDescription = strpos($description, strtoupper($expectedDescription)) !== false;
            
            if ($amount >= $expectedAmount 
                && ($hasBookingCode || $hasFullDescription)
                && $isRecent) {
                error_log("✅ MATCH FOUND! Booking: $bookingCode, Amount: $amount");
                return true;
            } else {
                if (!$isRecent) error_log("   ⏰ Transaction too old");
                if ($amount < $expectedAmount) error_log("   💰 Amount mismatch: $amount < $expectedAmount");
                if (!$hasBookingCode && !$hasFullDescription) error_log("   📝 Description mismatch");
            }
        }
    }
    
    error_log("⏳ No matching transaction found yet");
    return false;
}
