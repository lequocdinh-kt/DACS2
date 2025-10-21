-- ========================================
-- ADD HORIZONTAL POSTER SUPPORT
-- ========================================
-- Script này thêm trường posterHorizontalURL vào bảng Movie
-- để hỗ trợ ảnh ngang cho banner/hero section

-- 1. Thêm trường mới (nếu chưa có)
ALTER TABLE Movie
ADD COLUMN IF NOT EXISTS posterHorizontalURL VARCHAR(500);

-- 2. Kiểm tra cấu trúc bảng
DESCRIBE Movie;

-- 3. (Optional) Cập nhật ảnh ngang cho các phim mẫu
-- Uncomment và chỉnh sửa theo dữ liệu thực tế của bạn

/*
-- Ví dụ: Cập nhật poster ngang cho phim có ID = 1
UPDATE Movie 
SET posterHorizontalURL = '/src/img/moviesHorizontal/movie1.jpg' 
WHERE movieID = 1;

-- Cập nhật nhiều phim cùng lúc
UPDATE Movie 
SET posterHorizontalURL = CASE movieID
    WHEN 1 THEN '/src/img/moviesHorizontal/movie1.jpg'
    WHEN 2 THEN '/src/img/moviesHorizontal/movie2.jpg'
    WHEN 3 THEN '/src/img/moviesHorizontal/movie3.jpg'
    WHEN 4 THEN '/src/img/moviesHorizontal/movie4.jpg'
    WHEN 5 THEN '/src/img/moviesHorizontal/movie5.jpg'
    ELSE NULL
END
WHERE movieID IN (1, 2, 3, 4, 5);

-- Hoặc tự động tạo URL dựa trên movieID
UPDATE Movie 
SET posterHorizontalURL = CONCAT('/src/img/moviesHorizontal/', movieID, '.jpg')
WHERE movieStatus = 'now_showing';
*/

-- 4. Kiểm tra kết quả
SELECT 
    movieID,
    title,
    posterURL as 'Poster Dọc',
    posterHorizontalURL as 'Poster Ngang',
    movieStatus
FROM Movie
ORDER BY movieID;

-- ========================================
-- ROLLBACK (Nếu cần xóa trường)
-- ========================================
-- Uncomment dòng dưới nếu muốn xóa trường posterHorizontalURL
-- ALTER TABLE Movie DROP COLUMN posterHorizontalURL;
