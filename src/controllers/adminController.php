<?php
// Admin Controller - xử lý các request từ trang admin
// ĐÃ SỬA: Dùng showDate và showTime thay vì showtimeDate
session_start();
require_once __DIR__ . '/../models/database.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user']['roleID']) || $_SESSION['user']['roleID'] != 1) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

try {
    switch ($action) {
        case 'getStats':
            getStats();
            break;
        case 'getRevenueChart':
            getRevenueChart();
            break;
        case 'getShowtimes':
            getShowtimes();
            break;
        case 'getShowtime':
            getShowtime();
            break;
        case 'addShowtime':
            addShowtime();
            break;
        case 'updateShowtime':
            updateShowtime();
            break;
        case 'deleteShowtime':
            deleteShowtime();
            break;
        case 'getTopMovies':
            getTopMovies();
            break;
        case 'getRecentBookings':
            getRecentBookings();
            break;
        case 'getMovies':
            getMovies();
            break;
        case 'getRooms':
            getRooms();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// Lấy thống kê tổng quan
function getStats() {
    global $db;
    
    // Doanh thu tháng này
    $sql = "SELECT COALESCE(SUM(totalPrice), 0) as totalRevenue 
            FROM bookings 
            WHERE paymentStatus = 'paid' 
            AND MONTH(bookingDate) = MONTH(CURRENT_DATE())
            AND YEAR(bookingDate) = YEAR(CURRENT_DATE())";
    $stmt = $db->query($sql);
    $totalRevenue = $stmt->fetch(PDO::FETCH_ASSOC)['totalRevenue'];
    
    // Tổng số vé đã bán (tháng này)
    $sql = "SELECT COUNT(*) as totalBookings 
            FROM bookings 
            WHERE paymentStatus = 'paid'
            AND MONTH(bookingDate) = MONTH(CURRENT_DATE())
            AND YEAR(bookingDate) = YEAR(CURRENT_DATE())";
    $stmt = $db->query($sql);
    $totalBookings = $stmt->fetch(PDO::FETCH_ASSOC)['totalBookings'];
    
    // Tổng người dùng
    $sql = "SELECT COUNT(*) as totalUsers FROM `user`";
    $stmt = $db->query($sql);
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['totalUsers'];
    
    // Tổng phim đang chiếu
    $sql = "SELECT COUNT(*) as totalMovies FROM movie WHERE movieStatus = 'now_showing'";
    $stmt = $db->query($sql);
    $totalMovies = $stmt->fetch(PDO::FETCH_ASSOC)['totalMovies'];
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'totalRevenue' => (float)$totalRevenue,
            'totalBookings' => (int)$totalBookings,
            'totalUsers' => (int)$totalUsers,
            'totalMovies' => (int)$totalMovies
        ]
    ]);
}

// Lấy dữ liệu biểu đồ doanh thu
function getRevenueChart() {
    global $db;
    
    $period = isset($_GET['period']) ? (int)$_GET['period'] : 30;
    
    $sql = "SELECT DATE(bookingDate) as date, SUM(totalPrice) as revenue
            FROM bookings
            WHERE paymentStatus = 'paid'
            AND bookingDate >= DATE_SUB(CURRENT_DATE(), INTERVAL :period DAY)
            GROUP BY DATE(bookingDate)
            ORDER BY date ASC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute(['period' => $period]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $labels = [];
    $values = [];
    
    foreach ($data as $row) {
        $labels[] = date('d/m', strtotime($row['date']));
        $values[] = (float)$row['revenue'];
    }
    
    echo json_encode([
        'success' => true,
        'labels' => $labels,
        'values' => $values
    ]);
}

// Lấy danh sách suất chiếu
function getShowtimes() {
    global $db;
    
    $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    $roomID = isset($_GET['roomID']) ? $_GET['roomID'] : '';
    
    $sql = "SELECT s.*, 
            CONCAT(s.showDate, ' ', s.showTime) as showtimeDate,
            m.title as movieTitle, r.roomName, r.roomType, r.totalSeats,
            COUNT(bs.seatID) as bookedSeats,
            (r.totalSeats - COUNT(bs.seatID)) as availableSeats
            FROM showtimes s
            JOIN movie m ON s.movieID = m.movieID
            JOIN rooms r ON s.roomID = r.roomID
            LEFT JOIN bookingseats bs ON bs.bookingID IN (
                SELECT b.bookingID FROM bookings b WHERE b.showtimeID = s.showtimeID AND b.paymentStatus = 'paid'
            )
            WHERE s.showDate = :date";
    
    if (!empty($roomID)) {
        $sql .= " AND s.roomID = :roomID";
    }
    
    $sql .= " GROUP BY s.showtimeID ORDER BY s.showTime ASC";
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':date', $date);
    if (!empty($roomID)) {
        $stmt->bindParam(':roomID', $roomID, PDO::PARAM_INT);
    }
    $stmt->execute();
    
    $showtimes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'showtimes' => $showtimes
    ]);
}

// Lấy thông tin 1 suất chiếu
function getShowtime() {
    global $db;
    
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    $sql = "SELECT *, CONCAT(showDate, ' ', showTime) as showtimeDate FROM showtimes WHERE showtimeID = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['id' => $id]);
    $showtime = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($showtime) {
        echo json_encode([
            'success' => true,
            'showtime' => $showtime
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Showtime not found'
        ]);
    }
}

