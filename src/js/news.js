/**
 * News Page JavaScript
 * Xử lý hiển thị tin tức từ database
 */

document.addEventListener('DOMContentLoaded', () => {
    loadNews();
    setupFilters();
});

/**
 * Load tin tức từ server
 */
async function loadNews(type = null) {
    try {
        const newsGrid = document.querySelector('.news-grid');
        
        // Hiển thị loading state
        newsGrid.innerHTML = `
            <div class="loading-state">
                <i class="fas fa-newspaper"></i>
                <p>Đang tải tin tức...</p>
            </div>
        `;
        
        // Gọi API
        let url = '/src/controllers/newsController.php?action=get_news&limit=12';
        if (type) {
            url += `&type=${type}`;
        }
        
        const response = await fetch(url);
        const result = await response.json();
        
        if (result.success && result.data.length > 0) {
            displayNews(result.data);
        } else {
            showEmptyState();
        }
    } catch (error) {
        // console.error('Error loading news:', error);
        showErrorState();
    }
}

/**
 * Hiển thị danh sách tin tức
 */
function displayNews(newsList) {
    const newsGrid = document.querySelector('.news-grid');
    newsGrid.innerHTML = '';
    
    newsList.forEach(news => {
        const newsCard = createNewsCard(news);
        newsGrid.appendChild(newsCard);
    });
}

/**
 * Tạo card tin tức
 */
function createNewsCard(news) {
    const card = document.createElement('div');
    card.className = 'news-card';
    
    // Icon theo loại tin
    const iconMap = {
        'promotion': 'fa-gift',
        'event': 'fa-calendar-star',
        'announcement': 'fa-bullhorn',
        'news': 'fa-newspaper'
    };
    const icon = iconMap[news.type] || 'fa-newspaper';
    
    // Xử lý hình ảnh
    let imageHTML;
    if (news.imageURL) {
        imageHTML = `<img src="${news.imageURL}" alt="${news.title}">`;
    } else {
        imageHTML = `
            <div class="news-image-placeholder">
                <i class="fas ${icon}"></i>
            </div>
        `;
    }
    
    card.innerHTML = `
        <div class="news-image">
            ${imageHTML}
            <span class="news-type-badge">${news.typeLabel}</span>
        </div>
        <div class="news-content">
            <div class="news-meta">
                <span><i class="fas fa-calendar"></i> ${news.formattedDate}</span>
                ${news.viewCount ? `<span><i class="fas fa-eye"></i> ${news.viewCount}</span>` : ''}
            </div>
            <h3 class="news-title">${news.title}</h3>
            <p class="news-excerpt">${news.excerpt || news.content.substring(0, 150) + '...'}</p>
            <button class="news-read-more" onclick="showNewsDetail(${news.newsID})">
                Đọc thêm <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    `;
    
    return card;
}

/**
 * Hiển thị empty state
 */
function showEmptyState() {
    const newsGrid = document.querySelector('.news-grid');
    newsGrid.innerHTML = `
        <div class="empty-state">
            <i class="fas fa-newspaper"></i>
            <p>Chưa có tin tức nào</p>
        </div>
    `;
}

/**
 * Hiển thị error state
 */
function showErrorState() {
    const newsGrid = document.querySelector('.news-grid');
    newsGrid.innerHTML = `
        <div class="error-state">
            <i class="fas fa-exclamation-triangle"></i>
            <p>Không thể tải tin tức. Vui lòng thử lại sau.</p>
        </div>
    `;
}

/**
 * Setup bộ lọc tin tức
 */
function setupFilters() {
    const filterButtons = document.querySelectorAll('.news-filter-btn');
    
    filterButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            // Remove active từ tất cả
            filterButtons.forEach(b => b.classList.remove('active'));
            // Add active cho button được click
            btn.classList.add('active');
            
            // Load tin tức theo loại
            const type = btn.dataset.type;
            loadNews(type);
        });
    });
}

/**
 * Hiển thị chi tiết tin tức trong modal
 */
async function showNewsDetail(newsID) {
    try {
        const response = await fetch(`/src/controllers/newsController.php?action=get_news_detail&id=${newsID}`);
        const result = await response.json();
        
        if (result.success) {
            const news = result.data;
            
            // Tạo modal
            const modal = document.createElement('div');
            modal.className = 'news-modal';
            modal.innerHTML = `
                <div class="news-modal-overlay"></div>
                <div class="news-modal-content">
                    <button class="news-modal-close">
                        <i class="fas fa-times"></i>
                    </button>
                    <div class="news-modal-header">
                        ${news.imageURL ? `<img src="${news.imageURL}" alt="${news.title}">` : ''}
                        <span class="news-type-badge">${news.typeLabel}</span>
                    </div>
                    <div class="news-modal-body">
                        <h2>${news.title}</h2>
                        <div class="news-meta">
                            <span><i class="fas fa-calendar"></i> ${news.formattedDate}</span>
                            <span><i class="fas fa-eye"></i> ${news.viewCount || 0} lượt xem</span>
                        </div>
                        <div class="news-content-full">
                            ${news.content}
                        </div>
                        ${news.movieTitle ? `
                            <div class="news-related-movie">
                                <h4><i class="fas fa-film"></i> Phim liên quan</h4>
                                <p>${news.movieTitle}</p>
                            </div>
                        ` : ''}
                        ${news.promotionCode ? `
                            <div class="news-promo-code">
                                <h4><i class="fas fa-ticket-alt"></i> Mã khuyến mãi</h4>
                                <div class="promo-code-box">
                                    <span class="promo-code-value">${news.promotionCode}</span>
                                    <button class="promo-copy-btn" onclick="copyPromoCode('${news.promotionCode}')">
                                        <i class="fas fa-copy"></i> Sao chép
                                    </button>
                                </div>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Close modal
            modal.querySelector('.news-modal-close').addEventListener('click', () => {
                modal.remove();
            });
            
            modal.querySelector('.news-modal-overlay').addEventListener('click', () => {
                modal.remove();
            });
            
            // Hiển thị modal với animation
            setTimeout(() => modal.classList.add('show'), 10);
        }
    } catch (error) {
        console.error('Error loading news detail:', error);
        alert('Không thể tải chi tiết tin tức');
    }
}

/**
 * Copy mã khuyến mãi
 */
function copyPromoCode(code) {
    navigator.clipboard.writeText(code).then(() => {
        // Hiển thị thông báo
        const btn = event.target.closest('.promo-copy-btn');
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Đã sao chép';
        btn.style.background = '#00ff88';
        
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.style.background = '';
        }, 2000);
    }).catch(err => {
        console.error('Error copying:', err);
        alert('Không thể sao chép mã');
    });
}
