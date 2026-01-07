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
    // console.log('=== Member.js: Checking login status ===');
    try {
        const response = await fetch('/src/controllers/memberController.php?action=check_login', {
            credentials: 'include'
        });
        // console.log('Response status:', response.status);
        
        const result = await response.json();
        // console.log('Login check result:', result);
        
        if (result.success && result.logged_in) {
            // console.log('User is logged in, user_id:', result.user_id);
            // Đã đăng nhập - load thông tin user
            loadMemberContent();
        } else {
            // console.log('User is NOT logged in');
            // Chưa đăng nhập - hiển thị thông báo
            showLoginRequired();
        }
    } catch (error) {
        // console.error('Error checking login:', error);
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
    // console.log('=== Loading member content ===');
    try {
        // Load thông tin user
        const userResponse = await fetch('/src/controllers/memberController.php?action=get_user_info', {
            credentials: 'include'
        });
        // console.log('User info response status:', userResponse.status);
        
        const userResult = await userResponse.json();
        // console.log('User info result:', userResult);
        
        if (!userResult.success) {
            // console.log('Failed to get user info, showing login required');
            // Nếu không có thông tin user, mở modal đăng nhập
            showLoginRequired();
            return;
        }
        
        const user = userResult.user;
        // console.log('User data:', user);
        
        // Load lịch sử booking
        const bookingResponse = await fetch('/src/controllers/memberController.php?action=get_booking_history', {
            credentials: 'include'
        });
        const bookingResult = await bookingResponse.json();
        // console.log('Booking history result:', bookingResult);
        
        const bookings = bookingResult.success ? bookingResult.bookings : [];
        
        // Hiển thị nội dung
        displayMemberContent(user, bookings);
        
    } catch (error) {
        // console.error('Error loading member content:', error);
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
    
    // Nút review chỉ hiển thị cho booking đã thanh toán
    const reviewButton = booking.paymentStatus === 'paid' ? `
        <button class="btn-review" onclick="openReviewModal(${booking.bookingID}, ${booking.movieID}, '${booking.movieTitle.replace(/'/g, "\\'")}')">   <i class="fas fa-star"></i> Đánh giá
        </button>
    ` : '';
    
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
                        ${reviewButton}
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
        
        const response = await fetch(url, {
            credentials: 'include'
        });
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
        // console.error('Error filtering bookings:', error);
    }
}

/**
 * Hiển thị chi tiết booking trong modal
 */
