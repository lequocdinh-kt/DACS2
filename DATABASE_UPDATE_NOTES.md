# 🔧 CẬP NHẬT: Database Schema Đã Được Tối Ưu

## ⚠️ Vấn Đề Cũ
File `database_schema.sql` quá dài (562 dòng) → Linting bị vô hiệu hóa

## ✅ Giải Pháp Mới
Đã tách thành **3 file riêng biệt** để dễ quản lý:

### 📁 Các File Mới

#### 1. **database_core.sql** (Bảng chính - ~270 dòng)
- 13 bảng chính của hệ thống
- Indexes đầy đủ
- Foreign keys
- **Sử dụng:** Tạo cấu trúc database cơ bản

#### 2. **database_sample_data.sql** (Dữ liệu mẫu - ~90 dòng)
- 3 users (admin/user1/manager)
- 3 phòng chiếu
- 180 ghế (tự động tạo)
- 4 phim mẫu
- Lịch chiếu 7 ngày
- **Sử dụng:** Testing và demo

#### 3. **database_views_procedures.sql** (Views & Procedures - ~100 dòng)
- Views: `v_movie_statistics`, `v_booking_details`
- Triggers: Auto cleanup, auto update seat count
- Stored procedures: Revenue statistics
- **Sử dụng:** Tối ưu queries và automation

---

## 🚀 Cách Import (3 Phương Pháp)

### Phương Pháp 1: phpMyAdmin (Khuyến Nghị)

```
1. Mở http://localhost/phpmyadmin
2. Click "Import" → Chọn database_core.sql → Go
3. Click "Import" → Chọn database_sample_data.sql → Go  
4. Click "Import" → Chọn database_views_procedures.sql → Go
```

### Phương Pháp 2: MySQL Command Line

```bash
# Windows PowerShell
cd "e:\school\hoc ki 1 2025-2026\DACS2"

# Import core tables
& "C:\xampp\mysql\bin\mysql.exe" -u root < database_core.sql

# Import sample data
& "C:\xampp\mysql\bin\mysql.exe" -u root < database_sample_data.sql

# Import views & procedures
& "C:\xampp\mysql\bin\mysql.exe" -u root < database_views_procedures.sql
```

### Phương Pháp 3: All-in-One Script

```sql
-- Trong MySQL Workbench hoặc phpMyAdmin SQL tab
SOURCE e:/school/hoc ki 1 2025-2026/DACS2/database_core.sql;
SOURCE e:/school/hoc ki 1 2025-2026/DACS2/database_sample_data.sql;
SOURCE e:/school/hoc ki 1 2025-2026/DACS2/database_views_procedures.sql;
```

---

## 🎯 Import Nhanh (Chỉ Cần Essentials)

Nếu chỉ cần cấu trúc database (không cần dữ liệu mẫu):

```bash
# Chỉ import core tables
& "C:\xampp\mysql\bin\mysql.exe" -u root < database_core.sql
```

---

## 📊 So Sánh

| Tiêu Chí | File Cũ | Files Mới |
|----------|---------|-----------|
| **Số dòng** | 562 dòng | 270 + 90 + 100 dòng |
| **Linting** | ❌ Disabled | ✅ Enabled |
| **Quản lý** | ❌ Khó | ✅ Dễ |
| **Tốc độ** | 🐢 Chậm | ⚡ Nhanh |
| **Debug** | ❌ Khó tìm lỗi | ✅ Dễ debug |
| **Modularity** | ❌ Monolithic | ✅ Modular |

---

## ✨ Lợi Ích Của Cách Mới

### 🔧 **Dễ Bảo Trì**
- Sửa cấu trúc bảng → Chỉ sửa `database_core.sql`
- Thêm dữ liệu test → Chỉ sửa `database_sample_data.sql`
- Thêm view/trigger → Chỉ sửa `database_views_procedures.sql`

### ⚡ **Nhanh Hơn**
- File nhỏ → Import nhanh
- Linting hoạt động → Phát hiện lỗi ngay
- Dễ đọc → Tìm code nhanh

### 🎯 **Linh Hoạt**
- Development: Import core + sample data
- Production: Chỉ import core
- Testing: Import all
- Reset data: Chỉ re-import sample data

### 🛡️ **An Toàn Hơn**
- Tách biệt cấu trúc và dữ liệu
- Dễ backup từng phần
- Rollback dễ dàng

---

## 📝 Nội Dung Từng File

### database_core.sql
```sql
✅ 13 bảng chính
✅ All foreign keys
✅ All indexes
✅ Roles data (Admin/User/Manager)
❌ Không có dữ liệu test
❌ Không có views/triggers
```

### database_sample_data.sql
```sql
✅ 3 users (password: 123456)
✅ 3 rooms (60, 80, 100 ghế)
✅ 180 ghế tự động
✅ 4 phim mẫu
✅ 210 lịch chiếu (7 ngày x 3 phim x 5 suất x 2 phòng)
```

### database_views_procedures.sql
```sql
✅ v_movie_statistics (thống kê phim)
✅ v_booking_details (chi tiết đơn hàng)
✅ Triggers (auto cleanup, auto count)
✅ Stored procedures (revenue stats)
```

---

## 🔄 Migration Từ File Cũ

Nếu đã import file cũ `database_schema.sql`:

```sql
-- Không cần làm gì! Cấu trúc giống hệt nhau
-- Chỉ khác cách tổ chức file

-- Nếu muốn reset lại:
DROP DATABASE IF EXISTS dacs2;

-- Sau đó import 3 file mới
```

---

## 🐛 Kiểm Tra Sau Import

```sql
USE dacs2;

-- Kiểm tra số bảng (phải có 13 bảng)
SHOW TABLES;

-- Kiểm tra users
SELECT COUNT(*) FROM user; -- Kết quả: 3

-- Kiểm tra ghế
SELECT COUNT(*) FROM seats; -- Kết quả: 240 (60+80+100)

-- Kiểm tra lịch chiếu
SELECT COUNT(*) FROM showtimes; -- Kết quả: 210

-- Kiểm tra views
SELECT * FROM v_movie_statistics;
```

---

## 💡 Tips

### Import Chỉ Core (Production)
```bash
& "C:\xampp\mysql\bin\mysql.exe" -u root < database_core.sql
```

### Reset Chỉ Sample Data (Testing)
```sql
-- Xóa data cũ
TRUNCATE TABLE booking_promotions;
TRUNCATE TABLE bookingseats;
TRUNCATE TABLE bookings;
TRUNCATE TABLE seatlocks;
TRUNCATE TABLE payments;
TRUNCATE TABLE showtimes;
TRUNCATE TABLE seats;
TRUNCATE TABLE rooms;
TRUNCATE TABLE movie;
DELETE FROM user WHERE userID > 0;

-- Import lại
SOURCE database_sample_data.sql;
```

### Backup Database
```bash
# Backup toàn bộ
& "C:\xampp\mysql\bin\mysqldump.exe" -u root dacs2 > backup.sql

# Backup chỉ structure (không data)
& "C:\xampp\mysql\bin\mysqldump.exe" -u root --no-data dacs2 > structure.sql
```

---

## 🎉 Kết Luận

✅ **File cũ:** Vẫn hoạt động bình thường  
✅ **Files mới:** Tối ưu hơn, dễ quản lý hơn  
✅ **Cấu trúc:** Giống hệt nhau 100%  
✅ **Linting:** Đã fix, không còn warning  

**Khuyến nghị:** Sử dụng 3 file mới cho dễ maintain! 🚀
