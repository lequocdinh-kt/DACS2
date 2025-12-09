<?php
/**
 * Promotions Database Functions
 * Các hàm xử lý dữ liệu khuyến mãi
 */

// Hàm helper để lấy database connection
function get_db_connection() {
    static $conn = null;
    
    if ($conn === null) {
        // Sử dụng constants từ config.php
        if (!defined('DB_HOST')) {
            require_once __DIR__ . '/../../config.php';
        }
        
        try {
            $conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw $e;
        }
    }
    
    return $conn;
}

/**
 * Lấy tất cả promotions đang active
 */
function get_active_promotions() {
    try {
        $db = get_db_connection();
        
        $sql = "SELECT * FROM promotions 
                WHERE status = 'active' 
                AND (startDate IS NULL OR startDate <= CURDATE())
                AND (endDate IS NULL OR endDate >= CURDATE())
                ORDER BY discountValue DESC";
        
        $stmt = $db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting active promotions: " . $e->getMessage());
        return [];
    }
}

/**
 * Lấy promotion theo code
 */
function get_promotion_by_code($code) {
    try {
        $db = get_db_connection();
        
        $sql = "SELECT * FROM promotions 
                WHERE code = ? 
                AND status = 'active'
                AND (startDate IS NULL OR startDate <= CURDATE())
                AND (endDate IS NULL OR endDate >= CURDATE())";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([strtoupper($code)]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting promotion by code: " . $e->getMessage());
        return null;
    }
}

/**
 * Lấy promotion theo ID
 */
function get_promotion_by_id($promotionID) {
    try {
        $db = get_db_connection();
        
        $sql = "SELECT * FROM promotions WHERE promotionID = ?";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$promotionID]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting promotion by ID: " . $e->getMessage());
        return null;
    }
}

/**
 * Kiểm tra promotion có hợp lệ với đơn hàng không
 */
function validate_promotion($code, $orderAmount) {
    $promo = get_promotion_by_code($code);
    
    if (!$promo) {
        return [
            'valid' => false,
            'message' => 'Mã giảm giá không tồn tại hoặc đã hết hạn'
        ];
    }
    
    // Kiểm tra giá trị đơn hàng tối thiểu
    if ($orderAmount < $promo['minOrderValue']) {
        return [
            'valid' => false,
            'message' => sprintf(
                'Đơn hàng tối thiểu %s để áp dụng mã này',
                number_format($promo['minOrderValue']) . 'đ'
            )
        ];
    }
    
    // Kiểm tra số lần sử dụng
    if ($promo['usageLimit'] > 0 && $promo['usedCount'] >= $promo['usageLimit']) {
        return [
            'valid' => false,
            'message' => 'Mã giảm giá đã hết lượt sử dụng'
        ];
    }
    
    return [
        'valid' => true,
        'promotion' => $promo
    ];
}

/**
 * Tính số tiền giảm
 */
function calculate_discount($promo, $orderAmount) {
    $discount = 0;
    
    if ($promo['discountType'] === 'percent') {
        $discount = ($orderAmount * $promo['discountValue']) / 100;
        
        // Áp dụng giảm tối đa nếu có
        if ($promo['maxDiscount'] && $discount > $promo['maxDiscount']) {
            $discount = $promo['maxDiscount'];
        }
    } else { // fixed
        $discount = $promo['discountValue'];
    }
    
    // Không cho giảm nhiều hơn giá trị đơn hàng
    if ($discount > $orderAmount) {
        $discount = $orderAmount;
    }
    
    return $discount;
}

/**
 * Áp dụng promotion cho booking
 */