async function showBookingDetail(bookingID) {
    try {
        const response = await fetch(`/src/controllers/memberController.php?action=get_booking_detail&id=${bookingID}`, {
            credentials: 'include'
        });
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
        // console.error('Error showing booking detail:', error);
        alert('Không thể tải chi tiết đặt vé');
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

/**
 * Mở modal review phim
 */
function openReviewModal(bookingID, movieID, movieTitle) {
    // Tạo modal review nếu chưa tồn tại
    let modal = document.getElementById('reviewModal');
    if (!modal) {
        modal = createReviewModal();
        document.body.appendChild(modal);
    }
    
    // Set thông tin phim
    document.getElementById('reviewMovieTitle').textContent = movieTitle;
    document.getElementById('reviewBookingID').value = bookingID;
    document.getElementById('reviewMovieID').value = movieID;
    
    // Reset form
    document.getElementById('reviewForm').reset();
    setRating(0); // Start at 0 stars
    
    // Hiển thị modal
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('show'), 10);
}

/**
 * Tạo HTML cho review modal
 */
function createReviewModal() {
    const modal = document.createElement('div');
    modal.id = 'reviewModal';
    modal.className = 'booking-modal';
    modal.innerHTML = `
        <div class="booking-modal-overlay" onclick="closeReviewModal()"></div>
        <div class="booking-modal-content review-modal-content">
            <button class="booking-modal-close" onclick="closeReviewModal()">
                <i class="fas fa-times"></i>
            </button>
            
            <h2><i class="fas fa-star"></i> Đánh giá phim</h2>
            <h3 id="reviewMovieTitle" style="color: #667eea; margin-bottom: 30px;"></h3>
            
            <form id="reviewForm" onsubmit="submitReview(event)">
                <input type="hidden" id="reviewBookingID" name="bookingID">
                <input type="hidden" id="reviewMovieID" name="movieID">
                <input type="hidden" id="reviewRating" name="rating" value="0">
                
                <div class="form-group">
                    <label style="color: white; font-size: 16px; margin-bottom: 15px; display: block;">
                        <i class="fas fa-star" style="color: #ffd700;"></i> Đánh giá của bạn:
                    </label>
                    <div class="star-rating" style="font-size: 48px; margin-bottom: 25px; display: flex; gap: 8px; justify-content: center;">
                        <i class="far fa-star" data-rating="1" onclick="setRating(1)" onmouseover="hoverRating(1)" onmouseout="resetHover()" style="color: #666; cursor: pointer; transition: all 0.2s;"></i>
                        <i class="far fa-star" data-rating="2" onclick="setRating(2)" onmouseover="hoverRating(2)" onmouseout="resetHover()" style="color: #666; cursor: pointer; transition: all 0.2s;"></i>
                        <i class="far fa-star" data-rating="3" onclick="setRating(3)" onmouseover="hoverRating(3)" onmouseout="resetHover()" style="color: #666; cursor: pointer; transition: all 0.2s;"></i>
                        <i class="far fa-star" data-rating="4" onclick="setRating(4)" onmouseover="hoverRating(4)" onmouseout="resetHover()" style="color: #666; cursor: pointer; transition: all 0.2s;"></i>
                        <i class="far fa-star" data-rating="5" onclick="setRating(5)" onmouseover="hoverRating(5)" onmouseout="resetHover()" style="color: #666; cursor: pointer; transition: all 0.2s;"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="reviewComment" style="color: white; font-size: 16px; margin-bottom: 10px; display: block;">
                        <i class="fas fa-comment"></i> Nhận xét của bạn:
                    </label>
                    <textarea 
                        id="reviewComment" 
                        name="comment" 
                        rows="5" 
                        placeholder="Chia sẻ cảm nhận của bạn về bộ phim..."
                        required
                        style="width: 100%; padding: 15px; border-radius: 8px; border: 2px solid rgba(102, 126, 234, 0.3); background: rgba(255, 255, 255, 0.05); color: white; font-size: 14px; resize: vertical; font-family: inherit;"
                    ></textarea>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 25px;">
                    <button type="button" class="btn-secondary" onclick="closeReviewModal()" style="flex: 1; padding: 12px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600;">
                        Hủy
                    </button>
                    <button type="submit" class="btn-primary" style="flex: 2; padding: 12px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <i class="fas fa-paper-plane"></i> Gửi đánh giá
                    </button>
                </div>
            </form>
        </div>
    `;
    return modal;
}

/**
 * Đóng review modal
 */
function closeReviewModal() {
    const modal = document.getElementById('reviewModal');
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => modal.style.display = 'none', 300);
    }
}

/**
 * Set rating stars
 */
function setRating(rating) {
    document.getElementById('reviewRating').value = rating;
    const stars = document.querySelectorAll('.star-rating i');
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.remove('far');
            star.classList.add('fas');
            star.style.color = '#ffd700';
        } else {
            star.classList.remove('fas');
            star.classList.add('far');
            star.style.color = '#666';
        }
    });
}

/**
 * Hover effect for stars
 */
function hoverRating(rating) {
    const stars = document.querySelectorAll('.star-rating i');
    stars.forEach((star, index) => {
        if (index < rating) {
            star.style.color = '#ffd700';
            star.style.transform = 'scale(1.1)';
        } else {
            star.style.color = '#666';
            star.style.transform = 'scale(1)';
        }
    });
}

/**
 * Reset hover effect
 */
function resetHover() {
    const currentRating = parseInt(document.getElementById('reviewRating').value);
    const stars = document.querySelectorAll('.star-rating i');
    stars.forEach((star, index) => {
        star.style.transform = 'scale(1)';
        if (index < currentRating) {
            star.style.color = '#ffd700';
        } else {
            star.style.color = '#666';
        }
    });
}

/**
 * Submit review
 */
async function submitReview(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const submitBtn = event.target.querySelector('button[type="submit"]');
    
    // Disable button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang gửi...';
    
    try {
        const response = await fetch('/src/controllers/reviewController.php', {
            method: 'POST',
            body: formData,
            credentials: 'include'
        });
        
        // Log response để debug
        const text = await response.text();
        // console.log('Response text:', text);
        
        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            // console.error('Failed to parse JSON:', text);
            // Hiển thị response để debug
            alert('Lỗi server:\n' + text.substring(0, 500));
            throw new Error('Server trả về dữ liệu không hợp lệ');
        }
        
        if (result.success) {
            alert('Cảm ơn bạn đã đánh giá! Đánh giá của bạn đã được gửi thành công.');
            closeReviewModal();
            // Reload bookings để cập nhật
            loadMemberContent();
        } else {
            alert('Lỗi: ' + (result.message || 'Không thể gửi đánh giá'));
        }
    } catch (error) {
        // console.error('Error submitting review:', error);
        alert('Có lỗi xảy ra khi gửi đánh giá. Vui lòng thử lại!');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Gửi đánh giá';
    }
}
