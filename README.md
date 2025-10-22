# 🎬 VKU Cinema - Hệ Thống Đặt Vé Xem Phim

## 📋 Mục Lục
- [Tổng Quan Dự Án](#tổng-quan-dự-án)
- [Cấu Trúc Thư Mục](#cấu-trúc-thư-mục)
- [Giải Thích Chi Tiết Từng File](#giải-thích-chi-tiết-từng-file)
- [Luồng Hoạt Động](#luồng-hoạt-động)
- [Cài Đặt và Sử Dụng](#cài-đặt-và-sử-dụng)

---

## 🎯 Tổng Quan Dự Án

**VKU Cinema** là hệ thống website đặt vé xem phim online với các tính năng:
- ✅ Đăng nhập/Đăng ký bằng Modal (không reload trang)
- ✅ Hiển thị phim đang chiếu và sắp chiếu
- ✅ Banner slider với thông tin phim
- ✅ Đếm ngược thời gian ra mắt phim
- ✅ Xem trailer YouTube
- ✅ Quản lý session người dùng
- ✅ Responsive design

---

## 📁 Cấu Trúc Thư Mục

```
DACS2/
├── index.php                          # File chính - điểm khởi đầu
├── src/
│   ├── controllers/                   # Xử lý logic nghiệp vụ
│   │   ├── homeController.php         # Controller trang chủ
│   │   ├── loginController.php        # Xử lý đăng nhập (form thường)
│   │   ├── loginControllerAjax.php    # Xử lý đăng nhập (AJAX/Modal)
│   │   ├── logoutController.php       # Xử lý đăng xuất
│   │   ├── registerController.php     # Xử lý đăng ký (form thường)
│   │   └── registerControllerAjax.php # Xử lý đăng ký (AJAX/Modal)
│   │
│   ├── models/                        # Tương tác với database
│   │   ├── database.php               # Kết nối database
│   │   ├── user_db.php                # Xử lý dữ liệu người dùng
│   │   └── movie_db.php               # Xử lý dữ liệu phim
│   │
│   ├── views/                         # Giao diện người dùng
│   │   ├── header.php                 # Header + Auth Modal
│   │   ├── footer.php                 # Footer
│   │   ├── home.php                   # Trang chủ
│   │   ├── login.php                  # Trang đăng nhập độc lập
│   │   └── register.php               # Trang đăng ký độc lập
│   │
│   ├── styles/                        # CSS files
│   │   ├── header.css                 # Style cho header + modal
│   │   ├── home.css                   # Style cho trang chủ
│   │   ├── login.css                  # Style cho trang login
│   │   ├── register.css               # Style cho trang register
│   │   └── footer.css                 # Style cho footer
│   │
│   ├── js/                            # JavaScript files
│   │   ├── header.js                  # Logic cho header + modal + slideshow
│   │   ├── home.js                    # Logic cho trang chủ
│   │   ├── login.js                   # Logic cho trang login
│   │   └── register.js                # Logic cho trang register
│   │
│   ├── helpers/                       # Các hàm tiện ích
│   │   └── session_helper.php         # Quản lý session
│   │
│   └── img/                           # Hình ảnh
│       ├── posters/                   # Poster phim dọc
│       ├── moviesHorizontal/          # Poster phim ngang
│       └── moviesVertical/            # Poster phim dọc
│
└── README.md                          # File này
```

---

## 📝 Giải Thích Chi Tiết Từng File

### 🔹 **1. index.php** (File Khởi Đầu)

**Chức năng:** Điểm vào chính của website

**Code:**
```php
<?php 
session_start();  // Khởi động session để lưu trạng thái đăng nhập
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Load các file CSS -->
    <link rel="stylesheet" href="src/styles/header.css">
    <link rel="stylesheet" href="/src/styles/home.css">
    <link rel="stylesheet" href="src/styles/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Home</title>
</head>
<body>
    <?php include 'src/views/header.php'; ?>  <!-- Include header -->
    <?php include 'src/views/home.php'; ?>     <!-- Include nội dung chính -->
    <?php include 'src/views/footer.php'; ?>   <!-- Include footer -->
</body>
</html>
```

**Giải thích:**
- `session_start()`: Bắt buộc phải gọi TRƯỚC bất kỳ output HTML nào
- Include 3 phần: header (menu + modal), home (nội dung), footer
- Load tất cả CSS và Font Awesome icons

---

### 🔹 **2. src/models/database.php** (Kết Nối Database)

**Chức năng:** Tạo kết nối PDO đến MySQL database

**Code:**
```php
<?php
// Thông tin kết nối XAMPP
$host = 'localhost';
$dbname = 'dacs2';
$username = 'root';
$password = '';

try {
    // Tạo kết nối PDO
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";
    $db = new PDO($dsn, $username, $password);
    
    // Bật chế độ báo lỗi
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    error_log("❌ Lỗi kết nối CSDL: " . $e->getMessage());
    die("Không thể kết nối đến cơ sở dữ liệu.");
}
?>
```

**Giải thích:**
- Sử dụng **PDO** (PHP Data Objects) - an toàn hơn mysqli
- `$db` là biến global được dùng trong toàn bộ project
- `ERRMODE_EXCEPTION`: Ném exception khi có lỗi SQL
- `charset=utf8`: Hỗ trợ tiếng Việt

**⚠️ Lưu ý quan trọng:**
- KHÔNG được có dòng trống hoặc BOM ở đầu file
- KHÔNG được echo/print gì trong file này (sẽ làm hỏng JSON response)

---

### 🔹 **3. src/models/user_db.php** (Xử Lý User Data)

**Chức năng:** Các hàm tương tác với bảng `user` trong database

**Các hàm chính:**

#### 📌 `get_user_by_email($email)`
```php
function get_user_by_email($email) {
    global $db;
    $sql = 'SELECT userID, username, password, email, roleID 
            FROM `user` WHERE email = :email LIMIT 1';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':email', $email);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
```
- Tìm user theo email
- Trả về array thông tin user hoặc `false` nếu không tìm thấy

#### 📌 `authenticate_user($email, $password)`
```php
function authenticate_user($email, $password) {
    $user = get_user_by_email($email);
    if (!$user) return false;
    
    if (password_verify($password, $user['password'])) {
        unset($user['password']); // Xóa password trước khi return
        return $user;
    }
    return false;
}
```
- Xác thực người dùng
- Sử dụng `password_verify()` để kiểm tra mật khẩu đã hash
- Xóa password khỏi array trước khi trả về (bảo mật)

#### 📌 `email_exists($email)`
```php
function email_exists($email) {
    global $db;
    $sql = 'SELECT COUNT(*) FROM `user` WHERE email = :email';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':email', $email);
    $stmt->execute();
    return $stmt->fetchColumn() > 0;
}
```
- Kiểm tra email đã tồn tại chưa
- Trả về `true/false`

#### 📌 `register_user($username, $email, $password)`
```php
function register_user($username, $email, $password) {
    global $db;
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $sql = 'INSERT INTO `user` (username, email, password, roleID) 
            VALUES (:username, :email, :password, 2)';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':username', $username);
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':password', $hashedPassword);
    
    try {
        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}
```
- Tạo user mới
- `password_hash()`: Hash mật khẩu an toàn (bcrypt)
- `roleID = 2`: User thường (1 là admin)

---

### 🔹 **4. src/controllers/loginControllerAjax.php** (Xử Lý Đăng Nhập AJAX)

**Chức năng:** Xử lý request đăng nhập từ modal (không reload trang)

**Code:**
```php
<?php
session_start();

// Cấu hình error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0); // Không hiển thị lỗi ra màn hình
ini_set('log_errors', 1);     // Log lỗi vào file

header('Content-Type: application/json'); // Trả về JSON

try {
    require_once __DIR__ . '/../models/user_db.php';

    // Kiểm tra method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }

    // Lấy dữ liệu từ form
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Validate
    if ($email === '' || $password === '') {
        echo json_encode([
            'success' => false, 
            'message' => 'Vui lòng nhập đầy đủ email và mật khẩu!'
        ]);
        exit;
    }

    // Xác thực
    $user = authenticate_user($email, $password);
    
    if ($user) {
        // Lưu vào session
        $_SESSION['user'] = $user;
        $_SESSION['userID'] = $user['userID'];
        $_SESSION['roleID'] = $user['roleID'];

        // Cập nhật last login
        update_last_login($user['userID']);

        // Trả về thành công
        echo json_encode([
            'success' => true, 
            'message' => 'Đăng nhập thành công!', 
            'user' => [
                'username' => $user['username'], 
                'roleID' => $user['roleID']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Email hoặc mật khẩu không đúng!'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Đã xảy ra lỗi hệ thống. Vui lòng thử lại!'
    ]);
}
?>
```

**Luồng xử lý:**
1. Nhận POST request từ JavaScript
2. Validate dữ liệu đầu vào
3. Xác thực với database
4. Lưu thông tin vào session
5. Trả về JSON response
6. JavaScript nhận response và reload trang

**⚠️ Quan trọng:**
- Phải set `Content-Type: application/json`
- Không được có bất kỳ output nào trước `header()`
- Tất cả response đều là JSON

---

### 🔹 **5. src/controllers/logoutController.php** (Xử Lý Đăng Xuất)

**Chức năng:** Đăng xuất và xóa session

**Code:**
```php
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Xóa tất cả session variables
$_SESSION = array();

// Xóa session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Hủy session
session_destroy();

// Tạo session mới để lưu flash message
session_start();
$_SESSION['flash'] = [
    'type' => 'success',
    'message' => 'Đăng xuất thành công!'
];

// Redirect về trang chủ
header('Location: /index.php');
exit;
?>
```

**Luồng xử lý:**
1. Xóa toàn bộ dữ liệu session
2. Xóa cookie session
3. Hủy session hiện tại
4. Tạo session mới với flash message
5. Redirect về trang chủ

---

### 🔹 **6. src/views/header.php** (Header + Modal Auth)

**Chức năng:** 
- Hiển thị header với menu
- Chứa modal đăng nhập/đăng ký
- Hiển thị username nếu đã đăng nhập

**Cấu trúc:**

#### A. Header Desktop
```php
<header class="main-header">
    <div class="header-container">
        <!-- Logo -->
        <div class="header-logo">...</div>
        
        <!-- Menu -->
        <nav class="header-nav">
            <ul class="nav-menu">
                <li><a href="index.php">LỊCH CHIẾU</a></li>
                <li><a href="movies.php">PHIM</a></li>
                ...
            </ul>
        </nav>
        
        <!-- Auth Section -->
        <div class="header-auth">
            <?php if(isset($_SESSION['userID'])): ?>
                <!-- Hiển thị username + nút logout -->
                <a href="profile.php" class="user-profile">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo htmlspecialchars($_SESSION['user']['username']); ?></span>
                </a>
                <a href="./src/controllers/logoutController.php" class="btn-logout">
                    ĐĂNG XUẤT
                </a>
            <?php else: ?>
                <!-- Hiển thị nút login/register -->
                <a href="javascript:void(0)" onclick="openAuthModal('login')">ĐĂNG NHẬP</a>
                <a href="javascript:void(0)" onclick="openAuthModal('register')">ĐĂNG KÝ</a>
            <?php endif; ?>
        </div>
    </div>
</header>
```

#### B. Modal Đăng Nhập/Đăng Ký
```php
<div id="authModal" class="auth-modal">
    <div class="auth-modal-overlay" onclick="closeAuthModal()"></div>
    
    <div class="auth-modal-content">
        <button class="auth-modal-close" onclick="closeAuthModal()">×</button>
        
        <div class="auth-modal-container">
            <!-- Bên trái: Slideshow poster -->
            <div class="auth-left-section">
                <div class="welcome-msg">
                    <h1 id="authWelcomeTitle">Chào mừng quay trở lại</h1>
                    <p class="brand">VKU Cinema</p>
                </div>
                <div class="slideshow-outer">
                    <div class="slideshow-frame">
                        <img id="auth-slideshow-current" src="/src/img/posters/1.jpg" />
                        <img id="auth-slideshow-next" src="/src/img/posters/2.jpg" />
                    </div>
                    <div class="slideshow-dots" id="auth-slideshow-dots"></div>
                </div>
            </div>
            
            <!-- Bên phải: Form -->
            <div class="auth-right-section">
                <!-- Login Form -->
                <div id="loginFormContainer" class="auth-form-container">
                    <form id="loginForm" class="auth-form">
                        <input type="email" name="email" required>
                        <input type="password" name="password" required>
                        <button type="submit">Đăng Nhập</button>
                    </form>
                </div>
                
                <!-- Register Form -->
                <div id="registerFormContainer" style="display: none;">
                    <form id="registerForm" class="auth-form">
                        <input type="text" name="username" required>
                        <input type="email" name="email" required>
                        <input type="password" name="password" required>
                        <button type="submit">Đăng Ký</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
```

**Giải thích:**
- Modal có 2 phần: slideshow (trái) + form (phải)
- Slideshow tự động chuyển ảnh mỗi 4 giây
- Form submit qua AJAX, không reload trang
- Overlay tối phía sau modal

---

### 🔹 **7. src/js/header.js** (Logic cho Header & Modal)

**Các hàm chính:**

#### A. Mở/Đóng Modal
```javascript
function openAuthModal(formType) {
    const modal = document.getElementById('authModal');
    const loginContainer = document.getElementById('loginFormContainer');
    const registerContainer = document.getElementById('registerFormContainer');
    const welcomeTitle = document.getElementById('authWelcomeTitle');
    
    if (formType === 'login') {
        loginContainer.style.display = 'block';
        registerContainer.style.display = 'none';
        welcomeTitle.textContent = 'Chào mừng quay trở lại';
    } else {
        loginContainer.style.display = 'none';
        registerContainer.style.display = 'block';
        welcomeTitle.textContent = 'Tham gia cùng chúng tôi';
    }
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden'; // Khóa scroll
    
    initAuthSlideshow(); // Khởi động slideshow
}

function closeAuthModal() {
    const modal = document.getElementById('authModal');
    modal.classList.remove('active');
    document.body.style.overflow = ''; // Mở scroll
    
    // Dừng slideshow
    if (window.authSlideshowInterval) {
        clearInterval(window.authSlideshowInterval);
    }
    
    // Clear forms
    document.getElementById('loginForm').reset();
    document.getElementById('registerForm').reset();
}
```

#### B. Slideshow Tự Động
```javascript
function initAuthSlideshow() {
    const posters = [
        '/src/img/posters/1.jpg',
        '/src/img/posters/2.jpg',
        '/src/img/posters/3.jpg',
        '/src/img/posters/4.jpg',
        '/src/img/posters/5.jpg'
    ];
    
    let currentIndex = 0;
    const currentImg = document.getElementById('auth-slideshow-current');
    const nextImg = document.getElementById('auth-slideshow-next');
    
    function nextSlide() {
        const nextIndex = (currentIndex + 1) % posters.length;
        
        // Fade effect
        nextImg.src = posters[nextIndex];
        currentImg.style.opacity = '0';
        nextImg.style.opacity = '1';
        
        setTimeout(() => {
            currentImg.src = posters[nextIndex];
            currentImg.style.opacity = '1';
            nextImg.style.opacity = '0';
            currentIndex = nextIndex;
        }, 1000);
    }
    
    // Tự động chuyển mỗi 4 giây
    window.authSlideshowInterval = setInterval(nextSlide, 4000);
}
```

#### C. Xử Lý Submit Login Form (AJAX)
```javascript
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Ngăn form submit thông thường
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('.btn-submit');
    
    // Disable button, hiển thị loading
    submitBtn.innerHTML = '<span>Đang xử lý...</span>';
    submitBtn.disabled = true;
    
    // Gửi AJAX request
    fetch('/src/controllers/loginControllerAjax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        try {
            const data = JSON.parse(text);
            
            // Khôi phục button
            submitBtn.innerHTML = '<span>Đăng Nhập</span>';
            submitBtn.disabled = false;
            
            if (data.success) {
                showAlert('loginAlert', data.message, 'success');
                // Reload trang sau 1 giây
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showAlert('loginAlert', data.message, 'error');
            }
        } catch (e) {
            console.error('JSON Parse Error:', e);
            console.error('Response:', text);
            showAlert('loginAlert', 'Lỗi xử lý dữ liệu!', 'error');
        }
    })
    .catch(error => {
        submitBtn.innerHTML = '<span>Đăng Nhập</span>';
        submitBtn.disabled = false;
        showAlert('loginAlert', 'Đã xảy ra lỗi kết nối!', 'error');
        console.error('Error:', error);
    });
});
```

**Giải thích:**
- `e.preventDefault()`: Ngăn form reload trang
- `FormData`: Tự động lấy tất cả dữ liệu form
- `fetch()`: Gửi AJAX request
- Parse JSON và hiển thị kết quả
- Reload trang nếu đăng nhập thành công

---

### 🔹 **8. src/styles/header.css** (Style cho Header & Modal)

**Các phần quan trọng:**

#### A. Modal Overlay
```css
.auth-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10000;
}

.auth-modal.active {
    display: flex;
    align-items: center;
    justify-content: center;
}

.auth-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.75); /* Nền đen 75% */
    backdrop-filter: blur(5px);      /* Blur phía sau */
    cursor: pointer;
}
```

#### B. Modal Content (2 cột)
```css
.auth-modal-content {
    position: relative;
    background: white;
    border-radius: 20px;
    width: 90%;
    max-width: 1000px;
    max-height: 90vh;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    z-index: 10001;
}

.auth-modal-container {
    display: flex; /* 2 cột ngang */
}

.auth-left-section {
    flex: 1;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 40px;
}

.auth-right-section {
    flex: 1;
    padding: 40px;
    overflow-y: auto;
}
```

#### C. Slideshow Animation
```css
.slide-img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 1;
    transition: opacity 1s ease-in-out;
}

.slide-img.slide-next {
    opacity: 0; /* Ảnh tiếp theo ẩn dưới */
}
```

#### D. Responsive (Mobile)
```css
@media (max-width: 768px) {
    .auth-modal-container {
        flex-direction: column; /* Đổi thành dọc trên mobile */
    }
    
    .auth-left-section {
        min-height: 250px;
    }
}
```

---

## 🔄 Luồng Hoạt Động

### 1️⃣ **Luồng Đăng Nhập**

```
User click "ĐĂNG NHẬP"
    ↓
openAuthModal('login') được gọi
    ↓
Modal hiện ra với form đăng nhập
    ↓
User nhập email + password
    ↓
Click "Đăng Nhập"
    ↓
JavaScript bắt sự kiện submit
    ↓
preventDefault() - Ngăn reload
    ↓
Gửi AJAX đến loginControllerAjax.php
    ↓
PHP xác thực với database
    ↓
Lưu thông tin vào $_SESSION
    ↓
Trả về JSON {success: true, message: "..."}
    ↓
JavaScript nhận response
    ↓
Hiển thị thông báo thành công
    ↓
Reload trang sau 1 giây
    ↓
Header hiển thị username + nút Logout
```

### 2️⃣ **Luồng Đăng Ký**

```
User click "ĐĂNG KÝ"
    ↓
openAuthModal('register') được gọi
    ↓
Modal hiện ra với form đăng ký
    ↓
User nhập username, email, password
    ↓
Click "Đăng Ký"
    ↓
JavaScript validate password match
    ↓
Gửi AJAX đến registerControllerAjax.php
    ↓
PHP kiểm tra email/username đã tồn tại chưa
    ↓
Hash password và insert vào database
    ↓
Trả về JSON {success: true}
    ↓
JavaScript chuyển sang form Login
    ↓
User đăng nhập với tài khoản vừa tạo
```

### 3️⃣ **Luồng Đăng Xuất**

```
User click "ĐĂNG XUẤT"
    ↓
Redirect đến logoutController.php
    ↓
Xóa tất cả $_SESSION
    ↓
Xóa session cookie
    ↓
session_destroy()
    ↓
Tạo flash message "Đăng xuất thành công"
    ↓
Redirect về index.php
    ↓
Header hiển thị lại "ĐĂNG NHẬP / ĐĂNG KÝ"
```

---

## 🛠️ Cài Đặt và Sử Dụng

### Yêu Cầu Hệ Thống
- XAMPP (PHP 7.4+, MySQL)
- Trình duyệt hiện đại (Chrome, Firefox, Edge)

### Các Bước Cài Đặt

1. **Clone hoặc copy project vào `htdocs`**
```bash
C:\xampp\htdocs\DACS2\
```

2. **Tạo database**
- Mở phpMyAdmin: `http://localhost/phpmyadmin`
- Tạo database tên `dacs2`
- Import file SQL (nếu có) hoặc tạo bảng `user`:

```sql
CREATE TABLE `user` (
    `userID` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `roleID` INT DEFAULT 2,
    `last_login` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

3. **Cấu hình database**
- Mở `src/models/database.php`
- Kiểm tra thông tin kết nối:
```php
$host = 'localhost';
$dbname = 'dacs2';
$username = 'root';
$password = '';
```

4. **Khởi động XAMPP**
- Start Apache
- Start MySQL

5. **Truy cập website**
```
http://localhost/DACS2/index.php
```

---

## 🐛 Xử Lý Lỗi Thường Gặp

### Lỗi 1: "Session cannot be started after headers"
**Nguyên nhân:** Có output (whitespace, echo) trước `session_start()`

**Giải pháp:**
- Kiểm tra file `database.php` không có dòng trống ở đầu
- Đảm bảo `session_start()` là dòng đầu tiên
- Không có BOM (Byte Order Mark) trong file

### Lỗi 2: "Đã xảy ra lỗi" khi đăng nhập
**Nguyên nhân:** JSON response bị lỗi do có output không mong muốn

**Giải pháp:**
- Mở Developer Tools (F12) → Console tab
- Xem lỗi chi tiết
- Kiểm tra file `database.php` không echo gì
- Kiểm tra `loginControllerAjax.php` set đúng `Content-Type`

### Lỗi 3: Modal không hiện ra
**Nguyên nhân:** JavaScript không load hoặc có lỗi

**Giải pháp:**
- Kiểm tra Console có lỗi JS không
- Đảm bảo file `header.js` được load
- Kiểm tra hàm `openAuthModal()` có tồn tại không

---

## 📚 Kiến Thức Cần Có

### PHP
- Session management
- PDO và prepared statements
- Password hashing (`password_hash`, `password_verify`)
- JSON encoding/decoding
- Header manipulation

### JavaScript
- DOM manipulation
- Event handling
- Fetch API (AJAX)
- Promise và async/await
- JSON parse

### CSS
- Flexbox layout
- CSS Grid
- Animations & Transitions
- Media queries (responsive)
- Z-index và positioning

### Database
- MySQL queries
- Table relationships
- Indexes
- CRUD operations

---

## 📞 Liên Hệ

**Dự án:** VKU Cinema  
**Trường:** Đại học VKU  
**Năm học:** 2025-2026  

---

## 📄 License

Dự án học tập - VKU Cinema © 2025
