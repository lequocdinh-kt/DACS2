# HƯỚNG DẪN SỬ DỤNG TRANG ADMIN

## 📋 Tổng quan

Hệ thống Admin Dashboard cho phép quản trị viên (roleID = 1) quản lý toàn bộ hệ thống VKU Cinema với các tính năng:

- ✅ Thống kê doanh thu, vé bán, người dùng, phim
- 📊 Biểu đồ trực quan doanh thu theo thời gian
- 🎬 Quản lý suất chiếu (Thêm/Sửa/Xóa)
- 🏆 Xem top phim bán chạy
- 📜 Theo dõi đặt vé gần đây

## 🚀 Cài đặt

### 1. Cập nhật Database

Chạy file SQL để cập nhật suất chiếu mới:

```sql
SOURCE database_showtimes_update.sql;
```

Hoặc import qua phpMyAdmin:
1. Mở phpMyAdmin
2. Chọn database `dacs2`
3. Vào tab **Import**
4. Chọn file `database_showtimes_update.sql`
5. Click **Go**

### 2. Tạo tài khoản Admin

Nếu chưa có tài khoản admin, chạy SQL sau:

```sql
-- Cập nhật user hiện tại thành admin
UPDATE `user` SET roleID = 1 WHERE userID = 1;

-- Hoặc tạo user admin mới
INSERT INTO `user` (username, email, password, roleID) 
VALUES ('admin', 'admin@vkucinema.com', '$2y$10$YourHashedPasswordHere', 1);
```

## 📖 Sử dụng

### Truy cập trang Admin

1. Đăng nhập với tài khoản có `roleID = 1`
2. Click vào menu **ADMIN** (có icon vương miện 👑) trên header
3. Hoặc truy cập: `http://localhost/index.php?page=admin`

### Các chức năng chính

#### 1. Dashboard Thống kê
- **Doanh thu tháng**: Tổng doanh thu trong tháng hiện tại
- **Vé đã bán**: Số lượng vé đã bán trong tháng
- **Người dùng**: Tổng số người dùng đã đăng ký
- **Phim đang chiếu**: Số lượng phim hiện đang chiếu

#### 2. Biểu đồ Doanh thu
- Xem doanh thu theo 7/30/90 ngày gần đây
- Biểu đồ đường (Line Chart) hiển thị xu hướng
- Hover vào điểm để xem chi tiết

#### 3. Quản lý Suất chiếu

**Thêm suất chiếu mới:**
1. Click nút **"+ Thêm suất chiếu"**
2. Chọn phim, phòng chiếu
3. Nhập ngày, giờ chiếu
4. Nhập giá vé cơ bản
5. Click **Lưu**

**Sửa suất chiếu:**
1. Click nút **"Sửa"** ở suất chiếu muốn sửa
2. Chỉnh sửa thông tin
3. Click **Lưu**

**Xóa suất chiếu:**
1. Click nút **"Xóa"** ở suất chiếu muốn xóa
2. Xác nhận xóa
3. ⚠️ Lưu ý: Không thể xóa suất chiếu đã có người đặt vé

**Lọc suất chiếu:**
- Chọn ngày chiếu
- Chọn phòng (hoặc "Tất cả phòng")
- Click **"Tìm"**

#### 4. Top Phim bán chạy
- Hiển thị 5 phim có số vé bán nhiều nhất trong tháng
- Xếp hạng với huy chương vàng/bạc/đồng
- Hiển thị số vé và doanh thu

#### 5. Đặt vé gần đây
- Xem 10 đơn đặt vé gần nhất
- Thông tin: Mã đặt, khách hàng, phim, số ghế, tổng tiền, trạng thái

## 📊 Database Schema

### Tables sử dụng bởi Admin

**showtimes** - Quản lý suất chiếu:
```sql
CREATE TABLE showtimes (
    showtimeID INT PRIMARY KEY AUTO_INCREMENT,
    movieID INT,
    roomID INT,
    showtimeDate DATETIME,
    basePrice DECIMAL(10,2),
    status ENUM('available', 'full', 'cancelled')
);
```

**bookings** - Quản lý đặt vé:
```sql
CREATE TABLE bookings (
    bookingID INT PRIMARY KEY AUTO_INCREMENT,
    userID INT,
    showtimeID INT,
    bookingCode VARCHAR(50),
    totalPrice DECIMAL(10,2),
    totalSeats INT,
    paymentStatus ENUM('paid', 'pending', 'expired'),
    bookingDate DATETIME
);
```

