<?php
/**
 * Get Packages API
 * Returns all active activities and accommodations as bookable resort packages.
 * YP4K Resort — Booking System
 */

require_once '../phpAuthentication/db_config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // ── Activities ──────────────────────────────────────────────────────────
    $actStmt = $conn->prepare("
        SELECT 
            id,
            name,
            slug,
            description,
            price_per_hour,
            min_duration_hours,
            max_duration_hours,
            capacity,
            icon,
            image_url,
            is_active
        FROM activities
        WHERE is_active = 1
        ORDER BY name ASC
    ");
    $actStmt->execute();
    $actResult = $actStmt->get_result();

    $activities = [];
    while ($row = $actResult->fetch_assoc()) {
        // Parse amenities if stored as JSON or comma-separated
        $amenities = [];
        if (!empty($row['description'])) {
            // Extract keywords as pseudo-amenities from description
            $amenities = [];
        }
        $activities[] = [
            'id'                 => $row['id'],
            'package_type'       => 'activity',
            'name'               => $row['name'],
            'slug'               => $row['slug'],
            'description'        => $row['description'],
            'short_description'  => mb_strimwidth($row['description'] ?? '', 0, 120, '...'),
            'price'              => (float) $row['price_per_hour'],
            'price_label'        => '₱' . number_format($row['price_per_hour'], 2) . '/hr',
            'starting_price'     => '₱' . number_format($row['price_per_hour'], 2),
            'capacity'           => $row['capacity'],
            'icon'               => $row['icon'] ?? 'bi-star',
            'image_url'          => $row['image_url'],
            'min_duration_hours' => (float) $row['min_duration_hours'],
            'max_duration_hours' => (float) $row['max_duration_hours'],
            'amenities'          => [],
            'inclusions'         => [
                'Activity access for ' . $row['capacity'] . ' person(s)',
                'Min ' . $row['min_duration_hours'] . 'h – Max ' . $row['max_duration_hours'] . 'h',
                'Equipment provided (if applicable)',
                'Safety briefing included',
            ],
        ];
    }
    $actStmt->close();

    // ── Accommodations ───────────────────────────────────────────────────────
    $accStmt = $conn->prepare("
        SELECT 
            id,
            name,
            type,
            description,
            capacity,
            price_per_night,
            price_per_day,
            amenities,
            image_url,
            is_active
        FROM accommodations
        WHERE is_active = 1
        ORDER BY type ASC, name ASC
    ");
    $accStmt->execute();
    $accResult = $accStmt->get_result();

    $accommodations = [];
    while ($row = $accResult->fetch_assoc()) {
        // Amenities may be stored as JSON array or comma-separated string
        $amenitiesList = [];
        if (!empty($row['amenities'])) {
            $decoded = json_decode($row['amenities'], true);
            if (is_array($decoded)) {
                $amenitiesList = $decoded;
            } else {
                $amenitiesList = array_map('trim', explode(',', $row['amenities']));
            }
        }

        $accommodations[] = [
            'id'               => $row['id'],
            'package_type'     => 'accommodation',
            'name'             => $row['name'],
            'slug'             => strtolower(str_replace(' ', '-', $row['name'])),
            'type'             => $row['type'],
            'description'      => $row['description'],
            'short_description'=> mb_strimwidth($row['description'] ?? '', 0, 120, '...'),
            'price'            => (float) $row['price_per_night'],
            'price_label'      => '₱' . number_format($row['price_per_night'], 2) . '/night',
            'starting_price'   => '₱' . number_format($row['price_per_night'], 2),
            'capacity'         => $row['capacity'],
            'icon'             => $row['type'] === 'room' ? 'bi-door-open' :
                                  ($row['type'] === 'cottage' ? 'bi-house' : 'bi-building'),
            'image_url'        => $row['image_url'],
            'amenities'        => $amenitiesList,
            'inclusions'       => [
                ucfirst($row['type']) . ' for up to ' . $row['capacity'] . ' guest(s)',
                'Price: ₱' . number_format($row['price_per_night'], 2) . '/night',
                'Day rate: ₱' . number_format($row['price_per_day'], 2) . '/day',
            ],
        ];
    }
    $accStmt->close();

    // ── Combine ──────────────────────────────────────────────────────────────
    $packages = array_merge($activities, $accommodations);

    echo json_encode([
        'success'        => true,
        'packages'       => $packages,
        'activities'     => $activities,
        'accommodations' => $accommodations,
        'total'          => count($packages),
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
}
?>
