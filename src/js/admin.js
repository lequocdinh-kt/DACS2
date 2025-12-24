// Admin Dashboard JavaScript
let revenueChart = null;

// Load dashboard data when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardStats();
    loadRevenueChart();
    loadShowtimes();
    loadTopMovies();
    loadRecentBookings();
    loadMoviesList();
    loadRoomsList();
    
    // Chart period change
    document.getElementById('chartPeriod').addEventListener('change', loadRevenueChart);
    
    // Showtime form submit
    document.getElementById('showtimeForm').addEventListener('submit', handleShowtimeSubmit);
});

// Load dashboard statistics
async function loadDashboardStats() {
    try {
        const response = await fetch('/src/controllers/adminController.php?action=getStats');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('totalRevenue').textContent = 
                formatCurrency(data.stats.totalRevenue);
            document.getElementById('totalBookings').textContent = 
                data.stats.totalBookings;
            document.getElementById('totalUsers').textContent = 
                data.stats.totalUsers;
            document.getElementById('totalMovies').textContent = 
                data.stats.totalMovies;
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

// Load revenue chart
async function loadRevenueChart() {
    const period = document.getElementById('chartPeriod').value;
    
    try {
        const response = await fetch(`/src/controllers/adminController.php?action=getRevenueChart&period=${period}`);
        const data = await response.json();
        
        if (data.success) {
            const ctx = document.getElementById('revenueChart').getContext('2d');
            
            // Destroy existing chart if exists
            if (revenueChart) {
                revenueChart.destroy();
            }
            
            revenueChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Doanh thu (VNĐ)',
                        data: data.values,
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.15)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointHoverRadius: 8,
                        pointBackgroundColor: '#667eea',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointHoverBackgroundColor: '#764ba2',
                        pointHoverBorderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                font: {
                                    size: 14,
                                    family: 'Montserrat',
                                    weight: 'bold'
                                },
                                color: '#1a1f3a',
                                padding: 15
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(26, 31, 58, 0.95)',
                            padding: 15,
                            borderColor: '#667eea',
                            borderWidth: 2,
                            titleFont: {
                                size: 15,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 14
                            },
                            displayColors: true,
                            boxWidth: 12,
                            boxHeight: 12,
                            callbacks: {
                                label: function(context) {
                                    return 'Doanh thu: ' + formatCurrency(context.parsed.y);
                                },
                                title: function(context) {
                                    return 'Ngày ' + context[0].label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(102, 126, 234, 0.1)',
                                lineWidth: 1
                            },
                            ticks: {
                                font: {
                                    size: 12,
                                    weight: '500'
                                },
                                color: '#666',
                                padding: 10,
                                callback: function(value) {
                                    if (value >= 1000000) {
                                        return (value / 1000000).toFixed(1) + 'M đ';
                                    } else if (value >= 1000) {
                                        return (value / 1000).toFixed(0) + 'K đ';
                                    }
                                    return formatCurrency(value);
                                }
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(224, 224, 224, 0.3)',
                                lineWidth: 1
                            },
                            ticks: {
                                font: {
                                    size: 11,
                                    weight: '500'
                                },
                                color: '#666',
                                padding: 8
                            }
                        }
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false
                    }
                }
            });
        }
    } catch (error) {
        console.error('Error loading chart:', error);
    }
}

