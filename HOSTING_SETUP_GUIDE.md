# 🔧 HƯỚNG DẪN CẤU HÌNH HOSTING & LOCALHOST

## 📋 MỤC LỤC
1. [Cấu hình XAMPP (Localhost)](#1-cấu-hình-xampp-localhost)
2. [Cấu hình cPanel (Hosting)](#2-cấu-hình-cpanel-hosting)
3. [Kiểm tra kết nối](#3-kiểm-tra-kết-nối)
4. [Xử lý lỗi thường gặp](#4-xử-lý-lỗi-thường-gặp)

---

## 1. CẤU HÌNH XAMPP (LOCALHOST)

### Bước 1: Khởi động XAMPP
```
- Mở XAMPP Control Panel
- Start Apache
- Start MySQL
```

### Bước 2: Tạo Database
1. Truy cập: http://localhost/phpmyadmin
2. Tạo database mới tên: `dacs2`
3. Chọn Collation: `utf8mb4_unicode_ci`

### Bước 3: Import Database
Import theo thứ tự:
1. `database_core.sql` - Tạo cấu trúc tables
2. `database_sample_data.sql` - Thêm dữ liệu mẫu
3. `database_views_procedures.sql` - Thêm views và procedures

### Bước 4: Kiểm tra config.php
```php
// Phần localhost trong config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'dacs2');
define('DB_USER', 'root');
define('DB_PASS', '');  // Để trống với XAMPP mặc định
```

### Bước 5: Test
- Truy cập: http://localhost/test_connection.php
- Kiểm tra tất cả mục phải có ✅

---

## 2. CẤU HÌNH CPANEL (HOSTING)

### Bước 1: Tạo Database trên cPanel

1. **Đăng nhập cPanel**
   - URL: https://yourhosting.com/cpanel
   - Hoặc: https://yourhosting.com:2083

2. **Vào MySQL® Databases**
   - Tìm phần "DATABASES" → Click "MySQL® Databases"

3. **Tạo Database mới**
   ```
   Create New Database:
   Tên: DACS2 (sẽ tự động thành: username_DACS2)
   → Click "Create Database"
   ```

4. **Tạo MySQL User**
   ```
   Create New MySQL User:
   Username: xiaoying
   Password: [tạo password mạnh]
   → Click "Create User"
   ```

5. **Gán User vào Database**
   ```
   Add User To Database:
   User: xiaoying
   Database: DACS2
   → Click "Add"
   → Chọn "ALL PRIVILEGES"
   → Click "Make Changes"
   ```

### Bước 2: Lấy thông tin Database

Sau khi tạo xong, bạn sẽ có:
```
Database Name: slrnkpifhosting_DACS2
Database User: slrnkpifhosting_xiaoying
Database Password: [password bạn vừa tạo]
Database Host: localhost (hầu hết hosting dùng localhost)
```

### Bước 3: Import Database vào cPanel

**Cách 1: Dùng phpMyAdmin**
1. Vào cPanel → phpMyAdmin
2. Chọn database `slrnkpifhosting_DACS2` ở sidebar trái
3. Click tab "Import"
4. Chọn file → Import theo thứ tự:
   - `database_core.sql`
   - `database_sample_data.sql`
   - `database_views_procedures.sql`

**Cách 2: Dùng File Manager**
1. Upload 3 file SQL vào thư mục chính
2. Vào cPanel → phpMyAdmin
3. Click tab "SQL"
4. Chạy lệnh:
```sql
SOURCE database_core.sql;
SOURCE database_sample_data.sql;
SOURCE database_views_procedures.sql;
```

### Bước 4: Cập nhật config.php

Sửa file `config.php` phần hosting:
```php
} else {
    // Cấu hình cho HOSTING (cPanel)
    define('DB_HOST', 'localhost');  // Hoặc IP từ cPanel
    define('DB_NAME', 'slrnkpifhosting_DACS2');  // Từ cPanel
    define('DB_USER', 'slrnkpifhosting_xiaoying');  // Từ cPanel
    define('DB_PASS', 'your_password_here');  // Password bạn vừa tạo
    
    define('BASE_URL', 'https://lequocdinh.id.vn');
    define('BASE_PATH', '/');
    
    // TẠM BẬT để debug, TẮT khi production
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}
```

### Bước 5: Upload Files lên Hosting

**Upload qua File Manager:**
1. Vào cPanel → File Manager
2. Upload tất cả files và folders vào `public_html/`
3. Đảm bảo cấu trúc:
```
public_html/
├── config.php
├── index.php
├── test_connection.php
├── database_core.sql
├── database_sample_data.sql
├── database_views_procedures.sql
└── src/
    ├── controllers/
    ├── models/
    ├── views/
    ├── styles/
    ├── js/
    └── img/
```

**Upload qua FTP (FileZilla):**
```
Host: ftp.lequocdinh.id.vn (hoặc từ cPanel)
Username: [FTP username từ cPanel]
Password: [FTP password]
Port: 21
```

### Bước 6: Set Permissions (Chmod)

Trong File Manager:
```
config.php → 644
src/ folder → 755
src/img/ → 777 (nếu cần upload ảnh)
```

---

## 3. KIỂM TRA KẾT NỐI

### Test trên Localhost:
```
http://localhost/test_connection.php
```

### Test trên Hosting:
```
https://lequocdinh.id.vn/test_connection.php
```

### Kết quả mong đợi:
- ✅ Môi trường: Hiển thị đúng LOCALHOST hoặc HOSTING
- ✅ Cấu hình: Hiển thị đúng DB_HOST, DB_NAME, DB_USER
- ✅ Kết nối Database: "KẾT NỐI THÀNH CÔNG"
- ✅ Tìm thấy 13 tables
- ✅ Tables có dữ liệu (movie, user, rooms...)
- ✅ Test query: Hiển thị danh sách phim

---

## 4. XỬ LÝ LỖI THƯỜNG GẶP

### ❌ Lỗi 1: "Access denied for user"
```
SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost'
```

**Nguyên nhân:**
- Username hoặc password sai
- Database user chưa được tạo

**Giải pháp:**
- XAMPP: Dùng `root` / không password
- Hosting: Kiểm tra lại username/password trong cPanel → MySQL Databases

---

### ❌ Lỗi 2: "Unknown database"
```
SQLSTATE[HY000] [1049] Unknown database 'dacs2'
```

**Nguyên nhân:**
- Database chưa được tạo

**Giải pháp:**
- XAMPP: Tạo database `dacs2` trong phpMyAdmin
- Hosting: Tạo database trong cPanel → MySQL Databases

---

### ❌ Lỗi 3: "Table doesn't exist"
```
Table 'dacs2.movie' doesn't exist
```

**Nguyên nhân:**
- Database rỗng, chưa import SQL

**Giải pháp:**
- Import 3 file SQL theo thứ tự

---

### ❌ Lỗi 4: "Connection refused" hoặc "Host not found"
```
SQLSTATE[HY000] [2002] Connection refused
```

**Nguyên nhân:**
- DB_HOST sai
- MySQL chưa chạy (XAMPP)

**Giải pháp Localhost:**
- Kiểm tra XAMPP MySQL đã Start chưa
- Thử đổi `localhost` thành `127.0.0.1`

**Giải pháp Hosting:**
- Hầu hết hosting dùng `localhost`
- Một số dùng IP riêng, kiểm tra trong cPanel
- File `test_connection.php` sẽ tự động thử các host khác nhau

---

### ❌ Lỗi 5: "Fatal error: Call to member function on null"
```
Fatal error: Call to a member function prepare() on null
```

**Nguyên nhân:**
- Biến `$db` bị null vì kết nối database failed

**Giải pháp:**
- Fix lỗi database connection trước
- Chạy `test_connection.php` để biết lỗi cụ thể

---

### ❌ Lỗi 6: Trang trắng, không hiện gì
```
Màn hình trắng, không có lỗi, không có nội dung
```

**Nguyên nhân:**
- PHP fatal error nhưng `display_errors = 0`
- File bị lỗi syntax hoặc logic

**Giải pháp:**
- Bật display_errors trong `config.php`:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```
- Kiểm tra error log: `public_html/error.log`

---

### ❌ Lỗi 7: Hosting hiển thị "500 Internal Server Error"
```
Internal Server Error
```

**Nguyên nhân:**
- .htaccess có vấn đề
- PHP version không tương thích
- File permission sai

**Giải pháp:**
1. Kiểm tra PHP version trong cPanel (cần ≥ 7.4)
2. Set permission: Files 644, Folders 755
3. Kiểm tra .htaccess (nếu có)
4. Xem error log trong cPanel

---

## 📌 CHECKLIST TRIỂN KHAI

### ✅ Localhost (XAMPP)
- [ ] XAMPP đã start Apache + MySQL
- [ ] Database `dacs2` đã tạo
- [ ] Import xong 3 file SQL
- [ ] `test_connection.php` hiển thị ✅
- [ ] Truy cập `http://localhost` thấy trang chủ

### ✅ Hosting (cPanel)
- [ ] Database đã tạo trong cPanel
- [ ] MySQL User đã tạo và gán vào database
- [ ] Import xong 3 file SQL vào phpMyAdmin
- [ ] Upload đầy đủ files lên `public_html/`
- [ ] Cập nhật `config.php` với thông tin database
- [ ] `test_connection.php` hiển thị ✅
- [ ] Truy cập `https://lequocdinh.id.vn` thấy trang chủ
- [ ] TẮT `display_errors` sau khi hoàn tất

---

## 🔒 BẢO MẬT SAU KHI DEPLOY

Khi website chạy ổn định, hãy:

1. **Tắt hiển thị lỗi trong config.php:**
```php
// Hosting production
ini_set('display_errors', 0);
error_reporting(0);
```

2. **Xóa file test:**
```
- test_connection.php
- test_database.php
- test_hosting.php
```

3. **Bảo vệ config.php:**
Thêm vào `.htaccess`:
```apache
<Files "config.php">
    Order Allow,Deny
    Deny from all
</Files>
```

4. **Backup định kỳ:**
- Export database từ phpMyAdmin hàng tuần
- Lưu vào Google Drive hoặc local

---

## 📞 HỖ TRỢ

Nếu gặp vấn đề:
1. Chạy `test_connection.php` → Chụp màn hình
2. Kiểm tra error.log
3. Kiểm tra lại checklist

**Lưu ý:** Sau khi fix xong, nhớ TẮT `display_errors` trên hosting! 🔒
