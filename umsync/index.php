<?php

define('appini_path', '/var/www/s4aaas.target-imedia.nl/rest/application/configs/application.ini');
define('dtFormat','Y-m-d H:i:s');
define('userPath', '/target/gpfs2/monk/.monk.passwd');
$xmlPath = '/target/gpfs2/monk/UserPreferences/.permissions/%s%s.xml';

if(!is_file(userPath))
    exit('MONK user management files not available.');


include 'db.php';
$db = new db();
include 'userManagementMonk.php';
$userManagementMonk = new userManagementMonk();
include 'userBaseMonk2db.php';
include 'ts.php';
include 'monk2s4aaas.php';
include 's4aaas2monk.php';
include 'userBaseMonk2db.php'; // 2nd
?>
