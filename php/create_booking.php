<?php
/**
 * Create Booking Process
 * Handles booking creation for activities and accommodations
 */

session_start();
require_once '../phpAuthentication/db_config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to create a booking'
    ]);
    exit();
}

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception('Invalid request data');
    }
    
    // Ensure phone_number fallback if empty
    if (empty($data['phone_number'])) {
        $data['phone_number'] = $_SESSION['user_phone'] ?? '';
        if (empty($data['phone_number']) && isset($_SESSION['user_id'])) {
            $stmtP = $conn->prepare("SELECT phone_number FROM users WHERE id = ?");
            $stmtP->bind_param('i', $_SESSION['user_id']);
            $stmtP->execute();
            $rowP = $stmtP->get_result()->fetch_assoc();
            $data['phone_number'] = $rowP['phone_number'] ?? '';
        }
        if (empty($data['phone_number'])) {
            $data['phone_number'] = '';
        }
    }

    // Validate required fields
    $required_fields = ['booking_type', 'full_name', 'email', 'check_in_date', 'number_of_guests'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            throw new Exception("Field '$field' is required");
        }
    }
    
    // Validate booking type
    if (!in_array($data['booking_type'], ['activity', 'accommodation', 'bundle'])) {
        throw new Exception('Invalid booking type');
    }

    // Reject bookings on admin-blocked dates
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
    $blockedStmt = $conn->prepare("SELECT id FROM blocked_dates WHERE blocked_date = ? LIMIT 1");
    $blockedStmt->bind_param('s', $data['check_in_date']);
    $blockedStmt->execute();
    if ($blockedStmt->get_result()->num_rows > 0) {
        throw new Exception('This date is not available for booking. Please choose another date.');
    }
    
    // Get item ID based on booking type
    $item_id = null;
    $price = 0;
    
    if ($data['booking_type'] === 'activity') {
        if (empty($data['activity_id'])) {
            throw new Exception('Activity ID is required for activity bookings');
        }
        $item_id = $data['activity_id'];
        
        // Get activity price & details
        $stmt = $conn->prepare("SELECT price_per_hour, price_type, min_duration_hours, max_duration_hours FROM activities WHERE id = ? AND is_active = 1");
        $stmt->bind_param('i', $item_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $activity = $result->fetch_assoc();
        
        if (!$activity) {
            throw new Exception('Activity not found or inactive');
        }
        
        // Calculate duration and price
        if (empty($data['check_in_time']) || empty($data['check_out_time'])) {
            throw new Exception('Time-in and Time-out are required for activity bookings');
        }
        
        $timeIn = strtotime($data['check_in_time']);
        $timeOut = strtotime($data['check_out_time']);
        $durationHours = ($timeOut - $timeIn) / 3600;
        
        if ($durationHours < $activity['min_duration_hours'] || $durationHours > $activity['max_duration_hours']) {
            throw new Exception("Duration must be between {$activity['min_duration_hours']} and {$activity['max_duration_hours']} hours");
        }
        
        $pType = $activity['price_type'] ?? 'flat_rate';
        if ($pType === 'per_head') {
            $price = floatval($activity['price_per_hour']) * intval($data['number_of_guests']);
        } elseif ($pType === 'per_hour') {
            $price = floatval($activity['price_per_hour']) * $durationHours;
        } else {
            $price = floatval($activity['price_per_hour']);
        }
        
    } else if ($data['booking_type'] === 'accommodation') {
        if (empty($data['accommodation_id'])) {
            throw new Exception('Accommodation ID is required for accommodation bookings');
        }
        $item_id = $data['accommodation_id'];
        
        // Get accommodation price & capacities
        $stmt = $conn->prepare("SELECT price_per_night, price_per_day, capacity, base_capacity, extra_person_fee FROM accommodations WHERE id = ? AND is_active = 1");
        $stmt->bind_param('i', $item_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $accommodation = $result->fetch_assoc();
        
        if (!$accommodation) {
            throw new Exception('Accommodation not found or inactive');
        }
        
        // Validate guest count
        if ($data['number_of_guests'] > $accommodation['capacity']) {
            throw new Exception("Accommodation capacity is {$accommodation['capacity']} guests");
        }
        
        $tourType = strtolower(trim($data['tour_type'] ?? ''));
        $checkOutVal = !empty($data['check_out_date']) ? $data['check_out_date'] : null;
        $isDayTour = ($tourType === 'daytour' || empty($checkOutVal) || $checkOutVal === $data['check_in_date']);

        $baseCap = !empty($accommodation['base_capacity']) ? intval($accommodation['base_capacity']) : intval($accommodation['capacity']);
        $extraFee = floatval($accommodation['extra_person_fee'] ?? 0);
        $numGuests = intval($data['number_of_guests']);

        if ($isDayTour) {
            $numberOfDays = 1;
            $dayRate = (!empty($accommodation['price_per_day']) && floatval($accommodation['price_per_day']) > 0)
                ? floatval($accommodation['price_per_day'])
                : floatval($accommodation['price_per_night']);
            $price = $dayRate;
            if ($numGuests > $baseCap && $extraFee > 0) {
                $price += ($numGuests - $baseCap) * $extraFee;
            }
        } else {
            // Overnight
            if (empty($checkOutVal)) {
                throw new Exception('Check-out date is required for overnight accommodation bookings');
            }
            $checkIn = new DateTime($data['check_in_date']);
            $checkOut = new DateTime($checkOutVal);
            $interval = $checkIn->diff($checkOut);
            $numberOfDays = max(1, $interval->days);
            
            $nightRate = floatval($accommodation['price_per_night']);
            $price = $nightRate * $numberOfDays;
            if ($numGuests > $baseCap && $extraFee > 0) {
                $price += ($numGuests - $baseCap) * $extraFee * $numberOfDays;
            }
        }
    } else {
        // bundle
        if (empty($data['bundle_id'])) {
            throw new Exception('Bundle ID is required for bundle bookings');
        }
        $item_id = $data['bundle_id'];
        
        // Get bundle price and fixed duration
        $stmt = $conn->prepare("SELECT price, capacity, start_time, duration_hours FROM bundles WHERE id = ? AND is_active = 1");
        $stmt->bind_param('i', $item_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $bundle = $result->fetch_assoc();
        
        if (!$bundle) {
            throw new Exception('Bundle not found or inactive');
        }
        
        // Validate guest count
        if ($data['number_of_guests'] > $bundle['capacity']) {
            throw new Exception("Bundle capacity is {$bundle['capacity']} guests");
        }

        $startTime = $bundle['start_time'] ?: '00:00:00';
        $durationHours = intval($bundle['duration_hours'] ?? 0);
        if ($durationHours < 1) $durationHours = 24;

        $checkInDT = new DateTime($data['check_in_date'] . ' ' . $startTime);
        $checkOutDT = clone $checkInDT;
        $checkOutDT->modify('+' . $durationHours . ' hours');

        $data['check_in_time']  = $checkInDT->format('H:i:s');
        $data['check_out_date'] = $checkOutDT->format('Y-m-d');
        $data['check_out_time'] = $checkOutDT->format('H:i:s');
        $numberOfDays = max(1, (int) $checkInDT->diff($checkOutDT)->days);
        $data['number_of_days'] = $numberOfDays;

        $price = $bundle['price']; // Flat price for the bundle package
    }
    
    // Check availability
    $availabilityCheck = checkAvailability($conn, $data['booking_type'], $item_id, $data['check_in_date'], $data['check_in_time'] ?? null, $data['check_out_time'] ?? null);
    
    if (!$availabilityCheck['available']) {
        throw new Exception($availabilityCheck['message']);
    }
    
    // Generate booking reference
    $bookingReference = 'BK' . date('Ymd') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    
    // Get pending status ID
    $stmt = $conn->prepare("SELECT id FROM booking_status WHERE name = 'pending_review'");
    $stmt->execute();
    $result = $stmt->get_result();
    $status = $result->fetch_assoc();
    $statusId = $status['id'];
    
    // Insert booking
    $itemColumn = $data['booking_type'] === 'activity' ? 'activity_id' : ($data['booking_type'] === 'accommodation' ? 'accommodation_id' : 'bundle_id');
    $stmt = $conn->prepare("
        INSERT INTO bookings (
            booking_reference, user_id, booking_type, $itemColumn,
            status_id, full_name, email, phone_number,
            check_in_date, check_in_time, check_out_date, check_out_time,
            number_of_days, number_of_guests, special_requests,
            total_amount, payment_status, payment_method
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $checkInTime = $data['check_in_time'] ?? null;
    $checkOutDate = $data['check_out_date'] ?? null;
    $checkOutTime = $data['check_out_time'] ?? null;
    $numberOfDays = $data['number_of_days'] ?? 1;
    $specialRequests = $data['special_requests'] ?? null;
    $payStatus = 'pending';
    $payMethod = 'gcash';

    $stmt->bind_param(
        'sisiisssssssiisdss',
        $bookingReference,
        $_SESSION['user_id'],
        $data['booking_type'],
        $item_id,
        $statusId,
        $data['full_name'],
        $data['email'],
        $data['phone_number'],
        $data['check_in_date'],
        $checkInTime,
        $checkOutDate,
        $checkOutTime,
        $numberOfDays,
        $data['number_of_guests'],
        $specialRequests,
        $price,
        $payStatus,
        $payMethod
    );
    
    $stmt->execute();
    $bookingId = $conn->insert_id;
    
    // Log booking creation
    logBookingChange($conn, $bookingId, null, $statusId, 'user', $_SESSION['user_id'], 'Booking created');
    
    // Update availability
    updateAvailability($conn, $data['booking_type'], $item_id, $data['check_in_date'], $data['check_in_time'] ?? null, $data['check_out_time'] ?? null);
    
    if ($data['booking_type'] === 'accommodation' && !empty($checkOutDate)) {
        incrementRoomBookingCounts($conn, $item_id, $data['check_in_date'], $checkOutDate);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Booking created successfully',
        'booking_id' => $bookingId,
        'booking_reference' => $bookingReference,
        'total_amount' => $price,
        'redirect' => 'my-bookings.php'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Check availability for booking
 */
function checkAvailability($conn, $type, $itemId, $date, $timeIn = null, $timeOut = null) {
    $itemColumn = $type === 'activity' ? 'activity_id' : ($type === 'accommodation' ? 'accommodation_id' : 'bundle_id');
    $query = "
        SELECT COUNT(*) as count
        FROM bookings b
        WHERE b.booking_type = ?
        AND b.$itemColumn = ?
        AND b.check_in_date = ?
        AND b.status_id IN (SELECT id FROM booking_status WHERE name IN ('pending_review', 'approved'))
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sis', $type, $itemId, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $countResult = $result->fetch_assoc();
    
    // Get capacity
    if ($type === 'activity') {
        $stmt = $conn->prepare("SELECT capacity FROM activities WHERE id = ?");
        $stmt->bind_param('i', $itemId);
    } else if ($type === 'accommodation') {
        $stmt = $conn->prepare("SELECT capacity FROM accommodations WHERE id = ?");
        $stmt->bind_param('i', $itemId);
    } else {
        $stmt = $conn->prepare("SELECT capacity FROM bundles WHERE id = ?");
        $stmt->bind_param('i', $itemId);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    
    if ($countResult['count'] >= $item['capacity']) {
        return [
            'available' => false,
            'message' => 'This slot is fully booked'
        ];
    }
    
    return ['available' => true];
}

/**
 * Update availability after booking
 */
function updateAvailability($conn, $type, $itemId, $date, $timeIn = null, $timeOut = null) {
    // Check if availability record exists
    $query = "
        SELECT id, available_slots FROM availability
        WHERE item_type = ? AND item_id = ? AND date = ?
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sis', $type, $itemId, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $availability = $result->fetch_assoc();
    
    if ($availability) {
        // Update existing record
        $newSlots = max(0, $availability['available_slots'] - 1);
        $isAvailable = $newSlots > 0 ? 1 : 0;
        
        $stmt = $conn->prepare("
            UPDATE availability
            SET available_slots = ?, is_available = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param('iii', $newSlots, $isAvailable, $availability['id']);
        $stmt->execute();
    } else {
        // Create new availability record
        if ($type === 'activity') {
            $stmt = $conn->prepare("SELECT capacity FROM activities WHERE id = ?");
            $stmt->bind_param('i', $itemId);
        } else if ($type === 'accommodation') {
            $stmt = $conn->prepare("SELECT capacity FROM accommodations WHERE id = ?");
            $stmt->bind_param('i', $itemId);
        } else {
            $stmt = $conn->prepare("SELECT capacity FROM bundles WHERE id = ?");
            $stmt->bind_param('i', $itemId);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();
        
        $totalSlots = $item['capacity'];
        $availableSlots = $totalSlots - 1;
        $isAvailable = $availableSlots > 0 ? 1 : 0;
        
        $stmt = $conn->prepare("
            INSERT INTO availability (item_type, item_id, date, time_slot, available_slots, total_slots, is_available)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('sisiiii', $type, $itemId, $date, $timeIn, $availableSlots, $totalSlots, $isAvailable);
        $stmt->execute();
    }
}

/**
 * Log booking status change
 */
function logBookingChange($conn, $bookingId, $oldStatusId, $newStatusId, $changedBy, $changedById, $notes = null) {
    $stmt = $conn->prepare("
        INSERT INTO booking_logs (booking_id, old_status_id, new_status_id, changed_by, changed_by_id, notes)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('iiisis', $bookingId, $oldStatusId, $newStatusId, $changedBy, $changedById, $notes);
    $stmt->execute();
}

function incrementRoomBookingCounts($conn, $room_id, $check_in_date, $check_out_date) {
    $begin = new DateTime($check_in_date);
    $end = new DateTime($check_out_date);
    $interval = new DateInterval('P1D');
    
    $datePeriod = new DatePeriod($begin, $interval, $end);
    
    $stmt = $conn->prepare("
        INSERT INTO room_inventory (date, room_id, booked_count) 
        VALUES (?, ?, 1) 
        ON DUPLICATE KEY UPDATE booked_count = booked_count + 1
    ");
    
    foreach ($datePeriod as $date) {
        $formattedDate = $date->format('Y-m-d');
        $stmt->bind_param("si", $formattedDate, $room_id);
        $stmt->execute();
    }
    
    $stmt->close();
}
?>
