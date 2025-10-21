# 🖼️ Tính năng Poster Ngang (Horizontal Poster)

## 📋 Mô tả
Tính năng này cho phép upload và hiển thị ảnh ngang cho phần banner/hero section trên trang chủ. Ảnh ngang sẽ có tỷ lệ phù hợp hơn cho banner (16:9) so với poster dọc (2:3).

## 🗄️ Database Schema

### 1. Thêm trường mới vào bảng Movie

```sql
ALTER TABLE Movie
ADD COLUMN posterHorizontalURL VARCHAR(500);
```

### 2. Mô tả trường
- **posterURL**: Ảnh dọc (vertical) - tỷ lệ 2:3 - dùng cho danh sách phim
- **posterHorizontalURL**: Ảnh ngang (horizontal) - tỷ lệ 16:9 - dùng cho banner

## 🎯 Cách sử dụng

### 1. Thêm phim mới
```php
$movieData = [
    'title' => 'Tên phim',
    'genre' => 'Hành động',
    'duration' => 120,
    'description' => 'Mô tả phim',
    'rating' => 8.5,
    'movieStatus' => 'now_showing',
    'posterURL' => '/src/img/moviesVertical/movie1.jpg',  // Ảnh dọc
    'posterHorizontalURL' => '/src/img/moviesHorizontal/movie1.jpg',  // Ảnh ngang
    'trailerURL' => 'https://www.youtube.com/watch?v=xxxxx',
    'author' => 'Đạo diễn',
    'releaseDate' => '2025-01-01'
];

$movieID = create_movie($movieData);
```

### 2. Cập nhật poster ngang cho phim đã có
```php
// Cập nhật trong hàm update_movie
$updateData = [
    'title' => 'Tên phim',
    'genre' => 'Hành động',
    // ... các trường khác
    'posterURL' => '/src/img/moviesVertical/movie1.jpg',
    'posterHorizontalURL' => '/src/img/moviesHorizontal/movie1.jpg'  // Thêm trường này
];

update_movie($movieID, $updateData);

// Hoặc cập nhật riêng poster ngang
update_movie_poster_horizontal($movieID, '/src/img/moviesHorizontal/movie1.jpg');
```

### 3. Cập nhật hàng loạt (SQL)
```sql
-- Cập nhật poster ngang cho phim có ID = 1
UPDATE Movie 
SET posterHorizontalURL = '/src/img/moviesHorizontal/movie1.jpg' 
WHERE movieID = 1;

-- Cập nhật nhiều phim
UPDATE Movie 
SET posterHorizontalURL = CONCAT('/src/img/moviesHorizontal/', movieID, '.jpg')
WHERE movieStatus = 'now_showing';
```

## 📁 Cấu trúc thư mục

```
src/
  img/
    moviesVertical/        # Ảnh poster dọc (2:3)
      movie1.jpg
      movie2.jpg
    moviesHorizontal/      # Ảnh poster ngang (16:9)
      movie1.jpg
      movie2.jpg
    posters/               # Ảnh khác
```

## 🎨 Kích thước ảnh khuyến nghị

### Poster Vertical (posterURL)
- **Tỷ lệ**: 2:3 (ví dụ: 800x1200px)
- **Dùng cho**: 
  - Danh sách phim
  - Movie cards
  - Phim sắp chiếu

### Poster Horizontal (posterHorizontalURL)
- **Tỷ lệ**: 16:9 (ví dụ: 1920x1080px)
- **Dùng cho**: 
  - Hero banner
  - Slider trang chủ

## 🔄 Logic hiển thị trong code

### Trong home.php
```php
<!-- Banner sử dụng posterHorizontalURL, fallback về posterURL -->
<img src="<?php echo htmlspecialchars(
    !empty($movie['posterHorizontalURL']) 
        ? $movie['posterHorizontalURL'] 
        : $movie['posterURL']
); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>">
```

