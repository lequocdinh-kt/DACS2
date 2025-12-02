/**
 * VKU Cinema - Home Page JavaScript
 * Tối ưu và ngắn gọn
 */

// ==================== GLOBAL VARIABLES ====================
let currentSlide = 0;
let nowShowingPosition = 0;
let comingSoonPosition = 0;
let isSliding = false;

// ==================== BANNER SLIDER ====================
function initBannerSlider() {
    const slides = document.querySelectorAll('.banner-slide');
    const dotsContainer = document.querySelector('.banner-dots');
    
    if (slides.length === 0 || !dotsContainer) return;
    
    // Tạo dots navigation
    slides.forEach((_, index) => {
        const dot = document.createElement('div');
        dot.classList.add('banner-dot');
        if (index === 0) dot.classList.add('active');
        dot.onclick = () => goToSlide(index);
        dotsContainer.appendChild(dot);
    });
    
    const dots = document.querySelectorAll('.banner-dot');
    
    function updateBannerButtons(slideIndex) {
        const activeSlide = slides[slideIndex];
        const movieID = activeSlide.dataset.movieId;
        
        console.log('Updating buttons for slide:', slideIndex, 'movieID:', movieID);
        
        if (!movieID) {
            console.warn('No movieID found for slide:', slideIndex);
            return;
        }
        
        // Tìm tất cả các nút trong tất cả slides
        slides.forEach((slide, idx) => {
            const bannerContent = slide.querySelector('.banner-content');
            if (!bannerContent) return;
            
            // Nếu đây là slide active, cập nhật buttons
            if (idx === slideIndex) {
                // Cập nhật nút đặt vé
                const bookingBtn = bannerContent.querySelector('.btn-primary');
                if (bookingBtn) {
                    bookingBtn.href = `/src/views/booking_step1_showtimes.php?movieID=${movieID}`;
                    bookingBtn.setAttribute('onclick', `return checkLoginBeforeBooking(event, ${movieID})`);
                    console.log('Updated booking button href:', bookingBtn.href);
                }
                
                // Cập nhật nút chi tiết
                const detailBtn = bannerContent.querySelector('.btn-secondary');
                if (detailBtn) {
                    detailBtn.href = `/src/views/movie_detail.php?id=${movieID}`;
                    console.log('Updated detail button href:', detailBtn.href);
                }
                
                // Cập nhật nút trailer
                const trailerBtn = bannerContent.querySelector('.btn-trailer');
                if (trailerBtn) {
                    // Trailer button dùng onclick với videoId, không cần update
                    console.log('Trailer button found (uses onclick)');
                }
            }
        });
    }
    
    function goToSlide(n) {
        // Xóa active class
        slides[currentSlide].classList.remove('active');
        dots[currentSlide].classList.remove('active');
        
        // Tính slide mới
        currentSlide = (n + slides.length) % slides.length;
        
        // Thêm active class
        slides[currentSlide].classList.add('active');
        dots[currentSlide].classList.add('active');
        
        // Cập nhật buttons ngay lập tức
        setTimeout(() => {
            updateBannerButtons(currentSlide);
        }, 50);
    }
    
    // Nút điều khiển
    document.querySelector('.banner-next')?.addEventListener('click', () => goToSlide(currentSlide + 1));
    document.querySelector('.banner-prev')?.addEventListener('click', () => goToSlide(currentSlide - 1));
    
    // Cập nhật buttons cho slide đầu tiên
    setTimeout(() => {
        updateBannerButtons(0);
    }, 100);
    
    // Auto slide 8s (tăng từ 5s lên 8s)
    if (slides.length > 1) {
        setInterval(() => goToSlide(currentSlide + 1), 8000);
    }
}

