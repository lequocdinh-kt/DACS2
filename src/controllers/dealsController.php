<?php
/**
 * Deals Controller
 * Xử lý AJAX requests cho trang ưu đãi
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../models/database.php';

try {
    // Lấy action từ request
    $action = $_GET['action'] ?? 'get_promotions';
    
    switch ($action) {
        case 'get_promotions':
            // Lấy tất cả promotions đang active
            $sql = 'SELECT * FROM promotions 
                    WHERE status = "active" 
                    AND (endDate IS NULL OR endDate >= CURDATE())
                    ORDER BY promotionID DESC';
            
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $promotions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'promotions' => $promotions,
                'count' => count($promotions)
            ]);
            break;
            
        case 'get_promotion_details':
            $promotionID = $_GET['id'] ?? 0;
            
            if ($promotionID <= 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'ID khuyến mãi không hợp lệ'
                ]);
                exit;
            }
            
            // Lấy thông tin promotion
            $sql = 'SELECT p.*, n.content, n.imageURL 
                    FROM promotions p
                    LEFT JOIN news n ON n.promotionID = p.promotionID AND n.type = "promotion"
                    WHERE p.promotionID = :promotionID';
            
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':promotionID', $promotionID, PDO::PARAM_INT);
            $stmt->execute();
            $promotion = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($promotion) {
                echo json_encode([
                    'success' => true,
                    'promotion' => $promotion
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Không tìm thấy khuyến mãi'
                ]);
            }
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Action không hợp lệ'
            ]);
            break;
    }
    
} catch (Exception $e) {
    error_log('Deals Controller Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
    ]);
}
?>
