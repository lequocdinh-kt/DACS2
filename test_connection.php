<?php
/**
 * TEST DATABASE CONNECTION
 * File này giúp kiểm tra kết nối database trên cả localhost và hosting
 */

// Bật hiển thị lỗi
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Database Connection Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 30px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; border-left: 4px solid #dc3545; margin: 10px 0; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; border-left: 4px solid #17a2b8; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #4CAF50; color: white; }
        tr:hover { background: #f5f5f5; }
        .code { background: #f4f4f4; padding: 10px; border-radius: 4px; font-family: 'Courier New', monospace; margin: 10px 0; }
        ul { line-height: 1.8; }
        .badge { display: inline-block; padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .badge-success { background: #28a745; color: white; }
        .badge-error { background: #dc3545; color: white; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>🔍 Database Connection Test</h1>";

// 1. Kiểm tra môi trường
echo "<h2>1. Môi Trường (Environment)</h2>";
$currentHost = $_SERVER['HTTP_HOST'];
$isLocalhost = in_array($currentHost, ['localhost', 'localhost:3000', '127.0.0.1', 'localhost:8080', 'localhost:80']);

echo "<table>";
echo "<tr><th>Thông tin</th><th>Giá trị</th></tr>";
echo "<tr><td>HTTP_HOST</td><td><strong>$currentHost</strong></td></tr>";
echo "<tr><td>Môi trường</td><td>" . ($isLocalhost ? "<span class='badge badge-success'>LOCALHOST (XAMPP)</span>" : "<span class='badge badge-error'>HOSTING (cPanel)</span>") . "</td></tr>";
echo "<tr><td>PHP Version</td><td>" . phpversion() . "</td></tr>";
echo "<tr><td>Server Software</td><td>" . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</td></tr>";
echo "<tr><td>Document Root</td><td>" . $_SERVER['DOCUMENT_ROOT'] . "</td></tr>";
echo "</table>";

// 2. Load config
echo "<h2>2. Cấu Hình (Config)</h2>";
if (file_exists(__DIR__ . '/config.php')) {
    echo "<div class='success'>✅ File config.php tồn tại</div>";
    require_once __DIR__ . '/config.php';
    
    echo "<table>";
    echo "<tr><th>Tham số</th><th>Giá trị</th></tr>";
    echo "<tr><td>DB_HOST</td><td><strong>" . DB_HOST . "</strong></td></tr>";
    echo "<tr><td>DB_NAME</td><td><strong>" . DB_NAME . "</strong></td></tr>";
    echo "<tr><td>DB_USER</td><td><strong>" . DB_USER . "</strong></td></tr>";
    echo "<tr><td>DB_PASS</td><td>" . (DB_PASS ? '<strong>***' . substr(DB_PASS, -4) . '</strong>' : '<em>empty</em>') . "</td></tr>";
    echo "<tr><td>BASE_URL</td><td>" . BASE_URL . "</td></tr>";
    echo "<tr><td>BASE_PATH</td><td>" . BASE_PATH . "</td></tr>";
    echo "</table>";
} else {
    echo "<div class='error'>❌ File config.php KHÔNG TỒN TẠI!</div>";
    die();
}

// 3. Test Database Connection
echo "<h2>3. Kết Nối Database</h2>";

// Thử các DB_HOST khác nhau cho hosting
$possibleHosts = [
    DB_HOST,
    'localhost',
    '127.0.0.1',
    'onehost-webhn072403.000nethost.com',
];

$connected = false;
$workingHost = '';
$pdo = null;

foreach ($possibleHosts as $testHost) {
    try {
        $dsn = "mysql:host=$testHost;dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $connected = true;
        $workingHost = $testHost;
        echo "<div class='success'>";
        echo "✅ <strong>KẾT NỐI THÀNH CÔNG!</strong><br>";
        echo "📍 Host đang hoạt động: <strong>$workingHost</strong>";
        echo "</div>";
        break;
        
    } catch (PDOException $e) {
        echo "<div class='warning'>";
        echo "⚠️ Thử host: <code>$testHost</code> → <strong>FAILED</strong><br>";
        echo "Lỗi: " . $e->getMessage();
        echo "</div>";
    }
}

if (!$connected) {
    echo "<div class='error'>";
    echo "<h3>❌ KHÔNG THỂ KẾT NỐI DATABASE!</h3>";
    echo "<h4>Giải pháp:</h4>";
    if ($isLocalhost) {
        echo "<ul>";
        echo "<li>Kiểm tra XAMPP đã chạy chưa? (Apache + MySQL)</li>";
        echo "<li>Vào phpMyAdmin: <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></li>";
        echo "<li>Tạo database tên '<strong>" . DB_NAME . "</strong>'</li>";
        echo "<li>Import file: <code>database_core.sql</code></li>";
        echo "</ul>";
    } else {
        echo "<ul>";
        echo "<li>Vào cPanel → MySQL Databases</li>";
        echo "<li>Kiểm tra tên database: <strong>" . DB_NAME . "</strong></li>";
        echo "<li>Kiểm tra username: <strong>" . DB_USER . "</strong></li>";
        echo "<li>Kiểm tra password có đúng không</li>";
        echo "<li>Kiểm tra DB_HOST (thường là 'localhost' trên cPanel)</li>";
        echo "</ul>";
    }
    echo "</div>";
    die();
}

// 4. Kiểm tra Database Structure
echo "<h2>4. Cấu Trúc Database</h2>";

// Đếm số tables
$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
$tableCount = count($tables);

if ($tableCount > 0) {
    echo "<div class='success'>✅ Tìm thấy <strong>$tableCount</strong> tables</div>";
    
    echo "<table>";
    echo "<tr><th>STT</th><th>Tên Table</th><th>Số Records</th></tr>";
    
    foreach ($tables as $index => $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM `$table`");
            $count = $stmt->fetch()['total'];
            echo "<tr><td>" . ($index + 1) . "</td><td><strong>$table</strong></td><td>$count</td></tr>";
        } catch (PDOException $e) {
            echo "<tr><td>" . ($index + 1) . "</td><td><strong>$table</strong></td><td><em>Error</em></td></tr>";
        }
    }
    echo "</table>";
    
    // Kiểm tra các tables quan trọng
    $requiredTables = ['user', 'movie', 'rooms', 'seats', 'showtimes', 'bookings', 'payments'];
    $missingTables = array_diff($requiredTables, $tables);
    
    if (count($missingTables) > 0) {
        echo "<div class='warning'>";
        echo "⚠️ <strong>THIẾU TABLES:</strong> " . implode(', ', $missingTables);
        echo "<p>Bạn cần import file <code>database_core.sql</code></p>";
        echo "</div>";
    } else {
        echo "<div class='success'>✅ Tất cả tables quan trọng đã có!</div>";
    }
    
} else {
    echo "<div class='error'>";
    echo "❌ Database rỗng - CHƯA CÓ TABLE NÀO!";
    echo "<p><strong>Giải pháp:</strong> Import các file SQL sau theo thứ tự:</p>";
    echo "<ol>";
    echo "<li><code>database_core.sql</code> - Tạo cấu trúc tables</li>";
    echo "<li><code>database_sample_data.sql</code> - Thêm dữ liệu mẫu</li>";
    echo "<li><code>database_views_procedures.sql</code> - Thêm views và procedures</li>";
    echo "</ol>";
    echo "</div>";
}

// 5. Test query phim
if (in_array('movie', $tables)) {
    echo "<h2>5. Test Query Dữ Liệu</h2>";
    
    try {
        $stmt = $pdo->query("SELECT movieID, title, genre, duration, releaseDate FROM movie LIMIT 3");
        $movies = $stmt->fetchAll();
        
        if (count($movies) > 0) {
            echo "<div class='success'>✅ Lấy dữ liệu phim thành công!</div>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Tên Phim</th><th>Thể Loại</th><th>Thời Lượng</th><th>Ngày Phát Hành</th></tr>";
            foreach ($movies as $movie) {
                echo "<tr>";
                echo "<td>{$movie['movieID']}</td>";
                echo "<td><strong>{$movie['title']}</strong></td>";
                echo "<td>{$movie['genre']}</td>";
                echo "<td>{$movie['duration']} phút</td>";
                echo "<td>{$movie['releaseDate']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='warning'>⚠️ Table 'movie' không có dữ liệu. Import file <code>database_sample_data.sql</code></div>";
        }
        
    } catch (PDOException $e) {
        echo "<div class='error'>❌ Lỗi query: " . $e->getMessage() . "</div>";
    }
}

// 6. Kiểm tra file structure
echo "<h2>6. Kiểm Tra File Structure</h2>";
$files = [
    'index.php' => 'Trang chủ',
    'config.php' => 'File cấu hình',
    'src/models/database.php' => 'Database connection',
    'src/models/movie_db.php' => 'Movie model',
    'src/controllers/homeController.php' => 'Home controller',
    'src/views/home.php' => 'Home view',
    'src/views/header.php' => 'Header',
    'src/views/footer.php' => 'Footer',
];

echo "<table>";
echo "<tr><th>File</th><th>Mô tả</th><th>Trạng thái</th></tr>";
foreach ($files as $file => $desc) {
    $exists = file_exists(__DIR__ . '/' . $file);
    $status = $exists ? "<span class='badge badge-success'>✅ OK</span>" : "<span class='badge badge-error'>❌ MISSING</span>";
    echo "<tr><td><code>$file</code></td><td>$desc</td><td>$status</td></tr>";
}
echo "</table>";

// 7. Cấu hình đề xuất
echo "<h2>7. Cấu Hình Đề Xuất</h2>";

if ($workingHost !== DB_HOST) {
    echo "<div class='warning'>";
    echo "<h3>⚠️ CẢNH BÁO: DB_HOST cần được cập nhật!</h3>";
    echo "<p>Host hiện tại trong config: <code>" . DB_HOST . "</code></p>";
    echo "<p>Host đang hoạt động: <strong><code>$workingHost</code></strong></p>";
    echo "<p><strong>Giải pháp:</strong> Sửa file <code>config.php</code>, đổi dòng:</p>";
    echo "<div class='code'>";
    if (!$isLocalhost) {
        echo "define('DB_HOST', '<strong>$workingHost</strong>');  // Đổi thành host đang hoạt động";
    }
    echo "</div>";
    echo "</div>";
}

// Kết luận
echo "<h2>✅ KẾT LUẬN</h2>";
if ($connected && $tableCount > 0) {
    echo "<div class='success'>";
    echo "<h3>🎉 HỆ THỐNG SẴN SÀNG!</h3>";
    echo "<ul>";
    echo "<li>✅ Database kết nối thành công</li>";
    echo "<li>✅ Có $tableCount tables</li>";
    echo "<li>✅ File structure OK</li>";
    echo "</ul>";
    echo "<p><strong>Bạn có thể truy cập:</strong></p>";
    echo "<ul>";
    echo "<li><a href='index.php'>🏠 Trang chủ</a></li>";
    echo "<li><a href='src/views/login.php'>🔐 Đăng nhập</a></li>";
    echo "<li><a href='src/views/register.php'>📝 Đăng ký</a></li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div class='error'>";
    echo "<h3>❌ HỆ THỐNG CHƯA SẴN SÀNG</h3>";
    echo "<p>Hãy làm theo các giải pháp ở trên để hoàn tất cấu hình.</p>";
    echo "</div>";
}

echo "</div></body></html>";
?>
