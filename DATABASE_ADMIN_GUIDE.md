# DATABASE STRUCTURE - Admin Guide

## 📊 Tổng quan Database

Database `dacs2` được thiết kế cho hệ thống quản lý rạp chiếu phim VKU Cinema với các bảng chính sau:

## 🗄️ Các bảng chính

### 1. **roles** - Vai trò người dùng
```sql
CREATE TABLE roles (
    roleID INT PRIMARY KEY AUTO_INCREMENT,
    roleName VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Dữ liệu:**
- `roleID = 1`: Admin (Quản trị viên)
- `roleID = 2`: User (Người dùng thường)
- `roleID = 3`: Manager (Quản lý rạp)

---

### 2. **user** - Người dùng
```sql
CREATE TABLE user (
    userID INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    dateOfBirth DATE,
    sex ENUM('male', 'female', 'other'),
    cccd VARCHAR(20),
    roleID INT DEFAULT 2,
    last_login DATETIME,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (roleID) REFERENCES roles(roleID)
);
```

**Sử dụng trong Admin:**
- Thống kê tổng số người dùng
- Quản lý tài khoản
- Phân quyền admin

---

### 3. **movie** - Phim
```sql
CREATE TABLE movie (
    movieID INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    genre VARCHAR(100),
    duration INT COMMENT 'Phút',
    description TEXT,
    rating DECIMAL(3,1) DEFAULT 0.0,
    movieStatus ENUM('now_showing', 'coming_soon', 'stopped') DEFAULT 'coming_soon',
    posterURL VARCHAR(500),
    posterHorizontalURL VARCHAR(500),
    trailerURL VARCHAR(500),
    author VARCHAR(255) COMMENT 'Đạo diễn',
    releaseDate DATE,
    ageRating VARCHAR(10) DEFAULT 'P',
    language VARCHAR(50) DEFAULT 'Vietnamese',
    country VARCHAR(50) DEFAULT 'Vietnam',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**Sử dụng trong Admin:**
- Dropdown chọn phim khi thêm suất chiếu
- Thống kê phim đang chiếu
- Top phim bán chạy

---

### 4. **rooms** - Phòng chiếu
```sql
CREATE TABLE rooms (
    roomID INT PRIMARY KEY AUTO_INCREMENT,
    roomName VARCHAR(100) NOT NULL,
    roomType ENUM('2D', '3D', 'IMAX', '4DX') DEFAULT '2D',
    totalSeats INT DEFAULT 0,
    totalRows INT DEFAULT 0,
    seatsPerRow INT DEFAULT 0,
    status ENUM('active', 'maintenance', 'closed') DEFAULT 'active',
    description TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**Dữ liệu mẫu:**
- VKU Cinema 1: 2D, 60 ghế (6x10)
- VKU Cinema 2: 3D, 80 ghế (8x10)
- VKU Cinema 3: IMAX, 100 ghế (10x10)

**Sử dụng trong Admin:**
- Dropdown chọn phòng khi thêm suất chiếu
- Lọc suất chiếu theo phòng
- Tính toán ghế trống

---

### 5. **showtimes** - Suất chiếu ⭐
```sql
CREATE TABLE showtimes (
    showtimeID INT PRIMARY KEY AUTO_INCREMENT,
    movieID INT NOT NULL,
    roomID INT NOT NULL,
    showtimeDate DATETIME NOT NULL,
    basePrice DECIMAL(10,2) NOT NULL,
    status ENUM('available', 'full', 'cancelled') DEFAULT 'available',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (movieID) REFERENCES movie(movieID),
    FOREIGN KEY (roomID) REFERENCES rooms(roomID)
);
```

**Status:**
- `available`: Có thể đặt vé
- `full`: Hết vé
- `cancelled`: Đã hủy

**Sử dụng trong Admin:**
- CRUD (Create, Read, Update, Delete) suất chiếu
- Lọc theo ngày, phòng
- Hiển thị trạng thái ghế trống

---

### 6. **bookings** - Đặt vé
```sql
CREATE TABLE bookings (
    bookingID INT PRIMARY KEY AUTO_INCREMENT,
    userID INT NOT NULL,
    showtimeID INT NOT NULL,
    bookingCode VARCHAR(50) UNIQUE NOT NULL,
    totalPrice DECIMAL(10,2) NOT NULL,
    totalSeats INT NOT NULL,
    paymentStatus ENUM('paid', 'pending', 'expired') DEFAULT 'pending',
    paymentMethod VARCHAR(50),
    paidAt DATETIME,
    bookingDate DATETIME NOT NULL,
    expiredAt DATETIME,
    notes TEXT,
    FOREIGN KEY (userID) REFERENCES user(userID),
    FOREIGN KEY (showtimeID) REFERENCES showtimes(showtimeID)
);
```

**Payment Status:**
- `paid`: Đã thanh toán
- `pending`: Đang chờ thanh toán
- `expired`: Hết hạn

**Sử dụng trong Admin:**
- Thống kê doanh thu
- Đếm số vé bán ra
- Hiển thị đơn đặt gần đây
- Biểu đồ doanh thu theo thời gian

---

### 7. **bookingseats** - Ghế đã đặt
```sql
CREATE TABLE bookingseats (
    bookingSeatID INT PRIMARY KEY AUTO_INCREMENT,
    bookingID INT NOT NULL,
    seatID INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bookingID) REFERENCES bookings(bookingID),
    FOREIGN KEY (seatID) REFERENCES seats(seatID)
);
```

**Sử dụng trong Admin:**
- Tính số ghế đã đặt
- Tính ghế còn trống cho mỗi suất chiếu

---

### 8. **seats** - Ghế ngồi
```sql
CREATE TABLE seats (
    seatID INT PRIMARY KEY AUTO_INCREMENT,
    roomID INT NOT NULL,
    seatRow CHAR(1) NOT NULL COMMENT 'A-Z',
    seatNumber INT NOT NULL,
    seatType ENUM('standard', 'vip', 'couple') DEFAULT 'standard',
    price DECIMAL(10,2) NOT NULL,
    status ENUM('active', 'broken', 'reserved') DEFAULT 'active',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (roomID) REFERENCES rooms(roomID)
);
```

**Sử dụng trong Admin:**
- Tính tổng số ghế trong phòng
- Giá ghế cho các loại khác nhau

---

### 9. **promotions** - Khuyến mãi
```sql
CREATE TABLE promotions (
    promotionID INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    discountType ENUM('percent', 'fixed') NOT NULL,
    discountValue DECIMAL(10,2) NOT NULL,
    minOrderValue DECIMAL(10,2) DEFAULT 0,
    maxDiscount DECIMAL(10,2),
    startDate DATE NOT NULL,
    endDate DATE NOT NULL,
    usageLimit INT DEFAULT 0 COMMENT '0 = unlimited',
    usedCount INT DEFAULT 0,
    status ENUM('active', 'inactive', 'expired') DEFAULT 'active',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Sử dụng trong Admin:**
- Tạo mã khuyến mãi mới
- Theo dõi số lần sử dụng
- Quản lý thời hạn khuyến mãi

---

### 10. **news** - Tin tức/Sự kiện
```sql
CREATE TABLE news (
    newsID INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    summary TEXT,
    imageURL VARCHAR(500),
    type ENUM('news', 'promotion', 'event', 'announcement') DEFAULT 'news',
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    promotionID INT,
    movieID INT,
    priority INT DEFAULT 0,
    publishDate DATETIME,
    expireDate DATETIME,
    viewCount INT DEFAULT 0,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (promotionID) REFERENCES promotions(promotionID),
    FOREIGN KEY (movieID) REFERENCES movie(movieID)
);
```

**Sử dụng trong Admin:**
- Đăng tin tức mới
- Quản lý sự kiện
- Gắn khuyến mãi với tin tức

---

## 🔍 Queries quan trọng cho Admin

### 1. Thống kê doanh thu tháng
```sql
SELECT COALESCE(SUM(totalPrice), 0) as totalRevenue 
FROM bookings 
WHERE paymentStatus = 'paid' 
AND MONTH(bookingDate) = MONTH(CURRENT_DATE())
AND YEAR(bookingDate) = YEAR(CURRENT_DATE());
```

### 2. Số vé bán trong tháng
```sql
SELECT COUNT(*) as totalBookings 
FROM bookings 
WHERE paymentStatus = 'paid'
AND MONTH(bookingDate) = MONTH(CURRENT_DATE())
AND YEAR(bookingDate) = YEAR(CURRENT_DATE());
```

### 3. Top phim bán chạy
```sql
SELECT m.movieID, m.title, 
       COUNT(b.bookingID) as totalBookings, 
       SUM(b.totalPrice) as totalRevenue
FROM movie m
JOIN showtimes s ON m.movieID = s.movieID
JOIN bookings b ON s.showtimeID = b.showtimeID
WHERE b.paymentStatus = 'paid'
AND MONTH(b.bookingDate) = MONTH(CURRENT_DATE())
AND YEAR(b.bookingDate) = YEAR(CURRENT_DATE())
GROUP BY m.movieID
ORDER BY totalBookings DESC
LIMIT 5;
```

### 4. Doanh thu theo ngày (cho biểu đồ)
```sql
SELECT DATE(bookingDate) as date, SUM(totalPrice) as revenue
FROM bookings
WHERE paymentStatus = 'paid'
AND bookingDate >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
GROUP BY DATE(bookingDate)
ORDER BY date ASC;
```

### 5. Suất chiếu với thông tin chi tiết
```sql
SELECT s.*, m.title as movieTitle, r.roomName, r.roomType, r.totalSeats,
       COUNT(bs.seatID) as bookedSeats,
       (r.totalSeats - COUNT(bs.seatID)) as availableSeats
FROM showtimes s
JOIN movie m ON s.movieID = m.movieID
JOIN rooms r ON s.roomID = r.roomID
LEFT JOIN bookingseats bs ON bs.bookingID IN (
    SELECT b.bookingID FROM bookings b 
    WHERE b.showtimeID = s.showtimeID 
    AND b.paymentStatus = 'paid'
)
WHERE DATE(s.showtimeDate) = '2024-12-25'
GROUP BY s.showtimeID
ORDER BY s.showtimeDate ASC;
```

### 6. Kiểm tra trùng lịch chiếu
```sql
SELECT COUNT(*) as count 
FROM showtimes 
WHERE roomID = 1 
AND DATE(showtimeDate) = '2024-12-25'
AND ABS(TIMESTAMPDIFF(MINUTE, showtimeDate, '2024-12-25 14:00:00')) < 180;
```

---

## 📈 Indexes để tối ưu hiệu suất

```sql
-- User table
CREATE INDEX idx_email ON user(email);
CREATE INDEX idx_username ON user(username);
CREATE INDEX idx_roleID ON user(roleID);

-- Movie table
CREATE INDEX idx_movieStatus ON movie(movieStatus);
CREATE INDEX idx_rating ON movie(rating);
CREATE INDEX idx_releaseDate ON movie(releaseDate);

-- Showtimes table
CREATE INDEX idx_movieID ON showtimes(movieID);
CREATE INDEX idx_roomID ON showtimes(roomID);
CREATE INDEX idx_showtimeDate ON showtimes(showtimeDate);
CREATE INDEX idx_status ON showtimes(status);

-- Bookings table
CREATE INDEX idx_userID ON bookings(userID);
CREATE INDEX idx_showtimeID ON bookings(showtimeID);
CREATE INDEX idx_paymentStatus ON bookings(paymentStatus);
CREATE INDEX idx_bookingDate ON bookings(bookingDate);
CREATE INDEX idx_bookingCode ON bookings(bookingCode);
```

---

## 🔗 Quan hệ giữa các bảng

```
roles (1) ----< (*) user
movie (1) ----< (*) showtimes
rooms (1) ----< (*) showtimes
rooms (1) ----< (*) seats

user (1) ----< (*) bookings
showtimes (1) ----< (*) bookings
bookings (1) ----< (*) bookingseats
seats (1) ----< (*) bookingseats

promotions (1) ----< (*) news
movie (1) ----< (*) news
```

---

## 💡 Tips cho Admin

### Backup Database
```bash
mysqldump -u root -p dacs2 > backup_$(date +%Y%m%d).sql
```

### Restore Database
```bash
mysql -u root -p dacs2 < backup_20241224.sql
```

### Kiểm tra kích thước database
```sql
SELECT 
    table_name AS 'Table',
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.TABLES
WHERE table_schema = 'dacs2'
ORDER BY (data_length + index_length) DESC;
```

### Xóa dữ liệu cũ (Cẩn thận!)
```sql
-- Xóa bookings cũ hơn 6 tháng và đã expired
DELETE FROM bookings 
WHERE paymentStatus = 'expired' 
AND bookingDate < DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH);

-- Xóa suất chiếu cũ
DELETE FROM showtimes 
WHERE showtimeDate < DATE_SUB(CURRENT_DATE(), INTERVAL 3 MONTH)
AND showtimeID NOT IN (SELECT showtimeID FROM bookings);
```

---

**Lưu ý**: Luôn backup database trước khi thực hiện các thao tác xóa dữ liệu!
