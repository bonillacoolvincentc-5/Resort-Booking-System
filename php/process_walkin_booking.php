<?php
// ── Walk-In Reservation Backend ───────────────────────────────────────────────
// File: php/process_walkin_booking.php
// Called by Admin_Dashboard/bookings.php (Walk-In modal) via fetch() POST JSON.
// Returns JSON: { success: bool, message: string, reference?: string }
// ─────────────────────────────────────────────────────────────────────────────

session_start();
header('Content-Type: application/json');

// ── 1. Admin auth guard ──────────────────────────────────────────────────────
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorised access.']);
    exit;
}

// ── 2. Accept JSON body ──────────────────────────────────────────────────────
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid request payload.']);
    exit;
}

// ── 3. Pull & sanitise fields ────────────────────────────────────────────────
$guest_name     = trim($data['guest_name']    ?? '');
$guest_contact  = trim($data['guest_contact'] ?? '');
$booking_type   = trim($data['booking_type']  ?? '');   // activity | accommodation | bundle
$item_id        = (int)($data['item_id']      ?? 0);
$check_in_date  = trim($data['check_in_date'] ?? '');
$check_in_time  = trim($data['check_in_time'] ?? '') ?: null;
$check_out_date = trim($data['check_out_date'] ?? '') ?: null;
$check_out_time = trim($data['check_out_time'] ?? '') ?: null;
$num_guests     = max(1, (int)($data['num_guests'] ?? 1));
$payment_method = in_array($data['payment_method'] ?? '', ['cash', 'gcash'])
                    ? $data['payment_method'] : 'cash';
$special_req    = trim($data['special_requests'] ?? '') ?: null;
$num_days       = ($check_out_date && $check_in_date)
                    ? (int)((strtotime($check_out_date) - strtotime($check_in_date)) / 86400)
                    : 1;

// ── 4. Basic validation ──────────────────────────────────────────────────────
$errors = [];
if ($guest_name === '')                                     $errors[] = 'Guest name is required.';
if (!preg_match('/^09\d{9}$/', $guest_contact))            $errors[] = 'Guest contact must be a valid PH mobile number (09XXXXXXXXX).';
if (!in_array($booking_type, ['activity','accommodation','bundle']))
                                                           $errors[] = 'Invalid booking type.';
if ($item_id < 1)                                          $errors[] = 'No item selected.';
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $check_in_date)) $errors[] = 'Invalid check-in date.';

if ($errors) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// ── 5. DB connection ─────────────────────────────────────────────────────────
require_once __DIR__ . '/../phpAuthentication/db_config.php';
global $conn;

// ── 6. Fetch item price & name; verify item exists & is active ───────────────
switch ($booking_type) {
    case 'activity':
        $iStmt = $conn->prepare(
            "SELECT id, name, price_per_hour FROM activities WHERE id = ? AND is_active = 1 LIMIT 1"
        );
        break;
    case 'accommodation':
        $iStmt = $conn->prepare(
            "SELECT id, name, price_per_night AS price_per_hour FROM accommodations WHERE id = ? AND is_active = 1 LIMIT 1"
        );
        break;
    case 'bundle':
        $iStmt = $conn->prepare(
            "SELECT id, name, price AS price_per_hour FROM bundles WHERE id = ? AND is_active = 1 LIMIT 1"
        );
        break;
}

$iStmt->bind_param('i', $item_id);
$iStmt->execute();
$item = $iStmt->get_result()->fetch_assoc();
$iStmt->close();

if (!$item) {
    echo json_encode(['success' => false, 'message' => 'Selected item not found or inactive.']);
    exit;
}

// ── 7. Duration / amount calculation ────────────────────────────────────────
$duration_hours = ($check_in_time && $check_out_time)
    ? max(1, (strtotime($check_out_time) - strtotime($check_in_time)) / 3600)
    : 1;

switch ($booking_type) {
    case 'activity':
        $total_amount = round($item['price_per_hour'] * $duration_hours, 2);
        break;
    case 'accommodation':
        $total_amount = round($item['price_per_hour'] * max(1, $num_days), 2);
        break;
    case 'bundle':
        $total_amount = round($item['price_per_hour'], 2);   // flat bundle price
        break;
    default:
        $total_amount = 0;
}

// ── 8. STRICT OVERLAP VALIDATION ─────────────────────────────────────────────
// Blocks insert if the exact item/date/time slot already has an approved or
// pending_review booking (regardless of online or walk-in source).
//
// Rule:  A conflict exists when the requested period overlaps with any existing
//        active booking for the same item:
//          new.check_in  < existing.check_out
//          new.check_out > existing.check_in
// "Active" statuses = pending_review (id 1) and approved (id 2).
// ─────────────────────────────────────────────────────────────────────────────

$col_item = match($booking_type) {
    'activity'      => 'activity_id',
    'accommodation' => 'accommodation_id',
    'bundle'        => 'bundle_id',
};

// Treat NULL check_out as same day end (check_in date + 1 day)
$effective_checkout_date = $check_out_date ?: date('Y-m-d', strtotime($check_in_date . ' +1 day'));
$effective_checkout_time = $check_out_time ?: '23:59:59';
$effective_checkin_time  = $check_in_time  ?: '00:00:00';

