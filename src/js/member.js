/**
 * Member Page JavaScript
 * Xử lý kiểm tra đăng nhập, hiển thị thông tin user và lịch sử booking
 */

document.addEventListener('DOMContentLoaded', () => {
    // Đợi một chút để đảm bảo auth.js đã load
    setTimeout(() => {
        checkLoginStatus();
    }, 100);
    
    // Lắng nghe sự kiện đăng nhập thành công
    window.addEventListener('loginSuccess', () => {
        // Đóng modal và load nội dung member
        if (typeof closeAuthModal === 'function') {
            closeAuthModal();
        }
        loadMemberContent();
    });
});

/**
 * Kiểm tra trạng thái đăng nhập
 */
async function checkLoginStatus() {
    console.log('=== Member.js: Checking login status ===');
    try {
        const response = await fetch('/src/controllers/memberController.php?action=check_login');
        console.log('Response status:', response.status);
        
        const result = await response.json();
        console.log('Login check result:', result);
        
        if (result.success && result.logged_in) {
            console.log('User is logged in, user_id:', result.user_id);
            // Đã đăng nhập - load thông tin user
            loadMemberContent();
        } else {
            console.log('User is NOT logged in');
            // Chưa đăng nhập - hiển thị thông báo
            showLoginRequired();
        }
    } catch (error) {
        console.error('Error checking login:', error);
        showLoginRequired();
    }
}

/**
 * Hiển thị thông báo yêu cầu đăng nhập
 */
function showLoginRequired() {
    const container = document.querySelector('.member-container');
    container.innerHTML = `
        <div class="login-required-message">
            <div class="login-required-content">
                <i class="fas fa-user-lock"></i>
                <h2>Vui lòng đăng nhập</h2>
                <p>Bạn cần đăng nhập để xem thông tin thành viên và lịch sử đặt vé</p>
                <button class="btn-login-now" onclick="if(typeof openAuthModal === 'function') openAuthModal('login')">
                    <i class="fas fa-sign-in-alt"></i> Đăng nhập ngay
                </button>
            </div>
        </div>
    `;
}

/**
 * Load nội dung thành viên
 */
async function loadMemberContent() {
    console.log('=== Loading member content ===');
    try {
        // Load thông tin user
        const userResponse = await fetch('/src/controllers/memberController.php?action=get_user_info');
        console.log('User info response status:', userResponse.status);
        
        const userResult = await userResponse.json();
        console.log('User info result:', userResult);
        
        if (!userResult.success) {
            console.log('Failed to get user info, showing login required');
            // Nếu không có thông tin user, mở modal đăng nhập
            showLoginRequired();
            return;
        }
        
        const user = userResult.user;
        console.log('User data:', user);
        
        // Load lịch sử booking
        const bookingResponse = await fetch('/src/controllers/memberController.php?action=get_booking_history');
        const bookingResult = await bookingResponse.json();
        console.log('Booking history result:', bookingResult);
        
        const bookings = bookingResult.success ? bookingResult.bookings : [];
        
        // Hiển thị nội dung
        displayMemberContent(user, bookings);
        
    } catch (error) {
        console.error('Error loading member content:', error);
        showErrorState();
    }
}

/**
 * Hiển thị nội dung thành viên
 */
