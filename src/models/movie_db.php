<?php
/**
 * Movie Database Functions
 * Quản lý tất cả các thao tác liên quan đến phim
 */

require_once 'database.php';

// ==================== CREATE ====================

/**
 * Thêm phim mới vào database
 * @param array $data - Thông tin phim (title, genre, duration, description, rating, movieStatus, posterURL, posterHorizontalURL, trailerURL, author, releaseDate)
 * @return int|false - movieID của phim mới tạo hoặc false nếu thất bại
 */
function create_movie($data) {
    global $db;
    
    $sql = 'INSERT INTO movie (title, genre, duration, description, rating, movieStatus, posterURL, posterHorizontalURL, trailerURL, author, releaseDate) 
            VALUES (:title, :genre, :duration, :description, :rating, :movieStatus, :posterURL, :posterHorizontalURL, :trailerURL, :author, :releaseDate)';
    
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':title', $data['title']);
    $stmt->bindValue(':genre', $data['genre']);
    $stmt->bindValue(':duration', $data['duration'], PDO::PARAM_INT);
    $stmt->bindValue(':description', $data['description']);
    $stmt->bindValue(':rating', $data['rating'], PDO::PARAM_STR);
    $stmt->bindValue(':movieStatus', $data['movieStatus']);
    $stmt->bindValue(':posterURL', $data['posterURL']);
    $stmt->bindValue(':posterHorizontalURL', $data['posterHorizontalURL'] ?? null);
    $stmt->bindValue(':trailerURL', $data['trailerURL']);
    $stmt->bindValue(':author', $data['author']);
    $stmt->bindValue(':releaseDate', $data['releaseDate'] ?? null);
    
    if ($stmt->execute()) {
        return $db->lastInsertId();
    }
    return false;
}

// ==================== READ ====================

/**
 * Lấy thông tin phim theo ID
 * @param int $movieID
 * @return array|false - Thông tin phim hoặc false nếu không tìm thấy
 */
