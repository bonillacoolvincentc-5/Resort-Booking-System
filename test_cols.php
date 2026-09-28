<?php
require_once __DIR__ . '/phpAuthentication/db_config.php';
global $conn;

function checkCols($table) {
    global $conn;
    $res = $conn->query("SHOW COLUMNS FROM `$table`");
    $cols = [];
    while ($row = $res->fetch_assoc()) {
        $cols[] = $row['Field'];
    }
    echo "$table columns: " . implode(', ', $cols) . "\n";
}

checkCols('activities');
checkCols('accommodations');
checkCols('bundles');
?>
