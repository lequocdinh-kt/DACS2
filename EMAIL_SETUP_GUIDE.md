# Hướng dẫn cấu hình Email cho Form Liên hệ

## 📧 Bước 1: Cài đặt PHPMailer

Mở Terminal/PowerShell trong thư mục dự án và chạy:

```bash
composer install
```

Nếu chưa có Composer, tải tại: https://getcomposer.org/download/

## 🔑 Bước 2: Lấy App Password từ Gmail

### A. Bật xác thực 2 bước:
1. Đăng nhập Gmail
2. Vào https://myaccount.google.com/security
3. Tìm "2-Step Verification" và bật nó
4. Làm theo hướng dẫn để hoàn tất

### B. Tạo App Password:
1. Sau khi bật 2-Step, vào lại https://myaccount.google.com/security
2. Tìm "App passwords" (Mật khẩu ứng dụng)
3. Chọn "Mail" và "Other (Custom name)"
4. Nhập tên: "VKU Cinema"
5. Click "Generate"
6. **Copy mật khẩu 16 ký tự** (dạng: xxxx xxxx xxxx xxxx)

⚠️ **LƯU Ý**: Mật khẩu này chỉ hiển thị 1 lần, hãy lưu lại!

## ⚙️ Bước 3: Cấu hình trong Code

Mở file `src/controllers/contactController.php` và sửa các dòng sau:

```php
// Dòng 58-62
$smtp_username = 'your-email@gmail.com'; // Thay bằng email Gmail của bạn
$smtp_password = 'your-app-password';    // Thay bằng App Password vừa tạo (16 ký tự)
$to_email = 'dinhlq.24itb@vku.udn.vn';   // Email sẽ nhận tin nhắn
```

**Ví dụ thực tế:**
```php
$smtp_username = 'dinhlq.24itb@gmail.com';
$smtp_password = 'abcd efgh ijkl mnop';  // App Password từ Gmail
$to_email = 'dinhlq.24itb@vku.udn.vn';
```

## 🧪 Bước 4: Test Email

1. Khởi động XAMPP/WAMP
2. Truy cập: http://localhost/index.php?page=lien-he
3. Điền form và click "Gửi Tin Nhắn"
4. Kiểm tra email của bạn

## 🚨 Xử lý lỗi thường gặp

### Lỗi: "SMTP connect() failed"
- **Nguyên nhân**: Sai username/password hoặc chưa bật App Password
- **Giải pháp**: 
  - Kiểm tra lại email và App Password
  - Đảm bảo đã bật 2-Step Verification
  - Tạo lại App Password mới

### Lỗi: "Could not authenticate"
- **Nguyên nhân**: App Password không đúng
- **Giải pháp**: Tạo lại App Password từ Gmail

### Lỗi: "Composer not found"
- **Nguyên nhân**: Chưa cài Composer
- **Giải pháp**: Tải và cài Composer từ https://getcomposer.org

### Email không đến
- Kiểm tra thư mục **Spam/Junk**
- Đợi 1-2 phút (đôi khi bị delay)
- Kiểm tra lại email nhận trong code

## 📝 Cấu trúc thư mục sau khi cài

```
DACS2/
├── vendor/               (tự động tạo khi chạy composer install)
│   └── phpmailer/
├── composer.json         (đã tạo)
├── composer.lock         (tự động tạo)
└── src/
    └── controllers/
        └── contactController.php (đã cập nhật)
```

## 🔒 Bảo mật

**QUAN TRỌNG**: Khi deploy lên server thật:

1. **KHÔNG** commit App Password lên Git
2. Tạo file `.env` để lưu thông tin nhạy cảm:
   ```
   SMTP_USERNAME=your-email@gmail.com
   SMTP_PASSWORD=your-app-password
   ```
3. Thêm `.env` vào `.gitignore`

## 💡 Tips

- Sử dụng email khác với email nhận để dễ quản lý
- Test kỹ trước khi deploy production
- Giữ App Password an toàn, không chia sẻ
- Có thể dùng email domain riêng thay vì Gmail khi có domain

## 🆘 Cần hỗ trợ?

Nếu vẫn gặp vấn đề, check:
1. Log lỗi trong browser console (F12)
2. Check response trong Network tab
3. Đảm bảo internet stable (SMTP cần kết nối)
