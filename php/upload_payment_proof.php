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
    $bundleId       = intval($_POST['bundle_id'] ?? 0);
    $checkInDate    = $_POST['check_in_date'];
    $checkOutDate   = $_POST['check_out_date'] ?? null;
    $numberOfGuests = intval($_POST['number_of_guests']);
    $specialReqs    = $_POST['special_requests'] ?? null;

    if ($bookingType !== 'bundle' || !$bundleId) {
        throw new Exception('Invalid booking type or missing bundle ID.');
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

    // ── Fetch bundle ─────────────────────────────────────────────
    $s = $conn->prepare("SELECT price, capacity FROM bundles WHERE id = ? AND is_active = 1");
    $s->bind_param('i', $bundleId);
    $s->execute();
    $bundle = $s->get_result()->fetch_assoc();
    if (!$bundle) throw new Exception('Bundle not found or inactive.');

    if ($numberOfGuests > $bundle['capacity']) {
        throw new Exception("Bundle capacity is {$bundle['capacity']} guests.");
    }

    $price = $bundle['price'];

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
    $avCheck = $conn->prepare("
        SELECT COUNT(*) as cnt FROM bookings
        WHERE booking_type = 'bundle' AND bundle_id = ? AND check_in_date = ?
        AND status_id IN (SELECT id FROM booking_status WHERE name IN ('pending_review','approved'))
    ");
    $avCheck->bind_param('is', $bundleId, $checkInDate);
    $avCheck->execute();
    $avRow = $avCheck->get_result()->fetch_assoc();
    if ($avRow['cnt'] >= $bundle['capacity']) {
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
            booking_reference, user_id, booking_type, bundle_id,
            status_id, full_name, email, phone_number,
            check_in_date, check_out_date,
            number_of_days, number_of_guests, special_requests,
            total_amount, payment_status, payment_method, payment_proof
        ) VALUES (?, ?, 'bundle', ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, 'pending', 'gcash', ?)
    ");

    // types: s=string, i=int, d=double
    // booking_reference(s), user_id(i), bundle_id(i), status_id(i),
    // full_name(s), email(s), phone(s), check_in_date(s), check_out_date(s),
    // number_of_guests(i), special_requests(s), total_amount(d), payment_proof(s)
    $ins->bind_param(
        'siiisssssisds',
        $bookingRef,
        $userId,
        $bundleId,
        $statusId,
        $fullName,
        $email,
        $phone,
        $checkInDate,
        $checkOutDate,
        $numberOfGuests,
        $specialReqs,
        $price,
        $proofPath
    );

    if (!$ins->execute()) {
        // Fallback without payment_proof column (in case column doesn't exist yet)
        $ins2 = $conn->prepare("
            INSERT INTO bookings (
                booking_reference, user_id, booking_type, bundle_id,
                status_id, full_name, email, phone_number,
                check_in_date, check_out_date,
                number_of_days, number_of_guests, special_requests,
                total_amount, payment_status, payment_method
            ) VALUES (?, ?, 'bundle', ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, 'pending', 'gcash')
        ");
        $ins2->bind_param(
            'siiisssssisd',
            $bookingRef,
            $userId,
            $bundleId,
            $statusId,
            $fullName,
            $email,
            $phone,
            $checkInDate,
            $checkOutDate,
            $numberOfGuests,
            $specialReqs,
            $price
        );
        if (!$ins2->execute()) {
            throw new Exception('Failed to create booking: ' . $conn->error);
        }
        $bookingId = $conn->insert_id;
        // Try attaching proof afterwards
        $upd = $conn->prepare("UPDATE bookings SET payment_proof = ? WHERE id = ?");
        if ($upd) {
            $upd->bind_param('si', $proofPath, $bookingId);
            $upd->execute();
        }
    } else {
        $bookingId = $conn->insert_id;
    }

    if (!$bookingId) throw new Exception('Failed to save booking. Please try again.');

    // ── Log creation ──────────────────────────────────────────────
    $log = $conn->prepare("
        INSERT INTO booking_logs (booking_id, old_status_id, new_status_id, changed_by, changed_by_id, notes)
        VALUES (?, NULL, ?, 'user', ?, 'Booking created with GCash payment proof')
    ");
    $log->bind_param('iii', $bookingId, $statusId, $userId);
    $log->execute();

    // ── Update availability ───────────────────────────────────────
    $avRec = $conn->prepare("SELECT id, available_slots FROM availability WHERE item_type = 'bundle' AND item_id = ? AND date = ?");
    $avRec->bind_param('is', $bundleId, $checkInDate);
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
