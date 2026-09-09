<?php
/**
 * Upload Payment Proof & Create Booking
 * Handles multipart form data with proof-of-payment image upload
 */

session_start();
require_once '../phpAuthentication/db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in.']);
    exit();
}

try {
    // ── Validate required text fields ─────────────────────────────
    $required = ['booking_type', 'full_name', 'email', 'phone_number', 'check_in_date', 'number_of_guests'];
    foreach ($required as $f) {
        if (empty($_POST[$f])) throw new Exception("Field '$f' is required");
    }

    $bookingType    = $_POST['booking_type'];
    $checkInDate    = $_POST['check_in_date'];
    $checkOutDate   = !empty($_POST['check_out_date']) ? $_POST['check_out_date'] : null;
    $checkInTime    = !empty($_POST['check_in_time']) ? $_POST['check_in_time'] : null;
    $checkOutTime   = !empty($_POST['check_out_time']) ? $_POST['check_out_time'] : null;
    $numberOfGuests = intval($_POST['number_of_guests']);
    $specialReqs    = $_POST['special_requests'] ?? null;

    $itemId = 0;
    if ($bookingType === 'activity') $itemId = intval($_POST['activity_id'] ?? 0);
    elseif ($bookingType === 'accommodation') $itemId = intval($_POST['accommodation_id'] ?? 0);
    elseif ($bookingType === 'bundle') $itemId = intval($_POST['bundle_id'] ?? 0);

    if (!$itemId || !in_array($bookingType, ['activity', 'accommodation', 'bundle'])) {
        throw new Exception('Invalid booking type or missing item ID.');
    }

    // ── Check blocked dates ───────────────────────────────────────
    $conn->query("
        CREATE TABLE IF NOT EXISTS `blocked_dates` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `blocked_date` date NOT NULL,
            `reason` text DEFAULT NULL,
            `blocked_by` int(11) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_blocked_date` (`blocked_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $bs = $conn->prepare("SELECT id FROM blocked_dates WHERE blocked_date = ? LIMIT 1");
    $bs->bind_param('s', $checkInDate);
    $bs->execute();
    if ($bs->get_result()->num_rows > 0) {
        throw new Exception('This date is blocked. Please choose another date.');
    }

    // ── Fetch item and calculate price ─────────────────────────────
    $price = 0;
    $numberOfDays = 1;

    if ($bookingType === 'activity') {
        $s = $conn->prepare("SELECT price_per_hour, capacity FROM activities WHERE id = ? AND is_active = 1");
        $s->bind_param('i', $itemId);
        $s->execute();
        $item = $s->get_result()->fetch_assoc();
        if (!$item) throw new Exception('Activity not found or inactive.');
        if ($numberOfGuests > $item['capacity']) throw new Exception("Activity capacity is {$item['capacity']} guests.");
        $tIn = strtotime($checkInTime);
        $tOut = strtotime($checkOutTime);
        $hours = max(1, ($tOut - $tIn) / 3600);
        $price = $item['price_per_hour'] * $hours;
    } elseif ($bookingType === 'accommodation') {
        $s = $conn->prepare("SELECT price_per_night, capacity FROM accommodations WHERE id = ? AND is_active = 1");
        $s->bind_param('i', $itemId);
        $s->execute();
        $item = $s->get_result()->fetch_assoc();
        if (!$item) throw new Exception('Accommodation not found or inactive.');
        if ($numberOfGuests > $item['capacity']) throw new Exception("Accommodation capacity is {$item['capacity']} guests.");
        if ($checkOutDate) {
            $d1 = new DateTime($checkInDate);
            $d2 = new DateTime($checkOutDate);
            $numberOfDays = max(1, $d1->diff($d2)->days);
        }
        $price = $item['price_per_night'] * $numberOfDays;
    } else {
        $s = $conn->prepare("SELECT price, capacity, start_time, duration_hours FROM bundles WHERE id = ? AND is_active = 1");
        $s->bind_param('i', $itemId);
        $s->execute();
        $item = $s->get_result()->fetch_assoc();
        if (!$item) throw new Exception('Bundle not found or inactive.');
        if ($numberOfGuests > $item['capacity']) throw new Exception("Bundle capacity is {$item['capacity']} guests.");

        $startTime = $item['start_time'] ?: '00:00:00';
        $durationHours = intval($item['duration_hours'] ?? 0);
        if ($durationHours < 1) $durationHours = 24;

        $checkInDT = new DateTime($checkInDate . ' ' . $startTime);
        $checkOutDT = clone $checkInDT;
        $checkOutDT->modify('+' . $durationHours . ' hours');

        $checkInTime  = $checkInDT->format('H:i:s');
        $checkOutDate = $checkOutDT->format('Y-m-d');
        $checkOutTime = $checkOutDT->format('H:i:s');
        $numberOfDays = max(1, (int) $checkInDT->diff($checkOutDT)->days);
        $price = $item['price'];
    }

    // ── Handle payment proof upload ───────────────────────────────
    $proofPath = null;
    if (!empty($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/uploads/payment_proofs/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($_FILES['payment_proof']['tmp_name']);
        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception('Invalid file type. Only JPG, PNG, GIF, WEBP allowed.');
        }

        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($_FILES['payment_proof']['size'] > $maxSize) {
            throw new Exception('File too large. Max 5MB.');
        }

        $ext      = pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION);
        $filename = 'proof_' . $_SESSION['user_id'] . '_' . time() . '.' . strtolower($ext);
        $fullPath = $uploadDir . $filename;

        if (!move_uploaded_file($_FILES['payment_proof']['tmp_name'], $fullPath)) {
            throw new Exception('Failed to save payment proof. Please try again.');
        }

        $proofPath = 'assets/uploads/payment_proofs/' . $filename;
    } else {
        throw new Exception('Payment proof image is required.');
    }

    // ── Check availability ────────────────────────────────────────
    $itemColumn = $bookingType === 'activity' ? 'activity_id' : ($bookingType === 'accommodation' ? 'accommodation_id' : 'bundle_id');
    $avCheck = $conn->prepare("
        SELECT COUNT(*) as cnt FROM bookings
        WHERE booking_type = ? AND $itemColumn = ? AND check_in_date = ?
        AND status_id IN (SELECT id FROM booking_status WHERE name IN ('pending_review','approved'))
    ");
    $avCheck->bind_param('sis', $bookingType, $itemId, $checkInDate);
    $avCheck->execute();
    $avRow = $avCheck->get_result()->fetch_assoc();
    if ($avRow['cnt'] >= $item['capacity']) {
        throw new Exception('This slot is fully booked. Please choose another date.');
    }

    // ── Generate booking reference ────────────────────────────────
    $bookingRef = 'BK' . date('Ymd') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

    // ── Get pending_review status ID ──────────────────────────────
    $stSt = $conn->prepare("SELECT id FROM booking_status WHERE name = 'pending_review'");
    $stSt->execute();
    $statusRow = $stSt->get_result()->fetch_assoc();
    if (!$statusRow) throw new Exception('Booking status not configured. Contact admin.');
    $statusId = $statusRow['id'];

    // ── Ensure payment_proof column exists ────────────────────────
    @$conn->query("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS payment_proof VARCHAR(500) NULL");

    // ── Insert booking (with payment_proof) ───────────────────────
    $fullName   = $_POST['full_name'];
    $email      = $_POST['email'];
    $phone      = $_POST['phone_number'];
    $userId     = $_SESSION['user_id'];

    $ins = $conn->prepare("
        INSERT INTO bookings (
            booking_reference, user_id, booking_type, $itemColumn,
            status_id, full_name, email, phone_number,
            check_in_date, check_in_time, check_out_date, check_out_time,
            number_of_days, number_of_guests, special_requests,
            total_amount, payment_status, payment_method, payment_proof
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'gcash', ?)
    ");

    $ins->bind_param(
        'sisiisssssssiisds',
        $bookingRef,
        $userId,
        $bookingType,
        $itemId,
        $statusId,
        $fullName,
        $email,
        $phone,
        $checkInDate,
        $checkInTime,
        $checkOutDate,
        $checkOutTime,
        $numberOfDays,
        $numberOfGuests,
        $specialReqs,
        $price,
        $proofPath
    );

    if (!$ins->execute()) {
        throw new Exception('Failed to create booking: ' . $conn->error);
    }
    $bookingId = $conn->insert_id;

    if (!$bookingId) throw new Exception('Failed to save booking. Please try again.');

    // ── Log creation ──────────────────────────────────────────────
    $log = $conn->prepare("
        INSERT INTO booking_logs (booking_id, old_status_id, new_status_id, changed_by, changed_by_id, notes)
        VALUES (?, NULL, ?, 'user', ?, 'Booking created with GCash payment proof')
    ");
    $log->bind_param('iii', $bookingId, $statusId, $userId);
    $log->execute();

    // ── Update availability ───────────────────────────────────────
    $avRec = $conn->prepare("SELECT id, available_slots FROM availability WHERE item_type = ? AND item_id = ? AND date = ?");
    $avRec->bind_param('sis', $bookingType, $itemId, $checkInDate);
    $avRec->execute();
    $avData = $avRec->get_result()->fetch_assoc();
    if ($avData) {
        $newSlots = max(0, $avData['available_slots'] - 1);
        $isAvail  = $newSlots > 0 ? 1 : 0;
        $avUp = $conn->prepare("UPDATE availability SET available_slots=?, is_available=?, updated_at=NOW() WHERE id=?");
        $avUp->bind_param('iii', $newSlots, $isAvail, $avData['id']);
        $avUp->execute();
    }

    echo json_encode([
        'success'           => true,
        'message'           => 'Booking submitted successfully! Pending admin review.',
        'booking_id'        => $bookingId,
        'booking_reference' => $bookingRef,
        'total_amount'      => $price,
        'redirect'          => 'my-bookings.php'
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
