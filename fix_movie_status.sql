-- Fix SQL: Sửa lại movieStatus thành tiếng Anh
-- Chạy script này để update dữ liệu đã insert

-- Cách 1: Update dữ liệu hiện có
UPDATE Movie 
SET movieStatus = 'now_showing' 
WHERE movieStatus = 'đang chiếu';

UPDATE Movie 
SET movieStatus = 'coming_soon' 
WHERE movieStatus = 'sắp chiếu';

UPDATE Movie 
SET movieStatus = 'stopped' 
WHERE movieStatus = 'ngừng chiếu';

-- Cách 2: Xóa và insert lại với giá trị đúng
DELETE FROM Movie WHERE movieID IN (1, 2, 3);

INSERT INTO Movie (
  movieID, title, genre, duration, description, rating, movieStatus, posterURL, trailerURL, author, releaseDate
) VALUES
(1, 
 'Cục vàng của ngoại', 
 'Gia đình, Tâm Lý', 
 119, 
 'Lấy cảm hứng từ những ký ức tuổi thơ ngọt ngào, "Cục Vàng Của Ngoại" mang đến câu chuyện ấm áp về tình bà cháu trong một xóm nhỏ chan chứa nghĩa tình. Bà Hậu – người phụ nữ cả đời tần tảo, nay trở thành chỗ dựa duy nhất của cháu ngoại khi con gái bỏ đi. Dẫu cuộc sống còn nhiều nhọc nhằn, tình thương bà dành cho cháu vẫn luôn trọn vẹn. Với bà, cháu là "cục vàng" – niềm vui, niềm an ủi và cũng là lẽ sống của đời mình. Bộ phim nhẹ nhàng dẫn khán giả trở lại những khoảnh khắc quen thuộc nơi xóm nhỏ: nụ cười hồn nhiên của cháu, vòng tay chở che của bà và sự đùm bọc từ hàng xóm láng giềng. Tất cả cùng hòa thành một bức tranh đời thường ấm áp, gợi nhắc về tuổi thơ bình yên và tình người mộc mạc, chân thành.', 
 4, 
 'now_showing', 
 'src/img/moviesVertical/cucvangcuangoai.jpg', 
 'https://youtu.be/YPCtgD0KnGk', 
 'Khương Ngọc', 
 '2025-10-16 00:00:00'),

(2, 
 'Gió vẫn thổi', 
 'Hoạt hình', 
 127, 
 'Lấy bối cảnh Nhật Bản trong thời kỳ Taishō và Shōwa, The Wind Rises kể về Jirō Horikoshi – chàng trai mang ước mơ bay lượn giữa bầu trời, dù đôi mắt cận không cho phép. Trong những giấc mơ, anh được nhà thiết kế máy bay Caproni truyền cảm hứng, và ngoài đời, Jirō trở thành kỹ sư hàng không tài năng. Sau trận đại động đất Kantō, anh gặp Nahoko – cô gái dịu dàng và lạc quan. Tình yêu chớm nở giữa khung cảnh bình yên của Karuizawa, rồi kết trái bằng một cuộc hôn nhân đầy hy vọng. Nhưng bệnh lao phổi của Nahoko ngày càng trở nặng... Trong khi đất nước dấn sâu vào chiến tranh, Jirō lao vào thiết kế mẫu tiêm kích thử nghiệm với tất cả đam mê – giằng xé giữa lý tưởng bay cao và hiện thực cay nghiệt của thời đại.', 
 3, 
 'now_showing', 
 'src/img/moviesVertical/giovantho1.jpg', 
 'https://youtu.be/rp9VsYzVltw', 
 'Hayao Miyazaki', 
 '2025-10-17 00:00:00'),

(3, 
 'Tee Yod: Quỷ ăn tạng phần 3', 
 'Kinh dị', 
 104, 
 'Yak và gia đình phải đối mặt với nỗi kinh hoàng mới khi "Yee" – cô em út – đột ngột mất tích bí ẩn. Yak buộc phải cùng Yos, Yod và Papan lên đường đến "Bong Sa Noh Bian" – khu rừng ma ám – để cứu Yee trước khi những linh hồn tà ác một lần nữa bị đánh thức.', 
 4, 
 'now_showing', 
 'src/img/moviesVertical/quyantang1.jpg', 
 'https://youtu.be/DMOGnGokm4c', 
 'Narit Yuvaboon', 
 '2025-10-10 00:00:00');

-- Kiểm tra kết quả
SELECT movieID, title, movieStatus, rating, releaseDate FROM Movie;