// ==================== MOVIE SLIDER ====================
function slideMovies(direction, trackId) {
    // Chỉ hoạt động trên desktop (>480px)
    if (window.innerWidth <= 480) return;
    
    if (isSliding) return;
    
    const track = document.getElementById(trackId);
    if (!track) return;
    
    const cards = track.querySelectorAll('.movie-card-small');
    if (cards.length === 0) return;
    
    isSliding = true;
    
    const cardWidth = cards[0].offsetWidth + 20;
    const wrapperWidth = track.parentElement.offsetWidth;
    const visibleCards = Math.floor(wrapperWidth / cardWidth);
    const maxPosition = -(cards.length - visibleCards) * cardWidth;
    
    // Nếu tất cả cards hiển thị, không slide
    if (maxPosition >= 0) {
        isSliding = false;
        return;
    }
    
    // Lấy position hiện tại theo đúng track
    let position;
    if (trackId === 'nowShowingTrack') {
        position = nowShowingPosition;
    } else if (trackId === 'comingSoonTrack') {
        position = comingSoonPosition;
    } else {
        position = 0; // fallback
    }
    
    // Tính position mới
    if (direction === 'prev') {
        // Prev (←): Slider di chuyển sang trái, hiện phim trước
        const newPosition = position - cardWidth * visibleCards;
        // Nếu vượt quá trái thì quay về cuối (phải)
        position = newPosition < maxPosition ? 0 : newPosition;
    } else {
        // Next (→): Slider di chuyển sang phải, hiện phim sau
        const newPosition = position + cardWidth * visibleCards;
        // Nếu vượt quá phải thì quay về đầu (trái)
        position = newPosition > 0 ? maxPosition : newPosition;
    }
    
    // Lưu position theo đúng track
    if (trackId === 'nowShowingTrack') {
        nowShowingPosition = position;
    } else if (trackId === 'comingSoonTrack') {
        comingSoonPosition = position;
    }
    
    track.style.transform = `translateX(${position}px)`;
    
    setTimeout(() => isSliding = false, 600);
}

// Auto slide cho movie tracks (chỉ trên desktop)
function initAutoSlide() {
    if (window.innerWidth <= 480) return; // Tắt auto-slide trên mobile
    
    ['nowShowingTrack', 'comingSoonTrack'].forEach(trackId => {
        const track = document.getElementById(trackId);
        if (track?.querySelectorAll('.movie-card-small').length > 4) {
            setInterval(() => slideMovies('next', trackId), 5000);
        }
    });
}

// ==================== TOUCH SWIPE FOR MOBILE ====================
function initTouchSwipe() {
    if (window.innerWidth > 480) return; // Chỉ kích hoạt trên mobile
    
    document.querySelectorAll('.movies-slider-wrapper').forEach(wrapper => {
        let startX = 0;
        let startY = 0;
        let scrollLeft = 0;
        let isDown = false;
        let isHorizontalScroll = false;
        
        wrapper.addEventListener('touchstart', (e) => {
            isDown = true;
            startX = e.touches[0].pageX - wrapper.offsetLeft;
            startY = e.touches[0].pageY;
            scrollLeft = wrapper.scrollLeft;
            isHorizontalScroll = false;
        });
        
        wrapper.addEventListener('touchmove', (e) => {
            if (!isDown) return;
            
            const x = e.touches[0].pageX - wrapper.offsetLeft;
            const y = e.touches[0].pageY;
            const walkX = Math.abs(x - startX);
            const walkY = Math.abs(y - startY);
            
            // Xác định hướng scroll: ngang hay dọc
            if (!isHorizontalScroll && (walkX > 5 || walkY > 5)) {
                isHorizontalScroll = walkX > walkY;
            }
            
            // Chỉ prevent default khi scroll ngang
            if (isHorizontalScroll) {
                e.preventDefault();
                const walk = (x - startX) * 2; // Scroll speed
                wrapper.scrollLeft = scrollLeft - walk;
            }
        });
        
        wrapper.addEventListener('touchend', () => {
            isDown = false;
            isHorizontalScroll = false;
        });
    });
}

