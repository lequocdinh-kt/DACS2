/**
 * VKU Cinema - Schedule Page JavaScript
 */

// ==================== GLOBAL VARIABLES ====================
let selectedDate = null;
let allMoviesData = [];
let currentFilter = 'all';

// ==================== INITIALIZATION ====================
document.addEventListener('DOMContentLoaded', function() {
    // console.log('Schedule page loaded');
    initializeDateSlider();
    initializeFilterButtons();
    loadSchedule();
});

// ==================== DATE SLIDER ====================
function initializeDateSlider() {
    const dateSlider = document.getElementById('dateSlider');
    if (!dateSlider) return;
    
    const dates = generateNext7Days();
    
    dates.forEach((date, index) => {
        const dateItem = createDateItem(date, index === 0);
        dateSlider.appendChild(dateItem);
        
        if (index === 0) {
            selectedDate = date.fullDate;
        }
    });
}

function generateNext7Days() {
    const dates = [];
    const today = new Date();
    
    for (let i = 0; i < 7; i++) {
        const date = new Date(today);
        date.setDate(today.getDate() + i);
        
        dates.push({
            dayName: getDayName(date, i),
            dayNumber: date.getDate(),
            monthYear: getMonthYear(date),
            fullDate: formatDate(date)
        });
    }
    
    return dates;
}

function getDayName(date, index) {
    if (index === 0) return 'Hôm nay';
    if (index === 1) return 'Ngày mai';
    
    const days = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
    return days[date.getDay()];
}

function getMonthYear(date) {
    const months = ['Th1', 'Th2', 'Th3', 'Th4', 'Th5', 'Th6', 'Th7', 'Th8', 'Th9', 'Th10', 'Th11', 'Th12'];
    return `${months[date.getMonth()]} ${date.getFullYear()}`;
}

function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function createDateItem(date, isActive) {
    const dateItem = document.createElement('div');
    dateItem.className = `date-item ${isActive ? 'active' : ''}`;
    dateItem.dataset.date = date.fullDate;
    
    dateItem.innerHTML = `
        <div class="day-name">${date.dayName}</div>
        <div class="day-number">${date.dayNumber}</div>
        <div class="month-year">${date.monthYear}</div>
    `;
    
    dateItem.addEventListener('click', function() {
        // Remove active class from all dates
        document.querySelectorAll('.date-item').forEach(item => {
            item.classList.remove('active');
        });
        
        // Add active class to clicked date
        this.classList.add('active');
        
        // Update selected date
        selectedDate = this.dataset.date;
        
        // Reload schedule
        loadSchedule();
    });
    
    return dateItem;
}

// ==================== FILTER BUTTONS ====================
function initializeFilterButtons() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons
            filterButtons.forEach(b => b.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Update current filter
            currentFilter = this.dataset.filter;
            
            // Filter movies
            filterMovies();
        });
    });
}

// ==================== LOAD SCHEDULE ====================
async function loadSchedule() {
    const moviesList = document.getElementById('moviesList');
    
    // Show loading
    moviesList.innerHTML = `
        <div class="loading">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Đang tải lịch chiếu...</p>
        </div>
    `;
    
    try {
        const apiUrl = `${window.location.origin}/src/controllers/scheduleController.php?date=${selectedDate}`;
        
        const response = await fetch(apiUrl);
        const data = await response.json();
        
        if (data.success) {
            allMoviesData = data.movies;
            displayMovies(allMoviesData);
        } else {
            showEmptyState(data.message || 'Không có lịch chiếu');
        }
    } catch (error) {
        // console.error('Error loading schedule:', error);
        showEmptyState('Có lỗi xảy ra khi tải lịch chiếu');
    }
}

// ==================== DISPLAY MOVIES ====================
function displayMovies(movies) {
    const moviesList = document.getElementById('moviesList');
    
    if (!movies || movies.length === 0) {
        showEmptyState('Không có lịch chiếu cho ngày này');
        return;
    }
    
    moviesList.innerHTML = movies.map(movie => createMovieScheduleItem(movie)).join('');
}

