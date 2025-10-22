<?php
/**
 * Controller: Xử lý AJAX cho chức năng showtime
 */

session_start();
header('Content-Type: application/json');

// Require database connection and model
require_once __DIR__ . '/../models/database.php';
require_once __DIR__ . '/../models/showtime_db.php';

// Lấy action từ request
$action = $_POST['action'] ?? $_GET['action'] ?? null;

if (!$action) {
    echo json_encode(['success' => false, 'message' => 'No action specified']);
    exit();
}

switch ($action) {
    case 'get_dates':
        get_available_dates();
        break;
    
    case 'get_showtimes':
        get_showtimes_by_date();
        break;
    
    case 'get_showtime_detail':
        get_showtime_detail();
        break;
    
    case 'check_availability':
        check_availability();
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

/**
 * Lấy danh sách ngày có lịch chiếu
 */
function get_available_dates() {
    $movieID = $_GET['movieID'] ?? null;
    
    if (!$movieID) {
        echo json_encode(['success' => false, 'message' => 'Movie ID is required']);
        return;
    }
    
    try {
        $dates = get_available_dates_by_movie($movieID);
        echo json_encode([
            'success' => true,
            'dates' => $dates
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching dates: ' . $e->getMessage()
        ]);
    }
}

/**
 * Lấy danh sách suất chiếu theo ngày
 */
function get_showtimes_by_date() {
    $movieID = $_GET['movieID'] ?? null;
    $showDate = $_GET['showDate'] ?? null;
    
    if (!$movieID || !$showDate) {
        echo json_encode(['success' => false, 'message' => 'Movie ID and show date are required']);
        return;
    }
    
    try {
        $showtimes = get_showtimes_by_movie($movieID, $showDate);
        
        // Format dữ liệu trả về
        $formatted = array_map(function($showtime) {
            return [
                'showtimeID' => $showtime['showtimeID'],
                'showTime' => date('H:i', strtotime($showtime['showTime'])),
                'roomName' => $showtime['roomName'],
                'roomType' => $showtime['roomType'],
                'availableSeats' => $showtime['availableSeats'],
                'totalSeats' => $showtime['totalSeats'],
                'basePrice' => $showtime['basePrice'],
                'status' => $showtime['status']
            ];
        }, $showtimes);
        
        echo json_encode([
            'success' => true,
            'showtimes' => $formatted
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching showtimes: ' . $e->getMessage()
        ]);
    }
}

/**
 * Lấy chi tiết một suất chiếu
 */
function get_showtime_detail() {
    $showtimeID = $_GET['showtimeID'] ?? null;
    
    if (!$showtimeID) {
        echo json_encode(['success' => false, 'message' => 'Showtime ID is required']);
        return;
    }
    
    try {
        $showtime = get_showtime_by_id($showtimeID);
        
        if (!$showtime) {
            echo json_encode(['success' => false, 'message' => 'Showtime not found']);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'showtime' => $showtime
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching showtime: ' . $e->getMessage()
        ]);
    }
}

/**
 * Kiểm tra tình trạng còn chỗ
 */
function check_availability() {
    $showtimeID = $_GET['showtimeID'] ?? null;
    
    if (!$showtimeID) {
        echo json_encode(['success' => false, 'message' => 'Showtime ID is required']);
        return;
    }
    
    try {
        $isAvailable = check_showtime_availability($showtimeID);
        $showtime = get_showtime_by_id($showtimeID);
        
        echo json_encode([
            'success' => true,
            'available' => $isAvailable,
            'availableSeats' => $showtime['availableSeats'] ?? 0,
            'status' => $showtime['status']
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error checking availability: ' . $e->getMessage()
        ]);
    }
}
