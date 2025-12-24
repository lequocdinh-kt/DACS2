<?php
/**
 * Review Controller
 * Xử lý các thao tác liên quan đến đánh giá phim
 */

// Bắt đầu output buffering để tránh output trước JSON
ob_start();

require_once __DIR__ . '/../helpers/session_helper.php';
require_once __DIR__ . '/../models/database.php';

// Clear any previous output
ob_clean();

// Set JSON response header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng đăng nhập để đánh giá phim'
    ]);
    exit;
}

$userID = $_SESSION['user']['userID'];

// Xử lý POST request (submit review)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Debug all POST data
        error_log('All POST data: ' . print_r($_POST, true));
        
        // Validate input
        $bookingID = filter_input(INPUT_POST, 'bookingID', FILTER_VALIDATE_INT);
        $movieID = filter_input(INPUT_POST, 'movieID', FILTER_VALIDATE_INT);
        $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
        $comment = trim($_POST['comment'] ?? '');
        
        // Debug log
        error_log('Review data: bookingID=' . var_export($bookingID, true) . ', movieID=' . var_export($movieID, true) . ', rating=' . var_export($rating, true) . ', comment=' . var_export($comment, true));
        
        // Check if movieID is 0 (which is invalid but filter_input returns false for 0)
        if (isset($_POST['movieID']) && $_POST['movieID'] === '0') {
            throw new Exception('Movie ID không hợp lệ (ID = 0)');
        }
        
        if ($bookingID === false || $bookingID === null || $movieID === false || $movieID === null || $rating === false || $rating === null || empty($comment)) {
            throw new Exception('Vui lòng điền đầy đủ thông tin (bookingID: ' . var_export($bookingID, true) . ', movieID: ' . var_export($movieID, true) . ', rating: ' . var_export($rating, true) . ', movieID raw: ' . ($_POST['movieID'] ?? 'not set') . ')');
        }
        
        if ($rating < 1 || $rating > 5) {
            throw new Exception('Đánh giá phải từ 1 đến 5 sao');
        }
        
        if (strlen($comment) < 10) {
            throw new Exception('Nhận xét phải có ít nhất 10 ký tự');
        }
        
        if (strlen($comment) > 1000) {
            throw new Exception('Nhận xét không được quá 1000 ký tự');
        }
        
        // Get database connection
        global $db;
        
        // Verify booking belongs to user and is paid
        $stmt = $db->prepare("
            SELECT paymentStatus 
            FROM bookings 
            WHERE bookingID = :bookingID AND userID = :userID
        ");
        $stmt->execute([
            ':bookingID' => $bookingID,
            ':userID' => $userID
        ]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$booking) {
            throw new Exception('Không tìm thấy đơn đặt vé');
        }
        
        if ($booking['paymentStatus'] !== 'paid') {
            throw new Exception('Chỉ có thể đánh giá khi đã thanh toán');
        }
        
        // Check if user already reviewed this movie
        $stmt = $db->prepare("
            SELECT reviewID 
            FROM reviews 
            WHERE userID = :userID AND movieID = :movieID
        ");
        $stmt->execute([
            ':userID' => $userID,
            ':movieID' => $movieID
        ]);
        
        if ($stmt->fetch()) {
            throw new Exception('Bạn đã đánh giá phim này rồi');
        }
        
        // Insert review
        $stmt = $db->prepare("
            INSERT INTO reviews (userID, movieID, rating, comment, createdAt, updatedAt)
            VALUES (:userID, :movieID, :rating, :comment, NOW(), NOW())
        ");
        
        $stmt->execute([
            ':userID' => $userID,
            ':movieID' => $movieID,
            ':rating' => $rating,
            ':comment' => $comment
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Đánh giá của bạn đã được gửi thành công'
        ]);
        
    } catch (PDOException $e) {
        error_log('Review DB Error: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi database: ' . $e->getMessage()
        ]);
    } catch (Exception $e) {
        error_log('Review Error: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// Xử lý GET request (lấy reviews)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $movieID = filter_input(INPUT_GET, 'movieID', FILTER_VALIDATE_INT);
        
        error_log('GET reviews - movieID: ' . var_export($movieID, true));
        
        if (!$movieID) {
            throw new Exception('Movie ID không hợp lệ');
        }
        
        global $db;
        
        // Get all reviews for movie
        $stmt = $db->prepare("
            SELECT 
                r.reviewID,
                r.rating,
                r.comment,
                r.createdAt,
                u.username as userName
            FROM reviews r
            JOIN user u ON r.userID = u.userID
            WHERE r.movieID = :movieID
            ORDER BY r.createdAt DESC
            LIMIT 50
        ");
        
        $stmt->execute([':movieID' => $movieID]);
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log('Found reviews: ' . count($reviews));
        
        // Calculate average rating
        $stmt = $db->prepare("
            SELECT AVG(rating) as avgRating, COUNT(*) as totalReviews
            FROM reviews
            WHERE movieID = :movieID
        ");
        
        $stmt->execute([':movieID' => $movieID]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        error_log('Stats: avgRating=' . var_export($stats['avgRating'], true) . ', totalReviews=' . var_export($stats['totalReviews'], true));
        
        echo json_encode([
            'success' => true,
            'reviews' => $reviews,
            'avgRating' => round($stats['avgRating'] ?? 0, 1),
            'totalReviews' => (int)($stats['totalReviews'] ?? 0)
        ]);
        
    } catch (Exception $e) {
        error_log('GET reviews error: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// Method not allowed
http_response_code(405);
echo json_encode([
    'success' => false,
    'message' => 'Method not allowed'
]);
