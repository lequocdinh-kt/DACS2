<?php
/**
 * Controller: Xử lý AJAX cho chức năng chọn ghế
 */

session_start();
header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized', 'redirect' => '/src/views/login.php']);
    exit();
}

require_once __DIR__ . '/../models/database.php';
require_once __DIR__ . '/../models/seat_db.php';

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
        // Kiểm tra ghế có sẵn không
        $available = check_seats_available($showtimeID, $seatIDs);
        
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
 * Refresh trạng thái ghế (dọn dẹp expired locks)
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
