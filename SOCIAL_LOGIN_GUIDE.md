# Hướng dẫn Setup Đăng nhập Facebook & Google

## 📋 Mục lục
1. [Google OAuth Setup](#google-oauth-setup)
2. [Facebook OAuth Setup](#facebook-oauth-setup)
3. [Cài đặt PHP Libraries](#cài-đặt-php-libraries)
4. [Backend Implementation](#backend-implementation)
5. [Frontend Integration](#frontend-integration)
6. [Testing](#testing)

---

## 🔵 Google OAuth Setup

### Bước 1: Tạo Project trên Google Cloud Console

1. Truy cập: https://console.cloud.google.com/
2. Đăng nhập bằng tài khoản Google
3. Click **Select a project** → **NEW PROJECT**
4. Nhập tên project: `VKU Cinema`
5. Click **Create**

### Bước 2: Enable Google+ API

1. Vào **APIs & Services** → **Library**
2. Tìm kiếm: `Google+ API`
3. Click vào và nhấn **ENABLE**

### Bước 3: Tạo OAuth Credentials

1. Vào **APIs & Services** → **Credentials**
2. Click **CREATE CREDENTIALS** → **OAuth client ID**
3. Nếu chưa có OAuth consent screen:
   - Click **CONFIGURE CONSENT SCREEN**
   - Chọn **External** → **CREATE**
   - Nhập thông tin:
     - App name: `VKU Cinema`
     - User support email: email của bạn
     - Developer contact: email của bạn
   - Click **SAVE AND CONTINUE**
   - Ở phần Scopes, click **ADD OR REMOVE SCOPES**
   - Thêm: `email`, `profile`, `openid`
   - Click **UPDATE** → **SAVE AND CONTINUE**
   - Ở Test users: Thêm email test → **SAVE AND CONTINUE**

4. Quay lại **Credentials** → **CREATE CREDENTIALS** → **OAuth client ID**
5. Chọn Application type: **Web application**
6. Name: `VKU Cinema Web`
7. **Authorized JavaScript origins**:
   ```
   http://localhost
   http://localhost:8000
   http://127.0.0.1
   ```
8. **Authorized redirect URIs**:
   ```
   http://localhost/callback/google
   http://localhost:8000/callback/google
   ```
9. Click **CREATE**
10. **Lưu lại**:
    - Client ID: `123456789-abc...apps.googleusercontent.com`
    - Client Secret: `GOCSPX-abc...`

---

## 🔴 Facebook OAuth Setup

### Bước 1: Tạo App trên Facebook Developers

1. Truy cập: https://developers.facebook.com/
2. Đăng nhập bằng tài khoản Facebook
3. Click **My Apps** → **Create App**
4. Chọn **Consumer** → **Next**
5. Nhập thông tin:
   - App name: `VKU Cinema`
   - App contact email: email của bạn
6. Click **Create App**

### Bước 2: Setup Facebook Login

1. Trong dashboard app, tìm **Facebook Login**
2. Click **Set Up**
3. Chọn **Web** → nhập URL: `http://localhost`
4. Click **Save** → **Continue**

### Bước 3: Cấu hình Facebook Login Settings

1. Vào **Facebook Login** → **Settings** (sidebar)
2. **Valid OAuth Redirect URIs**:
   ```
   http://localhost/callback/facebook
   http://localhost:8000/callback/facebook
   ```
3. Click **Save Changes**

### Bước 4: Lấy App Credentials

1. Vào **Settings** → **Basic**
2. **Lưu lại**:
   - App ID: `1234567890123456`
   - App Secret: Click **Show** → `abc123...`

### Bước 5: Chuyển sang Development Mode

1. Ở góc trên, đảm bảo app đang ở **Development mode**
2. Thêm test users: **Roles** → **Test Users** → **Add**

---

## 📦 Cài đặt PHP Libraries

### Option 1: Dùng Composer (Khuyến nghị)

```bash
# Cài đặt Composer nếu chưa có
# Download từ: https://getcomposer.org/download/

# Install Google Client Library
composer require google/apiclient:"^2.0"

# Install Facebook PHP SDK
composer require facebook/graph-sdk
```

### Option 2: Manual Download (nếu không dùng Composer)

#### Google API Client
1. Download: https://github.com/googleapis/google-api-php-client/releases
2. Extract vào `vendor/google/apiclient/`

#### Facebook PHP SDK
1. Download: https://github.com/facebookarchive/php-graph-sdk/releases
2. Extract vào `vendor/facebook/graph-sdk/`

---

## 🔧 Backend Implementation

### 1. Tạo file config cho Social Login

**File: `config/social_config.php`**

```php
<?php
return [
    'google' => [
        'client_id' => 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com',
        'client_secret' => 'YOUR_GOOGLE_CLIENT_SECRET',
        'redirect_uri' => 'http://localhost/callback/google',
        'scopes' => ['email', 'profile']
    ],
    'facebook' => [
        'app_id' => 'YOUR_FACEBOOK_APP_ID',
        'app_secret' => 'YOUR_FACEBOOK_APP_SECRET',
        'redirect_uri' => 'http://localhost/callback/facebook',
        'graph_api_version' => 'v18.0'
    ]
];
```

**⚠️ QUAN TRỌNG**: Thay thế các giá trị YOUR_... bằng credentials thực tế

### 2. Tạo Google Login Handler

**File: `src/controllers/googleAuthController.php`**

```php
<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../models/database.php';
require_once __DIR__ . '/../models/user_db.php';

$config = require_once __DIR__ . '/../../config/social_config.php';

// Initialize Google Client
$client = new Google_Client();
$client->setClientId($config['google']['client_id']);
$client->setClientSecret($config['google']['client_secret']);
$client->setRedirectUri($config['google']['redirect_uri']);
$client->addScope('email');
$client->addScope('profile');

// Check if returning from Google
if (isset($_GET['code'])) {
    try {
        // Exchange code for token
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        
        if (isset($token['error'])) {
            throw new Exception($token['error_description']);
        }
        
        $client->setAccessToken($token);
        
        // Get user info
        $oauth = new Google_Service_Oauth2($client);
        $userInfo = $oauth->userinfo->get();
        
        $googleId = $userInfo->id;
        $email = $userInfo->email;
        $name = $userInfo->name;
        $picture = $userInfo->picture;
        
        // Check if user exists
        $existingUser = getUserByEmail($email);
        
        if ($existingUser) {
            // User exists, login
            $_SESSION['userID'] = $existingUser['userID'];
            $_SESSION['user'] = $existingUser;
            
            // Update Google ID if not set
            if (empty($existingUser['googleID'])) {
                updateUserGoogleID($existingUser['userID'], $googleId);
            }
        } else {
            // Create new user
            $userData = [
                'username' => $name,
                'email' => $email,
                'googleID' => $googleId,
                'avatar' => $picture,
                'roleID' => 2 // Regular user
            ];
            
            $userID = createSocialUser($userData);
            
            if ($userID) {
                $user = getUserById($userID);
                $_SESSION['userID'] = $userID;
                $_SESSION['user'] = $user;
            }
        }
        
        // Redirect to home
        header('Location: /index.php');
        exit();
        
    } catch (Exception $e) {
        $_SESSION['login_error'] = 'Lỗi đăng nhập Google: ' . $e->getMessage();
        header('Location: /index.php');
        exit();
    }
} else {
    // Redirect to Google OAuth
    $authUrl = $client->createAuthUrl();
    header('Location: ' . $authUrl);
    exit();
}
```

### 3. Tạo Facebook Login Handler

**File: `src/controllers/facebookAuthController.php`**

```php
<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../models/database.php';
require_once __DIR__ . '/../models/user_db.php';

$config = require_once __DIR__ . '/../../config/social_config.php';

// Initialize Facebook SDK
$fb = new \Facebook\Facebook([
    'app_id' => $config['facebook']['app_id'],
    'app_secret' => $config['facebook']['app_secret'],
    'default_graph_version' => $config['facebook']['graph_api_version'],
]);

$helper = $fb->getRedirectLoginHelper();

// Check if returning from Facebook
if (isset($_GET['code'])) {
    try {
        // Get access token
        $accessToken = $helper->getAccessToken();
        
        if (!$accessToken) {
            throw new Exception('Failed to get access token');
        }
        
        // Get user info
        $response = $fb->get('/me?fields=id,name,email,picture', $accessToken);
        $userInfo = $response->getGraphUser();
        
        $facebookId = $userInfo['id'];
        $email = $userInfo['email'];
        $name = $userInfo['name'];
        $picture = $userInfo['picture']['url'] ?? '';
        
        // Check if user exists
        $existingUser = getUserByEmail($email);
        
        if ($existingUser) {
            // User exists, login
            $_SESSION['userID'] = $existingUser['userID'];
            $_SESSION['user'] = $existingUser;
            
            // Update Facebook ID if not set
            if (empty($existingUser['facebookID'])) {
                updateUserFacebookID($existingUser['userID'], $facebookId);
            }
        } else {
            // Create new user
            $userData = [
                'username' => $name,
                'email' => $email,
                'facebookID' => $facebookId,
                'avatar' => $picture,
                'roleID' => 2
            ];
            
            $userID = createSocialUser($userData);
            
            if ($userID) {
                $user = getUserById($userID);
                $_SESSION['userID'] = $userID;
                $_SESSION['user'] = $user;
            }
        }
        
        // Redirect to home
        header('Location: /index.php');
        exit();
        
    } catch (Exception $e) {
        $_SESSION['login_error'] = 'Lỗi đăng nhập Facebook: ' . $e->getMessage();
        header('Location: /index.php');
        exit();
    }
} else {
    // Redirect to Facebook OAuth
    $permissions = ['email', 'public_profile'];
    $loginUrl = $helper->getLoginUrl($config['facebook']['redirect_uri'], $permissions);
    header('Location: ' . $loginUrl);
    exit();
}
```

### 4. Cập nhật user_db.php

**Thêm vào file: `src/models/user_db.php`**

```php
<?php
// ... existing code ...

// Get user by email
function getUserByEmail($email) {
    global $db;
    $sql = "SELECT u.*, r.roleName 
            FROM `user` u 
            JOIN roles r ON u.roleID = r.roleID 
            WHERE u.email = :email";
    $stmt = $db->prepare($sql);
    $stmt->execute(['email' => $email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Create user from social login
function createSocialUser($userData) {
    global $db;
    
    $sql = "INSERT INTO `user` (username, email, googleID, facebookID, avatar, roleID, createdAt) 
            VALUES (:username, :email, :googleID, :facebookID, :avatar, :roleID, NOW())";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([
        'username' => $userData['username'],
        'email' => $userData['email'],
        'googleID' => $userData['googleID'] ?? null,
        'facebookID' => $userData['facebookID'] ?? null,
        'avatar' => $userData['avatar'] ?? null,
        'roleID' => $userData['roleID']
    ]);
    
    return $db->lastInsertId();
}

// Update Google ID
function updateUserGoogleID($userID, $googleID) {
    global $db;
    $sql = "UPDATE `user` SET googleID = :googleID WHERE userID = :userID";
    $stmt = $db->prepare($sql);
    return $stmt->execute(['googleID' => $googleID, 'userID' => $userID]);
}

// Update Facebook ID
function updateUserFacebookID($userID, $facebookID) {
    global $db;
    $sql = "UPDATE `user` SET facebookID = :facebookID WHERE userID = :userID";
    $stmt = $db->prepare($sql);
    return $stmt->execute(['facebookID' => $facebookID, 'userID' => $userID]);
}

// Get user by ID
function getUserById($userID) {
    global $db;
    $sql = "SELECT u.*, r.roleName 
            FROM `user` u 
            JOIN roles r ON u.roleID = r.roleID 
            WHERE u.userID = :userID";
    $stmt = $db->prepare($sql);
    $stmt->execute(['userID' => $userID]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
```

### 5. Cập nhật Database Schema

**Chạy SQL này để thêm các cột cần thiết:**

```sql
-- Thêm cột cho Google và Facebook ID
ALTER TABLE `user` 
ADD COLUMN `googleID` VARCHAR(255) NULL AFTER `password`,
ADD COLUMN `facebookID` VARCHAR(255) NULL AFTER `googleID`,
ADD COLUMN `avatar` VARCHAR(500) NULL AFTER `facebookID`,
ADD UNIQUE INDEX `idx_googleID` (`googleID`),
ADD UNIQUE INDEX `idx_facebookID` (`facebookID`);

-- Cho phép password NULL (cho user đăng nhập bằng social)
ALTER TABLE `user` 
MODIFY COLUMN `password` VARCHAR(255) NULL;
```

### 6. Cập nhật .htaccess hoặc routing

**Thêm routes cho callback:**

```apache
# File: .htaccess (nếu dùng Apache)
RewriteEngine On

# Google OAuth callback
RewriteRule ^callback/google$ src/controllers/googleAuthController.php [L]

# Facebook OAuth callback
RewriteRule ^callback/facebook$ src/controllers/facebookAuthController.php [L]
```

**HOẶC** trong `index.php`:

```php
<?php
// ... existing code ...

// Handle OAuth callbacks
if (isset($_GET['callback'])) {
    switch ($_GET['callback']) {
        case 'google':
            require_once 'src/controllers/googleAuthController.php';
            exit();
        case 'facebook':
            require_once 'src/controllers/facebookAuthController.php';
            exit();
    }
}

// ... rest of code ...
```

Và URL callback sẽ là:
- Google: `http://localhost/index.php?callback=google`
- Facebook: `http://localhost/index.php?callback=facebook`

---

## 🎨 Frontend Integration

### Cập nhật auth.js

**File: `src/js/auth.js`**

Thêm các function sau:

```javascript
// Google Login
function loginWithGoogle() {
    window.location.href = '/src/controllers/googleAuthController.php';
}

// Facebook Login
function loginWithFacebook() {
    window.location.href = '/src/controllers/facebookAuthController.php';
}
```

### Cập nhật auth_modal.php

**Thay đổi các button social login:**

```html
<div class="social-login">
    <button type="button" class="btn-social btn-google" onclick="loginWithGoogle()">
        <i class="fab fa-google"></i>
        Google
    </button>
    <button type="button" class="btn-social btn-facebook" onclick="loginWithFacebook()">
        <i class="fab fa-facebook-f"></i>
        Facebook
    </button>
</div>
```

---

## 🧪 Testing

### Test Google Login

1. Mở website: `http://localhost`
2. Click nút **Login with Google**
3. Chọn tài khoản Google test
4. Cho phép quyền truy cập
5. Kiểm tra:
   - ✅ Redirect về trang chủ
   - ✅ Hiển thị tên user đã đăng nhập
   - ✅ Database có user mới với googleID

### Test Facebook Login

1. Click nút **Login with Facebook**
2. Đăng nhập bằng tài khoản Facebook test
3. Cho phép quyền truy cập
4. Kiểm tra tương tự Google

### Debug Common Issues

#### Google Error: "redirect_uri_mismatch"
- Kiểm tra redirect URI trong Google Console khớp với code
- Đảm bảo không có trailing slash

#### Facebook Error: "URL Blocked"
- Thêm URL vào Valid OAuth Redirect URIs
- Đảm bảo app đang ở Development mode

#### Database Error
- Kiểm tra đã chạy ALTER TABLE chưa
- Kiểm tra kết nối database

---

## 📝 Security Best Practices

### 1. Bảo mật Credentials

**Tạo file: `.env`**

```env
# Google OAuth
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret

# Facebook OAuth
FACEBOOK_APP_ID=your_app_id
FACEBOOK_APP_SECRET=your_app_secret
```

**Cập nhật `social_config.php`:**

```php
<?php
// Load .env file
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

return [
    'google' => [
        'client_id' => $_ENV['GOOGLE_CLIENT_ID'] ?? '',
        'client_secret' => $_ENV['GOOGLE_CLIENT_SECRET'] ?? '',
        // ...
    ],
    'facebook' => [
        'app_id' => $_ENV['FACEBOOK_APP_ID'] ?? '',
        'app_secret' => $_ENV['FACEBOOK_APP_SECRET'] ?? '',
        // ...
    ]
];
```

**Thêm `.env` vào `.gitignore`:**

```
.env
config/social_config.php
```

### 2. Validate Email Domain

```php
function validateEmail($email) {
    // Chỉ cho phép email @vku.udn.vn
    if (!str_ends_with($email, '@vku.udn.vn')) {
        throw new Exception('Chỉ chấp nhận email @vku.udn.vn');
    }
    return true;
}
```

### 3. CSRF Protection

Thêm token vào session và validate khi callback.

---

## 🚀 Production Deployment

### Trước khi deploy:

1. **Cập nhật Redirect URIs** trong Google Console và Facebook App:
   ```
   https://yourdomain.com/callback/google
   https://yourdomain.com/callback/facebook
   ```

2. **Chuyển Facebook App sang Live mode**:
   - Vào Settings → Basic
   - Điền đầy đủ thông tin
   - Add Privacy Policy URL
   - Submit for review nếu cần permissions đặc biệt

3. **Enable HTTPS**:
   - Bắt buộc cho production
   - Dùng Let's Encrypt miễn phí

4. **Rate Limiting**:
   - Giới hạn số lần login attempt
   - Implement cooldown

---

## 📚 Resources

### Documentation
- [Google OAuth 2.0](https://developers.google.com/identity/protocols/oauth2)
- [Facebook Login](https://developers.facebook.com/docs/facebook-login)
- [Google API PHP Client](https://github.com/googleapis/google-api-php-client)
- [Facebook PHP SDK](https://developers.facebook.com/docs/php/gettingstarted)

### Video Tutorials
- Google OAuth: https://www.youtube.com/watch?v=...
- Facebook Login: https://www.youtube.com/watch?v=...

---

## ❓ Troubleshooting

### Issue: "This app isn't verified"
**Solution**: Trong development, click "Advanced" → "Go to [app name] (unsafe)"

### Issue: "App Not Setup"
**Solution**: Kiểm tra Facebook app đang ở Development mode và đã add test users

### Issue: "Invalid redirect URI"
**Solution**: 
1. Kiểm tra config khớp với Google Console
2. Không có dấu / ở cuối URI
3. Chính xác http/https

### Issue: "Email already exists"
**Solution**: User đã đăng ký bằng email thường, cần merge accounts hoặc show message yêu cầu đăng nhập bằng password

---

**Cập nhật:** 24/12/2025  
**Version:** 1.0  
**Project:** VKU Cinema
