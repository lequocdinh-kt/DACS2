-- ============================================
-- VKU CINEMA - CẬP NHẬT PHIM MỚI
-- Dữ liệu phim từ Metiz.vn
-- ============================================

USE dacs2;

-- Xóa dữ liệu cũ (nếu cần reset hoàn toàn)
-- DELETE FROM showtimes;
-- DELETE FROM movie;

-- ============================================
-- PHIM ĐANG CHIẾU (Now Showing)
-- ============================================

INSERT INTO movie (title, genre, duration, description, rating, movieStatus, posterURL, trailerURL, author, releaseDate, ageRating) VALUES

-- 1. Truy Tìm Long Diên Hương
('Truy Tìm Long Diên Hương', 'Hành Động, Hài', 103, 
 'Báu vật làng biển Long Diên Hương bị đánh cắp, mở ra cuộc hành trình truy tìm đầy kịch tính. Không chỉ có võ thuật mãn nhãn, bộ phim còn mang đến tiếng cười, sự gắn kết và những giá trị nhân văn của con người làng chài.', 
 9.0, 'now_showing', 'https://metiz.vn/media/poster_film/truy_t_m_long_di_n_h_ng_-_payoff_poster_kc_14112025.jpg',
 'https://youtu.be/3U3HuAA2Y3M', 'Dương Minh Chiến', '2024-11-12', 'C16'),

-- 2. Phi Vụ Động Trời 2 (Zootopia 2)
('Phi Vụ Động Trời 2', 'Phiêu Lưu, Hành Động, Hoạt Hình, Gia Đình', 107, 
 'ZOOTOPIA 2 trở lại sau 9 năm! Đội OTP Nick & Judy tiếp tục những cuộc phiêu lưu mới đầy hài hước và cảm động trong thành phố động vật sôi động. Một hành trình đầy bất ngờ và ý nghĩa về tình bạn, lòng dũng cảm và sự công bằng.', 
 8.0, 'now_showing', 'https://metiz.vn/media/poster_film/cgv_350x495_1_1_eb7PImW.jpg',
 'https://youtu.be/zrTpAHB6NZY', 'Jared Bush, Byron Howard', '2024-11-27', 'P'),

-- 3. Phi Vụ Thế Kỷ - Thoắt Ẩn Thoắt Hiện (Now You See Me 3)
('Phi Vụ Thế Kỷ - Thoắt Ẩn Thoắt Hiện', 'Tội Phạm, Hồi Hộp', 113, 
 'Tứ Kỵ Sĩ chính thức tái xuất, bắt tay cùng các tân binh ảo thuật gia Gen Z trong một phi vụ đánh cắp kim cương liều lĩnh nhất trong sự nghiệp. Họ phải đối đầu với bà trùm Veronika của đế chế rửa tiền nhà Vandenberg - một người phụ nữ quyền lực và đầy thủ đoạn. Khi kinh nghiệm lão làng va chạm với công nghệ 4.0, liệu ai sẽ làm chủ cuộc chơi?', 
 9.0, 'now_showing', 'https://metiz.vn/media/poster_film/teaser_poster_phi_vu_the_ky_1.jpg',
 'https://youtu.be/b_cXd5blWQU', 'Ruben Fleischer', '2024-11-28', 'C13'),

-- 4. Gangster Về Làng
('Gangster Về Làng', 'Hài, Tình Cảm, Kịch Tính', 105, 
 'Baek Sung-chul chỉ còn một tháng để thoát án tử. Anh cải trang, ẩn mình trong một ngôi làng hẻo lánh nhưng lại là tâm điểm biểu tình. Khi tìm thấy tình yêu với cô gái Bora, gã giang hồ buộc phải mang mặt nạ đom đóm đứng lên chiến đấu, đối mặt với quá khứ. Anh sẽ tìm thấy sự cứu rỗi hay bị nhấn chìm mãi mãi?', 
 8.0, 'now_showing', 'https://metiz.vn/media/poster_film/406x600px-murderer.jpg',
 'https://www.youtube.com/watch?v=AKp50Ia4zwk', 'Kim Hee-Sung', '2024-11-28', 'C16'),

-- 5. Phiên Chợ Của Quỷ
('Phiên Chợ Của Quỷ', 'Kinh Dị', 97, 
 'Phiên chợ của quỷ - Nơi linh hồn trở thành những món hàng để thỏa mãn tham vọng của con người. Mỗi đêm, cổng chợ âm sẽ mở, quỷ sẽ bắt hồn. Một khi đã dám bán rẻ linh hồn, cái giá phải trả có thể là máu, là thịt, hoặc chính sự tồn tại của những kẻ dám liều mạng. Nỗi ám ảnh không lối thoát với phim tâm linh - kinh dị hợp tác Việt - Hàn quỷ dị nhất dịp cuối năm!', 
 7.0, 'now_showing', 'https://metiz.vn/media/poster_film/457x590-the_cursed.jpg',
 'https://youtu.be/Ul92PIniNpQ', 'Hong Won-ki', '2024-11-28', 'C18'),

-- 6. Phòng Trọ Ma Bầu
('Phòng Trọ Ma Bầu', 'Hài, Kinh Dị', 102, 
 'Hai người bạn thân thuê phải một căn phòng trọ cũ, nơi liên tục xảy ra những hiện tượng kỳ bí. Trong hành trình tìm hiểu, họ đối mặt với hồn ma của một người phụ nữ mang thai – "ma bầu". Ẩn sau nỗi ám ảnh rùng rợn là bi kịch và tình yêu mẫu tử thiêng liêng, nơi sự hy sinh của người mẹ trở thành sợi dây kết nối những thế hệ.', 
 9.0, 'now_showing', 'https://metiz.vn/media/poster_film/ma_bau.jpg',
 'https://youtu.be/DYoc217HIXo', 'Ngụy Minh Khang', '2024-11-28', 'C16');


