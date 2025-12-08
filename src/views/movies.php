<main class="movies-main">
    <div class="movies-container">
        <!-- Header -->
        <section class="movies-hero">
            <h1>
                <i class="fas fa-film"></i> Danh sách phim
            </h1>
            <p>Khám phá những bộ phim đang chiếu và sắp chiếu</p>
        </section>

        <!-- Filter Section -->
        <section class="movies-filter">
            <div class="filter-wrapper">
                <div class="filter-buttons">
                    <button class="filter-btn active" data-filter="now-showing">
                        <i class="fas fa-clock"></i> Đang chiếu
                    </button>
                    <button class="filter-btn" data-filter="coming-soon">
                        <i class="fas fa-calendar-plus"></i> Sắp chiếu
                    </button>
                </div>
                <div class="search-box">
                    <input type="text" id="movieSearch" placeholder="Tìm kiếm phim...">
                    <i class="fas fa-search search-icon"></i>
                </div>
            </div>
        </section>

        <!-- Movies Grid -->
        <section class="movies-grid" id="moviesGrid">
            <!-- Movie cards will be loaded here -->
            <div class="loading-state">
                <i class="fas fa-film"></i>
                <p>Đang tải danh sách phim...</p>
            </div>
        </section>
    </div>
</main>
