<?php

// On implementation this link must point to the file on the MONK system.
define('metadata', 'navis-id2path.php');

if (!is_file(metadata))
    exit('MONK collection metadata file not available.');
define('appini_path', '/var/www/s4aaas.target-imedia.nl/rest/application/configs/application.ini');
include metadata;
include 'db.php';
$db = new db();

$monkMonkDirs = bookdirlist();
$monkMonkCollections = collections();
$monkMonkInstitutions = institutions();

foreach (array_diff($monkMonkDirs, $db->query('SELECT MONK_DIR FROM BOOKS', true)) as $monkMonkDir) {
    $navis_id = str_replace('_%04d-line-%03d', '', bookdir2linepattern($monkMonkDir));    
    $shortname = shortname(str_replace('_%04d-line-%03d', '_0000-line-000', bookdir2linepattern($monkMonkDir)));    
    $monk_id = str_replace('_0000', '',page_id(str_replace('_%04d-line-%03d', '_0000', bookdir2linepattern($monkMonkDir))));
    $db->stmnt('INSERT INTO INSTITUTIONS (MONK_ID) VALUES (\'' . $monkMonkInstitutions[$monkMonkDir] . '\') ON DUPLICATE KEY UPDATE MONK_ID = \'' . $monkMonkInstitutions[$monkMonkDir] . '\'');
    $db->stmnt('INSERT INTO COLLECTIONS (INSTITUTION_ID, MONK_ID) VALUES ((SELECT ID FROM INSTITUTIONS WHERE MONK_ID = \'' . $monkMonkInstitutions[$monkMonkDir] . '\') ,\'' . $monkMonkCollections[$monkMonkDir] . '\') ON DUPLICATE KEY UPDATE INSTITUTION_ID = (SELECT ID FROM INSTITUTIONS WHERE MONK_ID = \'' . $monkMonkInstitutions[$monkMonkDir] . '\'), MONK_ID = \'' . $monkMonkCollections[$monkMonkDir] . '\'');
    $db->stmnt('INSERT INTO BOOKS (BOOK_DIR, COLLECTION_ID, MONK_ID, MONK_DIR, SHORT_NAME, NAVIS_ID) VALUES (\'' . substr(md5(uniqid(mt_rand(), true)), 0, 8) . '\', (SELECT ID FROM COLLECTIONS WHERE MONK_ID = \'' . $monkMonkCollections[$monkMonkDir] . '\') , \'' . $monk_id . '\', \'' . $monkMonkDir . '\', \'' . $shortname . '\', \'' . $navis_id . '\')');
}





?>