<?php
/**
 * Home Controller
 * Xử lý dữ liệu cho trang chủ
 */

require_once __DIR__ . '/../models/database.php';
require_once __DIR__ . '/../models/movie_db.php';

// Lấy phim cho hero banner (5 phim ngẫu nhiên đang chiếu)
$bannerMovies = get_random_movies(5);

// Lấy phim đang chiếu (top 6 phim HOT)
$nowShowingMovies = get_hot_movies(6);

// Lấy phim sắp chiếu theo ngày phát hành (8 phim)
$comingSoonMovies = get_upcoming_movies_by_date(8);

// Nếu không đủ phim có releaseDate, lấy thêm từ get_coming_soon_movies()
if (count($comingSoonMovies) < 8) {
    $additionalMovies = get_coming_soon_movies();
    $comingSoonMovies = array_merge($comingSoonMovies, $additionalMovies);
    $comingSoonMovies = array_unique($comingSoonMovies, SORT_REGULAR);
    $comingSoonMovies = array_slice($comingSoonMovies, 0, 8);
}

// Lấy danh sách phim cho dropdown booking (chỉ phim đang chiếu)
$bookingMovies = get_now_showing_movies();

// Thống kê
$totalMovies = count_total_movies();
$nowShowingCount = count_movies_by_status('now_showing');
$comingSoonCount = count_movies_by_status('coming_soon');

?>
