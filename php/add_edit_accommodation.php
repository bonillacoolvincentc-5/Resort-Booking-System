<?php
session_start();

// Include database configuration
require_once '../phpAuthentication/db_config.php';

// Check if admin is logged in
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

header('Content-Type: application/json');

global $conn;

$limitCheck = $conn->query("SHOW COLUMNS FROM `accommodations` LIKE 'daily_booking_limit'");
if ($limitCheck && $limitCheck->num_rows === 0) {
    $conn->query("ALTER TABLE `accommodations` ADD COLUMN `daily_booking_limit` INT(11) NOT NULL DEFAULT 1 AFTER `capacity`");
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'add' || $action === 'edit') {
    $accId = $_POST['accommodation_id'] ?? '';
    $name = trim($_POST['name'] ?? '');
    
    // Type handling (dropdown: 'room' or 'cottage')
    $rawType = strtolower(trim($_POST['type'] ?? 'room'));
    $type = in_array($rawType, ['room', 'cottage']) ? $rawType : 'room';

    // Tour / Stay Type handling (checkboxes: 'overnight', 'day_tour', or both)
    $tourTypes = $_POST['tour_types'] ?? $_POST['tour_type'] ?? [];
    if (!is_array($tourTypes)) {
        $tourTypes = array_filter(array_map('trim', explode(',', (string)$tourTypes)));
    }
    $validTourTypes = array_values(array_intersect($tourTypes, ['overnight', 'day_tour']));
    if (empty($validTourTypes)) {
        $validTourTypes = ['overnight'];
    }
    $tourType = implode(',', $validTourTypes);

    $description = trim($_POST['description'] ?? '');
    $baseCapacity = max(1, intval($_POST['base_capacity'] ?? 1));
    $capacity = max($baseCapacity, intval($_POST['capacity'] ?? 2));
    $extraPersonFee = max(0, floatval($_POST['extra_person_fee'] ?? 0));
    $pricePerNight = floatval($_POST['price_per_night'] ?? 0);
    $pricePerDay = floatval($_POST['price_per_day'] ?? 0);
    $amenities = trim($_POST['amenities'] ?? '');
    $isActive = intval($_POST['is_active'] ?? 1);
    $dailyLimit = max(1, intval($_POST['daily_booking_limit'] ?? 1));
    
    // Handle image upload
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/uploads/accommodations/';
        
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            $imagePath = 'assets/uploads/accommodations/' . $fileName;
        }
    }
    
    try {
        if ($action === 'add') {
            $query = "INSERT INTO accommodations (name, type, tour_type, description, base_capacity, capacity, daily_booking_limit, extra_person_fee, price_per_night, price_per_day, amenities, image_url, is_active) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('ssssiiidddssi', $name, $type, $tourType, $description, $baseCapacity, $capacity, $dailyLimit, $extraPersonFee, $pricePerNight, $pricePerDay, $amenities, $imagePath, $isActive);
            $stmt->execute();
            
            $item_id = $conn->insert_id;
            if ((in_array('day_tour', $validTourTypes) || $type === 'cottage') && !empty($_POST['slot_label']) && !empty($_POST['start_time']) && !empty($_POST['end_time'])) {
                $ts_stmt = $conn->prepare("INSERT INTO time_slots (accommodation_id, label, start_time, end_time, is_active) VALUES (?, ?, ?, ?, 1)");
                foreach ($_POST['slot_label'] as $index => $label) {
                    $start = $_POST['start_time'][$index];
                    $end = $_POST['end_time'][$index];
                    if (!empty($start) && !empty($end)) {
                        $ts_stmt->bind_param("isss", $item_id, $label, $start, $end);
                        $ts_stmt->execute();
                    }
                }
                $ts_stmt->close();
            }
            
            echo json_encode(['success' => true, 'message' => 'Amenity / Accommodation added successfully']);
        } elseif ($action === 'edit') {
            if (empty($accId)) {
                echo json_encode(['success' => false, 'message' => 'Accommodation ID is required for editing']);
                exit();
            }
            
            if ($imagePath) {
                $query = "UPDATE accommodations SET name = ?, type = ?, tour_type = ?, description = ?, base_capacity = ?, capacity = ?, daily_booking_limit = ?, extra_person_fee = ?, price_per_night = ?, price_per_day = ?, amenities = ?, image_url = ?, is_active = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('ssssiiidddssii', $name, $type, $tourType, $description, $baseCapacity, $capacity, $dailyLimit, $extraPersonFee, $pricePerNight, $pricePerDay, $amenities, $imagePath, $isActive, $accId);
            } else {
                $query = "UPDATE accommodations SET name = ?, type = ?, tour_type = ?, description = ?, base_capacity = ?, capacity = ?, daily_booking_limit = ?, extra_person_fee = ?, price_per_night = ?, price_per_day = ?, amenities = ?, is_active = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('ssssiiidddsii', $name, $type, $tourType, $description, $baseCapacity, $capacity, $dailyLimit, $extraPersonFee, $pricePerNight, $pricePerDay, $amenities, $isActive, $accId);
            }
            $stmt->execute();
            
            echo json_encode(['success' => true, 'message' => 'Amenity / Accommodation updated successfully']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($action === 'delete') {
    $accId = $_POST['accommodation_id'] ?? '';
    
    if (empty($accId)) {
        echo json_encode(['success' => false, 'message' => 'Accommodation ID is required']);
        exit();
    }
    
    try {
        $checkQuery = "SELECT COUNT(*) as count FROM bookings WHERE accommodation_id = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param('i', $accId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete amenity/accommodation with existing bookings. You can disable it instead.']);
            exit();
        }
        
        $query = "DELETE FROM accommodations WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $accId);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Amenity / Accommodation deleted successfully']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($action === 'toggle') {
    $accId = $_POST['accommodation_id'] ?? '';
    $isActive = intval($_POST['is_active'] ?? 0);
    
    if (empty($accId)) {
        echo json_encode(['success' => false, 'message' => 'Accommodation ID is required']);
        exit();
    }
    
    try {
        $query = "UPDATE accommodations SET is_active = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $isActive, $accId);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($action === 'get') {
    $accId = $_GET['accommodation_id'] ?? '';
    
    if (empty($accId)) {
        echo json_encode(['success' => false, 'message' => 'Accommodation ID is required']);
        exit();
    }
    
    try {
        $query = "SELECT * FROM accommodations WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $accId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            echo json_encode(['success' => true, 'accommodation' => $row]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Amenity / Accommodation not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
