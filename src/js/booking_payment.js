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
    
    // 🔥 STRATEGY: Check database thường xuyên (nhanh), check API ít hơn (tốn phí)
    startPaymentCheck();                    // Check DB mỗi 3 giây
    startAutoPaymentVerification();         // Check API Casso mỗi 5 giây
    
    console.log('💡 TIP: Trang này đang tự động kiểm tra thanh toán:');
    console.log('   📊 Check database: mỗi 3 giây');
    console.log('   🏦 Check API Casso: mỗi 5 giây');
    console.log('   🚀 Sẽ tự động chuyển trang khi phát hiện thanh toán thành công!');
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
            console.log('🔄 [checkPaymentStatus] Response:', data);
            
            if (data.requireLogin) {
                window.location.href = '/?openLogin=1';
                return;
            }
            if (data.success) {
                console.log('📊 Payment Status:', data.paymentStatus);
                
                if (data.paymentStatus === 'paid') {
                    // Thanh toán thành công
                    console.log('✅ Payment confirmed! Redirecting...');
                    clearInterval(paymentCheckInterval);
                    clearInterval(timerInterval);
                    clearInterval(pollingInterval); // 🔥 Dừng polling luôn
                    showPaymentSuccess();
                } else if (data.expired) {
                    // Hết hạn
                    clearInterval(paymentCheckInterval);
                    clearInterval(timerInterval);
                    clearInterval(pollingInterval);
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
    console.log('=' .repeat(60));
    console.log(`🔄 [${new Date().toLocaleTimeString()}] Checking bank transaction...`);
    console.log(`   📋 Booking ID: ${bookingId}`);
    console.log(`   💰 Expected Amount: ${expectedAmount}`);
    
    fetch('/src/controllers/paymentController.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=verify_bank_transaction&booking_id=${bookingId}&amount=${expectedAmount}`
    })
    .then(response => {
        console.log('📡 Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        // 🔥 FIX: Clone response để log cả text và JSON
        return response.clone().text().then(text => {
            console.log('📄 Response text:', text.substring(0, 500)); // Log 500 ký tự đầu
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('❌ JSON Parse Error:', e);
                console.error('❌ Full response:', text);
                throw new Error('Server returned invalid JSON. Check PHP errors.');
            }
        });
    })
    .then(data => {
        console.log('📦 Response data:', data);
        
        if (data.success && data.transaction_found) {
            console.log('✅✅✅ PAYMENT CONFIRMED! ✅✅✅');
            console.log('   Transaction Code:', data.transaction_code);
            
            // Dừng TẤT CẢ polling
            clearInterval(pollingInterval);
            clearInterval(paymentCheckInterval);
            clearInterval(timerInterval);
            
            // Hiển thị thông báo thành công
            showPaymentSuccess();
            
            // Chuyển trang sau 2 giây
            setTimeout(() => {
                console.log('🔄 Redirecting to confirmation page...');
                window.location.href = `/src/views/booking_step4_confirm.php?bookingID=${bookingId}`;
            }, 2000);
        } else {
            console.log('⏳ Transaction not found yet, will retry in 5s...');
            if (data.message) {
                console.log('   💬 Message:', data.message);
            }
        }
        console.log('=' .repeat(60));
    })
    .catch(error => {
        console.error('❌ Error checking bank transaction:', error);
        console.log('=' .repeat(60));
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

/**
 * ==============================
 * PROMOTION CODE FUNCTIONS
 * ==============================
 */

let appliedPromotion = null;
const originalPrice = window.bookingAmount;

/**
 * Áp dụng mã giảm giá
 */
async function applyPromoCode() {
    const promoInput = document.getElementById('promoCode');
    const promoCode = promoInput.value.trim().toUpperCase();
    
    if (!promoCode) {
        showPromoMessage('Vui lòng nhập mã giảm giá', 'error');
        return;
    }
    
    if (appliedPromotion) {
        showPromoMessage('Bạn đã áp dụng mã giảm giá rồi!', 'error');
        return;
    }
    
    // Disable button khi đang xử lý
    const applyBtn = event.target;
    const originalText = applyBtn.innerHTML;
    applyBtn.disabled = true;
    applyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang kiểm tra...';
    
    try {
        const response = await fetch('/src/controllers/paymentController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=validate_promo&promo_code=${promoCode}&booking_id=${window.bookingID}&amount=${originalPrice}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Áp dụng thành công
            appliedPromotion = {
                code: promoCode,
                discount: data.discount,
                finalPrice: data.final_price,
                description: data.description
            };
            
            updatePriceDisplay();
            showPromoMessage(`✅ ${data.message}`, 'success');
            
            // Disable input sau khi áp dụng
            promoInput.disabled = true;
            
        } else {
            showPromoMessage(data.message || 'Mã giảm giá không hợp lệ', 'error');
        }
        
    } catch (error) {
        console.error('Error validating promo code:', error);
        showPromoMessage('Có lỗi xảy ra, vui lòng thử lại', 'error');
    } finally {
        applyBtn.disabled = false;
        applyBtn.innerHTML = originalText;
    }
}

/**
 * Xóa mã giảm giá
 */
function removePromoCode() {
    appliedPromotion = null;
    
    // Reset input
    const promoInput = document.getElementById('promoCode');
    promoInput.value = '';
    promoInput.disabled = false;
    
    // Ẩn sections
    document.getElementById('promoDiscount').style.display = 'none';
    document.getElementById('finalPriceSection').style.display = 'none';
    
    // Ẩn message
    const promoMessage = document.getElementById('promoMessage');
    promoMessage.style.display = 'none';
    
    // Reset giá gốc
    document.getElementById('originalPrice').textContent = formatPrice(originalPrice);
    
    // Cập nhật QR code về giá gốc
    updateQRCode(originalPrice);
    
    showPromoMessage('Đã xóa mã giảm giá', 'success');
    setTimeout(() => {
        document.getElementById('promoMessage').style.display = 'none';
    }, 2000);
}

/**
 * Cập nhật hiển thị giá sau khi áp dụng mã
 */
function updatePriceDisplay() {
    if (!appliedPromotion) return;
    
    // Hiện discount section
    const promoDiscount = document.getElementById('promoDiscount');
    promoDiscount.style.display = 'block';
    
    // Cập nhật thông tin discount
    document.getElementById('promoCodeApplied').textContent = appliedPromotion.code;
    document.getElementById('discountAmount').textContent = formatPrice(appliedPromotion.discount);
    
    // Hiện final price
    const finalPriceSection = document.getElementById('finalPriceSection');
    finalPriceSection.style.display = 'flex';
    document.getElementById('finalPrice').textContent = formatPrice(appliedPromotion.finalPrice);
    
    // Cập nhật QR code với giá mới
    updateQRCode(appliedPromotion.finalPrice);
}

/**
 * Cập nhật QR code với số tiền mới
 */
function updateQRCode(amount) {
    const qrImage = document.getElementById('qrCode');
    const bankAccount = '0795701805'; // Số tài khoản
    const accountName = 'LE QUOC DINH';
    const description = `VKU CINEMA ${window.bookingCode}`;
    
    // Tạo URL QR mới
    const qrUrl = `https://img.vietqr.io/image/MB-${bankAccount}-compact2.png?amount=${amount}&addInfo=${encodeURIComponent(description)}&accountName=${encodeURIComponent(accountName)}`;
    
    qrImage.src = qrUrl;
    
    // Cập nhật số tiền hiển thị trong bank info
    const amountElements = document.querySelectorAll('.bank-detail strong');
    amountElements.forEach(el => {
        if (el.textContent.includes('đ') && !el.textContent.includes('VKU')) {
            el.textContent = formatPrice(amount) + 'đ';
        }
    });
}

/**
 * Hiển thị thông báo promo
 */
function showPromoMessage(message, type) {
    const promoMessage = document.getElementById('promoMessage');
    promoMessage.textContent = message;
    promoMessage.className = `promo-message ${type}`;
    promoMessage.style.display = 'block';
    
    // Tự động ẩn sau 5 giây
    setTimeout(() => {
        promoMessage.style.display = 'none';
    }, 5000);
}

/**
 * Format giá tiền
 */
function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN').format(price);
}

/**
 * Enter key để apply promo
 */
document.addEventListener('DOMContentLoaded', function() {
    const promoInput = document.getElementById('promoCode');
    if (promoInput) {
        promoInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyPromoCode();
            }
        });
    }
});

/**
 * Chọn mã từ danh sách có sẵn
 */
function selectPromo(promoCode) {
    const promoInput = document.getElementById('promoCode');
    promoInput.value = promoCode;
    
    // Scroll đến input
    promoInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
    
    // Highlight input
    promoInput.focus();
    
    // Auto apply sau 500ms
    setTimeout(() => {
        applyPromoCode();
    }, 300);
}
