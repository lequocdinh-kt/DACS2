<?php
/**
 * Email Helper - Gửi email xác nhận đặt vé
 * Sử dụng PHPMailer
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config.php';

/**
 * Gửi email xác nhận đặt vé thành công
 * 
 * @param array $booking - Thông tin booking từ database
 * @return bool - True nếu gửi thành công
 */
function send_booking_confirmation_email($booking) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_ENCRYPTION;
        $mail->Port = MAIL_PORT;
        
        // Tắt debug output (bật nếu cần debug)
        $mail->SMTPDebug = 0; // 0 = off, 2 = debug output
        
        // Recipients
        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mail->addAddress($booking['email'], $booking['fullName']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = "Xác nhận đặt vé thành công - {$booking['bookingCode']} - VKU Cinema";
        
        // Email body với HTML template đẹp
        $mail->Body = generate_booking_email_html($booking);
        
        // Plain text fallback
        $mail->AltBody = generate_booking_email_text($booking);
        
        $mail->send();
        
        // Log thành công
        error_log("[EMAIL] ✅ Sent booking confirmation to {$booking['email']} - BookingCode: {$booking['bookingCode']}");
        
        return true;
        
    } catch (Exception $e) {
        error_log("[EMAIL] ❌ Failed to send: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Tạo HTML email template đẹp
 */
function generate_booking_email_html($booking) {
    $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($booking['bookingCode']);
    
    $showDate = format_date_vn($booking['showDate']);
    $showTime = format_time($booking['showTime']);
    
    return <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .header p {
            margin: 10px 0 0 0;
            opacity: 0.95;
        }
        .success-icon {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            color: #667eea;
        }
        .content {
            padding: 30px;
        }
        .booking-code {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .booking-code h2 {
            margin: 0 0 10px 0;
            font-size: 16px;
            font-weight: 500;
            opacity: 0.9;
        }
        .booking-code .code {
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 2px;
            margin: 10px 0;
        }
        .qr-section {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        .qr-section img {
            max-width: 200px;
            border: 3px solid #667eea;
            border-radius: 8px;
        }
        .details {
            background: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-row .label {
            font-weight: 500;
            color: #666;
        }
        .detail-row .value {
            font-weight: 600;
            color: #333;
            text-align: right;
        }
        .movie-info {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px;
            background: white;
            border: 2px solid #667eea;
            border-radius: 8px;
        }
        .movie-info img {
            width: 100px;
            height: 140px;
            object-fit: cover;
            border-radius: 4px;
        }
        .movie-info .info {
            flex: 1;
        }
        .movie-info h3 {
            margin: 0 0 10px 0;
            color: #667eea;
            font-size: 18px;
        }
        .total {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }
        .total .label {
            font-size: 14px;
            opacity: 0.9;
        }
        .total .amount {
            font-size: 32px;
            font-weight: bold;
            margin-top: 5px;
        }
        .notes {
            background: #fff9e6;
            border-left: 4px solid #ffa500;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .notes h4 {
            margin: 0 0 10px 0;
            color: #ff6b00;
        }
        .notes ul {
            margin: 0;
            padding-left: 20px;
        }
        .notes li {
            margin: 5px 0;
        }
        .footer {
            background: #f5f5f5;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .footer a {
            color: #667eea;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="success-icon">✓</div>
            <h1>Đặt vé thành công!</h1>
            <p>Cảm ơn bạn đã chọn VKU Cinema</p>
        </div>
        
        <div class="content">
            <div class="booking-code">
                <h2>Mã đặt vé của bạn</h2>
                <div class="code">{$booking['bookingCode']}</div>
                <p style="margin: 10px 0 0 0; font-size: 14px; opacity: 0.9;">
                    Vui lòng xuất trình mã này tại quầy
                </p>
            </div>
            
            <div class="movie-info">
                <img src="{$booking['posterURL']}" alt="{$booking['movieTitle']}">
                <div class="info">
                    <h3>{$booking['movieTitle']}</h3>
                    <p style="margin: 5px 0; color: #666;">
                        <strong>Thể loại:</strong> {$booking['genre']}<br>
                        <strong>Thời lượng:</strong> {$booking['duration']} phút
                    </p>
                </div>
            </div>
            
            <div class="details">
                <div class="detail-row">
                    <span class="label">📅 Ngày chiếu</span>
                    <span class="value">{$showDate}</span>
                </div>
                <div class="detail-row">
                    <span class="label">🕐 Giờ chiếu</span>
                    <span class="value">{$showTime}</span>
                </div>
                <div class="detail-row">
                    <span class="label">🚪 Phòng chiếu</span>
                    <span class="value">{$booking['roomName']}</span>
                </div>
                <div class="detail-row">
                    <span class="label">💺 Ghế ngồi</span>
                    <span class="value">{$booking['seats']}</span>
                </div>
                <div class="detail-row">
                    <span class="label">🎫 Số lượng vé</span>
                    <span class="value">{$booking['totalSeats']} vé</span>
                </div>
            </div>
            
            <div class="total">
                <div class="label">Tổng thanh toán</div>
                <div class="amount">{$booking['totalPrice_formatted']}</div>
                <p style="margin: 10px 0 0 0; font-size: 14px; opacity: 0.9;">
                    ✓ Đã thanh toán thành công
                </p>
            </div>
            
            <div class="qr-section">
                <h4 style="margin: 0 0 15px 0; color: #667eea;">QR Code Check-in</h4>
                <img src="{$qrCodeUrl}" alt="QR Code">
                <p style="margin: 15px 0 0 0; color: #666; font-size: 14px;">
                    Quét mã này tại rạp để check-in nhanh
                </p>
            </div>
            
            <div class="notes">
                <h4>⚠️ Lưu ý quan trọng</h4>
                <ul>
                    <li>Có mặt trước <strong>15 phút</strong> để check-in</li>
                    <li>Mang <strong>CCCD/CMND</strong> để đối chiếu thông tin</li>
                    <li>Xuất trình <strong>mã vé</strong> hoặc <strong>QR code</strong> tại quầy</li>
                    <li>Vé <strong>không hoàn trả</strong> hoặc đổi sau khi đã đặt</li>
                    <li>Không mang thức ăn từ bên ngoài vào rạp</li>
                </ul>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>VKU Cinema</strong></p>
            <p>📍 470 Đ. Trần Đại Nghĩa, Hòa Quý, Ngũ Hành Sơn, Đà Nẵng</p>
            <p>📞 Hotline: 1900-xxxx | 📧 Email: support@vkucinema.vn</p>
            <p style="margin-top: 15px;">
                <a href="{$booking['confirmUrl']}">Xem chi tiết vé</a> | 
                <a href="https://vkucinema.vn">Trang chủ</a>
            </p>
            <p style="margin-top: 15px; font-size: 12px; color: #999;">
                Email này được gửi tự động, vui lòng không trả lời.
            </p>
        </div>
    </div>
</body>
</html>
HTML;
}

/**
 * Tạo plain text email (fallback)
 */
function generate_booking_email_text($booking) {
    $showDate = format_date_vn($booking['showDate']);
    $showTime = format_time($booking['showTime']);
    
    return <<<TEXT
===================================
   ĐẶT VÉ THÀNH CÔNG - VKU CINEMA
===================================

Xin chào {$booking['fullName']},

Cảm ơn bạn đã đặt vé tại VKU Cinema!

MÃ ĐẶT VÉ: {$booking['bookingCode']}

THÔNG TIN PHIM:
• Tên phim: {$booking['movieTitle']}
• Thể loại: {$booking['genre']}
• Thời lượng: {$booking['duration']} phút

THÔNG TIN SUẤT CHIẾU:
• Ngày chiếu: {$showDate}
• Giờ chiếu: {$showTime}
• Phòng chiếu: {$booking['roomName']}
• Ghế ngồi: {$booking['seats']}
• Số lượng vé: {$booking['totalSeats']} vé

THANH TOÁN:
• Tổng tiền: {$booking['totalPrice_formatted']}
• Trạng thái: Đã thanh toán ✓

LƯU Ý QUAN TRỌNG:
• Có mặt trước 15 phút để check-in
• Mang CCCD/CMND để đối chiếu thông tin
• Xuất trình mã vé hoặc QR code tại quầy
• Vé không hoàn trả hoặc đổi sau khi đã đặt
• Không mang thức ăn từ bên ngoài vào rạp

-----------------------------------
VKU Cinema
📍 470 Đ. Trần Đại Nghĩa, Hòa Quý, Ngũ Hành Sơn, Đà Nẵng
📞 Hotline: 1900-xxxx
📧 Email: support@vkucinema.vn
TEXT;
}

/**
 * Helper: Format ngày tiếng Việt
 */
if (!function_exists('format_date_vn')) {
    function format_date_vn($date) {
        $d = new DateTime($date);
        $days = ['Chủ nhật', 'Thứ hai', 'Thứ ba', 'Thứ tư', 'Thứ năm', 'Thứ sáu', 'Thứ bảy'];
        return $days[$d->format('w')] . ', ' . $d->format('d/m/Y');
    }
}

/**
 * Helper: Format giờ
 */
if (!function_exists('format_time')) {
    function format_time($time) {
        return date('H:i', strtotime($time));
    }
}
