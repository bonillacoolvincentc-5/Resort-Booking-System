<?php
$f = file_get_contents('c:/xampp/htdocs/YP4K_Resort/User_Dashboard/create-booking.php');
$lines = explode("\n", $f);
foreach($lines as $i => $l) {
    if(stripos($l, 'additional rooms') !== false || stripos($l, 'add room') !== false) {
        echo ($i+1) . ': ' . trim($l) . "\n";
    }
}
?>
