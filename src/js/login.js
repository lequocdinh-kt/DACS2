// Toggle password
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    if (!passwordInput) return;
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon?.classList.remove('fa-eye');
        toggleIcon?.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon?.classList.remove('fa-eye-slash');
        toggleIcon?.classList.add('fa-eye');
    }
}

// Khi load trang
window.addEventListener('load', function() {
    document.querySelector('.login-box')?.classList.add('fade-in');
});

// Slideshow poster với animation từ phải sang trái
const posterImages = [
    '../img/posters/1.jpg',
    '../img/posters/2.jpg',
    '../img/posters/3.jpg',
    '../img/posters/4.jpg',
    '../img/posters/5.jpg'
];

const currentImg = document.getElementById('slideshow-current');
const nextImg = document.getElementById('slideshow-next');
const dotsContainer = document.getElementById('slideshow-dots');

let posterIndex = 0;
let autoTimer = null;
const intervalMs = 2000;

function buildDots() {
    if (!dotsContainer) return;
    dotsContainer.innerHTML = '';
    posterImages.forEach((_, i) => {
        const dot = document.createElement('button');
        dot.className = 'dot' + (i === posterIndex ? ' active' : '');
        dot.dataset.index = i;
        dot.title = 'Poster ' + (i + 1);
        dot.addEventListener('click', onDotClick);
        dotsContainer.appendChild(dot);
    });
}

function setActiveDot(index) {
    if (!dotsContainer) return;
    const dots = dotsContainer.querySelectorAll('.dot');
    dots.forEach((dot, i) => dot.classList.toggle('active', i === index));
}

function getDirection(current, target, length) {
    const diff = target - current;
    const half = Math.floor(length / 2);
    if (Math.abs(diff) <= half) {
        return diff > 0 ? 'left' : (diff < 0 ? 'right' : 'none');
    } else {
        return diff > 0 ? 'right' : 'left';
    }
}

function goToIndex(targetIndex) {
    if (!currentImg || !nextImg) return;
    if (targetIndex === posterIndex) return;

    const direction = getDirection(posterIndex, targetIndex, posterImages.length);
    if (direction === 'none') return;

    nextImg.src = posterImages[targetIndex];
    nextImg.style.transition = 'none';
    currentImg.style.transition = 'none';

    if (direction === 'left') {
        nextImg.style.transform = 'translateX(100%)';
        currentImg.style.transform = 'translateX(0)';
    } else {
        nextImg.style.transform = 'translateX(-100%)';
        currentImg.style.transform = 'translateX(0)';
    }

    void nextImg.offsetWidth;

    nextImg.style.transition = 'transform 500ms ease-out';
    currentImg.style.transition = 'transform 500ms ease-out';

    if (direction === 'left') {
        currentImg.style.transform = 'translateX(-100%)';
        nextImg.style.transform = 'translateX(0)';
    } else {
        currentImg.style.transform = 'translateX(100%)';
        nextImg.style.transform = 'translateX(0)';
    }

    const onEnd = () => {
        currentImg.src = posterImages[targetIndex];
        currentImg.style.transition = 'none';
        currentImg.style.transform = 'translateX(0)';
        nextImg.style.transition = 'none';
        nextImg.style.transform = 'translateX(100%)';
        posterIndex = targetIndex;
        setActiveDot(posterIndex);
        nextImg.removeEventListener('transitionend', onEnd);
    };
    nextImg.addEventListener('transitionend', onEnd);

    restartAuto();
}

function onDotClick(e) {
    const idx = Number(e.currentTarget.dataset.index);
    goToIndex(idx);
}

function nextPoster() {
    const target = (posterIndex + 1) % posterImages.length;
    goToIndex(target);
}

function restartAuto() {
    if (autoTimer) clearInterval(autoTimer);
    autoTimer = setInterval(nextPoster, intervalMs);
}

// Disable drag & drop for images and slideshow area
(function() {
    document.querySelectorAll('.slide-img').forEach(img => {
        img.draggable = false;
        img.addEventListener('dragstart', e => e.preventDefault());
    });

    const slideshowOuter = document.querySelector('.slideshow-outer');
    if (slideshowOuter) {
        ['dragenter','dragover','dragleave','drop'].forEach(evt =>
            slideshowOuter.addEventListener(evt, e => e.preventDefault())
        );
    }

    window.addEventListener('dragover', e => e.preventDefault());
    window.addEventListener('drop', e => {
        if (e.target.closest && e.target.closest('.slideshow-outer')) {
            e.preventDefault();
        }
    });
})();

// Khởi tạo slideshow khi DOM sẵn sàng
document.addEventListener('DOMContentLoaded', function() {
    buildDots();
    setActiveDot(posterIndex);
    restartAuto();
});

// Remove query params ?error or ?success after page load so F5 won't re-show alerts,
// and auto-hide alert messages after a short delay.
(function cleanupAlertsAndQuery() {
    // Run after DOM ready
    function runCleanup() {
        try {
            // Remove query string but keep path and hash
            const u = new URL(window.location.href);
            if (u.searchParams.has('error') || u.searchParams.has('success')) {
                u.search = '';
                window.history.replaceState({}, document.title, u.pathname + u.hash);
            }
        } catch (e) {
            // ignore URL parsing errors on older browsers
        }

        // Auto-hide any .alert elements after 4s
        const ALERT_HIDE_MS = 4000;
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(el => {
                // fade out if CSS supports .fade-out
                el.classList.add('fade-out');
                // remove from DOM after fade (300ms)
                setTimeout(() => el.remove(), 300);
            });
        }, ALERT_HIDE_MS);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', runCleanup);
    } else {
        runCleanup();
    }
})();
