/**
 * VKU Cinema - Deals Page JavaScript
 */

let allPromotions = [];

// ==================== INITIALIZATION ====================
document.addEventListener('DOMContentLoaded', function() {
    console.log('Deals page loaded');
    loadPromotions();
});

// ==================== LOAD PROMOTIONS ====================
async function loadPromotions() {
    const dealsGrid = document.querySelector('.deals-grid');
    
    // Show loading
    dealsGrid.innerHTML = `
        <div class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Đang tải ưu đãi...</p>
        </div>
    `;
    
    try {
        const response = await fetch('/src/controllers/dealsController.php?action=get_promotions');
        const data = await response.json();
        
        console.log('Promotions loaded:', data);
        
        if (data.success && data.promotions.length > 0) {
            allPromotions = data.promotions;
            displayPromotions(data.promotions);
        } else {
            showEmptyState('Hiện không có chương trình ưu đãi nào');
        }
    } catch (error) {
        console.error('Error loading promotions:', error);
        showEmptyState('Có lỗi xảy ra khi tải ưu đãi');
    }
}

// ==================== DISPLAY PROMOTIONS ====================
function displayPromotions(promotions) {
    const dealsGrid = document.querySelector('.deals-grid');
    
    dealsGrid.innerHTML = promotions.map(promo => createPromotionCard(promo)).join('');
    
    // Add event listeners to buttons
    document.querySelectorAll('.deal-detail-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const code = this.dataset.code;
            showPromotionModal(code);
        });
    });
}

function createPromotionCard(promo) {
    // Xác định icon dựa trên loại khuyến mãi
    let icon = 'fa-gift';
    let iconColor = '#667eea';
    
    if (promo.code.includes('STUDENT')) {
        icon = 'fa-graduation-cap';
    } else if (promo.code.includes('WEEKEND')) {
        icon = 'fa-calendar-week';
    } else if (promo.code.includes('VIP')) {
        icon = 'fa-crown';
        iconColor = '#ffd700';
    } else if (promo.code.includes('COMBO')) {
        icon = 'fa-shopping-basket';
    } else if (promo.discountType === 'percent') {
        icon = 'fa-percent';
    } else {
        icon = 'fa-tag';
    }
    
    // Format giá trị giảm giá
    let discountText = '';
    if (promo.discountType === 'percent') {
        discountText = `Giảm ${promo.discountValue}%`;
    } else {
        discountText = `Giảm ${parseInt(promo.discountValue).toLocaleString('vi-VN')}đ`;
    }
    
    // Format ngày hết hạn
    let validText = '';
    if (promo.endDate) {
        const endDate = new Date(promo.endDate);
        validText = `Đến ${endDate.toLocaleDateString('vi-VN')}`;
    } else {
        validText = 'Không giới hạn';
    }
    
    return `
        <div class="deal-card">
            <div class="deal-content">
                <div class="deal-header">
                    <div class="deal-icon">
                        <i class="fas ${icon}" style="color: ${iconColor};"></i>
                    </div>
                    <div class="deal-title-wrapper">
                        <h3 class="deal-title">${discountText}</h3>
                        <span class="deal-code">Mã: ${promo.code}</span>
                    </div>
                </div>
                <p class="deal-description">${promo.description}</p>
                <div class="deal-info">
                    <div class="deal-info-item">
                        <i class="fas fa-calendar"></i>
                        <span>${validText}</span>
                    </div>
                    ${promo.minOrderValue > 0 ? `
                    <div class="deal-info-item">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Tối thiểu ${parseInt(promo.minOrderValue).toLocaleString('vi-VN')}đ</span>
                    </div>
                    ` : ''}
                    ${promo.maxDiscount ? `
                    <div class="deal-info-item">
                        <i class="fas fa-coins"></i>
                        <span>Giảm tối đa ${parseInt(promo.maxDiscount).toLocaleString('vi-VN')}đ</span>
                    </div>
                    ` : ''}
                </div>
                <button class="deal-detail-btn" data-code="${promo.code}">
                    <i class="fas fa-info-circle"></i> Chi tiết
                </button>
            </div>
        </div>
    `;
}

// ==================== SHOW PROMOTION MODAL ====================
function showPromotionModal(code) {
    const promo = allPromotions.find(p => p.code === code);
    if (!promo) return;
    
    // Tạo modal content
    let modalContent = `
        <div class="promo-modal-overlay" onclick="closePromoModal()">
            <div class="promo-modal-content" onclick="event.stopPropagation()">
                <button class="promo-modal-close" onclick="closePromoModal()">
                    <i class="fas fa-times"></i>
                </button>
                <div class="promo-modal-header">
                    <h2>${promo.description}</h2>
                    <div class="promo-code-box">
                        <span class="promo-code-label">Mã khuyến mãi:</span>
                        <span class="promo-code-value">${promo.code}</span>
                        <button class="promo-copy-btn" onclick="copyPromoCode('${promo.code}')">
                            <i class="fas fa-copy"></i> Sao chép
                        </button>
                    </div>
                </div>
                <div class="promo-modal-body">
                    <h3>Điều kiện áp dụng:</h3>
                    <ul>
                        ${promo.minOrderValue > 0 ? `<li>Áp dụng cho đơn hàng từ ${parseInt(promo.minOrderValue).toLocaleString('vi-VN')}đ</li>` : ''}
                        ${promo.maxDiscount ? `<li>Giảm tối đa ${parseInt(promo.maxDiscount).toLocaleString('vi-VN')}đ</li>` : ''}
                        ${promo.usageLimit > 0 ? `<li>Giới hạn ${promo.usageLimit} lượt sử dụng</li>` : '<li>Không giới hạn số lần sử dụng</li>'}
                        <li>Có hiệu lực từ ${new Date(promo.startDate).toLocaleDateString('vi-VN')} 
                            ${promo.endDate ? `đến ${new Date(promo.endDate).toLocaleDateString('vi-VN')}` : ''}</li>
                    </ul>
                    
                    <h3>Hướng dẫn sử dụng:</h3>
                    <ol>
                        <li>Chọn phim và suất chiếu</li>
                        <li>Chọn ghế ngồi</li>
                        <li>Tại trang thanh toán, nhập mã <strong>${promo.code}</strong></li>
                        <li>Nhấn "Áp dụng" để nhận ưu đãi</li>
                    </ol>
                </div>
                <div class="promo-modal-footer">
                    <button class="promo-use-btn" onclick="window.location.href='index.php?page=movies'">
                        <i class="fas fa-ticket-alt"></i> Đặt vé ngay
                    </button>
                </div>
            </div>
        </div>
    `;
    
    // Thêm modal vào body
    document.body.insertAdjacentHTML('beforeend', modalContent);
    document.body.style.overflow = 'hidden';
}

function closePromoModal() {
    const modal = document.querySelector('.promo-modal-overlay');
    if (modal) {
        modal.remove();
        document.body.style.overflow = '';
    }
}

function copyPromoCode(code) {
    navigator.clipboard.writeText(code).then(() => {
        // Show success message
        const btn = event.target.closest('.promo-copy-btn');
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Đã sao chép';
        btn.style.background = '#4caf50';
        
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.style.background = '';
        }, 2000);
    });
}

// ==================== EMPTY STATE ====================
function showEmptyState(message) {
    const dealsGrid = document.querySelector('.deals-grid');
    dealsGrid.innerHTML = `
        <div class="empty-state">
            <i class="fas fa-gift"></i>
            <h3>Không có ưu đãi</h3>
            <p>${message}</p>
        </div>
    `;
}
