# 🎬 YouTube Trailer Modal Feature - Tính năng xem Trailer YouTube

## 📋 Mô tả
Tính năng cho phép người dùng xem trailer phim trực tiếp trên website thông qua modal popup với YouTube iframe embed API. Không cần chuyển trang hay mở tab mới.

## ✨ Tính năng chính

### 1. **YouTube URL Parsing - Hỗ trợ nhiều format URL:**
- ✅ `https://www.youtube.com/watch?v=VIDEO_ID`
- ✅ `https://youtu.be/VIDEO_ID`
- ✅ `https://www.youtube.com/embed/VIDEO_ID`
- ✅ `https://www.youtube.com/v/VIDEO_ID`
- ✅ `https://www.youtube.com/watch?feature=...&v=VIDEO_ID`
- ✅ Chỉ cần video ID (ví dụ: `dQw4w9WgXcQ`)

### 2. **Modal Popup đẹp mắt:**
- ✅ Full-screen overlay với blur effect
- ✅ Responsive design (mobile, tablet, desktop)
- ✅ Smooth animations (fade in, slide up)
- ✅ Close button với rotate animation
- ✅ Click outside để đóng
- ✅ Nhấn ESC để đóng

### 3. **YouTube Player Features:**
- ✅ Autoplay khi mở modal
- ✅ Stop video khi đóng modal
- ✅ 16:9 aspect ratio responsive
- ✅ Full YouTube player controls
- ✅ Allowfullscreen mode

### 4. **UX Improvements:**
- ✅ Prevent body scroll khi modal mở
- ✅ Tên phim hiển thị trong title
- ✅ Loading smooth không bị giật
- ✅ Console logging để debug

## 🔧 Cấu trúc Code

### Backend Functions (PHP)

#### `movie_db.php` - Helper Functions:

```php
/**
 * Lấy YouTube video ID từ URL
 * Hỗ trợ nhiều format URL khác nhau
 */
function get_youtube_video_id($url)

/**
 * Tạo YouTube embed URL từ video ID hoặc URL
 * Return: https://www.youtube.com/embed/VIDEO_ID?autoplay=1&rel=0
 */
function get_youtube_embed_url($urlOrId)
```

### Frontend Implementation

#### HTML Structure (home.php):

```html
<!-- Trigger Button (Hero Banner) -->
<button class="btn-trailer" onclick="openTrailer('videoId', 'Movie Title')">
    <i class="fas fa-play"></i> Trailer
</button>

<!-- Trigger Button (Movie Card) -->
<button class="btn-play" onclick="openTrailer('videoId', 'Movie Title')">
    <i class="fas fa-play"></i>
</button>

<!-- Modal Structure -->
<div id="trailerModal" class="trailer-modal">
    <div class="trailer-modal-overlay" onclick="closeTrailer()"></div>
    <div class="trailer-modal-content">
        <button class="trailer-close-btn" onclick="closeTrailer()">
            <i class="fas fa-times"></i>
        </button>
        <h2 id="trailerTitle" class="trailer-title">Trailer</h2>
        <div class="trailer-video-container">
            <iframe id="trailerIframe"></iframe>
        </div>
    </div>
</div>
```

#### JavaScript Functions (home.js):

```javascript
/**
 * Mở modal trailer
 * @param {string} videoId - YouTube video ID
 * @param {string} title - Tên phim
 */
function openTrailer(videoId, title) {
    // Set iframe src với autoplay
    // Show modal
    // Disable body scroll
}

/**
 * Đóng modal trailer
 */
function closeTrailer() {
    // Hide modal
    // Stop video by clearing iframe src
    // Restore body scroll
}

// Event listener cho ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeTrailer();
});
```

#### CSS Styling (home.css):

```css
/* Modal Container */
.trailer-modal {
    position: fixed;
    z-index: 9999;
    display: none; /* Hidden by default */
}

.trailer-modal.active {
    display: flex; /* Show when active */
}

/* Modal Overlay - Dark background */
.trailer-modal-overlay {
    background: rgba(0, 0, 0, 0.95);
    backdrop-filter: blur(10px);
}

/* Modal Content - Center card */
.trailer-modal-content {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    border-radius: 20px;
    padding: 30px;
}

/* Video Container - 16:9 ratio */
.trailer-video-container {
    padding-bottom: 56.25%; /* 16:9 */
    position: relative;
}

.trailer-video-container iframe {
    position: absolute;
    width: 100%;
    height: 100%;
}
```

## 📦 Files Modified

### 1. **src/models/movie_db.php**
- ✅ Added `get_youtube_video_id($url)` function
- ✅ Added `get_youtube_embed_url($urlOrId)` function

### 2. **src/views/home.php**
- ✅ Changed trailer links to button with `onclick`
- ✅ Added YouTube modal HTML at bottom
- ✅ Extract video ID in PHP before rendering

### 3. **src/styles/home.css**
- ✅ Added `.trailer-modal` styles
- ✅ Added `.trailer-modal-overlay` styles
- ✅ Added `.trailer-modal-content` styles
- ✅ Added responsive breakpoints
- ✅ Added animations (`fadeIn`, `slideUp`)

