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
        FROM Seats s
        INNER JOIN Showtimes st ON s.roomID = st.roomID
        LEFT JOIN BookingSeats bs ON s.seatID = bs.seatID 
            AND bs.bookingID IN (
                SELECT b.bookingID FROM Bookings b 
                WHERE b.showtimeID = ? AND b.paymentStatus IN ('pending', 'paid')
            )
        LEFT JOIN SeatLocks sl ON s.seatID = sl.seatID 
            AND sl.showtimeID = ? AND sl.expiresAt > NOW()
        WHERE st.showtimeID = ?
        ORDER BY s.seatRow, s.seatNumber
    ");
    
    $stmt->execute([$showtimeID, $showtimeID, $showtimeID]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function lock_seats($showtimeID, $seatIDs, $userID, $minutes = 15) {
    global $db;
    
    unlock_seats_by_user($showtimeID, $userID);
    
    $expiresAt = date('Y-m-d H:i:s', strtotime("+{$minutes} minutes"));
    
    try {
        $db->beginTransaction();
        
        $stmt = $db->prepare("INSERT INTO SeatLocks (showtimeID, seatID, userID, expiresAt) VALUES (?, ?, ?, ?)");
        
        foreach ($seatIDs as $seatID) {
            $stmt->execute([$showtimeID, $seatID, $userID, $expiresAt]);
        }
        
        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollBack();
        return false;
    }
}

function unlock_seats_by_user($showtimeID, $userID) {
    global $db;
    
    $stmt = $db->prepare("DELETE FROM SeatLocks WHERE showtimeID = ? AND userID = ?");
    return $stmt->execute([$showtimeID, $userID]);
}

function cleanup_expired_locks() {
    global $db;
    
    $stmt = $db->prepare("DELETE FROM SeatLocks WHERE expiresAt < NOW()");
    return $stmt->execute();
}

function check_seats_available($showtimeID, $seatIDs) {
    global $db;
    
    $placeholders = implode(',', array_fill(0, count($seatIDs), '?'));
    $params = array_merge([$showtimeID, $showtimeID, $showtimeID], $seatIDs);
    
    $stmt = $db->prepare("
        SELECT s.seatID
        FROM Seats s
        INNER JOIN Showtimes st ON s.roomID = st.roomID
        LEFT JOIN BookingSeats bs ON s.seatID = bs.seatID 
            AND bs.bookingID IN (
                SELECT b.bookingID FROM Bookings b 
                WHERE b.showtimeID = ? AND b.paymentStatus IN ('pending', 'paid')
            )
        LEFT JOIN SeatLocks sl ON s.seatID = sl.seatID 
            AND sl.showtimeID = ? AND sl.expiresAt > NOW()
        WHERE st.showtimeID = ? AND s.seatID IN ($placeholders)
        AND bs.bookingSeatID IS NULL AND sl.lockID IS NULL
    ");
    
    $stmt->execute($params);
    $availableSeats = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    return count($availableSeats) === count($seatIDs);
}

function check_seats_available_for_user($showtimeID, $seatIDs, $userID) {
    global $db;
    
    $placeholders = implode(',', array_fill(0, count($seatIDs), '?'));
    $params = array_merge([$showtimeID, $showtimeID, $userID, $showtimeID], $seatIDs);
    
    $stmt = $db->prepare("
        SELECT s.seatID
        FROM Seats s
        INNER JOIN Showtimes st ON s.roomID = st.roomID
        LEFT JOIN BookingSeats bs ON s.seatID = bs.seatID 
            AND bs.bookingID IN (
                SELECT b.bookingID FROM Bookings b 
                WHERE b.showtimeID = ? AND b.paymentStatus IN ('pending', 'paid')
            )
        LEFT JOIN SeatLocks sl ON s.seatID = sl.seatID 
            AND sl.showtimeID = ? AND sl.userID != ? AND sl.expiresAt > NOW()
        WHERE st.showtimeID = ? AND s.seatID IN ($placeholders)
        AND bs.bookingSeatID IS NULL AND sl.lockID IS NULL
    ");
    
    $stmt->execute($params);
    $availableSeats = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    return count($availableSeats) === count($seatIDs);
}
