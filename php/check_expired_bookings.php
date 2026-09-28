<?php
/**
 * Auto-Cancel Expired Awaiting-Payment Bookings
 * Restores calendar capacity by updating status to 'cancelled' when NOW() > expires_at and unpaid.
 */
if (!isset($conn) || !($conn instanceof mysqli)) {
    require_once __DIR__ . '/../phpAuthentication/db_config.php';
}

if (isset($conn) && $conn instanceof mysqli) {
    $expiredBookings = $conn->query("
        SELECT b.id, b.status_id, bs_canc.id AS cancelled_status_id
        FROM bookings b
        INNER JOIN booking_status bs_wait ON b.status_id = bs_wait.id AND bs_wait.name = 'awaiting_payment'
        INNER JOIN booking_status bs_canc ON bs_canc.name = 'cancelled'
        WHERE b.expires_at IS NOT NULL 
          AND b.expires_at < NOW() 
          AND (b.payment_status IS NULL OR b.payment_status != 'paid')
    ");

    if ($expiredBookings && $expiredBookings->num_rows > 0) {
        while ($row = $expiredBookings->fetch_assoc()) {
            $bId = (int)$row['id'];
            $oldSt = (int)$row['status_id'];
            $cancSt = (int)$row['cancelled_status_id'];
            $conn->query("UPDATE bookings SET status_id = $cancSt, updated_at = NOW() WHERE id = $bId");
            $conn->query("INSERT INTO booking_logs (booking_id, old_status_id, new_status_id, changed_by, notes) VALUES ($bId, $oldSt, $cancSt, 'system', 'Auto-cancelled: 2-hour payment window expired')");
        }
    }

    // ── Auto-Complete Approved/Checked-In Bookings ──
    // Marks bookings as 'completed' if the current date is past their check-out date (or check-in date for day tours).
    $completedBookings = $conn->query("
        SELECT b.id, b.status_id, bs_comp.id AS completed_status_id
        FROM bookings b
        INNER JOIN booking_status bs_curr ON b.status_id = bs_curr.id 
        INNER JOIN booking_status bs_comp ON bs_comp.name = 'completed'
        WHERE bs_curr.name IN ('approved', 'checked_in')
          AND (
             (b.check_out_date IS NOT NULL AND b.check_out_date != '' AND b.check_out_date < CURDATE())
             OR 
             ((b.check_out_date IS NULL OR b.check_out_date = '') AND b.check_in_date < CURDATE())
          )
    ");

    if ($completedBookings && $completedBookings->num_rows > 0) {
        while ($row = $completedBookings->fetch_assoc()) {
            $bId = (int)$row['id'];
            $oldSt = (int)$row['status_id'];
            $compSt = (int)$row['completed_status_id'];
            $conn->query("UPDATE bookings SET status_id = $compSt, updated_at = NOW() WHERE id = $bId");
            $conn->query("INSERT INTO booking_logs (booking_id, old_status_id, new_status_id, changed_by, notes) VALUES ($bId, $oldSt, $compSt, 'system', 'Auto-completed: Booking date has passed')");
        }
    }
}
