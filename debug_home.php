<?php
/**
 * DEBUG HOME PAGE
 * File này giúp tìm lỗi tại sao trang chủ không hiển thị
 */

// Bật hiển thị lỗi
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Debug Home Page</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        h1 { color: #333; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 30px; background: #e8f5e9; padding: 10px; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .code { background: #f4f4f4; padding: 10px; border-radius: 4px; font-family: 'Courier New', monospace; margin: 10px 0; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #4CAF50; color: white; }
        .step { background: #e3f2fd; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #2196F3; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>🔍 DEBUG HOME PAGE</h1>";
echo "<p>File này kiểm tra từng bước để tìm lỗi tại sao trang chủ không hiển thị nội dung</p>";

// Step 1: Test config
echo "<h2>Bước 1: Kiểm tra Config</h2>";
try {
    require_once __DIR__ . '/config.php';
    echo "<div class='success'>✅ config.php loaded thành công</div>";
    echo "<div class='code'>";
    echo "DB_HOST: " . DB_HOST . "<br>";
    echo "DB_NAME: " . DB_NAME . "<br>";
    echo "DB_USER: " . DB_USER . "<br>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Lỗi load config.php: " . $e->getMessage() . "</div>";
    die();
}

// Step 2: Test database connection
echo "<h2>Bước 2: Kiểm tra Database Connection</h2>";
try {
    require_once __DIR__ . '/src/models/database.php';
    echo "<div class='success'>✅ database.php loaded thành công</div>";
    
    // Test query đơn giản
    $stmt = $db->query("SELECT COUNT(*) as total FROM movie");
    $count = $stmt->fetch();
    echo "<div class='success'>✅ Database connection OK - Có {$count['total']} phim</div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Lỗi database connection: " . $e->getMessage() . "</div>";
    die();
}

// Step 3: Test movie_db functions
echo "<h2>Bước 3: Kiểm tra Movie Functions</h2>";
try {
    require_once __DIR__ . '/src/models/movie_db.php';
    echo "<div class='success'>✅ movie_db.php loaded thành công</div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Lỗi load movie_db.php: " . $e->getMessage() . "</div>";
    die();
}

// Step 4: Test get_random_movies()
echo "<h2>Bước 4: Test get_random_movies(5)</h2>";
try {
    $bannerMovies = get_random_movies(5);
    
    if ($bannerMovies === false) {
        echo "<div class='error'>❌ get_random_movies() trả về FALSE</div>";
    } elseif (empty($bannerMovies)) {
        echo "<div class='warning'>⚠️ get_random_movies() trả về mảng RỖNG</div>";
        echo "<div class='code'>Có thể chưa có phim nào trong database hoặc không có phim 'now_showing'</div>";
    } else {
        echo "<div class='success'>✅ get_random_movies() OK - Tìm thấy " . count($bannerMovies) . " phim</div>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Tên Phim</th><th>Status</th></tr>";
        foreach ($bannerMovies as $movie) {
            echo "<tr>";
            echo "<td>{$movie['movieID']}</td>";
            echo "<td>{$movie['title']}</td>";
            echo "<td>{$movie['movieStatus']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Lỗi get_random_movies(): " . $e->getMessage() . "</div>";
    echo "<div class='code'><pre>" . $e->getTraceAsString() . "</pre></div>";
}

// Step 5: Test get_hot_movies()
echo "<h2>Bước 5: Test get_hot_movies(6)</h2>";
try {
    $nowShowingMovies = get_hot_movies(6);
    
    if ($nowShowingMovies === false) {
        echo "<div class='error'>❌ get_hot_movies() trả về FALSE</div>";
    } elseif (empty($nowShowingMovies)) {
        echo "<div class='warning'>⚠️ get_hot_movies() trả về mảng RỖNG</div>";
    } else {
        echo "<div class='success'>✅ get_hot_movies() OK - Tìm thấy " . count($nowShowingMovies) . " phim</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Lỗi get_hot_movies(): " . $e->getMessage() . "</div>";
    echo "<div class='code'><pre>" . $e->getTraceAsString() . "</pre></div>";
}

// Step 6: Test get_upcoming_movies_by_date()
echo "<h2>Bước 6: Test get_upcoming_movies_by_date(8)</h2>";
try {
    $comingSoonMovies = get_upcoming_movies_by_date(8);
    
    if ($comingSoonMovies === false) {
        echo "<div class='error'>❌ get_upcoming_movies_by_date() trả về FALSE</div>";
    } elseif (empty($comingSoonMovies)) {
        echo "<div class='warning'>⚠️ get_upcoming_movies_by_date() trả về mảng RỖNG</div>";
    } else {
        echo "<div class='success'>✅ get_upcoming_movies_by_date() OK - Tìm thấy " . count($comingSoonMovies) . " phim</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Lỗi get_upcoming_movies_by_date(): " . $e->getMessage() . "</div>";
    echo "<div class='code'><pre>" . $e->getTraceAsString() . "</pre></div>";
}

// Step 7: Test homeController
echo "<h2>Bước 7: Test homeController.php</h2>";
try {
    ob_start();
    require_once __DIR__ . '/src/controllers/homeController.php';
    $output = ob_get_clean();
    
    echo "<div class='success'>✅ homeController.php loaded thành công</div>";
    
    // Kiểm tra các biến được tạo
    echo "<div class='code'>";
    echo "bannerMovies: " . (isset($bannerMovies) ? count($bannerMovies) . " phim" : "KHÔNG TỒN TẠI") . "<br>";
    echo "nowShowingMovies: " . (isset($nowShowingMovies) ? count($nowShowingMovies) . " phim" : "KHÔNG TỒN TẠI") . "<br>";
    echo "comingSoonMovies: " . (isset($comingSoonMovies) ? count($comingSoonMovies) . " phim" : "KHÔNG TỒN TẠI") . "<br>";
    echo "bookingMovies: " . (isset($bookingMovies) ? count($bookingMovies) . " phim" : "KHÔNG TỒN TẠI") . "<br>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Lỗi homeController.php: " . $e->getMessage() . "</div>";
    echo "<div class='code'><pre>" . $e->getTraceAsString() . "</pre></div>";
}

// Step 8: Test home.php view
echo "<h2>Bước 8: Test home.php View (5 dòng đầu)</h2>";
try {
    $homeContent = file_get_contents(__DIR__ . '/src/views/home.php');
    
    if ($homeContent === false) {
        echo "<div class='error'>❌ Không đọc được file home.php</div>";
    } else {
        echo "<div class='success'>✅ File home.php tồn tại (" . strlen($homeContent) . " bytes)</div>";
        
        // Kiểm tra có require homeController không
        if (strpos($homeContent, 'homeController.php') !== false) {
            echo "<div class='success'>✅ home.php có require homeController.php</div>";
        } else {
            echo "<div class='warning'>⚠️ home.php KHÔNG require homeController.php</div>";
        }
        
        // Hiển thị 10 dòng đầu
        $lines = explode("\n", $homeContent);
        echo "<div class='code'>";
        echo "<strong>10 dòng đầu của home.php:</strong><br>";
        for ($i = 0; $i < min(10, count($lines)); $i++) {
            echo htmlspecialchars($lines[$i]) . "<br>";
        }
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Lỗi đọc home.php: " . $e->getMessage() . "</div>";
}

// Step 9: Test index.php
echo "<h2>Bước 9: Kiểm tra index.php</h2>";
try {
    $indexContent = file_get_contents(__DIR__ . '/index.php');
    
    if ($indexContent === false) {
        echo "<div class='error'>❌ Không đọc được file index.php</div>";
    } else {
        echo "<div class='success'>✅ File index.php tồn tại</div>";
        
        // Kiểm tra có include home.php không
        if (strpos($indexContent, 'home.php') !== false) {
            echo "<div class='success'>✅ index.php có include home.php</div>";
        } else {
            echo "<div class='error'>❌ index.php KHÔNG include home.php!</div>";
        }
        
        // Hiển thị nội dung
        echo "<div class='code'>";
        echo "<strong>Nội dung index.php:</strong><br>";
        echo "<pre>" . htmlspecialchars($indexContent) . "</pre>";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Lỗi đọc index.php: " . $e->getMessage() . "</div>";
}

// Kết luận
echo "<h2>✅ KẾT LUẬN</h2>";
echo "<div class='step'>";
echo "<strong>Nếu tất cả các bước trên đều OK:</strong><br>";
echo "→ Vấn đề có thể là CSS không load hoặc JavaScript bị lỗi<br>";
echo "→ Kiểm tra Console trong trình duyệt (F12 → Console)<br>";
echo "→ Kiểm tra Network tab để xem file nào không load được<br><br>";

echo "<strong>Nếu có bước nào BỊ LỖI:</strong><br>";
echo "→ Đó chính là nguyên nhân trang chủ không hiển thị<br>";
echo "→ Gửi screenshot lỗi cho tôi để fix<br>";
echo "</div>";

echo "<p><a href='index.php'>← Quay lại trang chủ</a></p>";

echo "</div></body></html>";
?>
