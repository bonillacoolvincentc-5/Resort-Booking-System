<?php
/**
 * Calendar Bookings API (Admin)
 * Returns all bookings for a specific date.
 */

session_start();
require_once '../phpAuthentication/db_config.php';

header('Content-Type: application/json');

$isAdmin = isset($_SESSION['admin_id']) || (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true);
$isStaff = isset($_SESSION['is_staff']) && $_SESSION['is_staff'] === true;

if (!$isAdmin && !$isStaff) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$date = trim($_GET['date'] ?? '');
if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode(['success' => false, 'message' => 'Valid date is required (YYYY-MM-DD)']);
    exit();
}

try {
    $stmt = $conn->prepare("
        SELECT
            b.id,
            b.booking_reference,
            b.booking_type,
            b.full_name,
            b.email,
            b.phone_number,
            b.check_in_date,
            b.check_in_time,
            b.check_out_date,
            b.check_out_time,
            b.number_of_guests,
            b.total_amount,
            b.payment_status,
            b.payment_method,
            b.special_requests,
            b.created_at,
            bs.name AS status_name,
            bs.color AS status_color,
            CASE
                WHEN b.booking_type = 'activity' THEN a.name
                WHEN b.booking_type = 'accommodation' THEN acc.name
                WHEN b.booking_type = 'bundle' THEN bun.name
            END AS item_name,
            acc.type AS accommodation_type
        FROM bookings b
        LEFT JOIN booking_status bs ON b.status_id = bs.id
        LEFT JOIN activities a ON b.activity_id = a.id
        LEFT JOIN accommodations acc ON b.accommodation_id = acc.id
        LEFT JOIN bundles bun ON b.bundle_id = bun.id
        WHERE bs.name NOT IN ('rejected', 'cancelled')
          AND (
              (b.booking_type = 'activity' AND b.check_in_date = ?)
              OR (
                  b.booking_type IN ('accommodation', 'bundle')
                  AND (
                      (b.check_in_date = b.check_out_date AND b.check_in_date = ?)
                      OR (b.check_in_date < b.check_out_date AND b.check_in_date <= ? AND b.check_out_date > ?)
                      OR ((b.check_out_date IS NULL OR b.check_out_date = '') AND b.check_in_date = ?)
                  )
              )
          )
          
        UNION ALL
        
        SELECT
            b.id,
            b.booking_reference,
            'accommodation' AS booking_type,
            b.full_name,
            b.email,
            b.phone_number,
            er.check_in_date,
            b.check_in_time,
            er.check_out_date,
            b.check_out_time,
            er.num_guests AS number_of_guests,
            b.total_amount,
            b.payment_status,
            b.payment_method,
            b.special_requests,
            b.created_at,
            bs.name AS status_name,
            bs.color AS status_color,
            acc.name AS item_name,
            acc.type AS accommodation_type
        FROM booking_extra_rooms er
        JOIN bookings b ON er.booking_id = b.id
        LEFT JOIN booking_status bs ON b.status_id = bs.id
        LEFT JOIN accommodations acc ON er.accommodation_id = acc.id
        WHERE bs.name NOT IN ('rejected', 'cancelled')
          AND (
              (er.check_in_date = er.check_out_date AND er.check_in_date = ?)
              OR (er.check_in_date < er.check_out_date AND er.check_in_date <= ? AND er.check_out_date > ?)
              OR ((er.check_out_date IS NULL OR er.check_out_date = '') AND er.check_in_date = ?)
          )
          
        ORDER BY check_in_time ASC, created_at ASC
    ");
    $stmt->bind_param('sssssssss', $date, $date, $date, $date, $date, $date, $date, $date, $date);
    $stmt->execute();
    $result = $stmt->get_result();

    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }

    echo json_encode([
        'success' => true,
        'date' => $date,
        'bookings' => $bookings
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
