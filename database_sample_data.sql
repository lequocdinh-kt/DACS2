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

-- Phim mẫu (Đang chiếu - Now Showing)
INSERT INTO movie (title, genre, duration, description, rating, movieStatus, posterURL, trailerURL, author, releaseDate, ageRating) VALUES
('Kraven The Hunter', 'Hành Động, Phiêu Lưu', 127, 
 'Kraven the Hunter là câu chuyện về việc một nhân vật phản diện mang tính biểu tượng của thế giới Marvel được tạo ra như thế nào. Được biết đến là một trong những thợ săn xuất sắc nhất, Sergei Kravinoff (Aaron Taylor-Johnson) có bản năng săn mồi bẩm sinh từ khi còn nhỏ, và ông đã dành cả cuộc đời để chứng minh rằng mình là chiến binh mạnh mẽ nhất của những kẻ săn mồi.', 
 7.5, 'now_showing', '/src/img/moviesVertical/kraven-the-hunter.jpg',
 'https://www.youtube.com/watch?v=bRbt40A0bSw', 'J.C. Chandor', '2024-12-13', 'C16'),
 
('Mufasa: The Lion King', 'Hoạt Hình, Phiêu Lưu, Gia Đình', 118,
 'Mufasa: The Lion King khai thác câu chuyện về huyền thoại Mufasa thông qua Rafiki, người kể lại cho Kiara - con gái của Simba và Nala - với Timon và Pumbaa góp thêm tính hài hước đặc trưng của họ. Bộ phim, được kể qua những cảnh hồi tưởng, giới thiệu Mufasa, một chú sư tử con mồ côi, lạc lõng và cô đơn cho đến khi gặp một con sư tử đầy thương cảm tên là Taka - người là con trai của dòng dõi hoàng gia.',
 8.0, 'now_showing', '/src/img/moviesVertical/mufasa.jpg',
 'https://www.youtube.com/watch?v=o17MF9vnabg', 'Barry Jenkins', '2024-12-20', 'P'),

('Sonic 3', 'Hành Động, Phiêu Lưu, Gia Đình', 110,
 'Sonic, Knuckles và Tails tái hợp để đối đầu với một đối thủ mạnh mẽ chưa từng thấy - Shadow, một phản diện bí ẩn sở hữu sức mạnh chưa từng có. Với năng lực vượt xa khả năng của nhóm, Team Sonic phải tìm kiếm một liên minh bất ngờ để ngăn chặn Shadow và bảo vệ hành tinh khỏi thảm họa diệt vong.',
 7.8, 'now_showing', '/src/img/moviesVertical/sonic-3.jpg',
 'https://www.youtube.com/watch?v=qSu6i2iFMO0', 'Jeff Fowler', '2024-12-25', 'P'),

('Wicked', 'Nhạc Kịch, Phiêu Lưu, Kỳ Ảo', 160,
 'Wicked kể về câu chuyện chưa được kể của những Nữ phù thủy xứ Oz. Bộ phim theo chân hành trình của hai nữ sinh trẻ thay đổi cuộc đời tại Đại học Shiz trong vùng đất kỳ diệu xứ Oz, họ phát triển một tình bạn bất ngờ và gắn bó sâu sắc. Nhưng sau một cuộc gặp gỡ định mệnh với Pháp sư xứ Oz, tình bạn của họ rơi vào ngã rẽ và số phận dẫn họ đến việc thực hiện những định mệnh rất khác nhau.',
 8.2, 'now_showing', '/src/img/moviesVertical/wicked.jpg',
 'https://www.youtube.com/watch?v=6COmYeLsz4c', 'Jon M. Chu', '2024-11-22', 'P'),

('Ngài Quỷ', 'Kinh Dị, Bí Ẩn', 108,
 'Phim xoay quanh câu chuyện về một người đàn ông bí ẩn xuất hiện ở một ngôi làng hẻo lánh, tự xưng là người có thể giải quyết mọi vấn đề của dân làng. Tuy nhiên, mỗi khi ông ta giải quyết một vấn đề, một cái chết lạ lùng lại xảy ra. Cảnh sát địa phương bắt đầu điều tra và phát hiện ra những bí mật đen tối đằng sau con người này.',
 7.2, 'now_showing', '/src/img/moviesVertical/ngai-quy.jpg',
 'https://www.youtube.com/watch?v=example', 'Kim Jee-woon', '2024-12-06', 'C18'),

('Làm Giàu Với Ma', 'Hài, Kinh Dị', 112,
 'Làm Giàu Với Ma xoay quanh Lanh (Tuấn Trần) - con trai của ông Đạo làm nghề mai táng (Hoài Linh), lâm vào đường cùng vì cờ bạc. Trong cơn túng quẫn, "duyên tình" đẩy đưa anh gặp một ma nữ (Lê Giang) và cùng nhau thực hiện những "công việc" để phục vụ những mục đích khác nhau. Khởi đầu từ những vụ việc éo le, cả nhóm dần dần vướng vào những rắc rối lớn hơn.',
 7.0, 'now_showing', '/src/img/moviesVertical/lam-giau-voi-ma.jpg',
 'https://www.youtube.com/watch?v=example', 'Trung Lùn', '2024-11-29', 'C16'),

