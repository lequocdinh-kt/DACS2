# 📝 CHANGELOG - Horizontal Poster Feature

## 🎯 Tóm tắt thay đổi
Đã thêm hỗ trợ poster ngang (horizontal) cho phần hero banner trên trang chủ.

## 📅 Ngày: 21/10/2025

## 🔧 Các file đã được cập nhật:

### 1. Database Schema
- **File SQL**: `add_horizontal_poster.sql`
- **Thay đổi**: Thêm cột `posterHorizontalURL VARCHAR(500)` vào bảng `Movie`

### 2. Models - movie_db.php
**Đã cập nhật:**

#### a. Function `create_movie($data)`
```php
// Thêm support cho posterHorizontalURL
$stmt->bindValue(':posterHorizontalURL', $data['posterHorizontalURL'] ?? null);
```

#### b. Function `update_movie($movieID, $data)`
```php
// Thêm posterHorizontalURL vào UPDATE query
posterHorizontalURL = :posterHorizontalURL,
```

#### c. Function mới: `update_movie_poster_horizontal($movieID, $posterHorizontalURL)`
```php
// Function riêng để update poster ngang
function update_movie_poster_horizontal($movieID, $posterHorizontalURL) {
    global $db;
    $sql = 'UPDATE Movie SET posterHorizontalURL = :posterHorizontalURL WHERE movieID = :movieID';
    ...
}
```

### 3. Views - home.php
**Banner Section:**
```php
// Ưu tiên dùng posterHorizontalURL, fallback về posterURL
<img src="<?php echo htmlspecialchars(
    !empty($movie['posterHorizontalURL']) 
        ? $movie['posterHorizontalURL'] 
        : $movie['posterURL']
); ?>" ...>
```

### 4. Cấu trúc thư mục
**Đã tạo:**
```
src/img/moviesHorizontal/
  └── README.md (hướng dẫn sử dụng)
```

### 5. Documentation
**Đã tạo:**
- `POSTER_HORIZONTAL_FEATURE.md` - Hướng dẫn chi tiết
- `add_horizontal_poster.sql` - Script update database
- `src/img/moviesHorizontal/README.md` - Quy cách ảnh

## 🎨 Quy cách sử dụng

### Ảnh Vertical (posterURL)
- Tỷ lệ: 2:3
- Dùng cho: Movie cards, danh sách phim
- Thư mục: `src/img/moviesVertical/`

### Ảnh Horizontal (posterHorizontalURL) - MỚI
- Tỷ lệ: 16:9
- Dùng cho: Hero banner, slider
- Thư mục: `src/img/moviesHorizontal/`

## 📋 Các bước thực hiện

### Bước 1: Chạy SQL Script
```bash
mysql -u username -p database_name < add_horizontal_poster.sql
```
Hoặc import trong phpMyAdmin/MySQL Workbench

### Bước 2: Upload ảnh ngang
- Chuẩn bị ảnh tỷ lệ 16:9 (1920x1080px khuyến nghị)
- Upload vào: `src/img/moviesHorizontal/`

### Bước 3: Cập nhật database
```sql
UPDATE Movie 
SET posterHorizontalURL = '/src/img/moviesHorizontal/movie1.jpg' 
WHERE movieID = 1;
```

### Bước 4: Kiểm tra
- Truy cập trang chủ
- Banner sẽ hiển thị ảnh ngang nếu có
- Nếu không có ảnh ngang, sẽ fallback về ảnh dọc

## ✅ Lợi ích

1. **UX tốt hơn**: Ảnh 16:9 phù hợp với banner hơn ảnh 2:3
2. **Linh hoạt**: Có thể dùng ảnh dọc hoặc ngang tùy vị trí
3. **Tương thích ngược**: Phim cũ không có ảnh ngang vẫn hoạt động bình thường
4. **SEO**: Ảnh đúng tỷ lệ giúp trang load nhanh và đẹp hơn

## 🔄 Rollback (nếu cần)

```sql
ALTER TABLE Movie DROP COLUMN posterHorizontalURL;
```

Và revert các thay đổi trong code về commit trước đó.

## 📞 Support

Nếu có vấn đề, tham khảo file `POSTER_HORIZONTAL_FEATURE.md` để biết chi tiết.

---
**Developed by**: VKU Cinema Team
**Date**: October 21, 2025
