<?php
/**
 * News Database Functions
 * Quản lý tin tức, sự kiện và khuyến mãi
 */

require_once 'database.php';

/**
 * Lấy tin tức mới nhất
 * @param int $limit Số lượng tin cần lấy
 * @param string|null $type Loại tin (promotion, event, announcement, news) hoặc null để lấy tất cả
 * @return array Danh sách tin tức
 */
function get_latest_news($limit = 10, $type = null) {
    global $db;
    
    try {
        if ($type) {
            $sql = "SELECT n.*, 
                           m.title AS movieTitle,
                           m.posterURL AS moviePoster,
                           p.code AS promotionCode,
                           p.discountType,
                           p.discountValue,
                           p.maxDiscount
                    FROM news n
                    LEFT JOIN movie m ON n.movieID = m.movieID
                    LEFT JOIN promotions p ON n.promotionID = p.promotionID
                    WHERE n.status = 'published' 
                    AND n.type = :type
                    AND (n.expireDate IS NULL OR n.expireDate > NOW())
                    ORDER BY n.priority DESC, n.publishDate DESC 
                    LIMIT :limit";
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':type', $type, PDO::PARAM_STR);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        } else {
            $sql = "SELECT n.*, 
                           m.title AS movieTitle,
                           m.posterURL AS moviePoster,
                           p.code AS promotionCode,
                           p.discountType,
                           p.discountValue,
                           p.maxDiscount
                    FROM news n
                    LEFT JOIN movie m ON n.movieID = m.movieID
                    LEFT JOIN promotions p ON n.promotionID = p.promotionID
                    WHERE n.status = 'published' 
                    AND (n.expireDate IS NULL OR n.expireDate > NOW())
                    ORDER BY n.priority DESC, n.publishDate DESC 
                    LIMIT :limit";
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $news;
    } catch (Exception $e) {
        error_log("Error in get_latest_news: " . $e->getMessage());
        return [];
    }
}

/**
 * Lấy tin tức theo loại
 * @param string $type Loại tin (promotion, event, announcement, news)
 * @param int $limit Số lượng tin cần lấy
 * @return array Danh sách tin tức
 */
function get_news_by_type($type, $limit = 10) {
    return get_latest_news($limit, $type);
}

/**
 * Lấy chi tiết một tin tức
 * @param int $newsID ID của tin tức
 * @return array|null Thông tin tin tức hoặc null nếu không tìm thấy
 */
function get_news_by_id($newsID) {
    global $db;
    
    try {
        $sql = "SELECT n.*, 
                       m.title AS movieTitle,
                       m.posterURL AS moviePoster,
                       m.trailerURL AS movieTrailer,
                       p.code AS promotionCode,
                       p.description AS promotionDescription,
                       p.discountType,
                       p.discountValue,
                       p.maxDiscount,
                       p.minOrderValue,
                       p.startDate AS promotionStartDate,
                       p.endDate AS promotionEndDate
                FROM news n
                LEFT JOIN movie m ON n.movieID = m.movieID
                LEFT JOIN promotions p ON n.promotionID = p.promotionID
                WHERE n.newsID = :newsID AND n.status = 'published'";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':newsID', $newsID, PDO::PARAM_INT);
        $stmt->execute();
        $news = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $news ? $news : null;
    } catch (Exception $e) {
        error_log("Error in get_news_by_id: " . $e->getMessage());
        return null;
    }
}

/**
 * Lấy tin tức liên quan đến phim
 * @param int $movieID ID của phim
 * @return array Danh sách tin tức
 */
function get_news_by_movie($movieID) {
    global $db;
    
    try {
        $sql = "SELECT * FROM news 
                WHERE movieID = :movieID 
                AND status = 'published'
                AND (expireDate IS NULL OR expireDate > NOW())
                ORDER BY priority DESC, publishDate DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':movieID', $movieID, PDO::PARAM_INT);
        $stmt->execute();
        $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $news;
    } catch (Exception $e) {
        error_log("Error in get_news_by_movie: " . $e->getMessage());
        return [];
    }
}

/**
 * Tăng số lượt xem tin tức
 * @param int $newsID ID của tin tức
 * @return bool True nếu thành công, false nếu thất bại
 */
