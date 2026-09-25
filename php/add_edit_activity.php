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

$colCheck = $conn->query("SHOW COLUMNS FROM `activities` LIKE 'price_type'");
if ($colCheck && $colCheck->num_rows === 0) {
    $conn->query("ALTER TABLE `activities` ADD COLUMN `price_type` ENUM('per_head','per_hour','flat_rate') NOT NULL DEFAULT 'flat_rate' AFTER `price_per_hour`");
}

$limitCheck = $conn->query("SHOW COLUMNS FROM `activities` LIKE 'daily_booking_limit'");
if ($limitCheck && $limitCheck->num_rows === 0) {
    $conn->query("ALTER TABLE `activities` ADD COLUMN `daily_booking_limit` INT(11) NOT NULL DEFAULT 10 AFTER `capacity`");
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'add' || $action === 'edit') {
    $activityId = $_POST['activity_id'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $pricePerHour = floatval($_POST['price_per_hour'] ?? 0);
    $priceType = trim($_POST['price_type'] ?? 'flat_rate');
    $isActive = intval($_POST['is_active'] ?? 1);
    $minDuration = floatval($_POST['min_duration_hours'] ?? 1.0);
    $maxDuration = floatval($_POST['max_duration_hours'] ?? 4.0);
    $capacity = intval($_POST['capacity'] ?? 10);
    $dailyLimit = intval($_POST['daily_booking_limit'] ?? 10);

    $allowedPriceTypes = ['per_head', 'per_hour', 'flat_rate'];
    if (!in_array($priceType, $allowedPriceTypes, true)) {
        $priceType = 'flat_rate';
    }
    
    // Generate slug from name
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
    
    // Handle image upload
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = dirname(__DIR__) . '/assets/uploads/activities/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (in_array($fileExtension, $allowed, true)) {
            $fileName = uniqid() . '.' . $fileExtension;
            $uploadPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $imagePath = 'assets/uploads/activities/' . $fileName;
            }
        }
    }
    
    try {
        if ($action === 'add') {
            // Insert new activity
            $query = "INSERT INTO activities (name, slug, description, price_per_hour, price_type, min_duration_hours, max_duration_hours, capacity, daily_booking_limit, image_url, is_active) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('sssdsddiisi', $name, $slug, $description, $pricePerHour, $priceType, $minDuration, $maxDuration, $capacity, $dailyLimit, $imagePath, $isActive);
            $stmt->execute();
            
            $item_id = $conn->insert_id;
            if (!empty($_POST['slot_label']) && !empty($_POST['start_time']) && !empty($_POST['end_time'])) {
                $ts_stmt = $conn->prepare("INSERT INTO time_slots (activity_id, label, start_time, end_time, is_active) VALUES (?, ?, ?, ?, 1)");
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
            
            echo json_encode(['success' => true, 'message' => 'Activity added successfully']);
        } elseif ($action === 'edit') {
            // Update existing activity
            if (empty($activityId)) {
                echo json_encode(['success' => false, 'message' => 'Activity ID is required for editing']);
                exit();
            }
            
            if ($imagePath) {
                // Update with new image
                $query = "UPDATE activities SET name = ?, slug = ?, description = ?, price_per_hour = ?, price_type = ?, min_duration_hours = ?, max_duration_hours = ?, capacity = ?, daily_booking_limit = ?, image_url = ?, is_active = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('sssdsddiisii', $name, $slug, $description, $pricePerHour, $priceType, $minDuration, $maxDuration, $capacity, $dailyLimit, $imagePath, $isActive, $activityId);
            } else {
                // Update without changing image
                $query = "UPDATE activities SET name = ?, slug = ?, description = ?, price_per_hour = ?, price_type = ?, min_duration_hours = ?, max_duration_hours = ?, capacity = ?, daily_booking_limit = ?, is_active = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('sssdsddiiii', $name, $slug, $description, $pricePerHour, $priceType, $minDuration, $maxDuration, $capacity, $dailyLimit, $isActive, $activityId);
            }
            $stmt->execute();
            
            echo json_encode(['success' => true, 'message' => 'Activity updated successfully']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($action === 'delete') {
    $activityId = $_POST['activity_id'] ?? '';
    
    if (empty($activityId)) {
        echo json_encode(['success' => false, 'message' => 'Activity ID is required']);
        exit();
    }
    
    try {
        // Check if activity has bookings
        $checkQuery = "SELECT COUNT(*) as count
            FROM bookings b
            INNER JOIN booking_status bs ON b.status_id = bs.id
            WHERE b.activity_id = ?
              AND bs.name IN ('pending_review', 'approved')";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param('i', $activityId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();


        
        if ($row['count'] > 0) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete activity with existing bookings']);
            exit();
        }
        
        // Delete activity
        $query = "DELETE FROM activities WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $activityId);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Activity deleted successfully']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($action === 'toggle') {
    $activityId = $_POST['activity_id'] ?? '';
    $isActive = intval($_POST['is_active'] ?? 0);
    
    if (empty($activityId)) {
        echo json_encode(['success' => false, 'message' => 'Activity ID is required']);
        exit();
    }
    
    try {
        $query = "UPDATE activities SET is_active = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $isActive, $activityId);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Activity status updated successfully']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($action === 'get') {
    $activityId = $_GET['activity_id'] ?? '';
    
    if (empty($activityId)) {
        echo json_encode(['success' => false, 'message' => 'Activity ID is required']);
        exit();
    }
    
    try {
        $query = "SELECT * FROM activities WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $activityId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            echo json_encode(['success' => true, 'activity' => $row]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Activity not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