### 4. **src/js/home.js**
- ✅ Added `openTrailer()` function
- ✅ Added `closeTrailer()` function
- ✅ Added ESC key listener
- ✅ Exposed functions to global scope

## 🎨 Design Specifications

### Colors:
- **Modal Overlay:** `rgba(0, 0, 0, 0.95)` với blur
- **Modal Background:** Gradient `#1a1a2e` → `#16213e`
- **Close Button:** Gradient `#667eea` → `#764ba2`
- **Title:** Gradient text `#667eea` → `#764ba2`
- **Trailer Button:** Gradient `#f093fb` → `#f5576c`

### Animations:
```css
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from { transform: translateY(50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
```

### Responsive Breakpoints:
- **Desktop:** Modal width 90%, max 1200px
- **Tablet (768px):** Modal width 95%, smaller padding
- **Mobile (480px):** Modal width 98%, minimal padding

## 📊 Usage Examples

### Ví dụ 1: Lưu URL YouTube vào database

```sql
-- Format 1: Full YouTube URL
UPDATE Movie SET trailerURL = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' WHERE movieID = 1;

-- Format 2: Short URL
UPDATE Movie SET trailerURL = 'https://youtu.be/dQw4w9WgXcQ' WHERE movieID = 2;

-- Format 3: Chỉ video ID
UPDATE Movie SET trailerURL = 'dQw4w9WgXcQ' WHERE movieID = 3;
```

### Ví dụ 2: Sử dụng trong PHP

```php
<?php
// Lấy video ID từ URL
$videoId = get_youtube_video_id($movie['trailerURL']);

// Kiểm tra có video ID hợp lệ không
if ($videoId): ?>
    <button onclick="openTrailer('<?php echo $videoId; ?>', '<?php echo $movie['title']; ?>')">
        Xem Trailer
    </button>
<?php endif; ?>
```

### Ví dụ 3: Gọi từ JavaScript

```javascript
// Mở trailer trực tiếp
openTrailer('dQw4w9WgXcQ', 'Never Gonna Give You Up');

// Đóng trailer
closeTrailer();
```

## 🚀 How to Use

### Bước 1: Thêm URL trailer vào database
```sql
UPDATE Movie 
SET trailerURL = 'https://www.youtube.com/watch?v=VIDEO_ID' 
WHERE movieID = 1;
```

### Bước 2: Load trang home
- Nút "Trailer" sẽ tự động xuất hiện nếu có `trailerURL`
- Click vào nút để mở modal

### Bước 3: Xem trailer
- Video tự động play khi modal mở
- Click nút X, click bên ngoài, hoặc nhấn ESC để đóng
- Video tự động dừng khi đóng modal

## 🎯 Benefits

✅ **Better UX:** Không cần rời khỏi trang  
✅ **Professional:** Modal đẹp với animations  
✅ **Flexible:** Hỗ trợ nhiều format URL YouTube  
✅ **SEO Friendly:** Sử dụng YouTube embed chính thức  
✅ **Performance:** Chỉ load iframe khi cần  
✅ **Mobile Optimized:** Responsive trên mọi thiết bị  
✅ **Accessible:** Keyboard support (ESC to close)  

## 🔮 Future Enhancements

1. **Playlist Support:** Cho phép xem nhiều trailer liên tiếp
2. **Quality Selection:** Chọn độ phân giải video
3. **Subtitle Support:** Tích hợp phụ đề
4. **Share Feature:** Chia sẻ trailer lên social media
5. **Watch History:** Lưu lịch sử xem trailer
6. **Related Videos:** Hiển thị trailer phim tương tự
7. **Analytics:** Theo dõi số lượt xem trailer
8. **Thumbnail Preview:** Hiển thị thumbnail YouTube

## 🐛 Troubleshooting

**Q: Modal không mở?**
A: Kiểm tra console log, đảm bảo video ID hợp lệ và functions được load.

**Q: Video không autoplay?**
A: Một số browser chặn autoplay. User cần click play button trên video.

**Q: Video không stop khi đóng modal?**
A: Function `closeTrailer()` clear iframe src, đảm bảo được gọi đúng cách.

**Q: URL không được parse đúng?**
A: Kiểm tra format URL, test function `get_youtube_video_id()` với URL cụ thể.

**Q: Modal không responsive?**
A: Kiểm tra CSS media queries và viewport meta tag.

## 📝 Notes

- **YouTube API Terms:** Tuân thủ YouTube Terms of Service khi embed
- **HTTPS Required:** Nên dùng HTTPS cho website để tránh mixed content
- **Privacy:** Sử dụng `youtube-nocookie.com` nếu cần privacy mode
- **Performance:** iframe chỉ load khi modal mở, tối ưu tốc độ trang
- **Browser Support:** Hoạt động trên tất cả modern browsers

## 🎬 Demo URLs for Testing

```
https://www.youtube.com/watch?v=dQw4w9WgXcQ (Rick Astley)
https://youtu.be/9bZkp7q19f0 (Gangnam Style)
https://www.youtube.com/watch?v=kJQP7kiw5Fk (Despacito)
```

---

**Created:** October 20, 2025  
**Version:** 1.0  
**Author:** VKU Cinema Development Team  
**License:** Internal Use Only