('Moana 2', 'Hoạt Hình, Phiêu Lưu, Gia Đình', 100,
 'Sau khi nhận được một lời gọi bất ngờ từ tổ tiên định hướng đại dương, Moana phải hành trình đến những vùng biển xa xôi của Châu Đại Dương và vào những vùng nước nguy hiểm đã mất tích từ lâu để có một cuộc phiêu lưu khác thường trước đây.',
 7.6, 'now_showing', '/src/img/moviesVertical/moana-2.jpg',
 'https://www.youtube.com/watch?v=hDZ7y8RP5HE', 'David Derrick Jr.', '2024-11-29', 'P'),

('Tee Yod: Quỷ Ăn Tạng 2', 'Kinh Dị', 111,
 'Sau cái chết của bà Yai, Yak (Nadech Kugimiya) đã phải sống cùng hồn ma của Tee Yod. Nhưng chuyện gì sẽ xảy ra khi ma quỷ muốn thoát khỏi anh và trở về để báo thù gia đình Thongphetch mà có người trong quá khứ đã giết chết nó. Một trận chiến mới giữa Yak và Tee Yod sắp bắt đầu.',
 6.8, 'now_showing', '/src/img/moviesVertical/tee-yod-2.jpg',
 'https://www.youtube.com/watch?v=example', 'Taweewat Wantha', '2024-11-14', 'C18'),

-- Phim sắp chiếu (Coming Soon)
('Cười Xuyên Biên Giới', 'Hài, Gia Đình', 95,
 'Phim kể về câu chuyện của một gia đình Việt Nam di cư sang Mỹ và những tình huống hài hước, ấm áp xảy ra khi họ cố gắng hòa nhập vào cuộc sống mới trong khi vẫn giữ gìn những giá trị truyền thống.',
 0, 'coming_soon', '/src/img/moviesVertical/cuoi-xuyen-bien-gioi.jpg',
 'https://www.youtube.com/watch?v=example', 'Trấn Thành', '2025-01-10', 'P'),

('Linh Miêu', 'Kinh Dị, Tâm Lý', 109,
 'Linh Miêu xoay quanh câu chuyện về một gia đình có truyền thống nuôi mèo từ thế hệ này sang thế hệ khác. Khi những hiện tượng lạ bắt đầu xảy ra, họ nhận ra rằng con mèo cưng của gia đình không phải là một sinh vật bình thường.',
 0, 'coming_soon', '/src/img/moviesVertical/linh-mieu.jpg',
 'https://www.youtube.com/watch?v=example', 'Lưu Thành Luân', '2025-01-17', 'C16'),

('Captain America: Brave New World', 'Hành Động, Khoa Học Viễn Tưởng', 135,
 'Sau những sự kiện của The Falcon and The Winter Soldier, Sam Wilson chính thức trở thành Captain America mới. Anh phải đối mặt với những thách thức lớn lao khi một âm mưu toàn cầu đe dọa trật tự thế giới.',
 0, 'coming_soon', '/src/img/moviesVertical/captain-america-brave-new-world.jpg',
 'https://www.youtube.com/watch?v=H_jezAhz47w', 'Julius Onah', '2025-02-14', 'C13'),

('Snow White', 'Kỳ Ảo, Nhạc Kịch, Gia Đình', 113,
 'Phiên bản live-action của câu chuyện cổ tích nổi tiếng về nàng Bạch Tuyết và bảy chú lùn. Một cô gái trẻ xinh đẹp trốn thoát khỏi mẹ kế độc ác và tìm thấy sự bảo vệ với bảy người bạn mới trong khu rừng.',
 0, 'coming_soon', '/src/img/moviesVertical/snow-white.jpg',
 'https://www.youtube.com/watch?v=TdvtRgBHSoU', 'Marc Webb', '2025-03-21', 'P'),

('Thunderbolts', 'Hành Động, Khoa Học Viễn Tưởng', 130,
 'Một nhóm gồm các phản diện và phản anh hùng được tập hợp lại bởi chính phủ để thực hiện những nhiệm vụ nguy hiểm. Thunderbolts khám phá ranh giới mong manh giữa anh hùng và kẻ xấu trong vũ trụ Marvel.',
 0, 'coming_soon', '/src/img/moviesVertical/thunderbolts.jpg',
 'https://www.youtube.com/watch?v=example', 'Jake Schreier', '2025-05-02', 'C13');

-- Lịch chiếu (20 ngày) - Chỉ cho phim đang chiếu
INSERT INTO showtimes (movieID, roomID, showDate, showTime, basePrice, status)
SELECT m.movieID, r.roomID, DATE_ADD(CURDATE(), INTERVAL d.day_offset DAY), t.showTime, 
       CASE 
           WHEN r.roomType = 'IMAX' THEN 65000
           WHEN r.roomType = '3D' THEN 55000
           ELSE 45000
       END as basePrice,
       'available'
FROM movie m
CROSS JOIN rooms r
CROSS JOIN (
    SELECT 0 as day_offset UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 
    UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9
    UNION SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14
    UNION SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19
) d
CROSS JOIN (
    SELECT '09:00:00' as showTime UNION SELECT '11:30:00' UNION SELECT '14:00:00' 
    UNION SELECT '16:30:00' UNION SELECT '19:00:00' UNION SELECT '21:30:00'
) t
WHERE m.movieStatus = 'now_showing';

SELECT 'Sample data inserted successfully!' as status;
