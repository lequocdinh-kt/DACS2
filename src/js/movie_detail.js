// Movie Detail Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // console.log('Movie detail page loaded');
    initializeTrailerModal();
    loadMovieReviews();
});

/**
 * Initialize trailer modal functionality
 */
function initializeTrailerModal() {
    const modal = document.getElementById('trailerModal');
    if (!modal) return;

    const overlay = modal.querySelector('.trailer-modal-overlay');
    const closeBtn = modal.querySelector('.trailer-close-btn');

    // Close on overlay click
    if (overlay) {
        overlay.addEventListener('click', closeTrailerModal);
    }

    // Close on button click
    if (closeBtn) {
        closeBtn.addEventListener('click', closeTrailerModal);
    }

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            closeTrailerModal();
        }
    });
}

/**
 * Open trailer modal with video ID
 * @param {string} videoID - YouTube video ID
 */
function openTrailerModal(videoID) {
    const modal = document.getElementById('trailerModal');
    const videoWrapper = modal.querySelector('.trailer-video-wrapper');
    
    if (!modal || !videoWrapper) {
        // console.error('Trailer modal elements not found');
        return;
    }

    // Create iframe with autoplay
    const iframe = document.createElement('iframe');
    iframe.src = `https://www.youtube.com/embed/${videoID}?autoplay=1&rel=0&modestbranding=1`;
    iframe.frameBorder = '0';
    iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
    iframe.allowFullscreen = true;

    // Clear existing content and add iframe
    videoWrapper.innerHTML = '';
    videoWrapper.appendChild(iframe);

    // Show modal with animation
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

/**
 * Close trailer modal and stop video
 */
function closeTrailerModal() {
    const modal = document.getElementById('trailerModal');
    const videoWrapper = modal.querySelector('.trailer-video-wrapper');
    
    if (!modal || !videoWrapper) return;

    // Remove iframe to stop video
    videoWrapper.innerHTML = '';

    // Hide modal
    modal.classList.remove('active');
    document.body.style.overflow = '';
}

/**
 * Format date for display
 * @param {Date} date - Date to format
 * @returns {string} Formatted date string
 */
function formatShowtimeDate(date) {
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);

    // Reset time for comparison
    today.setHours(0, 0, 0, 0);
    tomorrow.setHours(0, 0, 0, 0);
    date.setHours(0, 0, 0, 0);

    if (date.getTime() === today.getTime()) {
        return 'Hôm nay';
    } else if (date.getTime() === tomorrow.getTime()) {
        return 'Ngày mai';
    } else {
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        return `${day}/${month}`;
    }
}

/**
 * Check if showtime is bookable (not in the past)
 * @param {string} showtimeDate - Date string
 * @param {string} showtimeTime - Time string
 * @returns {boolean} True if bookable
 */
function isShowtimeBookable(showtimeDate, showtimeTime) {
    const now = new Date();
    const showtime = new Date(`${showtimeDate} ${showtimeTime}`);
    return showtime > now;
}

/**
 * Handle booking button click
 * @param {number} showtimeID - Showtime ID
 * @param {number} availableSeats - Number of available seats
 */
function handleBooking(showtimeID, availableSeats) {
    if (availableSeats <= 0) {
        alert('Xin lỗi, suất chiếu này đã hết chỗ.');
        return;
    }

    // Redirect to booking page
    window.location.href = `index.php?page=booking_step2_seats&showtimeID=${showtimeID}`;
}

/**
 * Scroll to showtimes section
 */
function scrollToShowtimes() {
    const showtimesSection = document.getElementById('showtimes-section');
    if (showtimesSection) {
        showtimesSection.scrollIntoView({ 
            behavior: 'smooth',
            block: 'start'
        });
    }
}

/**
 * Toggle description expansion (for long descriptions)
 */
function toggleDescription() {
    const description = document.querySelector('.movie-description p');
    if (!description) return;

    if (description.classList.contains('expanded')) {
        description.classList.remove('expanded');
        description.style.maxHeight = '150px';
    } else {
        description.classList.add('expanded');
        description.style.maxHeight = 'none';
    }
}

/**
 * Share movie on social media
 * @param {string} platform - Social media platform
 */
function shareMovie(platform) {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent(document.querySelector('.movie-title').textContent);
    
    let shareUrl = '';
    
    switch(platform) {
        case 'facebook':
            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
            break;
        case 'twitter':
            shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
            break;
        case 'telegram':
            shareUrl = `https://t.me/share/url?url=${url}&text=${title}`;
            break;
        default:
            console.error('Unknown platform:', platform);
            return;
    }
    
    window.open(shareUrl, '_blank', 'width=600,height=400');
}

/**
 * Copy movie link to clipboard
 */
function copyMovieLink() {
    const url = window.location.href;
    
    navigator.clipboard.writeText(url).then(() => {
        // Show success message
        showNotification('Đã sao chép link phim!');
    }).catch(err => {
        console.error('Failed to copy:', err);
        showNotification('Không thể sao chép link', 'error');
    });
}

