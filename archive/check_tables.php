<?php
$conn = new mysqli('localhost', 'root', '', 'yp4k_resort_db');
$res = $conn->query('SHOW TABLES');
while($row = $res->fetch_row()) {
    echo $row[0] . "\n";
}