// ==================== COUNTDOWN TIMER ====================
function updateCountdowns() {
    document.querySelectorAll('.countdown-timer').forEach(element => {
        const releaseDate = new Date(element.dataset.releaseDate);
        const diff = releaseDate - new Date();
        
        if (diff <= 0) {
            element.innerHTML = '<i class="fas fa-check-circle"></i> <span>Đã phát hành</span>';
            element.style.cssText = 'background: linear-gradient(135deg, rgba(255,215,0,0.1), rgba(255,165,0,0.1)); border-color: rgba(255,215,0,0.3); color: #ffd700;';
            return;
        }
        
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);
        
        const countdownText = element.querySelector('.countdown-text');
        if (countdownText) {
            if (days > 0) {
                countdownText.innerHTML = `<span class="days">${days}</span> ngày <span class="hours">${hours}</span> giờ <span class="minutes">${minutes}</span> phút`;
            } else if (hours > 0) {
                countdownText.innerHTML = `<span class="hours">${hours}</span> giờ <span class="minutes">${minutes}</span> phút <span class="seconds">${seconds}</span> giây`;
            } else {
                countdownText.innerHTML = `<span class="minutes">${minutes}</span> phút <span class="seconds">${seconds}</span> giây`;
            }
        }
    });
}

// ==================== TRAILER MODAL ====================
function openTrailer(videoId, title) {
    const modal = document.getElementById('trailerModal');
    const iframe = document.getElementById('trailerIframe');
    const titleElement = document.getElementById('trailerTitle');
    
    if (!modal || !iframe) return;
    
    if (titleElement) titleElement.textContent = `${title} - Trailer`;
    iframe.src = `https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0&modestbranding=1`;
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeTrailer() {
    const modal = document.getElementById('trailerModal');
    const iframe = document.getElementById('trailerIframe');
    
    if (!modal || !iframe) return;
    
    modal.classList.remove('active');
    document.body.style.overflow = '';
    iframe.src = '';
}

// ==================== BOOKING CHECK ====================
function checkLoginBeforeBooking(event, movieID) {
    if (typeof isUserLoggedIn !== 'undefined' && !isUserLoggedIn) {
        event.preventDefault();
        
        if (typeof openAuthModal === 'function') {
            openAuthModal('login');
            sessionStorage.setItem('pendingBooking', movieID);
        } else {
            window.location.href = `/src/views/login.php?redirect=/src/views/booking_step1_showtimes.php?movieID=${movieID}`;
        }
        return false;
    }
    return true;
}

function checkPendingBooking() {
    const pendingMovieID = sessionStorage.getItem('pendingBooking');
    if (pendingMovieID) {
        sessionStorage.removeItem('pendingBooking');
        window.location.href = `/src/views/booking_step1_showtimes.php?movieID=${pendingMovieID}`;
    }
}

// ==================== LAZY LOADING ====================
function initLazyLoading() {
    if (!('IntersectionObserver' in window)) return;
    
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
    
    document.querySelectorAll('img[data-src]').forEach(img => imageObserver.observe(img));
}

// ==================== SMOOTH SCROLL ====================
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href !== '') {
                e.preventDefault();
                document.querySelector(href)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
}

// ==================== INITIALIZATION ====================
document.addEventListener('DOMContentLoaded', () => {
    initBannerSlider();
    initAutoSlide();
    initTouchSwipe(); // Touch swipe cho mobile
    initLazyLoading();
    initSmoothScroll();
    
    // Start countdown timer
    updateCountdowns();
    setInterval(updateCountdowns, 1000);
    
    // Check pending booking nếu đã login
    if (typeof isUserLoggedIn !== 'undefined' && isUserLoggedIn) {
        checkPendingBooking();
    }
    
    // Close trailer on ESC key
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeTrailer();
    });
    
    console.log('🎬 VKU Cinema Home Page Loaded');
});

// Expose functions to global scope
window.slideMovies = slideMovies;
window.openTrailer = openTrailer;
window.closeTrailer = closeTrailer;
window.checkLoginBeforeBooking = checkLoginBeforeBooking;