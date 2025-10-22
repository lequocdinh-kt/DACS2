// Hero Banner Slider
let currentSlide = 0;
const slides = document.querySelectorAll('.banner-slide');
const dotsContainer = document.querySelector('.banner-dots');

if (slides.length > 0 && dotsContainer) {
    // Create dots
    slides.forEach((_, index) => {
        const dot = document.createElement('div');
        dot.classList.add('banner-dot');
        if (index === 0) dot.classList.add('active');
        dot.addEventListener('click', () => goToSlide(index));
        dotsContainer.appendChild(dot);
    });

    const dots = document.querySelectorAll('.banner-dot');

    function goToSlide(n) {
        slides[currentSlide].classList.remove('active');
        dots[currentSlide].classList.remove('active');
        currentSlide = n;
        if (currentSlide >= slides.length) currentSlide = 0;
        if (currentSlide < 0) currentSlide = slides.length - 1;
        slides[currentSlide].classList.add('active');
        dots[currentSlide].classList.add('active');
    }

    const nextBtn = document.querySelector('.banner-next');
    const prevBtn = document.querySelector('.banner-prev');

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            goToSlide(currentSlide + 1);
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            goToSlide(currentSlide - 1);
        });
    }

    // Auto slide every 5 seconds
    if (slides.length > 1) {
        setInterval(() => {
            goToSlide(currentSlide + 1);
        }, 5000);
    }
}


// Load showtimes when movie and date are selected
document.getElementById('selectMovie')?.addEventListener('change', loadShowtimes);
document.getElementById('selectDate')?.addEventListener('change', loadShowtimes);

function loadShowtimes() {
    const movieID = document.getElementById('selectMovie').value;
    const date = document.getElementById('selectDate').value;
    const showtimeSelect = document.getElementById('selectShowtime');
    
    if (!movieID || !date) {
        showtimeSelect.innerHTML = '<option value="">-- Chọn suất --</option>';
        return;
    }
    
    // AJAX call to get showtimes (tạm thời dùng dữ liệu mẫu)
    // TODO: Thay bằng API call thực tế khi có bảng Showtime
    showtimeSelect.innerHTML = `
        <option value="">-- Chọn suất --</option>
        <option value="1">09:00 - Phòng 1</option>
        <option value="2">12:00 - Phòng 2</option>
        <option value="3">15:00 - Phòng 1</option>
        <option value="4">18:00 - Phòng 3</option>
        <option value="5">21:00 - Phòng 2</option>
    `;
}

// Coming Soon Movies Slider
let currentPosition = 0;
let nowShowingPosition = 0;
const track = document.getElementById('comingSoonTrack');
const nowShowingTrack = document.getElementById('nowShowingTrack');
let isSliding = false;

function slideMovies(direction, trackId = 'comingSoonTrack') {
    if (isSliding) return;
    
    const currentTrack = document.getElementById(trackId);
    if (!currentTrack) {
        console.log('Track not found:', trackId);
        return;
    }
    
    isSliding = true;
    const cards = currentTrack.querySelectorAll('.movie-card-small');
    if (cards.length === 0) {
        isSliding = false;
        console.log('No cards found');
        return;
    }
    
    const cardWidth = cards[0].offsetWidth + 20; // width + gap
    const wrapper = currentTrack.parentElement;
    const wrapperWidth = wrapper.offsetWidth;
    const visibleCards = Math.floor(wrapperWidth / cardWidth);
    const maxPosition = -(cards.length - visibleCards) * cardWidth;
    
    console.log('Debug:', {
        trackId,
        cardsCount: cards.length,
        cardWidth,
        wrapperWidth,
        visibleCards,
        maxPosition
    });
    
    // Nếu tất cả cards đều hiển thị, không cần slide
    if (maxPosition >= 0) {
        console.log('All cards visible, no need to slide');
        isSliding = false;
        return;
    }
    
    // Sử dụng biến position riêng cho mỗi track
    let position = trackId === 'nowShowingTrack' ? nowShowingPosition : currentPosition;
    
    if (direction === 'next') {
        // Nút phải: đi từ trái sang phải (giảm position để slide sang trái)
        position -= cardWidth * visibleCards;
        // Nếu đã hết phim, quay lại đầu
        if (position < maxPosition) {
            position = 0;
        }
    } else {
        // Nút trái: đi từ phải sang trái (tăng position để slide sang phải)
        position += cardWidth * visibleCards;
        // Nếu đã về đầu, nhảy đến cuối
        if (position > 0) {
            position = maxPosition;
        }
    }
    
    // Lưu lại position cho track tương ứng
    if (trackId === 'nowShowingTrack') {
        nowShowingPosition = position;
    } else {
        currentPosition = position;
    }
    
    currentTrack.style.transform = `translateX(${position}px)`;
    
    console.log('Sliding:', trackId, direction, 'Position:', position);
    
    setTimeout(() => {
        isSliding = false;
    }, 600);
}

