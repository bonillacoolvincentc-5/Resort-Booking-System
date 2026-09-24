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
    // Ensure columns exist
    $chkAct = $conn->query("SHOW COLUMNS FROM `activities` LIKE 'daily_booking_limit'");
    if ($chkAct && $chkAct->num_rows === 0) {
        $conn->query("ALTER TABLE `activities` ADD COLUMN `daily_booking_limit` INT(11) NOT NULL DEFAULT 10 AFTER `capacity`");
    }
    $chkAcc = $conn->query("SHOW COLUMNS FROM `accommodations` LIKE 'daily_booking_limit'");
    if ($chkAcc && $chkAcc->num_rows === 0) {
        $conn->query("ALTER TABLE `accommodations` ADD COLUMN `daily_booking_limit` INT(11) NOT NULL DEFAULT 1 AFTER `capacity`");
    }

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
            daily_booking_limit,
            image_url,
            is_active,
            (SELECT COUNT(*) FROM bookings WHERE booking_type = 'activity' AND activity_id = activities.id AND check_in_date <= CURDATE() AND (check_out_date >= CURDATE() OR check_out_date IS NULL) AND status_id IN (SELECT id FROM booking_status WHERE name IN ('pending_review', 'approved', 'checked_in'))) AS booked_today_count
        FROM activities
        WHERE is_active = 1
        ORDER BY name ASC
    ");
    $actStmt->execute();
    $actResult = $actStmt->get_result();

    $activities = [];
    while ($row = $actResult->fetch_assoc()) {
        $pType = $row['price_type'] ?? 'flat_rate';
        if ($pType === 'per_head') {
            $priceLabel = '₱' . number_format($row['price_per_hour'], 2) . ' / person';
        } elseif ($pType === 'per_hour') {
            $priceLabel = '₱' . number_format($row['price_per_hour'], 2) . ' / hr';
        } else {
            $priceLabel = '₱' . number_format($row['price_per_hour'], 2) . ' / session';
        }

        $actInclusions = [
            'Capacity: up to ' . $row['capacity'] . ' person(s)',
        ];
        if (!empty($row['min_duration_hours']) || !empty($row['max_duration_hours'])) {
            $minH = (float)$row['min_duration_hours'];
            $maxH = (float)$row['max_duration_hours'];
            $actInclusions[] = ($minH == $maxH) ? "Duration: {$minH} hour(s)" : "Duration: {$minH}h – {$maxH}h";
        }
        $actInclusions[] = 'Pricing: ' . $priceLabel;

        $activities[] = [
            'id'                 => $row['id'],
            'package_type'       => 'activity',
            'name'               => $row['name'],
            'slug'               => $row['slug'],
            'description'        => $row['description'],
            'short_description'  => mb_strimwidth($row['description'] ?? '', 0, 120, '...'),
            'price'              => (float) $row['price_per_hour'],
            'price_type'         => $pType,
            'price_label'        => $priceLabel,
            'starting_price'     => '₱' . number_format($row['price_per_hour'], 2),
            'capacity'           => $row['capacity'],
            'icon'               => 'bi-activity',
            'image_url'          => normalizeImageUrl($row['image_url']),
            'max_duration_hours' => (float) $row['max_duration_hours'],
            'booked_today_count' => (int) $row['booked_today_count'],
            'booking_limit'      => (int) ($row['daily_booking_limit'] ?? $row['capacity']),
            'amenities'          => [],
            'inclusions'         => $actInclusions,
        ];
    }
    $actStmt->close();

    // ── Accommodations ───────────────────────────────────────────────────────
    $accStmt = $conn->prepare("
        SELECT 
            id,
            name,
            type,
            tour_type,
            description,
            base_capacity,
            capacity,
            daily_booking_limit,
            extra_person_fee,
            price_per_night,
            price_per_day,
            amenities,
            image_url,
            is_active,
            (SELECT COUNT(*) FROM bookings WHERE booking_type = 'accommodation' AND accommodation_id = accommodations.id AND check_in_date <= CURDATE() AND (check_out_date >= CURDATE() OR check_out_date IS NULL) AND status_id IN (SELECT id FROM booking_status WHERE name IN ('pending_review', 'approved', 'checked_in'))) AS booked_today_count
        FROM accommodations
        WHERE is_active = 1
        ORDER BY type ASC, name ASC
    ");
    $accStmt->execute();
    $accResult = $accStmt->get_result();

    $accommodations = [];
    while ($row = $accResult->fetch_assoc()) {
        // Amenities parsed from admin-stored comma or newline-separated string / JSON
        $amenitiesList = [];
        if (!empty($row['amenities'])) {
            $decoded = json_decode($row['amenities'], true);
            if (is_array($decoded)) {
                $amenitiesList = array_values(array_filter(array_map('trim', $decoded)));
            } else {
                $rawItems = preg_split('/[,\r\n]+/', $row['amenities']);
                $amenitiesList = array_values(array_filter(array_map('trim', $rawItems)));
            }
        }

        $accInclusions = [
            ucfirst($row['type']) . ' for up to ' . $row['capacity'] . ' guest(s)',
        ];
        if (!empty($row['base_capacity'])) {
            $accInclusions[] = 'Base capacity: ' . $row['base_capacity'] . ' guests';
        }
        if (!empty($row['extra_person_fee']) && floatval($row['extra_person_fee']) > 0) {
            $accInclusions[] = 'Extra person fee: ₱' . number_format($row['extra_person_fee'], 2) . ' / person';
        }
        if (!empty($row['price_per_night']) && floatval($row['price_per_night']) > 0) {
            $accInclusions[] = 'Overnight rate: ₱' . number_format($row['price_per_night'], 2) . ' / night';
        }
        if (!empty($row['price_per_day']) && floatval($row['price_per_day']) > 0) {
            $accInclusions[] = 'Day tour rate: ₱' . number_format($row['price_per_day'], 2) . ' / day';
        }

        $accommodations[] = [
            'id'               => $row['id'],
            'package_type'     => 'accommodation',
            'name'             => $row['name'],
            'slug'             => strtolower(str_replace(' ', '-', $row['name'])),
            'type'             => $row['type'],
            'tour_type'        => $row['tour_type'] ?? 'overnight,day_tour',
            'description'      => $row['description'],
            'short_description'=> mb_strimwidth($row['description'] ?? '', 0, 120, '...'),
            'price'            => (float) $row['price_per_night'],
            'price_per_night'  => (float) $row['price_per_night'],
            'price_per_day'    => (float) $row['price_per_day'],
            'base_capacity'    => (int) ($row['base_capacity'] ?? 1),
            'capacity'         => $row['capacity'],
            'extra_person_fee' => (float) ($row['extra_person_fee'] ?? 0),
            'price_label'      => '₱' . number_format($row['price_per_night'], 2) . '/night',
            'starting_price'   => '₱' . number_format($row['price_per_night'], 2),
            'icon'             => $row['type'] === 'room' ? 'bi-door-open' :
                                  ($row['type'] === 'cottage' ? 'bi-house' : 'bi-building'),
            'booked_today_count'=> (int) $row['booked_today_count'],
            'booking_limit'    => (int) ($row['daily_booking_limit'] ?? 1), // Daily unit logic
            'image_url'        => normalizeImageUrl($row['image_url']),
            'amenities'        => $amenitiesList,
            'inclusions'       => $accInclusions,
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
            is_active,
            daily_booking_limit,
            (SELECT COUNT(*) FROM bookings WHERE booking_type = 'bundle' AND bundle_id = bundles.id AND check_in_date <= CURDATE() AND (check_out_date >= CURDATE() OR check_out_date IS NULL) AND status_id IN (SELECT id FROM booking_status WHERE name IN ('pending_review', 'approved', 'checked_in'))) AS booked_today_count
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
            SELECT a.id, a.name 
            FROM activities a
            JOIN bundle_activities ba ON a.id = ba.activity_id
            WHERE ba.bundle_id = ?
        ");
        $actLinkStmt->bind_param('i', $row['id']);
        $actLinkStmt->execute();
        $actLinkRes = $actLinkStmt->get_result();
        $includedActivityIds = [];
        while ($actRow = $actLinkRes->fetch_assoc()) {
            $linkedActs[] = $actRow['name'];
            $includedActivityIds[] = (int)$actRow['id'];
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
            'id'                    => $row['id'],
            'package_type'          => 'bundle',
            'name'                  => $row['name'],
            'slug'                  => $row['slug'],
            'description'           => $row['description'],
            'short_description'     => mb_strimwidth($row['description'] ?? '', 0, 120, '...'),
            'price'                 => (float) $row['price'],
            'price_label'           => '₱' . number_format($row['price'], 2) . ' flat package price',
            'starting_price'        => '₱' . number_format($row['price'], 2),
            'capacity'              => $row['capacity'],
            'start_time'            => $row['start_time'],
            'duration_hours'        => $row['duration_hours'] !== null ? (int) $row['duration_hours'] : null,
            'booked_today_count'    => (int) $row['booked_today_count'],
            'booking_limit'         => (int) $row['daily_booking_limit'],
            'icon'                  => 'bi-box-seam',
            'image_url'             => normalizeImageUrl($row['image_url']),
            'amenities'             => $amenitiesList,
            'inclusions'            => $inclusions,
            'included_activity_ids' => $includedActivityIds,
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
