<?php
/**
 * Schedule Controller
 * Handles schedule page data requests
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../models/database.php';
require_once __DIR__ . '/../models/movie_db.php';
require_once __DIR__ . '/../models/showtime_db.php';

// Get date from query parameter, default to today
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

try {
    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        throw new Exception('Invalid date format');
    }
    
    // Get all movies that have showtimes on the selected date
    $movies = getMoviesWithShowtimesByDate($date);
    
    if (empty($movies)) {
        echo json_encode([
            'success' => true,
            'message' => 'Không có lịch chiếu cho ngày này',
            'movies' => []
        ]);
        exit;
    }
    
    // For each movie, get its showtimes for the selected date
    foreach ($movies as &$movie) {
        $movie['showtimes'] = get_showtimes_by_movie($movie['movieID'], $date);
        
        // Get format from showtimes (2D/3D)
        if (!empty($movie['showtimes'])) {
            $formats = array_unique(array_column($movie['showtimes'], 'roomType'));
            $movie['format'] = implode(', ', $formats);
        } else {
            $movie['format'] = '2D';
        }
    }
    
    echo json_encode([
        'success' => true,
        'date' => $date,
        'movies' => $movies
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'movies' => []
    ]);
}

/**
 * Get all movies that have showtimes on a specific date
 */
function getMoviesWithShowtimesByDate($date) {
    global $db;
    
    $stmt = $db->prepare("
        SELECT DISTINCT m.*
        FROM movie m
        INNER JOIN showtimes st ON m.movieID = st.movieID
        WHERE st.showDate = ?
        AND st.status = 'available'
        AND CONCAT(st.showDate, ' ', st.showTime) > NOW()
        ORDER BY m.title ASC
    ");
    
    $stmt->execute([$date]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