### Dữ liệu suất chiếu

File `database_showtimes_update.sql` chứa:
- **Suất chiếu từ 25/12/2024 đến 15/01/2025**
- Tổng cộng: **~200+ suất chiếu**
- Bao gồm tất cả 8 phim (6 đang chiếu + 2 sắp chiếu)
- 3 phòng chiếu với giá khác nhau:
  - VKU Cinema 1 (2D): 45,000đ - 60,000đ
  - VKU Cinema 2 (3D): 50,000đ - 65,000đ
  - VKU Cinema 3 (IMAX): 55,000đ - 75,000đ

### Giá vé theo thời gian

- **Ngày thường** (Thứ 2-5): Giá thấp
- **Cuối tuần** (Thứ 6-CN): Giá tăng 10-15%
- **Ngày lễ** (31/12, 01/01): Giá cao nhất

## 🎨 Giao diện

### Màu sắc chủ đạo
- **Primary**: `#667eea` (Tím)
- **Secondary**: `#764ba2` (Tím đậm)
- **Success**: `#28a745` (Xanh lá)
- **Danger**: `#dc3545` (Đỏ)
- **Warning**: `#ffc107` (Vàng)

### Responsive
- Desktop: Hiển thị đầy đủ tính năng
- Tablet: Responsive grid
- Mobile: Tối ưu cho màn hình nhỏ

## 🔒 Bảo mật

### Kiểm tra quyền truy cập
```php
// Trong admin.php
if (!isset($_SESSION['user']['roleID']) || $_SESSION['user']['roleID'] != 1) {
    header('Location: index.php');
    exit();
}
```

### Kiểm tra API
```php
// Trong adminController.php
if (!isset($_SESSION['user']['roleID']) || $_SESSION['user']['roleID'] != 1) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}
```

## 📝 API Endpoints

### GET Requests
- `?action=getStats` - Lấy thống kê tổng quan
- `?action=getRevenueChart&period=30` - Lấy dữ liệu biểu đồ
- `?action=getShowtimes&date=2024-12-25&roomID=1` - Lấy suất chiếu
- `?action=getShowtime&id=1` - Lấy 1 suất chiếu
- `?action=getTopMovies` - Lấy top phim
- `?action=getRecentBookings` - Lấy đơn đặt gần đây
- `?action=getMovies` - Lấy danh sách phim
- `?action=getRooms` - Lấy danh sách phòng

### POST Requests
- `action=addShowtime` - Thêm suất chiếu mới
- `action=updateShowtime` - Cập nhật suất chiếu
- `action=deleteShowtime` - Xóa suất chiếu

## 🛠️ Công nghệ sử dụng

### Frontend
- **HTML5/CSS3**: Giao diện
- **JavaScript ES6**: Logic xử lý
- **Chart.js**: Vẽ biểu đồ
- **Font Awesome**: Icons

### Backend
- **PHP 7.4+**: Server-side
- **MySQL/MariaDB**: Database
- **PDO**: Database connection

### Libraries
```html
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
```

## ⚠️ Lưu ý quan trọng

1. **Không xóa suất chiếu đã có vé**: Hệ thống sẽ từ chối xóa
2. **Kiểm tra trùng lịch**: Không thể tạo 2 suất chiếu cùng phòng trong vòng 3 giờ
3. **Backup database**: Nên backup trước khi xóa dữ liệu
4. **Quyền admin**: Chỉ roleID = 1 mới truy cập được

## 🐛 Troubleshooting

### Lỗi 403 Forbidden
**Nguyên nhân**: Tài khoản không có quyền admin
**Giải pháp**: 
```sql
UPDATE `user` SET roleID = 1 WHERE email = 'your@email.com';
```

### Biểu đồ không hiển thị
**Nguyên nhân**: Thiếu Chart.js
**Giải pháp**: Kiểm tra internet connection hoặc tải Chart.js về local

### API không hoạt động
**Nguyên nhân**: Session không tồn tại
**Giải pháp**: Đăng xuất và đăng nhập lại

### Suất chiếu không load
**Nguyên nhân**: Database chưa có suất chiếu
**Giải pháp**: Import file `database_showtimes_update.sql`

## 📞 Hỗ trợ

Nếu gặp vấn đề, vui lòng:
1. Kiểm tra console browser (F12)
2. Kiểm tra PHP error log
3. Kiểm tra database connection

---

**Phát triển bởi**: VKU Cinema Team  
**Phiên bản**: 1.0.0  
**Ngày cập nhật**: 24/12/2024
