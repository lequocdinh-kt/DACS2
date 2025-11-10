# 📊 HƯỚNG DẪN IMPORT DATABASE

## 🎯 Các Bước Import Database

### Phương Pháp 1: Sử dụng phpMyAdmin (Dễ nhất)

1. **Mở phpMyAdmin:**
   - Truy cập: `http://localhost/phpmyadmin`
   - Đăng nhập với username: `root`, password: (để trống)

2. **Tạo Database mới:**
   - Click tab "Databases" trên menu
   - Tên database: `dacs2`
   - Collation: `utf8mb4_unicode_ci`
   - Click "Create"

3. **Import File SQL:**
   - Click vào database `dacs2` vừa tạo
   - Click tab "Import"
   - Click "Choose File" → Chọn file `database_schema.sql`
   - Cuộn xuống dưới → Click "Go"
   - Chờ import hoàn tất ✅

### Phương Pháp 2: Sử dụng MySQL Command Line

```bash
# 1. Mở Command Prompt (Windows) hoặc Terminal (Mac/Linux)

# 2. Vào thư mục chứa file SQL
cd "e:\school\hoc ki 1 2025-2026\DACS2"

# 3. Login vào MySQL
mysql -u root -p

# 4. Tạo database (nếu chưa có)
CREATE DATABASE IF NOT EXISTS dacs2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 5. Sử dụng database
USE dacs2;

# 6. Import file SQL
SOURCE database_schema.sql;

# 7. Kiểm tra kết quả
SHOW TABLES;

# 8. Thoát
EXIT;
```

### Phương Pháp 3: Import trực tiếp từ Command Line (Nhanh nhất)

```bash
# Windows
cd "e:\school\hoc ki 1 2025-2026\DACS2"
"C:\xampp\mysql\bin\mysql.exe" -u root -p < database_schema.sql

# Mac/Linux
cd "/path/to/DACS2"
mysql -u root -p < database_schema.sql
```

---

## 📋 Cấu Trúc Database Sau Khi Import

### Tổng Quan Các Bảng

```
┌─────────────────────────────────────────────────────────┐
│                    DATABASE: dacs2                      │
├─────────────────────────────────────────────────────────┤
│  1. roles              - Vai trò người dùng             │
│  2. user               - Thông tin người dùng           │
│  3. movie              - Danh sách phim                 │
│  4. rooms              - Phòng chiếu                    │
│  5. seats              - Ghế ngồi                       │
│  6. showtimes          - Lịch chiếu phim               │
│  7. seatlocks          - Lock ghế tạm thời             │
│  8. bookings           - Đơn đặt vé                    │
│  9. bookingseats       - Chi tiết ghế đã đặt           │
│ 10. payments           - Thanh toán                     │
│ 11. reviews            - Đánh giá phim (optional)      │
│ 12. promotions         - Mã khuyến mãi (optional)      │
│ 13. booking_promotions - Áp dụng KM (optional)         │
└─────────────────────────────────────────────────────────┘
```

### Chi Tiết Từng Bảng

#### 1. **roles** (Vai trò)
```sql
roleID | roleName | description
-------|----------|-------------
1      | Admin    | Quản trị viên hệ thống
2      | User     | Người dùng thường
3      | Manager  | Quản lý rạp
```

#### 2. **user** (Người dùng)
```sql
userID | username | email | password | phone | roleID
-------|----------|-------|----------|-------|-------
- Lưu thông tin người dùng
- Password đã được hash (bcrypt)
- Foreign Key: roleID → roles
```

#### 3. **movie** (Phim)
```sql
movieID | title | genre | duration | rating | movieStatus
--------|-------|-------|----------|--------|------------
- movieStatus: 'now_showing', 'coming_soon', 'stopped'
- rating: 0.0 - 10.0
- duration: phút
```

#### 4. **rooms** (Phòng chiếu)
```sql
roomID | roomName       | roomType | totalSeats
-------|----------------|----------|------------
1      | VKU Cinema 1   | 2D       | 60
2      | VKU Cinema 2   | 3D       | 80
3      | VKU Cinema 3   | IMAX     | 100
```

#### 5. **seats** (Ghế ngồi)
```sql
seatID | roomID | seatRow | seatNumber | seatType | price
-------|--------|---------|------------|----------|-------
- seatType: 'standard', 'vip', 'couple'
- seatRow: A, B, C, D...
- seatNumber: 1, 2, 3...
- Unique: (roomID, seatRow, seatNumber)
```

