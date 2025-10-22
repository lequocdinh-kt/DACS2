<?php
/**
 * Payment Database Functions - PDO Version
 */

require_once __DIR__ . '/database.php';

function create_payment($bookingID, $amount, $paymentMethod = 'qr', $description = null) {
    global $db;
    
    $stmt = $db->prepare("
        INSERT INTO Payments (bookingID, amount, paymentMethod, paymentStatus)
        VALUES (?, ?, ?, 'pending')
    ");
    
    if ($stmt->execute([$bookingID, $amount, $paymentMethod])) {
        return $db->lastInsertId();
    }
    
    return false;
}

function get_payment_by_booking($bookingID) {
    global $db;
    
    $stmt = $db->prepare("
        SELECT *
        FROM Payments
        WHERE bookingID = ?
        ORDER BY paymentID DESC
        LIMIT 1
    ");
    
    $stmt->execute([$bookingID]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function confirm_payment($paymentID, $transactionCode = null) {
    global $db;
    
    try {
        $db->beginTransaction();
        
        // Update payment
        $stmt = $db->prepare("
            UPDATE Payments 
            SET paymentStatus = 'completed', transactionID = ?, completedAt = NOW()
            WHERE paymentID = ?
        ");
        $stmt->execute([$transactionCode, $paymentID]);
        
        // Get booking info
        $stmt = $db->prepare("SELECT bookingID FROM Payments WHERE paymentID = ?");
        $stmt->execute([$paymentID]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($payment) {
            require_once __DIR__ . '/booking_db.php';
            update_booking_payment_status($payment['bookingID'], 'paid', 'qr');
        }
        
        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollBack();
        return false;
    }
}

function generate_payment_qr_info($bookingCode, $amount) {
    $bankAccount = '0795701805';
    $bankName = 'MB';
    $accountName = 'LE QUOC DINH';
    $description = 'VKU CINEMA ' . $bookingCode;
    
    $qrUrl = "https://img.vietqr.io/image/{$bankName}-{$bankAccount}-compact2.png?" . 
             "amount={$amount}&addInfo=" . urlencode($description);
    
    return [
        'qrUrl' => $qrUrl,
        'bankAccount' => $bankAccount,
        'bankName' => $bankName,
        'accountName' => $accountName,
        'amount' => $amount,
        'description' => $description
    ];
}
