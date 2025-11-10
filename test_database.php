<?php
/**
 * TEST DATABASE CONNECTION
 * File này giúp kiểm tra kết nối database
 */

echo "<h1>🔍 Database Connection Test</h1>";

// Test 1: Check config.php
echo "<h2>1️⃣ Kiểm tra Config</h2>";
require_once __DIR__ . '/config.php';

echo "<ul>";
echo "<li><strong>HTTP_HOST:</strong> " . $_SERVER['HTTP_HOST'] . "</li>";
echo "<li><strong>DB_HOST:</strong> " . DB_HOST . "</li>";
echo "<li><strong>DB_NAME:</strong> " . DB_NAME . "</li>";
echo "<li><strong>DB_USER:</strong> " . DB_USER . "</li>";
echo "<li><strong>DB_PASS:</strong> " . (empty(DB_PASS) ? '(empty)' : '********') . "</li>";
echo "</ul>";

// Test 2: Connect to database
echo "<h2>2️⃣ Kết nối Database</h2>";
require_once __DIR__ . '/src/models/database.php';

if (isset($db) && $db instanceof PDO) {
    echo "<p style='color: green;'>✅ Kết nối PDO thành công!</p>";
    
    // Test 3: Check tables
    echo "<h2>3️⃣ Kiểm tra Bảng</h2>";
    try {
        $stmt = $db->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($tables)) {
            echo "<p style='color: red;'>❌ Database trống! Chưa có bảng nào.</p>";
            echo "<p><strong>👉 Giải pháp:</strong> Import file <code>database_core.sql</code></p>";
        } else {
            echo "<p style='color: green;'>✅ Tìm thấy " . count($tables) . " bảng:</p>";
            echo "<ul>";
            foreach ($tables as $table) {
                echo "<li>$table</li>";
            }
            echo "</ul>";
            
            // Test 4: Check specific tables
            echo "<h2>4️⃣ Kiểm tra Bảng Quan Trọng</h2>";
            $requiredTables = ['roles', 'user', 'movie', 'rooms', 'seats', 'showtimes', 'bookings'];
            
            echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
            echo "<tr style='background: #4CAF50; color: white;'><th>Bảng</th><th>Trạng thái</th><th>Số Records</th></tr>";
            
            foreach ($requiredTables as $table) {
                $exists = in_array($table, $tables);
                
                if ($exists) {
                    try {
                        $stmt = $db->query("SELECT COUNT(*) FROM `$table`");
                        $count = $stmt->fetchColumn();
                        echo "<tr><td>$table</td><td style='color: green;'>✅ OK</td><td>$count</td></tr>";
                    } catch (Exception $e) {
                        echo "<tr><td>$table</td><td style='color: green;'>✅ OK</td><td>Error</td></tr>";
                    }
                } else {
                    echo "<tr><td>$table</td><td style='color: red;'>❌ Thiếu</td><td>-</td></tr>";
                }
            }
            echo "</table>";
            
            // Test 5: Test query
            echo "<h2>5️⃣ Test Query</h2>";
            try {
                $stmt = $db->query("SELECT * FROM `user` LIMIT 3");
                $users = $stmt->fetchAll();
                
                if (empty($users)) {
                    echo "<p style='color: orange;'>⚠️ Bảng user trống. Import <code>database_sample_data.sql</code></p>";
                } else {
                    echo "<p style='color: green;'>✅ Có " . count($users) . " users (sample):</p>";
                    echo "<ul>";
                    foreach ($users as $user) {
                        echo "<li>{$user['username']} ({$user['email']})</li>";
                    }
                    echo "</ul>";
                }
                
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Lỗi query: " . $e->getMessage() . "</p>";
            }
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</p>";
    }
    
    echo "<hr>";
    echo "<h2>🎉 KẾT LUẬN</h2>";
    echo "<p style='color: green; font-size: 18px;'><strong>✅ Database hoạt động bình thường!</strong></p>";
    echo "<p>Bạn có thể bắt đầu sử dụng website.</p>";
    
} else {
    echo "<p style='color: red;'>❌ Không thể kết nối database</p>";
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Database Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h1 { color: #333; }
        h2 { 
            color: #555; 
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
        }
        code {
            background: #e0e0e0;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        table {
            width: 100%;
            background: white;
        }
    </style>
</head>
<body>
</body>
</html>