function displayMemberContent(user, bookings) {
    const container = document.querySelector('.member-container');
    
    // Tính toán membership tier dựa trên tổng chi tiêu
    let tier = 'Silver';
    let tierClass = 'silver';
    let tierIcon = 'fa-medal';
    
    if (user.totalSpent >= 5000000) {
        tier = 'Platinum';
        tierClass = 'platinum';
        tierIcon = 'fa-gem';
    } else if (user.totalSpent >= 2000000) {
        tier = 'Gold';
        tierClass = 'gold';
        tierIcon = 'fa-crown';
    }
    
    container.innerHTML = `
        <!-- Member Profile -->
        <section class="member-profile">
            <div class="profile-header">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="profile-info">
                    <h2>${user.username}</h2>
                    <p>${user.email}</p>
                    <div class="membership-badge ${tierClass}">
                        <i class="fas ${tierIcon}"></i>
                        <span>${tier} Member</span>
                    </div>
                </div>
            </div>
            
            <div class="profile-stats">
                <div class="stat-item">
                    <i class="fas fa-ticket-alt"></i>
                    <div>
                        <span class="stat-value">${user.paidBookings}</span>
                        <span class="stat-label">Vé đã mua</span>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-coins"></i>
                    <div>
                        <span class="stat-value">${formatPrice(user.totalSpent)}</span>
                        <span class="stat-label">Tổng chi tiêu</span>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-history"></i>
                    <div>
                        <span class="stat-value">${user.totalBookings}</span>
                        <span class="stat-label">Tổng đặt vé</span>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Booking History -->
        <section class="booking-history">
            <div class="history-header">
                <h2><i class="fas fa-history"></i> Lịch sử đặt vé</h2>
                <div class="history-filters">
                    <button class="filter-btn active" data-status="all" onclick="filterBookings('all')">
                        Tất cả
                    </button>
                    <button class="filter-btn" data-status="paid" onclick="filterBookings('paid')">
                        Đã thanh toán
                    </button>
                    <button class="filter-btn" data-status="pending" onclick="filterBookings('pending')">
                        Chờ thanh toán
                    </button>
                    <button class="filter-btn" data-status="cancelled" onclick="filterBookings('cancelled')">
                        Đã hủy
                    </button>
                </div>
            </div>
            
            <div class="bookings-list">
                ${bookings.length > 0 ? bookings.map(booking => createBookingCard(booking)).join('') : '<div class="empty-bookings"><i class="fas fa-inbox"></i><p>Chưa có lịch sử đặt vé</p></div>'}
            </div>
        </section>
    `;
}

/**
 * Tạo card booking
 */
