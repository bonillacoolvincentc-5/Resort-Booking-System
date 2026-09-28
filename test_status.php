<?php
$c = new mysqli('localhost','root','','yp4k_resort_db');
$r = $c->query('SELECT * FROM booking_status');
while($row = $r->fetch_assoc()) print_r($row);
?>
