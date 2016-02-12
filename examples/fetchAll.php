<?php
require_once '../src/Tilannehuone.class.php';

$th = new Tilannehuone; // construct
foreach($th->fetch() as $data) { // fetch all data
    echo "<a href=\"http://www.tilannehuone.fi/tehtava.php?hash=" . $data['hash'] . "\" target=\"_blank\"><b>" . date("d.m.Y H:i", $data['time']) . "</b>: " . (empty($data['description']) ? '???' : $data['description']) . ", " . $data['place'] . " (" . $data['coordinates'] . ")</a><br />"; // show data
}
?>