// Expose function to global scope
window.slideMovies = slideMovies;

// Auto slide for coming soon movies every 5 seconds
if (track) {
    const cards = track.querySelectorAll('.movie-card-small');
    if (cards.length > 4) {
        setInterval(() => {
            slideMovies('next', 'comingSoonTrack');
        }, 5000);
    }
}

// Auto slide for now showing movies every 5 seconds
if (nowShowingTrack) {
    const cards = nowShowingTrack.querySelectorAll('.movie-card-small');
    if (cards.length > 4) {
        setInterval(() => {
            slideMovies('next', 'nowShowingTrack');
        }, 5000);
    }
}

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href !== '#' && href !== '') {
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    });
});

// Set minimum date for booking to today
const dateInput = document.getElementById('selectDate');
if (dateInput) {
    const today = new Date().toISOString().split('T')[0];
    dateInput.setAttribute('min', today);
    dateInput.value = today; // Set default to today
}

// Movie card hover effects
document.querySelectorAll('.movie-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.classList.add('hovered');
    });
    
    card.addEventListener('mouseleave', function() {
        this.classList.remove('hovered');
    });
});

// Lazy loading images
if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                }
                observer.unobserve(img);
            }
        });
    });

    document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
    });
}

// Console log for debugging
console.log('Home page loaded successfully');
console.log('Total banner slides:', slides.length);
console.log('Coming soon track:', track ? 'Found' : 'Not found');

// ==================== COUNTDOWN TIMER ====================

/**
 * Cập nhật countdown timer cho phim sắp chiếu
 */
function updateCountdowns() {
    const countdownElements = document.querySelectorAll('.countdown-timer');
    
    countdownElements.forEach(element => {
        const releaseDate = new Date(element.dataset.releaseDate);
        const now = new Date();
        
        // Tính toán thời gian còn lại (milliseconds)
        const diff = releaseDate - now;
        
        // Nếu đã phát hành (diff <= 0), ẩn countdown
        if (diff <= 0) {
            element.innerHTML = '<i class="fas fa-check-circle"></i> <span>Đã phát hành</span>';
            element.style.background = 'linear-gradient(135deg, rgba(255, 215, 0, 0.1) 0%, rgba(255, 165, 0, 0.1) 100%)';
            element.style.borderColor = 'rgba(255, 215, 0, 0.3)';
            element.style.color = '#ffd700';
            return;
        }
        
        // Chuyển đổi milliseconds thành ngày, giờ, phút, giây
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);
        
        // Cập nhật hiển thị
        const countdownText = element.querySelector('.countdown-text');
        if (countdownText) {
            if (days > 0) {
                countdownText.innerHTML = `
                    <span class="days">${days}</span> ngày 
                    <span class="hours">${hours}</span> giờ 
                    <span class="minutes">${minutes}</span> phút
                `;
            } else if (hours > 0) {
                countdownText.innerHTML = `
                    <span class="hours">${hours}</span> giờ 
                    <span class="minutes">${minutes}</span> phút 
                    <span class="seconds">${seconds}</span> giây
                `;
            } else {
                countdownText.innerHTML = `
                    <span class="minutes">${minutes}</span> phút 
                    <span class="seconds">${seconds}</span> giây
                `;
            }
        }
    });
}