// Combine date+time into a single DATETIME string for comparison
$req_start = $check_in_date . ' ' . $effective_checkin_time;
$req_end   = $effective_checkout_date . ' ' . $effective_checkout_time;

$overlapSql = "
    SELECT COUNT(*) AS cnt
    FROM bookings b
    JOIN booking_status bs ON b.status_id = bs.id
    WHERE b.{$col_item} = ?
      AND bs.name IN ('pending_review', 'approved', 'checked_in')
      AND CONCAT(b.check_in_date, ' ', COALESCE(b.check_in_time, '00:00:00'))  < ?
      AND CONCAT(COALESCE(b.check_out_date, DATE_ADD(b.check_in_date, INTERVAL 1 DAY)),
                 ' ', COALESCE(b.check_out_time, '23:59:59'))                  > ?
";

$oStmt = $conn->prepare($overlapSql);
$oStmt->bind_param('iss', $item_id, $req_end, $req_start);
$oStmt->execute();
$overlapCount = (int)$oStmt->get_result()->fetch_assoc()['cnt'];
$oStmt->close();

if ($overlapCount > 0) {
    echo json_encode([
        'success' => false,
        'message' => "Slot conflict detected — the selected {$booking_type} already has an active booking "
                   . "overlapping that date/time. Please choose a different slot or item."
    ]);
    exit;
}

// ── 9. Generate unique booking reference ─────────────────────────────────────
do {
    $ref = 'WI-' . date('Y') . '-' . strtoupper(substr(uniqid(), -6));
    $rStmt = $conn->prepare("SELECT id FROM bookings WHERE booking_reference = ? LIMIT 1");
    $rStmt->bind_param('s', $ref);
    $rStmt->execute();
    $exists = $rStmt->get_result()->num_rows > 0;
    $rStmt->close();
} while ($exists);

// ── 10. Resolve 'checked_in' status ID ───────────────────────────────────────
$sStmt = $conn->prepare("SELECT id FROM booking_status WHERE name = 'checked_in' LIMIT 1");
$sStmt->execute();
$statusRow = $sStmt->get_result()->fetch_assoc();
$sStmt->close();

if (!$statusRow) {
    echo json_encode(['success' => false, 'message' => "Status 'checked_in' not found. Did you run walkin_schema_update.sql?"]);
    exit;
}
$status_id = (int)$statusRow['id'];

// ── 11. Insert the walk-in booking ───────────────────────────────────────────
$activity_id      = ($booking_type === 'activity')      ? $item_id : null;
$accommodation_id = ($booking_type === 'accommodation') ? $item_id : null;
$bundle_id        = ($booking_type === 'bundle')        ? $item_id : null;

// full_name & phone for backward-compat with online booking columns
$full_name   = $guest_name;
$email       = '';          // walk-ins have no email
$phone       = $guest_contact;

$insertSql = "
    INSERT INTO bookings (
        booking_reference, user_id, booking_type,
        activity_id, accommodation_id, bundle_id,
        status_id, full_name, email, phone_number,
        check_in_date, check_in_time, check_out_date, check_out_time,
        number_of_days, number_of_guests, special_requests,
        total_amount, payment_status, payment_method,
        booking_source, guest_name, guest_contact
    ) VALUES (
        ?, NULL, ?,
        ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?,
        ?, 'paid', ?,
        'walk_in', ?, ?
    )
";

$ins = $conn->prepare($insertSql);
$ins->bind_param(
    'ssiiiisssssssiisdsss',
    $ref,           // booking_reference
    $booking_type,  // booking_type
    $activity_id,   // activity_id
    $accommodation_id,
    $bundle_id,
    $status_id,     // status_id
    $full_name,     // full_name
    $email,         // email
    $phone,         // phone_number
    $check_in_date,
    $check_in_time,
    $check_out_date,
    $check_out_time,
    $num_days,
    $num_guests,
    $special_req,
    $total_amount,
    $payment_method,
    $guest_name,    // guest_name (walk-in specific)
    $guest_contact  // guest_contact
);

$ins->execute();
$new_id = $conn->insert_id;
$ins->close();

// ── 12. Log the action in booking_logs ───────────────────────────────────────
$admin_id  = (int)($_SESSION['admin_id'] ?? 0);
$logNote   = 'Walk-in reservation created by admin.';
$logStmt   = $conn->prepare(
    "INSERT INTO booking_logs (booking_id, new_status_id, changed_by, changed_by_id, notes)
     VALUES (?, ?, 'admin', ?, ?)"
);
$logStmt->bind_param('iiis', $new_id, $status_id, $admin_id, $logNote);
$logStmt->execute();
$logStmt->close();

// ── 13. Return success ───────────────────────────────────────────────────────
echo json_encode([
    'success'   => true,
    'message'   => "Walk-in booking created successfully.",
    'reference' => $ref,
    'booking_id'=> $new_id,
    'item_name' => $item['name'],
    'total'     => $total_amount,
]);
exit;
