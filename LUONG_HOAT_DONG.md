# 🎬 GIẢI THÍCH LUỒNG HOẠT ĐỘNG HỆ THỐNG VKU CINEMA

## 📚 MỤC LỤC
1. [Tổng Quan Kiến Trúc](#1-tổng-quan-kiến-trúc)
2. [Luồng Khởi Động Hệ Thống](#2-luồng-khởi-động-hệ-thống)
3. [Luồng Đăng Ký/Đăng Nhập](#3-luồng-đăng-ký-đăng-nhập)
4. [Luồng Đặt Vé (4 Bước)](#4-luồng-đặt-vé-4-bước)
5. [Luồng Thanh Toán](#5-luồng-thanh-toán)
6. [Quản Lý Session & Bảo Mật](#6-quản-lý-session--bảo-mật)
7. [Cơ Chế AJAX & Real-time](#7-cơ-chế-ajax--real-time)
8. [Sơ Đồ Tổng Thể](#8-sơ-đồ-tổng-thể)

---

## 1. TỔNG QUAN KIẾN TRÚC

### 🏗️ Mô Hình MVC (Model-View-Controller)

```
┌──────────────────────────────────────────────────────┐
│                    CLIENT (Browser)                   │
│  ┌──────────────┐  ┌──────────────┐  ┌────────────┐ │
│  │   HTML/CSS   │  │  JavaScript  │  │   AJAX     │ │
│  └──────────────┘  └──────────────┘  └────────────┘ │
└────────────────────────┬─────────────────────────────┘
                         │ HTTP Request
                         ▼
┌──────────────────────────────────────────────────────┐
│                    SERVER (PHP)                       │
│  ┌────────────────────────────────────────────────┐  │
│  │              ENTRY POINT                       │  │
│  │            index.php / config.php              │  │
│  └────────────────────┬───────────────────────────┘  │
│                       │                              │
│       ┌───────────────┼───────────────┐              │
│       │               │               │              │
│  ┌────▼─────┐   ┌────▼────┐   ┌─────▼────┐         │
│  │  VIEWS   │   │ CONTROL │   │  MODELS  │         │
│  │ (Giao    │◄──┤  LERS   │──►│ (Database│         │
│  │  diện)   │   │ (Logic) │   │  Logic)  │         │
│  └──────────┘   └─────────┘   └─────┬────┘         │
│                                      │              │
└──────────────────────────────────────┼──────────────┘
                                       │
                                       ▼
                        ┌──────────────────────┐
                        │   DATABASE (MySQL)   │
                        │   - users            │
                        │   - movies           │
                        │   - bookings         │
                        │   - seats            │
                        │   - showtimes        │
                        └──────────────────────┘
```

### 📂 Cấu Trúc Thư Mục & Chức Năng

```
DACS2/
├── 🚀 index.php              # Entry point chính của ứng dụng
├── ⚙️ config.php             # Cấu hình database & môi trường
├── 🐛 debug.php              # Tool debug (development)
│
├── src/
│   ├── 🎮 controllers/        # Xử lý logic nghiệp vụ
│   │   ├── homeController.php           # Lấy dữ liệu trang chủ
│   │   ├── loginController.php          # Xử lý đăng nhập (form submit)
│   │   ├── loginControllerAjax.php      # Xử lý đăng nhập (AJAX)
│   │   ├── registerController.php       # Xử lý đăng ký (form submit)
│   │   ├── registerControllerAjax.php   # Xử lý đăng ký (AJAX)
│   │   ├── logoutController.php         # Xử lý đăng xuất
│   │   ├── bookingController.php        # Xử lý đặt vé (AJAX)
│   │   ├── seatController.php           # Quản lý ghế ngồi
│   │   ├── showtimeController.php       # Quản lý suất chiếu
│   │   └── paymentController.php        # Xử lý thanh toán
│   │
│   ├── 🗄️ models/             # Tương tác với database
│   │   ├── database.php         # Kết nối PDO MySQL
│   │   ├── user_db.php          # CRUD người dùng
│   │   ├── movie_db.php         # CRUD phim
│   │   ├── booking_db.php       # CRUD đặt vé
│   │   ├── seat_db.php          # CRUD ghế ngồi
│   │   ├── showtime_db.php      # CRUD suất chiếu
│   │   └── payment_db.php       # CRUD thanh toán
│   │
│   ├── 🎨 views/              # Giao diện người dùng
│   │   ├── header.php                    # Header + Auth Modal
│   │   ├── footer.php                    # Footer
│   │   ├── home.php                      # Trang chủ
│   │   ├── login.php                     # Trang đăng nhập
│   │   ├── register.php                  # Trang đăng ký
│   │   ├── booking_step1_showtimes.php   # Bước 1: Chọn suất chiếu
│   │   ├── booking_step2_seats.php       # Bước 2: Chọn ghế
│   │   ├── booking_step3_payment.php     # Bước 3: Thanh toán
│   │   └── booking_step4_confirm.php     # Bước 4: Xác nhận
│   │
│   ├── 💅 styles/             # CSS files
│   ├── 📜 js/                 # JavaScript files
│   ├── 🖼️ img/                # Hình ảnh
│   └── 🛠️ helpers/            # Utility functions
│       └── session_helper.php  # Quản lý session
```

---

## 2. LUỒNG KHỞI ĐỘNG HỆ THỐNG

### 🚀 Khi Người Dùng Truy Cập Website

```
1. Browser Request: http://localhost/
   ↓
2. index.php được load
   ├── session_start()              # Khởi tạo session
   ├── include header.php           # Load header + menu
   ├── include home.php             # Load nội dung trang chủ
   └── include footer.php           # Load footer
   ↓
3. homeController.php được thực thi
   ├── require database.php         # Kết nối DB
   ├── require movie_db.php         # Load functions phim
   │
   ├── get_random_movies(5)         # Lấy 5 phim cho banner
   ├── get_hot_movies(6)            # Lấy 6 phim hot
   ├── get_upcoming_movies(8)       # Lấy 8 phim sắp chiếu
   └── get_now_showing_movies()     # Lấy phim đang chiếu
   ↓
4. Dữ liệu được truyền vào views/home.php
   ├── Hiển thị banner slider
   ├── Hiển thị danh sách phim
   └── Render giao diện hoàn chỉnh
```

### ⚙️ Chi Tiết File config.php

```php
// Phát hiện môi trường tự động
$isLocalhost = in_array($_SERVER['HTTP_HOST'], ['localhost:3000', '127.0.0.1']);

if ($isLocalhost) {
    // Cấu hình LOCALHOST (XAMPP)
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'dacs2');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('BASE_URL', 'http://localhost');
} else {
    // Cấu hình HOSTING (Production)
    define('DB_HOST', 'onehost-webhn072403.000nethost.com');
    define('DB_NAME', 'slrnkpifhosting_DACS2');
    define('DB_USER', 'slrnkpifhosting_xiaoying');
    define('DB_PASS', '2D3i$>?+ZZ!`_bc');
    define('BASE_URL', 'https://lequocdinh.id.vn');
}

// Helper function tạo URL
function url($path = '') {
    return BASE_URL . BASE_PATH . ltrim($path, '/');
}
```

**💡 Lợi ích:**
- Tự động chuyển đổi giữa localhost và hosting
- Không cần sửa code khi deploy
- Quản lý lỗi khác nhau theo môi trường

---

## 3. LUỒNG ĐĂNG KÝ / ĐĂNG NHẬP

### 📝 ĐĂNG KÝ (2 Phương Thức)

#### **Phương Thức 1: Form Submit Thường (Reload Trang)**

```
1. User click "Đăng ký" → Chuyển đến register.php
   ↓
2. User điền form → Submit
   ↓
3. POST → registerController.php
   ├── Validate dữ liệu (email, password, confirmPassword)
   ├── Kiểm tra email đã tồn tại chưa (user_db.php::check_email_exists())
   ├── Hash password (password_hash())
   └── Lưu vào database (user_db.php::create_user())
   ↓
4. Redirect về login.php (header('Location: ...'))
   └── Hiển thị thông báo thành công qua $_SESSION['flash']
```

#### **Phương Thức 2: AJAX Modal (Không Reload)**

```
1. User click "Đăng ký" → Mở modal trên header.php
   ↓
2. User điền form → Click submit
   ↓
3. JavaScript (register.js) intercept:
   ├── event.preventDefault()           # Chặn submit mặc định
   ├── Thu thập dữ liệu form
   └── Gửi AJAX request
   ↓
4. POST → registerControllerAjax.php
   ├── header('Content-Type: application/json')  # Trả về JSON
   ├── Validate dữ liệu
   ├── Kiểm tra email
   ├── Hash password
   └── Lưu database
   ↓
5. Response JSON:
   {
     "success": true,
     "message": "Đăng ký thành công",
     "redirect": "/src/views/login.php"
   }
   ↓
6. JavaScript nhận response:
   ├── Đóng modal
   ├── Hiển thị thông báo
   └── Chuyển hướng (nếu cần)
```

### 🔐 ĐĂNG NHẬP (2 Phương Thức Tương Tự)

```
1. POST → loginController.php hoặc loginControllerAjax.php
   ↓
2. Xác thực user (user_db.php::authenticate_user())
   ├── SELECT user WHERE email = ?
   ├── Verify password (password_verify($password, $hash))
   └── Return user data hoặc false
   ↓
3. Nếu thành công:
   ├── Lưu thông tin vào SESSION:
   │   ├── $_SESSION['user'] = $user;
   │   ├── $_SESSION['userID'] = $user['userID'];
   │   └── $_SESSION['roleID'] = $user['roleID'];
   │
   ├── Update last login time (user_db.php::update_last_login())
   │
   └── Redirect theo role:
       ├── roleID = 1 → Admin Dashboard
       └── roleID = 2 → Home Page
   ↓
4. Nếu thất bại:
   └── Hiển thị lỗi "Email hoặc mật khẩu không đúng"
```

### 🔒 Bảo Mật Password

```php
// Khi đăng ký
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
// → Tạo hash bcrypt với cost 10 (mặc định)
// → Tự động thêm salt ngẫu nhiên
// → Kết quả: $2y$10$... (60 ký tự)

// Khi đăng nhập
if (password_verify($password, $user['password'])) {
    // ✅ Đúng password
} else {
    // ❌ Sai password
}
```

**💡 Tại sao an toàn?**
- Mỗi password có salt riêng
- Không thể reverse hash → plaintext
- Chống brute force (cost factor)

---

## 4. LUỒNG ĐẶT VÉ (4 BƯỚC)

### 🎟️ Tổng Quan Quy Trình

```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   BƯỚC 1    │───►│   BƯỚC 2    │───►│   BƯỚC 3    │───►│   BƯỚC 4    │
│  Chọn Suất  │    │  Chọn Ghế   │    │ Thanh Toán  │    │  Xác Nhận   │
│   Chiếu     │    │             │    │             │    │             │
└─────────────┘    └─────────────┘    └─────────────┘    └─────────────┘
     ↓                   ↓                   ↓                   ↓
  Chọn phim         Lock ghế 10p        Tạo booking         Confirm + QR
  Chọn ngày         Real-time           Tính tiền           Send email
  Chọn giờ          WebSocket?          VNPay/Momo
```

### 🎬 BƯỚC 1: Chọn Suất Chiếu

**File:** `booking_step1_showtimes.php`

```
1. User click "Đặt vé" trên card phim
   ├── Chuyển đến: booking_step1_showtimes.php?movieID=123
   └── Kiểm tra đăng nhập:
       ├── Nếu chưa đăng nhập → Hiển thị modal login
       └── Nếu đã đăng nhập → Cho phép đặt vé
   ↓
2. Controller Load Dữ Liệu:
   ├── require movie_db.php
   ├── require showtime_db.php
   │
   ├── get_movie_by_id($movieID)        # Lấy thông tin phim
   ├── get_available_dates_by_movie()   # Lấy 7 ngày có suất chiếu
   └── get_showtimes_by_movie()         # Lấy suất chiếu theo ngày
   ↓
3. Hiển thị Giao Diện:
   ├── Thông tin phim (poster, tên, thể loại, thời lượng)
   ├── Date picker (7 ngày)
   └── Danh sách suất chiếu:
       ├── Thời gian (09:00, 12:00, 15:00...)
       ├── Rạp (VKU Cinema 1, 2, 3...)
       └── Loại màn hình (2D, 3D, IMAX)
   ↓
4. User Chọn Suất Chiếu:
   ├── Click vào button suất chiếu
   └── Redirect: booking_step2_seats.php?showtimeID=456
```

**JavaScript Logic:**

```javascript
// booking_showtimes.js
document.querySelectorAll('.showtime-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const showtimeID = this.dataset.showtimeId;
        window.location.href = `/src/views/booking_step2_seats.php?showtimeID=${showtimeID}`;
    });
});
```

---

### 💺 BƯỚC 2: Chọn Ghế

**File:** `booking_step2_seats.php`

```
1. Load Trang:
   ├── GET showtimeID từ URL
   ├── Kiểm tra đăng nhập (redirect nếu chưa đăng nhập)
   │
   ├── require seat_db.php
   ├── require showtime_db.php
   │
   ├── get_showtime_details($showtimeID)    # Thông tin suất chiếu
   └── get_seats_by_showtime($showtimeID)   # Danh sách ghế
   ↓
2. Hiển thị Sơ Đồ Rạp:
   ├── Màn hình (Screen)
   ├── Ghế theo hàng (A, B, C, D...)
   └── Trạng thái ghế:
       ├── ⬜ Available (có thể chọn)
       ├── 🟦 Selected (đang chọn)
       ├── 🟥 Booked (đã được đặt)
       └── 🟨 Locked (đang được giữ bởi user khác)
   ↓
3. User Click Chọn Ghế:
   ├── JavaScript toggle class 'selected'
   ├── Cập nhật danh sách ghế đã chọn
   └── Tính tổng tiền real-time
   ↓
4. AJAX Lock Ghế (10 phút):
   ├── POST → seatController.php?action=lock_seats
   ├── Gửi: {showtimeID, seatIDs, userID}
   │
   └── Database:
       UPDATE seats
       SET status = 'locked',
           lockedBy = $userID,
           lockedAt = NOW()
       WHERE seatID IN (...)
   ↓
5. Countdown Timer (10:00):
   ├── Đếm ngược thời gian giữ ghế
   └── Hết giờ → Auto unlock ghế
   ↓
6. User Click "Tiếp tục":
   ├── Validate: Ít nhất 1 ghế được chọn
   └── Redirect: booking_step3_payment.php?showtimeID=456&seats=1,2,3
```

**JavaScript Logic (booking_seats.js):**

```javascript
// Chọn/bỏ chọn ghế
seats.forEach(seat => {
    seat.addEventListener('click', function() {
        if (this.classList.contains('available')) {
            this.classList.toggle('selected');
            updateSelectedSeats();
            calculateTotal();
        }
    });
});

// Lock ghế khi chọn
async function lockSeats(seatIDs) {
    const response = await fetch('/src/controllers/seatController.php', {
        method: 'POST',
        body: JSON.stringify({
            action: 'lock_seats',
            showtimeID: showtimeID,
            seatIDs: seatIDs
        })
    });
    const data = await response.json();
    if (!data.success) {
        alert('Ghế đã được đặt. Vui lòng chọn ghế khác.');
    }
}

// Countdown 10 phút
let timeLeft = 600; // 10 phút = 600 giây
const timer = setInterval(() => {
    timeLeft--;
    updateTimerDisplay(timeLeft);
    if (timeLeft <= 0) {
        clearInterval(timer);
        unlockSeats();
        alert('Hết thời gian giữ ghế!');
    }
}, 1000);
```

**Backend Logic (seatController.php):**

```php
case 'lock_seats':
    $showtimeID = $_POST['showtimeID'];
    $seatIDs = $_POST['seatIDs'];
    $userID = $_SESSION['userID'];
    
    // Kiểm tra ghế còn available không
    $available = check_seats_available($showtimeID, $seatIDs);
    if (!$available) {
        echo json_encode(['success' => false, 'message' => 'Ghế đã được đặt']);
        exit;
    }
    
    // Lock ghế
    $locked = lock_seats($showtimeID, $seatIDs, $userID, 10); // 10 phút
    echo json_encode(['success' => $locked]);
    break;
```

---

### 💳 BƯỚC 3: Thanh Toán

**File:** `booking_step3_payment.php`

```
1. Load Trang:
   ├── GET showtimeID, seats từ URL
   ├── Validate dữ liệu
   │
   ├── get_showtime_details($showtimeID)
   ├── get_seats_by_ids($seatIDs)
   └── Tính tổng tiền:
       ├── Standard seat: 45,000đ
       ├── VIP seat: 75,000đ
       ├── Couple seat: 150,000đ
       └── Food combo (nếu có): +50,000đ
   ↓
2. Hiển thị Thông Tin:
   ├── Chi tiết phim & suất chiếu
   ├── Danh sách ghế đã chọn
   ├── Tổng tiền
   └── Form thông tin:
       ├── Họ tên
       ├── Số điện thoại
       └── Email
   ↓
3. Chọn Phương Thức Thanh Toán:
   ├── 💳 VNPay
   ├── 🏦 Momo
   ├── 🏪 Banking
   └── 💵 Tiền mặt (tại quầy)
   ↓
4. User Click "Thanh Toán":
   ├── POST → paymentController.php
   ├── Gửi: {showtimeID, seatIDs, totalPrice, paymentMethod, userInfo}
   │
   └── Server:
       ├── Tạo booking:
       │   INSERT INTO bookings (userID, showtimeID, totalPrice, status)
       │   → bookingID
       │
       ├── Lưu chi tiết ghế:
       │   INSERT INTO booking_details (bookingID, seatID, price)
       │
       ├── Cập nhật trạng thái ghế:
       │   UPDATE seats SET status = 'booked' WHERE seatID IN (...)
       │
       └── Tạo mã QR code (bookingCode)
   ↓
5. Xử Lý Thanh Toán:
   ├── Nếu Online (VNPay/Momo):
   │   ├── Tạo payment request
   │   ├── Redirect đến cổng thanh toán
   │   └── Callback sau khi thanh toán
   │
   └── Nếu Tiền mặt:
       └── Đánh dấu status = 'pending_payment'
   ↓
6. Redirect: booking_step4_confirm.php?bookingID=789
```

**Backend Logic (bookingController.php):**

```php
function create_booking_action() {
    $showtimeID = $_POST['showtimeID'];
    $seatIDs = $_POST['seatIDs'];
    $userID = $_SESSION['userID'];
    $totalPrice = $_POST['totalPrice'];
    
    // Bắt đầu transaction
    $db->beginTransaction();
    try {
        // 1. Tạo booking
        $bookingCode = generate_booking_code(); // VKU202511100001
        $bookingID = create_booking($userID, $showtimeID, $totalPrice, $bookingCode);
        
        // 2. Lưu chi tiết ghế
        foreach ($seatIDs as $seatID) {
            $seatInfo = get_seat_by_id($seatID);
            insert_booking_detail($bookingID, $seatID, $seatInfo['price']);
        }
        
        // 3. Cập nhật trạng thái ghế
        update_seats_status($seatIDs, 'booked');
        
        // 4. Tạo QR code
        $qrCode = generate_qr_code($bookingCode);
        
        // Commit transaction
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'bookingID' => $bookingID,
            'bookingCode' => $bookingCode,
            'qrCode' => $qrCode
        ]);
    } catch (Exception $e) {
        // Rollback nếu có lỗi
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
```

---

### ✅ BƯỚC 4: Xác Nhận

**File:** `booking_step4_confirm.php`

```
1. Load Trang:
   ├── GET bookingID từ URL
   ├── get_booking_details($bookingID)
   └── Kiểm tra quyền (user chỉ xem được booking của mình)
   ↓
2. Hiển thị Thông Tin:
   ├── ✅ Đặt vé thành công!
   ├── Mã đặt vé (VKU202511100001)
   ├── QR Code (để quét tại rạp)
   ├── Thông tin phim & suất chiếu
   ├── Danh sách ghế
   ├── Tổng tiền
   └── Trạng thái thanh toán
   ↓
3. Actions:
   ├── 📧 Gửi email xác nhận
   ├── 💾 Tải vé (PDF)
   ├── 🖨️ In vé
   └── 🏠 Về trang chủ
```

---

## 5. LUỒNG THANH TOÁN

### 💳 Tích Hợp VNPay

```
1. User chọn VNPay → paymentController.php
   ↓
2. Tạo Payment Request:
   ├── Tham số:
   │   ├── vnp_Amount: 450000 (VNĐ * 100)
   │   ├── vnp_TxnRef: VKU202511100001
   │   ├── vnp_OrderInfo: "Thanh toan ve xem phim"
   │   ├── vnp_ReturnUrl: callback URL
   │   └── vnp_SecureHash: SHA256 hash
   │
   └── Redirect đến VNPay:
       https://sandbox.vnpayment.vn/paymentv2/vpcpay.html?vnp_Amount=...
   ↓
3. User Thanh Toán trên VNPay:
   ├── Nhập thông tin thẻ
   └── Xác nhận thanh toán
   ↓
4. VNPay Callback:
   ├── GET → paymentController.php?vnp_ResponseCode=00&vnp_TxnRef=...
   ├── Verify secure hash
   │
   └── Update booking:
       ├── vnp_ResponseCode = '00' (thành công)
       │   ├── UPDATE bookings SET status = 'confirmed'
       │   └── Send email xác nhận
       │
       └── vnp_ResponseCode != '00' (thất bại)
           ├── UPDATE bookings SET status = 'cancelled'
           └── Unlock seats
```

**Code Tích Hợp VNPay:**

```php
// Tạo payment URL
function create_vnpay_payment_url($bookingID, $amount) {
    $vnp_TmnCode = "YOUR_TMN_CODE";
    $vnp_HashSecret = "YOUR_HASH_SECRET";
    $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
    $vnp_ReturnUrl = url('src/controllers/paymentController.php?action=vnpay_callback');
    
    $vnp_Params = [
        'vnp_Version' => '2.1.0',
        'vnp_Command' => 'pay',
        'vnp_TmnCode' => $vnp_TmnCode,
        'vnp_Amount' => $amount * 100,
        'vnp_BankCode' => 'NCB',
        'vnp_CreateDate' => date('YmdHis'),
        'vnp_CurrCode' => 'VND',
        'vnp_IpAddr' => $_SERVER['REMOTE_ADDR'],
        'vnp_Locale' => 'vn',
        'vnp_OrderInfo' => "Thanh toan ve xem phim #$bookingID",
        'vnp_OrderType' => 'billpayment',
        'vnp_ReturnUrl' => $vnp_ReturnUrl,
        'vnp_TxnRef' => $bookingID,
    ];
    
    ksort($vnp_Params);
    $query = http_build_query($vnp_Params);
    $vnp_SecureHash = hash_hmac('sha512', $query, $vnp_HashSecret);
    $vnp_Url = $vnp_Url . "?" . $query . '&vnp_SecureHash=' . $vnp_SecureHash;
    
    return $vnp_Url;
}

// Xử lý callback
function vnpay_callback() {
    $vnp_ResponseCode = $_GET['vnp_ResponseCode'];
    $vnp_TxnRef = $_GET['vnp_TxnRef']; // bookingID
    
    if ($vnp_ResponseCode == '00') {
        // Thanh toán thành công
        update_booking_status($vnp_TxnRef, 'confirmed');
        send_confirmation_email($vnp_TxnRef);
        
        header('Location: /src/views/booking_step4_confirm.php?bookingID=' . $vnp_TxnRef);
    } else {
        // Thanh toán thất bại
        cancel_booking($vnp_TxnRef);
        header('Location: /src/views/booking_step3_payment.php?error=payment_failed');
    }
}
```

---

## 6. QUẢN LÝ SESSION & BẢO MẬT

### 🔐 Session Lifecycle

```
1. Khởi tạo Session:
   ├── index.php → session_start()
   └── Tạo session ID (cookie PHPSESSID)
   ↓
2. Lưu Thông Tin User:
   $_SESSION['user'] = [
       'userID' => 1,
       'email' => 'user@example.com',
       'fullName' => 'Nguyễn Văn A',
       'roleID' => 2
   ];
   $_SESSION['userID'] = 1;
   $_SESSION['roleID'] = 2;
   ↓
3. Kiểm Tra Đăng Nhập:
   if (!isset($_SESSION['userID'])) {
       header('Location: /src/views/login.php');
       exit;
   }
   ↓
4. Đăng Xuất:
   ├── session_unset()         # Xóa tất cả biến session
   ├── session_destroy()        # Hủy session
   └── Redirect về trang chủ
```

### 🛡️ Bảo Mật

#### **1. SQL Injection Prevention**

```php
// ❌ BAD - Dễ bị SQL Injection
$sql = "SELECT * FROM users WHERE email = '$email'";

// ✅ GOOD - Sử dụng Prepared Statements
$stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
```

#### **2. XSS Prevention**

```php
// ❌ BAD - Có thể inject script
echo $_GET['name'];

// ✅ GOOD - Escape output
echo htmlspecialchars($_GET['name'], ENT_QUOTES, 'UTF-8');
```

#### **3. CSRF Prevention**

```php
// Tạo token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Trong form
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

// Verify
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('CSRF token invalid');
}
```

#### **4. Password Hashing**

```php
// Hash
$hash = password_hash($password, PASSWORD_DEFAULT);
// → $2y$10$... (bcrypt)

// Verify
if (password_verify($password, $hash)) {
    // ✅ Password correct
}
```

---

## 7. Cơ CHẾ AJAX & REAL-TIME

### 📡 AJAX Request Flow

```javascript
// 1. Client gửi request
fetch('/src/controllers/bookingController.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        action: 'create_booking',
        showtimeID: 123,
        seatIDs: [1, 2, 3],
        totalPrice: 135000
    })
})
.then(response => response.json())
.then(data => {
    // 2. Xử lý response
    if (data.success) {
        alert('Đặt vé thành công!');
        window.location.href = `/booking_step4_confirm.php?bookingID=${data.bookingID}`;
    } else {
        alert(data.message);
    }
})
.catch(error => {
    console.error('Error:', error);
});
```

### ⚡ Real-time Seat Status

```javascript
// Polling: Kiểm tra trạng thái ghế mỗi 5 giây
setInterval(async () => {
    const response = await fetch(`/src/controllers/seatController.php?action=get_seats&showtimeID=${showtimeID}`);
    const data = await response.json();
    
    // Cập nhật UI
    data.seats.forEach(seat => {
        const seatElement = document.querySelector(`[data-seat-id="${seat.seatID}"]`);
        seatElement.className = `seat ${seat.status}`;
    });
}, 5000);
```

**💡 Lưu ý:** Có thể nâng cấp lên WebSocket để real-time tốt hơn:

```javascript
// WebSocket (Advanced)
const ws = new WebSocket('ws://localhost:8080');

