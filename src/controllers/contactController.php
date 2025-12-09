<?php
/**
 * Contact Form Controller
 * Xử lý gửi email từ form liên hệ sử dụng PHPMailer
 */

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require __DIR__ . '/../../vendor/autoload.php';

// Chỉ chấp nhận POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Lấy dữ liệu từ form
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Validate dữ liệu
$errors = [];

if (empty($name)) {
    $errors[] = 'Vui lòng nhập họ tên';
}

if (empty($email)) {
    $errors[] = 'Vui lòng nhập email';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email không hợp lệ';
}

if (empty($message)) {
    $errors[] = 'Vui lòng nhập nội dung tin nhắn';
}

if (!empty($errors)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

// ============================================================
// CẤU HÌNH EMAIL - QUAN TRỌNG: THAY ĐỔI THÔNG TIN CỦA BẠN
// ============================================================
$smtp_host = 'smtp.gmail.com';          // SMTP server
$smtp_port = 587;                        // Port (587 cho TLS, 465 cho SSL)
$smtp_username = 'xiaoying1805@gmail.com'; // ⚠️ THAY BẰNG EMAIL CỦA BẠN
$smtp_password = 'drqp waeh onet tvey';    // ⚠️ THAY BẰNG APP PASSWORD (loại bỏ khoảng trắng)
$from_email = 'noreply@vkucinema.com';   // Email người gửi
$from_name = 'VKU Cinema';               // Tên người gửi
$to_email = 'dinhlq.24itb@vku.udn.vn';   // ⚠️ Email nhận (email của bạn)
// ============================================================
// Tạo nội dung email HTML
$email_content = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 20px; text-align: center; }
        .header h2 { margin: 0; font-size: 24px; }
        .content { background: #f9f9f9; padding: 30px 20px; }
        .info-row { margin: 15px 0; padding: 12px; background: white; border-radius: 5px; }
        .label { font-weight: bold; color: #667eea; display: inline-block; min-width: 120px; }
        .value { color: #333; }
        .message-box { background: white; padding: 20px; border-left: 4px solid #667eea; margin-top: 20px; border-radius: 5px; }
        .message-box .label { display: block; margin-bottom: 10px; font-size: 16px; }
        .message-text { color: #555; line-height: 1.8; white-space: pre-line; }
        .footer { text-align: center; color: #999; font-size: 12px; padding: 20px; background: #f0f0f0; }
        .footer p { margin: 5px 0; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>📧 Tin nhắn mới từ Website</h2>
        </div>
        <div class='content'>
            <div class='info-row'>
                <span class='label'>👤 Họ và tên:</span>
                <span class='value'>" . htmlspecialchars($name) . "</span>
            </div>
            <div class='info-row'>
                <span class='label'>📧 Email:</span>
                <span class='value'>" . htmlspecialchars($email) . "</span>
            </div>
            <div class='info-row'>
                <span class='label'>📱 Điện thoại:</span>
                <span class='value'>" . htmlspecialchars($phone ? $phone : 'Không cung cấp') . "</span>
            </div>
            <div class='message-box'>
                <div class='label'>💬 Nội dung tin nhắn:</div>
                <div class='message-text'>" . htmlspecialchars($message) . "</div>
            </div>
        </div>
        <div class='footer'>
            <p><strong>VKU Cinema - Hệ thống rạp chiếu phim</strong></p>
            <p>Email này được gửi từ form liên hệ trên website</p>
            <p>Thời gian: " . date('d/m/Y H:i:s') . "</p>
        </div>
    </div>
</body>
</html>
";

// Tạo instance PHPMailer
$mail = new PHPMailer(true);

try {
    // Cấu hình SMTP
    $mail->isSMTP();
    $mail->Host       = $smtp_host;
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtp_username;
    $mail->Password   = $smtp_password;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $smtp_port;
    $mail->CharSet    = 'UTF-8';
    
    // Người gửi và người nhận
    $mail->setFrom($from_email, $from_name);
    $mail->addAddress($to_email);
    $mail->addReplyTo($email, $name); // Reply sẽ gửi đến email của khách
    
    // Nội dung email
    $mail->isHTML(true);
    $mail->Subject = 'Liên hệ từ website VKU Cinema - ' . $name;
    $mail->Body    = $email_content;
    $mail->AltBody = "Tên: $name\nEmail: $email\nĐiện thoại: $phone\nTin nhắn: $message";
    
    // Gửi email
    $mail->send();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi trong thời gian sớm nhất.'
    ]);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Không thể gửi email. Vui lòng thử lại sau hoặc liên hệ trực tiếp: 0795701805',
        'error' => $mail->ErrorInfo // Chỉ để debug, xóa dòng này khi deploy production
    ]);
}
?>
