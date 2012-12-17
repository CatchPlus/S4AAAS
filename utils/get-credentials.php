<?php
    define('appini_path', '/var/www/s4aaas.target-imedia.nl/rest/application/configs/application.ini');
    $appini = parse_ini_file(appini_path);

    echo $appini['resources.db.params.host'] . "\t" .
         $appini['resources.db.params.username'] . "\t" .
         $appini['resources.db.params.password'] . "\t" .
         $appini['resources.db.params.dbname'];
?>
