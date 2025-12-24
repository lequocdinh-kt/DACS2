# Hướng dẫn sửa lỗi và cập nhật Showtimes

## Lỗi đã xảy ra
```
#1054 - Unknown column 'showtimeDate' in 'field list'
```

**Nguyên nhân**: Database sử dụng 2 cột riêng biệt `showDate` (DATE) và `showTime` (TIME), không phải 1 cột `showtimeDate` (DATETIME).

## Các file đã được sửa

### 1. database_showtimes_update_fixed.sql ✅
- File SQL mới với cấu trúc đúng
- Chứa 200+ suất chiếu từ 25/12/2024 đến 15/01/2025
- Sử dụng: `INSERT INTO showtimes (movieID, roomID, showDate, showTime, basePrice, status)`

### 2. adminController_fixed.php ✅
**Các thay đổi chính:**
- Line 143: `CONCAT(s.showDate, ' ', s.showTime) as showtimeDate` để hiển thị
- Line 147: `WHERE s.showDate = :date` thay vì `WHERE DATE(s.showtimeDate) = :date`
- Line 175: `SELECT *, CONCAT(showDate, ' ', showTime) as showtimeDate` 
- Line 195: `INSERT INTO showtimes (movieID, roomID, showDate, showTime, ...)`
- Line 224: `UPDATE showtimes SET ... showDate = :showDate, showTime = :showTime, ...`

### 3. admin.js ✅
**Các thay đổi chính:**
- Line 338-341: Gửi `showDate` và `showTime` riêng biệt thay vì kết hợp thành `showtimeDate`
```javascript
formData.set('showDate', date);
formData.set('showTime', `${time}:00`);
formData.delete('showtimeDate');
formData.delete('showtimeTime');
```

## Cách cập nhật hệ thống

### Bước 1: Sao lưu database hiện tại
```sql
-- Export bảng showtimes cũ (nếu cần)
mysqldump -u root -p dacs2 showtimes > showtimes_backup.sql
```

### Bước 2: Xóa dữ liệu showtimes cũ
```sql
-- Xóa các suất chiếu cũ đã hết hạn
DELETE FROM showtimes WHERE showDate < '2024-12-25';

-- Hoặc xóa tất cả để import lại
TRUNCATE TABLE showtimes;
```

### Bước 3: Import showtimes mới
1. Mở phpMyAdmin
2. Chọn database `dacs2`
3. Vào tab "Import"
4. Chọn file `database_showtimes_update_fixed.sql`
5. Click "Go"

Hoặc dùng lệnh:
```bash
mysql -u root -p dacs2 < database_showtimes_update_fixed.sql
```

### Bước 4: Thay thế file adminController.php
```bash
# Xóa file cũ (hoặc đổi tên)
mv src/controllers/adminController.php src/controllers/adminController_old.php

# Đổi tên file mới
mv src/controllers/adminController_fixed.php src/controllers/adminController.php
```

Hoặc thủ công:
1. Xóa hoặc đổi tên `src/controllers/adminController.php` cũ
2. Đổi tên `src/controllers/adminController_fixed.php` thành `adminController.php`

### Bước 5: admin.js đã được cập nhật tự động ✅
File `src/js/admin.js` đã được sửa trực tiếp, không cần thao tác thêm.

## Kiểm tra sau khi cập nhật

### 1. Kiểm tra database
```sql
-- Xem cấu trúc bảng
DESCRIBE showtimes;

-- Đếm số suất chiếu mới
SELECT COUNT(*) FROM showtimes WHERE showDate >= '2024-12-25';

-- Xem một vài suất chiếu
SELECT *, CONCAT(showDate, ' ', showTime) as showtimeDateTime 
FROM showtimes 
ORDER BY showDate, showTime 
LIMIT 10;
```

### 2. Kiểm tra trang Admin
1. Đăng nhập với tài khoản admin (roleID = 1)
2. Truy cập trang Admin từ menu header
3. Kiểm tra các chức năng:
   - ✅ Thống kê hiển thị đúng
   - ✅ Biểu đồ doanh thu hoạt động
   - ✅ Danh sách suất chiếu hiển thị (chọn ngày từ 25/12/2024 trở đi)
   - ✅ Thêm suất chiếu mới
   - ✅ Sửa suất chiếu
   - ✅ Xóa suất chiếu (nếu chưa có vé đặt)

### 3. Test CRUD operations
```javascript
// Test trong Console của trình duyệt
// 1. Lấy danh sách suất chiếu
fetch('/src/controllers/adminController.php?action=getShowtimes&date=2024-12-25')
    .then(r => r.json())
    .then(d => console.log(d));

// 2. Thêm suất chiếu
const formData = new FormData();
formData.append('action', 'addShowtime');
formData.append('movieID', '1');
formData.append('roomID', '1');
formData.append('showDate', '2025-01-16');
formData.append('showTime', '09:00:00');
formData.append('basePrice', '45000');
formData.append('showtimeStatus', 'available');

fetch('/src/controllers/adminController.php', {
    method: 'POST',
    body: formData
}).then(r => r.json()).then(d => console.log(d));
```

## Cấu trúc Database chính xác

```sql
CREATE TABLE showtimes (
    showtimeID INT AUTO_INCREMENT PRIMARY KEY,
    movieID INT NOT NULL,
    roomID INT NOT NULL,
    showDate DATE NOT NULL,           -- Ngày chiếu (2024-12-25)
    showTime TIME NOT NULL,           -- Giờ chiếu (09:00:00)
    basePrice DECIMAL(10,2) NOT NULL, -- Giá vé cơ bản
    status ENUM('available', 'sold_out', 'cancelled') DEFAULT 'available',
    FOREIGN KEY (movieID) REFERENCES movie(movieID),
    FOREIGN KEY (roomID) REFERENCES rooms(roomID)
);
```

## Lưu ý quan trọng

1. **Không sử dụng cột `showtimeDate`** - Cột này không tồn tại trong database
2. **Sử dụng CONCAT** khi cần kết hợp: `CONCAT(showDate, ' ', showTime)`
3. **Filter theo showDate**: `WHERE showDate = '2024-12-25'`
4. **Thời gian suất chiếu**: Có các khung 09:00, 12:00, 15:00, 18:00, 21:00
5. **Giá vé**: 45,000đ (phòng Standard) hoặc 80,000đ (phòng VIP)

## Troubleshooting

### Lỗi: "Unknown column 'showtimeDate'"
➡️ Bạn chưa cập nhật adminController.php hoặc admin.js

### Lỗi: "Duplicate entry" khi import
➡️ Xóa dữ liệu cũ trước: `TRUNCATE TABLE showtimes;`

### Không hiển thị suất chiếu
➡️ Kiểm tra ngày lọc, chọn từ 25/12/2024 trở đi

### Lỗi khi thêm/sửa suất chiếu
➡️ Mở Console (F12) để xem lỗi chi tiết
➡️ Kiểm tra adminController.php đã được thay thế chưa

## Hỗ trợ

Nếu gặp vấn đề, kiểm tra:
1. Database có cột `showDate` và `showTime` (không phải `showtimeDate`)
2. File adminController.php đã được thay thế bằng version mới
3. File admin.js đã được cập nhật (line 338-341)
4. Import file SQL đúng: `database_showtimes_update_fixed.sql`
