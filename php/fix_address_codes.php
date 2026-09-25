<?php
if (php_sapi_name() !== 'cli') exit('CLI only');
require_once __DIR__ . '/../phpAuthentication/db_config.php';

$res = $conn->query("SELECT id, address, region, province, city_municipality, barangay FROM users");
while ($row = $res->fetch_assoc()) {
    $id = $row['id'];
    $r = $row['region'];
    $p = $row['province'];
    $c = $row['city_municipality'];
    $b = $row['barangay'];
    $addr = $row['address'] ?? '';
    $updated = false;

    if (is_numeric($r) && strlen($r) >= 8) {
        $json = @file_get_contents("https://psgc.gitlab.io/api/regions/{$r}/");
        if ($json) {
            $d = json_decode($json, true);
            if (!empty($d['name'])) { $r = $d['name']; $updated = true; }
        }
    }
    if (is_numeric($p) && strlen($p) >= 8) {
        $json = @file_get_contents("https://psgc.gitlab.io/api/provinces/{$p}/");
        if ($json) {
            $d = json_decode($json, true);
            if (!empty($d['name'])) { $p = $d['name']; $updated = true; }
        }
    }
    if (is_numeric($c) && strlen($c) >= 8) {
        $json = @file_get_contents("https://psgc.gitlab.io/api/cities-municipalities/{$c}/");
        if ($json) {
            $d = json_decode($json, true);
            if (!empty($d['name'])) { $c = $d['name']; $updated = true; }
        }
    }
    if (is_numeric($b) && strlen($b) >= 8) {
        $json = @file_get_contents("https://psgc.gitlab.io/api/barangays/{$b}/");
        if ($json) {
            $d = json_decode($json, true);
            if (!empty($d['name'])) { $b = $d['name']; $updated = true; }
        }
    }

    // If address column contains numbers or was empty or we updated the location fields, rebuild address
    if ($updated || preg_match('/\d{8,}/', $addr) || empty($addr)) {
        $parts = array_filter([$b, $c, $p, $r]);
        $newAddr = implode(', ', $parts);
        $stmt = $conn->prepare("UPDATE users SET region = ?, province = ?, city_municipality = ?, barangay = ?, address = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("sssssi", $r, $p, $c, $b, $newAddr, $id);
            $stmt->execute();
            $stmt->close();
            echo "Successfully converted user ID {$id} address to: {$newAddr}\n";
        }
    } else {
        echo "User ID {$id} address already in character format: {$addr}\n";
    }
}
$conn->close();
?>
