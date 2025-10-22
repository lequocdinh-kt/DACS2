# 📦 HƯỚNG DẪN EXPORT/IMPORT DATABASE

## 🎯 Hệ Thống Tự Động Phát Hiện Môi Trường

Code của bạn giờ đây **TỰ ĐỘNG** biết đang chạy ở đâu:
- 🏠 **Localhost** (XAMPP) → Dùng database `dacs2`
- ☁️ **Hosting** → Dùng database `slrnkpifhosting_DACS2`

**KHÔNG CẦN SỬA CODE** khi chuyển giữa localhost và hosting!

---

## 📤 BƯỚC 1: EXPORT DATABASE Từ XAMPP

### Cách 1: Dùng phpMyAdmin (KHUYẾN NGHỊ)

1. Mở **phpMyAdmin**: http://localhost/phpmyadmin
2. Click vào database **`dacs2`** ở bên trái
3. Click tab **Export** ở trên
4. Chọn:
   - Export method: **Quick**
   - Format: **SQL**
5. Click **Go** → File `.sql` sẽ được tải về
6. Lưu file với tên: `dacs2_export.sql`

### Cách 2: Dùng Script (Tự động)

Mở PowerShell và chạy:

```powershell
cd "e:\school\hoc ki 1 2025-2026\dacs2"
.\export_database.bat
```

Hoặc click đúp vào file `export_database.bat`

File SQL sẽ được tạo với tên: `dacs2_export_YYYYMMDD.sql`

### Cách 3: Dùng Command Line

```powershell
# Mở PowerShell và chạy:
cd "C:\xampp\mysql\bin"
.\mysqldump.exe -u root dacs2 > "e:\school\hoc ki 1 2025-2026\dacs2\dacs2_export.sql"
```

---

## 📥 BƯỚC 2: IMPORT DATABASE LÊN HOSTING

### A. Tạo Database trên cPanel (Nếu chưa có)

1. Đăng nhập **cPanel**
2. Vào **MySQL Databases**
3. Tạo database mới:
   - Database Name: `DACS2` (hệ thống tự thêm prefix: `slrnkpifhosting_DACS2`)
4. Tạo user mới (hoặc dùng user có sẵn):
   - Username: `xiaoying` (prefix: `slrnkpifhosting_xiaoying`)
   - Password: `2D3i$>?+ZZ!`_bc` (hoặc tạo password mới)
5. **Add User To Database**:
   - Chọn user: `slrnkpifhosting_xiaoying`
   - Chọn database: `slrnkpifhosting_DACS2`
   - Grant quyền: **ALL PRIVILEGES**

### B. Import File SQL

#### Cách 1: Dùng phpMyAdmin (File < 50MB)

1. Vào **cPanel** → **phpMyAdmin**
2. Click vào database **`slrnkpifhosting_DACS2`** bên trái
3. Click tab **Import**
4. Click **Choose File** → Chọn file `dacs2_export.sql`
5. Scroll xuống dưới → Click **Go**
6. Đợi import xong → Thấy thông báo "Import has been successfully finished"

#### Cách 2: Dùng MySQL Database Wizard (File lớn)

1. Vào **cPanel** → **MySQL Database Wizard**
2. Follow các bước tạo database
3. Upload file SQL qua **File Manager**
4. Import bằng command line trong **Terminal**:

```bash
mysql -u slrnkpifhosting_xiaoying -p slrnkpifhosting_DACS2 < dacs2_export.sql
```

---

## ✅ BƯỚC 3: KIỂM TRA KẾT QUẢ

### Kiểm tra trên Hosting:

1. Vào **phpMyAdmin** trên cPanel
2. Click database `slrnkpifhosting_DACS2`
3. Xem danh sách bảng, phải có:
   - ✅ `Bookings`
   - ✅ `BookingSeats`
   - ✅ `Movie`
   - ✅ `Rooms`
   - ✅ `Seats`
   - ✅ `Showtimes`
   - ✅ `Users`

### Test Website:

Truy cập: **https://lequocdinh.id.vn/test_booking.php**

Kỳ vọng tất cả test PASS (màu xanh).

---

## 🔄 CẬP NHẬT DATABASE SAU NÀY

Khi bạn thêm phim, suất chiếu mới trên localhost và muốn sync lên hosting:

### Export + Import lại toàn bộ:

```powershell
# 1. Export từ XAMPP
cd "e:\school\hoc ki 1 2025-2026\dacs2"
.\export_database.bat

