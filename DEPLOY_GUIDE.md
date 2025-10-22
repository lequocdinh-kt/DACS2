# 🚀 Hướng Dẫn Deploy Lên Hosting (cPanel)

## ⚠️ Nguyên Nhân Lỗi 500 Thường Gặp

1. **Cấu hình database sai** ❌
2. **Đường dẫn file không đúng** ❌
3. **PHP version không tương thích** ❌
4. **Missing PHP extensions** ❌
5. **File permissions sai** ❌
6. **Syntax error trong code** ❌

---

## 📋 BƯỚC 1: Chuẩn Bị Trên Hosting

### 1.1. Tạo Database MySQL
1. Đăng nhập vào **cPanel**
2. Vào **MySQL Databases**
3. Tạo database mới: `lequocdi_dacs2` (hoặc tên bạn muốn)
4. Tạo user mới: `lequocdi_user`
5. Set password mạnh
6. **Add user to database** với quyền ALL PRIVILEGES
7. **LƯU LẠI** thông tin:
   - Database name: `lequocdi_dacs2`
   - Username: `lequocdi_user`
   - Password: `your_password_here`
   - Host: `localhost`

### 1.2. Import Database
1. Vào **phpMyAdmin**
2. Chọn database vừa tạo
3. Click tab **Import**
4. Chọn file `dacs2.sql` từ localhost
5. Click **Go** để import

---

## 📋 BƯỚC 2: Cấu Hình File `config.php`

**Mở file `config.php`** và cập nhật phần hosting:

```php
} else {
    // Cấu hình cho HOSTING (cPanel)
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'lequocdi_dacs2');      // ← ĐỔI THÀNH TÊN DATABASE CỦA BẠN
    define('DB_USER', 'lequocdi_user');       // ← ĐỔI THÀNH USERNAME CỦA BẠN
    define('DB_PASS', 'YOUR_STRONG_PASSWORD'); // ← ĐỔI THÀNH PASSWORD CỦA BẠN
    
    define('BASE_URL', 'https://lequocdinh.id.vn');
    define('BASE_PATH', '/');
}
```

---

## 📋 BƯỚC 3: Upload Files Lên Hosting

### Cách 1: Dùng File Manager (cPanel)
1. Vào **File Manager** trong cPanel
2. Vào thư mục `public_html`
3. Upload tất cả files (có thể nén thành .zip trước)
4. Giải nén nếu upload file .zip

### Cách 2: Dùng FTP (FileZilla)
1. Tải FileZilla Client
2. Kết nối FTP:
   - Host: `ftp.lequocdinh.id.vn` hoặc IP
   - Username: username cPanel
   - Password: password cPanel
   - Port: 21
3. Upload toàn bộ files vào `/public_html/`

### ⚠️ CẤU TRÚC THƯ MỤC TRÊN HOSTING:
```
public_html/
├── index.php
├── config.php          ← FILE QUAN TRỌNG!
├── .htaccess
├── debug.php           ← XÓA SAU KHI DEBUG
├── src/
│   ├── controllers/
│   ├── models/
│   │   └── database.php
│   ├── views/
│   ├── js/
│   ├── styles/
│   └── img/
└── ...
```

---

## 📋 BƯỚC 4: Kiểm Tra Lỗi

### 4.1. Chạy Debug Tool
Truy cập: **https://lequocdinh.id.vn/debug.php**

File này sẽ kiểm tra:
- ✅ PHP version
- ✅ Extensions cần thiết
- ✅ Kết nối database
- ✅ File tồn tại
- ✅ Session hoạt động

### 4.2. Xem Error Log
```bash
# Trong cPanel File Manager, tìm file:
/home/YOUR_USERNAME/public_html/error.log
```

Hoặc tạo file `view_errors.php`:
```php
<?php
if (file_exists('error.log')) {
    echo "<pre>" . htmlspecialchars(file_get_contents('error.log')) . "</pre>";
} else {
    echo "Không có error log";
}
?>
```

---

## 📋 BƯỚC 5: Fix Các Lỗi Thường Gặp

### ❌ Lỗi: "Can't connect to database"
**Nguyên nhân:** Config database sai

