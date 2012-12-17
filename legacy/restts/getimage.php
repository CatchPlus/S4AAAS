<?php

define('imgcache_dir', getcwd() . '/../imgcache');
header('Content-Type: image/jpeg');
passthru('cd ' . imgcache_dir . '; ./getimage image links ' . $_GET['id']);

?>
