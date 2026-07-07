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

$action = $_POST['action'] ?? '';

if ($action === 'add' || $action === 'edit') {
    $activityId = $_POST['activity_id'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $pricePerHour = floatval($_POST['price_per_hour'] ?? 0);
    $isActive = intval($_POST['is_active'] ?? 1);
    $minDuration = floatval($_POST['min_duration_hours'] ?? 1.0);
    $maxDuration = floatval($_POST['max_duration_hours'] ?? 4.0);
    $capacity = intval($_POST['capacity'] ?? 10);
    
    // Generate slug from name
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
    
    // Handle image upload
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/uploads/activities/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            $imagePath = 'assets/uploads/activities/' . $fileName;
        }
    }
    
    try {
        if ($action === 'add') {
            // Insert new activity
            $query = "INSERT INTO activities (name, slug, description, price_per_hour, min_duration_hours, max_duration_hours, capacity, image_url, is_active) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            // s,s,s = name/slug/desc; d,d,d = price/minDur/maxDur; i = capacity; s = image; i = is_active
            $stmt->bind_param('sssdddisi', $name, $slug, $description, $pricePerHour, $minDuration, $maxDuration, $capacity, $imagePath, $isActive);
            $stmt->execute();
            
            echo json_encode(['success' => true, 'message' => 'Activity added successfully']);
        } elseif ($action === 'edit') {
            // Update existing activity
            if (empty($activityId)) {
                echo json_encode(['success' => false, 'message' => 'Activity ID is required for editing']);
                exit();
            }
            
            if ($imagePath) {
                // Update with new image
                $query = "UPDATE activities SET name = ?, slug = ?, description = ?, price_per_hour = ?, min_duration_hours = ?, max_duration_hours = ?, capacity = ?, image_url = ?, is_active = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                // s,s,s,d,d,d,i,s,i,i = name/slug/desc, price/min/max, capacity, image, isActive, id
                $stmt->bind_param('sssdddisii', $name, $slug, $description, $pricePerHour, $minDuration, $maxDuration, $capacity, $imagePath, $isActive, $activityId);
            } else {
                // Update without changing image
                $query = "UPDATE activities SET name = ?, slug = ?, description = ?, price_per_hour = ?, min_duration_hours = ?, max_duration_hours = ?, capacity = ?, is_active = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                // s,s,s,d,d,d,i,i,i = name/slug/desc, price/min/max, capacity, isActive, id
                $stmt->bind_param('sssdddiii', $name, $slug, $description, $pricePerHour, $minDuration, $maxDuration, $capacity, $isActive, $activityId);
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
        $checkQuery = "SELECT COUNT(*) as count FROM bookings WHERE activity_id = ?";
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