ws.onmessage = (event) => {
    const data = JSON.parse(event.data);
    if (data.type === 'seat_locked') {
        updateSeatStatus(data.seatID, 'locked');
    }
};
```

---

## 8. SƠ ĐỒ TỔNG THỂ

### 🗺️ Database Schema

```sql
-- Bảng users
users
├── userID (PK)
├── email (UNIQUE)
├── password (HASHED)
├── fullName
├── phoneNumber
├── roleID (FK → roles)
└── createdAt

-- Bảng movies
movies
├── movieID (PK)
├── title
├── description
├── duration
├── releaseDate
├── posterURL
├── trailerURL
├── status (now_showing, coming_soon)
└── rating

-- Bảng showtimes
showtimes
├── showtimeID (PK)
├── movieID (FK → movies)
├── cinemaID (FK → cinemas)
├── showDate
├── showTime
└── screenType (2D, 3D, IMAX)

-- Bảng seats
seats
├── seatID (PK)
├── cinemaID (FK → cinemas)
├── seatRow (A, B, C...)
├── seatNumber (1, 2, 3...)
├── seatType (standard, vip, couple)
└── price

-- Bảng bookings
bookings
├── bookingID (PK)
├── userID (FK → users)
├── showtimeID (FK → showtimes)
├── bookingCode (VKU202511100001)
├── totalPrice
├── status (pending, confirmed, cancelled)
├── paymentMethod
├── paymentStatus
└── createdAt

