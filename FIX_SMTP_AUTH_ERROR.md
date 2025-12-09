# 🔧 Hướng dẫn Fix Lỗi SMTP Authentication

## Lỗi hiện tại
```
SMTP Error: Could not authenticate
```

## Nguyên nhân
Gmail từ chối đăng nhập vì:
- App Password không đúng/hết hạn
- Chưa bật 2-Step Verification
- Cấu hình bảo mật Gmail chưa đúng

---

## ✅ Giải pháp 1: Tạo lại App Password (KHUYẾN NGHỊ)

### Bước 1: Bật 2-Step Verification
1. Vào: https://myaccount.google.com/security
2. Tìm "2-Step Verification" 
3. Click **"Turn on"** và làm theo hướng dẫn
4. Xác nhận qua SMS/Phone

### Bước 2: Tạo App Password mới
1. Vào: https://myaccount.google.com/apppasswords
2. Hoặc: Google Account → Security → 2-Step Verification → App passwords
3. Select app: **"Mail"**
4. Select device: **"Other (Custom name)"**
5. Nhập tên: **"Cinema Website"**
6. Click **"Generate"**
7. Copy password 16 ký tự (dạng: `xxxx xxxx xxxx xxxx`)

### Bước 3: Cập nhật code
```php
// Trong contactController.php (dòng 58)
$mail->Password = 'xxxx xxxx xxxx xxxx'; // App Password MỚI (16 ký tự)
```

---

## ✅ Giải pháp 2: Dùng Gmail account khác

Nếu account chính gặp vấn đề, tạo Gmail account mới:

### Tạo Gmail account mới
1. Vào: https://accounts.google.com/signup
2. Tạo account (ví dụ: `cinema.contact.vku@gmail.com`)
3. Bật 2-Step Verification ngay
4. Tạo App Password

### Cập nhật trong code
```php
// contactController.php
$mail->Username = 'cinema.contact.vku@gmail.com'; // Email MỚI
$mail->Password = 'xxxx xxxx xxxx xxxx';          // App Password MỚI
$mail->setFrom('cinema.contact.vku@gmail.com', 'VKU Cinema');
```

---

## ✅ Giải pháp 3: Dùng SMTP khác (không cần App Password)

### Option A: Sử dụng SendGrid (FREE 100 emails/day)
```php
$mail->Host = 'smtp.sendgrid.net';
$mail->Port = 587;
$mail->Username = 'apikey';
$mail->Password = 'SG.xxxxxxxxxxxxxx'; // SendGrid API Key
```

**Setup SendGrid:**
1. Đăng ký: https://sendgrid.com/free/
2. Verify email
3. Tạo API Key: Settings → API Keys → Create API Key
4. Copy API Key làm password

### Option B: Sử dụng Mailtrap (TEST môi trường)
```php
$mail->Host = 'smtp.mailtrap.io';
$mail->Port = 2525;
$mail->Username = 'your-mailtrap-username';
$mail->Password = 'your-mailtrap-password';
```

**Setup Mailtrap:**
1. Đăng ký: https://mailtrap.io/
2. Tạo inbox
3. Copy SMTP credentials từ inbox settings

---

## 🔍 Kiểm tra App Password hiện tại

Chạy file test này để verify:

```php
<?php
// test_gmail_auth.php
$username = 'dinhlq.24itb@gmail.com';
$password = 'qjyc sovk incs gxfo';

$connection = fsockopen('smtp.gmail.com', 587, $errno, $errstr, 30);
if ($connection) {
    echo "✓ Kết nối SMTP OK\n";
    
    $response = fgets($connection, 515);
    echo "Server: $response\n";
    
    fputs($connection, "EHLO localhost\r\n");
    $response = fgets($connection, 515);
    echo "EHLO: $response\n";
    
    fputs($connection, "STARTTLS\r\n");
    $response = fgets($connection, 515);
    echo "STARTTLS: $response\n";
    
    fclose($connection);
} else {
    echo "✗ Không kết nối được: $errstr ($errno)\n";
}
?>
```

---

## 📝 Checklist

- [ ] Bật 2-Step Verification trên Gmail
- [ ] Tạo App Password MỚI (16 ký tự)
- [ ] Cập nhật password trong `contactController.php`
- [ ] Xóa cache browser (Ctrl + Shift + Del)
- [ ] Test lại với `debug_email.php`

---

## ⚠️ Lưu ý

1. **KHÔNG dùng password Gmail thật** - Chỉ dùng App Password
2. **App Password không có khoảng trắng** - Loại bỏ spaces khi copy
3. **Account phải bật 2FA** - Bắt buộc để tạo App Password
4. **Kiểm tra Gmail bị khóa** - Vào https://mail.google.com/ xem có thông báo không

---

## 📞 Nếu vẫn lỗi

1. Thử Gmail account khác
2. Hoặc dùng SendGrid/Mailtrap (miễn phí)
3. Liên hệ: **0795701805**

---

## 🎯 Quick Fix (Nhanh nhất)

```bash
# Bước 1: Tạo App Password
1. Vào: https://myaccount.google.com/apppasswords
2. Tạo password mới
3. Copy 16 ký tự

# Bước 2: Cập nhật code
Mở: src/controllers/contactController.php
Tìm dòng 58: $mail->Password = 'qjyc sovk incs gxfo';
Thay bằng: $mail->Password = 'NEW_APP_PASSWORD_HERE';

# Bước 3: Test
http://localhost/debug_email.php
```
