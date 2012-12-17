<?php
/**
 * Created by De Ontwikkelfabriek.
 * User: Postie
 * Date: 8/29/11
 * Time: 10:24 AM
 * Copyright 2011 De Ontwikkelfabriek
 */
 
class NewWordLabel extends MonkURI {

    function __construct($label, $shear = Config::DEFAULT_SHEAR, $sep = ',')
    {
        parent::__construct('newwordlabel-api', $label, $shear, $sep);
    }

    public function getURI()
    {

        $uri = array();
        if (isset($this->label['method']))
        {
            $className = StringUtils::ucf($this->label['method'] . 'URI');

            $uri['cmd']   = $this->api;
            $uri['token'] = $this->token;
            $uri['appid'] = $this->appid;

            $shape = new $className;
            
            $uri = array_merge($uri, $shape->getURI($this->label, $this->shear));

        }
        return $uri;
    }

}
