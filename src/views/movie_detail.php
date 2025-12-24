<?php 
// Chỉ cho phép truy cập qua index.php
if (!defined('INCLUDED_FROM_INDEX')) {
    header('Location: /index.php');
    exit;
}

require_once __DIR__ . '/../models/movie_db.php';

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
                                <?php if ($movie['movieStatus'] === 'now_showing'): ?>
                                    <?php if (isset($_SESSION['userID'])): ?>
                                        <a href="/src/views/booking_step1_showtimes.php?movieID=<?php echo $movie['movieID']; ?>" class="btn-booking-large">
                                            <i class="fas fa-ticket-alt"></i> Đặt vé ngay
                                        </a>
                                    <?php else: ?>
                                        <a href="javascript:void(0)" onclick="openAuthModal('login')" class="btn-booking-large">
                                            <i class="fas fa-ticket-alt"></i> Đặt vé ngay
                                        </a>
                                    <?php endif; ?>
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


            </div>
        </section>

        <!-- Reviews Section -->
        <section class="reviews-section">
            <div class="container">
                <div class="reviews-header">
                    <h2><i class="fas fa-star"></i> Đánh giá từ khán giả</h2>
                    <div class="reviews-stats">
                        <div class="average-rating">
                            <span class="rating-number" id="avgRating">0.0</span>
                            <div class="rating-stars" id="avgStars"></div>
                            <span class="total-reviews" id="totalReviews">0 đánh giá</span>
                        </div>
                    </div>
                </div>

                <!-- Reviews List -->
                <div class="reviews-list" id="reviewsList">
                    <div class="reviews-loading">
                        <i class="fas fa-spinner fa-spin"></i>
                        <p>Đang tải đánh giá...</p>
                    </div>
                </div>

                <!-- No Reviews Message -->
                <div class="no-reviews" id="noReviews" style="display: none;">
                    <i class="fas fa-comment-slash"></i>
                    <p>Chưa có đánh giá nào cho phim này</p>
                    <p class="subtitle">Hãy là người đầu tiên đánh giá!</p>
                </div>

                <!-- Load More Button -->
                <div class="reviews-actions" id="reviewsActions" style="display: none;">
                    <button class="btn-load-more" id="btnLoadMore" onclick="loadMoreReviews()">
                        <i class="fas fa-chevron-down"></i> Xem thêm đánh giá
                    </button>
                </div>
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
