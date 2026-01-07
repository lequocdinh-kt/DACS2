<?php
/**
 * Controller: Xử lý AJAX cho chức năng chọn ghế
 */

session_start();
header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để tiếp tục', 'requireLogin' => true]);
    exit();
}

require_once __DIR__ . '/../models/database.php';
require_once __DIR__ . '/../models/seat_db.php';
require_once __DIR__ . '/../models/booking_db.php';

// Cleanup expired locks và bookings tự động khi truy cập controller
cleanup_expired_locks();
cleanup_expired_bookings();

$action = $_POST['action'] ?? $_GET['action'] ?? null;

if (!$action) {
    echo json_encode(['success' => false, 'message' => 'No action specified']);
    exit();
}

switch ($action) {
    case 'get_seats':
        get_seats();
        break;
    
    case 'lock_seats':
        lock_seats_action();
        break;
    
    case 'unlock_seats':
        unlock_seats_action();
        break;
    
    case 'refresh_seats':
        refresh_seats();
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

/**
 * Lấy danh sách ghế cho suất chiếu
 */
function get_seats() {
    $showtimeID = $_GET['showtimeID'] ?? null;
    
    if (!$showtimeID) {
        echo json_encode(['success' => false, 'message' => 'Showtime ID is required']);
        return;
    }
    
    try {
        $seats = get_seats_by_showtime($showtimeID);
        echo json_encode([
            'success' => true,
            'seats' => $seats
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching seats: ' . $e->getMessage()
        ]);
    }
}

/**
 * Khóa ghế khi user chọn
 */
function lock_seats_action() {
    $showtimeID = $_POST['showtimeID'] ?? null;
    $seatIDs = $_POST['seatIDs'] ?? null; // Mảng ID ghế
    $userID = $_SESSION['userID'];
    
    // Debug log
    $logFile = __DIR__ . '/../../CONSOLE_DEBUG_LOG.txt';
    $time = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$time] [LOCK_SEATS] showtimeID: $showtimeID, userID: $userID, seatIDs raw: " . var_export($seatIDs, true) . "\n", FILE_APPEND);
    
    if (!$showtimeID || !$seatIDs) {
        echo json_encode(['success' => false, 'message' => 'Missing parameters']);
        return;
    }
    
    // Parse seatIDs nếu là JSON string
    if (is_string($seatIDs)) {
        $seatIDs = json_decode($seatIDs, true);
        file_put_contents($logFile, "[$time] [LOCK_SEATS] After json_decode: " . var_export($seatIDs, true) . "\n", FILE_APPEND);
    }
    
    if (!is_array($seatIDs) || empty($seatIDs)) {
        file_put_contents($logFile, "[$time] [LOCK_SEATS] ERROR: Invalid seat IDs array\n", FILE_APPEND);
        echo json_encode(['success' => false, 'message' => 'Invalid seat IDs']);
        return;
    }
    
    file_put_contents($logFile, "[$time] [LOCK_SEATS] Seat count: " . count($seatIDs) . "\n", FILE_APPEND);
    
    try {
        // Kiểm tra ghế có sẵn không
        $available = check_seats_available($showtimeID, $seatIDs);
        
        file_put_contents($logFile, "[$time] [LOCK_SEATS] Available check result: " . ($available ? 'true' : 'false') . "\n", FILE_APPEND);
        
        if (!$available) {
            echo json_encode([
                'success' => false,
                'message' => 'Một hoặc nhiều ghế đã được đặt hoặc đang được giữ'
            ]);
            return;
        }
        
        // Unlock ghế cũ của user (nếu có)
        unlock_seats_by_user($showtimeID, $userID);
        
        // Lock ghế mới
        $locked = lock_seats($showtimeID, $seatIDs, $userID);
        
        file_put_contents($logFile, "[$time] [LOCK_SEATS] Lock result: " . ($locked ? 'success' : 'failed') . "\n", FILE_APPEND);
        
        if ($locked) {
            echo json_encode([
                'success' => true,
                'message' => 'Đã giữ ghế thành công',
                'expiresAt' => date('Y-m-d H:i:s', strtotime('+15 minutes'))
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Không thể giữ ghế'
            ]);
        }
    } catch (Exception $e) {
        file_put_contents($logFile, "[$time] [LOCK_SEATS] EXCEPTION: " . $e->getMessage() . "\n", FILE_APPEND);
        echo json_encode([
            'success' => false,
            'message' => 'Error locking seats: ' . $e->getMessage()
        ]);
    }
}

/**
 * Mở khóa ghế
 */
function unlock_seats_action() {
    $showtimeID = $_POST['showtimeID'] ?? null;
    $userID = $_SESSION['userID'];
    
    if (!$showtimeID) {
        echo json_encode(['success' => false, 'message' => 'Showtime ID is required']);
        return;
    }
    
    try {
        $unlocked = unlock_seats_by_user($showtimeID, $userID);
        
        echo json_encode([
            'success' => true,
            'message' => 'Đã hủy giữ ghế'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error unlocking seats: ' . $e->getMessage()
        ]);
    }
}

/**
 * Refresh trạng thái ghế (dọn dẹp expired locks và bookings)
 */
function refresh_seats() {
    $showtimeID = $_GET['showtimeID'] ?? null;
    
    if (!$showtimeID) {
        echo json_encode(['success' => false, 'message' => 'Showtime ID is required']);
        return;
    }
    
    try {
        // Cleanup expired locks
        cleanup_expired_locks();
        
        // Cleanup expired bookings
        cleanup_expired_bookings();
        
        // Lấy danh sách ghế mới nhất
        $seats = get_seats_by_showtime($showtimeID);
        
        echo json_encode([
            'success' => true,
            'seats' => $seats
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error refreshing seats: ' . $e->getMessage()
        ]);
    }
}
