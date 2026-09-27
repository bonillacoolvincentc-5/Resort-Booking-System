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
}
