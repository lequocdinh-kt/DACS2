-- ============================================
-- VKU CINEMA - SAMPLE DATA
-- Dữ liệu mẫu cho testing
-- ============================================

USE dacs2;

-- User mẫu (password: 123456)
INSERT INTO `user` (username, email, password, roleID) VALUES
('admin', 'admin@vkucinema.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
('user1', 'user1@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2),
('manager', 'manager@vkucinema.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3);

-- Phòng chiếu
INSERT INTO rooms (roomName, roomType, totalSeats, totalRows, seatsPerRow, status) VALUES
('VKU Cinema 1', '2D', 60, 6, 10, 'active'),
('VKU Cinema 2', '3D', 80, 8, 10, 'active'),
('VKU Cinema 3', 'IMAX', 100, 10, 10, 'active');

-- Ghế phòng 1 (60 ghế)
INSERT INTO seats (roomID, seatRow, seatNumber, seatType, price)
SELECT 1, CHAR(64 + row_num.n), col_num.n,
    CASE WHEN row_num.n IN (1,2) THEN 'standard' WHEN row_num.n IN (5,6) THEN 'vip' ELSE 'standard' END,
    CASE WHEN row_num.n IN (1,2) THEN 45000 WHEN row_num.n IN (5,6) THEN 75000 ELSE 45000 END
FROM 
    (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6) row_num,
    (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION 
     SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10) col_num;

-- Ghế phòng 2 (80 ghế)
INSERT INTO seats (roomID, seatRow, seatNumber, seatType, price)
SELECT 2, CHAR(64 + row_num.n), col_num.n,
    CASE WHEN row_num.n IN (1,2,3) THEN 'standard' WHEN row_num.n IN (7,8) THEN 'vip' ELSE 'standard' END,
    CASE WHEN row_num.n IN (1,2,3) THEN 50000 WHEN row_num.n IN (7,8) THEN 80000 ELSE 50000 END
FROM 
    (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8) row_num,
    (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION 
     SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10) col_num;

-- Ghế phòng 3 (100 ghế)
INSERT INTO seats (roomID, seatRow, seatNumber, seatType, price)
SELECT 3, CHAR(64 + row_num.n), col_num.n,
    CASE 
        WHEN row_num.n IN (1,2) THEN 'standard'
        WHEN row_num.n IN (9,10) THEN 'vip'
        WHEN row_num.n = 5 AND col_num.n IN (4,5,6,7) THEN 'couple'
        ELSE 'standard'
    END,
    CASE 
        WHEN row_num.n IN (1,2) THEN 55000
        WHEN row_num.n IN (9,10) THEN 90000
        WHEN row_num.n = 5 AND col_num.n IN (4,5,6,7) THEN 150000
        ELSE 55000
    END
FROM 
    (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION 
     SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10) row_num,
    (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION 
     SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10) col_num;

-- Phim mẫu
INSERT INTO movie (title, genre, duration, description, rating, movieStatus, posterURL, trailerURL, author, releaseDate, ageRating) VALUES
('Avengers: Endgame', 'Action, Adventure, Sci-Fi', 181, 
 'After the devastating events of Avengers: Infinity War, the universe is in ruins.', 
 8.4, 'now_showing', '/src/img/moviesVertical/avengers-endgame.jpg',
 'https://www.youtube.com/watch?v=TcMBFSGVi1c', 'Anthony Russo, Joe Russo', '2019-04-26', 'C13'),
('The Batman', 'Action, Crime, Drama', 176,
 'When the Riddler, a sadistic serial killer, begins murdering key political figures in Gotham.',
 7.8, 'now_showing', '/src/img/moviesVertical/the-batman.jpg',
 'https://www.youtube.com/watch?v=mqqft2x_Aa4', 'Matt Reeves', '2022-03-04', 'C16'),
('Spider-Man: No Way Home', 'Action, Adventure, Fantasy', 148,
 'With Spider-Man identity now revealed, Peter asks Doctor Strange for help.',
 8.2, 'now_showing', '/src/img/moviesVertical/spiderman-nwh.jpg',
 'https://www.youtube.com/watch?v=JfVOs4VSpmA', 'Jon Watts', '2021-12-17', 'C13'),
('Avatar: The Way of Water', 'Action, Adventure, Fantasy', 192,
 'Jake Sully lives with his newfound family formed on the extrasolar moon Pandora.',
 7.6, 'coming_soon', '/src/img/moviesVertical/avatar-2.jpg',
 'https://www.youtube.com/watch?v=d9MyW72ELq0', 'James Cameron', '2025-12-16', 'C13');

-- Lịch chiếu (7 ngày)
INSERT INTO showtimes (movieID, roomID, showDate, showTime, basePrice, status)
SELECT m.movieID, r.roomID, DATE_ADD(CURDATE(), INTERVAL d.day_offset DAY), t.showTime, 45000, 'available'
FROM (SELECT 1 as movieID UNION SELECT 2 UNION SELECT 3) m
CROSS JOIN (SELECT 1 as roomID UNION SELECT 2) r
CROSS JOIN (SELECT 0 as day_offset UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6) d
CROSS JOIN (SELECT '09:00:00' as showTime UNION SELECT '12:00:00' UNION SELECT '15:00:00' UNION SELECT '18:00:00' UNION SELECT '21:00:00') t
WHERE m.movieID IN (SELECT movieID FROM movie WHERE movieStatus = 'now_showing');

SELECT 'Sample data inserted successfully!' as status;
