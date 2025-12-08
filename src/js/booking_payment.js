/**
 * BOOKING PAYMENT - JavaScript
 */

let paymentCheckInterval;
let timerInterval;
let pollingInterval;

document.addEventListener('DOMContentLoaded', function() {
    // Ẩn overlay loading ngay khi trang load
    const qrOverlay = document.getElementById('qrOverlay');
    if (qrOverlay) {
        qrOverlay.style.display = 'none';
    }
    
    startCountdown();
    startPaymentCheck();
    
    // Bắt đầu polling kiểm tra thanh toán tự động
    startAutoPaymentVerification();
});

/**
 * Đếm ngược thời gian còn lại - TẮT TÍNH NĂNG NÀY
 */
function startCountdown() {
    const timerEl = document.getElementById('timer');
    // Tắt timer - chỉ hiển thị text
    timerEl.textContent = '∞ (không giới hạn)';
    return;
    
    /* TIMER CODE - ĐANG TẮT
    const expiredTime = new Date(expiredAt).getTime();
    
    timerInterval = setInterval(function() {
        const now = new Date().getTime();
        const timeLeft = expiredTime - now;
        
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            clearInterval(paymentCheckInterval);
            alert('Hết thời gian thanh toán! Đơn hàng đã bị hủy.');
            window.location.href = '/';
            return;
        }
        
        const minutes = Math.floor(timeLeft / 1000 / 60);
        const seconds = Math.floor((timeLeft / 1000) % 60);
        
        timerEl.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        
        // Cảnh báo khi còn 3 phút
        if (timeLeft <= 180000 && timeLeft > 179000) {
            showWarning('Chỉ còn 3 phút! Vui lòng nhanh chóng hoàn tất thanh toán!');
        }
    }, 1000);
    */
}

/**
 * Kiểm tra trạng thái thanh toán định kỳ
 */
function startPaymentCheck() {
    paymentCheckInterval = setInterval(function() {
        checkPaymentStatus();
    }, 3000); // Kiểm tra mỗi 3 giây
}

/**
 * Kiểm tra trạng thái thanh toán
 */
function checkPaymentStatus() {
    fetch(`/src/controllers/paymentController.php?action=check_payment&bookingID=${bookingID}`)
        .then(response => response.json())
        .then(data => {
            if (data.requireLogin) {
                window.location.href = '/?openLogin=1';
                return;
            }
            if (data.success) {
                if (data.paymentStatus === 'paid') {
                    // Thanh toán thành công
                    clearInterval(paymentCheckInterval);
                    clearInterval(timerInterval);
                    showPaymentSuccess();
                } else if (data.expired) {
                    // Hết hạn
                    clearInterval(paymentCheckInterval);
                    clearInterval(timerInterval);
                    alert('Đơn hàng đã hết hạn!');
                    window.location.href = '/';
                }
            }
        })
        .catch(error => {
            console.error('Error checking payment:', error);
        });
}

/**
 * Hiển thị thành công
 */
function showPaymentSuccess() {
    const paymentStatus = document.getElementById('paymentStatus');
    const qrOverlay = document.getElementById('qrOverlay');
    
    if (qrOverlay) {
        qrOverlay.style.display = 'none';
    }
    
    if (paymentStatus) {
        paymentStatus.style.display = 'block';
    }
    
    // Chuyển sang trang xác nhận sau 2 giây
    setTimeout(function() {
        window.location.href = `/src/views/booking_step4_confirm.php?bookingID=${bookingID}`;
    }, 2000);
}

/**
 * Copy to clipboard
 */
function copyToClipboard(text) {
    // Tạo element tạm
    const tempInput = document.createElement('input');
    tempInput.value = text;
    document.body.appendChild(tempInput);
    tempInput.select();
    document.execCommand('copy');
    document.body.removeChild(tempInput);
    
    // Hiển thị thông báo
    showNotification('Đã sao chép: ' + text);
}

/**
 * Hiển thị thông báo
 */
