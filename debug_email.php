<?php
/**
 * Debug Email System
 * Kiểm tra từng bước để tìm nguyên nhân lỗi gửi email
 */

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>Email Debug</title>";
echo "<style>
body { font-family: Arial; padding: 20px; background: #f5f5f5; }
.test { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #666; }
.success { border-left-color: #4CAF50; }
.error { border-left-color: #f44336; }
.warning { border-left-color: #ff9800; }
h3 { margin: 0 0 10px 0; }
pre { background: #f5f5f5; padding: 10px; overflow: auto; }
</style></head><body>";

echo "<h1>🔍 Email System Debug</h1>";

// Test 1: Check PHP Version
echo "<div class='test'>";
echo "<h3>1. PHP Version</h3>";
$phpVersion = phpversion();
echo "PHP Version: <strong>$phpVersion</strong><br>";
if (version_compare($phpVersion, '7.2', '>=')) {
    echo "<span style='color: green'>✓ PHP version OK (PHPMailer yêu cầu PHP 7.2+)</span>";
} else {
    echo "<span style='color: red'>✗ PHP version quá cũ (cần PHP 7.2+)</span>";
}
echo "</div>";

// Test 2: Check Required Extensions
echo "<div class='test'>";
echo "<h3>2. PHP Extensions</h3>";
$extensions = [
    'openssl' => 'Cần cho SMTP TLS/SSL',
    'sockets' => 'Cần cho kết nối mạng',
    'mbstring' => 'Cần cho xử lý UTF-8',
];

foreach ($extensions as $ext => $desc) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? '✓' : '✗';
    $color = $loaded ? 'green' : 'red';
    echo "<span style='color: $color'>$status <strong>$ext</strong> - $desc</span><br>";
}
echo "</div>";

// Test 3: Check autoload.php
echo "<div class='test'>";
echo "<h3>3. Autoload File</h3>";
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    echo "<span style='color: green'>✓ File autoload.php tồn tại</span><br>";
    echo "Path: <code>$autoloadPath</code><br>";
    
    try {
        require_once $autoloadPath;
        echo "<span style='color: green'>✓ Autoload được load thành công</span>";
    } catch (Exception $e) {
        echo "<span style='color: red'>✗ Lỗi khi load autoload: {$e->getMessage()}</span>";
    }
} else {
    echo "<span style='color: red'>✗ File autoload.php không tồn tại</span><br>";
    echo "Expected path: <code>$autoloadPath</code>";
}
echo "</div>";

// Test 4: Check PHPMailer Classes
echo "<div class='test'>";
echo "<h3>4. PHPMailer Classes</h3>";
$classes = [
    'PHPMailer\\PHPMailer\\PHPMailer',
    'PHPMailer\\PHPMailer\\SMTP',
    'PHPMailer\\PHPMailer\\Exception',
];

foreach ($classes as $class) {
    $exists = class_exists($class);
    $status = $exists ? '✓' : '✗';
    $color = $exists ? 'green' : 'red';
    $shortName = basename(str_replace('\\', '/', $class));
    echo "<span style='color: $color'>$status <strong>$shortName</strong> class</span><br>";
}
echo "</div>";

// Test 5: Check PHPMailer Directory
echo "<div class='test'>";
echo "<h3>5. PHPMailer Directory Structure</h3>";
$phpmailerDir = __DIR__ . '/vendor/phpmailer/phpmailer';
if (is_dir($phpmailerDir)) {
    echo "<span style='color: green'>✓ Thư mục PHPMailer tồn tại</span><br>";
    echo "Path: <code>$phpmailerDir</code><br><br>";
    
    $srcDir = $phpmailerDir . '/src';
    if (is_dir($srcDir)) {
        echo "<span style='color: green'>✓ Thư mục src/ tồn tại</span><br>";
        $files = ['PHPMailer.php', 'SMTP.php', 'Exception.php'];
        foreach ($files as $file) {
            $filePath = $srcDir . '/' . $file;
            $exists = file_exists($filePath);
            $status = $exists ? '✓' : '✗';
            $color = $exists ? 'green' : 'red';
            echo "<span style='color: $color'>$status src/$file</span><br>";
        }
    } else {
        echo "<span style='color: red'>✗ Thư mục src/ không tồn tại</span>";
    }
} else {
    echo "<span style='color: red'>✗ Thư mục PHPMailer không tồn tại</span><br>";
    echo "Expected path: <code>$phpmailerDir</code>";
}
echo "</div>";

// Test 6: Try to create PHPMailer instance
echo "<div class='test'>";
echo "<h3>6. PHPMailer Instance Test</h3>";
try {
    if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        echo "<span style='color: green'>✓ Tạo PHPMailer instance thành công</span><br>";
        echo "Version: <strong>" . PHPMailer\PHPMailer\PHPMailer::VERSION . "</strong>";
    } else {
        echo "<span style='color: red'>✗ Không thể load PHPMailer class</span>";
    }
} catch (Exception $e) {
    echo "<span style='color: red'>✗ Lỗi khi tạo instance: {$e->getMessage()}</span>";
}
echo "</div>";

// Test 7: SMTP Connection Test
echo "<div class='test'>";
echo "<h3>7. SMTP Connection Test</h3>";
echo "<p>Kiểm tra kết nối đến Gmail SMTP server...</p>";

$smtp_host = 'smtp.gmail.com';
$smtp_port = 587;

if (function_exists('fsockopen')) {
    $errno = 0;
    $errstr = '';
    $timeout = 10;
    
    echo "Testing connection to <strong>$smtp_host:$smtp_port</strong>...<br>";
    $fp = @fsockopen($smtp_host, $smtp_port, $errno, $errstr, $timeout);
    
    if ($fp) {
        echo "<span style='color: green'>✓ Kết nối thành công đến SMTP server</span><br>";
        echo "Port $smtp_port mở và có thể truy cập";
        fclose($fp);
    } else {
        echo "<span style='color: red'>✗ Không thể kết nối đến SMTP server</span><br>";
        echo "Error: $errstr (Code: $errno)<br>";
        echo "<strong>Có thể do:</strong><br>";
        echo "- Firewall/Antivirus chặn port $smtp_port<br>";
        echo "- ISP chặn SMTP<br>";
        echo "- Không có kết nối internet<br>";
    }
} else {
    echo "<span style='color: red'>✗ Function fsockopen không khả dụng</span>";
}
echo "</div>";

// Test 8: Send Test Email
echo "<div class='test'>";
echo "<h3>8. Send Test Email</h3>";

if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'dinhlq.24itb@gmail.com';
        $mail->Password = 'qjyc sovk incs gxfo';
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        
        // Enable debug output
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = function($str, $level) {
            echo "<div style='background: #f0f0f0; padding: 5px; margin: 2px 0; font-size: 12px;'>";
            echo htmlspecialchars($str);
            echo "</div>";
        };
        
        // Email content
        $mail->setFrom('dinhlq.24itb@gmail.com', 'Test Debug');
        $mail->addAddress('dinhlq.24itb@vku.udn.vn');
        $mail->Subject = 'Test Email from Debug Script';
        $mail->Body = 'This is a test email sent at ' . date('Y-m-d H:i:s');
        
        echo "<p><strong>Đang gửi test email...</strong></p>";
        echo "<pre style='max-height: 300px; overflow: auto;'>";
        
        if ($mail->send()) {
            echo "</pre>";
            echo "<span style='color: green; font-size: 18px;'>✓ EMAIL ĐÃ GỬI THÀNH CÔNG!</span><br>";
            echo "Email test đã được gửi đến dinhlq.24itb@vku.udn.vn";
        } else {
            echo "</pre>";
            echo "<span style='color: red'>✗ Gửi email thất bại</span><br>";
            echo "Error: " . $mail->ErrorInfo;
        }
        
    } catch (Exception $e) {
        echo "</pre>";
        echo "<span style='color: red'>✗ Exception: {$e->getMessage()}</span><br>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
} else {
    echo "<span style='color: orange'>⚠ Bỏ qua test này vì PHPMailer class không khả dụng</span>";
}
echo "</div>";

// Test 9: Check contactController.php
echo "<div class='test'>";
echo "<h3>9. Contact Controller File</h3>";
$controllerPath = __DIR__ . '/src/controllers/contactController.php';
if (file_exists($controllerPath)) {
    echo "<span style='color: green'>✓ File contactController.php tồn tại</span><br>";
    echo "Path: <code>$controllerPath</code><br>";
    echo "Size: " . filesize($controllerPath) . " bytes<br>";
    echo "Last modified: " . date('Y-m-d H:i:s', filemtime($controllerPath));
} else {
    echo "<span style='color: red'>✗ File contactController.php không tồn tại</span>";
}
echo "</div>";

// Summary
echo "<div class='test' style='border-left-color: #2196F3;'>";
echo "<h3>📊 Tóm tắt</h3>";
echo "<p>Chạy file debug này để xác định nguyên nhân lỗi email.</p>";
echo "<p>Nếu tất cả tests đều PASS nhưng vẫn không gửi được email từ contact form:</p>";
echo "<ul>";
echo "<li>Kiểm tra Console (F12) xem có lỗi JavaScript không</li>";
echo "<li>Kiểm tra Network tab xem request đến contactController.php có lỗi gì không</li>";
echo "<li>Kiểm tra PHP error log: <code>php -i | grep error_log</code></li>";
echo "<li>Thử gửi trực tiếp bằng POST request đến contactController.php</li>";
echo "</ul>";
echo "<p><strong>Liên hệ trực tiếp nếu cần hỗ trợ: 0795701805</strong></p>";
echo "</div>";

echo "</body></html>";
?>
