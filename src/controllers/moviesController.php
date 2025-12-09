<?php
/**
 * Movies Controller
 * Xử lý AJAX requests cho trang danh sách phim
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../models/movie_db.php';

try {
    // Lấy action từ request
    $action = $_GET['action'] ?? 'get_movies';
    
    switch ($action) {
        case 'get_movies':
            // Lấy filter (now-showing hoặc coming-soon)
            $filter = $_GET['filter'] ?? 'now-showing';
            
            if ($filter === 'now-showing') {
                $movies = get_now_showing_movies();
            } elseif ($filter === 'coming-soon') {
                $movies = get_coming_soon_movies();
            } else {
                $movies = get_all_movies();
            }
            
            echo json_encode([
                'success' => true,
                'movies' => $movies,
                'count' => count($movies)
            ]);
            break;
            
        case 'search_movies':
            $keyword = $_GET['keyword'] ?? '';
            
            // Debug logging
            error_log("Search Movies - Keyword: " . $keyword);
            
            if (empty($keyword)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Vui lòng nhập từ khóa tìm kiếm'
                ]);
                exit;
            }
            
            $movies = search_movies($keyword);
            
            // Debug logging
            error_log("Search Movies - Results count: " . count($movies));
            
            echo json_encode([
                'success' => true,
                'movies' => $movies,
                'count' => count($movies),
                'keyword' => $keyword
            ]);
            break;
            
        case 'get_movie_by_id':
            $movieID = $_GET['id'] ?? 0;
            
            if ($movieID <= 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'ID phim không hợp lệ'
                ]);
                exit;
            }
            
            $movie = get_movie_by_id($movieID);
            
            if ($movie) {
                echo json_encode([
                    'success' => true,
                    'movie' => $movie
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Không tìm thấy phim'
                ]);
            }
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Action không hợp lệ'
            ]);
            break;
    }
    
} catch (Exception $e) {
    error_log('Movies Controller Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
    ]);
}
?>