function createBookingCard(booking) {
    const statusClass = {
        'paid': 'status-paid',
        'pending': 'status-pending',
        'cancelled': 'status-cancelled',
        'expired': 'status-expired'
    }[booking.paymentStatus] || '';
    
    return `
        <div class="booking-card ${statusClass}" data-status="${booking.paymentStatus}">
            <div class="booking-poster">
                <img src="${booking.posterURL}" alt="${booking.movieTitle}">
                <span class="booking-status ${statusClass}">${booking.statusLabel}</span>
            </div>
            <div class="booking-info">
                <h3>${booking.movieTitle}</h3>
                <div class="booking-details">
                    <p><i class="fas fa-calendar"></i> ${booking.formattedDate}</p>
                    <p><i class="fas fa-clock"></i> ${booking.formattedTime}</p>
                    <p><i class="fas fa-door-open"></i> ${booking.roomName}</p>
                    <p><i class="fas fa-chair"></i> ${booking.seats}</p>
                </div>
                <div class="booking-footer">
                    <span class="booking-price">${booking.formattedPrice} VNĐ</span>
                    <div class="booking-actions">
                        <button class="btn-detail" onclick="showBookingDetail(${booking.bookingID})">
                            <i class="fas fa-info-circle"></i> Chi tiết
                        </button>
                        ${booking.canCancel ? `
                            <button class="btn-cancel" onclick="cancelBooking(${booking.bookingID})">
                                <i class="fas fa-times"></i> Hủy vé
                            </button>
                        ` : ''}
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Lọc bookings theo trạng thái
 */
async function filterBookings(status) {
    // Update active button
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.status === status) {
            btn.classList.add('active');
        }
    });
    
    try {
        let url = '/src/controllers/memberController.php?action=get_booking_history';
        if (status !== 'all') {
            url += `&status=${status}`;
        }
        
        const response = await fetch(url);
        const result = await response.json();
        
        if (result.success) {
            const bookingsList = document.querySelector('.bookings-list');
            if (result.bookings.length > 0) {
                bookingsList.innerHTML = result.bookings.map(booking => createBookingCard(booking)).join('');
            } else {
                bookingsList.innerHTML = '<div class="empty-bookings"><i class="fas fa-inbox"></i><p>Không có vé nào</p></div>';
            }
        }
    } catch (error) {
        console.error('Error filtering bookings:', error);
    }
}

/**
 * Hiển thị chi tiết booking trong modal
 */
async function showBookingDetail(bookingID) {
    try {
        const response = await fetch(`/src/controllers/memberController.php?action=get_booking_detail&id=${bookingID}`);
        const result = await response.json();
        
        if (!result.success) {
            alert(result.message);
            return;
        }
        
        const booking = result.booking;
        
        // Tạo modal
        const modal = document.createElement('div');
        modal.className = 'booking-modal';
        modal.innerHTML = `
            <div class="booking-modal-overlay"></div>
            <div class="booking-modal-content">
                <button class="booking-modal-close">
                    <i class="fas fa-times"></i>
                </button>
                <h2><i class="fas fa-ticket-alt"></i> Chi tiết đặt vé</h2>
                
                <div class="modal-section">
                    <h3>Thông tin phim</h3>
                    <p><strong>Tên phim:</strong> ${booking.movieTitle}</p>
                    <p><strong>Thời gian:</strong> ${booking.formattedDateTime}</p>
                    <p><strong>Phòng:</strong> ${booking.roomName}</p>
                    <p><strong>Thời lượng:</strong> ${booking.duration} phút</p>
                    <p><strong>Thể loại:</strong> ${booking.genre}</p>
                </div>
                
                <div class="modal-section">
                    <h3>Thông tin đặt vé</h3>
                    <p><strong>Mã đặt vé:</strong> ${booking.bookingCode}</p>
                    <p><strong>Ghế:</strong> ${booking.seats}</p>
                    <p><strong>Số lượng:</strong> ${booking.totalSeats} ghế</p>
                    <p><strong>Trạng thái:</strong> <span class="status-${booking.paymentStatus}">${booking.statusLabel}</span></p>
                    ${booking.paymentMethod ? `<p><strong>Phương thức:</strong> ${booking.paymentMethod}</strong></p>` : ''}
                </div>
                
                <div class="modal-section">
                    <h3>Thanh toán</h3>
                    <p><strong>Tổng tiền:</strong> <span class="highlight-price">${booking.formattedPrice} VNĐ</span></p>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Close handlers
        modal.querySelector('.booking-modal-close').onclick = () => modal.remove();
        modal.querySelector('.booking-modal-overlay').onclick = () => modal.remove();
        
        setTimeout(() => modal.classList.add('show'), 10);
        
    } catch (error) {
        console.error('Error showing booking detail:', error);
        alert('Không thể tải chi tiết đặt vé');
    }
}

/**
 * Hủy booking
 */
async function cancelBooking(bookingID) {
    if (!confirm('Bạn có chắc muốn hủy vé này?')) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('booking_id', bookingID);
        
        const response = await fetch('/src/controllers/memberController.php?action=cancel_booking', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Hủy vé thành công!');
            // Reload lại nội dung
            loadMemberContent();
        } else {
            alert(result.message || 'Không thể hủy vé');
        }
    } catch (error) {
        console.error('Error cancelling booking:', error);
        alert('Có lỗi xảy ra khi hủy vé');
    }
}

/**
 * Format giá tiền
 */
function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN').format(price);
}

/**
 * Hiển thị error state
 */
function showErrorState() {
    const container = document.querySelector('.member-container');
    container.innerHTML = `
        <div class="error-state">
            <i class="fas fa-exclamation-triangle"></i>
            <p>Không thể tải thông tin. Vui lòng thử lại sau.</p>
        </div>
    `;
}