/**
 * Show notification message
 * @param {string} message - Message to display
 * @param {string} type - Notification type (success, error)
 */
function showNotification(message, type = 'success') {
    // Remove existing notification
    const existing = document.querySelector('.notification-toast');
    if (existing) {
        existing.remove();
    }

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification-toast ${type}`;
    notification.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? 'linear-gradient(135deg, #00ff88 0%, #00d4ff 100%)' : 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)'};
        color: ${type === 'success' ? '#0a0e1a' : 'white'};
        padding: 15px 25px;
        border-radius: 30px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
        z-index: 10000;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Add animation styles
if (!document.getElementById('notification-animations')) {
    const style = document.createElement('style');
    style.id = 'notification-animations';
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
}

/**
 * Load movie reviews
 */
let currentReviews = [];
let displayedReviewsCount = 5;

async function loadMovieReviews() {
    const movieID = getMovieIDFromURL();
    if (!movieID) {
        console.error('No movie ID found');
        return;
    }

    console.log('Loading reviews for movie:', movieID);

    const reviewsList = document.getElementById('reviewsList');
    const noReviews = document.getElementById('noReviews');
    const avgRatingEl = document.getElementById('avgRating');
    const avgStarsEl = document.getElementById('avgStars');
    const totalReviewsEl = document.getElementById('totalReviews');

    try {
        const url = `/src/controllers/reviewController.php?movieID=${movieID}`;
        // console.log('Fetching from:', url);
        
        const response = await fetch(url);
        const text = await response.text();
        // console.log('Raw response:', text);
        
        const data = JSON.parse(text);
        // console.log('Parsed data:', data);

        if (data.success && data.reviews) {
            currentReviews = data.reviews;
            console.log('Total reviews:', currentReviews.length);

            // Update stats
            avgRatingEl.textContent = data.avgRating || '0.0';
            avgStarsEl.innerHTML = generateStars(data.avgRating || 0);
            totalReviewsEl.textContent = `${data.totalReviews || 0} đánh giá`;

            if (currentReviews.length > 0) {
                displayReviews();
                reviewsList.style.display = 'block';
                noReviews.style.display = 'none';
            } else {
                reviewsList.style.display = 'none';
                noReviews.style.display = 'flex';
            }
        } else {
            console.error('API returned error:', data.message);
            reviewsList.style.display = 'none';
            noReviews.style.display = 'flex';
        }
    } catch (error) {
        console.error('Error loading reviews:', error);
        reviewsList.innerHTML = '<div class="reviews-error"><i class="fas fa-exclamation-triangle"></i><p>Không thể tải đánh giá: ' + error.message + '</p></div>';
    }
}

/**
 * Display reviews with pagination
 */
function displayReviews() {
    const reviewsList = document.getElementById('reviewsList');
    const reviewsActions = document.getElementById('reviewsActions');
    
    const reviewsToShow = currentReviews.slice(0, displayedReviewsCount);
    
    reviewsList.innerHTML = reviewsToShow.map(review => createReviewCard(review)).join('');

    // Show/hide load more button
    if (displayedReviewsCount < currentReviews.length) {
        reviewsActions.style.display = 'block';
    } else {
        reviewsActions.style.display = 'none';
    }
}

/**
 * Load more reviews
 */
function loadMoreReviews() {
    displayedReviewsCount += 5;
    displayReviews();
}

/**
 * Create review card HTML
 */
function createReviewCard(review) {
    const date = new Date(review.createdAt);
    const formattedDate = date.toLocaleDateString('vi-VN', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });

    return `
        <div class="review-card">
            <div class="review-header">
                <div class="review-user">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-info">
                        <h4>${escapeHtml(review.userName)}</h4>
                        <span class="review-date">${formattedDate}</span>
                    </div>
                </div>
                <div class="review-rating">
                    ${generateStars(review.rating)}
                    <span class="rating-value">${review.rating}/10</span>
                </div>
            </div>
            <div class="review-content">
                <p>${escapeHtml(review.comment)}</p>
            </div>
        </div>
    `;
}

/**
 * Generate star rating HTML
 */
function generateStars(rating) {
    const fullStars = Math.floor(rating / 2); // Convert 10-point to 5-star
    const hasHalfStar = (rating % 2) >= 1;
    const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);

    let starsHTML = '';
    
    // Full stars
    for (let i = 0; i < fullStars; i++) {
        starsHTML += '<i class="fas fa-star"></i>';
    }
    
    // Half star
    if (hasHalfStar) {
        starsHTML += '<i class="fas fa-star-half-alt"></i>';
    }
    
    // Empty stars
    for (let i = 0; i < emptyStars; i++) {
        starsHTML += '<i class="far fa-star"></i>';
    }
    
    return starsHTML;
}

/**
 * Get movie ID from URL
 */
function getMovieIDFromURL() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('id');
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
