<?php
// Kiểm tra quyền admin
if (!isset($_SESSION['user']['roleID']) || $_SESSION['user']['roleID'] != 1) {
    header('Location: index.php');
    exit();
}

require_once __DIR__ . '/../models/database.php';
?>

<!-- Admin Dashboard -->
<div class="admin-dashboard">
    <div class="admin-container">
        <!-- Header Dashboard -->
        <div class="dashboard-header">
            <h1><i class="fas fa-crown"></i> Admin Dashboard</h1>
            <p>Quản lý hệ thống VKU Cinema</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card stat-revenue">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-info">
                    <h3 id="totalRevenue">0đ</h3>
                    <p>Doanh thu tháng</p>
                </div>
            </div>

            <div class="stat-card stat-bookings">
                <div class="stat-icon">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <div class="stat-info">
                    <h3 id="totalBookings">0</h3>
                    <p>Vé đã bán</p>
                </div>
            </div>

            <div class="stat-card stat-users">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3 id="totalUsers">0</h3>
                    <p>Người dùng</p>
                </div>
            </div>

            <div class="stat-card stat-movies">
                <div class="stat-icon">
                    <i class="fas fa-film"></i>
                </div>
                <div class="stat-info">
                    <h3 id="totalMovies">0</h3>
                    <p>Phim đang chiếu</p>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="dashboard-grid">
            <!-- Chart Section -->
            <div class="dashboard-section chart-section">
                <div class="section-header">
                    <h2><i class="fas fa-chart-line"></i> Biểu đồ doanh thu</h2>
                    <div class="chart-controls">
                        <select id="chartPeriod" class="select-control">
                            <option value="7">7 ngày qua</option>
                            <option value="30" selected>30 ngày qua</option>
                            <option value="90">90 ngày qua</option>
                        </select>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Showtimes Management -->
            <div class="dashboard-section showtimes-section">
                <div class="section-header">
                    <h2><i class="fas fa-clock"></i> Quản lý suất chiếu</h2>
                    <button class="btn btn-primary" onclick="openAddShowtimeModal()">
                        <i class="fas fa-plus"></i> Thêm suất chiếu
                    </button>
                </div>
                
                <div class="filter-controls">
                    <input type="date" id="filterDate" class="input-control" value="<?php echo date('Y-m-d'); ?>">
                    <select id="filterRoom" class="select-control">
                        <option value="">Tất cả phòng</option>
                    </select>
                    <button class="btn btn-secondary" onclick="loadShowtimes()">
                        <i class="fas fa-search"></i> Tìm
                    </button>
                </div>

                <div class="showtimes-list" id="showtimesList">
                    <div class="loading">Đang tải...</div>
                </div>
            </div>

            <!-- Top Movies -->
            <div class="dashboard-section top-movies-section">
                <div class="section-header">
                    <h2><i class="fas fa-trophy"></i> Top phim bán chạy</h2>
                </div>
                <div class="top-movies-list" id="topMoviesList">
                    <div class="loading">Đang tải...</div>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="dashboard-section recent-bookings-section">
                <div class="section-header">
                    <h2><i class="fas fa-receipt"></i> Đặt vé gần đây</h2>
                </div>
                <div class="bookings-table-wrapper">
                    <table class="bookings-table" id="recentBookingsTable">
                        <thead>
                            <tr>
                                <th>Mã đặt</th>
                                <th>Khách hàng</th>
                                <th>Phim</th>
                                <th>Số ghế</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Thời gian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7" class="loading">Đang tải...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Thêm/Sửa Suất Chiếu -->
<div id="showtimeModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Thêm suất chiếu mới</h3>
            <button class="modal-close" onclick="closeShowtimeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="showtimeForm" class="modal-form">
            <input type="hidden" id="showtimeID" name="showtimeID">
            
            <div class="form-group">
                <label for="movieID">Phim <span class="required">*</span></label>
                <select id="movieID" name="movieID" class="input-control" required>
                    <option value="">-- Chọn phim --</option>
                </select>
            </div>

            <div class="form-group">
                <label for="roomID">Phòng chiếu <span class="required">*</span></label>
                <select id="roomID" name="roomID" class="input-control" required>
                    <option value="">-- Chọn phòng --</option>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="showtimeDate">Ngày chiếu <span class="required">*</span></label>
                    <input type="date" id="showtimeDate" name="showtimeDate" class="input-control" required>
                </div>

                <div class="form-group">
                    <label for="showtimeTime">Giờ chiếu <span class="required">*</span></label>
                    <input type="time" id="showtimeTime" name="showtimeTime" class="input-control" required>
                </div>
            </div>

            <div class="form-group">
                <label for="basePrice">Giá vé cơ bản <span class="required">*</span></label>
                <input type="number" id="basePrice" name="basePrice" class="input-control" placeholder="45000" required>
            </div>

            <div class="form-group">
                <label for="showtimeStatus">Trạng thái</label>
                <select id="showtimeStatus" name="showtimeStatus" class="input-control">
                    <option value="available">Có thể đặt</option>
                    <option value="full">Hết vé</option>
                    <option value="cancelled">Đã hủy</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeShowtimeModal()">Hủy</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Lưu
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="src/js/admin.js"></script>
