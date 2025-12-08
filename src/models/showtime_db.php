<?php
/**
 * Showtime Database Functions - PDO Version
 */

require_once __DIR__ . '/database.php';

function get_showtimes_by_movie($movieID, $showDate = null, $days = 7) {
    global $db;
    
    if ($showDate) {
        $stmt = $db->prepare("
            SELECT st.*, r.roomName, r.roomType, r.totalSeats,
                   COALESCE(COUNT(DISTINCT bs.seatID), 0) as bookedSeats,
                   (r.totalSeats - COALESCE(COUNT(DISTINCT bs.seatID), 0)) as availableSeats
            FROM showtimes st
            INNER JOIN rooms r ON st.roomID = r.roomID
            LEFT JOIN bookings b ON st.showtimeID = b.showtimeID AND b.paymentStatus IN ('pending', 'paid')
            LEFT JOIN bookingseats bs ON b.bookingID = bs.bookingID
            WHERE st.movieID = ? AND st.showDate = ? AND st.status = 'available'
            GROUP BY st.showtimeID
            ORDER BY st.showTime ASC
        ");
        $stmt->execute([$movieID, $showDate]);
    } else {
        $stmt = $db->prepare("
            SELECT st.*, r.roomName, r.roomType, r.totalSeats
            FROM showtimes st
            INNER JOIN rooms r ON st.roomID = r.roomID
            WHERE st.movieID = ? AND st.showDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
            AND st.status = 'available'
            ORDER BY st.showDate ASC, st.showTime ASC
        ");
        $stmt->execute([$movieID, $days]);
    }
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_showtime_by_id($showtimeID) {
    global $db;
    
    $stmt = $db->prepare("
        SELECT st.*, m.title as movieTitle, m.posterURL, m.duration, m.genre,
               r.roomName, r.roomType, r.totalSeats,
               COALESCE(COUNT(DISTINCT bs.seatID), 0) as bookedSeats,
               (r.totalSeats - COALESCE(COUNT(DISTINCT bs.seatID), 0)) as availableSeats
        FROM showtimes st
        INNER JOIN movie m ON st.movieID = m.movieID
        INNER JOIN rooms r ON st.roomID = r.roomID
        LEFT JOIN bookings b ON st.showtimeID = b.showtimeID AND b.paymentStatus IN ('pending', 'paid')
        LEFT JOIN bookingseats bs ON b.bookingID = bs.bookingID
        WHERE st.showtimeID = ?
        GROUP BY st.showtimeID
    ");
    
    $stmt->execute([$showtimeID]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_available_dates_by_movie($movieID, $days = 7) {
    global $db;
    
    $stmt = $db->prepare("
        SELECT DISTINCT showDate
        FROM showtimes
        WHERE movieID = ? AND showDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
        AND status = 'available'
        ORDER BY showDate ASC
    ");
    
    $stmt->execute([$movieID, $days]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function check_showtime_availability($showtimeID) {
    $showtime = get_showtime_by_id($showtimeID);
    return $showtime ? $showtime['availableSeats'] > 0 : false;
}

function get_upcoming_showtimes_by_movie($movieID, $days = 7) {
    global $db;
    
    $stmt = $db->prepare("
        SELECT st.*, r.roomName, r.roomType as format, r.totalSeats,
               COALESCE(COUNT(DISTINCT bs.seatID), 0) as bookedSeats,
               (r.totalSeats - COALESCE(COUNT(DISTINCT bs.seatID), 0)) as availableSeats
        FROM showtimes st
        INNER JOIN rooms r ON st.roomID = r.roomID
        LEFT JOIN bookings b ON st.showtimeID = b.showtimeID AND b.paymentStatus IN ('pending', 'paid')
        LEFT JOIN bookingseats bs ON b.bookingID = bs.bookingID
        WHERE st.movieID = ? 
        AND st.showDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
        AND st.status = 'available'
        AND CONCAT(st.showDate, ' ', st.showTime) > NOW()
        GROUP BY st.showtimeID
        ORDER BY st.showDate ASC, st.showTime ASC
    ");
    
    $stmt->execute([$movieID, $days]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
