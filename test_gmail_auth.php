<?php
/**
 * Test Gmail Authentication
 * Kiểm tra App Password có hoạt động không
 */

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test Gmail Auth</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 20px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #2196F3; }
        .success { border-left-color: #4CAF50; }
        .error { border-left-color: #f44336; }
        .warning { border-left-color: #ff9800; }
        pre { background: #f5f5f5; padding: 10px; overflow: auto; font-size: 12px; }
        input, button { padding: 10px; margin: 5px 0; font-size: 14px; }
        input[type="text"], input[type="password"] { width: 400px; }
        button { background: #2196F3; color: white; border: none; cursor: pointer; padding: 10px 20px; }
        button:hover { background: #1976D2; }
    </style>
</head>
<body>
    <h1>🔐 Test Gmail SMTP Authentication</h1>

    <div class="box warning">
        <h3>⚠️ Hướng dẫn</h3>
        <ol>
            <li>Nhập Gmail address của bạn</li>
            <li>Nhập <strong>App Password</strong> (KHÔNG phải password Gmail thật)</li>
            <li>Click "Test Authentication" để kiểm tra</li>
        </ol>
        <p><strong>Tạo App Password:</strong> <a href="https://myaccount.google.com/apppasswords" target="_blank">https://myaccount.google.com/apppasswords</a></p>
    </div>

    <div class="box">
        <h3>Nhập thông tin</h3>
        <form method="POST">
            <label>Gmail Address:</label><br>
            <input type="text" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : 'dinhlq.24itb@gmail.com'; ?>" required><br>
            
            <label>App Password (16 ký tự):</label><br>
            <input type="password" name="password" value="<?php echo isset($_POST['password']) ? htmlspecialchars($_POST['password']) : ''; ?>" placeholder="xxxx xxxx xxxx xxxx" required><br>
            
            <button type="submit" name="test">🧪 Test Authentication</button>
        </form>
    </div>

<?php
if (isset($_POST['test'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    // Remove spaces from app password
    $password = str_replace(' ', '', $password);
    
    echo "<div class='box'>";
    echo "<h3>📊 Kết quả kiểm tra</h3>";
    
    // Test 1: Check format
    echo "<p><strong>1. Kiểm tra định dạng:</strong></p>";
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<span style='color: green'>✓ Email format hợp lệ</span><br>";
    } else {
        echo "<span style='color: red'>✗ Email format không hợp lệ</span><br>";
    }
    
    if (strlen($password) == 16) {
        echo "<span style='color: green'>✓ App Password đúng 16 ký tự</span><br>";
    } else {
        echo "<span style='color: orange'>⚠ App Password có " . strlen($password) . " ký tự (nên là 16)</span><br>";
    }
    
    // Test 2: Test SMTP connection
    echo "<p><strong>2. Kiểm tra kết nối SMTP:</strong></p>";
    $fp = @fsockopen('smtp.gmail.com', 587, $errno, $errstr, 10);
    if ($fp) {
        echo "<span style='color: green'>✓ Kết nối đến smtp.gmail.com:587 thành công</span><br>";
        fclose($fp);
    } else {
        echo "<span style='color: red'>✗ Không thể kết nối: $errstr</span><br>";
    }
    
    // Test 3: Check if autoload exists
    echo "<p><strong>3. Kiểm tra PHPMailer:</strong></p>";
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
        
        if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            echo "<span style='color: green'>✓ PHPMailer loaded</span><br>";
            
            // Test 4: Try authentication
            echo "<p><strong>4. Test Authentication với Gmail:</strong></p>";
            echo "<pre>";
            
            try {
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                
                // SMTP config
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = $email;
                $mail->Password = $password;
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                $mail->SMTPDebug = 2;
                $mail->Debugoutput = 'html';
                
                // Try to connect
                if ($mail->smtpConnect()) {
                    echo "</pre>";
                    echo "<div class='box success'>";
                    echo "<h2 style='color: green; margin: 0;'>✅ AUTHENTICATION THÀNH CÔNG!</h2>";
                    echo "<p>App Password <strong>HỢP LỆ</strong> và có thể gửi email.</p>";
                    echo "<p>Email: <strong>$email</strong></p>";
                    echo "<p>Bạn có thể dùng thông tin này trong <code>contactController.php</code></p>";
                    echo "</div>";
                    
                    $mail->smtpClose();
                } else {
                    echo "</pre>";
                    echo "<div class='box error'>";
                    echo "<h2 style='color: red; margin: 0;'>❌ AUTHENTICATION THẤT BẠI!</h2>";
                    echo "<p>Không thể xác thực với Gmail.</p>";
                    echo "<p><strong>Nguyên nhân có thể:</strong></p>";
                    echo "<ul>";
                    echo "<li>App Password không đúng hoặc hết hạn</li>";
                    echo "<li>Chưa bật 2-Step Verification trên Gmail</li>";
                    echo "<li>Account bị khóa hoặc có vấn đề bảo mật</li>";
                    echo "</ul>";
                    echo "<p><strong>Giải pháp:</strong></p>";
                    echo "<ol>";
                    echo "<li>Vào <a href='https://myaccount.google.com/security' target='_blank'>Google Security Settings</a></li>";
                    echo "<li>Bật 2-Step Verification nếu chưa có</li>";
                    echo "<li>Tạo App Password MỚI tại <a href='https://myaccount.google.com/apppasswords' target='_blank'>App Passwords</a></li>";
                    echo "<li>Copy password 16 ký tự và thử lại</li>";
                    echo "</ol>";
                    echo "</div>";
                }
                
            } catch (Exception $e) {
                echo "</pre>";
                echo "<div class='box error'>";
                echo "<h2 style='color: red; margin: 0;'>❌ LỖI: " . $e->getMessage() . "</h2>";
                echo "<pre>" . $e->getTraceAsString() . "</pre>";
                echo "</div>";
            }
            
        } else {
            echo "<span style='color: red'>✗ PHPMailer class không tồn tại</span>";
        }
    } else {
        echo "<span style='color: red'>✗ File autoload.php không tồn tại</span>";
    }
    
    echo "</div>";
}
?>

    <div class="box warning">
        <h3>📚 Tài liệu tham khảo</h3>
        <ul>
            <li><a href="https://myaccount.google.com/security" target="_blank">Google Security Settings</a></li>
            <li><a href="https://myaccount.google.com/apppasswords" target="_blank">Create App Password</a></li>
            <li><a href="https://support.google.com/accounts/answer/185833" target="_blank">Sign in with App Passwords</a></li>
        </ul>
        <p><strong>Liên hệ hỗ trợ: 0795701805</strong></p>
    </div>

</body>
</html>
