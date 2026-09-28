<?php
/**
 * Upload Payment Proof & Create Booking
 * Handles multipart form data with proof-of-payment image upload
 */

session_start();
require_once '../phpAuthentication/db_config.php';
require_once __DIR__ . '/check_expired_bookings.php';

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
    $hasATVShort = false;
    $hasATVLong = false;
    foreach ($addonItems as $ai) {
        $aPrice = floatval($ai['price'] ?? 0);
        $aQty = max(1, intval($ai['qty'] ?? 1));
        $pType = $ai['price_type'] ?? 'flat_rate';
        $aNameLower = strtolower($ai['name'] ?? '');
        
        if (strpos($aNameLower, 'atv short ride') !== false) {
            $hasATVShort = true;
        } elseif (strpos($aNameLower, 'atv long ride') !== false) {
            $hasATVLong = true;
        }
        
        if ($pType === 'flat_rate') {
            $price += $aPrice;
        } else {
            $price += ($aPrice * $aQty);
        }
    }
    
    if ($hasATVShort) {
        $price += 1500;
        $addonItems[] = [
            'name' => 'Required Tour Guide (ATV Short Ride)',
            'qty' => 1,
            'price_type' => 'flat_rate'
        ];
    } elseif ($hasATVLong) {
        $price += 30000;
        $addonItems[] = [
            'name' => 'Required Tour Guide (ATV Long Ride)',
            'qty' => 1,
            'price_type' => 'flat_rate'
        ];
    }

    // ── Handle payment proof upload (Optional for initial booking request) ──
    $proofPath = null;
    if (!empty($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/uploads/payment_proofs/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Server-side MIME detection (safer for free hosts)
        $detectedMime = '';
        if (function_exists('finfo_open')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $detectedMime = $finfo->file($_FILES['payment_proof']['tmp_name']);
        } elseif (function_exists('mime_content_type')) {
            $detectedMime = mime_content_type($_FILES['payment_proof']['tmp_name']);
        } else {
            $imgInfo = getimagesize($_FILES['payment_proof']['tmp_name']);
            if ($imgInfo !== false) {
                $detectedMime = $imgInfo['mime'];
            }
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($detectedMime, $allowedTypes, true)) {
            throw new Exception('Invalid file type. Only JPG, PNG, and WEBP images are accepted.');
        }

        $maxSize = 2097152; // 2 MiB
        if ($_FILES['payment_proof']['size'] > $maxSize) {
            throw new Exception('File too large. Maximum allowed size is 2 MB.');
        }

        // Derive extension from the detected MIME type (safe — not from client filename)
        $mimeExtMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $ext = $mimeExtMap[$detectedMime] ?? 'jpg';
        $filename = 'proof_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
        $fullPath = $uploadDir . $filename;

        if (!move_uploaded_file($_FILES['payment_proof']['tmp_name'], $fullPath)) {
            throw new Exception('Failed to save payment proof. Please try again.');
        }

        $proofPath = 'assets/uploads/payment_proofs/' . $filename;
    }


    // ── Availability / overlap check ──────────────────────────────
    $activeStatuses = "(SELECT id FROM booking_status WHERE name IN ('awaiting_payment','payment_verifying','approved','checked_in'))";

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

    $itemColumn = 'accommodation_id';
    if ($bookingType === 'activity') $itemColumn = 'activity_id';
    if ($bookingType === 'bundle') $itemColumn = 'bundle_id';

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
    
    // ── Append Extra Rooms to Special Requests & Add Price ──
    $parsed_extra_rooms = [];
    if (!empty($extraRoomsData)) {
        $extraRoomsDesc = [];
        foreach ($extraRoomsData as $er) {
            $erType = $er['package_type'] ?? '';
            $erId   = intval($er['package_id'] ?? 0);
            $erGuests = intval($er['guests'] ?? 1);
            $erIn   = $er['check_in_date']  ?? '';
            $erOut  = $er['check_out_date'] ?? '';
            
            if ($erType !== 'accommodation' || !$erId || !$erIn) continue;
            
            $erOutNorm = (!empty($erOut) && $erOut !== $erIn)
                ? $erOut
                : date('Y-m-d', strtotime($erIn . ' +1 day'));
            $erDays = max(1, round((strtotime($erOutNorm) - strtotime($erIn)) / 86400000));
            
            // Fetch extra room price & details
            $erStmt = $conn->prepare("SELECT name, price_per_night, price_per_day, base_capacity, extra_person_fee FROM accommodations WHERE id = ? LIMIT 1");
            $erStmt->bind_param('i', $erId);
            $erStmt->execute();
            $erRow = $erStmt->get_result()->fetch_assoc();
            
            if ($erRow) {
                // Calculate extra room price
                $erBaseCap = !empty($erRow['base_capacity']) ? intval($erRow['base_capacity']) : 1;
                $erExtraFee = floatval($erRow['extra_person_fee'] ?? 0);
                
                $tourType = strtolower(trim($_POST['tour_type'] ?? ''));
                $isDayTour = ($tourType === 'daytour' || empty($erOut) || $erOut === $erIn);
                
                $erPrice = 0;
                if ($isDayTour) {
                    $dayRate = (!empty($erRow['price_per_day']) && floatval($erRow['price_per_day']) > 0)
                        ? floatval($erRow['price_per_day'])
                        : floatval($erRow['price_per_night']);
                    $erPrice = $dayRate;
                    if ($erGuests > $erBaseCap && $erExtraFee > 0) {
                        $erPrice += ($erGuests - $erBaseCap) * $erExtraFee;
                    }
                } else {
                    $nightRate = floatval($erRow['price_per_night']);
                    $erPrice = $nightRate * $erDays;
                    if ($erGuests > $erBaseCap && $erExtraFee > 0) {
                        $erPrice += ($erGuests - $erBaseCap) * $erExtraFee * $erDays;
                    }
                }
                
                $price += $erPrice; // Add to global total
                
                // Add to description
                if ($isDayTour) {
                    $extraRoomsDesc[] = "{$erRow['name']} (Guests: {$erGuests}, Date: {$erIn}, Price: ₱" . number_format($erPrice, 2) . ")";
                } else {
                    $extraRoomsDesc[] = "{$erRow['name']} (Guests: {$erGuests}, Dates: {$erIn} to {$erOutNorm}, Price: ₱" . number_format($erPrice, 2) . ")";
                }
                
                // Save for database insertion later
                $parsed_extra_rooms[] = [
                    'accommodation_id' => $erId,
                    'num_guests' => $erGuests,
                    'check_in_date' => $erIn,
                    'check_out_date' => $isDayTour ? null : $erOutNorm,
                    'price' => $erPrice
                ];
            }
        }
        
        if (!empty($extraRoomsDesc)) {
            $specialReqs = trim($specialReqs . "\n\n[Additional Rooms]:\n- " . implode("\n- ", $extraRoomsDesc));
        }
    }

    if (!empty($addonItems)) {
        $addonDesc = "\n\n[Add-on Activities]: " . implode(', ', array_map(function($a) {
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

    // ── Insert Extra Rooms to Database ──────────────────────────────────────────
    if (!empty($parsed_extra_rooms)) {
        $erIns = $conn->prepare("INSERT INTO booking_extra_rooms (booking_id, accommodation_id, num_guests, check_in_date, check_out_date, price) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($parsed_extra_rooms as $per) {
            $erIns->bind_param(
                'iiissd',
                $bookingId,
                $per['accommodation_id'],
                $per['num_guests'],
                $per['check_in_date'],
                $per['check_out_date'],
                $per['price']
            );
            $erIns->execute();
        }
        $erIns->close();
    }

    // ── Log creation ──────────────────────────────────────────────
    $log = $conn->prepare("
        INSERT INTO booking_logs (booking_id, old_status_id, new_status_id, changed_by, changed_by_id, notes)
        VALUES (?, NULL, ?, 'user', ?, 'Booking request submitted (pending admin review)')
    ");
    $log->bind_param('iii', $bookingId, $statusId, $userId);
    $log->execute();



    // Availability is reserved/deducted upon Admin Approval into 'awaiting_payment'

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
