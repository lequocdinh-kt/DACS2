<?php
/**
 * DEBUG FILE - Kiểm tra dữ liệu phim
 * Chạy file này để xem phim có trong database không
 */

require_once __DIR__ . '/src/models/database.php';
require_once __DIR__ . '/src/models/movie_db.php';

echo "<h1>🔍 Debug: Kiểm tra dữ liệu phim</h1>";
echo "<hr>";

// 1. Kiểm tra kết nối database
echo "<h2>1. Kết nối Database:</h2>";
try {
    $db->query("SELECT 1");
    echo "✅ Database đã kết nối thành công!<br>";
} catch (Exception $e) {
    echo "❌ Lỗi kết nối: " . $e->getMessage() . "<br>";
}

// 2. Đếm tổng số phim
echo "<h2>2. Tổng số phim:</h2>";
$total = count_total_movies();
echo "Có <strong>{$total}</strong> phim trong database<br>";

// 3. Kiểm tra movieStatus trong database
echo "<h2>3. Giá trị movieStatus trong database:</h2>";
$sql = "SELECT DISTINCT movieStatus FROM Movie";
$stmt = $db->query($sql);
$statuses = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "<ul>";
foreach ($statuses as $status) {
    echo "<li><code>'{$status}'</code></li>";
}
echo "</ul>";
echo "<strong>⚠️ Chú ý:</strong> Code PHP tìm kiếm: 'now_showing', 'coming_soon', 'stopped'<br>";

// 4. Lấy tất cả phim
echo "<h2>4. Danh sách tất cả phim:</h2>";
$allMovies = get_all_movies();
if (empty($allMovies)) {
    echo "❌ Không có phim nào trong database!<br>";
} else {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr style='background: #333; color: white;'>
            <th>ID</th>
            <th>Tên phim</th>
            <th>Thể loại</th>
            <th>Trạng thái</th>
            <th>Rating</th>
            <th>Ngày phát hành</th>
            <th>Trailer</th>
          </tr>";
    foreach ($allMovies as $movie) {
        $hasTrailer = !empty($movie['trailerURL']) ? '✅' : '❌';
        echo "<tr>
                <td>{$movie['movieID']}</td>
                <td>{$movie['title']}</td>
                <td>{$movie['genre']}</td>
                <td><strong>{$movie['movieStatus']}</strong></td>
                <td>{$movie['rating']}★</td>
                <td>{$movie['releaseDate']}</td>
                <td>{$hasTrailer}</td>
              </tr>";
    }
    echo "</table>";
}

// 5. Kiểm tra phim đang chiếu
echo "<h2>5. Phim ĐANG CHIẾU (now_showing):</h2>";
$nowShowing = get_now_showing_movies();
echo "Tìm thấy <strong>" . count($nowShowing) . "</strong> phim đang chiếu<br>";
if (empty($nowShowing)) {
    echo "❌ <strong>KHÔNG CÓ PHIM NÀO!</strong><br>";
    echo "➡️ Nguyên nhân: movieStatus trong database không phải 'now_showing'<br>";
} else {
    echo "<ul>";
    foreach ($nowShowing as $movie) {
        echo "<li>{$movie['title']} (Rating: {$movie['rating']})</li>";
    }
    echo "</ul>";
}

// 6. Kiểm tra phim sắp chiếu
echo "<h2>6. Phim SẮP CHIẾU (coming_soon):</h2>";
$comingSoon = get_coming_soon_movies();
echo "Tìm thấy <strong>" . count($comingSoon) . "</strong> phim sắp chiếu<br>";
if (!empty($comingSoon)) {
    echo "<ul>";
    foreach ($comingSoon as $movie) {
        echo "<li>{$movie['title']} (Release: {$movie['releaseDate']})</li>";
    }
    echo "</ul>";
}

// 7. Test YouTube video ID extraction
echo "<h2>7. Test YouTube Trailer URL:</h2>";
if (!empty($allMovies)) {
    foreach ($allMovies as $movie) {
        if (!empty($movie['trailerURL'])) {
            $videoId = get_youtube_video_id($movie['trailerURL']);
            $status = $videoId ? "✅ OK" : "❌ FAIL";
            echo "<strong>{$movie['title']}:</strong><br>";
            echo "- URL: <code>{$movie['trailerURL']}</code><br>";
            echo "- Video ID: <code>{$videoId}</code> {$status}<br><br>";
        }
    }
}

// 8. Giải pháp
echo "<hr>";
echo "<h2>🔧 GIẢI PHÁP:</h2>";
echo "<ol>";
echo "<li><strong>Nếu movieStatus = 'đang chiếu':</strong><br>
      Chạy lệnh: <code>UPDATE Movie SET movieStatus = 'now_showing' WHERE movieStatus = 'đang chiếu';</code></li>";
echo "<li><strong>Nếu movieStatus = 'sắp chiếu':</strong><br>
      Chạy lệnh: <code>UPDATE Movie SET movieStatus = 'coming_soon' WHERE movieStatus = 'sắp chiếu';</code></li>";
echo "<li><strong>Hoặc xem file:</strong> <code>fix_movie_status.sql</code></li>";
echo "</ol>";

echo "<hr>";
echo "<p style='color: green;'><strong>✅ Debug hoàn tất!</strong></p>";
?>
