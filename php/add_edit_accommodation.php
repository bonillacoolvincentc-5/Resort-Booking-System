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

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'add' || $action === 'edit') {
    $accId = $_POST['accommodation_id'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $type = trim($_POST['type'] ?? 'room');
    $description = trim($_POST['description'] ?? '');
    $pricePerNight = floatval($_POST['price_per_night'] ?? 0);
    $pricePerDay = floatval($_POST['price_per_day'] ?? 0);
    $capacity = intval($_POST['capacity'] ?? 2);
    $amenities = trim($_POST['amenities'] ?? '');
    $isActive = intval($_POST['is_active'] ?? 1);
    
    // Ensure valid enum type
    if (!in_array($type, ['room', 'cottage', 'stay-in'])) {
        $type = 'room';
    }
    
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
            $query = "INSERT INTO accommodations (name, type, description, capacity, price_per_night, price_per_day, amenities, image_url, is_active) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            // s = name, s = type, s = desc, i = cap, d = night, d = day, s = amen, s = img, i = active
            $stmt->bind_param('sssiddssi', $name, $type, $description, $capacity, $pricePerNight, $pricePerDay, $amenities, $imagePath, $isActive);
            $stmt->execute();
            
            echo json_encode(['success' => true, 'message' => 'Amenity / Accommodation added successfully']);
        } elseif ($action === 'edit') {
            if (empty($accId)) {
                echo json_encode(['success' => false, 'message' => 'Accommodation ID is required for editing']);
                exit();
            }
            
            if ($imagePath) {
                $query = "UPDATE accommodations SET name = ?, type = ?, description = ?, capacity = ?, price_per_night = ?, price_per_day = ?, amenities = ?, image_url = ?, is_active = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('sssiddssii', $name, $type, $description, $capacity, $pricePerNight, $pricePerDay, $amenities, $imagePath, $isActive, $accId);
            } else {
                $query = "UPDATE accommodations SET name = ?, type = ?, description = ?, capacity = ?, price_per_night = ?, price_per_day = ?, amenities = ?, is_active = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('sssiddsii', $name, $type, $description, $capacity, $pricePerNight, $pricePerDay, $amenities, $isActive, $accId);
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
