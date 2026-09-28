<?php
$c = new mysqli('localhost','root','','yp4k_resort_db');
$r = $c->query("SELECT b.id, b.full_name, b.booking_type, b.tour_type, b.check_in_date, b.check_out_date, b.status_id, acc.name AS item_name FROM bookings b LEFT JOIN accommodations acc ON b.accommodation_id = acc.id WHERE b.check_in_date LIKE '2026-09-%' OR b.check_out_date LIKE '2026-09-%'");
while($row = $r->fetch_assoc()) {
    print_r($row);
}
?>
