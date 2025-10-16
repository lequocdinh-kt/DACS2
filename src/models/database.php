<?php
$dsn = 'mysql:host=localhost;port=3306;dbname=slrnkpifhosting_DACS2;charset=utf8';
$username = 'slrnkpifhosting_xiaoying';
$password = 'i[~+5xn4?eyX4VX'; // mật khẩu thật của user MySQL

try {
    $db = new PDO($dsn, $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Kết nối thành công!";
} catch (PDOException $e) {
    echo "❌ Lỗi kết nối CSDL: " . $e->getMessage();
    exit();
}
?>
