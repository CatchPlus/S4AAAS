<?php

$host = $_SERVER['HTTP_HOST'];
/* set the root directory */
define('ROOT', getcwd() . DIRECTORY_SEPARATOR);
/* set the include directory */
define('INCLUDES',ROOT . 'include'.DIRECTORY_SEPARATOR);
/* config */
define('CONFIG', ROOT . 'config'.DIRECTORY_SEPARATOR);

require_once(INCLUDES . '/AutoLoader.php');
$autoLoader = new AutoLoader(); // registering autoload functions

// preveting direct access
//if(isset($_SERVER['HTTP_REFERER']) && stripos($_SERVER['HTTP_REFERER'], Config::LOCAL_URL . Config::LOCAL_SUFFIX) !== false)
//{

    try {

        $suggestions = new Suggestions($_GET);
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: ' . date('r'));
        header('Content-type: application/json');
        echo $suggestions->getSuggestions();

    }  catch (Exception $e) {

        $template->assign("exceptionMessage", $e->getMessage());
        $template->assign('exceptionCode', $e->getCode());
        $template->assign('exceptionLine', $e->getLine());
        $template->assign('exceptionFile', $e->getFile());
        $template->assign('exceptionTraceAsString', $e->getTraceAsString());

        $template->display('exception.tpl');

        exit;
    }
//} else {
//    die('Direct access not allowed');
//}

?>
