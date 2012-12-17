<?php
/**
 * Autoload classes
 *
 * Autoload classes from INCLUDES or INCLUDES/Smarty.
 * Filename should be Classname.php (case sensitive)
 */
class Autoloader {

    /**
     * Constructor registering the autoload function
     */
    public function __construct() {
        spl_autoload_register('spl_autoload');
        spl_autoload_register(array($this, 'loader'));
    }
    
    private function loader($className)
    {

        if($className == 'Config')
            require_once(CONFIG . $className . '.php');
        elseif(file_exists(ENUMS . $className . '.php' ))
            require_once(ENUMS . $className . '.php');
        elseif(file_exists(INCLUDES . $className . '.php' ))
            require_once(INCLUDES . $className . '.php');
        elseif(file_exists(LIBRARIES . $className . '.php' ))
            require_once(LIBRARIES . $className . '.php');
        elseif(file_exists(LIBRARIES . 'Smarty' . DIRECTORY_SEPARATOR . $className . '.class.php'))
            require_once(LIBRARIES . 'Smarty' . DIRECTORY_SEPARATOR . $className . '.class.php');
        else
            return;
    }
    
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'loader'));
    }
}
