<?php
/* something that looks like a bootstrap */
define('ROOT', getcwd() . DIRECTORY_SEPARATOR);                 /* set the root directory */
define('INCLUDES',  ROOT . 'includes'  . DIRECTORY_SEPARATOR);      /* set the include directory */
define('LIBRARIES', ROOT . 'libraries' . DIRECTORY_SEPARATOR);    /* setting the libraries directory */
define('CONFIG',    ROOT . 'config'    . DIRECTORY_SEPARATOR);          /* location of the configuration */
define('ENUMS',     ROOT . 'enums'     . DIRECTORY_SEPARATOR);

/* include the autoloader */
require_once(INCLUDES . 'AutoLoader.php');
//require_once(INCLUDES . 'SecureSession.php');

$autoLoader = new AutoLoader(); // registering autoload functions

/* starting session AFTER the autoload. Do NOT move it before the autoload.
 * If you do, the shit hits the fan
 * The program stores the result in a session variable. If session start is before the autoload
 * this class will become a incomplete class and that's not something you want 
 */
session_start();

$application = new Application();
try
{
    $application->run();
} catch (Exception $e) {
    /* unregister autoload */
    $autoLoader->unregister();
    require_once(LIBRARIES . 'Smarty' . DIRECTORY_SEPARATOR . 'Smarty.class.php');
    $smarty = new Smarty();
    $smarty->assign("exceptionMessage", $e->getMessage());
    $smarty->assign('exceptionCode', $e->getCode());
    $smarty->assign('exceptionLine', $e->getLine());
    $smarty->assign('exceptionFile', $e->getFile());
    $smarty->assign('exceptionTrace', $e->getTraceAsString());
    $smarty->display('exception.tpl');
    exit;
}