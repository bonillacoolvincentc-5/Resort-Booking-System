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
        $s = $conn->prepare("SELECT price_per_hour, price_type, capacity, daily_booking_limit FROM activities WHERE id = ? AND is_active = 1");
        $s->bind_param('i', $itemId);
        $s->execute();
        $item = $s->get_result()->fetch_assoc();
        if (!$item) throw new Exception('Activity not found or inactive.');
        if ($numberOfGuests > $item['capacity']) throw new Exception("Activity capacity is {$item['capacity']} guests.");
        $tIn = strtotime($checkInTime);
        $tOut = strtotime($checkOutTime);
        $hours = max(1, ($tOut - $tIn) / 3600);
        $pType = $item['price_type'] ?? 'flat_rate';
        if ($pType === 'per_head') {
            $price = floatval($item['price_per_hour']) * $numberOfGuests;
        } elseif ($pType === 'per_hour') {
            $price = floatval($item['price_per_hour']) * $hours;
        } else {
            $price = floatval($item['price_per_hour']);
        }
    } elseif ($bookingType === 'accommodation') {
        $s = $conn->prepare("SELECT price_per_night, price_per_day, base_capacity, capacity, daily_booking_limit, extra_person_fee FROM accommodations WHERE id = ? AND is_active = 1");
        $s->bind_param('i', $itemId);
        $s->execute();
        $item = $s->get_result()->fetch_assoc();
        if (!$item) throw new Exception('Accommodation not found or inactive.');
        if ($numberOfGuests > $item['capacity']) throw new Exception("Accommodation capacity is {$item['capacity']} guests.");

        $tourType = strtolower(trim($_POST['tour_type'] ?? ''));
        $isDayTour = ($tourType === 'daytour' || empty($checkOutDate) || $checkOutDate === $checkInDate);

        $baseCap = !empty($item['base_capacity']) ? intval($item['base_capacity']) : intval($item['capacity']);
        $extraFee = floatval($item['extra_person_fee'] ?? 0);

        if ($isDayTour) {
            $numberOfDays = 1;
            $dayRate = (!empty($item['price_per_day']) && floatval($item['price_per_day']) > 0)
                ? floatval($item['price_per_day'])
                : floatval($item['price_per_night']);
            $price = $dayRate;
            if ($numberOfGuests > $baseCap && $extraFee > 0) {
                $price += ($numberOfGuests - $baseCap) * $extraFee;
            }
        } else {
            if ($checkOutDate) {
                $d1 = new DateTime($checkInDate);
                $d2 = new DateTime($checkOutDate);
                $numberOfDays = max(1, $d1->diff($d2)->days);
            }
            $nightRate = floatval($item['price_per_night']);
            $price = $nightRate * $numberOfDays;
            if ($numberOfGuests > $baseCap && $extraFee > 0) {
                $price += ($numberOfGuests - $baseCap) * $extraFee * $numberOfDays;
            }
        }
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

    // ── Calculate Add-on Activities (if any) ────────────────────────
    $addonsJson = $_POST['addons'] ?? '';
    $addonItems = [];
    if (!empty($addonsJson)) {
        if (is_string($addonsJson)) {
            $addonItems = json_decode($addonsJson, true) ?: [];
        } elseif (is_array($addonsJson)) {
            $addonItems = $addonsJson;
        }
    }
    foreach ($addonItems as $ai) {
        $aPrice = floatval($ai['price'] ?? 0);
        $aQty = max(1, intval($ai['qty'] ?? 1));
        $pType = $ai['price_type'] ?? 'flat_rate';
        if ($pType === 'flat_rate') {
            $price += $aPrice;
        } else {
            $price += ($aPrice * $aQty);
        }
    }

    // ── Handle payment proof upload ───────────────────────────────
    $proofPath = null;
    if (!empty($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/uploads/payment_proofs/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Server-side MIME detection (never trust client-supplied type)
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $detectedMime = $finfo->file($_FILES['payment_proof']['tmp_name']);
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($detectedMime, $allowedTypes, true)) {
            throw new Exception('Invalid file type. Only JPG, PNG, and WEBP images are accepted.');
        }

        $maxSize = 1048576; // 1 MiB
        if ($_FILES['payment_proof']['size'] > $maxSize) {
            throw new Exception('File too large. Maximum allowed size is 1 MB.');
        }

        // Derive extension from the detected MIME type (safe — not from client filename)
        $mimeExtMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $ext = $mimeExtMap[$detectedMime];
        $filename = 'proof_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
        $fullPath = $uploadDir . $filename;

        if (!move_uploaded_file($_FILES['payment_proof']['tmp_name'], $fullPath)) {
            throw new Exception('Failed to save payment proof. Please try again.');
        }

        $proofPath = 'assets/uploads/payment_proofs/' . $filename;
    } else {
        throw new Exception('Payment proof image is required.');
    }

    // ── Global Daily Resort Capacity Check ────────────────────────
    $globalCapStmt = $conn->prepare("SELECT max_guests FROM daily_resort_capacity WHERE target_date = ?");
    $globalCapStmt->bind_param('s', $checkInDate);
    $globalCapStmt->execute();
    $globalCapResult = $globalCapStmt->get_result();
    if ($globalCapRow = $globalCapResult->fetch_assoc()) {
        $dailyResortCap = intval($globalCapRow['max_guests']);
        
        $activeStatuses = "(SELECT id FROM booking_status WHERE name IN ('pending_review','approved'))";
        $totalGuestsStmt = $conn->prepare("
            SELECT SUM(number_of_guests) as total_guests 
            FROM bookings 
            WHERE check_in_date = ? AND status_id IN $activeStatuses
        ");
        $totalGuestsStmt->bind_param('s', $checkInDate);
        $totalGuestsStmt->execute();
        $totalGuestsRow = $totalGuestsStmt->get_result()->fetch_assoc();
        $currentTotalGuests = intval($totalGuestsRow['total_guests'] ?? 0);
        
        if (($currentTotalGuests + $numberOfGuests) > $dailyResortCap) {
            throw new Exception("The resort has reached its maximum global guest limit for {$checkInDate}. Only " . max(0, $dailyResortCap - $currentTotalGuests) . " spots remaining.");
        }
    }

    // ── Availability / overlap check ──────────────────────────────
    $activeStatuses = "(SELECT id FROM booking_status WHERE name IN ('pending_review','approved'))";

    if ($bookingType === 'accommodation') {
        // Each accommodation row = 1 physical unit.
        // Block if ANY active booking for this accommodation overlaps the requested date range.
        // Overlap condition: existing.check_in < requested.check_out
        //                AND existing.check_out > requested.check_in
        // For day-tour (no check-out), treat the stay as a single day (check_out = check_in + 1 day).
        $reqCheckIn  = $checkInDate;
        $reqCheckOut = (!empty($checkOutDate) && $checkOutDate !== $checkInDate)
            ? $checkOutDate
            : date('Y-m-d', strtotime($checkInDate . ' +1 day'));

        $avCheck = $conn->prepare("
            SELECT COUNT(*) AS cnt
            FROM bookings
            WHERE booking_type = 'accommodation'
              AND accommodation_id = ?
              AND status_id IN $activeStatuses
              AND check_in_date  < ?
              AND COALESCE(
                    NULLIF(check_out_date, ''),
                    DATE_ADD(check_in_date, INTERVAL 1 DAY)
                  ) > ?
        ");
        $avCheck->bind_param('iss', $itemId, $reqCheckOut, $reqCheckIn);
        $avCheck->execute();
        $avRow = $avCheck->get_result()->fetch_assoc();

        $dailyLimit = max(1, (int)($item['daily_booking_limit'] ?? 1));
        if ($avRow['cnt'] >= $dailyLimit) {
            throw new Exception(
                'This room/cottage is already fully booked for the selected dates. ' .
                'Please choose different dates or another accommodation.'
            );
        }
    } else {
        // Activities and bundles: simple same-day slot count vs. capacity.
        $itemColumn = $bookingType === 'activity' ? 'activity_id' : 'bundle_id';
        $avCheck = $conn->prepare("
            SELECT COUNT(*) AS cnt
            FROM bookings
            WHERE booking_type = ? AND $itemColumn = ? AND check_in_date = ?
              AND status_id IN $activeStatuses
        ");
        $avCheck->bind_param('sis', $bookingType, $itemId, $checkInDate);
        $avCheck->execute();
        $avRow = $avCheck->get_result()->fetch_assoc();

        $dailyLimit = $item['daily_booking_limit'] ?? $item['capacity'];
        if ($avRow['cnt'] >= $dailyLimit) {
            throw new Exception('This slot is fully booked for the selected date. Please choose another date.');
        }
    }

    // ── Validate extra rooms (duplicate + overlap per room) ───────────
    $extraRoomsRaw  = !empty($_POST['extra_rooms']) ? json_decode($_POST['extra_rooms'], true) : [];
    $extraRoomsData = is_array($extraRoomsRaw) ? $extraRoomsRaw : [];

    // Build a map: accommodation_id => [windows] — include primary booking if accommodation.
    $accommodationWindows = [];
    if ($bookingType === 'accommodation') {
        $primaryOut = (!empty($checkOutDate) && $checkOutDate !== $checkInDate)
            ? $checkOutDate
            : date('Y-m-d', strtotime($checkInDate . ' +1 day'));
        $accommodationWindows[$itemId][] = ['in' => $checkInDate, 'out' => $primaryOut];
    }
    foreach ($extraRoomsData as $er) {
        $erType = $er['package_type'] ?? '';
        $erId   = intval($er['package_id'] ?? 0);
        $erIn   = $er['check_in_date']  ?? '';
        $erOut  = $er['check_out_date'] ?? '';
        if ($erType !== 'accommodation' || !$erId || !$erIn) continue;
        $erOutNorm = (!empty($erOut) && $erOut !== $erIn)
            ? $erOut
            : date('Y-m-d', strtotime($erIn . ' +1 day'));
        $accommodationWindows[$erId][] = ['in' => $erIn, 'out' => $erOutNorm];
    }

    foreach ($accommodationWindows as $accId => $windows) {
        $accNameStmt = $conn->prepare("SELECT name, daily_booking_limit FROM accommodations WHERE id = ? LIMIT 1");
        $accNameStmt->bind_param('i', $accId);
        $accNameStmt->execute();
        $accRow = $accNameStmt->get_result()->fetch_assoc();
        $accLabel = $accRow['name'] ?? "Accommodation #$accId";
        $dailyLimit = max(1, (int)($accRow['daily_booking_limit'] ?? 1));

        // Single-unit rule: check against daily_booking_limit
        if (count($windows) > $dailyLimit) {
            throw new Exception(
                "\"$accLabel\" has a limit of $dailyLimit unit(s) and appears more times in your booking. " .
                "Please remove the duplicates before submitting."
            );
        }

        // Overlap check for extra rooms (primary was already checked above).
        if ($bookingType !== 'accommodation' || $accId !== $itemId) {
            $w = $windows[0];
            $erAvCheck = $conn->prepare("
                SELECT COUNT(*) AS cnt
                FROM bookings
                WHERE booking_type = 'accommodation'
                  AND accommodation_id = ?
                  AND status_id IN $activeStatuses
                  AND check_in_date < ?
                  AND COALESCE(
                        NULLIF(check_out_date, ''),
                        DATE_ADD(check_in_date, INTERVAL 1 DAY)
                      ) > ?
            ");
            $erAvCheck->bind_param('iss', $accId, $w['out'], $w['in']);
            $erAvCheck->execute();
            if ($erAvCheck->get_result()->fetch_assoc()['cnt'] >= $dailyLimit) {
                throw new Exception(
                    "\"$accLabel\" is already fully booked for the selected dates. " .
                    "Please choose different dates or another accommodation."
                );
            }
        }
    }
    // ─────────────────────────────────────────────────────────────────

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
    $specialReqs = $_POST['special_requests'] ?? '';
    if (!empty($addonItems)) {
        $addonDesc = "\n[Add-on Activities]: " . implode(', ', array_map(function($a) {
            $qtyStr = ($a['price_type'] ?? '') !== 'flat_rate' ? " (Qty: {$a['qty']})" : "";
            return $a['name'] . $qtyStr;
        }, $addonItems));
        $specialReqs = trim($specialReqs . $addonDesc);
    }

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