-- Bảng booking_details (ghế đã đặt)
booking_details
├── detailID (PK)
├── bookingID (FK → bookings)
├── seatID (FK → seats)
└── price

-- Bảng seat_locks (ghế đang lock tạm thời)
seat_locks
├── lockID (PK)
├── seatID (FK → seats)
├── showtimeID (FK → showtimes)
├── userID (FK → users)
├── lockedAt
└── expiresAt (lockedAt + 10 phút)
```

### 🔄 Luồng Dữ Liệu Hoàn Chỉnh

```
┌──────────────────────────────────────────────────────────┐
│                    USER JOURNEY                          │
└──────────────────────────────────────────────────────────┘
                            │
        ┌───────────────────┼───────────────────┐
        │                   │                   │
        ▼                   ▼                   ▼
   🏠 TRANG CHỦ      🔐 ĐĂNG NHẬP        📝 ĐĂNG KÝ
        │                   │                   │
        │              ✅ Success               │
        └───────────────────┴───────────────────┘
                            │
                            ▼
                    🎟️ ĐẶT VÉ
                            │
        ┌───────────────────┼───────────────────┐
        │                   │                   │
        ▼                   ▼                   ▼
   📅 Bước 1          💺 Bước 2          💳 Bước 3
   Chọn suất          Chọn ghế          Thanh toán
        │                   │                   │
        └───────────────────┴───────────────────┘
                            │
                            ▼
                    ✅ Bước 4: Xác nhận
                            │
        ┌───────────────────┼───────────────────┐
        │                   │                   │
        ▼                   ▼                   ▼
   📧 Email         📄 PDF Vé         🏠 Về trang chủ
