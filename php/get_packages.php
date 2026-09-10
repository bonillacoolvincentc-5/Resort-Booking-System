<?php
/**
 * Get Packages API
 * Returns all active activities and accommodations as bookable resort packages.
 * YP4K Resort — Booking System
 */

require_once __DIR__ . '/../phpAuthentication/db_config.php';

/** @var mysqli $conn */
global $conn;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

function normalizeImageUrl($url) {
    if (empty($url)) return null;
    $url = trim($url);
    if (preg_match('#^https?://#i', $url)) return $url;
    if (substr($url, 0, 1) === '/') return $url;
    if (substr($url, 0, 3) === '../') return $url;
    return '../' . ltrim($url, '/');
}

try {
    // ── Activities ──────────────────────────────────────────────────────────
    $actStmt = $conn->prepare("
        SELECT 
            id,
            name,
            slug,
            description,
            price_per_hour,
            price_type,
            min_duration_hours,
            max_duration_hours,
            capacity,
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
            'price_type'         => $row['price_type'] ?? 'flat_rate',
            'price_label'        => '₱' . number_format($row['price_per_hour'], 2) . '/hr',
            'starting_price'     => '₱' . number_format($row['price_per_hour'], 2),
            'capacity'           => $row['capacity'],
            'icon'               => 'bi-activity',
            'image_url'          => normalizeImageUrl($row['image_url']),
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
            'image_url'        => normalizeImageUrl($row['image_url']),
            'amenities'        => $amenitiesList,
            'inclusions'       => [
                ucfirst($row['type']) . ' for up to ' . $row['capacity'] . ' guest(s)',
                'Price: ₱' . number_format($row['price_per_night'], 2) . '/night',
                'Day rate: ₱' . number_format($row['price_per_day'], 2) . '/day',
            ],
        ];
    }
    $accStmt->close();

    // ── Bundles ──────────────────────────────────────────────────────────────
    $bundleStmt = $conn->prepare("
        SELECT 
            id,
            name,
            slug,
            description,
            price,
            capacity,
            start_time,
            duration_hours,
            amenities,
            image_url,
            is_active
        FROM bundles
        WHERE is_active = 1
        ORDER BY name ASC
    ");
    $bundleStmt->execute();
    $bundleResult = $bundleStmt->get_result();

    $bundles = [];
    while ($row = $bundleResult->fetch_assoc()) {
        // Parse amenities
        $amenitiesList = [];
        if (!empty($row['amenities'])) {
            $amenitiesList = array_map('trim', explode(',', $row['amenities']));
        }

        // Fetch linked activities names
        $linkedActs = [];
        $actLinkStmt = $conn->prepare("
            SELECT a.name 
            FROM activities a
            JOIN bundle_activities ba ON a.id = ba.activity_id
            WHERE ba.bundle_id = ?
        ");
        $actLinkStmt->bind_param('i', $row['id']);
        $actLinkStmt->execute();
        $actLinkRes = $actLinkStmt->get_result();
        while ($actRow = $actLinkRes->fetch_assoc()) {
            $linkedActs[] = $actRow['name'];
        }
        $actLinkStmt->close();

        // Fetch linked accommodations names
        $linkedAccs = [];
        $accLinkStmt = $conn->prepare("
            SELECT acc.name 
            FROM accommodations acc
            JOIN bundle_accommodations bacc ON acc.id = bacc.accommodation_id
            WHERE bacc.bundle_id = ?
        ");
        $accLinkStmt->bind_param('i', $row['id']);
        $accLinkStmt->execute();
        $accLinkRes = $accLinkStmt->get_result();
        while ($accRow = $accLinkRes->fetch_assoc()) {
            $linkedAccs[] = $accRow['name'];
        }
        $accLinkStmt->close();

        // Inclusions: combine activities & accommodations
        $inclusions = [];
        if (!empty($linkedAccs)) {
            $inclusions[] = 'Accommodation: ' . implode(', ', $linkedAccs);
        }
        if (!empty($linkedActs)) {
            $inclusions[] = 'Activities: ' . implode(', ', $linkedActs);
        }
        $inclusions[] = 'Capacity: for ' . $row['capacity'] . ' person(s)';

        $bundles[] = [
            'id'                => $row['id'],
            'package_type'      => 'bundle',
            'name'              => $row['name'],
            'slug'              => $row['slug'],
            'description'       => $row['description'],
            'short_description' => mb_strimwidth($row['description'] ?? '', 0, 120, '...'),
            'price'             => (float) $row['price'],
            'price_label'       => '₱' . number_format($row['price'], 2) . ' flat package price',
            'starting_price'    => '₱' . number_format($row['price'], 2),
            'capacity'          => $row['capacity'],
            'start_time'        => $row['start_time'],
            'duration_hours'    => $row['duration_hours'] !== null ? (int) $row['duration_hours'] : null,
            'icon'              => 'bi-box-seam',
            'image_url'         => normalizeImageUrl($row['image_url']),
            'amenities'         => $amenitiesList,
            'inclusions'        => $inclusions
        ];
    }
    $bundleStmt->close();

    // ── Combine ──────────────────────────────────────────────────────────────
    $packages = array_merge($activities, $accommodations, $bundles);

    echo json_encode([
        'success'        => true,
        'packages'       => $packages,
        'activities'     => $activities,
        'accommodations' => $accommodations,
        'bundles'        => $bundles,
        'total'          => count($packages),
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
}
?>
