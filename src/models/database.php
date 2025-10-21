

<?php
// xampp 
// Thông tin kết nối
$host = 'localhost';   // hoặc 'localhost'
$dbname = 'dacs2'; // tên database bạn tạo
$username = 'root';    // user mặc định của XAMPP
$password = '';        // XAMPP mặc định không có mật khẩu

try {
    // Chuỗi DSN
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";    // Tạo đối tượng PDO
    $db = new PDO($dsn, $username, $password);

    // Cấu hình chế độ lỗi
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Kết nối thành công (không echo để tránh làm hỏng JSON response)
    // echo "✅ Kết nối MySQL thành công!";
} catch (PDOException $e) {
    // Log lỗi vào file thay vì echo
    error_log("❌ Lỗi kết nối CSDL: " . $e->getMessage());
    die("Không thể kết nối đến cơ sở dữ liệu.");
}
?>
