<?php
/**
 * Handles the Front, creating the corresponding Controller if needed
 *
 */
class FrontController {
    
    private $getData;
    
    function __construct()
    {
        $this->getData = $_GET;
    }
    
    public function run()
    {
        if(isset($this->getData['needle']))
        {   
            if(!(empty($this->getData['needle'])))
            {
                $this->createController ('Result');
            }
            else
            {
                header('Location: ' . $_SERVER['SCRIPT_NAME']);
                exit;
            }
        }
        else if(isset($this->getData['language']))
        {

            $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
            setcookie('language', $this->getData['language'], time()+86400, "/", $domain, false);
            unset($_GET['language']);
            header('Location: ' . $_SERVER['SCRIPT_NAME']);

        }
        else
        {
            $monkController = $this->createController('Monk');
            if((isset($this->getData['cmd'])) && ($this->getData['cmd'] == 'getcollection'))
            {
                echo $monkController->collectionAsJSON();
            }
            else
            {
                $monkController->run();
            }
        }
        

    }

    /* creates the controller based on the ClassName.
     * Name of the variable for the controller is classnameController
     */
    private function createController($controllerName)
    {
        $className = ucfirst($controllerName) . 'Controller';
        ${$this->lcf($controllerName) . 'Controller'} = new $className();
        return ${$this->lcf($controllerName) . 'Controller'};
    }

    /* lcfirst function replacement */
    private function lcf($string)
    {
        if (!function_exists('lcfirst')) {
            return substr_replace($string, strtolower(substr($string, 0, 1)), 0, 1);
        } else {
            return lcfirst($string);
        }
    }
    
}