function increment_news_view_count($newsID) {
    global $db;
    
    try {
        $sql = "UPDATE news SET viewCount = viewCount + 1 WHERE newsID = :newsID";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':newsID', $newsID, PDO::PARAM_INT);
        $success = $stmt->execute();
        
        return $success;
    } catch (Exception $e) {
        error_log("Error in increment_news_view_count: " . $e->getMessage());
        return false;
    }
}

/**
 * Lấy tin khuyến mãi đang hoạt động (có liên kết với bảng promotions)
 * @return array Danh sách tin khuyến mãi
 */
function get_active_promotion_news() {
    global $db;
    
    try {
        $sql = "SELECT n.*, 
                       p.code AS promotionCode,
                       p.discountType,
                       p.discountValue,
                       p.maxDiscount,
                       p.minOrderValue,
                       p.startDate AS promotionStartDate,
                       p.endDate AS promotionEndDate
                FROM news n
                INNER JOIN promotions p ON n.promotionID = p.promotionID
                WHERE n.status = 'published' 
                AND n.type = 'promotion'
                AND (n.expireDate IS NULL OR n.expireDate > NOW())
                AND p.endDate > NOW()
                AND p.status = 'active'
                ORDER BY n.priority DESC, n.publishDate DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $news;
    } catch (Exception $e) {
        error_log("Error in get_active_promotion_news: " . $e->getMessage());
        return [];
    }
}

/**
 * Lấy tin tức phổ biến (nhiều lượt xem nhất)
 * @param int $limit Số lượng tin cần lấy
 * @return array Danh sách tin tức
 */
function get_popular_news($limit = 5) {
    global $db;
    
    try {
        $sql = "SELECT n.*, 
                       m.title AS movieTitle,
                       p.code AS promotionCode
                FROM news n
                LEFT JOIN movie m ON n.movieID = m.movieID
                LEFT JOIN promotions p ON n.promotionID = p.promotionID
                WHERE n.status = 'published'
                AND (n.expireDate IS NULL OR n.expireDate > NOW())
                ORDER BY n.viewCount DESC, n.publishDate DESC
                LIMIT :limit";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $news;
    } catch (Exception $e) {
        error_log("Error in get_popular_news: " . $e->getMessage());
        return [];
    }
}

/**
 * Đếm tổng số tin tức đang hoạt động
 * @param string|null $type Loại tin hoặc null để đếm tất cả
 * @return int Số lượng tin tức
 */
function count_news($type = null) {
    global $db;
    
    try {
        if ($type) {
            $sql = "SELECT COUNT(*) as total FROM news 
                    WHERE status = 'published' 
                    AND type = :type
                    AND (expireDate IS NULL OR expireDate > NOW())";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':type', $type, PDO::PARAM_STR);
        } else {
            $sql = "SELECT COUNT(*) as total FROM news 
                    WHERE status = 'published'
                    AND (expireDate IS NULL OR expireDate > NOW())";
            $stmt = $db->prepare($sql);
        }
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int)$row['total'];
    } catch (Exception $e) {
        error_log("Error in count_news: " . $e->getMessage());
        return 0;
    }
}

/**
 * Format loại tin tức thành tiếng Việt
 * @param string $type Loại tin (promotion, event, announcement, news)
 * @return string Tên tiếng Việt
 */
function format_news_type($type) {
    $types = [
        'promotion' => 'Ưu đãi',
        'event' => 'Sự kiện',
        'announcement' => 'Thông báo',
        'news' => 'Tin tức'
    ];
    
    return $types[$type] ?? 'Tin tức';
}

/**
 * Format giá trị khuyến mãi
 * @param string $discountType Loại giảm giá (percent, fixed)
 * @param float $discountValue Giá trị giảm
 * @param float|null $maxDiscount Giảm tối đa (cho percent)
 * @return string Chuỗi mô tả giảm giá
 */
function format_discount($discountType, $discountValue, $maxDiscount = null) {
    if ($discountType === 'percent') {
        $text = "Giảm {$discountValue}%";
        if ($maxDiscount) {
            $text .= " (tối đa " . number_format($maxDiscount, 0, ',', '.') . "đ)";
        }
        return $text;
    } else {
        return "Giảm " . number_format($discountValue, 0, ',', '.') . "đ";
    }
}

?>
