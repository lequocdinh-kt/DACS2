/**
 * VKU Cinema - Movies Page JavaScript
 */

let currentFilter = 'now-showing';
let allMovies = [];

// ==================== INITIALIZATION ====================
document.addEventListener('DOMContentLoaded', function() {
    // console.log('Movies page loaded');
    initializeFilters();
    initializeSearch();
    loadMovies();
});

// ==================== FILTER INITIALIZATION ====================
function initializeFilters() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons
            filterButtons.forEach(b => b.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Update current filter
            currentFilter = this.dataset.filter;
            
            // Load movies with new filter
            loadMovies();
        });
    });
}

// ==================== SEARCH INITIALIZATION ====================
function initializeSearch() {
    const searchInput = document.getElementById('movieSearch');
    let searchTimeout;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        
        const keyword = this.value.trim();
        // console.log('Input changed, keyword:', keyword);
        
        if (keyword.length === 0) {
            // If empty, show all movies with current filter
            displayMovies(allMovies);
            return;
        }
        
        // Search locally trong allMovies đã được filter
        searchTimeout = setTimeout(() => {
            // console.log('Searching locally in filtered movies...');
            const filtered = allMovies.filter(movie => 
                movie.title.toLowerCase().includes(keyword.toLowerCase()) ||
                movie.genre.toLowerCase().includes(keyword.toLowerCase()) ||
                movie.author.toLowerCase().includes(keyword.toLowerCase())
            );
            // console.log('Local search results:', filtered.length);
            
            if (filtered.length > 0) {
                displayMovies(filtered);
            } else {
                showEmptyState(`Không tìm thấy phim với từ khóa "${keyword}"`);
            }
        }, 300);
    });
}

// ==================== LOAD MOVIES ====================
async function loadMovies() {
    const moviesGrid = document.getElementById('moviesGrid');
    
    // Show loading
    moviesGrid.innerHTML = `
        <div class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Đang tải danh sách phim...</p>
        </div>
    `;
    
    try {
        const response = await fetch(`/src/controllers/moviesController.php?action=get_movies&filter=${currentFilter}`);
        const data = await response.json();
        
        if (data.success) {
            allMovies = data.movies;
            displayMovies(data.movies);
        } else {
            showEmptyState('Không thể tải danh sách phim');
        }
    } catch (error) {
        // console.error('Error loading movies:', error);
        showEmptyState('Có lỗi xảy ra khi tải danh sách phim');
    }
}

// ==================== SEARCH MOVIES ====================
async function searchMovies(keyword) {
    const moviesGrid = document.getElementById('moviesGrid');
    
    // Show loading
    moviesGrid.innerHTML = `
        <div class="loading-state">
            <i class="fas fa-search"></i>
            <p>Đang tìm kiếm...</p>
        </div>
    `;
    
    try {
        // console.log('Searching for:', keyword);
        const response = await fetch(`/src/controllers/moviesController.php?action=search_movies&keyword=${encodeURIComponent(keyword)}`);
        // console.log('Response status:', response.status);
        const data = await response.json();
        // console.log('Search results:', data);
        
        if (data.success) {
            if (data.movies.length > 0) {
                displayMovies(data.movies);
            } else {
                showEmptyState(`Không tìm thấy phim với từ khóa "${keyword}"`);
            }
        } else {
            // console.error('Search failed:', data.message);
            showEmptyState('Không thể tìm kiếm phim');
        }
    } catch (error) {
        // console.error('Error searching movies:', error);
        showEmptyState('Có lỗi xảy ra khi tìm kiếm');
    }
}

// ==================== DISPLAY MOVIES ====================
function displayMovies(movies) {
    const moviesGrid = document.getElementById('moviesGrid');
    
    if (!movies || movies.length === 0) {
        showEmptyState('Không có phim nào');
        return;
    }
    
    moviesGrid.innerHTML = movies.map(movie => createMovieCard(movie)).join('');
}

function createMovieCard(movie) {
    const statusClass = movie.movieStatus === 'now_showing' ? 'now-showing' : 'coming-soon';
    const statusText = movie.movieStatus === 'now_showing' ? 'Đang chiếu' : 'Sắp chiếu';
    const statusIcon = movie.movieStatus === 'now_showing' ? 'fa-play-circle' : 'fa-calendar-alt';
    
    return `
        <div class="movie-card" data-movie-id="${movie.movieID}">
            <a href="index.php?page=movie_detail&id=${movie.movieID}" class="movie-card-link">
                <div class="movie-poster">
                    <img src="${movie.posterURL}" alt="${movie.title}">
                    <div class="movie-overlay">
                        <div class="overlay-content">
                            <i class="fas fa-play-circle play-icon"></i>
                            <p>Xem chi tiết</p>
                        </div>
                    </div>
                    <span class="status-badge ${statusClass}">
                        <i class="fas ${statusIcon}"></i> ${statusText}
                    </span>
                </div>
                <div class="movie-info">
                    <h3 class="movie-title">${movie.title}</h3>
                    <div class="movie-meta">
                        <div class="movie-rating">
                            <i class="fas fa-star"></i>
                            <span>${movie.rating}</span>
                        </div>
                        <div class="movie-duration">
                            <i class="fas fa-clock"></i>
                            <span>${movie.duration} phút</span>
                        </div>
                    </div>
                    <div class="movie-genre">
                        <i class="fas fa-tag"></i>
                        <span>${movie.genre}</span>
                    </div>
                </div>
            </a>
        </div>
    `;
}

// ==================== EMPTY STATE ====================
function showEmptyState(message) {
    const moviesGrid = document.getElementById('moviesGrid');
    moviesGrid.innerHTML = `
        <div class="empty-state">
            <i class="fas fa-film"></i>
            <h3>Không có phim</h3>
            <p>${message}</p>
        </div>
    `;
}
