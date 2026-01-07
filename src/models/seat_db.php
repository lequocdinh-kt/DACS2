<?php
/**
 * Seat Database Functions - PDO Version
 */

require_once __DIR__ . '/database.php';

function get_seats_by_showtime($showtimeID) {
    global $db;
    
    $stmt = $db->prepare("
        SELECT s.seatID, s.seatRow, s.seatNumber, s.seatType, s.price,
               CASE 
                   WHEN bs.bookingSeatID IS NOT NULL THEN 'booked'
                   WHEN sl.lockID IS NOT NULL AND sl.expiresAt > NOW() THEN 'locked'
                   ELSE 'available'
               END as status,
               sl.userID as lockedByUserID
        FROM seats s
        INNER JOIN showtimes st ON s.roomID = st.roomID
        LEFT JOIN bookingseats bs ON s.seatID = bs.seatID 
            AND bs.bookingID IN (
                SELECT b.bookingID FROM bookings b 
                WHERE b.showtimeID = ? 
                AND b.paymentStatus = 'paid'
            )
        LEFT JOIN seatlocks sl ON s.seatID = sl.seatID 
            AND sl.showtimeID = ? AND sl.expiresAt > NOW()
        WHERE st.showtimeID = ?
        ORDER BY s.seatRow, s.seatNumber
    ");
    
    $stmt->execute([$showtimeID, $showtimeID, $showtimeID]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function lock_seats($showtimeID, $seatIDs, $userID, $minutes = 15) {
    global $db;
    
    // Debug log
    $logFile = __DIR__ . '/../../CONSOLE_DEBUG_LOG.txt';
    $time = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$time] [LOCK_SEATS_FUNC] Starting lock for user $userID, " . count($seatIDs) . " seats\n", FILE_APPEND);
    
    unlock_seats_by_user($showtimeID, $userID);
    
    $expiresAt = date('Y-m-d H:i:s', strtotime("+{$minutes} minutes"));
    file_put_contents($logFile, "[$time] [LOCK_SEATS_FUNC] ExpiresAt: $expiresAt\n", FILE_APPEND);
    
    try {
        $db->beginTransaction();
        file_put_contents($logFile, "[$time] [LOCK_SEATS_FUNC] Transaction started\n", FILE_APPEND);
        
        $stmt = $db->prepare("INSERT INTO seatlocks (showtimeID, seatID, userID, expiresAt) VALUES (?, ?, ?, ?)");
        
        foreach ($seatIDs as $seatID) {
            file_put_contents($logFile, "[$time] [LOCK_SEATS_FUNC] Locking seat $seatID\n", FILE_APPEND);
            $result = $stmt->execute([$showtimeID, $seatID, $userID, $expiresAt]);
            file_put_contents($logFile, "[$time] [LOCK_SEATS_FUNC] Execute result for seat $seatID: " . ($result ? 'success' : 'failed') . "\n", FILE_APPEND);
        }
        
        $db->commit();
        file_put_contents($logFile, "[$time] [LOCK_SEATS_FUNC] Transaction committed successfully\n", FILE_APPEND);
        return true;
    } catch (Exception $e) {
        $db->rollBack();
        file_put_contents($logFile, "[$time] [LOCK_SEATS_FUNC] EXCEPTION: " . $e->getMessage() . "\n", FILE_APPEND);
        file_put_contents($logFile, "[$time] [LOCK_SEATS_FUNC] SQL State: " . $e->getCode() . "\n", FILE_APPEND);
        return false;
    }
}

function unlock_seats_by_user($showtimeID, $userID) {
    global $db;
    
    $stmt = $db->prepare("DELETE FROM seatlocks WHERE showtimeID = ? AND userID = ?");
    return $stmt->execute([$showtimeID, $userID]);
}

function cleanup_expired_locks() {
    global $db;
    
    $stmt = $db->prepare("DELETE FROM seatlocks WHERE expiresAt < NOW()");
    return $stmt->execute();
}

function check_seats_available($showtimeID, $seatIDs) {
    global $db;
    
    // Debug log
    $logFile = __DIR__ . '/../../CONSOLE_DEBUG_LOG.txt';
    $time = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$time] [CHECK_SEATS] showtimeID: $showtimeID, checking " . count($seatIDs) . " seats: " . implode(',', $seatIDs) . "\n", FILE_APPEND);
    
    $placeholders = implode(',', array_fill(0, count($seatIDs), '?'));
    $params = array_merge([$showtimeID, $showtimeID, $showtimeID], $seatIDs);
    
    $query = "
        SELECT s.seatID
        FROM seats s
        INNER JOIN showtimes st ON s.roomID = st.roomID
        LEFT JOIN bookingseats bs ON s.seatID = bs.seatID 
            AND bs.bookingID IN (
                SELECT b.bookingID FROM bookings b 
                WHERE b.showtimeID = ? 
                AND b.paymentStatus = 'paid'
            )
        LEFT JOIN seatlocks sl ON s.seatID = sl.seatID 
            AND sl.showtimeID = ? AND sl.expiresAt > NOW()
        WHERE st.showtimeID = ? AND s.seatID IN ($placeholders)
        AND bs.bookingSeatID IS NULL AND sl.lockID IS NULL
    ";
    
    file_put_contents($logFile, "[$time] [CHECK_SEATS] Query: $query\n", FILE_APPEND);
    file_put_contents($logFile, "[$time] [CHECK_SEATS] Params: " . implode(',', $params) . "\n", FILE_APPEND);
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $availableSeats = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    file_put_contents($logFile, "[$time] [CHECK_SEATS] Available seats found: " . count($availableSeats) . " (" . implode(',', $availableSeats) . ")\n", FILE_APPEND);
    file_put_contents($logFile, "[$time] [CHECK_SEATS] Requested seats: " . count($seatIDs) . "\n", FILE_APPEND);
    file_put_contents($logFile, "[$time] [CHECK_SEATS] Result: " . (count($availableSeats) === count($seatIDs) ? 'ALL AVAILABLE' : 'SOME NOT AVAILABLE') . "\n", FILE_APPEND);
    
    return count($availableSeats) === count($seatIDs);
}

function check_seats_available_for_user($showtimeID, $seatIDs, $userID) {
    global $db;
    
    $placeholders = implode(',', array_fill(0, count($seatIDs), '?'));
    $params = array_merge([$showtimeID, $showtimeID, $userID, $showtimeID], $seatIDs);
    
    $stmt = $db->prepare("
        SELECT s.seatID
        FROM seats s
        INNER JOIN showtimes st ON s.roomID = st.roomID
        LEFT JOIN bookingseats bs ON s.seatID = bs.seatID 
            AND bs.bookingID IN (
                SELECT b.bookingID FROM bookings b 
                WHERE b.showtimeID = ? 
                AND b.paymentStatus = 'paid'
            )
        LEFT JOIN seatlocks sl ON s.seatID = sl.seatID 
            AND sl.showtimeID = ? AND sl.userID != ? AND sl.expiresAt > NOW()
        WHERE st.showtimeID = ? AND s.seatID IN ($placeholders)
        AND bs.bookingSeatID IS NULL AND sl.lockID IS NULL
    ");
    
    $stmt->execute($params);
    $availableSeats = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    return count($availableSeats) === count($seatIDs);
}
