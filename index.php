<?php 
session_start();

// Xác định trang cần hiển thị dựa vào query parameter
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Mapping các trang hợp lệ
$valid_pages = [
    'home' => ['file' => 'src/views/home.php', 'title' => 'Trang chủ', 'css' => 'src/styles/home.css'],
    'schedule' => ['file' => 'src/views/schedule.php', 'title' => 'Lịch chiếu', 'css' => 'src/styles/schedule.css'],
    'movies' => ['file' => 'src/views/movies.php', 'title' => 'Phim', 'css' => 'src/styles/movies.css'],
    'movie_detail' => ['file' => 'src/views/movie_detail.php', 'title' => 'Chi tiết phim', 'css' => 'src/styles/movie_detail.css'],
    'deals' => ['file' => 'src/views/deals.php', 'title' => 'Ưu đãi', 'css' => 'src/styles/deals.css'],
    'news' => ['file' => 'src/views/news.php', 'title' => 'Tin tức phim', 'css' => 'src/styles/news.css'],
    'member' => ['file' => 'src/views/member.php', 'title' => 'Thành viên', 'css' => 'src/styles/member.css'],
    'tuyen-dung' => ['file' => 'src/views/tuyen-dung.php', 'title' => 'Tuyển dụng', 'css' => 'src/styles/info_pages.css'],
    'lien-he' => ['file' => 'src/views/lien-he.php', 'title' => 'Liên hệ', 'css' => 'src/styles/info_pages.css'],
    'dieu-khoan' => ['file' => 'src/views/dieu-khoan.php', 'title' => 'Điều khoản chung', 'css' => 'src/styles/info_pages.css'],
    'cau-hoi' => ['file' => 'src/views/cau-hoi.php', 'title' => 'Câu hỏi thường gặp', 'css' => 'src/styles/info_pages.css'],
    'dieu-khoan-giao-dich' => ['file' => 'src/views/dieu-khoan-giao-dich.php', 'title' => 'Điều khoản giao dịch', 'css' => 'src/styles/info_pages.css']
];
//test
// Kiểm tra trang có hợp lệ không
if (!isset($valid_pages[$page])) {
    $page = 'home';
}

$page_info = $valid_pages[$page];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/src/styles/header.css">
    <?php if (file_exists($page_info['css'])): ?>
    <link rel="stylesheet" href="/<?php echo $page_info['css']; ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="/src/styles/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <title><?php echo ($page === 'movie_detail' && isset($movie)) ? htmlspecialchars($movie['title']) . ' - VKU Cinema' : $page_info['title'] . ' - VKU Cinema'; ?></title>
</head>

<body>
    <?php include 'src/views/header.php'; ?>
    
    <?php 
    // Define constant to allow view files to know they're included from index
    define('INCLUDED_FROM_INDEX', true);
    
    error_log("index.php: About to include file: " . $page_info['file']);
    
    // Include nội dung trang tương ứng
    if (file_exists($page_info['file'])) {
        error_log("index.php: File exists, including...");
        include $page_info['file'];
        error_log("index.php: File included successfully");
    } else {
        error_log("index.php: File not found, using home.php");
        include 'src/views/home.php';
    }
    
    error_log("=== index.php: Request completed ===");
    ?>

    <?php include 'src/views/footer.php'; ?>
    
    <?php if (isset($_GET['openLogin']) && $_GET['openLogin'] == '1'): ?>
    <script>
        // Mở modal đăng nhập khi có parameter openLogin=1
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof openAuthModal === 'function') {
                openAuthModal('login');
                <?php if (isset($_SESSION['login_message'])): ?>
                // Hiển thị thông báo nếu có
                setTimeout(function() {
                    const loginAlert = document.getElementById('loginAlert');
                    if (loginAlert) {
                        loginAlert.textContent = '<?php echo addslashes($_SESSION['login_message']); ?>';
                        loginAlert.className = 'alert alert-info';
                        loginAlert.style.display = 'block';
                    }
                }, 100);
                <?php unset($_SESSION['login_message']); ?>
                <?php endif; ?>
            }
        });
    </script>
    <?php endif; ?>
</body>

</html>