**Giải pháp:**
1. Kiểm tra lại `config.php`
2. Đảm bảo database name có prefix (VD: `lequocdi_dacs2`)
3. Kiểm tra user đã được add vào database chưa
4. Test kết nối bằng phpMyAdmin

### ❌ Lỗi: "File not found" hoặc "require_once failed"
**Nguyên nhân:** Đường dẫn file sai

**Giải pháp:**
1. Đảm bảo dùng `__DIR__` cho đường dẫn tương đối
2. Kiểm tra case-sensitive (Linux phân biệt chữ hoa thường!)
3. Dùng `require_once __DIR__ . '/../../config.php'` thay vì `/config.php`

### ❌ Lỗi: "Syntax error"
**Nguyên nhân:** PHP version không tương thích

**Giải pháp:**
1. Kiểm tra PHP version trong cPanel → **Select PHP Version**
2. Đổi sang PHP 7.4 hoặc 8.0
3. Bật các extensions cần thiết

### ❌ Lỗi: "500 Internal Server Error"
**Nguyên nhân:** Nhiều khả năng

**Giải pháp:**
1. Chạy `debug.php` để xem lỗi chi tiết
2. Xem `error.log`
3. Kiểm tra `.htaccess` (thử xóa tạm để test)
4. Kiểm tra file permissions (644 cho files, 755 cho folders)

---

## 📋 BƯỚC 6: Cấu Hình File Permissions (Quyền File)

### Quyền chuẩn:
```
Files:   644 (rw-r--r--)
Folders: 755 (rwxr-xr-x)
```

### Cách set trong cPanel:
1. Vào File Manager
2. Click chuột phải vào file/folder
3. Chọn **Change Permissions**
4. Set theo bảng trên

---

## 📋 BƯỚC 7: Test Hệ Thống

### 7.1. Test Trang Chủ
Truy cập: **https://lequocdinh.id.vn/**

Kỳ vọng: Hiển thị danh sách phim

### 7.2. Test Đăng Nhập/Đăng Ký
1. Click nút Đăng Nhập
2. Thử đăng ký account mới
3. Đăng nhập

### 7.3. Test Booking
1. Click "Đặt vé" (khi chưa đăng nhập → modal xuất hiện)
2. Đăng nhập
3. Chọn suất chiếu
4. Chọn ghế
5. Thanh toán

---

## 📋 BƯỚC 8: Xóa File Debug (QUAN TRỌNG!)

Sau khi fix xong TẤT CẢ lỗi, **XÓA NGAY**:
- ❌ `debug.php`
- ❌ `view_errors.php` (nếu có)

Và **TẮT display_errors** trong `config.php`:
```php
ini_set('display_errors', 0);
error_reporting(0);
```

---

## 🔧 Checklist Cuối Cùng

- [ ] Database đã import xong
- [ ] `config.php` đã cập nhật đúng thông tin
- [ ] Tất cả files đã upload
- [ ] Cấu trúc thư mục đúng
- [ ] PHP version >= 7.4
- [ ] File permissions đúng (644/755)
- [ ] Debug tool chạy OK (không có lỗi đỏ)
- [ ] Trang chủ load được
- [ ] Đăng nhập/Đăng ký hoạt động
- [ ] Booking flow hoạt động
- [ ] **ĐÃ XÓA `debug.php`**
- [ ] **ĐÃ TẮT display_errors**

---

## 🆘 Nếu Vẫn Bị Lỗi

### Gửi cho tôi thông tin sau:
1. Screenshot lỗi 500
2. Nội dung file `error.log`
3. Kết quả chạy `debug.php`
4. PHP version đang dùng
5. Thông báo lỗi trong Console (F12 → Console)

### Liên hệ support hosting:
Nếu lỗi liên quan đến server (PHP settings, permissions), liên hệ support hosting với thông tin:
- Tôi cần bật PHP extension: PDO, PDO_MySQL, mbstring
- Tôi cần PHP version 7.4 trở lên
- Tôi cần quyền ghi vào thư mục X

---

## 📚 Tài Liệu Tham Khảo

- [PHP PDO Documentation](https://www.php.net/manual/en/book.pdo.php)
- [cPanel Documentation](https://docs.cpanel.net/)
- [MySQL Import/Export](https://dev.mysql.com/doc/)

---

**✨ Chúc bạn deploy thành công!**
