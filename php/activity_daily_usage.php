<?php
/**
 * Activity Daily Limit helpers
 * ---------------------------------------------------------------------------
 * The admin sets a "Daily Booking Limit" per activity (activities.daily_booking_limit).
 * That number is how many "spots" of the activity can be used in ONE day.
 *
 * A spot is used by:
 *   1. A standalone booking of the activity       -> number_of_guests (per_head) or 1 (other price types)
 *   2. The activity chosen as an ADD-ON of another booking (e.g. a VIP room + ATV Long Ride)
 *      -> the add-on quantity saved in `booking_addons`
 *
 * Only bookings that still hold a slot are counted (rejected / cancelled / expired free it up).
 */

/** Statuses that keep a slot reserved. */
function activityUsageActiveStatuses() {
    return "(SELECT id FROM booking_status WHERE name IN ('pending_review','awaiting_payment','payment_verifying','approved','checked_in','completed'))";
}

/** Creates the table that remembers which add-on activities each booking selected. */
function ensureBookingAddonsTable($conn) {
    $conn->query("
        CREATE TABLE IF NOT EXISTS `booking_addons` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `booking_id` INT(11) NOT NULL,
            `activity_id` INT(11) NOT NULL,
            `qty` INT(11) NOT NULL DEFAULT 1,
            `usage_date` DATE NOT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `idx_addon_activity_date` (`activity_id`, `usage_date`),
            KEY `idx_addon_booking` (`booking_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $chk = $conn->query("SHOW COLUMNS FROM `activities` LIKE 'daily_booking_limit'");
    if ($chk && $chk->num_rows === 0) {
        $conn->query("ALTER TABLE `activities` ADD COLUMN `daily_booking_limit` INT(11) NOT NULL DEFAULT 10 AFTER `capacity`");
    }
}

/**
 * Usage of every active activity (or just $onlyId) on one date.
 * @return array  [activity_id => ['id','name','price_type','limit','used','remaining']]
 */
function getActivityDailyUsage($conn, $date, $onlyId = null) {
    ensureBookingAddonsTable($conn);
    $active = activityUsageActiveStatuses();

    $sql = "
        SELECT a.id, a.name, a.price_type, a.daily_booking_limit,
            (
                SELECT COALESCE(SUM(CASE WHEN a.price_type = 'per_head' THEN b.number_of_guests ELSE 1 END), 0)
                FROM bookings b
                WHERE b.booking_type = 'activity'
                  AND b.activity_id = a.id
                  AND b.check_in_date = ?
                  AND b.status_id IN $active
            ) + (
                SELECT COALESCE(SUM(ba.qty), 0)
                FROM booking_addons ba
                JOIN bookings b2 ON b2.id = ba.booking_id
                WHERE ba.activity_id = a.id
                  AND ba.usage_date = ?
                  AND b2.status_id IN $active
            ) AS used
        FROM activities a
        WHERE a.is_active = 1
    ";
    if ($onlyId !== null) $sql .= " AND a.id = ?";

    $stmt = $conn->prepare($sql);
    if ($onlyId !== null) {
        $onlyId = (int)$onlyId;
        $stmt->bind_param('ssi', $date, $date, $onlyId);
    } else {
        $stmt->bind_param('ss', $date, $date);
    }
    $stmt->execute();
    $res = $stmt->get_result();

    $out = [];
    while ($row = $res->fetch_assoc()) {
        $limit = max(0, (int)$row['daily_booking_limit']);
        $used  = (int)$row['used'];
        $out[(int)$row['id']] = [
            'id'         => (int)$row['id'],
            'name'       => $row['name'],
            'price_type' => $row['price_type'] ?? 'flat_rate',
            'limit'      => $limit,
            'used'       => $used,
            'remaining'  => max(0, $limit - $used),
        ];
    }
    $stmt->close();
    return $out;
}

/**
 * Used spots of ONE activity for every date in a range (for the availability calendar).
 * @return array  ['Y-m-d' => used]
 */
function getActivityUsageByDate($conn, $activityId, $priceType, $startDate, $endDate) {
    ensureBookingAddonsTable($conn);
    $active   = activityUsageActiveStatuses();
    $unitExpr = ($priceType === 'per_head') ? 'b.number_of_guests' : '1';

    $stmt = $conn->prepare("
        SELECT d, SUM(units) AS used FROM (
            SELECT b.check_in_date AS d, $unitExpr AS units
            FROM bookings b
            WHERE b.booking_type = 'activity'
              AND b.activity_id = ?
              AND b.check_in_date BETWEEN ? AND ?
              AND b.status_id IN $active
            UNION ALL
            SELECT ba.usage_date AS d, ba.qty AS units
            FROM booking_addons ba
            JOIN bookings b ON b.id = ba.booking_id
            WHERE ba.activity_id = ?
              AND ba.usage_date BETWEEN ? AND ?
              AND b.status_id IN $active
        ) t
        GROUP BY d
    ");
    $activityId = (int)$activityId;
    $stmt->bind_param('ississ', $activityId, $startDate, $endDate, $activityId, $startDate, $endDate);
    $stmt->execute();
    $res = $stmt->get_result();

    $out = [];
    while ($row = $res->fetch_assoc()) {
        $out[$row['d']] = (int)$row['used'];
    }
    $stmt->close();
    return $out;
}
