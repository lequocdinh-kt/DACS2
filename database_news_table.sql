-- ============================================================
-- TẠO BẢNG NEWS (TIN TỨC & ƯU ĐÃI)
-- ============================================================

USE vku_cinema;

-- Tạo bảng news
CREATE TABLE IF NOT EXISTS news (
    newsID INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL COMMENT 'Tiêu đề tin tức',
    content TEXT NOT NULL COMMENT 'Nội dung chi tiết',
    summary VARCHAR(500) COMMENT 'Tóm tắt ngắn gọn',
    imageURL VARCHAR(255) COMMENT 'Đường dẫn hình ảnh',
    type ENUM('promotion', 'event', 'announcement', 'news') DEFAULT 'news' COMMENT 'Loại tin: ưu đãi, sự kiện, thông báo, tin tức',
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft' COMMENT 'Trạng thái: nháp, công khai, lưu trữ',
    promotionID INT NULL COMMENT 'Liên kết với mã khuyến mãi (nếu là tin ưu đãi)',
    movieID INT NULL COMMENT 'Liên kết với phim (nếu tin liên quan đến phim cụ thể)',
    priority INT DEFAULT 0 COMMENT 'Độ ưu tiên hiển thị (số càng lớn càng ưu tiên)',
    publishDate DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Ngày xuất bản',
    expireDate DATETIME NULL COMMENT 'Ngày hết hạn hiển thị',
    viewCount INT DEFAULT 0 COMMENT 'Số lượt xem',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign keys
    CONSTRAINT fk_news_promotion FOREIGN KEY (promotionID) REFERENCES promotions(promotionID) ON DELETE SET NULL,
    CONSTRAINT fk_news_movie FOREIGN KEY (movieID) REFERENCES movie(movieID) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_news_type (type),
    INDEX idx_news_status (status),
    INDEX idx_news_publishDate (publishDate),
    INDEX idx_news_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng quản lý tin tức, sự kiện và ưu đãi';

-- ============================================================
-- DỮ LIỆU MẪU CHO BẢNG PROMOTIONS
-- ============================================================

-- Tạo các mã khuyến mãi trước
INSERT INTO promotions (code, description, discountType, discountValue, minOrderValue, maxDiscount, startDate, endDate, usageLimit, status) VALUES
('STUDENT20', 'Ưu đãi sinh viên - Giảm 20% tất cả suất chiếu', 'percent', 20.00, 0, 50000, '2024-12-01', '2025-01-31', 0, 'active'),
('NEWYEAR2025', 'Khuyến mãi Tết Dương lịch - Giảm 30%', 'percent', 30.00, 100000, 80000, '2024-12-25', '2025-01-05', 500, 'active'),
('WEEKEND50K', 'Giảm 50K cho đơn hàng cuối tuần', 'fixed', 50000, 150000, NULL, '2024-12-01', '2025-02-28', 0, 'active'),
('VIP100K', 'Thành viên VIP - Giảm 100K', 'fixed', 100000, 300000, NULL, '2024-12-01', '2025-12-31', 0, 'active'),
('FIRSTBOOKING', 'Khách hàng mới - Giảm 15%', 'percent', 15.00, 0, 30000, '2024-12-01', '2025-03-31', 1000, 'active'),
('COMBO99K', 'Combo bắp nước - Giảm thêm 20K', 'fixed', 20000, 99000, NULL, '2024-12-01', '2025-01-15', 0, 'active'),
('IMAX50', 'Khai trương IMAX - Giảm 50K', 'fixed', 50000, 200000, NULL, '2024-12-15', '2024-12-31', 200, 'active');

-- ============================================================
-- DỮ LIỆU MẪU CHO BẢNG NEWS
-- ============================================================

-- 1. Tin ưu đãi sinh viên (liên kết với promotion)
INSERT INTO news (title, content, summary, imageURL, type, status, promotionID, priority, publishDate, expireDate) VALUES
(
    'Ưu Đãi Sinh Viên - Giảm 20% Cho Tất Cả Suất Chiếu',
    'VKU Cinema dành tặng sinh viên ưu đãi giảm giá 20% cho tất cả các suất chiếu phim trong tuần. Chỉ cần xuất trình thẻ sinh viên hợp lệ tại quầy vé hoặc nhập mã khuyến mãi STUDENT20 khi đặt vé online.\n\nĐiều kiện áp dụng:\n- Áp dụng cho tất cả suất chiếu từ thứ 2 đến thứ 6\n- Không áp dụng cho suất chiếu đặc biệt và ngày lễ\n- Mỗi thẻ sinh viên chỉ được sử dụng 1 lần/ngày\n- Không áp dụng đồng thời với các chương trình khuyến mãi khác',
    'Giảm ngay 20% cho sinh viên khi xuất trình thẻ sinh viên hoặc sử dụng mã STUDENT20',
    '/src/img/news/student-discount.jpg',
    'promotion',
    'published',
    1,
    10,
    NOW(),
    DATE_ADD(NOW(), INTERVAL 30 DAY)
);

-- 2. Sự kiện đêm phim Châu Á
INSERT INTO news (title, content, summary, imageURL, type, status, priority, publishDate, expireDate) VALUES
(
    'Đêm Phim Châu Á - Khám Phá Điện Ảnh Đông Phương',
    'VKU Cinema tự hào giới thiệu "Đêm Phim Châu Á" - chuỗi sự kiện đặc biệt chiếu các tác phẩm điện ảnh xuất sắc từ Hàn Quốc, Nhật Bản, Trung Quốc và các quốc gia Châu Á khác.\n\nLịch chiếu:\n- Thứ 5 hàng tuần: Phim Hàn Quốc\n- Thứ 6 hàng tuần: Phim Nhật Bản\n- Thứ 7 hàng tuần: Phim Trung Quốc\n\nĐặc biệt: Suất chiếu 19h00 với phụ đề tiếng Việt chuyên nghiệp, không lồng tiếng.\n\nĐăng ký thành viên VIP để được ưu tiên đặt vé và nhận thông tin phim mới sớm nhất!',
    'Chuỗi sự kiện chiếu phim Châu Á đặc sắc mỗi tuần tại VKU Cinema',
    '/src/img/news/asian-film-night.jpg',
    'event',
    'published',
    8,
    NOW(),
    DATE_ADD(NOW(), INTERVAL 60 DAY)
);

-- 3. Thông báo khai trương phòng IMAX
INSERT INTO news (title, content, summary, imageURL, type, status, priority, publishDate) VALUES
(
    'Khai Trương Phòng Chiếu IMAX - Trải Nghiệm Điện Ảnh Đỉnh Cao',
    'VKU Cinema hân hạnh thông báo chính thức khai trương phòng chiếu IMAX tiêu chuẩn quốc tế với:\n\n✨ Màn hình IMAX khổng lồ 22m x 12m\n✨ Hệ thống âm thanh vòm 12.1 kênh\n✨ Ghế ngồi cao cấp với chế độ massage\n✨ Công nghệ hình ảnh 4K Laser độc quyền\n\nPhim khai trương: Dune: Part Two\nNgày khởi chiếu: 15/12/2024\nGiá vé đặc biệt: 200.000đ (giá thường 250.000đ)\n\nĐặt vé ngay để trải nghiệm điện ảnh ở đẳng cấp hoàn toàn mới!',
    'Khai trương phòng chiếu IMAX với công nghệ tiên tiến nhất, mang đến trải nghiệm điện ảnh tuyệt vời',
    '/src/img/news/imax-opening.jpg',
    'announcement',
    'published',
    9,
    NOW()
);

-- 4. Tin tức về phim mới (liên kết với movie)
INSERT INTO news (title, content, summary, imageURL, type, status, movieID, priority, publishDate) VALUES
(
    'Mai - Siêu Phẩm Điện Ảnh Việt Đầu Năm 2024',
    'Bộ phim "Mai" của đạo diễn Trấn Thành hứa hẹn sẽ là một trong những tác phẩm điện ảnh Việt đáng chú ý nhất đầu năm 2024. Với sự tham gia của dàn diễn viên thực lực, phim kể về câu chuyện xúc động về tình người và những nỗi đau trong cuộc sống.\n\nThông tin phim:\n- Đạo diễn: Trấn Thành\n- Diễn viên: Phương Anh Đào, Tuấn Trần, Hồng Đào\n- Thể loại: Tâm lý, Gia đình\n- Thời lượng: 131 phút\n- Khởi chiếu: 10/02/2024\n\nĐặc biệt: Suất chiếu sớm dành riêng cho thành viên VIP vào 08/02/2024',
    'Trấn Thành trở lại với siêu phẩm điện ảnh "Mai" - câu chuyện cảm động về tình người',
    '/src/img/news/mai-movie.jpg',
    'news',
    'published',
    1,
    7,
    NOW()
);

-- 5. Ưu đãi combo bắp nước
INSERT INTO news (title, content, summary, imageURL, type, status, priority, publishDate, expireDate) VALUES
(
    'Combo Bắp Nước Siêu Tiết Kiệm - Chỉ 99K',
    'Thưởng thức phim với combo bắp nước siêu tiết kiệm:\n\n🍿 01 Bắp rang bơ size L\n🥤 02 Nước ngọt size L\n💰 Chỉ với 99.000đ (Tiết kiệm 40%)\n\nĐặc biệt:\n- Miễn phí nâng cấp caramel popcorn\n- Tặng kèm 01 ly sưu tầm phim bom tấn\n- Áp dụng cho tất cả các suất chiếu\n\nNhanh tay đặt vé và chọn combo ngay hôm nay!',
    'Combo bắp nước chỉ 99K - tiết kiệm 40%, tặng kèm quà hấp dẫn',
    '/src/img/news/combo-popcorn.jpg',
    'promotion',
    'published',
    6,
    NOW(),
    DATE_ADD(NOW(), INTERVAL 15 DAY)
);

-- 6. Sự kiện ra mắt phim
INSERT INTO news (title, content, summary, imageURL, type, status, movieID, priority, publishDate) VALUES
(
    'Họp Báo Ra Mắt Phim "Godzilla x Kong: The New Empire"',
    'VKU Cinema vinh dự đồng tổ chức buổi họp báo và công chiếu sớm bom tấn Hollywood "Godzilla x Kong: The New Empire".\n\nThời gian: 19h00, Thứ 7, 23/03/2024\nĐịa điểm: Phòng VIP 1, VKU Cinema\n\nChương trình:\n- 19h00 - 19h30: Họp báo với đại diện nhà phát hành\n- 19h30 - 20h00: Giao lưu và chụp ảnh\n- 20h00 - 22h30: Công chiếu đặc biệt\n\nĐăng ký tham dự miễn phí cho 100 khách hàng đầu tiên!',
    'Tham dự họp báo và công chiếu sớm bom tấn Godzilla x Kong miễn phí',
    '/src/img/news/godzilla-premiere.jpg',
    'event',
    'published',
    2,
    8,
    NOW()
);

-- ============================================================
-- STORED PROCEDURES
-- ============================================================

DELIMITER //

-- Procedure: Lấy tin tức mới nhất
CREATE PROCEDURE GetLatestNews(
    IN p_limit INT,
    IN p_type VARCHAR(50)
)
BEGIN
    IF p_type IS NULL OR p_type = '' THEN
        SELECT * FROM news 
        WHERE status = 'published' 
        AND (expireDate IS NULL OR expireDate > NOW())
        ORDER BY priority DESC, publishDate DESC 
        LIMIT p_limit;
    ELSE
        SELECT * FROM news 
        WHERE status = 'published' 
        AND type = p_type
        AND (expireDate IS NULL OR expireDate > NOW())
        ORDER BY priority DESC, publishDate DESC 
        LIMIT p_limit;
    END IF;
END //

-- Procedure: Lấy tin tức theo loại
CREATE PROCEDURE GetNewsByType(
    IN p_type VARCHAR(50),
    IN p_limit INT
)
BEGIN
    SELECT n.*, 
           m.title AS movieTitle,
           p.discountPercentage,
           p.promotionCode
    FROM news n
    LEFT JOIN movie m ON n.movieID = m.movieID
    LEFT JOIN promotions p ON n.promotionID = p.promotionID
    WHERE n.status = 'published' 
    AND n.type = p_type
    AND (n.expireDate IS NULL OR n.expireDate > NOW())
    ORDER BY n.priority DESC, n.publishDate DESC 
    LIMIT p_limit;
END //

-- Procedure: Lấy tin tức liên quan đến phim
CREATE PROCEDURE GetNewsByMovie(
    IN p_movieID INT
)
BEGIN
    SELECT * FROM news 
    WHERE movieID = p_movieID 
    AND status = 'published'
    AND (expireDate IS NULL OR expireDate > NOW())
    ORDER BY priority DESC, publishDate DESC;
END //

-- Procedure: Tăng lượt xem tin tức
CREATE PROCEDURE IncrementNewsViewCount(
    IN p_newsID INT
)
BEGIN
    UPDATE news 
    SET viewCount = viewCount + 1 
    WHERE newsID = p_newsID;
END //

-- Procedure: Lấy tin khuyến mãi đang hoạt động
CREATE PROCEDURE GetActivePromotionNews()
BEGIN
    SELECT n.*, p.promotionCode, p.discountPercentage, p.maxDiscount
    FROM news n
    INNER JOIN promotions p ON n.promotionID = p.promotionID
    WHERE n.status = 'published' 
    AND n.type = 'promotion'
    AND (n.expireDate IS NULL OR n.expireDate > NOW())
    AND p.endDate > NOW()
    ORDER BY n.priority DESC, n.publishDate DESC;
END //

DELIMITER ;

-- ============================================================
-- VIEWS
-- ============================================================

-- View: Tin tức đang hiển thị
CREATE OR REPLACE VIEW view_active_news AS
SELECT 
    n.newsID,
    n.title,
    n.summary,
    n.imageURL,
    n.type,
    n.priority,
    n.publishDate,
    n.viewCount,
    m.title AS movieTitle,
    m.posterURL AS moviePoster,
    p.promotionCode,
    p.discountPercentage
FROM news n
LEFT JOIN movie m ON n.movieID = m.movieID
LEFT JOIN promotions p ON n.promotionID = p.promotionID
WHERE n.status = 'published'
AND (n.expireDate IS NULL OR n.expireDate > NOW())
ORDER BY n.priority DESC, n.publishDate DESC;

-- View: Thống kê tin tức
CREATE OR REPLACE VIEW view_news_statistics AS
SELECT 
    type,
    COUNT(*) AS totalNews,
    SUM(viewCount) AS totalViews,
    AVG(viewCount) AS avgViews,
    MAX(publishDate) AS latestPublish
FROM news
WHERE status = 'published'
GROUP BY type;

-- ============================================================
-- TRIGGERS
-- ============================================================

DELIMITER //

-- Trigger: Tự động set publishDate khi chuyển sang published
CREATE TRIGGER before_news_publish
BEFORE UPDATE ON news
FOR EACH ROW
BEGIN
    IF NEW.status = 'published' AND OLD.status != 'published' THEN
        IF NEW.publishDate IS NULL OR NEW.publishDate < NOW() THEN
            SET NEW.publishDate = NOW();
        END IF;
    END IF;
END //

DELIMITER ;

-- ============================================================
-- INDEXES BỔ SUNG
-- ============================================================

-- Index cho tìm kiếm full-text
ALTER TABLE news ADD FULLTEXT INDEX idx_news_search (title, summary, content);

-- ============================================================
-- KẾT THÚC SCRIPT
-- ============================================================
