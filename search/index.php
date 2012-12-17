<?php

$host = $_SERVER['HTTP_HOST'];

/* set the root directory */
define('ROOT', getcwd() . DIRECTORY_SEPARATOR);
/* set the include directory */
define('INCLUDES', ROOT . 'include'.DIRECTORY_SEPARATOR);
/* config path */
define('CONFIG', ROOT . 'config'.DIRECTORY_SEPARATOR);
/* set the template directory */
define('TEMPLATES', ROOT . 'templates' . DIRECTORY_SEPARATOR);
/* security measure */
define('_MONK_', true);

/* include the autoloader */
require_once(INCLUDES . '/AutoLoader.php');

$autoLoader = new AutoLoader(); // registering autoload functions

session_start();

/* set the controller */
$frontController = new FrontController();

$frontController->run();

