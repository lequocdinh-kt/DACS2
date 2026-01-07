/**
 * BOOKING SEATS - JavaScript
 */

let selectedSeats = [];
let totalPrice = 0;
let seatPrices = {};
let timerInterval;

document.addEventListener('DOMContentLoaded', function() {
    initSeats();
    startTimer();
    refreshSeatsInterval();
});

/**
 * Khởi tạo seats
 */
function initSeats() {
    const seats = document.querySelectorAll('.seat:not([data-disabled])');
    
    seats.forEach(seat => {
        // Kiểm tra có đầy đủ data không
        if (!seat.dataset.seatId || !seat.dataset.price) {
            // console.error('Seat missing data:', seat);
            return;
        }
        
        seat.addEventListener('click', function() {
            toggleSeat(this);
        });
        
        // Lưu giá của mỗi ghế - PHẢI DÙNG seat CHỨ KHÔNG PHẢI this
        const seatID = seat.dataset.seatId;
        const price = parseFloat(seat.dataset.price);
        seatPrices[seatID] = price;
    });
}

/**
 * Toggle chọn ghế
 */
function toggleSeat(seatElement) {
    const seatID = seatElement.dataset.seatId;
    const seatName = seatElement.dataset.seatName;
    const price = parseFloat(seatElement.dataset.price);
    
    if (seatElement.classList.contains('selected')) {
        // Bỏ chọn
        seatElement.classList.remove('selected');
        selectedSeats = selectedSeats.filter(s => s.id !== seatID);
        totalPrice -= price;
    } else {
        // Chọn
        seatElement.classList.add('selected');
        selectedSeats.push({ id: seatID, name: seatName, price: price });
        totalPrice += price;
    }
    
    updateSummary();
    checkContinueButton();
}

/**
 * Cập nhật summary
 */
function updateSummary() {
    const selectedSeatsText = document.getElementById('selectedSeatsText');
    const seatCount = document.getElementById('seatCount');
    const totalPriceEl = document.getElementById('totalPrice');
    
    if (selectedSeats.length === 0) {
        selectedSeatsText.textContent = 'Chưa chọn';
    } else {
        const seatNames = selectedSeats.map(s => s.name).join(', ');
        selectedSeatsText.textContent = seatNames;
    }
    
    seatCount.textContent = `${selectedSeats.length} ghế`;
    totalPriceEl.textContent = formatPrice(totalPrice) + 'đ';
}

/**
 * Kiểm tra button continue
 */
function checkContinueButton() {
    const btnContinue = document.getElementById('btnContinue');
    
    if (selectedSeats.length > 0) {
        btnContinue.disabled = false;
        btnContinue.onclick = proceedToPayment;
    } else {
        btnContinue.disabled = true;
    }
}

/**
 * Tiến hành thanh toán
 */
function proceedToPayment() {
    const seatIDs = selectedSeats.map(s => s.id);
    
    // Hiển thị loading
    const btnContinue = document.getElementById('btnContinue');
    btnContinue.disabled = true;
    btnContinue.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
    
    // Lock ghế trước
    fetch('/src/controllers/seatController.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=lock_seats&showtimeID=${showtimeID}&seatIDs=${JSON.stringify(seatIDs)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.requireLogin) {
            // Chuyển về trang chủ và mở modal đăng nhập
            window.location.href = '/?openLogin=1';
            return;
        }
        if (data.success) {
            // Tạo booking
            createBooking(seatIDs);
        } else {
            alert(data.message || 'Không thể giữ ghế. Vui lòng thử lại!');
            btnContinue.disabled = false;
            btnContinue.innerHTML = '<i class="fas fa-arrow-right"></i> Tiếp tục thanh toán';
            
            // Refresh seats
            location.reload();
        }
    })
    .catch(error => {
        // console.error('Error locking seats:', error);
        alert('Có lỗi xảy ra. Vui lòng thử lại!');
        btnContinue.disabled = false;
        btnContinue.innerHTML = '<i class="fas fa-arrow-right"></i> Tiếp tục thanh toán';
    });
}

/**
 * Tạo booking
 */
function createBooking(seatIDs) {
    fetch('/src/controllers/bookingController.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=create_booking&showtimeID=${showtimeID}&seatIDs=${JSON.stringify(seatIDs)}&totalPrice=${totalPrice}`
    })
    .then(response => response.json())
    .then(data => {
        // console.log('Booking response:', data); // Debug
        if (data.requireLogin) {
            // Chuyển về trang chủ và mở modal đăng nhập
            window.location.href = '/?openLogin=1';
            return;
        }
        if (data.success) {
            // Chuyển sang trang thanh toán
            window.location.href = `/src/views/booking_step3_payment.php?bookingID=${data.bookingID}`;
        } else {
            alert(data.message || 'Không thể tạo đơn đặt vé. Vui lòng thử lại!');
            location.reload();
        }
    })
    .catch(error => {
        // console.error('Error creating booking:', error);
        alert('Có lỗi xảy ra. Vui lòng thử lại!');
        location.reload();
    });
}

/**
 * Bắt đầu đếm ngược 15 phút
 */
function startTimer() {
    const timerEl = document.getElementById('timer');
    let timeLeft = 15 * 60; // 15 phút
    
    timerInterval = setInterval(function() {
        timeLeft--;
        
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            alert('Hết thời gian giữ ghế! Vui lòng chọn lại.');
            window.location.reload();
            return;
        }
        
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        timerEl.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        
        // Cảnh báo khi còn 2 phút
        if (timeLeft === 120) {
            alert('Chỉ còn 2 phút! Vui lòng nhanh chóng hoàn tất!');
        }
    }, 1000);
}

/**
 * Refresh seats mỗi 5 giây để cập nhật trạng thái
 */
function refreshSeatsInterval() {
    setInterval(function() {
        fetch(`/src/controllers/seatController.php?action=refresh_seats&showtimeID=${showtimeID}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateSeatsStatus(data.seats);
                }
            })
            .catch(error => {
                // console.error('Error refreshing seats:', error);
            });
    }, 5000); // Mỗi 5 giây
}

/**
 * Cập nhật trạng thái ghế
 */
function updateSeatsStatus(seats) {
    seats.forEach(seat => {
        const seatEl = document.querySelector(`[data-seat-id="${seat.seatID}"]`);
        
        if (seatEl && !seatEl.classList.contains('selected')) {
            // Xóa tất cả class status
            seatEl.classList.remove('available', 'booked', 'locked', 'my-lock');
            
            // Add class mới
            seatEl.classList.add(seat.status);
            
            // Update disabled
            if (seat.status === 'booked' || seat.status === 'locked') {
                seatEl.setAttribute('data-disabled', 'true');
            } else {
                seatEl.removeAttribute('data-disabled');
            }
        }
    });
}

/**
 * Format giá tiền
 */
function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN').format(price);
}

/**
 * Cleanup khi rời trang
 */
window.addEventListener('beforeunload', function() {
    clearInterval(timerInterval);
});
