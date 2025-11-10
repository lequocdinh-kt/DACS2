-- ============================================
-- VKU CINEMA - DATABASE SCHEMA (Optimized)
-- Version: 2.1 - Tối ưu cho linting
-- ============================================
-- Hướng dẫn: Import theo thứ tự
-- 1. database_core.sql (bảng chính)
-- 2. database_sample_data.sql (dữ liệu mẫu)
-- 3. database_views_procedures.sql (views/triggers)
-- ============================================

CREATE DATABASE IF NOT EXISTS dacs2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dacs2;

-- ============================================
-- 1. BẢNG ROLES (Vai trò người dùng)
-- ============================================
CREATE TABLE IF NOT EXISTS roles (
    roleID INT AUTO_INCREMENT PRIMARY KEY,
    roleName VARCHAR(50) NOT NULL UNIQUE COMMENT 'Tên vai trò: Admin, User, Manager',
    description TEXT COMMENT 'Mô tả vai trò',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Bảng quản lý vai trò người dùng';

-- Thêm dữ liệu mặc định cho roles
INSERT INTO roles (roleID, roleName, description) VALUES
(1, 'Admin', 'Quản trị viên hệ thống - Có toàn quyền'),
(2, 'User', 'Người dùng thường - Đặt vé xem phim'),
(3, 'Manager', 'Quản lý rạp - Quản lý phim và suất chiếu');

-- ============================================
-- 2. BẢNG USER (Người dùng)
-- ============================================
CREATE TABLE IF NOT EXISTS `user` (
    userID INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE COMMENT 'Tên đăng nhập',
    email VARCHAR(100) NOT NULL UNIQUE COMMENT 'Email đăng nhập',
    password VARCHAR(255) NOT NULL COMMENT 'Mật khẩu đã hash (bcrypt)',
    phone VARCHAR(15) COMMENT 'Số điện thoại',
    dateOfBirth DATE COMMENT 'Ngày sinh',
    sex ENUM('male', 'female', 'other') COMMENT 'Giới tính',
    cccd VARCHAR(20) COMMENT 'Số CCCD/CMND',
    roleID INT DEFAULT 2 COMMENT 'ID vai trò (FK)',
    last_login DATETIME COMMENT 'Lần đăng nhập cuối',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Ngày tạo tài khoản',
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (roleID) REFERENCES roles(roleID) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Bảng quản lý người dùng';

-- Index để tối ưu tìm kiếm
CREATE INDEX idx_email ON `user`(email);
CREATE INDEX idx_username ON `user`(username);
CREATE INDEX idx_roleID ON `user`(roleID);

-- ============================================
-- 3. BẢNG MOVIE (Phim)
-- ============================================
CREATE TABLE IF NOT EXISTS movie (
    movieID INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL COMMENT 'Tên phim',
    genre VARCHAR(100) COMMENT 'Thể loại (Action, Drama, Comedy...)',
    duration INT COMMENT 'Thời lượng phim (phút)',
    description TEXT COMMENT 'Mô tả nội dung phim',
    rating DECIMAL(3,1) DEFAULT 0.0 COMMENT 'Đánh giá (0.0 - 10.0)',
    movieStatus ENUM('now_showing', 'coming_soon', 'stopped') DEFAULT 'coming_soon' 
        COMMENT 'Trạng thái: đang chiếu, sắp chiếu, ngừng chiếu',
    posterURL VARCHAR(500) COMMENT 'Link poster dọc',
    posterHorizontalURL VARCHAR(500) COMMENT 'Link poster ngang (banner)',
    trailerURL VARCHAR(500) COMMENT 'Link trailer YouTube',
    author VARCHAR(255) COMMENT 'Đạo diễn',
    releaseDate DATE COMMENT 'Ngày ra mắt',
    ageRating VARCHAR(10) DEFAULT 'P' COMMENT 'Độ tuổi: P, C13, C16, C18',
    language VARCHAR(50) DEFAULT 'Vietnamese' COMMENT 'Ngôn ngữ',
    country VARCHAR(50) DEFAULT 'Vietnam' COMMENT 'Quốc gia sản xuất',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Bảng quản lý phim';

-- Index để tối ưu tìm kiếm
CREATE INDEX idx_movieStatus ON movie(movieStatus);
CREATE INDEX idx_rating ON movie(rating);
CREATE INDEX idx_releaseDate ON movie(releaseDate);
CREATE INDEX idx_title ON movie(title);

-- ============================================
-- 4. BẢNG ROOMS (Phòng chiếu/Rạp)
-- ============================================
CREATE TABLE IF NOT EXISTS rooms (
    roomID INT AUTO_INCREMENT PRIMARY KEY,
    roomName VARCHAR(100) NOT NULL COMMENT 'Tên phòng (VKU Cinema 1, 2, 3...)',
    roomType ENUM('2D', '3D', 'IMAX', '4DX') DEFAULT '2D' COMMENT 'Loại màn hình',
    totalSeats INT DEFAULT 0 COMMENT 'Tổng số ghế trong phòng',
    totalRows INT DEFAULT 0 COMMENT 'Tổng số hàng ghế (A, B, C...)',
    seatsPerRow INT DEFAULT 0 COMMENT 'Số ghế mỗi hàng',
    status ENUM('active', 'maintenance', 'closed') DEFAULT 'active' COMMENT 'Trạng thái phòng',
    description TEXT COMMENT 'Mô tả phòng chiếu',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Bảng quản lý phòng chiếu';

-- ============================================
-- 5. BẢNG SEATS (Ghế ngồi)
-- ============================================
CREATE TABLE IF NOT EXISTS seats (
    seatID INT AUTO_INCREMENT PRIMARY KEY,
    roomID INT NOT NULL COMMENT 'ID phòng chiếu (FK)',
    seatRow VARCHAR(2) NOT NULL COMMENT 'Hàng ghế (A, B, C, D...)',
    seatNumber INT NOT NULL COMMENT 'Số ghế (1, 2, 3...)',
    seatType ENUM('standard', 'vip', 'couple') DEFAULT 'standard' 
        COMMENT 'Loại ghế: thường, VIP, đôi',
    price DECIMAL(10,2) DEFAULT 45000.00 COMMENT 'Giá ghế (VNĐ)',
    status ENUM('active', 'broken', 'reserved') DEFAULT 'active' COMMENT 'Trạng thái ghế',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (roomID) REFERENCES rooms(roomID) ON DELETE CASCADE,
    UNIQUE KEY unique_seat (roomID, seatRow, seatNumber)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Bảng quản lý ghế ngồi';

-- Index để tối ưu
CREATE INDEX idx_roomID ON seats(roomID);
CREATE INDEX idx_seatType ON seats(seatType);

-- ============================================
-- 6. BẢNG SHOWTIMES (Lịch chiếu phim)
-- ============================================
CREATE TABLE IF NOT EXISTS showtimes (
    showtimeID INT AUTO_INCREMENT PRIMARY KEY,
    movieID INT NOT NULL COMMENT 'ID phim (FK)',
    roomID INT NOT NULL COMMENT 'ID phòng chiếu (FK)',
    showDate DATE NOT NULL COMMENT 'Ngày chiếu',
    showTime TIME NOT NULL COMMENT 'Giờ chiếu',
    basePrice DECIMAL(10,2) DEFAULT 45000.00 COMMENT 'Giá vé cơ bản',
    status ENUM('available', 'full', 'cancelled') DEFAULT 'available' 
        COMMENT 'Trạng thái suất chiếu',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (movieID) REFERENCES movie(movieID) ON DELETE CASCADE,
    FOREIGN KEY (roomID) REFERENCES rooms(roomID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Bảng lịch chiếu phim';

-- Index để tối ưu tìm kiếm
CREATE INDEX idx_movieID ON showtimes(movieID);
CREATE INDEX idx_roomID_showtime ON showtimes(roomID);
CREATE INDEX idx_showDate ON showtimes(showDate);
CREATE INDEX idx_showDateTime ON showtimes(showDate, showTime);
CREATE INDEX idx_status_showtime ON showtimes(status);

-- ============================================
-- 7. BẢNG SEATLOCKS (Khóa ghế tạm thời)
-- ============================================
CREATE TABLE IF NOT EXISTS seatlocks (
    lockID INT AUTO_INCREMENT PRIMARY KEY,
    showtimeID INT NOT NULL COMMENT 'ID suất chiếu (FK)',
    seatID INT NOT NULL COMMENT 'ID ghế (FK)',
    userID INT NOT NULL COMMENT 'ID người dùng đang giữ ghế (FK)',
    lockedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời gian lock',
    expiresAt DATETIME NOT NULL COMMENT 'Thời gian hết hạn lock (10-15 phút)',
    FOREIGN KEY (showtimeID) REFERENCES showtimes(showtimeID) ON DELETE CASCADE,
    FOREIGN KEY (seatID) REFERENCES seats(seatID) ON DELETE CASCADE,
    FOREIGN KEY (userID) REFERENCES `user`(userID) ON DELETE CASCADE,
    UNIQUE KEY unique_seat_showtime_lock (showtimeID, seatID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Bảng lock ghế tạm thời (10-15 phút khi user đang chọn)';

-- Index để tối ưu
CREATE INDEX idx_expiresAt ON seatlocks(expiresAt);
CREATE INDEX idx_userID_lock ON seatlocks(userID);

-- ============================================
-- 8. BẢNG BOOKINGS (Đơn đặt vé)
-- ============================================
CREATE TABLE IF NOT EXISTS bookings (
    bookingID INT AUTO_INCREMENT PRIMARY KEY,
    userID INT NOT NULL COMMENT 'ID người dùng (FK)',
    showtimeID INT NOT NULL COMMENT 'ID suất chiếu (FK)',
    bookingCode VARCHAR(50) NOT NULL UNIQUE COMMENT 'Mã đặt vé (VKU202511100001)',
    totalPrice DECIMAL(10,2) NOT NULL COMMENT 'Tổng tiền',
    totalSeats INT NOT NULL COMMENT 'Tổng số ghế đã đặt',
    paymentStatus ENUM('pending', 'paid', 'cancelled', 'expired') DEFAULT 'pending' 
        COMMENT 'Trạng thái thanh toán',
    paymentMethod VARCHAR(50) COMMENT 'Phương thức: qr, momo, vnpay, cash',
    paidAt DATETIME COMMENT 'Thời gian thanh toán',
    bookingDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời gian đặt vé',
    expiredAt DATETIME COMMENT 'Thời gian hết hạn đơn (15 phút nếu chưa thanh toán)',
    notes TEXT COMMENT 'Ghi chú',
    FOREIGN KEY (userID) REFERENCES `user`(userID) ON DELETE CASCADE,
    FOREIGN KEY (showtimeID) REFERENCES showtimes(showtimeID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Bảng đơn đặt vé';

-- Index để tối ưu
CREATE INDEX idx_userID_booking ON bookings(userID);
CREATE INDEX idx_showtimeID_booking ON bookings(showtimeID);
CREATE INDEX idx_bookingCode ON bookings(bookingCode);
CREATE INDEX idx_paymentStatus ON bookings(paymentStatus);
CREATE INDEX idx_bookingDate ON bookings(bookingDate);

-- ============================================
-- 9. BẢNG BOOKINGSEATS (Chi tiết ghế đã đặt)
-- ============================================
CREATE TABLE IF NOT EXISTS bookingseats (
    bookingSeatID INT AUTO_INCREMENT PRIMARY KEY,
    bookingID INT NOT NULL COMMENT 'ID đơn đặt vé (FK)',
    seatID INT NOT NULL COMMENT 'ID ghế (FK)',
    price DECIMAL(10,2) NOT NULL COMMENT 'Giá ghế tại thời điểm đặt',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bookingID) REFERENCES bookings(bookingID) ON DELETE CASCADE,
    FOREIGN KEY (seatID) REFERENCES seats(seatID) ON DELETE CASCADE,
    UNIQUE KEY unique_booking_seat (bookingID, seatID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Bảng chi tiết ghế của từng đơn đặt vé';

-- Index để tối ưu
CREATE INDEX idx_bookingID ON bookingseats(bookingID);
CREATE INDEX idx_seatID_booking ON bookingseats(seatID);

-- ============================================
-- 10. BẢNG PAYMENTS (Thanh toán)
-- ============================================
CREATE TABLE IF NOT EXISTS payments (
    paymentID INT AUTO_INCREMENT PRIMARY KEY,
    bookingID INT NOT NULL COMMENT 'ID đơn đặt vé (FK)',
    amount DECIMAL(10,2) NOT NULL COMMENT 'Số tiền thanh toán',
    paymentMethod ENUM('qr', 'momo', 'vnpay', 'banking', 'cash') DEFAULT 'qr' 
        COMMENT 'Phương thức thanh toán',
    paymentStatus ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending' 
        COMMENT 'Trạng thái thanh toán',
    transactionID VARCHAR(255) COMMENT 'Mã giao dịch từ cổng thanh toán',
    paymentDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời gian tạo',
    completedAt DATETIME COMMENT 'Thời gian hoàn thành thanh toán',
    description TEXT COMMENT 'Mô tả giao dịch',
    FOREIGN KEY (bookingID) REFERENCES bookings(bookingID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Bảng quản lý thanh toán';

-- Index để tối ưu
CREATE INDEX idx_bookingID_payment ON payments(bookingID);
CREATE INDEX idx_transactionID ON payments(transactionID);
CREATE INDEX idx_paymentStatus_payment ON payments(paymentStatus);

-- ============================================
-- 11. BẢNG REVIEWS (Đánh giá phim) - Optional
-- ============================================
CREATE TABLE IF NOT EXISTS reviews (
    reviewID INT AUTO_INCREMENT PRIMARY KEY,
    movieID INT NOT NULL COMMENT 'ID phim (FK)',
    userID INT NOT NULL COMMENT 'ID người dùng (FK)',
    rating DECIMAL(2,1) CHECK (rating >= 0 AND rating <= 10) COMMENT 'Điểm đánh giá (0-10)',
    comment TEXT COMMENT 'Nội dung đánh giá',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (movieID) REFERENCES movie(movieID) ON DELETE CASCADE,
    FOREIGN KEY (userID) REFERENCES `user`(userID) ON DELETE CASCADE,
    UNIQUE KEY unique_user_movie_review (userID, movieID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Bảng đánh giá phim của người dùng';

-- ============================================
-- 12. BẢNG PROMOTIONS (Khuyến mãi) - Optional
-- ============================================
CREATE TABLE IF NOT EXISTS promotions (
    promotionID INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE COMMENT 'Mã khuyến mãi (SALE20, NEWYEAR...)',
    description TEXT COMMENT 'Mô tả chương trình',
    discountType ENUM('percent', 'fixed') DEFAULT 'percent' 
        COMMENT 'Loại giảm giá: phần trăm hoặc số tiền cố định',
    discountValue DECIMAL(10,2) NOT NULL COMMENT 'Giá trị giảm (20% hoặc 50000đ)',
    minOrderValue DECIMAL(10,2) DEFAULT 0 COMMENT 'Giá trị đơn hàng tối thiểu',
    maxDiscount DECIMAL(10,2) COMMENT 'Giảm tối đa (cho loại percent)',
    startDate DATE NOT NULL COMMENT 'Ngày bắt đầu',
    endDate DATE NOT NULL COMMENT 'Ngày kết thúc',
    usageLimit INT DEFAULT 0 COMMENT 'Số lần sử dụng tối đa (0 = không giới hạn)',
    usedCount INT DEFAULT 0 COMMENT 'Số lần đã sử dụng',
    status ENUM('active', 'inactive', 'expired') DEFAULT 'active',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Bảng quản lý mã khuyến mãi';

-- ============================================
-- 13. BẢNG BOOKING_PROMOTIONS (Áp dụng khuyến mãi) - Optional
-- ============================================
CREATE TABLE IF NOT EXISTS booking_promotions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bookingID INT NOT NULL COMMENT 'ID đơn đặt vé (FK)',
    promotionID INT NOT NULL COMMENT 'ID khuyến mãi (FK)',
    discountAmount DECIMAL(10,2) NOT NULL COMMENT 'Số tiền giảm thực tế',
    appliedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bookingID) REFERENCES bookings(bookingID) ON DELETE CASCADE,
    FOREIGN KEY (promotionID) REFERENCES promotions(promotionID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Bảng lưu khuyến mãi đã áp dụng cho đơn hàng';

-- ============================================
-- DỮ LIỆU MẪU (Sample Data)
-- ============================================

-- Thêm user mẫu (password: 123456)
INSERT INTO `user` (username, email, password, roleID) VALUES
('admin', 'admin@vkucinema.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
('user1', 'user1@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2),
('manager', 'manager@vkucinema.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3);

-- Thêm phòng chiếu mẫu
INSERT INTO rooms (roomName, roomType, totalSeats, totalRows, seatsPerRow, status) VALUES
('VKU Cinema 1', '2D', 60, 6, 10, 'active'),
('VKU Cinema 2', '3D', 80, 8, 10, 'active'),
('VKU Cinema 3', 'IMAX', 100, 10, 10, 'active');

-- Tạo ghế cho phòng 1 (60 ghế: A1-A10, B1-B10... F1-F10)
INSERT INTO seats (roomID, seatRow, seatNumber, seatType, price)
SELECT 
    1 as roomID,
    CHAR(64 + rows.n) as seatRow,
    cols.n as seatNumber,
    CASE 
        WHEN rows.n IN (1, 2) THEN 'standard'  -- Hàng A, B: ghế thường
        WHEN rows.n IN (5, 6) THEN 'vip'       -- Hàng E, F: ghế VIP
        ELSE 'standard'
    END as seatType,
    CASE 
        WHEN rows.n IN (1, 2) THEN 45000
        WHEN rows.n IN (5, 6) THEN 75000
        ELSE 45000
    END as price
FROM 
    (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6) rows,
    (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION 
     SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10) cols
ORDER BY seatRow, seatNumber;

-- Tạo ghế cho phòng 2 (80 ghế: A1-A10, B1-B10... H1-H10)
INSERT INTO seats (roomID, seatRow, seatNumber, seatType, price)
SELECT 
    2 as roomID,
    CHAR(64 + rows.n) as seatRow,
    cols.n as seatNumber,
    CASE 
        WHEN rows.n IN (1, 2, 3) THEN 'standard'
        WHEN rows.n IN (7, 8) THEN 'vip'
        ELSE 'standard'
    END as seatType,
    CASE 
        WHEN rows.n IN (1, 2, 3) THEN 50000
        WHEN rows.n IN (7, 8) THEN 80000
        ELSE 50000
    END as price
FROM 
    (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION 
     SELECT 6 UNION SELECT 7 UNION SELECT 8) rows,
    (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION 
     SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10) cols
ORDER BY seatRow, seatNumber;

-- Tạo ghế cho phòng 3 (100 ghế: A1-A10, B1-B10... J1-J10)
INSERT INTO seats (roomID, seatRow, seatNumber, seatType, price)
SELECT 
    3 as roomID,
    CHAR(64 + rows.n) as seatRow,
    cols.n as seatNumber,
    CASE 
        WHEN rows.n IN (1, 2) THEN 'standard'
        WHEN rows.n IN (9, 10) THEN 'vip'
        WHEN rows.n = 5 AND cols.n IN (4, 5, 6, 7) THEN 'couple'
        ELSE 'standard'
    END as seatType,
    CASE 
        WHEN rows.n IN (1, 2) THEN 55000
        WHEN rows.n IN (9, 10) THEN 90000
        WHEN rows.n = 5 AND cols.n IN (4, 5, 6, 7) THEN 150000
        ELSE 55000
    END as price
FROM 
    (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION 
     SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10) rows,
    (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION 
     SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10) cols
ORDER BY seatRow, seatNumber;

-- Thêm phim mẫu
INSERT INTO movie (title, genre, duration, description, rating, movieStatus, 
                   posterURL, trailerURL, author, releaseDate, ageRating) VALUES
('Avengers: Endgame', 'Action, Adventure, Sci-Fi', 181, 
 'After the devastating events of Avengers: Infinity War, the universe is in ruins.', 
 8.4, 'now_showing', 
 '/src/img/moviesVertical/avengers-endgame.jpg',
 'https://www.youtube.com/watch?v=TcMBFSGVi1c',
 'Anthony Russo, Joe Russo', '2019-04-26', 'C13'),

('The Batman', 'Action, Crime, Drama', 176,
 'When the Riddler, a sadistic serial killer, begins murdering key political figures in Gotham.',
 7.8, 'now_showing',
 '/src/img/moviesVertical/the-batman.jpg',
 'https://www.youtube.com/watch?v=mqqft2x_Aa4',
 'Matt Reeves', '2022-03-04', 'C16'),

('Spider-Man: No Way Home', 'Action, Adventure, Fantasy', 148,
 'With Spider-Man identity now revealed, Peter asks Doctor Strange for help.',
 8.2, 'now_showing',
 '/src/img/moviesVertical/spiderman-nwh.jpg',
 'https://www.youtube.com/watch?v=JfVOs4VSpmA',
 'Jon Watts', '2021-12-17', 'C13'),

('Avatar: The Way of Water', 'Action, Adventure, Fantasy', 192,
 'Jake Sully lives with his newfound family formed on the extrasolar moon Pandora.',
 7.6, 'coming_soon',
 '/src/img/moviesVertical/avatar-2.jpg',
 'https://www.youtube.com/watch?v=d9MyW72ELq0',
 'James Cameron', '2025-12-16', 'C13');

-- Thêm suất chiếu mẫu (7 ngày tới)
INSERT INTO showtimes (movieID, roomID, showDate, showTime, basePrice, status)
SELECT 
    m.movieID,
    r.roomID,
    DATE_ADD(CURDATE(), INTERVAL d.day_offset DAY) as showDate,
    t.showTime,
    45000 as basePrice,
    'available' as status
FROM 
    (SELECT 1 as movieID UNION SELECT 2 UNION SELECT 3) m
CROSS JOIN
    (SELECT 1 as roomID UNION SELECT 2) r
CROSS JOIN
    (SELECT 0 as day_offset UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION 
     SELECT 4 UNION SELECT 5 UNION SELECT 6) d
CROSS JOIN
    (SELECT '09:00:00' as showTime UNION SELECT '12:00:00' UNION SELECT '15:00:00' UNION 
     SELECT '18:00:00' UNION SELECT '21:00:00') t
WHERE m.movieID IN (SELECT movieID FROM movie WHERE movieStatus = 'now_showing')
ORDER BY showDate, showTime, roomID;

-- ============================================
-- STORED PROCEDURES & TRIGGERS (Optional)
-- ============================================

-- Trigger: Tự động xóa lock ghế hết hạn
DELIMITER //
CREATE TRIGGER IF NOT EXISTS cleanup_expired_locks
BEFORE INSERT ON bookings
FOR EACH ROW
BEGIN
    DELETE FROM seatlocks WHERE expiresAt < NOW();
END//
DELIMITER ;

-- Trigger: Cập nhật tổng số ghế trong phòng khi thêm ghế mới
DELIMITER //
CREATE TRIGGER IF NOT EXISTS update_room_seat_count_insert
AFTER INSERT ON seats
FOR EACH ROW
BEGIN
    UPDATE rooms 
    SET totalSeats = (SELECT COUNT(*) FROM seats WHERE roomID = NEW.roomID)
    WHERE roomID = NEW.roomID;
END//
DELIMITER ;

-- Trigger: Cập nhật tổng số ghế trong phòng khi xóa ghế
DELIMITER //
CREATE TRIGGER IF NOT EXISTS update_room_seat_count_delete
AFTER DELETE ON seats
FOR EACH ROW
BEGIN
    UPDATE rooms 
    SET totalSeats = (SELECT COUNT(*) FROM seats WHERE roomID = OLD.roomID)
    WHERE roomID = OLD.roomID;
END//
DELIMITER ;

-- Stored Procedure: Lấy thống kê doanh thu theo ngày
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS get_revenue_by_date(IN target_date DATE)
BEGIN
    SELECT 
        DATE(b.paidAt) as date,
        COUNT(DISTINCT b.bookingID) as total_bookings,
        SUM(b.totalPrice) as total_revenue,
        SUM(b.totalSeats) as total_tickets
    FROM bookings b
    WHERE DATE(b.paidAt) = target_date 
    AND b.paymentStatus = 'paid'
    GROUP BY DATE(b.paidAt);
END//
DELIMITER ;

-- ============================================
-- VIEWS (Optional)
-- ============================================

-- View: Thống kê phim đang chiếu
CREATE OR REPLACE VIEW v_movie_statistics AS
SELECT 
    m.movieID,
    m.title,
    m.rating,
    m.movieStatus,
    COUNT(DISTINCT st.showtimeID) as total_showtimes,
    COUNT(DISTINCT b.bookingID) as total_bookings,
    COALESCE(SUM(b.totalSeats), 0) as total_tickets_sold,
    COALESCE(SUM(b.totalPrice), 0) as total_revenue
FROM movie m
LEFT JOIN showtimes st ON m.movieID = st.movieID
LEFT JOIN bookings b ON st.showtimeID = b.showtimeID AND b.paymentStatus = 'paid'
GROUP BY m.movieID, m.title, m.rating, m.movieStatus;

-- View: Chi tiết đơn đặt vé
CREATE OR REPLACE VIEW v_booking_details AS
SELECT 
    b.bookingID,
    b.bookingCode,
    b.bookingDate,
    u.username,
    u.email,
    m.title as movieTitle,
    st.showDate,
    st.showTime,
    r.roomName,
    b.totalSeats,
    b.totalPrice,
    b.paymentStatus,
    b.paymentMethod,
    GROUP_CONCAT(CONCAT(s.seatRow, s.seatNumber) ORDER BY s.seatRow, s.seatNumber SEPARATOR ', ') as seats
FROM bookings b
INNER JOIN `user` u ON b.userID = u.userID
INNER JOIN showtimes st ON b.showtimeID = st.showtimeID
INNER JOIN movie m ON st.movieID = m.movieID
INNER JOIN rooms r ON st.roomID = r.roomID
LEFT JOIN bookingseats bs ON b.bookingID = bs.bookingID
LEFT JOIN seats s ON bs.seatID = s.seatID
GROUP BY b.bookingID;

-- ============================================
-- INDEXES BỔ SUNG (Tối ưu hiệu suất)
-- ============================================

-- Composite indexes cho các truy vấn phức tạp
CREATE INDEX idx_showtime_movie_date ON showtimes(movieID, showDate, status);
CREATE INDEX idx_booking_user_status ON bookings(userID, paymentStatus);
CREATE INDEX idx_seat_room_status ON seats(roomID, status);

-- ============================================
-- HOÀN TẤT
-- ============================================

-- Hiển thị thông tin database
SELECT 'Database created successfully!' as status;
SELECT 'Total tables:' as info, COUNT(*) as count FROM information_schema.tables WHERE table_schema = 'dacs2';

-- Liệt kê tất cả bảng
SELECT table_name, table_rows, table_comment 
FROM information_schema.tables 
WHERE table_schema = 'dacs2' 
ORDER BY table_name;