function apply_promotion_to_booking($bookingID, $promotionID) {
    try {
        $db = get_db_connection();
        
        // Kiểm tra xem đã áp dụng promotion chưa
        $checkSql = "SELECT * FROM booking_promotions WHERE bookingID = ?";
        $checkStmt = $db->prepare($checkSql);
        $checkStmt->execute([$bookingID]);
        
        if ($checkStmt->rowCount() > 0) {
            // Cập nhật promotion cũ
            $sql = "UPDATE booking_promotions 
                    SET promotionID = ?, appliedAt = NOW() 
                    WHERE bookingID = ?";
            $stmt = $db->prepare($sql);
            return $stmt->execute([$promotionID, $bookingID]);
        } else {
            // Thêm mới
            $sql = "INSERT INTO booking_promotions (bookingID, promotionID, appliedAt) 
                    VALUES (?, ?, NOW())";
            $stmt = $db->prepare($sql);
            return $stmt->execute([$bookingID, $promotionID]);
        }
    } catch (PDOException $e) {
        error_log("Error applying promotion to booking: " . $e->getMessage());
        return false;
    }
}

/**
 * Xóa promotion khỏi booking
 */
function remove_promotion_from_booking($bookingID) {
    try {
        $db = get_db_connection();
        
        $sql = "DELETE FROM booking_promotions WHERE bookingID = ?";
        $stmt = $db->prepare($sql);
        
        return $stmt->execute([$bookingID]);
    } catch (PDOException $e) {
        error_log("Error removing promotion from booking: " . $e->getMessage());
        return false;
    }
}

/**
 * Lấy promotion đã áp dụng cho booking
 */
function get_booking_promotion($bookingID) {
    try {
        $db = get_db_connection();
        
        $sql = "SELECT p.* 
                FROM promotions p
                INNER JOIN booking_promotions bp ON p.promotionID = bp.promotionID
                WHERE bp.bookingID = ?";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$bookingID]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting booking promotion: " . $e->getMessage());
        return null;
    }
}

/**
 * Tăng số lần sử dụng promotion
 */
function increment_promotion_usage($promotionID) {
    try {
        $db = get_db_connection();
        
        $sql = "UPDATE promotions SET usedCount = usedCount + 1 WHERE promotionID = ?";
        $stmt = $db->prepare($sql);
        
        return $stmt->execute([$promotionID]);
    } catch (PDOException $e) {
        error_log("Error incrementing promotion usage: " . $e->getMessage());
        return false;
    }
}

/**
 * Lấy promotions phù hợp với giá trị đơn hàng
 */
function get_applicable_promotions($orderAmount) {
    try {
        $db = get_db_connection();
        
        $sql = "SELECT * FROM promotions 
                WHERE status = 'active' 
                AND (startDate IS NULL OR startDate <= CURDATE())
                AND (endDate IS NULL OR endDate >= CURDATE())
                AND minOrderValue <= ?
                AND (usageLimit = 0 OR usedCount < usageLimit)
                ORDER BY discountValue DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$orderAmount]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting applicable promotions: " . $e->getMessage());
        return [];
    }
}

/**
 * Format thông tin promotion để hiển thị
 */
function format_promotion_display($promo) {
    $discountText = '';
    
    if ($promo['discountType'] === 'percent') {
        $discountText = 'Giảm ' . $promo['discountValue'] . '%';
        if ($promo['maxDiscount']) {
            $discountText .= ' (tối đa ' . number_format($promo['maxDiscount']) . 'đ)';
        }
    } else {
        $discountText = 'Giảm ' . number_format($promo['discountValue']) . 'đ';
    }
    
    $conditions = [];
    if ($promo['minOrderValue'] > 0) {
        $conditions[] = 'Đơn tối thiểu ' . number_format($promo['minOrderValue']) . 'đ';
    }
    
    if ($promo['usageLimit'] > 0) {
        $remaining = $promo['usageLimit'] - $promo['usedCount'];
        $conditions[] = 'Còn ' . $remaining . ' lượt';
    }
    
    return [
        'code' => $promo['code'],
        'description' => $promo['description'],
        'discount_text' => $discountText,
        'conditions' => implode(' • ', $conditions),
        'end_date' => $promo['endDate'] ? date('d/m/Y', strtotime($promo['endDate'])) : null
    ];
}
?>