// Cập nhật countdown ngay lập tức
updateCountdowns();

// Cập nhật mỗi giây
setInterval(updateCountdowns, 1000);

console.log('Countdown timers initialized:', document.querySelectorAll('.countdown-timer').length);

// ==================== YOUTUBE TRAILER MODAL ====================

/**
 * Mở modal trailer YouTube
 * @param {string} videoId - YouTube video ID
 * @param {string} title - Tên phim
 */
function openTrailer(videoId, title) {
    const modal = document.getElementById('trailerModal');
    const iframe = document.getElementById('trailerIframe');
    const titleElement = document.getElementById('trailerTitle');
    
    if (!modal || !iframe) {
        console.error('Modal elements not found');
        return;
    }
    
    // Set title
    if (titleElement) {
        titleElement.textContent = title + ' - Trailer';
    }
    
    // Set iframe src với autoplay
    const embedUrl = `https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0&modestbranding=1`;
    iframe.src = embedUrl;
    
    // Show modal
    modal.classList.add('active');
    document.body.style.overflow = 'hidden'; // Prevent body scroll
    
    console.log('Opening trailer:', videoId, title);
}

/**
 * Đóng modal trailer
 */
function closeTrailer() {
    const modal = document.getElementById('trailerModal');
    const iframe = document.getElementById('trailerIframe');
    
    if (!modal || !iframe) return;
    
    // Hide modal
    modal.classList.remove('active');
    document.body.style.overflow = ''; // Restore body scroll
    
    // Stop video by clearing iframe src
    iframe.src = '';
    
    console.log('Closing trailer');
}

// Đóng modal khi nhấn ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeTrailer();
    }
});

/**
 * Kiểm tra đăng nhập trước khi đặt vé
 */
function checkLoginBeforeBooking(event, movieID) {
    // Kiểm tra xem có session userID không (PHP sẽ set biến này)
    if (typeof isUserLoggedIn !== 'undefined' && !isUserLoggedIn) {
        event.preventDefault(); // Ngăn chặn chuyển trang
        
        // Mở modal đăng nhập
        if (typeof openAuthModal === 'function') {
            openAuthModal('login');
            
            // Lưu movieID vào sessionStorage để sau khi đăng nhập sẽ redirect đến trang đặt vé
            sessionStorage.setItem('pendingBooking', movieID);
        } else {
            // Fallback nếu không có modal - redirect đến trang login
            window.location.href = '/src/views/login.php?redirect=/src/views/booking_step1_showtimes.php?movieID=' + movieID;
        }
        
        return false;
    }
    
    return true;
}

/**
 * Sau khi đăng nhập thành công, redirect đến trang đặt vé nếu có
 */
function checkPendingBooking() {
    const pendingMovieID = sessionStorage.getItem('pendingBooking');
    if (pendingMovieID) {
        sessionStorage.removeItem('pendingBooking');
        window.location.href = '/src/views/booking_step1_showtimes.php?movieID=' + pendingMovieID;
    }
}

// Kiểm tra pending booking khi trang load
document.addEventListener('DOMContentLoaded', function() {
    // Chỉ check nếu user đã đăng nhập
    if (typeof isUserLoggedIn !== 'undefined' && isUserLoggedIn) {
        checkPendingBooking();
    }
});

// Expose functions to global scope
window.openTrailer = openTrailer;
window.closeTrailer = closeTrailer;
window.checkLoginBeforeBooking = checkLoginBeforeBooking;