<?php
/**
 * Member Controller
 * Xử lý các request liên quan đến thông tin thành viên và lịch sử đặt vé
 */

session_start();
require_once __DIR__ . '/../models/user_db.php';
require_once __DIR__ . '/../models/booking_db.php';

// Debug logging
error_log("=== memberController.php ===");
error_log("Session ID: " . session_id());
error_log("Session data: " . print_r($_SESSION, true));
error_log("Action: " . (isset($_GET['action']) ? $_GET['action'] : 'none'));

// Xử lý AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    $action = $_GET['action'];
    
    switch ($action) {
        case 'check_login':
            // Kiểm tra xem user đã đăng nhập chưa
            error_log("Checking login - userID in session: " . (isset($_SESSION['userID']) ? $_SESSION['userID'] : 'NOT SET'));
            error_log("Checking login - user_id in session: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET'));
            
            // Check cả userID và user_id để tương thích
            if (isset($_SESSION['userID']) || isset($_SESSION['user_id'])) {
                $userId = isset($_SESSION['userID']) ? $_SESSION['userID'] : $_SESSION['user_id'];
                echo json_encode([
                    'success' => true,
                    'logged_in' => true,
                    'user_id' => $userId
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'logged_in' => false
                ]);
            }
            break;
            
        case 'get_user_info':
            // Lấy thông tin user - check cả userID và user_id
            $userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);
            
            if (!$userID) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Chưa đăng nhập'
                ]);
                exit;
            }
            $user = get_user_by_id($userID);
            
            if ($user) {
                // Đếm số lượng booking
                $allBookings = get_user_bookings($userID);
                $paidBookings = get_user_bookings($userID, 'paid');
                
                // Tính tổng chi tiêu
                $totalSpent = 0;
                foreach ($paidBookings as $booking) {
                    $totalSpent += $booking['totalPrice'];
                }
                
                echo json_encode([
                    'success' => true,
                    'user' => [
                        'userID' => $user['userID'],
                        'username' => $user['username'],
                        'email' => $user['email'],
                        'roleID' => $user['roleID'],
                        'totalBookings' => count($allBookings),
                        'paidBookings' => count($paidBookings),
                        'totalSpent' => $totalSpent
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin user'
                ]);
            }
            break;
            
        case 'get_booking_history':
            // Lấy lịch sử đặt vé - check cả userID và user_id
            $userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);
            
            if (!$userID) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Chưa đăng nhập'
                ]);
                exit;
            }
            
            $status = isset($_GET['status']) ? $_GET['status'] : null;
            
            // Cleanup expired bookings trước khi lấy danh sách
            cleanup_expired_bookings();
            
            $bookings = get_user_bookings($userID, $status);
            
            // Format dữ liệu
            foreach ($bookings as &$booking) {
                // Format ngày giờ
                $showDateTime = new DateTime($booking['showDate'] . ' ' . $booking['showTime']);
                $booking['formattedDate'] = $showDateTime->format('d/m/Y');
                $booking['formattedTime'] = $showDateTime->format('H:i');
                $booking['formattedDateTime'] = $showDateTime->format('d/m/Y H:i');
                
                // Format giá
                $booking['formattedPrice'] = number_format($booking['totalPrice'], 0, ',', '.');
                
                // Kiểm tra trạng thái
                $booking['canCancel'] = false;
                if ($booking['paymentStatus'] === 'paid') {
                    $showTimestamp = $showDateTime->getTimestamp();
                    $minCancelTime = $showTimestamp - (2 * 60 * 60); // 2 giờ trước
                    if (time() < $minCancelTime) {
                        $booking['canCancel'] = true;
                    }
                }
                
                // Status label
                $statusLabels = [
                    'pending' => 'Chờ thanh toán',
                    'paid' => 'Đã thanh toán',
                    'cancelled' => 'Đã hủy',
                    'expired' => 'Hết hạn'
                ];
                $booking['statusLabel'] = $statusLabels[$booking['paymentStatus']] ?? 'Không xác định';
            }
            
            echo json_encode([
                'success' => true,
                'bookings' => $bookings,
                'total' => count($bookings)
            ]);
            break;
            
        case 'get_booking_detail':
            // Lấy chi tiết 1 booking - check cả userID và user_id
            $userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);
            
            if (!$userID) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Chưa đăng nhập'
                ]);
                exit;
            }
            
            $bookingID = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            
            if ($bookingID <= 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'ID không hợp lệ'
                ]);
                exit;
            }
            
            $booking = get_booking_with_details($bookingID);
            
            if (!$booking) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Không tìm thấy booking'
                ]);
                exit;
            }
            
            // Kiểm tra quyền truy cập
            if ($booking['userID'] != $userID) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Không có quyền truy cập'
                ]);
                exit;
            }
            
            // Format dữ liệu
            $showDateTime = new DateTime($booking['showDate'] . ' ' . $booking['showTime']);
            $booking['formattedDateTime'] = $showDateTime->format('d/m/Y H:i');
            $booking['formattedPrice'] = number_format($booking['totalPrice'], 0, ',', '.');
            
            echo json_encode([
                'success' => true,
                'booking' => $booking
            ]);
            break;
            
        case 'cancel_booking':
            // Hủy booking - check cả userID và user_id
            $userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);
            
            if (!$userID) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Chưa đăng nhập'
                ]);
                exit;
            }
            
            $bookingID = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
            
            if ($bookingID <= 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'ID không hợp lệ'
                ]);
                exit;
            }
            
            $result = cancel_booking($bookingID, $userID);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Hủy vé thành công'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Không thể hủy vé (đã quá thời gian hoặc không tồn tại)'
                ]);
            }
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Action không hợp lệ'
            ]);
    }
    exit;
}
?>
