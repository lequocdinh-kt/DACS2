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
const track = document.getElementById('comingSoonTrack');
let isSliding = false;

function slideMovies(direction) {
    if (!track || isSliding) return;
    
    isSliding = true;
    const cards = track.querySelectorAll('.movie-card-small');
    if (cards.length === 0) return;
    
    const cardWidth = cards[0].offsetWidth + 20; // width + gap
    const wrapper = track.parentElement;
    const wrapperWidth = wrapper.offsetWidth;
    const visibleCards = Math.floor(wrapperWidth / cardWidth);
    const maxPosition = -(cards.length - visibleCards) * cardWidth;
    
    if (direction === 'next') {
        currentPosition -= cardWidth * visibleCards;
        if (currentPosition < maxPosition) {
            currentPosition = 0; // Loop back to start
        }
    } else {
        currentPosition += cardWidth * visibleCards;
        if (currentPosition > 0) {
            currentPosition = maxPosition < 0 ? maxPosition : 0; // Loop to end
        }
    }
    
    track.style.transform = `translateX(${currentPosition}px)`;
    
    setTimeout(() => {
        isSliding = false;
    }, 600);
}

// Auto slide for coming soon movies every 5 seconds
if (track) {
    const cards = track.querySelectorAll('.movie-card-small');
    if (cards.length > 4) {
        setInterval(() => {
            slideMovies('next');
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

// Expose functions to global scope
window.openTrailer = openTrailer;
window.closeTrailer = closeTrailer;