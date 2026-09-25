<?php
$conn = new mysqli('localhost', 'root', '', 'yp4k_resort_db');
$conn->query("
CREATE TABLE IF NOT EXISTS room_inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    room_id INT NOT NULL,
    booked_count INT NOT NULL DEFAULT 0,
    UNIQUE KEY unique_room_date (room_id, date)
);
");
echo "Done";
