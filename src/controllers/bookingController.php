<?php
/**
 * Controller: Xử lý AJAX cho chức năng đặt vé
 */

session_start();
header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để tiếp tục', 'requireLogin' => true]);
    exit();
}

require_once __DIR__ . '/../models/database.php';
require_once __DIR__ . '/../models/booking_db.php';
require_once __DIR__ . '/../models/seat_db.php';

$action = $_POST['action'] ?? $_GET['action'] ?? null;

if (!$action) {
    echo json_encode(['success' => false, 'message' => 'No action specified']);
    exit();
}

switch ($action) {
    case 'create_booking':
        create_booking_action();
        break;
    
    case 'get_booking':
        get_booking_action();
        break;
    
    case 'cancel_booking':
        cancel_booking_action();
        break;
    
    case 'get_my_bookings':
        get_my_bookings_action();
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

/**
 * Tạo booking mới
 */
function create_booking_action() {
    $showtimeID = $_POST['showtimeID'] ?? null;
    $seatIDs = $_POST['seatIDs'] ?? null;
    $userID = $_SESSION['userID'];
    
    if (!$showtimeID || !$seatIDs) {
        echo json_encode(['success' => false, 'message' => 'Missing parameters']);
        return;
    }
    
    // Parse seatIDs nếu là JSON string
    if (is_string($seatIDs)) {
        $seatIDs = json_decode($seatIDs, true);
    }
    
    if (!is_array($seatIDs) || empty($seatIDs)) {
        echo json_encode(['success' => false, 'message' => 'Invalid seat IDs']);
        return;
    }
    
    try {
        // Kiểm tra ghế còn available cho user này không (bỏ qua ghế do chính user lock)
        $available = check_seats_available_for_user($showtimeID, $seatIDs, $userID);
        if (!$available) {
            echo json_encode([
                'success' => false,
                'message' => 'Một hoặc nhiều ghế đã được đặt'
            ]);
            return;
        }
        
        // Tạo booking
        // Lấy tổng giá từ client nếu có, nếu không thì fallback về 0 (hoặc tính ở server nếu có hàm phù hợp)
        $totalPrice = isset($_POST['totalPrice']) ? floatval($_POST['totalPrice']) : 0;
        
        // Debug log
        error_log("Creating booking - userID: $userID, showtimeID: $showtimeID, totalPrice: $totalPrice, seatIDs: " . json_encode($seatIDs));
        
        $bookingID = create_booking($userID, $showtimeID, $seatIDs, $totalPrice);
        
        if ($bookingID) {
            // Lấy thông tin booking vừa tạo
            $booking = get_booking_with_details($bookingID);
            
            echo json_encode([
                'success' => true,
                'message' => 'Tạo đơn đặt vé thành công',
                'bookingID' => $bookingID,
                'bookingCode' => $booking['bookingCode'],
                'totalPrice' => $booking['totalPrice'],
                'expiredAt' => $booking['expiredAt']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Không thể tạo đơn đặt vé'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error creating booking: ' . $e->getMessage()
        ]);
    }
}

/**
 * Lấy thông tin booking
 */
function get_booking_action() {
    $bookingID = $_GET['bookingID'] ?? null;
    $userID = $_SESSION['userID'];
    
    if (!$bookingID) {
        echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
        return;
    }
    
    try {
        $booking = get_booking_with_details($bookingID);
        
        if (!$booking) {
            echo json_encode(['success' => false, 'message' => 'Booking not found']);
            return;
        }
        
        // Kiểm tra quyền truy cập
        if ($booking['userID'] != $userID) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'booking' => $booking
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching booking: ' . $e->getMessage()
        ]);
    }
}

/**
 * Hủy booking
 */
function cancel_booking_action() {
    $bookingID = $_POST['bookingID'] ?? null;
    $userID = $_SESSION['userID'];
    
    if (!$bookingID) {
        echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
        return;
    }
    
    try {
        // Kiểm tra quyền sở hữu
        $booking = get_booking_with_details($bookingID);
        
        if (!$booking || $booking['userID'] != $userID) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        // Kiểm tra thời gian (chỉ hủy được nếu còn > 2h trước giờ chiếu)
        $showtime = strtotime($booking['showDate'] . ' ' . $booking['showTime']);
        $now = time();
        $hoursDiff = ($showtime - $now) / 3600;
        
        if ($hoursDiff < 2) {
            echo json_encode([
                'success' => false,
                'message' => 'Không thể hủy vé trong vòng 2 giờ trước giờ chiếu'
            ]);
            return;
        }
        
        // Hủy booking
        $cancelled = cancel_booking($bookingID, $userID);
        
        if ($cancelled) {
            echo json_encode([
                'success' => true,
                'message' => 'Đã hủy vé thành công'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Không thể hủy vé'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error cancelling booking: ' . $e->getMessage()
        ]);
    }
}

/**
 * Lấy danh sách booking của user
 */
function get_my_bookings_action() {
    $userID = $_SESSION['userID'];
    $status = $_GET['status'] ?? null; // pending, paid, cancelled, expired
    
    try {
        $bookings = get_user_bookings($userID, $status);
        
        echo json_encode([
            'success' => true,
            'bookings' => $bookings
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching bookings: ' . $e->getMessage()
        ]);
    }
}
