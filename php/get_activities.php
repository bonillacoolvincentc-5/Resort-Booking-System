<?php
/**
 * Get Activities API
 * Returns list of available activities
 */

require_once '../phpAuthentication/db_config.php';

header('Content-Type: application/json');

try {
    $stmt = $conn->prepare("
        SELECT id, name, slug, description, price_per_hour, min_duration_hours, 
               max_duration_hours, capacity, icon, image_url, is_active
        FROM activities
        WHERE is_active = 1
        ORDER BY name ASC
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $activities = [];
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'activities' => $activities
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
