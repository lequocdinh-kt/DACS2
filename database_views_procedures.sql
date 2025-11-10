-- ============================================
-- VKU CINEMA - VIEWS & PROCEDURES
-- Views, Triggers, và Stored Procedures
-- ============================================

USE dacs2;

-- ============================================
-- VIEWS
-- ============================================

-- View: Thống kê phim
CREATE OR REPLACE VIEW v_movie_statistics AS
SELECT 
    m.movieID, m.title, m.rating, m.movieStatus,
    COUNT(DISTINCT st.showtimeID) as total_showtimes,
    COUNT(DISTINCT b.bookingID) as total_bookings,
    COALESCE(SUM(b.totalSeats), 0) as total_tickets_sold,
    COALESCE(SUM(b.totalPrice), 0) as total_revenue
FROM movie m
LEFT JOIN showtimes st ON m.movieID = st.movieID
LEFT JOIN bookings b ON st.showtimeID = b.showtimeID AND b.paymentStatus = 'paid'
GROUP BY m.movieID, m.title, m.rating, m.movieStatus;

-- View: Chi tiết booking
CREATE OR REPLACE VIEW v_booking_details AS
SELECT 
    b.bookingID, b.bookingCode, b.bookingDate,
    u.username, u.email, m.title as movieTitle,
    st.showDate, st.showTime, r.roomName,
    b.totalSeats, b.totalPrice, b.paymentStatus, b.paymentMethod,
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
-- TRIGGERS
-- ============================================

DELIMITER //

-- Cleanup expired locks
CREATE TRIGGER IF NOT EXISTS cleanup_expired_locks
BEFORE INSERT ON bookings
FOR EACH ROW
BEGIN
    DELETE FROM seatlocks WHERE expiresAt < NOW();
END//

-- Update room seat count on insert
CREATE TRIGGER IF NOT EXISTS update_room_seat_count_insert
AFTER INSERT ON seats
FOR EACH ROW
BEGIN
    UPDATE rooms 
    SET totalSeats = (SELECT COUNT(*) FROM seats WHERE roomID = NEW.roomID)
    WHERE roomID = NEW.roomID;
END//

-- Update room seat count on delete
CREATE TRIGGER IF NOT EXISTS update_room_seat_count_delete
AFTER DELETE ON seats
FOR EACH ROW
BEGIN
    UPDATE rooms 
    SET totalSeats = (SELECT COUNT(*) FROM seats WHERE roomID = OLD.roomID)
    WHERE roomID = OLD.roomID;
END//

DELIMITER ;

-- ============================================
-- STORED PROCEDURES
-- ============================================

DELIMITER //

CREATE PROCEDURE IF NOT EXISTS get_revenue_by_date(IN target_date DATE)
BEGIN
    SELECT 
        DATE(b.paidAt) as date,
        COUNT(DISTINCT b.bookingID) as total_bookings,
        SUM(b.totalPrice) as total_revenue,
        SUM(b.totalSeats) as total_tickets
    FROM bookings b
    WHERE DATE(b.paidAt) = target_date AND b.paymentStatus = 'paid'
    GROUP BY DATE(b.paidAt);
END//

DELIMITER ;

SELECT 'Views and procedures created successfully!' as status;