```

---

## 🎯 TỔNG KẾT

### ✨ Các Tính Năng Chính

1. **Authentication System**
   - Đăng ký/Đăng nhập (Form + AJAX)
   - Session management
   - Password hashing (bcrypt)
   - Remember me (optional)

2. **Booking System**
   - 4 bước đặt vé trực quan
   - Lock ghế tạm thời (10 phút)
   - Real-time seat status
   - Countdown timer

3. **Payment Integration**
   - VNPay, Momo
   - Tiền mặt tại quầy
   - QR code generation
   - Email confirmation

4. **Security**
   - SQL Injection prevention (PDO)
   - XSS prevention (htmlspecialchars)
   - CSRF protection (tokens)
   - Password hashing (bcrypt)

### 🚀 Công Nghệ Sử Dụng

- **Backend:** PHP 7.4+ (PDO, Sessions)
- **Frontend:** HTML5, CSS3, JavaScript (ES6+)
- **Database:** MySQL 8.0
- **Libraries:**
  - Font Awesome (icons)
  - SweetAlert2 (alerts)
  - QRCode.js (QR generation)

### 📈 Khả Năng Mở Rộng

1. **Admin Dashboard**
   - Quản lý phim, suất chiếu
   - Quản lý người dùng
   - Thống kê doanh thu
   - Báo cáo

2. **Mobile App**
   - React Native / Flutter
   - Push notifications
   - In-app payment

3. **Advanced Features**
   - WebSocket real-time
   - Recommendation system (AI)
   - Loyalty program
   - Social sharing

---

## 📞 LIÊN HỆ & HỖ TRỢ

- **Developer:** [Tên của bạn]
- **Email:** [Email của bạn]
- **GitHub:** https://github.com/lequocdinh-kt/DACS2
- **Website:** https://lequocdinh.id.vn

---

**🎬 VKU Cinema - Đặt vé dễ dàng, xem phim thoải mái! 🍿**