#### 6. **showtimes** (Lịch chiếu)
```sql
showtimeID | movieID | roomID | showDate | showTime | status
-----------|---------|--------|----------|----------|--------
- Foreign Key: movieID → movie
- Foreign Key: roomID → rooms
- status: 'available', 'full', 'cancelled'
```

#### 7. **seatlocks** (Lock ghế tạm thời)
```sql
lockID | showtimeID | seatID | userID | lockedAt | expiresAt
-------|------------|--------|--------|----------|----------
- Ghế bị lock trong 10-15 phút
- Auto cleanup khi hết hạn
- Unique: (showtimeID, seatID)
```

#### 8. **bookings** (Đơn đặt vé)
```sql
bookingID | userID | showtimeID | bookingCode | totalPrice | paymentStatus
----------|--------|------------|-------------|------------|---------------
- bookingCode: VKU202511100001 (unique)
- paymentStatus: 'pending', 'paid', 'cancelled', 'expired'
- expiredAt: Hết hạn sau 15 phút nếu chưa thanh toán
```

#### 9. **bookingseats** (Chi tiết ghế)
```sql
bookingSeatID | bookingID | seatID | price
--------------|-----------|--------|------
- Lưu ghế nào được đặt trong đơn
- price: Giá tại thời điểm đặt
```

#### 10. **payments** (Thanh toán)
```sql
paymentID | bookingID | amount | paymentMethod | paymentStatus
----------|-----------|--------|---------------|---------------
- paymentMethod: 'qr', 'momo', 'vnpay', 'banking', 'cash'
- paymentStatus: 'pending', 'completed', 'failed', 'refunded'
```

---

## 🔐 Tài Khoản Mặc Định

### Danh Sách User Mẫu (Password: `123456`)

| Username | Email                    | Role    | Mô tả              |
|----------|--------------------------|---------|-------------------|
| admin    | admin@vkucinema.com      | Admin   | Quản trị viên     |
| user1    | user1@gmail.com          | User    | Người dùng thường |
| manager  | manager@vkucinema.com    | Manager | Quản lý rạp       |

### Cách Đăng Nhập

1. Truy cập: `http://localhost/src/views/login.php`
2. Nhập email và password: `123456`
3. Click "Đăng nhập"

---

## 📊 Dữ Liệu Mẫu Đã Có

### Phòng Chiếu
- ✅ **VKU Cinema 1** (2D): 60 ghế (A1-F10)
- ✅ **VKU Cinema 2** (3D): 80 ghế (A1-H10)
- ✅ **VKU Cinema 3** (IMAX): 100 ghế (A1-J10)

### Ghế Ngồi
- ✅ **180 ghế** đã được tạo sẵn
- Giá ghế:
  - Standard: 45,000đ - 55,000đ
  - VIP: 75,000đ - 90,000đ
  - Couple: 150,000đ

### Phim Mẫu
- ✅ Avengers: Endgame (Đang chiếu)
- ✅ The Batman (Đang chiếu)
- ✅ Spider-Man: No Way Home (Đang chiếu)
- ✅ Avatar: The Way of Water (Sắp chiếu)

### Lịch Chiếu
- ✅ **7 ngày** lịch chiếu cho các phim đang chiếu
- ✅ **5 suất/ngày**: 09:00, 12:00, 15:00, 18:00, 21:00
- ✅ Nhiều phòng chiếu

---

## ✅ Kiểm Tra Database Sau Import

### 1. Kiểm tra số lượng bảng

```sql
USE dacs2;
SHOW TABLES;
-- Kết quả: Nên có 13 bảng
```

### 2. Kiểm tra dữ liệu user

```sql
SELECT userID, username, email, roleID FROM user;
-- Kết quả: Nên có 3 users (admin, user1, manager)
```

### 3. Kiểm tra phòng chiếu và ghế

```sql
-- Kiểm tra phòng
SELECT * FROM rooms;

-- Kiểm tra số ghế mỗi phòng
SELECT roomID, COUNT(*) as totalSeats FROM seats GROUP BY roomID;
-- Kết quả: Room 1: 60, Room 2: 80, Room 3: 100
```

### 4. Kiểm tra phim

```sql
SELECT movieID, title, movieStatus, rating FROM movie;
-- Kết quả: Nên có 4 phim
```

### 5. Kiểm tra lịch chiếu