### Ưu tiên:
1. Nếu có `posterHorizontalURL` → dùng ảnh ngang
2. Nếu không có → fallback về `posterURL` (ảnh dọc)

## 📝 Các hàm PHP đã được cập nhật

### movie_db.php

1. **create_movie($data)** 
   - Đã thêm support cho `posterHorizontalURL`
   
2. **update_movie($movieID, $data)**
   - Đã thêm support cho `posterHorizontalURL`
   
3. **update_movie_poster_horizontal($movieID, $posterHorizontalURL)** *(MỚI)*
   - Cập nhật riêng poster ngang

## 🎬 Ví dụ sử dụng

### Ví dụ 1: Thêm phim mới với cả 2 loại poster
```php
<?php
require_once 'src/models/movie_db.php';

$newMovie = [
    'title' => 'Avengers: Endgame',
    'genre' => 'Hành động, Khoa học viễn tưởng',
    'duration' => 181,
    'description' => 'Các siêu anh hùng hợp lực để đánh bại Thanos...',
    'rating' => 8.9,
    'movieStatus' => 'now_showing',
    'posterURL' => '/src/img/moviesVertical/avengers.jpg',
    'posterHorizontalURL' => '/src/img/moviesHorizontal/avengers.jpg',
    'trailerURL' => 'https://www.youtube.com/watch?v=TcMBFSGVi1c',
    'author' => 'Anthony Russo, Joe Russo',
    'releaseDate' => '2025-04-26'
];

$movieID = create_movie($newMovie);
echo "Đã thêm phim với ID: " . $movieID;
?>
```

### Ví dụ 2: Cập nhật poster ngang cho phim đã có
```php
<?php
require_once 'src/models/movie_db.php';

// Cập nhật cho phim có ID = 5
update_movie_poster_horizontal(5, '/src/img/moviesHorizontal/spider-man.jpg');

echo "Đã cập nhật poster ngang cho phim!";
?>
```

### Ví dụ 3: Script cập nhật hàng loạt
```sql
-- Cập nhật tất cả phim đang chiếu
UPDATE Movie 
SET posterHorizontalURL = CASE 
    WHEN title LIKE '%Spider-Man%' THEN '/src/img/moviesHorizontal/spider-man.jpg'
    WHEN title LIKE '%Avengers%' THEN '/src/img/moviesHorizontal/avengers.jpg'
    WHEN title LIKE '%Batman%' THEN '/src/img/moviesHorizontal/batman.jpg'
    ELSE NULL
END
WHERE movieStatus = 'now_showing';
```

## ✅ Checklist

- [x] Đã thêm trường `posterHorizontalURL` vào database
- [x] Đã cập nhật `create_movie()` function
- [x] Đã cập nhật `update_movie()` function
- [x] Đã thêm `update_movie_poster_horizontal()` function
- [x] Đã cập nhật `home.php` để sử dụng poster ngang cho banner
- [x] Đã tạo thư mục `src/img/moviesHorizontal/`

## 📌 Lưu ý

1. **Tùy chọn (Optional)**: Trường `posterHorizontalURL` có thể NULL. Nếu không có ảnh ngang, hệ thống sẽ tự động dùng ảnh dọc.

2. **SEO**: Nên có cả 2 loại ảnh cho trải nghiệm tốt nhất:
   - Ảnh dọc: hiển thị đẹp trong danh sách
   - Ảnh ngang: hiển thị đẹp trong banner

3. **Performance**: Nên optimize ảnh trước khi upload để trang load nhanh hơn.

4. **Naming Convention**: Đặt tên file giống nhau cho dễ quản lý:
   ```
   moviesVertical/avengers-endgame.jpg
   moviesHorizontal/avengers-endgame.jpg
   ```

## 🚀 Tính năng mở rộng (Future)

- [ ] Auto resize và crop ảnh khi upload
- [ ] Lazy loading cho ảnh banner
- [ ] Responsive images (srcset)
- [ ] WebP format support
- [ ] CDN integration
