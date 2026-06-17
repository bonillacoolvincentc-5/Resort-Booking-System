<?php
/**
 * Bundle CRUD Handler
 * YP4K Resort — Admin API for Manage Bundles
 */

session_start();
require_once '../phpAuthentication/db_config.php';

// Admin-only guard
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

header('Content-Type: application/json');
global $conn;

// ── Auto-create tables if missing ─────────────────────────────────
$conn->query("
    CREATE TABLE IF NOT EXISTS `bundles` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `slug` varchar(255) NOT NULL,
        `description` text DEFAULT NULL,
        `price` decimal(10,2) NOT NULL DEFAULT 0.00,
        `capacity` int(11) NOT NULL DEFAULT 1,
        `amenities` text DEFAULT NULL,
        `image_url` varchar(500) DEFAULT NULL,
        `is_active` tinyint(1) DEFAULT 1,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `slug` (`slug`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$conn->query("
    CREATE TABLE IF NOT EXISTS `bundle_activities` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `bundle_id` int(11) NOT NULL,
        `activity_id` int(11) NOT NULL,
        PRIMARY KEY (`id`),
        KEY `bundle_id` (`bundle_id`),
        KEY `activity_id` (`activity_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$conn->query("
    CREATE TABLE IF NOT EXISTS `bundle_accommodations` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `bundle_id` int(11) NOT NULL,
        `accommodation_id` int(11) NOT NULL,
        PRIMARY KEY (`id`),
        KEY `bundle_id` (`bundle_id`),
        KEY `accommodation_id` (`accommodation_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// ── Router ────────────────────────────────────────────────────────
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ── GET single bundle ─────────────────────────────────────────────
if ($action === 'get') {
    $bundleId = intval($_GET['bundle_id'] ?? 0);
    if (!$bundleId) { echo json_encode(['success' => false, 'message' => 'Bundle ID required']); exit(); }

    try {
        $stmt = $conn->prepare("SELECT * FROM bundles WHERE id = ?");
        $stmt->bind_param('i', $bundleId);
        $stmt->execute();
        $bundle = $stmt->get_result()->fetch_assoc();
        if (!$bundle) { echo json_encode(['success' => false, 'message' => 'Bundle not found']); exit(); }

        // Activities linked
        $aStmt = $conn->prepare("SELECT activity_id FROM bundle_activities WHERE bundle_id = ?");
        $aStmt->bind_param('i', $bundleId);
        $aStmt->execute();
        $aRes = $aStmt->get_result();
        $bundle['activity_ids'] = [];
        while ($r = $aRes->fetch_assoc()) $bundle['activity_ids'][] = $r['activity_id'];

        // Accommodations linked
        $accStmt = $conn->prepare("SELECT accommodation_id FROM bundle_accommodations WHERE bundle_id = ?");
        $accStmt->bind_param('i', $bundleId);
        $accStmt->execute();
        $accRes = $accStmt->get_result();
        $bundle['accommodation_ids'] = [];
        while ($r = $accRes->fetch_assoc()) $bundle['accommodation_ids'][] = $r['accommodation_id'];

        echo json_encode(['success' => true, 'bundle' => $bundle]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

// ── ADD or EDIT bundle ────────────────────────────────────────────
if ($action === 'add' || $action === 'edit') {
    $bundleId    = intval($_POST['bundle_id'] ?? 0);
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = floatval($_POST['price'] ?? 0);
    $capacity    = intval($_POST['capacity'] ?? 1);
    $amenities   = trim($_POST['amenities'] ?? '');
    $isActive    = intval($_POST['is_active'] ?? 1);
    $activityIds = array_filter(array_map('intval', explode(',', $_POST['activity_ids'] ?? '')));
    $accommodationIds = array_filter(array_map('intval', explode(',', $_POST['accommodation_ids'] ?? '')));

    if (empty($name)) { echo json_encode(['success' => false, 'message' => 'Bundle name is required']); exit(); }
    if ($price < 0)   { echo json_encode(['success' => false, 'message' => 'Price must be a positive number']); exit(); }

    // Slug
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
    // Ensure unique slug for edits
    if ($action === 'edit' && $bundleId) {
        $slugCheck = $conn->prepare("SELECT id FROM bundles WHERE slug = ? AND id != ?");
        $slugCheck->bind_param('si', $slug, $bundleId);
    } else {
        $slugCheck = $conn->prepare("SELECT id FROM bundles WHERE slug = ?");
        $slugCheck->bind_param('s', $slug);
    }
    $slugCheck->execute();
    if ($slugCheck->get_result()->num_rows > 0) {
        $slug .= '-' . time();
    }

    // Handle image upload
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/uploads/bundles/';
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
        $ext      = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed  = ['jpg','jpeg','png','webp','gif'];
        if (!in_array($ext, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Invalid image format. Allowed: jpg, png, webp, gif']);
            exit();
        }
        $fileName  = uniqid('bundle_') . '.' . $ext;
        $fullPath  = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $fullPath)) {
            $imagePath = 'assets/uploads/bundles/' . $fileName;
        }
    }

    try {
        $conn->begin_transaction();

        if ($action === 'add') {
            if ($imagePath) {
                $stmt = $conn->prepare("INSERT INTO bundles (name, slug, description, price, capacity, amenities, image_url, is_active) VALUES (?,?,?,?,?,?,?,?)");
                $stmt->bind_param('sssdiisi', $name, $slug, $description, $price, $capacity, $amenities, $imagePath, $isActive);
            } else {
                $stmt = $conn->prepare("INSERT INTO bundles (name, slug, description, price, capacity, amenities, is_active) VALUES (?,?,?,?,?,?,?)");
                $stmt->bind_param('sssdisi', $name, $slug, $description, $price, $capacity, $amenities, $isActive);
            }
            $stmt->execute();
            $bundleId = $conn->insert_id;
        } else {
            if (!$bundleId) { throw new Exception('Bundle ID required for editing'); }
            if ($imagePath) {
                $stmt = $conn->prepare("UPDATE bundles SET name=?, slug=?, description=?, price=?, capacity=?, amenities=?, image_url=?, is_active=? WHERE id=?");
                $stmt->bind_param('sssdiisii', $name, $slug, $description, $price, $capacity, $amenities, $imagePath, $isActive, $bundleId);
            } else {
                $stmt = $conn->prepare("UPDATE bundles SET name=?, slug=?, description=?, price=?, capacity=?, amenities=?, is_active=? WHERE id=?");
                $stmt->bind_param('sssdisii', $name, $slug, $description, $price, $capacity, $amenities, $isActive, $bundleId);
            }
            $stmt->execute();

            // Remove old relations
            $conn->query("DELETE FROM bundle_activities WHERE bundle_id = $bundleId");
            $conn->query("DELETE FROM bundle_accommodations WHERE bundle_id = $bundleId");
        }

        // Insert activity relations
        if (!empty($activityIds)) {
            $aStmt = $conn->prepare("INSERT IGNORE INTO bundle_activities (bundle_id, activity_id) VALUES (?,?)");
            foreach ($activityIds as $aid) {
                $aStmt->bind_param('ii', $bundleId, $aid);
                $aStmt->execute();
            }
        }

        // Insert accommodation relations
        if (!empty($accommodationIds)) {
            $accStmt = $conn->prepare("INSERT IGNORE INTO bundle_accommodations (bundle_id, accommodation_id) VALUES (?,?)");
            foreach ($accommodationIds as $accid) {
                $accStmt->bind_param('ii', $bundleId, $accid);
                $accStmt->execute();
            }
        }

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Bundle ' . ($action === 'add' ? 'added' : 'updated') . ' successfully', 'bundle_id' => $bundleId]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit();
}

// ── DELETE bundle ─────────────────────────────────────────────────
if ($action === 'delete') {
    $bundleId = intval($_POST['bundle_id'] ?? 0);
    if (!$bundleId) { echo json_encode(['success' => false, 'message' => 'Bundle ID required']); exit(); }

    try {
        $conn->begin_transaction();
        $conn->query("DELETE FROM bundle_activities WHERE bundle_id = $bundleId");
        $conn->query("DELETE FROM bundle_accommodations WHERE bundle_id = $bundleId");
        $stmt = $conn->prepare("DELETE FROM bundles WHERE id = ?");
        $stmt->bind_param('i', $bundleId);
        $stmt->execute();
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Bundle deleted successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit();
}

// ── TOGGLE status ─────────────────────────────────────────────────
if ($action === 'toggle_status') {
    $bundleId = intval($_POST['bundle_id'] ?? 0);
    if (!$bundleId) { echo json_encode(['success' => false, 'message' => 'Bundle ID required']); exit(); }

    try {
        $conn->prepare("UPDATE bundles SET is_active = NOT is_active WHERE id = ?")->execute() ;
        $stmt = $conn->prepare("UPDATE bundles SET is_active = NOT is_active WHERE id = ?");
        $stmt->bind_param('i', $bundleId);
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => 'Bundle status updated']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>