```sql
SELECT 
    st.showtimeID,
    m.title,
    r.roomName,
    st.showDate,
    st.showTime
FROM showtimes st
INNER JOIN movie m ON st.movieID = m.movieID
INNER JOIN rooms r ON st.roomID = r.roomID
ORDER BY st.showDate, st.showTime
LIMIT 10;
-- Kết quả: Nên có nhiều lịch chiếu
```

### 6. Kiểm tra Views

```sql
-- Xem thống kê phim
SELECT * FROM v_movie_statistics;

-- Xem chi tiết booking (nếu có)
SELECT * FROM v_booking_details LIMIT 10;
```

---

## 🛠️ Sửa Lỗi Thường Gặp

### ❌ Lỗi: "Table already exists"

**Nguyên nhân:** Database đã tồn tại

**Giải pháp:**
```sql
-- Xóa database cũ (CẨN THẬN: Mất hết dữ liệu!)
DROP DATABASE IF EXISTS dacs2;

-- Import lại file SQL
SOURCE database_schema.sql;
```

### ❌ Lỗi: "Foreign key constraint fails"

**Nguyên nhân:** Thứ tự tạo bảng sai

**Giải pháp:**
```sql
-- Tắt foreign key check tạm thời
SET FOREIGN_KEY_CHECKS = 0;

-- Import file SQL
SOURCE database_schema.sql;

-- Bật lại foreign key check
SET FOREIGN_KEY_CHECKS = 1;
```

### ❌ Lỗi: "Access denied for user 'root'"

**Nguyên nhân:** Không có quyền truy cập

**Giải pháp:**
```bash
# Đảm bảo XAMPP/WAMP đang chạy
# Kiểm tra username/password trong phpMyAdmin

# Hoặc reset password MySQL
mysql -u root
ALTER USER 'root'@'localhost' IDENTIFIED BY '';
FLUSH PRIVILEGES;
```

### ❌ Lỗi: "Cannot connect to MySQL server"

**Nguyên nhân:** MySQL service chưa chạy

**Giải pháp:**
1. Mở XAMPP Control Panel
2. Click "Start" ở dòng MySQL
3. Đợi MySQL chuyển sang màu xanh
4. Thử import lại

---

## 🔄 Reset Database (Xóa tất cả dữ liệu)

### Reset toàn bộ

```sql
-- Cách 1: Drop và tạo lại
DROP DATABASE IF EXISTS dacs2;
SOURCE database_schema.sql;

-- Cách 2: Xóa từng bảng (giữ cấu trúc)
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE booking_promotions;
TRUNCATE TABLE bookingseats;
TRUNCATE TABLE bookings;
TRUNCATE TABLE seatlocks;
TRUNCATE TABLE payments;
TRUNCATE TABLE reviews;
TRUNCATE TABLE showtimes;
TRUNCATE TABLE seats;
TRUNCATE TABLE rooms;
TRUNCATE TABLE movie;
TRUNCATE TABLE user;
TRUNCATE TABLE roles;
SET FOREIGN_KEY_CHECKS = 1;

-- Import lại dữ liệu mẫu
SOURCE database_schema.sql;
```

---

## 📝 Cập Nhật Cấu Hình PHP

Sau khi import database, kiểm tra file `config.php`:

```php
<?php
// Localhost
define('DB_HOST', 'localhost');
define('DB_NAME', 'dacs2');          // ← Đảm bảo đúng tên database
define('DB_USER', 'root');           // ← Username MySQL
define('DB_PASS', '');               // ← Password MySQL (XAMPP: để trống)
?>
```

Hoặc file `src/models/database.php`:

```php
<?php
$host = 'localhost';
$dbname = 'dacs2';      // ← Đảm bảo đúng tên database
$username = 'root';     // ← Username MySQL
$password = '';         // ← Password MySQL
?>
```

---

## 🎉 Hoàn Tất!

Bây giờ bạn có thể:
- ✅ Đăng nhập với tài khoản mẫu
- ✅ Xem danh sách phim
- ✅ Chọn suất chiếu
- ✅ Đặt vé xem phim
- ✅ Thanh toán

---

## 📞 Hỗ Trợ

Nếu gặp vấn đề, vui lòng kiểm tra:
1. XAMPP/WAMP đã chạy chưa?
2. MySQL service có màu xanh không?
3. File `config.php` đã đúng chưa?
4. Database `dacs2` đã tồn tại chưa?
5. Có lỗi nào trong error log không?

**Good luck! 🚀**
