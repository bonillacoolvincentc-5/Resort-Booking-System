<?php
/**
 * Get All Bookings API (Admin)
 * Returns all bookings for admin management
 */

session_start();
require_once '../phpAuthentication/db_config.php';

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in as admin to view bookings'
    ]);
    exit();
}

try {
    $statusFilter = $_GET['status'] ?? null;
    $typeFilter   = $_GET['type']   ?? null;

    // FIX #8: u.name → CONCAT(first_name, ..., last_name)
    // FIX #9: Added b.bundle_id, LEFT JOIN bundles, and bundle CASE branches
    $query = "
        SELECT
            b.id,
            b.booking_reference,
            b.booking_type,
            b.activity_id,
            b.accommodation_id,
            b.bundle_id,
            b.user_id,
            b.full_name,
            b.email,
            b.phone_number,
            b.check_in_date,
            b.check_in_time,
            b.check_out_date,
            b.check_out_time,
            b.number_of_days,
            b.number_of_guests,
            b.special_requests,
            b.total_amount,
            b.payment_status,
            b.payment_method,
            b.created_at,
            bs.name        as status_name,
            bs.description as status_description,
            bs.color       as status_color,
            CONCAT(u.first_name, ' ', COALESCE(u.middle_name, ''), ' ', u.last_name) as user_name,
            u.email        as user_email,
            CASE
                WHEN b.booking_type = 'activity'      THEN a.name
                WHEN b.booking_type = 'accommodation' THEN acc.name
                WHEN b.booking_type = 'bundle'        THEN bun.name
            END as item_name,
            CASE
                WHEN b.booking_type = 'activity' THEN 'bi-activity'
                ELSE NULL
            END as item_icon,
            pr.file_path as receipt_path
        FROM bookings b
        LEFT JOIN booking_status bs  ON b.status_id       = bs.id
        LEFT JOIN users u            ON b.user_id          = u.id
        LEFT JOIN activities a       ON b.activity_id      = a.id
        LEFT JOIN accommodations acc ON b.accommodation_id = acc.id
        LEFT JOIN bundles bun        ON b.bundle_id        = bun.id
        LEFT JOIN payment_receipts pr ON b.id              = pr.booking_id
        WHERE 1=1
    ";

    $params = [];
    $types  = '';

    if ($statusFilter) {
        $query   .= " AND bs.name = ?";
        $params[] = $statusFilter;
        $types   .= 's';
    }

    if ($typeFilter) {
        $query   .= " AND b.booking_type = ?";
        $params[] = $typeFilter;
        $types   .= 's';
    }

    $query .= " ORDER BY b.created_at DESC";

    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }

    // Booking counts by status
    $statsQuery = "
        SELECT
            bs.name as status,
            COUNT(*) as count
        FROM bookings b
        JOIN booking_status bs ON b.status_id = bs.id
        GROUP BY bs.name
    ";

    $stmt = $conn->prepare($statsQuery);
    $stmt->execute();
    $result = $stmt->get_result();

    $stats = [];
    while ($row = $result->fetch_assoc()) {
        $stats[$row['status']] = $row['count'];
    }

    echo json_encode([
        'success'  => true,
        'bookings' => $bookings,
        'stats'    => $stats
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
