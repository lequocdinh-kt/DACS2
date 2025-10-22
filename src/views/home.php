<?php 
// Session đã được start ở index.php, không cần start lại
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../controllers/homeController.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VKU Cinema - Trang Chủ</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;900&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/src/styles/home.css">
</head>
<body>

    <!-- Hero Banner Slider -->
    <section class="hero-banner">
        <div class="banner-slider">
            <?php if (!empty($bannerMovies)): ?>
                <?php foreach ($bannerMovies as $index => $movie): ?>
                    <div class="banner-slide <?php echo $index === 0 ? 'active' : ''; ?>" data-movie-id="<?php echo $movie['movieID']; ?>">
                        <img src="<?php echo htmlspecialchars(!empty($movie['posterHorizontalURL']) ? $movie['posterHorizontalURL'] : $movie['posterURL']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                        <div class="banner-overlay">
                            <div class="banner-content">
                                <h1 class="banner-title"><?php echo strtoupper(htmlspecialchars($movie['title'])); ?></h1>
                                <p class="banner-description"><?php echo htmlspecialchars(substr($movie['description'], 0, 150)) . '...'; ?></p>
                                <div class="banner-meta">
                                    <span><i class="fas fa-clock"></i> <?php echo format_duration($movie['duration']); ?></span>
                                    <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($movie['genre']); ?></span>
                                    <span><i class="fas fa-star"></i> <?php echo $movie['rating']; ?></span>
                                </div>
                                <div class="banner-buttons">
                                    <a href="/src/views/booking.php?movieID=<?php echo $movie['movieID']; ?>" class="btn-primary">
                                        <i class="fas fa-ticket-alt"></i> Đặt vé ngay
                                    </a>
                                    <a href="/src/views/movie_detail.php?id=<?php echo $movie['movieID']; ?>" class="btn-secondary">
                                        <i class="fas fa-info-circle"></i> Chi tiết
                                    </a>
                                    <?php if (!empty($movie['trailerURL'])): ?>
                                        <?php $videoId = get_youtube_video_id($movie['trailerURL']); ?>
                                        <?php if ($videoId): ?>
                                            <button class="btn-trailer" onclick="openTrailer('<?php echo $videoId; ?>', '<?php echo htmlspecialchars($movie['title']); ?>')">
                                                <i class="fas fa-play"></i> Trailer
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="banner-slide active">
                    <img src="/src/img/posters/default.jpg" alt="VKU Cinema">
                    <div class="banner-overlay">
                        <div class="banner-content">
                            <h1 class="banner-title">CHÀO MỪNG ĐẾN VKU CINEMA</h1>
                            <p class="banner-description">Trải nghiệm điện ảnh đỉnh cao</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php if (count($bannerMovies) > 1): ?>
            <div class="banner-controls">
                <button class="banner-prev"><i class="fas fa-chevron-left"></i></button>
                <button class="banner-next"><i class="fas fa-chevron-right"></i></button>
            </div>
            <div class="banner-dots"></div>
        <?php endif; ?>
    </section>

    <!-- Now Showing Movies -->
    <section class="movies-section" id="now-showing">
        <div class="container">
            <div class="section-header">
                <h2>PHIM ĐANG CHIẾU</h2>
                <div class="header-info">
                    <span class="movie-count"><?php echo $nowShowingCount; ?> phim</span>
                    <a href="/src/views/movies.php?status=now_showing" class="view-all">
                        Xem tất cả <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <?php if (!empty($nowShowingMovies)): ?>
                <div class="movies-slider">
                    <button class="slider-btn prev" onclick="slideMovies('next', 'nowShowingTrack')">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div class="movies-slider-wrapper">
                        <div class="movies-track" id="nowShowingTrack">
                        <?php foreach ($nowShowingMovies as $movie): ?>
                            <div class="movie-card-small">
                                <div class="small-poster">
                                    <img src="<?php echo htmlspecialchars($movie['posterURL']); ?>" 
                                         alt="<?php echo htmlspecialchars($movie['title']); ?>">
                                    <div class="small-overlay">
                                        <?php if (!empty($movie['trailerURL'])): ?>
                                            <?php $videoId = get_youtube_video_id($movie['trailerURL']); ?>
                                            <?php if ($videoId): ?>
                                                <button class="btn-play-small" onclick="openTrailer('<?php echo $videoId; ?>', '<?php echo htmlspecialchars($movie['title']); ?>')">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($movie['rating'] >= 8.0): ?>
                                        <span class="movie-badge hot">HOT</span>
                                    <?php elseif ($movie['rating'] >= 7.0): ?>
                                        <span class="movie-badge new">MỚI</span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-info">
                                    <h4><?php echo htmlspecialchars($movie['title']); ?></h4>
                                    <p class="small-genre">
                                        <i class="fas fa-tag"></i> 
                                        <?php echo htmlspecialchars(substr($movie['genre'], 0, 20)); ?>
                                    </p>
                                    <p class="small-duration">
                                        <i class="fas fa-clock"></i> <?php echo format_duration($movie['duration']); ?>
                                    </p>
                                    <div class="small-rating">
                                        <i class="fas fa-star"></i>
                                        <span><?php echo $movie['rating']; ?></span>
                                    </div>
                                    <a href="/src/views/booking.php?movieID=<?php echo $movie['movieID']; ?>" 
                                       class="btn-booking-small">
                                        <i class="fas fa-ticket-alt"></i> Đặt vé
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    </div>
                    <button class="slider-btn next" onclick="slideMovies('prev', 'nowShowingTrack')">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            <?php else: ?>
                <div class="no-movies">
                    <i class="fas fa-film"></i>
                    <p>Hiện chưa có phim nào đang chiếu</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Coming Soon Movies -->
    <section class="movies-section coming-soon">
        <div class="container">
            <div class="section-header">
                <h2>PHIM SẮP CHIẾU</h2>
                <div class="header-info">
                    <span class="movie-count"><?php echo $comingSoonCount; ?> phim</span>
                    <a href="/src/views/movies.php?status=coming_soon" class="view-all">
                        Xem tất cả <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <?php if (!empty($comingSoonMovies)): ?>
                <div class="movies-slider">
                    <button class="slider-btn prev" onclick="slideMovies('prev', 'comingSoonTrack')">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div class="movies-slider-wrapper">
                        <div class="movies-track" id="comingSoonTrack">
                        <?php foreach ($comingSoonMovies as $movie): ?>
                            <div class="movie-card-small">
                                <div class="small-poster">
                                    <img src="<?php echo htmlspecialchars($movie['posterURL']); ?>" 
                                         alt="<?php echo htmlspecialchars($movie['title']); ?>">
                                    <div class="small-overlay">
                                        <?php if (!empty($movie['trailerURL'])): ?>
                                            <?php $videoId = get_youtube_video_id($movie['trailerURL']); ?>
                                            <?php if ($videoId): ?>
                                                <button class="btn-play-small" onclick="openTrailer('<?php echo $videoId; ?>', '<?php echo htmlspecialchars($movie['title']); ?>')">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                    <span class="coming-badge">SOON</span>
                                </div>
                                <div class="card-info">
                                    <h4><?php echo htmlspecialchars($movie['title']); ?></h4>
                                    <p class="small-genre">
                                        <i class="fas fa-tag"></i> 
                                        <?php echo htmlspecialchars(substr($movie['genre'], 0, 20)); ?>
                                    </p>
                                    <?php if (!empty($movie['releaseDate'])): ?>
                                        <?php $countdown = get_countdown_to_release($movie['releaseDate']); ?>
                                        <?php if ($countdown !== null): ?>
                                            <div class="countdown-timer" 
                                                 data-release-date="<?php echo $movie['releaseDate']; ?>"
                                                 data-movie-id="<?php echo $movie['movieID']; ?>">
                                                <i class="fas fa-clock"></i>
                                                <span class="countdown-text">
                                                    <span class="days"><?php echo $countdown['days']; ?></span> ngày 
                                                    <span class="hours"><?php echo $countdown['hours']; ?></span> giờ 
                                                    <span class="minutes"><?php echo $countdown['minutes']; ?></span> phút
                                                </span>
                                            </div>
                                        <?php else: ?>
                                            <p class="release-date">
                                                <i class="fas fa-calendar-alt"></i>
                                                <?php echo format_release_date($movie['releaseDate']); ?>
                                            </p>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <div class="small-rating">
                                        <i class="fas fa-star"></i>
                                        <span><?php echo $movie['rating']; ?></span>
                                    </div>
                                    <a href="/src/views/movie_detail.php?id=<?php echo $movie['movieID']; ?>" 
                                       class="btn-detail-small">
                                        <i class="fas fa-info-circle"></i> Chi tiết
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    </div>
                    <button class="slider-btn next" onclick="slideMovies('next', 'comingSoonTrack')">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            <?php else: ?>
                <div class="no-movies">
                    <i class="fas fa-calendar-alt"></i>
                    <p>Hiện chưa có phim sắp chiếu</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- News & Promotions -->
    <section class="news-section">
        <div class="container">
            <div class="section-header">
                <h2>TIN TỨC & ƯU ĐÃI</h2>
                <a href="/src/views/news.php" class="view-all">
                    Xem tất cả <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="news-grid">
                <div class="news-card">
                    <!-- <img src="/src/img/news/promo1.jpg" alt="Promotion 1"> -->
                    <div class="news-content">
                        <span class="news-tag">Ưu đãi</span>
                        <h3>Giảm 30% vé cho sinh viên</h3>
                        <p>Chương trình ưu đãi đặc biệt dành riêng cho sinh viên VKU. Xuất trình thẻ sinh viên để nhận ưu đãi...</p>
                        <a href="#">Xem thêm <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="news-card">
                    <!-- <img src="/src/img/news/promo2.jpg" alt="Promotion 2"> -->
                    <div class="news-content">
                        <span class="news-tag">Sự kiện</span>
                        <h3>Đêm hội phim Châu Á</h3>
                        <p>Tham gia cùng chúng tôi trong đêm hội phim Châu Á với những tác phẩm đặc sắc nhất...</p>
                        <a href="#">Xem thêm <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="news-card">
                    <!-- <img src="/src/img/news/promo3.jpg" alt="Promotion 3"> -->
                    <div class="news-content">
                        <span class="news-tag">Tin tức</span>
                        <h3>Khai trương phòng chiếu IMAX</h3>
                        <p>VKU Cinema tự hào giới thiệu phòng chiếu IMAX đầu tiên tại Đà Nẵng với trải nghiệm đỉnh cao...</p>
                        <a href="#">Xem thêm <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- YouTube Trailer Modal -->
    <div id="trailerModal" class="trailer-modal">
        <div class="trailer-modal-overlay" onclick="closeTrailer()"></div>
        <div class="trailer-modal-content">
            <button class="trailer-close-btn" onclick="closeTrailer()">
                <i class="fas fa-times"></i>
            </button>
            <h2 id="trailerTitle" class="trailer-title">Trailer</h2>
            <div class="trailer-video-container">
                <iframe id="trailerIframe" 
                        width="100%" 
                        height="100%" 
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen>
                </iframe>
            </div>
        </div>
    </div>


    <script src="/src/js/home.js"></script>
</body>
</html>