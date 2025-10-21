# 🕐 Countdown Timer Feature - Tính năng đếm ngược phát hành phim

## 📋 Mô tả
Tính năng countdown timer hiển thị thời gian còn lại đến ngày phát hành cho các phim sắp chiếu. Timer tự động cập nhật mỗi giây và có hiệu ứng visual đẹp mắt.

## ✨ Tính năng

### 1. **Hiển thị động theo thời gian còn lại:**
- **> 1 ngày:** Hiển thị "X ngày Y giờ Z phút"
- **< 1 ngày:** Hiển thị "X giờ Y phút Z giây" (có animation blink cho giây)
- **Đã phát hành:** Hiển thị "Đã phát hành" với màu vàng

### 2. **Hiệu ứng Visual:**
- ✅ Gradient background (xanh lá cây neon)
- ✅ Border với glow effect
- ✅ Pulse animation cho toàn bộ box
- ✅ Icon đồng hồ quay (tick animation)
- ✅ Số giây nhấp nháy khi countdown gần hết
- ✅ Text shadow với màu neon

### 3. **Responsive Design:**
- ✅ Tự động thu nhỏ font trên mobile
- ✅ Flexible layout cho các số liệu
- ✅ Dễ đọc trên mọi kích thước màn hình

## 🔧 Cách sử dụng

### Backend (PHP):

```php
// Lấy thông tin countdown
$countdown = get_countdown_to_release($movie['releaseDate']);

// Kiểm tra phim có sắp chiếu không
if ($countdown !== null) {
    echo "Còn {$countdown['days']} ngày";
}

// Hoặc lấy text ngắn gọn
echo get_countdown_text($movie['releaseDate']); // "Còn 5 ngày"
```

### Frontend (HTML):

```html
<div class="countdown-timer" 
     data-release-date="2025-12-25 00:00:00"
     data-movie-id="123">
    <i class="fas fa-clock"></i>
    <span class="countdown-text">
        <span class="days">5</span> ngày 
        <span class="hours">10</span> giờ 
        <span class="minutes">30</span> phút
    </span>
</div>
```

### JavaScript:

```javascript
// Hàm updateCountdowns() tự động chạy mỗi giây
setInterval(updateCountdowns, 1000);
```

## 📦 Files liên quan

### 1. **movie_db.php** - Backend Functions
- `get_countdown_to_release($releaseDate)` - Tính toán countdown chi tiết
- `get_countdown_text($releaseDate)` - Text ngắn gọn "Còn X ngày"

### 2. **home.php** - View Template
- Hiển thị countdown cho phim sắp chiếu
- Conditional rendering (chỉ hiện khi có releaseDate và chưa phát hành)

### 3. **home.css** - Styles
- `.countdown-timer` - Container với gradient & glow
- Animations: `pulse-glow`, `tick`, `blink`

### 4. **home.js** - Interactive Logic
- `updateCountdowns()` - Cập nhật mỗi giây
- Logic chuyển đổi định dạng theo thời gian còn lại

## 🎨 Color Scheme

```css
/* Countdown (chưa phát hành) */
Background: linear-gradient(135deg, rgba(0, 255, 136, 0.1), rgba(0, 200, 255, 0.1))
Border: rgba(0, 255, 136, 0.3)
Text: #00ff88 (xanh lá neon)
Icon: #00c8ff (xanh dương neon)
Seconds: #ff4757 (đỏ nhấp nháy)

/* Đã phát hành */
Background: linear-gradient(135deg, rgba(255, 215, 0, 0.1), rgba(255, 165, 0, 0.1))
Border: rgba(255, 215, 0, 0.3)
Text: #ffd700 (vàng gold)
```

## 📊 Ví dụ Output

### Trường hợp 1: Còn nhiều thời gian
```
🕐 5 ngày 10 giờ 30 phút
```

### Trường hợp 2: Sắp chiếu (< 1 ngày)
```
🕐 10 giờ 30 phút 45 giây
```

### Trường hợp 3: Gần hết (< 1 giờ)
```
🕐 30 phút 45 giây
```

### Trường hợp 4: Đã phát hành
```
✅ Đã phát hành
```

## 🚀 Performance

- **Tối ưu:** Chỉ update DOM khi cần thiết
- **Lightweight:** Sử dụng vanilla JavaScript (không cần thư viện)
- **Efficient:** setInterval 1000ms (1 giây) - không gây lag

## 🔮 Tính năng tương lai có thể thêm

1. **Notification:** Thông báo khi phim sắp phát hành (còn 1 ngày, 1 giờ)
2. **Share countdown:** Chia sẻ countdown lên social media
3. **Add to calendar:** Thêm ngày phát hành vào Google Calendar
4. **Email reminder:** Gửi email nhắc nhở trước ngày phát hành
5. **Push notification:** Thông báo đẩy khi phim ra mắt

## 📝 Notes

- Countdown chỉ hiển thị cho phim có `releaseDate` trong tương lai
- Phim đã phát hành sẽ hiển thị icon ✅ và text "Đã phát hành"
- Timezone: Sử dụng server timezone (cần config nếu deploy production)
- Update interval: 1 giây (có thể tăng lên 5-10 giây để giảm CPU nếu cần)

## 🐛 Troubleshooting

**Q: Countdown không cập nhật?**
A: Kiểm tra console.log, đảm bảo JavaScript được load và `data-release-date` có format đúng.

**Q: Hiển thị sai timezone?**
A: Cần set timezone trong PHP: `date_default_timezone_set('Asia/Ho_Chi_Minh');`

**Q: Animation bị lag?**
A: Giảm số lượng countdown hiển thị cùng lúc hoặc tăng interval lên 5-10 giây.

---

Created: October 20, 2025
Version: 1.0
Author: VKU Cinema Development Team
