-- ============================================
-- VKU CINEMA - CORE DATABASE SCHEMA
-- Các bảng chính cho hệ thống đặt vé xem phim
-- Version: 2.0 - Optimized
-- ============================================

CREATE DATABASE IF NOT EXISTS dacs2 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE dacs2;

-- ============================================
-- 1. ROLES
-- ============================================
CREATE TABLE IF NOT EXISTS roles (
    roleID INT AUTO_INCREMENT PRIMARY KEY,
    roleName VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO roles (roleID, roleName, description) VALUES
(1, 'Admin', 'Quản trị viên hệ thống'),
(2, 'User', 'Người dùng thường'),
(3, 'Manager', 'Quản lý rạp');

-- ============================================
-- 2. USER
-- ============================================
CREATE TABLE IF NOT EXISTS `user` (
    userID INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    dateOfBirth DATE,
    sex ENUM('male', 'female', 'other'),
    cccd VARCHAR(20),
    roleID INT DEFAULT 2,
    last_login DATETIME,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (roleID) REFERENCES roles(roleID) ON DELETE SET NULL,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_roleID (roleID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. MOVIE
-- ============================================
CREATE TABLE IF NOT EXISTS movie (
    movieID INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    genre VARCHAR(100),
    duration INT,
    description TEXT,
    rating DECIMAL(3,1) DEFAULT 0.0,
    movieStatus ENUM('now_showing', 'coming_soon', 'stopped') DEFAULT 'coming_soon',
    posterURL VARCHAR(500),
    posterHorizontalURL VARCHAR(500),
    trailerURL VARCHAR(500),
    author VARCHAR(255),
    releaseDate DATE,
    ageRating VARCHAR(10) DEFAULT 'P',
    language VARCHAR(50) DEFAULT 'Vietnamese',
    country VARCHAR(50) DEFAULT 'Vietnam',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_movieStatus (movieStatus),
    INDEX idx_rating (rating),
    INDEX idx_releaseDate (releaseDate),
    INDEX idx_title (title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. ROOMS
-- ============================================
CREATE TABLE IF NOT EXISTS rooms (
    roomID INT AUTO_INCREMENT PRIMARY KEY,
    roomName VARCHAR(100) NOT NULL,
    roomType ENUM('2D', '3D', 'IMAX', '4DX') DEFAULT '2D',
    totalSeats INT DEFAULT 0,
    totalRows INT DEFAULT 0,
    seatsPerRow INT DEFAULT 0,
    status ENUM('active', 'maintenance', 'closed') DEFAULT 'active',
    description TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. SEATS
-- ============================================
CREATE TABLE IF NOT EXISTS seats (
    seatID INT AUTO_INCREMENT PRIMARY KEY,
    roomID INT NOT NULL,
    seatRow VARCHAR(2) NOT NULL,
    seatNumber INT NOT NULL,
    seatType ENUM('standard', 'vip', 'couple') DEFAULT 'standard',
    price DECIMAL(10,2) DEFAULT 45000.00,
    status ENUM('active', 'broken', 'reserved') DEFAULT 'active',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (roomID) REFERENCES rooms(roomID) ON DELETE CASCADE,
    UNIQUE KEY unique_seat (roomID, seatRow, seatNumber),
    INDEX idx_roomID (roomID),
    INDEX idx_seatType (seatType)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. SHOWTIMES
-- ============================================
CREATE TABLE IF NOT EXISTS showtimes (
    showtimeID INT AUTO_INCREMENT PRIMARY KEY,
    movieID INT NOT NULL,
    roomID INT NOT NULL,
    showDate DATE NOT NULL,
    showTime TIME NOT NULL,
    basePrice DECIMAL(10,2) DEFAULT 45000.00,
    status ENUM('available', 'full', 'cancelled') DEFAULT 'available',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (movieID) REFERENCES movie(movieID) ON DELETE CASCADE,
    FOREIGN KEY (roomID) REFERENCES rooms(roomID) ON DELETE CASCADE,
    INDEX idx_movieID (movieID),
    INDEX idx_roomID_showtime (roomID),
    INDEX idx_showDate (showDate),
    INDEX idx_showDateTime (showDate, showTime),
    INDEX idx_status_showtime (status),
    INDEX idx_showtime_movie_date (movieID, showDate, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. SEATLOCKS
-- ============================================
CREATE TABLE IF NOT EXISTS seatlocks (
    lockID INT AUTO_INCREMENT PRIMARY KEY,
    showtimeID INT NOT NULL,
    seatID INT NOT NULL,
    userID INT NOT NULL,
    lockedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expiresAt DATETIME NOT NULL,
    FOREIGN KEY (showtimeID) REFERENCES showtimes(showtimeID) ON DELETE CASCADE,
    FOREIGN KEY (seatID) REFERENCES seats(seatID) ON DELETE CASCADE,
    FOREIGN KEY (userID) REFERENCES `user`(userID) ON DELETE CASCADE,
    UNIQUE KEY unique_seat_showtime_lock (showtimeID, seatID),
    INDEX idx_expiresAt (expiresAt),
    INDEX idx_userID_lock (userID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 8. BOOKINGS
-- ============================================
CREATE TABLE IF NOT EXISTS bookings (
    bookingID INT AUTO_INCREMENT PRIMARY KEY,
    userID INT NOT NULL,
    showtimeID INT NOT NULL,
    bookingCode VARCHAR(50) NOT NULL UNIQUE,
    totalPrice DECIMAL(10,2) NOT NULL,
    totalSeats INT NOT NULL,
    paymentStatus ENUM('pending', 'paid', 'cancelled', 'expired') DEFAULT 'pending',
    paymentMethod VARCHAR(50),
    paidAt DATETIME,
    bookingDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expiredAt DATETIME,
    notes TEXT,
    FOREIGN KEY (userID) REFERENCES `user`(userID) ON DELETE CASCADE,
    FOREIGN KEY (showtimeID) REFERENCES showtimes(showtimeID) ON DELETE CASCADE,
    INDEX idx_userID_booking (userID),
    INDEX idx_showtimeID_booking (showtimeID),
    INDEX idx_bookingCode (bookingCode),
    INDEX idx_paymentStatus (paymentStatus),
    INDEX idx_bookingDate (bookingDate),
    INDEX idx_booking_user_status (userID, paymentStatus)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 9. BOOKINGSEATS
-- ============================================
CREATE TABLE IF NOT EXISTS bookingseats (
    bookingSeatID INT AUTO_INCREMENT PRIMARY KEY,
    bookingID INT NOT NULL,
    seatID INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bookingID) REFERENCES bookings(bookingID) ON DELETE CASCADE,
    FOREIGN KEY (seatID) REFERENCES seats(seatID) ON DELETE CASCADE,
    UNIQUE KEY unique_booking_seat (bookingID, seatID),
    INDEX idx_bookingID (bookingID),
    INDEX idx_seatID_booking (seatID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 10. PAYMENTS
-- ============================================
CREATE TABLE IF NOT EXISTS payments (
    paymentID INT AUTO_INCREMENT PRIMARY KEY,
    bookingID INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    paymentMethod ENUM('qr', 'momo', 'vnpay', 'banking', 'cash') DEFAULT 'qr',
    paymentStatus ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    transactionID VARCHAR(255),
    paymentDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completedAt DATETIME,
    description TEXT,
    FOREIGN KEY (bookingID) REFERENCES bookings(bookingID) ON DELETE CASCADE,
    INDEX idx_bookingID_payment (bookingID),
    INDEX idx_transactionID (transactionID),
    INDEX idx_paymentStatus_payment (paymentStatus)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 11. REVIEWS (Optional)
-- ============================================
CREATE TABLE IF NOT EXISTS reviews (
    reviewID INT AUTO_INCREMENT PRIMARY KEY,
    movieID INT NOT NULL,
    userID INT NOT NULL,
    rating DECIMAL(2,1) CHECK (rating >= 0 AND rating <= 10),
    comment TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (movieID) REFERENCES movie(movieID) ON DELETE CASCADE,
    FOREIGN KEY (userID) REFERENCES `user`(userID) ON DELETE CASCADE,
    UNIQUE KEY unique_user_movie_review (userID, movieID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 12. PROMOTIONS (Optional)
-- ============================================
CREATE TABLE IF NOT EXISTS promotions (
    promotionID INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    discountType ENUM('percent', 'fixed') DEFAULT 'percent',
    discountValue DECIMAL(10,2) NOT NULL,
    minOrderValue DECIMAL(10,2) DEFAULT 0,
    maxDiscount DECIMAL(10,2),
    startDate DATE NOT NULL,
    endDate DATE NOT NULL,
    usageLimit INT DEFAULT 0,
    usedCount INT DEFAULT 0,
    status ENUM('active', 'inactive', 'expired') DEFAULT 'active',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 13. BOOKING_PROMOTIONS (Optional)
-- ============================================
CREATE TABLE IF NOT EXISTS booking_promotions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bookingID INT NOT NULL,
    promotionID INT NOT NULL,
    discountAmount DECIMAL(10,2) NOT NULL,
    appliedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bookingID) REFERENCES bookings(bookingID) ON DELETE CASCADE,
    FOREIGN KEY (promotionID) REFERENCES promotions(promotionID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'Core tables created successfully!' as status;