function showNotification(message) {
    // Tạo notification element
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #4caf50;
        color: white;
        padding: 15px 25px;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        z-index: 9999;
        animation: slideIn 0.3s ease-out;
    `;
    notification.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
    
    document.body.appendChild(notification);
    
    // Tự động xóa sau 3 giây
    setTimeout(function() {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

/**
 * Hiển thị cảnh báo
 */
function showWarning(message) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #ff9800;
        color: white;
        padding: 15px 25px;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        z-index: 9999;
        animation: pulse 1s infinite;
    `;
    notification.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${message}`;
    
    document.body.appendChild(notification);
    
    setTimeout(function() {
        notification.remove();
    }, 5000);
}

/**
 * Xác nhận thanh toán thủ công (cho admin hoặc test)
 */
function manualConfirmPayment() {
    const transactionCode = prompt('Nhập mã giao dịch:');
    
    if (!transactionCode) return;
    
    fetch('/src/controllers/paymentController.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=confirm_payment&bookingID=${bookingID}&transactionCode=${transactionCode}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.requireLogin) {
            window.location.href = '/?openLogin=1';
            return;
        }
        if (data.success) {
            showPaymentSuccess();
        } else {
            alert(data.message || 'Không thể xác nhận thanh toán');
        }
    })
    .catch(error => {
        console.error('Error confirming payment:', error);
        alert('Có lỗi xảy ra');
    });
}

/**
 * DEV/DEBUG: Xác nhận thanh toán nhanh để test
 */
function devConfirmPayment() {
    if (!confirm('🚨 Xác nhận thanh toán giả lập?\n\nChức năng này chỉ dùng để DEV/DEBUG!')) {
        return;
    }
    
    console.log('🔧 DEV: Đang xác nhận thanh toán...');
    
    const btn = document.querySelector('.btn-dev-confirm');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
    }
    
    fetch('/src/controllers/paymentController.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=confirm_payment_manual&bookingID=${window.bookingID}`
    })
    .then(response => response.json())
    .then(data => {
        console.log('🔧 DEV: Response:', data);
        
        if (data.requireLogin) {
            window.location.href = '/?openLogin=1';
            return;
        }
        
        if (data.success) {
            console.log('✅ DEV: Thanh toán thành công!');
            showPaymentSuccess();
            
            // Chuyển trang sau 2 giây
            setTimeout(() => {
                window.location.href = `/src/views/booking_step4_confirm.php?bookingID=${window.bookingID}`;
            }, 2000);
        } else {
            alert('❌ Lỗi: ' + (data.message || 'Không thể xác nhận thanh toán'));
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle"></i> Xác nhận thanh toán (DEV)';
            }
        }
    })
    .catch(error => {
        console.error('❌ DEV: Error:', error);
        alert('Có lỗi xảy ra: ' + error.message);
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle"></i> Xác nhận thanh toán (DEV)';
        }
    });
}

/**
 * Cleanup khi rời trang
 */
window.addEventListener('beforeunload', function() {
    clearInterval(paymentCheckInterval);
    clearInterval(timerInterval);
});

// CSS animations
const style = document.createElement('style');
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

// Manual payment confirmation
function confirmPaymentManual() {
    const bookingId = document.getElementById('bookingId').value;
    
    if (!bookingId) {
        alert('Không tìm thấy thông tin đặt vé');
        return;
    }
    
    // Show loading state
    const confirmBtn = event.target;
    const originalText = confirmBtn.innerHTML;
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xác nhận...';
    
    fetch('/src/controllers/paymentController.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=confirm_payment_manual&booking_id=${bookingId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showPaymentSuccess();
            setTimeout(() => {
                window.location.href = `/src/views/booking_step4_confirm.php?booking_id=${bookingId}`;
            }, 1500);
        } else {
            alert(data.message || 'Có lỗi xảy ra khi xác nhận thanh toán');
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi xác nhận thanh toán');
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = originalText;
    });
}

/**
 * Tự động kiểm tra thanh toán qua API ngân hàng (polling)
 * Kiểm tra mỗi 5 giây để xem có giao dịch khớp không
 */
function startAutoPaymentVerification() {
    // Lấy bookingID từ biến global đã được define trong view
    const bookingId = window.bookingID || document.getElementById('bookingId')?.value;
    const paymentMethod = document.querySelector('.payment-method');
    const totalAmount = paymentMethod ? paymentMethod.dataset.amount : null;
    
    if (!bookingId) {
        console.error('❌ Booking ID not found');
        console.log('💡 Tip: Kiểm tra xem biến bookingID đã được define trong view chưa');
        return;
    }
    
    if (!totalAmount) {
        console.error('❌ Total amount not found');
        console.log('💡 Tip: Kiểm tra xem element .payment-method có data-amount không');
        return;
    }
    
    console.log('🔍 Bắt đầu tự động kiểm tra thanh toán...');
    console.log('📋 Booking ID:', bookingId);
    console.log('💰 Total Amount:', totalAmount);
    
    // Kiểm tra ngay lập tức
    checkBankTransaction(bookingId, totalAmount);
    
    // Sau đó kiểm tra mỗi 5 giây
    pollingInterval = setInterval(() => {
        checkBankTransaction(bookingId, totalAmount);
    }, 5000); // 5 giây
}

/**
 * Kiểm tra giao dịch ngân hàng có khớp với booking không
 */
function checkBankTransaction(bookingId, expectedAmount) {
    console.log(`🔄 Đang gọi API... (Booking: ${bookingId}, Amount: ${expectedAmount})`);
    
    fetch('/src/controllers/paymentController.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=verify_bank_transaction&booking_id=${bookingId}&amount=${expectedAmount}`
    })
    .then(response => {
        console.log('📡 Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('📦 Response data:', data);
        
        if (data.success && data.transaction_found) {
            console.log('✅ Giao dịch được tìm thấy:', data);
            
            // Dừng polling
            clearInterval(pollingInterval);
            
            // Hiển thị thông báo thành công
            showPaymentSuccess();
            
            // Chuyển trang sau 2 giây
            setTimeout(() => {
                window.location.href = `/src/views/booking_step4_confirm.php?bookingID=${bookingId}`;
            }, 2000);
        } else {
            console.log('⏳ Chưa tìm thấy giao dịch, tiếp tục kiểm tra...');
            if (data.message) {
                console.log('💬 Message:', data.message);
            }
        }
    })
    .catch(error => {
        console.error('❌ Error checking bank transaction:', error);
    });
}

/**
 * Dừng polling khi user rời khỏi trang
 */
window.addEventListener('beforeunload', function() {
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
});
