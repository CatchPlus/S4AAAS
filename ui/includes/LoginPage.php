<?php
/**
 * User: Postie
 * Date: 27-8-12
 * Time: 10:04
 */
class LoginPage extends PageView
{
    function __construct()
    {
        $smartVars = array();

        if(isset($_SESSION['errorMessage']))
        {
            $smartVars["errorMessage"] = $_SESSION['errorMessage'];
            unset($_SESSION['errorMessage']);
        }

        $this->smarty = new SmartyPage('login.tpl', $smartVars);
    }

//    public function render()
//    {
//        $this->smarty->render();
//    }
}
