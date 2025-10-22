<?php
/**
 * Booking Database Functions - PDO Version
 */

require_once __DIR__ . '/database.php';

function create_booking($userID, $showtimeID, $seatIDs, $totalPrice) {
    global $db;
    
    try {
        $db->beginTransaction();
        
        $bookingCode = 'VKU' . str_pad(mt_rand(0, 999999999), 9, '0', STR_PAD_LEFT);
        $totalSeats = count($seatIDs);
        $expiredAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        
        $stmt = $db->prepare("
            INSERT INTO Bookings (userID, showtimeID, bookingCode, totalPrice, totalSeats, paymentStatus, expiredAt)
            VALUES (?, ?, ?, ?, ?, 'pending', ?)
        ");
        
        $stmt->execute([$userID, $showtimeID, $bookingCode, $totalPrice, $totalSeats, $expiredAt]);
        $bookingID = $db->lastInsertId();
        
        $stmt = $db->prepare("
            INSERT INTO BookingSeats (bookingID, seatID, price)
            SELECT ?, seatID, price FROM Seats WHERE seatID = ?
        ");
        
        foreach ($seatIDs as $seatID) {
            $stmt->execute([$bookingID, $seatID]);
        }
        
        $stmt = $db->prepare("DELETE FROM SeatLocks WHERE showtimeID = ? AND userID = ?");
        $stmt->execute([$showtimeID, $userID]);
        
        $db->commit();
        return $bookingID;
    } catch (Exception $e) {
        $db->rollBack();
        error_log("Booking error: " . $e->getMessage());
        throw $e; // Re-throw để controller bắt được
    }
}

function get_booking_by_id($bookingID) {
    global $db;
    
    $stmt = $db->prepare("
        SELECT b.*, u.username, u.email, st.showDate, st.showTime,
               m.title as movieTitle, m.posterURL, m.duration, m.genre,
               r.roomName
        FROM Bookings b
        INNER JOIN User u ON b.userID = u.userID
        INNER JOIN Showtimes st ON b.showtimeID = st.showtimeID
        INNER JOIN Movie m ON st.movieID = m.movieID
        INNER JOIN Rooms r ON st.roomID = r.roomID
        WHERE b.bookingID = ?
    ");
    
    $stmt->execute([$bookingID]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_booking_with_details($bookingID) {
    global $db;
    
    $booking = get_booking_by_id($bookingID);
    if (!$booking) return null;
    
    $stmt = $db->prepare("
        SELECT s.seatID, s.seatRow, s.seatNumber, s.seatType, bs.price
        FROM BookingSeats bs
        INNER JOIN Seats s ON bs.seatID = s.seatID
        WHERE bs.bookingID = ?
        ORDER BY s.seatRow, s.seatNumber
    ");
    
    $stmt->execute([$bookingID]);
    $seats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $seatNames = array_map(function($seat) {
        return $seat['seatRow'] . $seat['seatNumber'];
    }, $seats);
    
    $booking['seats'] = implode(', ', $seatNames);
    $booking['seatDetails'] = $seats;
    
    return $booking;
}

function update_booking_payment_status($bookingID, $status, $paymentMethod = null) {
    global $db;
    
    $validStatuses = ['pending', 'paid', 'cancelled', 'expired'];
    if (!in_array($status, $validStatuses)) return false;
    
    if ($status === 'paid') {
        $stmt = $db->prepare("
            UPDATE Bookings 
            SET paymentStatus = ?, paymentMethod = COALESCE(?, paymentMethod), paidAt = NOW()
            WHERE bookingID = ?
        ");
        return $stmt->execute([$status, $paymentMethod, $bookingID]);
    } else {
        $stmt = $db->prepare("UPDATE Bookings SET paymentStatus = ? WHERE bookingID = ?");
        return $stmt->execute([$status, $bookingID]);
    }
}

function get_user_bookings($userID, $status = null) {
    global $db;
    
    if ($status) {
        $stmt = $db->prepare("
            SELECT b.*, m.title as movieTitle, m.posterURL, st.showDate, st.showTime, r.roomName,
                   GROUP_CONCAT(CONCAT(s.seatRow, s.seatNumber) ORDER BY s.seatRow, s.seatNumber SEPARATOR ', ') as seats
            FROM Bookings b
            INNER JOIN Showtimes st ON b.showtimeID = st.showtimeID
            INNER JOIN Movie m ON st.movieID = m.movieID
            INNER JOIN Rooms r ON st.roomID = r.roomID
            LEFT JOIN BookingSeats bs ON b.bookingID = bs.bookingID
            LEFT JOIN Seats s ON bs.seatID = s.seatID
            WHERE b.userID = ? AND b.paymentStatus = ?
            GROUP BY b.bookingID
            ORDER BY b.bookingDate DESC
        ");
        $stmt->execute([$userID, $status]);
    } else {
        $stmt = $db->prepare("
            SELECT b.*, m.title as movieTitle, m.posterURL, st.showDate, st.showTime, r.roomName,
                   GROUP_CONCAT(CONCAT(s.seatRow, s.seatNumber) ORDER BY s.seatRow, s.seatNumber SEPARATOR ', ') as seats
            FROM Bookings b
            INNER JOIN Showtimes st ON b.showtimeID = st.showtimeID
            INNER JOIN Movie m ON st.movieID = m.movieID
            INNER JOIN Rooms r ON st.roomID = r.roomID
            LEFT JOIN BookingSeats bs ON b.bookingID = bs.bookingID
            LEFT JOIN Seats s ON bs.seatID = s.seatID
            WHERE b.userID = ?
            GROUP BY b.bookingID
            ORDER BY b.bookingDate DESC
        ");
        $stmt->execute([$userID]);
    }
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function cancel_booking($bookingID, $userID) {
    global $db;
    
    $stmt = $db->prepare("
        SELECT b.bookingID, st.showDate, st.showTime
        FROM Bookings b
        INNER JOIN Showtimes st ON b.showtimeID = st.showtimeID
        WHERE b.bookingID = ? AND b.userID = ? AND b.paymentStatus IN ('pending', 'paid')
    ");
    
    $stmt->execute([$bookingID, $userID]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) return false;
    
    $showDateTime = $booking['showDate'] . ' ' . $booking['showTime'];
    $minCancelTime = strtotime($showDateTime) - (2 * 60 * 60);
    
    if (time() > $minCancelTime) return false;
    
    return update_booking_payment_status($bookingID, 'cancelled');
}