-- ============================================
-- PHIM SẮP CHIẾU (Coming Soon)
-- ============================================

INSERT INTO movie (title, genre, duration, description, rating, movieStatus, posterURL, trailerURL, author, releaseDate, ageRating) VALUES

-- 1. 5 Centimet Trên Giây
('5 Centimet Trên Giây', 'Hoạt Hình, Tình Cảm', 76,
 'Câu chuyện cảm động về Takaki và Akari, đôi bạn thuở thiếu thời dần bị chia cắt bởi thời gian và khoảng cách. Qua ba giai đoạn khác nhau trong cuộc đời, hành trình khắc họa những ký ức, cuộc hội ngộ và sự xa cách của cặp đôi, với hình ảnh hoa anh đào rơi – 5cm/giây – như ẩn dụ cho tình yêu mong manh và thoáng chốc của tuổi trẻ.',
 9.0, 'coming_soon', 'https://metiz.vn/media/poster_film/5cm_logo_localize_mkt_material_digital_470x700.jpg',
 'https://youtu.be/xAfE3SOfjow', 'Shinkai Makoto', '2025-12-05', 'C13'),

-- 2. Chú Thuật Hồi Chiến x Tử Diệt Hồi Du
('Chú Thuật Hồi Chiến: Biến Cố Shibuya', 'Hoạt Hình, Hành Động', 88,
 'Sau bao ngày chờ đợi, Đại Chiến Shibuya cuối cùng cũng xuất hiện trên màn ảnh rộng, gom trọn những khoảnh khắc nghẹt thở nhất thành một cú nổ đúng nghĩa. Không chỉ tái hiện toàn bộ cơn ác mộng tại Shibuya, bộ phim còn hé lộ những bí mật then chốt và mở màn cho trò chơi sinh tử "Tử Diệt Hồi Du" đầy kịch tính và mãn nhãn.',
 9.0, 'coming_soon', 'https://metiz.vn/media/poster_film/jujutsu-kaisen-execution-2025-4_1764214883066.jpg',
 'https://youtu.be/HjWmrI5cz60', 'Goshozono Shouta', '2025-12-05', 'C16'),

-- 3. Cô Hầu Gái
('Cô Hầu Gái', 'Bí Ẩn, Hồi Hộp', 97,
 'Từ đạo diễn Paul Feig, một thế giới hỗn loạn sẽ mở ra, nơi sự hoàn hảo chỉ là ảo giác và mọi thứ dường như đều đang che đậy một bí mật đằng sau. Để chạy trốn khỏi quá khứ, Millie trở thành bảo mẫu cho gia đình giàu có. Nhưng ngay khi cô chuyển vào sống chung và bắt đầu công việc "trong mơ", sự thật dần được hé lộ - đằng sau vẻ ngoài xa hoa lộng lẫy là mối nguy lớn hơn bất cứ thứ gì Millie có thể tưởng tượng.',
 8.0, 'coming_soon', 'https://metiz.vn/media/poster_film/poster_co_hau_gai_7.jpg',
 'https://youtu.be/iJM-pjTsoQ0', 'Paul Feig', '2025-12-26', 'C18'),

-- 4. Nhà Hai Chủ
('Nhà Hai Chủ', 'Kinh Dị', 95,
 'Một gia đình nhỏ vì không đủ điều kiện đã phải mua một căn nhà mà người dân xung quanh đồn đoán rằng có nhiều điều kỳ lạ tâm linh. Liệu gia đình sẽ đối mặt với ngôi nhà nhiều chủ sẽ như thế nào? Một câu chuyện kinh dị đầy ám ảnh về những bí mật đen tối ẩn giấu trong ngôi nhà cũ kỹ.',
 7.0, 'coming_soon', 'https://metiz.vn/media/poster_film/nha_hai_chu.jpg',
 'https://youtu.be/qFBh-ANevrw', 'Trần Duy Linh, Phạm Trung Hiếu', '2025-12-26', 'C18'),

-- 5. SpongeBob - Lời Nguyền Hải Tặc
('SpongeBob - Lời Nguyền Hải Tặc', 'Phiêu Lưu, Hài, Hoạt Hình', 90,
 'SpongeBob phiêu lưu xuống đáy đại dương để đối mặt với hồn ma của Người Hà Lan bay, vượt qua thử thách và khám phá những bí mật dưới biển. Một cuộc hành trình đầy tiếng cười và phiêu lưu mạo hiểm dành cho cả gia đình.',
 9.0, 'coming_soon', 'https://metiz.vn/media/poster_film/botbien.jpg',
 'https://youtu.be/C7ppJK8cQnM', 'Derek Drymon', '2025-12-26', 'P');
-- ============================================
-- LỊCH CHIẾU (20 NGÀY) - CHỈ CHO PHIM ĐANG CHIẾU
-- ============================================

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

-- ============================================
-- THÔNG BÁO HOÀN TẤT
-- ============================================

SELECT 'Movies and showtimes updated successfully!' as status;
SELECT COUNT(*) as total_movies FROM movie;
SELECT COUNT(*) as total_showtimes FROM showtimes;
