<?php
/**
 * Admin Create Booking Process
 * Handles booking creation on behalf of a user from the Admin Dashboard
 */

session_start();
require_once '../phpAuthentication/db_config.php';

header('Content-Type: application/json');

// Guard: Check if admin is logged in
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized: Only admins can perform this action'
    ]);
    exit();
}

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        throw new Exception('Invalid request data');
    }
    
    // Validate required fields
    $required_fields = ['user_id', 'booking_type', 'item_id', 'check_in_date', 'number_of_guests', 'status_id', 'payment_status', 'payment_method'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            throw new Exception("Field '$field' is required");
        }
    }
    
    // Validate booking type
    if (!in_array($data['booking_type'], ['activity', 'accommodation', 'bundle'])) {
        throw new Exception('Invalid booking type');
    }
    
    $user_id = intval($data['user_id']);
    $item_id = intval($data['item_id']);
    $status_id = intval($data['status_id']);
    
    // Retrieve user details
    $stmt = $conn->prepare("SELECT first_name, middle_name, last_name, email, phone_number FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if (!$user) {
        throw new Exception('Customer not found');
    }
    
    $full_name = trim($user['first_name'] . ' ' . ($user['middle_name'] ? $user['middle_name'] . ' ' : '') . $user['last_name']);
    $email = $user['email'];
    $phone_number = $user['phone_number'];
    
    $price = 0;
    
    if ($data['booking_type'] === 'activity') {
        // Get activity price and capacity
        $stmt = $conn->prepare("SELECT price_per_hour, min_duration_hours, max_duration_hours, capacity FROM activities WHERE id = ? AND is_active = 1");
        $stmt->bind_param('i', $item_id);
        $stmt->execute();
        $activity = $stmt->get_result()->fetch_assoc();
        
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
        
        $price = $activity['price_per_hour'] * $durationHours;
        
    } else if ($data['booking_type'] === 'accommodation') {
        // Get accommodation price and capacity
        $stmt = $conn->prepare("SELECT price_per_night, capacity FROM accommodations WHERE id = ? AND is_active = 1");
        $stmt->bind_param('i', $item_id);
        $stmt->execute();
        $accommodation = $stmt->get_result()->fetch_assoc();
        
        if (!$accommodation) {
            throw new Exception('Accommodation not found or inactive');
        }
        
        // Validate guest count
        if ($data['number_of_guests'] > $accommodation['capacity']) {
            throw new Exception("Accommodation capacity is {$accommodation['capacity']} guests");
        }
        
        // Calculate number of days and price
        if (empty($data['check_out_date'])) {
            throw new Exception('Check-out date is required for accommodation bookings');
        }
        
        $checkIn = new DateTime($data['check_in_date']);
        $checkOut = new DateTime($data['check_out_date']);
        $interval = $checkIn->diff($checkOut);
        $numberOfDays = $interval->days;
        
        if ($numberOfDays < 1) {
            throw new Exception('Minimum stay is 1 day');
        }
        
        $price = $accommodation['price_per_night'] * $numberOfDays;
    } else {
        // bundle
        $stmt = $conn->prepare("SELECT price, capacity FROM bundles WHERE id = ? AND is_active = 1");
        $stmt->bind_param('i', $item_id);
        $stmt->execute();
        $bundle = $stmt->get_result()->fetch_assoc();
        
        if (!$bundle) {
            throw new Exception('Bundle not found or inactive');
        }
        
        // Validate guest count
        if ($data['number_of_guests'] > $bundle['capacity']) {
            throw new Exception("Bundle capacity is {$bundle['capacity']} guests");
        }
        
        // Calculate number of days
        if (empty($data['check_out_date'])) {
            throw new Exception('Check-out date is required for bundle bookings');
        }
        
        $checkIn = new DateTime($data['check_in_date']);
        $checkOut = new DateTime($data['check_out_date']);
        $interval = $checkIn->diff($checkOut);
        $numberOfDays = $interval->days;
        
        if ($numberOfDays < 1) {
            throw new Exception('Minimum stay is 1 day');
        }
        
        $price = $bundle['price']; // Flat price
    }
    
    // Check availability
    $availabilityCheck = checkAvailability($conn, $data['booking_type'], $item_id, $data['check_in_date'], $data['check_in_time'] ?? null, $data['check_out_time'] ?? null);
    if (!$availabilityCheck['available']) {
        throw new Exception($availabilityCheck['message']);
    }
    
    // Generate booking reference
    $bookingReference = 'BK' . date('Ymd') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    
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
    $numberOfDays = $numberOfDays ?? null;
    $specialRequests = $data['special_requests'] ?? null;
    
    $stmt->bind_param(
        // FIX #11: corrected type string — was 'sissssssssisisdss' (17 chars for 18 vars).
        // Correct mapping:
        //  s  $bookingReference   i  $user_id         s  $data['booking_type']
        //  i  $item_id            i  $status_id       s  $full_name
        //  s  $email              s  $phone_number    s  $data['check_in_date']
        //  s  $checkInTime        s  $checkOutDate    s  $checkOutTime
        //  i  $numberOfDays       s  $data['number_of_guests']
        //  s  $specialRequests    d  $price
        //  s  $data['payment_status']   s  $data['payment_method']
        'sisiisssssssissdss',
        $bookingReference,
        $user_id,
        $data['booking_type'],
        $item_id,
        $status_id,
        $full_name,
        $email,
        $phone_number,
        $data['check_in_date'],
        $checkInTime,
        $checkOutDate,
        $checkOutTime,
        $numberOfDays,
        $data['number_of_guests'],
        $specialRequests,
        $price,
        $data['payment_status'],
        $data['payment_method']
    );
    
    $stmt->execute();
    $bookingId = $conn->insert_id;
    
    // Log booking creation by admin
    logBookingChange($conn, $bookingId, null, $status_id, 'admin', $_SESSION['admin_id'], 'Booking created by admin');
    
    // Update availability
    updateAvailability($conn, $data['booking_type'], $item_id, $data['check_in_date'], $data['check_in_time'] ?? null, $data['check_out_time'] ?? null);
    
    echo json_encode([
        'success' => true,
        'message' => 'Booking created successfully by admin',
        'booking_id' => $bookingId,
        'booking_reference' => $bookingReference
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Check availability
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
 * Update availability
 */
function updateAvailability($conn, $type, $itemId, $date, $timeIn = null, $timeOut = null) {
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
 * Log booking change
 */
function logBookingChange($conn, $bookingId, $oldStatusId, $newStatusId, $changedBy, $changedById, $notes = null) {
    $stmt = $conn->prepare("
        INSERT INTO booking_logs (booking_id, old_status_id, new_status_id, changed_by, changed_by_id, notes)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('iiisis', $bookingId, $oldStatusId, $newStatusId, $changedBy, $changedById, $notes);
    $stmt->execute();
}
?>