// Thêm suất chiếu mới
function addShowtime() {
    global $db;
    
    $movieID = $_POST['movieID'];
    $roomID = $_POST['roomID'];
    $showDate = $_POST['showDate'];
    $showTime = $_POST['showTime'];
    $basePrice = $_POST['basePrice'];
    $status = isset($_POST['showtimeStatus']) ? $_POST['showtimeStatus'] : 'available';
    
    // Kiểm tra trùng lịch
    $sql = "SELECT COUNT(*) as count FROM showtimes 
            WHERE roomID = :roomID 
            AND showDate = :showDate
            AND ABS(TIMESTAMPDIFF(MINUTE, showTime, :showTime)) < 180";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        'roomID' => $roomID,
        'showDate' => $showDate,
        'showTime' => $showTime
    ]);
    
    if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Phòng chiếu đã có suất chiếu trong khoảng thời gian này'
        ]);
        return;
    }
    
    $sql = "INSERT INTO showtimes (movieID, roomID, showDate, showTime, basePrice, status) 
            VALUES (:movieID, :roomID, :showDate, :showTime, :basePrice, :status)";
    
    $stmt = $db->prepare($sql);
    $result = $stmt->execute([
        'movieID' => $movieID,
        'roomID' => $roomID,
        'showDate' => $showDate,
        'showTime' => $showTime,
        'basePrice' => $basePrice,
        'status' => $status
    ]);
    
    echo json_encode([
        'success' => $result,
        'message' => $result ? 'Thêm suất chiếu thành công' : 'Lỗi khi thêm suất chiếu'
    ]);
}

// Cập nhật suất chiếu
function updateShowtime() {
    global $db;
    
    $showtimeID = $_POST['showtimeID'];
    $movieID = $_POST['movieID'];
    $roomID = $_POST['roomID'];
    $showDate = $_POST['showDate'];
    $showTime = $_POST['showTime'];
    $basePrice = $_POST['basePrice'];
    $status = isset($_POST['showtimeStatus']) ? $_POST['showtimeStatus'] : 'available';
    
    $sql = "UPDATE showtimes 
            SET movieID = :movieID, roomID = :roomID, showDate = :showDate, showTime = :showTime,
                basePrice = :basePrice, status = :status 
            WHERE showtimeID = :showtimeID";
    
    $stmt = $db->prepare($sql);
    $result = $stmt->execute([
        'movieID' => $movieID,
        'roomID' => $roomID,
        'showDate' => $showDate,
        'showTime' => $showTime,
        'basePrice' => $basePrice,
        'status' => $status,
        'showtimeID' => $showtimeID
    ]);
    
    echo json_encode([
        'success' => $result,
        'message' => $result ? 'Cập nhật suất chiếu thành công' : 'Lỗi khi cập nhật suất chiếu'
    ]);
}

// Xóa suất chiếu
function deleteShowtime() {
    global $db;
    
    $showtimeID = $_POST['showtimeID'];
    
    // Kiểm tra xem có vé đã đặt chưa
    $sql = "SELECT COUNT(*) as count FROM bookings WHERE showtimeID = :showtimeID AND paymentStatus = 'paid'";
    $stmt = $db->prepare($sql);
    $stmt->execute(['showtimeID' => $showtimeID]);
    
    if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Không thể xóa suất chiếu đã có người đặt vé'
        ]);
        return;
    }
    
    $sql = "DELETE FROM showtimes WHERE showtimeID = :showtimeID";
    $stmt = $db->prepare($sql);
    $result = $stmt->execute(['showtimeID' => $showtimeID]);
    
    echo json_encode([
        'success' => $result,
        'message' => $result ? 'Xóa suất chiếu thành công' : 'Lỗi khi xóa suất chiếu'
    ]);
}

// Lấy top phim bán chạy
function getTopMovies() {
    global $db;
    
    $sql = "SELECT m.movieID, m.title, COUNT(b.bookingID) as totalBookings, 
            SUM(b.totalPrice) as totalRevenue
            FROM movie m
            JOIN showtimes s ON m.movieID = s.movieID
            JOIN bookings b ON s.showtimeID = b.showtimeID
            WHERE b.paymentStatus = 'paid'
            AND MONTH(b.bookingDate) = MONTH(CURRENT_DATE())
            AND YEAR(b.bookingDate) = YEAR(CURRENT_DATE())
            GROUP BY m.movieID
            ORDER BY totalBookings DESC
            LIMIT 5";
    
    $stmt = $db->query($sql);
    $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'movies' => $movies
    ]);
}

// Lấy đặt vé gần đây
function getRecentBookings() {
    global $db;
    
    $sql = "SELECT b.*, u.username, m.title as movieTitle
            FROM bookings b
            JOIN `user` u ON b.userID = u.userID
            JOIN showtimes s ON b.showtimeID = s.showtimeID
            JOIN movie m ON s.movieID = m.movieID
            ORDER BY b.bookingDate DESC
            LIMIT 10";
    
    $stmt = $db->query($sql);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'bookings' => $bookings
    ]);
}

// Lấy danh sách phim
function getMovies() {
    global $db;
    
    $sql = "SELECT movieID, title FROM movie WHERE movieStatus IN ('now_showing', 'coming_soon') ORDER BY title";
    $stmt = $db->query($sql);
    $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'movies' => $movies
    ]);
}

// Lấy danh sách phòng
function getRooms() {
    global $db;
    
    $sql = "SELECT roomID, roomName, roomType FROM rooms WHERE status = 'active' ORDER BY roomName";
    $stmt = $db->query($sql);
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'rooms' => $rooms
    ]);
}
?>
