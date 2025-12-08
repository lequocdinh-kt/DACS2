<?php 
// Chỉ cho phép truy cập qua index.php
if (!defined('INCLUDED_FROM_INDEX')) {
    header('Location: /index.php');
    exit;
}

require_once __DIR__ . '/../models/movie_db.php';
require_once __DIR__ . '/../models/showtime_db.php';

// Get movie ID from URL
$movieID = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($movieID <= 0) {
    header('Location: /index.php');
    exit;
}

// Get movie details
$movie = get_movie_by_id($movieID);

if (!$movie) {
    header('Location: /index.php');
    exit;
}

// Get upcoming showtimes for this movie
$upcomingShowtimes = get_upcoming_showtimes_by_movie($movieID, 7); // Next 7 days

// Helper function to extract YouTube video ID
function get_youtube_id($url) {
    if (empty($url)) return null;
    preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $url, $matches);
    return isset($matches[1]) ? $matches[1] : null;
}

$youtubeID = get_youtube_id($movie['trailerURL']);
?>

    <main class="movie-detail-main">
        <!-- Hero Section with Background -->
        <section class="movie-hero" style="background-image: url('<?php echo htmlspecialchars($movie['posterHorizontalURL'] ?: $movie['posterURL']); ?>');">
            <div class="hero-overlay"></div>
            <div class="hero-content">
                <div class="container">
                    <div class="movie-hero-wrapper">
                        <!-- Poster -->
                        <div class="movie-poster-large">
                            <img src="<?php echo htmlspecialchars($movie['posterURL']); ?>" 
                                 alt="<?php echo htmlspecialchars($movie['title']); ?>">
                            <?php if ($movie['movieStatus'] === 'now_showing'): ?>
                                <span class="status-badge now-showing">
                                    <i class="fas fa-play-circle"></i> Đang chiếu
                                </span>
                            <?php elseif ($movie['movieStatus'] === 'coming_soon'): ?>
                                <span class="status-badge coming-soon">
                                    <i class="fas fa-calendar-alt"></i> Sắp chiếu
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Movie Info -->
                        <div class="movie-info-main">
                            <h1 class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></h1>
                            
                            <div class="movie-rating-large">
                                <div class="rating-stars">
                                    <i class="fas fa-star"></i>
                                    <span class="rating-value"><?php echo $movie['rating']; ?></span>
                                    <span class="rating-max">/10</span>
                                </div>
                                <span class="age-rating"><?php echo htmlspecialchars($movie['ageRating'] ?: 'P'); ?></span>
                            </div>

                            <div class="movie-meta-grid">
                                <div class="meta-item">
                                    <i class="fas fa-clock"></i>
                                    <span class="meta-label">Thời lượng:</span>
                                    <span class="meta-value"><?php echo $movie['duration']; ?> phút</span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-tag"></i>
                                    <span class="meta-label">Thể loại:</span>
                                    <span class="meta-value"><?php echo htmlspecialchars($movie['genre']); ?></span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-calendar"></i>
                                    <span class="meta-label">Khởi chiếu:</span>
                                    <span class="meta-value"><?php echo date('d/m/Y', strtotime($movie['releaseDate'])); ?></span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-globe"></i>
                                    <span class="meta-label">Quốc gia:</span>
                                    <span class="meta-value"><?php echo htmlspecialchars($movie['country'] ?: 'N/A'); ?></span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-language"></i>
                                    <span class="meta-label">Ngôn ngữ:</span>
                                    <span class="meta-value"><?php echo htmlspecialchars($movie['language'] ?: 'N/A'); ?></span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-user-tie"></i>
                                    <span class="meta-label">Đạo diễn:</span>
                                    <span class="meta-value"><?php echo htmlspecialchars($movie['author'] ?: 'N/A'); ?></span>
                                </div>
                            </div>

                            <div class="movie-actions">
                                <?php if ($movie['movieStatus'] === 'now_showing' && !empty($upcomingShowtimes)): ?>
                                    <a href="booking_step1_showtimes.php?movieID=<?php echo $movie['movieID']; ?>" class="btn-booking-large">
                                        <i class="fas fa-ticket-alt"></i> Đặt vé ngay
                                    </a>
                                <?php endif; ?>
                                <?php if ($youtubeID): ?>
                                    <button class="btn-trailer-large" onclick="openTrailerModal('<?php echo $youtubeID; ?>')">
                                        <i class="fas fa-play"></i> Xem trailer
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Movie Details Content -->
        <section class="movie-content">
            <div class="container">
                <!-- Description -->
                <div class="content-section">
                    <h2 class="section-title">
                        <i class="fas fa-align-left"></i> Nội dung phim
                    </h2>
                    <div class="movie-description">
                        <p><?php echo nl2br(htmlspecialchars($movie['description'])); ?></p>
                    </div>
                </div>

                <!-- Trailer Section -->
                <?php if ($youtubeID): ?>
                <div class="content-section">
                    <h2 class="section-title">
                        <i class="fas fa-video"></i> Trailer
                    </h2>
                    <div class="trailer-container">
                        <iframe 
                            width="100%" 
                            height="500" 
                            src="https://www.youtube.com/embed/<?php echo $youtubeID; ?>" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                        </iframe>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Showtimes -->
                <?php if ($movie['movieStatus'] === 'now_showing' && !empty($upcomingShowtimes)): ?>
                <div class="content-section">
                    <h2 class="section-title">
                        <i class="fas fa-clock"></i> Lịch chiếu sắp tới
                    </h2>
                    <div class="showtimes-list">
                        <?php 
                        $showtimesByDate = [];
                        foreach ($upcomingShowtimes as $showtime) {
                            $date = $showtime['showDate'];
                            if (!isset($showtimesByDate[$date])) {
                                $showtimesByDate[$date] = [];
                            }
                            $showtimesByDate[$date][] = $showtime;
                        }
                        ?>
                        <?php foreach ($showtimesByDate as $date => $showtimes): ?>
                            <div class="showtime-date-group">
                                <h3 class="showtime-date">
                                    <i class="fas fa-calendar-day"></i>
                                    <?php 
                                    $dateObj = new DateTime($date);
                                    $today = new DateTime();
                                    $tomorrow = (clone $today)->modify('+1 day');
                                    
                                    if ($dateObj->format('Y-m-d') === $today->format('Y-m-d')) {
                                        echo 'Hôm nay - ';
                                    } elseif ($dateObj->format('Y-m-d') === $tomorrow->format('Y-m-d')) {
                                        echo 'Ngày mai - ';
                                    }
                                    echo $dateObj->format('d/m/Y');
                                    ?>
                                </h3>
                                <div class="showtime-buttons">
                                    <?php foreach ($showtimes as $showtime): ?>
                                        <?php
                                        $availableSeats = $showtime['totalSeats'] - $showtime['bookedSeats'];
                                        $isAvailable = $availableSeats > 0;
                                        ?>
                                        <a href="<?php echo $isAvailable ? 'booking_step2_seats.php?showtimeID=' . $showtime['showtimeID'] : '#'; ?>" 
                                           class="showtime-btn <?php echo !$isAvailable ? 'disabled' : ''; ?>">
                                            <div class="showtime-time">
                                                <?php echo date('H:i', strtotime($showtime['showTime'])); ?>
                                            </div>
                                            <div class="showtime-info">
                                                <span class="showtime-format"><?php echo htmlspecialchars($showtime['format']); ?></span>
                                                <span class="showtime-seats">
                                                    <?php if ($isAvailable): ?>
                                                        <i class="fas fa-chair"></i> <?php echo $availableSeats; ?> ghế
                                                    <?php else: ?>
                                                        <i class="fas fa-ban"></i> Hết vé
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="view-all-showtimes">
                        <a href="booking_step1_showtimes.php?movieID=<?php echo $movie['movieID']; ?>" class="btn-view-all">
                            <i class="fas fa-calendar-alt"></i> Xem tất cả lịch chiếu
                        </a>
                    </div>
                </div>
                <?php elseif ($movie['movieStatus'] === 'coming_soon'): ?>
                <div class="content-section">
                    <div class="coming-soon-notice">
                        <i class="fas fa-calendar-alt"></i>
                        <h3>Phim sắp chiếu</h3>
                        <p>Phim sẽ được công chiếu từ ngày <?php echo date('d/m/Y', strtotime($movie['releaseDate'])); ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

<!-- Trailer Modal -->
<div id="trailerModal" class="trailer-modal">
    <div class="trailer-modal-overlay" onclick="closeTrailerModal()"></div>
    <div class="trailer-modal-content">
        <button class="trailer-close-btn" onclick="closeTrailerModal()">
            <i class="fas fa-times"></i>
        </button>
        <div class="trailer-video-wrapper">
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

<script src="src/js/movie_detail.js"></script>