// Load showtimes list
async function loadShowtimes() {
    const date = document.getElementById('filterDate').value;
    const roomID = document.getElementById('filterRoom').value;
    
    try {
        const response = await fetch(`/src/controllers/adminController.php?action=getShowtimes&date=${date}&roomID=${roomID}`);
        const data = await response.json();
        
        const container = document.getElementById('showtimesList');
        
        if (data.success && data.showtimes.length > 0) {
            container.innerHTML = data.showtimes.map(showtime => `
                <div class="showtime-item">
                    <div class="showtime-info">
                        <h4>${escapeHtml(showtime.movieTitle)}</h4>
                        <p><i class="fas fa-door-open"></i> ${escapeHtml(showtime.roomName)} - ${showtime.roomType}</p>
                        <p><i class="fas fa-clock"></i> ${formatDateTime(showtime.showtimeDate)}</p>
                        <p><i class="fas fa-chair"></i> ${showtime.availableSeats}/${showtime.totalSeats} ghế trống</p>
                        <span class="status-badge status-${showtime.status}">
                            ${getStatusText(showtime.status)}
                        </span>
                    </div>
                    <div class="showtime-actions">
                        <button class="btn btn-small btn-primary" onclick="editShowtime(${showtime.showtimeID})">
                            <i class="fas fa-edit"></i> Sửa
                        </button>
                        <button class="btn btn-small btn-danger" onclick="deleteShowtime(${showtime.showtimeID})">
                            <i class="fas fa-trash"></i> Xóa
                        </button>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<div class="loading">Không có suất chiếu nào</div>';
        }
    } catch (error) {
        console.error('Error loading showtimes:', error);
        document.getElementById('showtimesList').innerHTML = 
            '<div class="loading">Lỗi khi tải dữ liệu</div>';
    }
}

// Load top movies
async function loadTopMovies() {
    try {
        const response = await fetch('/src/controllers/adminController.php?action=getTopMovies');
        const data = await response.json();
        
        const container = document.getElementById('topMoviesList');
        
        if (data.success && data.movies.length > 0) {
            container.innerHTML = data.movies.map((movie, index) => `
                <div class="top-movie-item">
                    <div class="movie-rank rank-${index + 1}">${index + 1}</div>
                    <div class="movie-details">
                        <h4>${escapeHtml(movie.title)}</h4>
                        <p><i class="fas fa-ticket-alt"></i> ${movie.totalBookings} vé - 
                           <i class="fas fa-dollar-sign"></i> ${formatCurrency(movie.totalRevenue)}</p>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<div class="loading">Chưa có dữ liệu</div>';
        }
    } catch (error) {
        console.error('Error loading top movies:', error);
    }
}

// Load recent bookings
async function loadRecentBookings() {
    try {
        const response = await fetch('/src/controllers/adminController.php?action=getRecentBookings');
        const data = await response.json();
        
        const tbody = document.querySelector('#recentBookingsTable tbody');
        
        if (data.success && data.bookings.length > 0) {
            tbody.innerHTML = data.bookings.map(booking => `
                <tr>
                    <td><strong>${escapeHtml(booking.bookingCode)}</strong></td>
                    <td>${escapeHtml(booking.username)}</td>
                    <td>${escapeHtml(booking.movieTitle)}</td>
                    <td>${booking.totalSeats}</td>
                    <td>${formatCurrency(booking.totalPrice)}</td>
                    <td><span class="status-${booking.paymentStatus}">${getPaymentStatus(booking.paymentStatus)}</span></td>
                    <td>${formatDateTime(booking.bookingDate)}</td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="7" class="loading">Chưa có đặt vé nào</td></tr>';
        }
    } catch (error) {
        console.error('Error loading bookings:', error);
    }
}

// Load movies list for select
async function loadMoviesList() {
    try {
        const response = await fetch('/src/controllers/adminController.php?action=getMovies');
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('movieID');
            select.innerHTML = '<option value="">-- Chọn phim --</option>' +
                data.movies.map(movie => 
                    `<option value="${movie.movieID}">${escapeHtml(movie.title)}</option>`
                ).join('');
        }
    } catch (error) {
        console.error('Error loading movies:', error);
    }
}

// Load rooms list for select
async function loadRoomsList() {
    try {
        const response = await fetch('/src/controllers/adminController.php?action=getRooms');
        const data = await response.json();
        
        if (data.success) {
            const selects = ['roomID', 'filterRoom'];
            selects.forEach(id => {
                const select = document.getElementById(id);
                const defaultOption = id === 'filterRoom' ? 
                    '<option value="">Tất cả phòng</option>' : 
                    '<option value="">-- Chọn phòng --</option>';
                
                select.innerHTML = defaultOption +
                    data.rooms.map(room => 
                        `<option value="${room.roomID}">${escapeHtml(room.roomName)} (${room.roomType})</option>`
                    ).join('');
            });
        }
    } catch (error) {
        console.error('Error loading rooms:', error);
    }
}

// Modal functions
function openAddShowtimeModal() {
    document.getElementById('modalTitle').textContent = 'Thêm suất chiếu mới';
    document.getElementById('showtimeForm').reset();
    document.getElementById('showtimeID').value = '';
    document.getElementById('showtimeModal').classList.add('active');
}

function closeShowtimeModal() {
    document.getElementById('showtimeModal').classList.remove('active');
}

async function editShowtime(showtimeID) {
    try {
        const response = await fetch(`/src/controllers/adminController.php?action=getShowtime&id=${showtimeID}`);
        const data = await response.json();
        
        if (data.success) {
            const showtime = data.showtime;
            document.getElementById('modalTitle').textContent = 'Sửa suất chiếu';
            document.getElementById('showtimeID').value = showtime.showtimeID;
            document.getElementById('movieID').value = showtime.movieID;
            document.getElementById('roomID').value = showtime.roomID;
            document.getElementById('showtimeDate').value = showtime.showtimeDate.split(' ')[0];
            document.getElementById('showtimeTime').value = showtime.showtimeDate.split(' ')[1].substring(0, 5);
            document.getElementById('basePrice').value = showtime.basePrice;
            document.getElementById('showtimeStatus').value = showtime.status;
            
            document.getElementById('showtimeModal').classList.add('active');
        }
    } catch (error) {
        console.error('Error loading showtime:', error);
        alert('Lỗi khi tải thông tin suất chiếu');
    }
}

async function deleteShowtime(showtimeID) {
    if (!confirm('Bạn có chắc muốn xóa suất chiếu này?')) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'deleteShowtime');
        formData.append('showtimeID', showtimeID);
        
        const response = await fetch('/src/controllers/adminController.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Xóa suất chiếu thành công');
            loadShowtimes();
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể xóa suất chiếu'));
        }
    } catch (error) {
        console.error('Error deleting showtime:', error);
        alert('Lỗi khi xóa suất chiếu');
    }
}

// Handle showtime form submit
async function handleShowtimeSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const showtimeID = formData.get('showtimeID');
    formData.append('action', showtimeID ? 'updateShowtime' : 'addShowtime');
    
    // Gửi showDate và showTime riêng biệt (không cần kết hợp)
    const date = formData.get('showtimeDate');
    const time = formData.get('showtimeTime');
    formData.set('showDate', date);
    formData.set('showTime', `${time}:00`);
    formData.delete('showtimeDate');
    formData.delete('showtimeTime');
    
    try {
        const response = await fetch('/src/controllers/adminController.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(showtimeID ? 'Cập nhật suất chiếu thành công' : 'Thêm suất chiếu thành công');
            closeShowtimeModal();
            loadShowtimes();
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể lưu suất chiếu'));
        }
    } catch (error) {
        console.error('Error saving showtime:', error);
        alert('Lỗi khi lưu suất chiếu');
    }
}

// Utility functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function getStatusText(status) {
    const statuses = {
        'available': 'Có thể đặt',
        'full': 'Hết vé',
        'cancelled': 'Đã hủy'
    };
    return statuses[status] || status;
}

function getPaymentStatus(status) {
    const statuses = {
        'paid': 'Đã thanh toán',
        'pending': 'Đang chờ',
        'expired': 'Hết hạn'
    };
    return statuses[status] || status;
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    const modal = document.getElementById('showtimeModal');
    if (e.target === modal) {
        closeShowtimeModal();
    }
});
