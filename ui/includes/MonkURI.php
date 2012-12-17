<?php
/**
 * Created by De Ontwikkelfabriek.
 * User: Postie
 * Date: 8/29/11
 * Time: 10:18 AM
 * Copyright 2011 De Ontwikkelfabriek
 */
 
abstract class MonkURI {

    protected $api;

    protected $token;

    protected $appid;

    protected $label;

    protected $shear;

    function __construct($api, $label, $shear, $sep)
    {

        $user = MonkUser::getInstance();

        if(is_array($label))
            $this->label = $label;
        else
            $this->label = explode($sep, $label);

        $this->api   = $api;
        $this->token = $user->getToken();
        $this->appid = Config::APPID;
        $this->shear = $shear;
        
    }

}
