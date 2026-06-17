<?php
/**
 * Get Accommodations API
 * Returns list of available accommodations
 */

require_once '../phpAuthentication/db_config.php';

header('Content-Type: application/json');

try {
    $stmt = $conn->prepare("
        SELECT id, name, type, description, capacity, price_per_night, 
               price_per_day, amenities, image_url, is_active
        FROM accommodations
        WHERE is_active = 1
        ORDER BY type ASC, name ASC
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $accommodations = [];
    while ($row = $result->fetch_assoc()) {
        $accommodations[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'accommodations' => $accommodations
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