function get_movie_by_id($movieID) {
    global $db;
    
    $sql = 'SELECT * FROM movie WHERE movieID = :movieID LIMIT 1';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':movieID', $movieID, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Lấy tất cả phim
 * @return array - Danh sách tất cả phim
 */
function get_all_movies() {
    global $db;
    
    $sql = 'SELECT * FROM movie ORDER BY movieID DESC';
    $stmt = $db->query($sql);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Lấy phim theo trạng thái (đang chiếu, sắp chiếu, ngừng chiếu)
 * @param string $status - 'now_showing', 'coming_soon', 'stopped'
 * @return array - Danh sách phim theo trạng thái
 */
function get_movies_by_status($status) {
    global $db;
    
    $sql = 'SELECT * FROM movie WHERE movieStatus = :status ORDER BY rating DESC';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':status', $status);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Lấy phim đang chiếu
 * @return array - Danh sách phim đang chiếu
 */
function get_now_showing_movies() {
    return get_movies_by_status('now_showing');
}

/**
 * Lấy phim sắp chiếu
 * @return array - Danh sách phim sắp chiếu
 */
function get_coming_soon_movies() {
    return get_movies_by_status('coming_soon');
}

/**
 * Lấy phim theo thể loại
 * @param string $genre - Thể loại phim
 * @return array - Danh sách phim theo thể loại
 */
function get_movies_by_genre($genre) {
    global $db;
    
    $sql = 'SELECT * FROM movie WHERE genre LIKE :genre ORDER BY rating DESC';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':genre', '%' . $genre . '%');
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Tìm kiếm phim theo tên
 * @param string $keyword - Từ khóa tìm kiếm
 * @return array - Danh sách phim tìm được
 */
function search_movies($keyword) {
    global $db;
    
    $sql = 'SELECT * FROM movie 
            WHERE title LIKE :keyword 
            OR description LIKE :keyword 
            OR author LIKE :keyword 
            ORDER BY rating DESC';
    
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':keyword', '%' . $keyword . '%');
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Lấy phim HOT (rating cao nhất)
 * @param int $limit - Số lượng phim cần lấy
 * @return array - Danh sách phim HOT
 */
function get_hot_movies($limit = 6) {
    global $db;
    
    $sql = 'SELECT * FROM movie 
            WHERE movieStatus = "now_showing" 
            ORDER BY rating DESC 
            LIMIT :limit';
    
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Lấy phim mới nhất
 * @param int $limit - Số lượng phim cần lấy
 * @return array - Danh sách phim mới nhất
 */
function get_latest_movies($limit = 10) {
    global $db;
    
    $sql = 'SELECT * FROM movie ORDER BY movieID DESC LIMIT :limit';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Lấy phim ngẫu nhiên (cho banner/slider)
 * @param int $limit - Số lượng phim cần lấy
 * @return array - Danh sách phim ngẫu nhiên
 */
function get_random_movies($limit = 5) {
    global $db;
    
    $sql = 'SELECT * FROM movie 
            WHERE movieStatus = "now_showing" 
            ORDER BY RAND() 
            LIMIT :limit';
    
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Lấy phim theo khoảng rating
 * @param float $minRating - Rating tối thiểu
 * @param float $maxRating - Rating tối đa
 * @return array - Danh sách phim trong khoảng rating
 */
function get_movies_by_rating_range($minRating, $maxRating = 5.0) {
    global $db;
    
    $sql = 'SELECT * FROM movie 
            WHERE rating BETWEEN :minRating AND :maxRating 
            ORDER BY rating DESC';
    
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':minRating', $minRating);
    $stmt->bindValue(':maxRating', $maxRating);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Đếm tổng số phim
 * @return int - Tổng số phim
 */
function count_total_movies() {
    global $db;
    
    $sql = 'SELECT COUNT(*) as total FROM movie';
    $stmt = $db->query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result['total'];
}

/**
 * Đếm số phim theo trạng thái
 * @param string $status - Trạng thái phim
 * @return int - Số lượng phim
 */
function count_movies_by_status($status) {
    global $db;
    
    $sql = 'SELECT COUNT(*) as total FROM movie WHERE movieStatus = :status';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':status', $status);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result['total'];
}

/**
 * Lấy phim sắp chiếu sắp xếp theo ngày phát hành
 * @param int $limit - Số lượng phim cần lấy
 * @return array - Danh sách phim sắp chiếu
 */
function get_upcoming_movies_by_date($limit = 10) {
    global $db;
    
    $sql = 'SELECT * FROM movie 
            WHERE movieStatus = "coming_soon" 
            AND releaseDate IS NOT NULL 
            ORDER BY releaseDate ASC 
            LIMIT :limit';
    
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Lấy phim mới phát hành (trong vòng X ngày gần đây)
 * @param int $days - Số ngày gần đây (mặc định 30 ngày)
 * @return array - Danh sách phim mới phát hành
 */
function get_newly_released_movies($days = 30) {
    global $db;
    
    $sql = 'SELECT * FROM movie 
            WHERE movieStatus = "now_showing" 
            AND releaseDate IS NOT NULL 
            AND releaseDate >= DATE_SUB(NOW(), INTERVAL :days DAY) 
            ORDER BY releaseDate DESC';
    
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':days', $days, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ==================== UPDATE ====================

/**
 * Cập nhật thông tin phim
 * @param int $movieID - ID phim cần cập nhật
 * @param array $data - Dữ liệu mới
 * @return bool - true nếu thành công, false nếu thất bại
 */
function update_movie($movieID, $data) {
    global $db;
    
    $sql = 'UPDATE movie SET 
            title = :title,
            genre = :genre,
            duration = :duration,
            description = :description,
            rating = :rating,
            movieStatus = :movieStatus,
            posterURL = :posterURL,
            posterHorizontalURL = :posterHorizontalURL,
            trailerURL = :trailerURL,
            author = :author,
            releaseDate = :releaseDate
            WHERE movieID = :movieID';
    
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':movieID', $movieID, PDO::PARAM_INT);
    $stmt->bindValue(':title', $data['title']);
    $stmt->bindValue(':genre', $data['genre']);
    $stmt->bindValue(':duration', $data['duration'], PDO::PARAM_INT);
    $stmt->bindValue(':description', $data['description']);
    $stmt->bindValue(':rating', $data['rating']);
    $stmt->bindValue(':movieStatus', $data['movieStatus']);
    $stmt->bindValue(':posterURL', $data['posterURL']);
    $stmt->bindValue(':posterHorizontalURL', $data['posterHorizontalURL'] ?? null);
    $stmt->bindValue(':trailerURL', $data['trailerURL']);
    $stmt->bindValue(':author', $data['author']);
    $stmt->bindValue(':releaseDate', $data['releaseDate'] ?? null);
    
    return $stmt->execute();
}

/**
 * Cập nhật trạng thái phim
 * @param int $movieID - ID phim
 * @param string $status - Trạng thái mới
 * @return bool - true nếu thành công
 */
function update_movie_status($movieID, $status) {
    global $db;
    
    $sql = 'UPDATE movie SET movieStatus = :status WHERE movieID = :movieID';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':movieID', $movieID, PDO::PARAM_INT);
    $stmt->bindValue(':status', $status);
    
    return $stmt->execute();
}

/**
 * Cập nhật rating phim
 * @param int $movieID - ID phim
 * @param float $rating - Rating mới
 * @return bool - true nếu thành công
 */
function update_movie_rating($movieID, $rating) {
    global $db;
    
    $sql = 'UPDATE movie SET rating = :rating WHERE movieID = :movieID';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':movieID', $movieID, PDO::PARAM_INT);
    $stmt->bindValue(':rating', $rating);
    
    return $stmt->execute();
}

/**
 * Cập nhật poster URL
 * @param int $movieID - ID phim
 * @param string $posterURL - URL poster mới
 * @return bool - true nếu thành công
 */
function update_movie_poster($movieID, $posterURL) {
    global $db;
    
    $sql = 'UPDATE movie SET posterURL = :posterURL WHERE movieID = :movieID';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':movieID', $movieID, PDO::PARAM_INT);
    $stmt->bindValue(':posterURL', $posterURL);
    
    return $stmt->execute();
}

/**
 * Cập nhật poster ngang URL
 * @param int $movieID - ID phim
 * @param string $posterHorizontalURL - URL poster ngang mới
 * @return bool - true nếu thành công
 */
function update_movie_poster_horizontal($movieID, $posterHorizontalURL) {
    global $db;
    
    $sql = 'UPDATE movie SET posterHorizontalURL = :posterHorizontalURL WHERE movieID = :movieID';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':movieID', $movieID, PDO::PARAM_INT);
    $stmt->bindValue(':posterHorizontalURL', $posterHorizontalURL);
    
    return $stmt->execute();
}

/**
 * Cập nhật ngày phát hành
 * @param int $movieID - ID phim
 * @param string $releaseDate - Ngày phát hành (YYYY-MM-DD hoặc YYYY-MM-DD HH:MM:SS)
 * @return bool - true nếu thành công
 */
function update_movie_release_date($movieID, $releaseDate) {
    global $db;
    
    $sql = 'UPDATE movie SET releaseDate = :releaseDate WHERE movieID = :movieID';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':movieID', $movieID, PDO::PARAM_INT);
    $stmt->bindValue(':releaseDate', $releaseDate);
    
    return $stmt->execute();
}

