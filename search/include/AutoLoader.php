<?php

/**
 * Description of AutoLoader
 *
 * @author Postie
 */
class Autoloader {
    public function __construct() {
        spl_autoload_register('spl_autoload');
        spl_autoload_register(array($this, 'loader'));
        
    }
    
    private function loader($className)
    {
        if($className == 'Constants')
        {
            if(file_exists(CONFIG . $className . '.php'))
            {
                require_once(CONFIG . $className . '.php');
            }
        }
        elseif($className == 'Config')
        {

            $hosttype = PathUtils::getHostType();
            $configFile = CONFIG . $className . '.' . $hosttype . '.php';

            if(file_exists($configFile))
            {
                require_once($configFile);
            }
            else
            {
                die('Sorry, no configuration file found.');
            }

        }
        elseif(file_exists(INCLUDES . $className . '.php' ))
        {
            require_once(INCLUDES . $className . '.php');
        } 
        elseif(file_exists(INCLUDES . 'Smarty' . DIRECTORY_SEPARATOR . $className . '.class.php'))
        {
            require_once(INCLUDES . 'Smarty' . DIRECTORY_SEPARATOR . $className . '.class.php');
        } 
        elseif(file_exists(INCLUDES . $className . '.class.php'))
        {
            require_once(INCLUDES . $className . '.class.php');
        } 
        else
        {
            // return, to get the autoloader of smarty..OR include smarty manually
            // could create an error, when class is not found and it's not a Smarty
            return;
        }
    }
}

?>