function createMovieScheduleItem(movie) {
    // if (window.debugLogger) {
    //     window.debugLogger.log('Creating movie schedule item', { title: movie.title, showtimesCount: movie.showtimes.length });
    // }
    
    const showtimesHtml = movie.showtimes.map(showtime => {
        const isFull = showtime.availableSeats <= 0;
        const seatsText = isFull ? 'Hết chỗ' : `${showtime.availableSeats} chỗ`;
        const format = showtime.roomType || showtime.format || '2D';
        
        // Use relative path from root - works with both direct access and routing
        const bookingUrl = `src/views/booking_step2_seats.php?showtimeID=${showtime.showtimeID}`;
        
        // if (window.debugLogger) {
        //     window.debugLogger.log('Showtime created', {
        //         id: showtime.showtimeID,
        //         time: showtime.showTime,
        //         url: bookingUrl,
        //         isFull: isFull,
        //         availableSeats: showtime.availableSeats
        //     });
        // }
        
        return `
            <a href="${bookingUrl}" 
               class="showtime-btn ${isFull ? 'full' : ''}" 
               data-format="${format}"
               data-showtime-id="${showtime.showtimeID}"
               ${isFull ? 'onclick="return false;"' : ''}
               onclick="handleShowtimeClick(event, ${showtime.showtimeID}, '${bookingUrl}')">
                <div class="time">${formatTime(showtime.showTime)}</div>
                <div class="room-info">
                    <i class="fas ${format === '3D' ? 'fa-glasses' : 'fa-film'}"></i>
                    ${showtime.roomName} - ${format}
                </div>
                <div class="seats-info">${seatsText}</div>
            </a>
        `;
    }).join('');
    
    return `
        <div class="movie-schedule-item" data-format="${movie.format || '2D'}">
            <div class="movie-info-row">
                <div class="movie-poster-small">
                    <img src="${movie.posterURL}" alt="${movie.title}">
                </div>
                <div class="movie-details">
                    <h3 class="movie-title-schedule">
                        ${movie.ageRating ? `<span class="age-rating">${movie.ageRating}</span>` : ''}
                        ${movie.title}
                    </h3>
                    <div class="movie-meta-schedule">
                        <div class="meta-item">
                            <i class="fas fa-film"></i>
                            <span>${movie.genre || 'Chưa phân loại'}</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-clock"></i>
                            <span>${movie.duration} phút</span>
                        </div>
                        ${movie.format ? `
                        <div class="meta-item">
                            <i class="fas fa-glasses"></i>
                            <span>${movie.format}</span>
                        </div>
                        ` : ''}
                        <div class="meta-item">
                            <i class="fas fa-star"></i>
                            <span>${movie.rating || 'N/A'}</span>
                        </div>
                    </div>
                    <p class="movie-description-short">${movie.description || 'Đang cập nhật mô tả phim...'}</p>
                </div>
            </div>
            <div class="showtimes-grid">
                ${showtimesHtml || '<p style="color: #888; text-align: center; width: 100%;">Chưa có suất chiếu</p>'}
            </div>
        </div>
    `;
}

function formatTime(timeString) {
    // Convert "HH:MM:SS" to "HH:MM"
    return timeString.substring(0, 5);
}

// ==================== FILTER MOVIES ====================
function filterMovies() {
    const moviesList = document.getElementById('moviesList');
    
    if (currentFilter === 'all') {
        // Show all movies and all showtimes
        displayMovies(allMoviesData);
        return;
    }
    
    // Filter movies that have showtimes matching the format
    const filteredMovies = allMoviesData.map(movie => {
        // Filter showtimes by format
        const filteredShowtimes = movie.showtimes.filter(showtime => {
            const format = showtime.roomType || showtime.format || '2D';
            return format.includes(currentFilter);
        });
        
        // Only include movie if it has matching showtimes
        if (filteredShowtimes.length > 0) {
            return {
                ...movie,
                showtimes: filteredShowtimes
            };
        }
        return null;
    }).filter(movie => movie !== null);
    
    if (filteredMovies.length === 0) {
        showEmptyState(`Không có suất chiếu ${currentFilter} cho ngày này`);
    } else {
        displayMovies(filteredMovies);
    }
}

// ==================== EMPTY STATE ====================
function showEmptyState(message) {
    const moviesList = document.getElementById('moviesList');
    moviesList.innerHTML = `
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <h3>Không có lịch chiếu</h3>
            <p>${message}</p>
        </div>
    `;
}

// // ==================== UTILITY FUNCTIONS ====================
// function handleShowtimeClick(event, showtimeID, url) {
//     if (window.debugLogger) {
//         window.debugLogger.log('=== SHOWTIME BUTTON CLICKED ===', {
//             showtimeID: showtimeID,
//             url: url,
//             fullURL: window.location.origin + '/' + url,
//             currentLocation: window.location.href,
//             eventType: event.type,
//             target: event.target.className
//         });
//     }
    
//     console.log('=== SHOWTIME CLICKED ===');
//     console.log('Event:', event);
//     console.log('Showtime ID:', showtimeID);
//     console.log('URL:', url);
//     console.log('Current location:', window.location.href);
//     console.log('=======================');
    
//     // Let the default link behavior happen
//     return true;
// }

// function checkLoginBeforeBooking(event, showtimeID) {
//     // Check if user is logged in (you can check session or local storage)
//     const isLoggedIn = document.querySelector('.user-profile') !== null;
    
//     if (!isLoggedIn) {
//         event.preventDefault();
//         alert('Vui lòng đăng nhập để đặt vé!');
//         // Optionally open login modal or redirect to login page
//         return false;
//     }
    
//     return true;
// }