// ==================== DELETE ====================

/**
 * Xóa phim theo ID
 * @param int $movieID - ID phim cần xóa
 * @return bool - true nếu thành công
 */
function delete_movie($movieID) {
    global $db;
    
    $sql = 'DELETE FROM movie WHERE movieID = :movieID';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':movieID', $movieID, PDO::PARAM_INT);
    
    return $stmt->execute();
}

/**
 * Xóa phim theo trạng thái (dùng để dọn dẹp database)
 * @param string $status - Trạng thái phim cần xóa
 * @return bool - true nếu thành công
 */
function delete_movies_by_status($status) {
    global $db;
    
    $sql = 'DELETE FROM movie WHERE movieStatus = :status';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':status', $status);
    
    return $stmt->execute();
}

// ==================== UTILITY FUNCTIONS ====================

/**
 * Kiểm tra phim có tồn tại không
 * @param int $movieID - ID phim
 * @return bool - true nếu tồn tại
 */
function movie_exists($movieID) {
    global $db;
    
    $sql = 'SELECT COUNT(*) as count FROM movie WHERE movieID = :movieID';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':movieID', $movieID, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result['count'] > 0;
}

/**
 * Lấy tất cả thể loại phim (unique)
 * @return array - Danh sách các thể loại
 */
function get_all_genres() {
    global $db;
    
    $sql = 'SELECT DISTINCT genre FROM movie ORDER BY genre ASC';
    $stmt = $db->query($sql);
    
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Lấy tất cả tác giả/đạo diễn (unique)
 * @return array - Danh sách tác giả
 */
function get_all_authors() {
    global $db;
    
    $sql = 'SELECT DISTINCT author FROM movie ORDER BY author ASC';
    $stmt = $db->query($sql);
    
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Phân trang phim
 * @param int $page - Trang hiện tại (bắt đầu từ 1)
 * @param int $perPage - Số phim mỗi trang
 * @param string $status - Lọc theo trạng thái (optional)
 * @return array - ['movies' => [], 'total' => int, 'pages' => int]
 */
function get_movies_paginated($page = 1, $perPage = 12, $status = null) {
    global $db;
    
    $offset = ($page - 1) * $perPage;
    
    // Count total
    if ($status) {
        $countSql = 'SELECT COUNT(*) as total FROM movie WHERE movieStatus = :status';
        $stmt = $db->prepare($countSql);
        $stmt->bindValue(':status', $status);
        $stmt->execute();
    } else {
        $countSql = 'SELECT COUNT(*) as total FROM movie';
        $stmt = $db->query($countSql);
    }
    $totalResult = $stmt->fetch(PDO::FETCH_ASSOC);
    $total = $totalResult['total'];
    
    // Get movies
    if ($status) {
        $sql = 'SELECT * FROM movie WHERE movieStatus = :status ORDER BY movieID DESC LIMIT :limit OFFSET :offset';
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        $sql = 'SELECT * FROM movie ORDER BY movieID DESC LIMIT :limit OFFSET :offset';
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
    }
    
    $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        'movies' => $movies,
        'total' => $total,
        'pages' => ceil($total / $perPage),
        'current_page' => $page
    ];
}

/**
 * Format thời lượng phim (phút -> giờ phút)
 * @param int $duration - Thời lượng (phút)
 * @return string - "2h 30m"
 */
function format_duration($duration) {
    $hours = floor($duration / 60);
    $minutes = $duration % 60;
    
    if ($hours > 0) {
        return $hours . 'h ' . $minutes . 'm';
    }
    return $minutes . 'm';
}

/**
 * Format ngày phát hành
 * @param string $releaseDate - Ngày phát hành (DATETIME từ database)
 * @param string $format - Định dạng output (mặc định: 'd/m/Y')
 * @return string - Ngày đã format hoặc 'Chưa xác định'
 */
function format_release_date($releaseDate, $format = 'd/m/Y') {
    if (empty($releaseDate)) {
        return 'Chưa xác định';
    }
    
    $date = new DateTime($releaseDate);
    return $date->format($format);
}

/**
 * Kiểm tra phim có phải MỚI không (phát hành trong vòng 14 ngày)
 * @param string $releaseDate - Ngày phát hành
 * @return bool - true nếu là phim mới
 */
function is_new_movie($releaseDate) {
    if (empty($releaseDate)) {
        return false;
    }
    
    $release = new DateTime($releaseDate);
    $now = new DateTime();
    $diff = $now->diff($release);
    
    return $diff->days <= 14 && $release <= $now;
}

/**
 * Tính countdown đến ngày phát hành
 * @param string $releaseDate - Ngày phát hành
 * @return array|null - ['days' => int, 'hours' => int, 'minutes' => int, 'seconds' => int, 'total_seconds' => int] hoặc null nếu đã phát hành
 */
function get_countdown_to_release($releaseDate) {
    if (empty($releaseDate)) {
        return null;
    }
    
    $release = new DateTime($releaseDate);
    $now = new DateTime();
    
    // Nếu đã phát hành (quá khứ), return null
    if ($release <= $now) {
        return null;
    }
    
    $diff = $now->diff($release);
    
    return [
        'days' => $diff->days,
        'hours' => $diff->h,
        'minutes' => $diff->i,
        'seconds' => $diff->s,
        'total_seconds' => $diff->days * 86400 + $diff->h * 3600 + $diff->i * 60 + $diff->s,
        'formatted' => $diff->days . ' ngày ' . $diff->h . ' giờ ' . $diff->i . ' phút'
    ];
}

/**
 * Lấy countdown text ngắn gọn
 * @param string $releaseDate - Ngày phát hành
 * @return string - "Còn 5 ngày" hoặc "Đã phát hành"
 */
function get_countdown_text($releaseDate) {
    $countdown = get_countdown_to_release($releaseDate);
    
    if ($countdown === null) {
        return 'Đã phát hành';
    }
    
    if ($countdown['days'] > 0) {
        return 'Còn ' . $countdown['days'] . ' ngày';
    } elseif ($countdown['hours'] > 0) {
        return 'Còn ' . $countdown['hours'] . ' giờ';
    } else {
        return 'Sắp chiếu';
    }
}

/**
 * Lấy YouTube video ID từ URL
 * @param string $url - URL YouTube (nhiều format khác nhau)
 * @return string|null - Video ID hoặc null nếu không hợp lệ
 */
function get_youtube_video_id($url) {
    if (empty($url)) {
        return null;
    }
    
    // Patterns cho các format URL YouTube khác nhau
    $patterns = [
        '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/',           // https://www.youtube.com/watch?v=VIDEO_ID
        '/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/',             // https://www.youtube.com/embed/VIDEO_ID
        '/youtu\.be\/([a-zA-Z0-9_-]+)/',                       // https://youtu.be/VIDEO_ID
        '/youtube\.com\/v\/([a-zA-Z0-9_-]+)/',                 // https://www.youtube.com/v/VIDEO_ID
        '/youtube\.com\/watch\?.*&v=([a-zA-Z0-9_-]+)/',        // https://www.youtube.com/watch?feature=...&v=VIDEO_ID
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
    }
    
    return null;
}

/**
 * Lấy YouTube embed URL từ video ID hoặc URL
 * @param string $urlOrId - URL YouTube hoặc video ID
 * @return string|null - Embed URL hoặc null
 */
function get_youtube_embed_url($urlOrId) {
    if (empty($urlOrId)) {
        return null;
    }
    
    // Nếu đã là video ID (không có http/https)
    if (!preg_match('/^https?:\/\//', $urlOrId)) {
        return 'https://www.youtube.com/embed/' . $urlOrId . '?autoplay=1&rel=0';
    }
    
    // Lấy video ID từ URL
    $videoId = get_youtube_video_id($urlOrId);
    
    if ($videoId) {
        return 'https://www.youtube.com/embed/' . $videoId . '?autoplay=1&rel=0';
    }
    
    return null;
}

/**
 * Validate dữ liệu phim
 * @param array $data - Dữ liệu phim
 * @return array - ['valid' => bool, 'errors' => []]
 */
function validate_movie_data($data) {
    $errors = [];
    
    if (empty($data['title'])) {
        $errors[] = 'Tên phim không được để trống';
    }
    
    if (empty($data['genre'])) {
        $errors[] = 'Thể loại không được để trống';
    }
    
    if (!isset($data['duration']) || $data['duration'] <= 0) {
        $errors[] = 'Thời lượng phải lớn hơn 0';
    }
    
    if (isset($data['rating']) && ($data['rating'] < 0 || $data['rating'] > 10)) {
        $errors[] = 'Rating phải từ 0 đến 10';
    }
    
    $validStatuses = ['now_showing', 'coming_soon', 'stopped'];
    if (!in_array($data['movieStatus'], $validStatuses)) {
        $errors[] = 'Trạng thái phim không hợp lệ';
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

?>
