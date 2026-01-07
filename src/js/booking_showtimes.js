/**
 * BOOKING SHOWTIMES - JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    const movieID = getMovieIDFromURL();
    const dateItems = document.querySelectorAll('.date-item');
    
    // Chọn ngày đầu tiên mặc định
    if (dateItems.length > 0) {
        dateItems[0].click();
    }
    
    // Event listener cho chọn ngày
    dateItems.forEach(item => {
        item.addEventListener('click', function() {
            // Remove active từ tất cả
            dateItems.forEach(d => d.classList.remove('active'));
            
            // Add active vào item được chọn
            this.classList.add('active');
            
            // Lấy ngày được chọn
            const selectedDate = this.dataset.date;
            
            // Load suất chiếu
            loadShowtimes(movieID, selectedDate);
        });
    });
});

/**
 * Lấy movieID từ URL
 */
function getMovieIDFromURL() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('movieID');
}

/**
 * Load danh sách suất chiếu theo ngày
 */
function loadShowtimes(movieID, showDate) {
    const showtimesGrid = document.querySelector('.showtimes-grid');
    
    if (!showtimesGrid) return;
    
    // Show loading
    showtimesGrid.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i><p>Đang tải suất chiếu...</p></div>';
    
    // Gọi API
    fetch(`/src/controllers/showtimeController.php?action=get_showtimes&movieID=${movieID}&showDate=${showDate}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.showtimes.length > 0) {
                renderShowtimes(data.showtimes);
            } else {
                showtimesGrid.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h3>Không có suất chiếu</h3>
                        <p>Vui lòng chọn ngày khác</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            // console.error('Error loading showtimes:', error);
            showtimesGrid.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Có lỗi xảy ra</h3>
                    <p>Vui lòng thử lại sau</p>
                </div>
            `;
        });
}

/**
 * Render danh sách suất chiếu
 */
function renderShowtimes(showtimes) {
    const showtimesGrid = document.querySelector('.showtimes-grid');
    
    if (!showtimesGrid) return;
    
    let html = '';
    
    showtimes.forEach(showtime => {
        const isFull = showtime.status === 'full' || showtime.availableSeats === 0;
        const isLow = showtime.availableSeats < 10;
        
        let roomBadgeClass = '';
        if (showtime.roomType === 'vip') roomBadgeClass = 'vip';
        else if (showtime.roomType === 'imax') roomBadgeClass = 'imax';
        
        let seatsClass = 'seats-available';
        if (isFull) seatsClass += ' full';
        else if (isLow) seatsClass += ' low';
        
        html += `
            <div class="showtime-card ${isFull ? 'full' : ''}" data-showtime-id="${showtime.showtimeID}">
                <div class="showtime-time">${showtime.showTime}</div>
                <div class="showtime-room">
                    <i class="fas fa-door-open"></i> ${showtime.roomName}
                    <span class="room-badge ${roomBadgeClass}">${showtime.roomType.toUpperCase()}</span>
                </div>
                <div class="showtime-seats">
                    <span class="${seatsClass}">
                        <i class="fas fa-chair"></i> 
                        ${showtime.availableSeats}/${showtime.totalSeats} ghế
                    </span>
                    <span class="showtime-price">${formatPrice(showtime.basePrice)}đ</span>
                </div>
                <button class="btn-select" 
                        onclick="selectShowtime(${showtime.showtimeID})"
                        ${isFull ? 'disabled' : ''}>
                    ${isFull ? 'Hết chỗ' : 'Chọn suất này'}
                </button>
            </div>
        `;
    });
    
    showtimesGrid.innerHTML = html;
}

/**
 * Chọn suất chiếu
 */
function selectShowtime(showtimeID) {
    window.location.href = `/src/views/booking_step2_seats.php?showtimeID=${showtimeID}`;
}

/**
 * Format giá tiền
 */
function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN').format(price);
}