# 2. Upload file .sql lên hosting
# 3. Import vào phpMyAdmin như bước 2B
```

### Hoặc chỉ export dữ liệu (không cấu trúc):

```sql
-- Trong phpMyAdmin XAMPP, Export với options:
- Export method: Custom
- Tables: Chọn bảng cần export (Movie, Showtimes, etc.)
- Format-specific options:
  ✅ Data: Chỉ chọn data
  ❌ Structure: Bỏ chọn
```

---

## 🎛️ CẤU HÌNH FILE `config.php`

File này **ĐÃ ĐƯỢC CẤU HÌNH SẴN**, bạn KHÔNG cần sửa gì!

### Kiểm tra lại (nếu cần):

```php
if ($isLocalhost) {
    // XAMPP
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'dacs2');
    define('DB_USER', 'root');
    define('DB_PASS', '');
} else {
    // HOSTING
    define('DB_HOST', 'onehost-webhn072403.000nethost.com');
    define('DB_NAME', 'slrnkpifhosting_DACS2');
    define('DB_USER', 'slrnkpifhosting_xiaoying');
    define('DB_PASS', '2D3i$>?+ZZ!`_bc');
}
```

---

## ⚠️ LƯU Ý QUAN TRỌNG

### 1. Tên Bảng Phân Biệt Hoa Thường

Linux (hosting) phân biệt hoa/thường:
- ✅ `Showtimes` (đúng)
- ❌ `showtimes` (sai)

**Giải pháp:** Khi tạo bảng, đặt tên giống y hệt trên XAMPP.

### 2. Charset & Collation

Khi import, đảm bảo:
- Charset: `utf8mb4`
- Collation: `utf8mb4_unicode_ci`

### 3. Auto Increment

Nếu database hosting đã có data cũ, xóa hết trước khi import:

```sql
-- Trong phpMyAdmin, chọn database → tab SQL:
DROP TABLE IF EXISTS Bookings;
DROP TABLE IF EXISTS BookingSeats;
DROP TABLE IF EXISTS Showtimes;
DROP TABLE IF EXISTS Seats;
DROP TABLE IF EXISTS Rooms;
DROP TABLE IF EXISTS Movie;
DROP TABLE IF EXISTS Users;
```

Sau đó import file SQL mới.

---

## 🐛 XỬ LÝ LỖI THƯỜNG GẶP

### ❌ Lỗi: "Table already exists"

**Cách fix:**
- Trong phpMyAdmin, xóa bảng cũ trước khi import
- Hoặc check option "DROP TABLE IF EXISTS" khi export

### ❌ Lỗi: "Max execution time exceeded"

**Cách fix:**
- Upload file qua File Manager
- Import qua SSH/Terminal thay vì phpMyAdmin

### ❌ Lỗi: "Access denied for user"

**Cách fix:**
- Kiểm tra lại username/password trong `config.php`
- Đảm bảo user đã được add vào database với ALL PRIVILEGES

---

## 📋 CHECKLIST

- [ ] Export database từ XAMPP thành công
- [ ] File .sql có dung lượng > 0 KB
- [ ] Database trên hosting đã được tạo
- [ ] User đã được add vào database với ALL PRIVILEGES
- [ ] Import file .sql thành công
- [ ] Kiểm tra trong phpMyAdmin: tất cả bảng đã có
- [ ] Test website: https://lequocdinh.id.vn/test_booking.php
- [ ] Tất cả test PASS (màu xanh)

---

## 🚀 SAU KHI HOÀN TẤT

1. ✅ Localhost (XAMPP) → Hoạt động bình thường
2. ✅ Hosting → Hoạt động bình thường
3. ✅ **KHÔNG CẦN SỬA CODE** khi chuyển đổi!

**Chúc mừng! Bạn đã setup xong hệ thống đa môi trường!** 🎉
