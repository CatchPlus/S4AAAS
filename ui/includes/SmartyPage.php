<?php
/**
 * Created by De Ontwikkelfabriek.
 * User: Postie
 * Date: 7/19/11
 * Time: 1:18 PM
 * Copyright 2011 De Ontwikkelfabriek
 */
 
class SmartyPage {

    private $template;

    private $filename;

    function __construct($template, $variables=array())
    {
        $this->template = new Smarty();
        $this->filename = $template;

        foreach($variables as $varName=>$varValue)
        {
            $this->template->assign($varName, $varValue);
        }
    }

    public function render()
    {
        $this->template->display($this->filename);
    }

    public function fetch()
    {
        return $this->template->fetch($this->filename);
    }

}
