# 📊 DATABASE DOCUMENTATION - VKU CINEMA

## 📚 MỤC LỤC
1. [Tổng Quan Database](#1-tổng-quan-database)
2. [Sơ Đồ Quan Hệ (ERD)](#2-sơ-đồ-quan-hệ-erd)
3. [Chi Tiết Từng Bảng](#3-chi-tiết-từng-bảng)
4. [Relationships (Quan Hệ)](#4-relationships-quan-hệ)
5. [Indexes & Tối Ưu](#5-indexes--tối-ưu)
6. [Views & Procedures](#6-views--procedures)
7. [Business Logic](#7-business-logic)
8. [Sample Queries](#8-sample-queries)

---

## 1. TỔNG QUAN DATABASE

### 🎯 Thông Tin Cơ Bản
- **Tên Database:** `dacs2`
- **Charset:** `utf8mb4`
- **Collation:** `utf8mb4_unicode_ci`
- **Engine:** `InnoDB` (hỗ trợ transactions & foreign keys)
- **Tổng số bảng:** 13 bảng

### 📦 Phân Loại Bảng

#### **Core Tables (Bảng Chính)** - 10 bảng
```
1. roles              → Vai trò người dùng
2. user               → Thông tin người dùng
3. movie              → Danh sách phim
4. rooms              → Phòng chiếu
5. seats              → Ghế ngồi
6. showtimes          → Lịch chiếu phim
7. seatlocks          → Lock ghế tạm thời
8. bookings           → Đơn đặt vé
9. bookingseats       → Chi tiết ghế đã đặt
10. payments          → Thanh toán
```

#### **Optional Tables (Bảng Mở Rộng)** - 3 bảng
```
11. reviews           → Đánh giá phim
12. promotions        → Mã khuyến mãi
13. booking_promotions → Áp dụng khuyến mãi
```

---

## 2. SƠ ĐỒ QUAN HỆ (ERD)

```
┌─────────────────────────────────────────────────────────────────────────┐
│                        VKU CINEMA - ERD DIAGRAM                         │
└─────────────────────────────────────────────────────────────────────────┘

┌──────────┐
│  roles   │
│──────────│
│ roleID PK│◄───┐
│ roleName │    │
└──────────┘    │
                │ FK
┌───────────────┼──────────────────────────────────────┐
│  user         │                                      │
│───────────────│                                      │
│ userID PK     │                                      │
│ username      │                                      │
│ email         │                                      │
│ password      │                                      │
│ roleID FK     │─────┘                                │
└───────────────┘                                      │
       │ 1                                             │
       │                                               │
       │ N                                             │
       ▼                                               │
┌───────────────┐         ┌──────────────┐            │
│  seatlocks    │         │  bookings    │            │
│───────────────│         │──────────────│            │
│ lockID PK     │         │ bookingID PK │            │
│ userID FK     │─────────│ userID FK    │            │
│ showtimeID FK │◄───┐    │ showtimeID FK│◄───┐       │
│ seatID FK     │    │    └──────────────┘    │       │
└───────────────┘    │           │ 1          │       │
                     │           │            │       │
                     │           │ N          │       │
┌──────────────┐     │    ┌──────▼───────┐   │       │
│  showtimes   │     │    │ bookingseats │   │       │
│──────────────│     │    │──────────────│   │       │
│ showtimeID PK│─────┤    │ bookingSeatID│   │       │
│ movieID FK   │◄─┐  │    │ bookingID FK │   │       │
│ roomID FK    │◄─┼──┘    │ seatID FK    │◄──┼───────┤
│ showDate     │  │       └──────────────┘   │       │
│ showTime     │  │                           │       │
└──────────────┘  │       ┌──────────────┐   │       │
                  │       │  payments    │   │       │
┌──────────────┐  │       │──────────────│   │       │
│  movie       │  │       │ paymentID PK │   │       │
│──────────────│  │       │ bookingID FK │───┘       │
│ movieID PK   │──┘       └──────────────┘           │
│ title        │                                      │
│ genre        │          ┌──────────────┐            │
│ rating       │          │  reviews     │            │
│ movieStatus  │          │──────────────│            │
└──────────────┘          │ reviewID PK  │            │
                          │ movieID FK   │────┐       │
┌──────────────┐          │ userID FK    │────┼───────┘
│  rooms       │          └──────────────┘    │
│──────────────│                              │
│ roomID PK    │──┐                           │
│ roomName     │  │                           │
│ roomType     │  │       ┌───────────────┐   │
│ totalSeats   │  │       │  promotions   │   │
└──────────────┘  │       │───────────────│   │
                  │       │ promotionID PK│───┐
┌──────────────┐  │       │ code          │   │
│  seats       │  │       │ discountType  │   │
│──────────────│  │       │ discountValue │   │
│ seatID PK    │  │       └───────────────┘   │
│ roomID FK    │──┘               │            │
│ seatRow      │                  │            │
│ seatNumber   │                  │            │
│ seatType     │          ┌───────▼────────────┼─┐
│ price        │          │ booking_promotions │ │
└──────────────┘          │────────────────────│ │
                          │ id PK              │ │
                          │ bookingID FK       │─┘
                          │ promotionID FK     │─┘
                          │ discountAmount     │
                          └────────────────────┘
```

---

## 3. CHI TIẾT TỪNG BẢNG

### 📋 1. BẢNG `roles` - Vai Trò Người Dùng

**Mục đích:** Định nghĩa các vai trò trong hệ thống

| Column | Type | Constraints | Mô Tả |
|--------|------|-------------|-------|
| `roleID` | INT | PRIMARY KEY, AUTO_INCREMENT | ID vai trò |
| `roleName` | VARCHAR(50) | NOT NULL, UNIQUE | Tên vai trò |
| `description` | TEXT | | Mô tả vai trò |
| `createdAt` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Ngày tạo |

**Dữ liệu mặc định:**
```sql
1 → Admin      (Quản trị viên hệ thống)
2 → User       (Người dùng thường)
3 → Manager    (Quản lý rạp)
```

**Business Rules:**
- Admin có toàn quyền quản lý hệ thống
- User chỉ có thể đặt vé
- Manager quản lý phim, suất chiếu, thống kê

---

### 👤 2. BẢNG `user` - Người Dùng

**Mục đích:** Lưu thông tin tài khoản người dùng

| Column | Type | Constraints | Mô Tả |
|--------|------|-------------|-------|
| `userID` | INT | PRIMARY KEY, AUTO_INCREMENT | ID người dùng |
| `username` | VARCHAR(50) | NOT NULL, UNIQUE | Tên đăng nhập |
| `email` | VARCHAR(100) | NOT NULL, UNIQUE | Email |
| `password` | VARCHAR(255) | NOT NULL | Mật khẩu (bcrypt hash) |
| `phone` | VARCHAR(15) | | Số điện thoại |
| `dateOfBirth` | DATE | | Ngày sinh |
| `sex` | ENUM | 'male', 'female', 'other' | Giới tính |
| `cccd` | VARCHAR(20) | | Số CCCD/CMND |
| `roleID` | INT | FK → roles, DEFAULT 2 | Vai trò |
| `last_login` | DATETIME | | Lần đăng nhập cuối |
| `createdAt` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Ngày tạo |
| `updatedAt` | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Ngày cập nhật |

**Indexes:**
```sql
idx_email       → Tìm kiếm theo email
idx_username    → Tìm kiếm theo username
idx_roleID      → Filter theo vai trò
```

**Security:**
- Password được hash bằng bcrypt (`password_hash()` PHP)
- Email và username phải UNIQUE
- Không bao giờ trả về password trong API

**Sample Data:**
```sql
admin@vkucinema.com   → Admin   (password: 123456)
user1@gmail.com       → User    (password: 123456)
manager@vkucinema.com → Manager (password: 123456)
```

---

### 🎬 3. BẢNG `movie` - Phim

**Mục đích:** Quản lý thông tin phim

| Column | Type | Constraints | Mô Tả |
|--------|------|-------------|-------|
| `movieID` | INT | PRIMARY KEY, AUTO_INCREMENT | ID phim |
| `title` | VARCHAR(255) | NOT NULL | Tên phim |
| `genre` | VARCHAR(100) | | Thể loại (Action, Drama...) |
| `duration` | INT | | Thời lượng (phút) |
| `description` | TEXT | | Mô tả nội dung |
| `rating` | DECIMAL(3,1) | DEFAULT 0.0 | Đánh giá (0.0-10.0) |
| `movieStatus` | ENUM | 'now_showing', 'coming_soon', 'stopped' | Trạng thái |
| `posterURL` | VARCHAR(500) | | Link poster dọc |
| `posterHorizontalURL` | VARCHAR(500) | | Link poster ngang |
| `trailerURL` | VARCHAR(500) | | Link trailer YouTube |
| `author` | VARCHAR(255) | | Đạo diễn |
| `releaseDate` | DATE | | Ngày ra mắt |
| `ageRating` | VARCHAR(10) | DEFAULT 'P' | Độ tuổi (P, C13, C16, C18) |
| `language` | VARCHAR(50) | DEFAULT 'Vietnamese' | Ngôn ngữ |
| `country` | VARCHAR(50) | DEFAULT 'Vietnam' | Quốc gia |
| `createdAt` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Ngày tạo |
| `updatedAt` | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Ngày cập nhật |

**Indexes:**
```sql
idx_movieStatus    → Filter phim đang chiếu/sắp chiếu
idx_rating         → Sắp xếp theo rating
idx_releaseDate    → Sắp xếp theo ngày ra mắt
idx_title          → Tìm kiếm theo tên
```

**Movie Status:**
- `now_showing` - Đang chiếu (có lịch chiếu)
- `coming_soon` - Sắp chiếu (chưa có lịch)
- `stopped` - Ngừng chiếu

**Age Rating:**
- `P` - Phổ biến (mọi lứa tuổi)
- `C13` - Cấm trẻ em dưới 13 tuổi
- `C16` - Cấm trẻ em dưới 16 tuổi
- `C18` - Cấm trẻ em dưới 18 tuổi

---

### 🏢 4. BẢNG `rooms` - Phòng Chiếu

**Mục đích:** Quản lý phòng chiếu/rạp

| Column | Type | Constraints | Mô Tả |
|--------|------|-------------|-------|
| `roomID` | INT | PRIMARY KEY, AUTO_INCREMENT | ID phòng |
| `roomName` | VARCHAR(100) | NOT NULL | Tên phòng |
| `roomType` | ENUM | '2D', '3D', 'IMAX', '4DX' | Loại màn hình |
| `totalSeats` | INT | DEFAULT 0 | Tổng số ghế |
| `totalRows` | INT | DEFAULT 0 | Tổng số hàng |
| `seatsPerRow` | INT | DEFAULT 0 | Số ghế/hàng |
| `status` | ENUM | 'active', 'maintenance', 'closed' | Trạng thái |
| `description` | TEXT | | Mô tả |
| `createdAt` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Ngày tạo |
| `updatedAt` | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Ngày cập nhật |

**Sample Data:**
```sql
VKU Cinema 1  → 2D    → 60 ghế  (6 hàng x 10 ghế)
VKU Cinema 2  → 3D    → 80 ghế  (8 hàng x 10 ghế)
VKU Cinema 3  → IMAX  → 100 ghế (10 hàng x 10 ghế)
```

**Triggers:**
- `totalSeats` tự động cập nhật khi thêm/xóa ghế

---

### 💺 5. BẢNG `seats` - Ghế Ngồi

**Mục đích:** Quản lý ghế trong từng phòng

| Column | Type | Constraints | Mô Tả |
|--------|------|-------------|-------|
| `seatID` | INT | PRIMARY KEY, AUTO_INCREMENT | ID ghế |
| `roomID` | INT | FK → rooms, NOT NULL | ID phòng |
| `seatRow` | VARCHAR(2) | NOT NULL | Hàng ghế (A, B, C...) |
| `seatNumber` | INT | NOT NULL | Số ghế (1, 2, 3...) |
| `seatType` | ENUM | 'standard', 'vip', 'couple' | Loại ghế |
| `price` | DECIMAL(10,2) | DEFAULT 45000 | Giá ghế (VNĐ) |
| `status` | ENUM | 'active', 'broken', 'reserved' | Trạng thái |
| `createdAt` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Ngày tạo |

**Indexes:**
```sql
idx_roomID           → Filter theo phòng
idx_seatType         → Filter theo loại ghế
unique_seat          → UNIQUE (roomID, seatRow, seatNumber)
```

**Seat Types & Prices:**
```
Standard  → 45,000đ - 55,000đ  (Ghế thường)
VIP       → 75,000đ - 90,000đ  (Ghế VIP)
Couple    → 150,000đ           (Ghế đôi)
```

**Naming Convention:**
```
A1, A2, A3... A10
B1, B2, B3... B10
C1, C2, C3... C10
...
```

**Sample Layout (Phòng 1):**
```
            [SCREEN]
    1  2  3  4  5  6  7  8  9  10
A   □  □  □  □  □  □  □  □  □  □   Standard
B   □  □  □  □  □  □  □  □  □  □   Standard
C   □  □  □  □  □  □  □  □  □  □   Standard
D   □  □  □  □  □  □  □  □  □  □   Standard
E   ■  ■  ■  ■  ■  ■  ■  ■  ■  ■   VIP
F   ■  ■  ■  ■  ■  ■  ■  ■  ■  ■   VIP
```

---

### 📅 6. BẢNG `showtimes` - Lịch Chiếu

**Mục đích:** Quản lý lịch chiếu phim

| Column | Type | Constraints | Mô Tả |
|--------|------|-------------|-------|
| `showtimeID` | INT | PRIMARY KEY, AUTO_INCREMENT | ID suất chiếu |
| `movieID` | INT | FK → movie, NOT NULL | ID phim |
| `roomID` | INT | FK → rooms, NOT NULL | ID phòng |
| `showDate` | DATE | NOT NULL | Ngày chiếu |
| `showTime` | TIME | NOT NULL | Giờ chiếu |
| `basePrice` | DECIMAL(10,2) | DEFAULT 45000 | Giá vé cơ bản |
| `status` | ENUM | 'available', 'full', 'cancelled' | Trạng thái |
| `createdAt` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Ngày tạo |
| `updatedAt` | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Ngày cập nhật |

**Indexes:**
```sql
idx_movieID              → Tìm suất chiếu theo phim
idx_roomID_showtime      → Tìm suất chiếu theo phòng
idx_showDate             → Filter theo ngày
idx_showDateTime         → Composite (date + time)
idx_status_showtime      → Filter theo status
idx_showtime_movie_date  → Composite tối ưu
```

**Status:**
- `available` - Còn chỗ
- `full` - Hết chỗ
- `cancelled` - Hủy suất

**Sample Showtimes:**
```sql
Avengers: Endgame → 2024-11-10 → 09:00, 12:00, 15:00, 18:00, 21:00
The Batman        → 2024-11-10 → 09:00, 12:00, 15:00, 18:00, 21:00
Spider-Man        → 2024-11-10 → 09:00, 12:00, 15:00, 18:00, 21:00
```

---

### 🔒 7. BẢNG `seatlocks` - Khóa Ghế Tạm Thời

**Mục đích:** Lock ghế trong 10-15 phút khi user đang chọn

| Column | Type | Constraints | Mô Tả |
|--------|------|-------------|-------|
| `lockID` | INT | PRIMARY KEY, AUTO_INCREMENT | ID lock |
| `showtimeID` | INT | FK → showtimes, NOT NULL | ID suất chiếu |
| `seatID` | INT | FK → seats, NOT NULL | ID ghế |
| `userID` | INT | FK → user, NOT NULL | ID user đang giữ |
| `lockedAt` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Thời gian lock |
| `expiresAt` | DATETIME | NOT NULL | Thời gian hết hạn |

**Indexes:**
```sql
idx_expiresAt                    → Cleanup expired locks
idx_userID_lock                  → Tìm locks của user
unique_seat_showtime_lock        → UNIQUE (showtimeID, seatID)
```

**Business Logic:**
```
1. User chọn ghế → Lock 15 phút
2. Countdown timer hiển thị
3. Hết giờ → Auto unlock (trigger)
4. User thanh toán → Chuyển sang booking → Xóa lock
```

**Lifecycle:**
```
[Available] → [Locked 15min] → [Expired] → [Available]
                    ↓
              [Confirmed] → [Booked]
```

---

### 🎟️ 8. BẢNG `bookings` - Đơn Đặt Vé

**Mục đích:** Quản lý đơn đặt vé

| Column | Type | Constraints | Mô Tả |
|--------|------|-------------|-------|
| `bookingID` | INT | PRIMARY KEY, AUTO_INCREMENT | ID đơn |
| `userID` | INT | FK → user, NOT NULL | ID người đặt |
| `showtimeID` | INT | FK → showtimes, NOT NULL | ID suất chiếu |
| `bookingCode` | VARCHAR(50) | NOT NULL, UNIQUE | Mã đặt vé |
| `totalPrice` | DECIMAL(10,2) | NOT NULL | Tổng tiền |
| `totalSeats` | INT | NOT NULL | Số ghế đã đặt |
| `paymentStatus` | ENUM | 'pending', 'paid', 'cancelled', 'expired' | Trạng thái thanh toán |
| `paymentMethod` | VARCHAR(50) | | Phương thức thanh toán |
| `paidAt` | DATETIME | | Thời gian thanh toán |
| `bookingDate` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Thời gian đặt |
| `expiredAt` | DATETIME | | Hết hạn (15 phút) |
| `notes` | TEXT | | Ghi chú |

**Indexes:**
```sql
idx_userID_booking        → Tìm booking của user
idx_showtimeID_booking    → Tìm booking của suất chiếu
idx_bookingCode           → Tìm theo mã
idx_paymentStatus         → Filter theo status
idx_bookingDate           → Sắp xếp theo ngày
idx_booking_user_status   → Composite tối ưu
```

**Booking Code Format:**
```
VKU + 9 số random
Ví dụ: VKU123456789
```

**Payment Status:**
- `pending` - Chờ thanh toán (15 phút)
- `paid` - Đã thanh toán
- `cancelled` - Đã hủy
- `expired` - Hết hạn (quá 15 phút chưa thanh toán)

**Payment Methods:**
- `qr` - QR Code (VietQR)
- `momo` - Ví Momo
- `vnpay` - VNPay
- `banking` - Chuyển khoản ngân hàng
- `cash` - Tiền mặt tại quầy

---

### 🪑 9. BẢNG `bookingseats` - Chi Tiết Ghế Đã Đặt

**Mục đích:** Lưu thông tin ghế của từng booking

| Column | Type | Constraints | Mô Tả |
|--------|------|-------------|-------|
| `bookingSeatID` | INT | PRIMARY KEY, AUTO_INCREMENT | ID |
| `bookingID` | INT | FK → bookings, NOT NULL | ID booking |
| `seatID` | INT | FK → seats, NOT NULL | ID ghế |
| `price` | DECIMAL(10,2) | NOT NULL | Giá tại thời điểm đặt |
| `createdAt` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Ngày tạo |

**Indexes:**
```sql
idx_bookingID         → Lấy ghế của booking
idx_seatID_booking    → Kiểm tra ghế đã đặt
unique_booking_seat   → UNIQUE (bookingID, seatID)
```

**Why Store Price?**
- Giá ghế có thể thay đổi theo thời gian
- Lưu giá tại thời điểm đặt để tính toán chính xác
- Audit trail cho doanh thu

---

### 💳 10. BẢNG `payments` - Thanh Toán

**Mục đích:** Quản lý các giao dịch thanh toán

| Column | Type | Constraints | Mô Tả |
|--------|------|-------------|-------|
| `paymentID` | INT | PRIMARY KEY, AUTO_INCREMENT | ID thanh toán |
| `bookingID` | INT | FK → bookings, NOT NULL | ID booking |
| `amount` | DECIMAL(10,2) | NOT NULL | Số tiền |
| `paymentMethod` | ENUM | 'qr', 'momo', 'vnpay', 'banking', 'cash' | Phương thức |
| `paymentStatus` | ENUM | 'pending', 'completed', 'failed', 'refunded' | Trạng thái |
| `transactionID` | VARCHAR(255) | | Mã GD từ cổng thanh toán |
| `paymentDate` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Ngày tạo |
| `completedAt` | DATETIME | | Thời gian hoàn thành |
| `description` | TEXT | | Mô tả |

**Indexes:**
```sql
idx_bookingID_payment      → Tìm payment của booking
idx_transactionID          → Tìm theo mã GD
idx_paymentStatus_payment  → Filter theo status
```

**Payment Flow:**
```
1. User chọn payment method
2. Tạo payment record (status = pending)
3. Redirect đến cổng thanh toán (VNPay/Momo)
4. User thanh toán
5. Callback → Update status = completed
6. Update booking.paymentStatus = paid
```

---

### ⭐ 11. BẢNG `reviews` - Đánh Giá Phim (Optional)

**Mục đích:** User đánh giá phim sau khi xem

| Column | Type | Constraints | Mô Tả |
|--------|------|-------------|-------|
| `reviewID` | INT | PRIMARY KEY, AUTO_INCREMENT | ID đánh giá |
| `movieID` | INT | FK → movie, NOT NULL | ID phim |
| `userID` | INT | FK → user, NOT NULL | ID user |
| `rating` | DECIMAL(2,1) | CHECK (0-10) | Điểm đánh giá |
| `comment` | TEXT | | Nội dung |
| `createdAt` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Ngày tạo |
| `updatedAt` | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Ngày sửa |

**Constraints:**
```sql
unique_user_movie_review  → User chỉ review 1 lần/phim
```

**Business Rules:**
- Rating: 0.0 - 10.0 (1 chữ số thập phân)
- User chỉ review sau khi đã xem phim
- Có thể edit review sau khi đăng

---

### 🎁 12. BẢNG `promotions` - Khuyến Mãi (Optional)

**Mục đích:** Quản lý mã giảm giá

| Column | Type | Constraints | Mô Tả |
|--------|------|-------------|-------|
| `promotionID` | INT | PRIMARY KEY, AUTO_INCREMENT | ID KM |
| `code` | VARCHAR(50) | NOT NULL, UNIQUE | Mã KM |
| `description` | TEXT | | Mô tả |
| `discountType` | ENUM | 'percent', 'fixed' | Loại giảm |
| `discountValue` | DECIMAL(10,2) | NOT NULL | Giá trị giảm |
| `minOrderValue` | DECIMAL(10,2) | DEFAULT 0 | Đơn tối thiểu |
| `maxDiscount` | DECIMAL(10,2) | | Giảm tối đa |
| `startDate` | DATE | NOT NULL | Ngày bắt đầu |
| `endDate` | DATE | NOT NULL | Ngày kết thúc |
| `usageLimit` | INT | DEFAULT 0 | Số lần dùng tối đa |
| `usedCount` | INT | DEFAULT 0 | Đã dùng |
| `status` | ENUM | 'active', 'inactive', 'expired' | Trạng thái |
| `createdAt` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Ngày tạo |

**Discount Types:**
```
percent → Giảm theo %
  Example: SALE20 → Giảm 20%
  
fixed   → Giảm số tiền cố định
  Example: GIAM50K → Giảm 50,000đ
```

**Sample Promotions:**
```sql
NEWYEAR2025  → 20% (max 100K) → Min 200K
STUDENT      → 50K fixed      → Min 100K
WEEKEND30    → 30% (max 150K) → Min 300K
```

---

### 🏷️ 13. BẢNG `booking_promotions` - Áp Dụng KM (Optional)

**Mục đích:** Lưu KM đã áp dụng cho booking

| Column | Type | Constraints | Mô Tả |
|--------|------|-------------|-------|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | ID |
| `bookingID` | INT | FK → bookings, NOT NULL | ID booking |
| `promotionID` | INT | FK → promotions, NOT NULL | ID KM |
| `discountAmount` | DECIMAL(10,2) | NOT NULL | Số tiền giảm thực tế |
| `appliedAt` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Thời gian áp dụng |

**Business Logic:**
```
1. User nhập mã KM
2. Validate: còn hạn? đủ điều kiện? còn lượt?
3. Tính discount amount
4. Update totalPrice trong booking
5. Lưu vào bảng này
6. Tăng usedCount trong promotions
```

---

## 4. RELATIONSHIPS (QUAN HỆ)

### 🔗 Foreign Key Relationships

```sql
┌─────────────────┬──────────────────┬─────────────────────┐
│ Child Table     │ Foreign Key      │ Parent Table        │
├─────────────────┼──────────────────┼─────────────────────┤
│ user            │ roleID           │ roles(roleID)       │
│ seats           │ roomID           │ rooms(roomID)       │
│ showtimes       │ movieID          │ movie(movieID)      │
│ showtimes       │ roomID           │ rooms(roomID)       │
│ seatlocks       │ showtimeID       │ showtimes(...)      │
│ seatlocks       │ seatID           │ seats(seatID)       │
│ seatlocks       │ userID           │ user(userID)        │
│ bookings        │ userID           │ user(userID)        │
│ bookings        │ showtimeID       │ showtimes(...)      │
│ bookingseats    │ bookingID        │ bookings(...)       │
│ bookingseats    │ seatID           │ seats(seatID)       │
│ payments        │ bookingID        │ bookings(...)       │
│ reviews         │ movieID          │ movie(movieID)      │
│ reviews         │ userID           │ user(userID)        │
│ booking_promo   │ bookingID        │ bookings(...)       │
│ booking_promo   │ promotionID      │ promotions(...)     │
└─────────────────┴──────────────────┴─────────────────────┘
```

### 📊 Cardinality (Lực lượng)

```
roles (1) ──────< (N) user
user (1) ───────< (N) bookings
user (1) ───────< (N) seatlocks
user (1) ───────< (N) reviews

movie (1) ──────< (N) showtimes
movie (1) ──────< (N) reviews

rooms (1) ──────< (N) seats
rooms (1) ──────< (N) showtimes

showtimes (1) ──< (N) bookings
showtimes (1) ──< (N) seatlocks

bookings (1) ───< (N) bookingseats
bookings (1) ───< (N) payments
bookings (1) ───< (N) booking_promotions

seats (1) ──────< (N) bookingseats
seats (1) ──────< (N) seatlocks

promotions (1) ─< (N) booking_promotions
```

### 🔄 Cascade Behaviors

```sql
ON DELETE CASCADE:
  - rooms → seats            (Xóa phòng → Xóa tất cả ghế)
  - movie → showtimes        (Xóa phim → Xóa suất chiếu)
  - showtimes → bookings     (Xóa suất → Xóa booking)
  - bookings → bookingseats  (Xóa booking → Xóa chi tiết ghế)
  - user → bookings          (Xóa user → Xóa booking)
  
ON DELETE SET NULL:
  - roles → user.roleID      (Xóa role → Set NULL cho user)
```

---

## 5. INDEXES & TỐI ƯU

### 🚀 Index Strategy

#### **Single Column Indexes**
```sql
-- Tìm kiếm và filter thường xuyên
idx_email          ON user(email)
idx_username       ON user(username)
idx_movieStatus    ON movie(movieStatus)
idx_showDate       ON showtimes(showDate)
idx_bookingCode    ON bookings(bookingCode)
idx_paymentStatus  ON bookings(paymentStatus)
```

#### **Composite Indexes** (Tối ưu queries phức tạp)
```sql
idx_showtime_movie_date   ON showtimes(movieID, showDate, status)
  → Query: Lấy suất chiếu theo phim và ngày
  
idx_booking_user_status   ON bookings(userID, paymentStatus)
  → Query: Lấy booking của user theo status
  
idx_seat_room_status      ON seats(roomID, status)
  → Query: Lấy ghế available của phòng
  
idx_showDateTime          ON showtimes(showDate, showTime)
  → Query: Sắp xếp theo ngày giờ chiếu
```

#### **Unique Indexes** (Đảm bảo tính duy nhất)
```sql
unique_seat                    ON seats(roomID, seatRow, seatNumber)
unique_seat_showtime_lock      ON seatlocks(showtimeID, seatID)
unique_booking_seat            ON bookingseats(bookingID, seatID)
unique_user_movie_review       ON reviews(userID, movieID)
```

### 📈 Query Performance

**Before Indexing:**
```sql
SELECT * FROM showtimes WHERE movieID = 1 AND showDate = '2024-11-10';
→ Full table scan (slow)
```

**After Indexing:**
```sql
-- Sử dụng idx_showtime_movie_date
→ Index seek (fast)
```

---

## 6. VIEWS & PROCEDURES

### 👁️ Views (Virtual Tables)

#### **v_movie_statistics** - Thống Kê Phim
```sql
CREATE VIEW v_movie_statistics AS
SELECT 
    m.movieID,
    m.title,
    m.rating,
    m.movieStatus,
    COUNT(DISTINCT st.showtimeID) as total_showtimes,
    COUNT(DISTINCT b.bookingID) as total_bookings,
    COALESCE(SUM(b.totalSeats), 0) as total_tickets_sold,
    COALESCE(SUM(b.totalPrice), 0) as total_revenue
FROM movie m
LEFT JOIN showtimes st ON m.movieID = st.movieID
LEFT JOIN bookings b ON st.showtimeID = b.showtimeID 
    AND b.paymentStatus = 'paid'
GROUP BY m.movieID;
```

**Sử dụng:**
```sql
-- Xem phim bán chạy nhất
SELECT * FROM v_movie_statistics 
ORDER BY total_revenue DESC 
LIMIT 10;
```

#### **v_booking_details** - Chi Tiết Booking
```sql
CREATE VIEW v_booking_details AS
SELECT 
    b.bookingID,
    b.bookingCode,
    u.username,
    u.email,
    m.title as movieTitle,
    st.showDate,
    st.showTime,
    r.roomName,
    b.totalSeats,
    b.totalPrice,
    b.paymentStatus,
    GROUP_CONCAT(CONCAT(s.seatRow, s.seatNumber) 
        ORDER BY s.seatRow, s.seatNumber 
        SEPARATOR ', ') as seats
FROM bookings b
INNER JOIN user u ON b.userID = u.userID
INNER JOIN showtimes st ON b.showtimeID = st.showtimeID
INNER JOIN movie m ON st.movieID = m.movieID
INNER JOIN rooms r ON st.roomID = r.roomID
LEFT JOIN bookingseats bs ON b.bookingID = bs.bookingID
LEFT JOIN seats s ON bs.seatID = s.seatID
GROUP BY b.bookingID;
```

**Sử dụng:**
```sql
-- Xem chi tiết booking
SELECT * FROM v_booking_details 
WHERE bookingCode = 'VKU123456789';
```

### ⚙️ Triggers (Tự Động Hóa)

#### **cleanup_expired_locks** - Cleanup Lock Hết Hạn
```sql
CREATE TRIGGER cleanup_expired_locks
BEFORE INSERT ON bookings
FOR EACH ROW
BEGIN
    DELETE FROM seatlocks WHERE expiresAt < NOW();
END;
```

**Khi nào chạy:** Trước mỗi lần tạo booking mới

#### **update_room_seat_count_insert** - Cập Nhật Số Ghế
```sql
CREATE TRIGGER update_room_seat_count_insert
AFTER INSERT ON seats
FOR EACH ROW
BEGIN
    UPDATE rooms 
    SET totalSeats = (SELECT COUNT(*) FROM seats WHERE roomID = NEW.roomID)
    WHERE roomID = NEW.roomID;
END;
```

**Khi nào chạy:** Sau khi thêm ghế mới

#### **update_room_seat_count_delete** - Cập Nhật Khi Xóa
```sql
CREATE TRIGGER update_room_seat_count_delete
AFTER DELETE ON seats
FOR EACH ROW
BEGIN
    UPDATE rooms 
    SET totalSeats = (SELECT COUNT(*) FROM seats WHERE roomID = OLD.roomID)
    WHERE roomID = OLD.roomID;
END;
```

**Khi nào chạy:** Sau khi xóa ghế

### 🔧 Stored Procedures

#### **get_revenue_by_date** - Thống Kê Doanh Thu
```sql
CREATE PROCEDURE get_revenue_by_date(IN target_date DATE)
BEGIN
    SELECT 
        DATE(b.paidAt) as date,
        COUNT(DISTINCT b.bookingID) as total_bookings,
        SUM(b.totalPrice) as total_revenue,
        SUM(b.totalSeats) as total_tickets
    FROM bookings b
    WHERE DATE(b.paidAt) = target_date 
    AND b.paymentStatus = 'paid'
    GROUP BY DATE(b.paidAt);
END;
```

**Sử dụng:**
```sql
CALL get_revenue_by_date('2024-11-10');
```

---

## 7. BUSINESS LOGIC

### 🎯 Luồng Đặt Vé (Booking Flow)

```
Step 1: CHỌN SUẤT CHIẾU
├─ User chọn phim (movie)
├─ Chọn ngày (showDate)
├─ Chọn giờ (showTime)
└─ Redirect: /booking_step2_seats.php?showtimeID=123

Step 2: CHỌN GHẾ
├─ Load seats từ bảng seats
├─ Check status:
│  ├─ Booked (trong bookingseats)
│  ├─ Locked (trong seatlocks & chưa hết hạn)
│  └─ Available (còn lại)
├─ User click chọn ghế
├─ AJAX: Lock ghế (INSERT INTO seatlocks)
│  ├─ expiresAt = NOW() + 15 minutes
│  └─ Start countdown timer
└─ Redirect: /booking_step3_payment.php

Step 3: THANH TOÁN
├─ Hiển thị thông tin:
│  ├─ Phim, suất chiếu
│  ├─ Danh sách ghế đã chọn
│  └─ Tổng tiền
├─ User chọn payment method
├─ Create booking:
│  ├─ INSERT INTO bookings (status=pending)
│  ├─ INSERT INTO bookingseats
│  └─ DELETE FROM seatlocks (của user này)
├─ Nếu online payment:
│  └─ Redirect đến VNPay/Momo
└─ Nếu cash: Status = pending

Step 4: XÁC NHẬN
├─ Payment callback
├─ UPDATE bookings SET paymentStatus='paid'
├─ UPDATE payments SET paymentStatus='completed'
├─ Generate QR code
├─ Send email confirmation
└─ Display booking details
```

### 🔒 Seat Locking Mechanism

```
┌─────────────────────────────────────────────────────┐
│           SEAT LOCKING STATE MACHINE                │
└─────────────────────────────────────────────────────┘

[AVAILABLE]
    │
    │ User clicks seat
    ▼
[LOCKED]
    │ INSERT INTO seatlocks
    │ expiresAt = NOW() + 15min
    │
    ├─── 15 minutes countdown ────┐
    │                              │
    │ User completes payment       │ Timer expires
    ▼                              ▼
[BOOKED]                      [AVAILABLE]
    │ INSERT INTO bookings         │ DELETE FROM seatlocks
    │ DELETE FROM seatlocks        │ (trigger cleanup)
    ▼                              │
[CONFIRMED]                        └─► Back to start
```

### 💰 Price Calculation

```sql
-- Tính tổng tiền booking
SELECT 
    SUM(s.price) as totalPrice
FROM seats s
WHERE s.seatID IN (user_selected_seats);

-- Áp dụng khuyến mãi (nếu có)
IF promotion.discountType = 'percent' THEN
    discount = totalPrice * (promotion.discountValue / 100)
    discount = MIN(discount, promotion.maxDiscount)
ELSE
    discount = promotion.discountValue
END IF

finalPrice = totalPrice - discount
```

### 🎫 Booking Code Generation

```php
// PHP code
$bookingCode = 'VKU' . str_pad(mt_rand(0, 999999999), 9, '0', STR_PAD_LEFT);
// Result: VKU012345678
```

### ⏰ Expiration Handling

```sql
-- Auto expire bookings sau 15 phút
UPDATE bookings 
SET paymentStatus = 'expired'
WHERE paymentStatus = 'pending' 
AND expiredAt < NOW();

-- Unlock seats của booking expired
DELETE FROM seatlocks 
WHERE expiresAt < NOW();
```

---

## 8. SAMPLE QUERIES

### 📊 Queries Thường Dùng

#### 1. **Lấy phim đang chiếu HOT nhất**
```sql
SELECT * FROM movie 
WHERE movieStatus = 'now_showing' 
ORDER BY rating DESC 
LIMIT 6;
```

#### 2. **Lấy suất chiếu theo phim và ngày**
```sql
SELECT 
    st.showtimeID,
    st.showTime,
    r.roomName,
    r.roomType,
    COUNT(bs.seatID) as booked_seats,
    r.totalSeats - COUNT(bs.seatID) as available_seats
FROM showtimes st
INNER JOIN rooms r ON st.roomID = r.roomID
LEFT JOIN bookings b ON st.showtimeID = b.showtimeID 
    AND b.paymentStatus IN ('pending', 'paid')
LEFT JOIN bookingseats bs ON b.bookingID = bs.bookingID
WHERE st.movieID = 1 
AND st.showDate = '2024-11-10'
GROUP BY st.showtimeID
ORDER BY st.showTime ASC;
```

#### 3. **Lấy trạng thái ghế theo suất chiếu**
```sql
SELECT 
    s.seatID,
    s.seatRow,
    s.seatNumber,
    s.seatType,
    s.price,
    CASE 
        WHEN bs.bookingSeatID IS NOT NULL THEN 'booked'
        WHEN sl.lockID IS NOT NULL AND sl.expiresAt > NOW() THEN 'locked'
        ELSE 'available'
    END as status,
    sl.userID as lockedByUserID
FROM seats s
INNER JOIN showtimes st ON s.roomID = st.roomID
LEFT JOIN bookingseats bs ON s.seatID = bs.seatID 
    AND bs.bookingID IN (
        SELECT bookingID FROM bookings 
        WHERE showtimeID = 123 
        AND paymentStatus IN ('pending', 'paid')
    )
LEFT JOIN seatlocks sl ON s.seatID = sl.seatID 
    AND sl.showtimeID = 123 
    AND sl.expiresAt > NOW()
WHERE st.showtimeID = 123
ORDER BY s.seatRow, s.seatNumber;
```

#### 4. **Lấy booking của user**
```sql
SELECT 
    b.*,
    m.title as movieTitle,
    m.posterURL,
    st.showDate,
    st.showTime,
    r.roomName,
    GROUP_CONCAT(
        CONCAT(s.seatRow, s.seatNumber) 
        ORDER BY s.seatRow, s.seatNumber 
        SEPARATOR ', '
    ) as seats
FROM bookings b
INNER JOIN showtimes st ON b.showtimeID = st.showtimeID
INNER JOIN movie m ON st.movieID = m.movieID
INNER JOIN rooms r ON st.roomID = r.roomID
LEFT JOIN bookingseats bs ON b.bookingID = bs.bookingID
LEFT JOIN seats s ON bs.seatID = s.seatID
WHERE b.userID = 2
GROUP BY b.bookingID
ORDER BY b.bookingDate DESC;
```

#### 5. **Thống kê doanh thu theo phim**
```sql
SELECT 
    m.movieID,
    m.title,
    COUNT(DISTINCT b.bookingID) as total_bookings,
    SUM(b.totalSeats) as total_tickets,
    SUM(b.totalPrice) as total_revenue,
    AVG(b.totalPrice) as avg_booking_value
FROM movie m
INNER JOIN showtimes st ON m.movieID = st.movieID
INNER JOIN bookings b ON st.showtimeID = b.showtimeID
WHERE b.paymentStatus = 'paid'
AND DATE(b.paidAt) BETWEEN '2024-11-01' AND '2024-11-30'
GROUP BY m.movieID
ORDER BY total_revenue DESC;
```

#### 6. **Tìm ghế còn trống cho suất chiếu**
```sql
SELECT 
    s.seatID,
    CONCAT(s.seatRow, s.seatNumber) as seatName,
    s.price
FROM seats s
INNER JOIN showtimes st ON s.roomID = st.roomID
WHERE st.showtimeID = 123
AND s.seatID NOT IN (
    -- Ghế đã booking
    SELECT bs.seatID 
    FROM bookingseats bs
    INNER JOIN bookings b ON bs.bookingID = b.bookingID
    WHERE b.showtimeID = 123 
    AND b.paymentStatus IN ('pending', 'paid')
)
AND s.seatID NOT IN (
    -- Ghế đang lock
    SELECT sl.seatID
    FROM seatlocks sl
    WHERE sl.showtimeID = 123
    AND sl.expiresAt > NOW()
)
AND s.status = 'active'
ORDER BY s.seatRow, s.seatNumber;
```

#### 7. **Top 10 user chi tiêu nhiều nhất**
```sql
SELECT 
    u.userID,
    u.username,
    u.email,
    COUNT(b.bookingID) as total_bookings,
    SUM(b.totalPrice) as total_spent
FROM user u
INNER JOIN bookings b ON u.userID = b.userID
WHERE b.paymentStatus = 'paid'
GROUP BY u.userID
ORDER BY total_spent DESC
LIMIT 10;
```

#### 8. **Phòng chiếu nào bán vé nhiều nhất?**
```sql
SELECT 
    r.roomID,
    r.roomName,
    r.roomType,
    COUNT(DISTINCT b.bookingID) as total_bookings,
    SUM(b.totalSeats) as total_tickets_sold,
    SUM(b.totalPrice) as total_revenue,
    ROUND(SUM(b.totalSeats) * 100.0 / (r.totalSeats * COUNT(DISTINCT st.showtimeID)), 2) as occupancy_rate
FROM rooms r
INNER JOIN showtimes st ON r.roomID = st.roomID
LEFT JOIN bookings b ON st.showtimeID = b.showtimeID 
    AND b.paymentStatus = 'paid'
GROUP BY r.roomID
ORDER BY total_revenue DESC;
```

#### 9. **Giờ chiếu nào đông khách nhất?**
```sql
SELECT 
    HOUR(st.showTime) as hour,
    COUNT(DISTINCT b.bookingID) as total_bookings,
    SUM(b.totalSeats) as total_tickets
FROM showtimes st
INNER JOIN bookings b ON st.showtimeID = b.showtimeID
WHERE b.paymentStatus = 'paid'
GROUP BY HOUR(st.showTime)
ORDER BY total_tickets DESC;
```

#### 10. **Cleanup expired locks & bookings (Maintenance)**
```sql
-- Xóa lock hết hạn
DELETE FROM seatlocks 
WHERE expiresAt < NOW();

-- Expire bookings chưa thanh toán
UPDATE bookings 
SET paymentStatus = 'expired'
WHERE paymentStatus = 'pending' 
AND expiredAt < NOW();
```

---

## 🎯 KẾT LUẬN

### ✅ Điểm Mạnh Của Schema

1. **Chuẩn hóa tốt (3NF)**
   - Không duplicate data
   - Dễ maintain và update

2. **Foreign Keys đầy đủ**
   - Đảm bảo referential integrity
   - Cascade deletes hợp lý

3. **Indexes tối ưu**
   - Single column indexes cho tìm kiếm
   - Composite indexes cho queries phức tạp
   - Unique constraints đảm bảo data quality

4. **Business Logic Clear**
   - Seat locking mechanism rõ ràng
   - Booking flow logic đầy đủ
   - Payment flow hoàn chỉnh

5. **Scalability**
   - Views cho complex queries
   - Triggers tự động hóa
   - Stored procedures tái sử dụng

### 🚀 Khả Năng Mở Rộng

1. **Thêm tính năng mới:**
   - Food & Beverage combos
   - Member tiers (Silver, Gold, Platinum)
   - Gift cards
   - Event bookings

2. **Tối ưu performance:**
   - Partitioning tables (theo tháng/năm)
   - Read replicas
   - Caching layer (Redis)

3. **Analytics:**
   - Data warehouse cho BI
   - Machine learning recommendations
   - Customer segmentation

---

## 📞 LIÊN HỆ & HỖ TRỢ

Nếu có thắc mắc về database schema, vui lòng:
- Đọc lại documentation này
- Check file `DATABASE_IMPORT_GUIDE.md`
- Check file `LUONG_HOAT_DONG.md`

**🎬 VKU Cinema Database - Documented with ❤️**
