<?php
/**
 * News Controller
 * Xử lý các request liên quan đến tin tức
 */

require_once __DIR__ . '/../models/news_db.php';

// Xử lý AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    $action = $_GET['action'];
    
    switch ($action) {
        case 'get_news':
            // Lấy tham số
            $type = isset($_GET['type']) ? $_GET['type'] : null;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;
            
            // Lấy tin tức từ database
            $news = get_latest_news($limit, $type);
            
            // Xử lý dữ liệu trước khi trả về
            foreach ($news as &$item) {
                // Format ngày tháng
                if ($item['publishDate']) {
                    $date = new DateTime($item['publishDate']);
                    $item['formattedDate'] = $date->format('d/m/Y');
                }
                
                // Format loại tin
                $item['typeLabel'] = format_news_type($item['type']);
                
                // Tạo excerpt nếu chưa có
                if (empty($item['excerpt']) && !empty($item['content'])) {
                    $item['excerpt'] = mb_substr(strip_tags($item['content']), 0, 150) . '...';
                }
                
                // Xử lý hình ảnh
                if (empty($item['imageURL'])) {
                    // Nếu không có ảnh, dùng poster phim (nếu có)
                    if (!empty($item['moviePoster'])) {
                        $item['imageURL'] = $item['moviePoster'];
                    }
                }
            }
            
            echo json_encode([
                'success' => true,
                'data' => $news,
                'total' => count($news)
            ]);
            break;
            
        case 'get_news_detail':
            $newsID = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            
            if ($newsID > 0) {
                $news = get_news_by_id($newsID);
                
                if ($news) {
                    // Tăng view count
                    increment_news_view_count($newsID);
                    
                    // Format date
                    if ($news['publishDate']) {
                        $date = new DateTime($news['publishDate']);
                        $news['formattedDate'] = $date->format('d/m/Y H:i');
                    }
                    
                    $news['typeLabel'] = format_news_type($news['type']);
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $news
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Không tìm thấy tin tức'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'ID không hợp lệ'
                ]);
            }
            break;
            
        case 'get_news_by_type':
            $type = isset($_GET['type']) ? $_GET['type'] : 'news';
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;
            
            $news = get_news_by_type($type, $limit);
            
            foreach ($news as &$item) {
                if ($item['publishDate']) {
                    $date = new DateTime($item['publishDate']);
                    $item['formattedDate'] = $date->format('d/m/Y');
                }
                $item['typeLabel'] = format_news_type($item['type']);
                
                if (empty($item['excerpt']) && !empty($item['content'])) {
                    $item['excerpt'] = mb_substr(strip_tags($item['content']), 0, 150) . '...';
                }
            }
            
            echo json_encode([
                'success' => true,
                'data' => $news,
                'total' => count($news)
            ]);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Action không hợp lệ'
            ]);
    }
    exit;
}
?>
