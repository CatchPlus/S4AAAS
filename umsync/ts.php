<?php

// Get MONK synchronisation timestamp and timestamp ID, actual and previous.
$r = $db->query('SELECT ID, TIMESTAMP FROM MONK_SYNCHRONISED ORDER BY ID DESC LIMIT 2');
$monkActualTsId = $r[0]['ID'];
$dto_monkActualTs = new DateTime($r[0]['TIMESTAMP']);
$firstSync = true;
if (isset($r[1]['ID'])) {
    $firstSync = false;
    $monkPreviousTsId = $r[1]['ID'];
    $dto_monkPreviousTs = new DateTime($r[1]['TIMESTAMP']);
}
?>
