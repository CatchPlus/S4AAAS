<?php
/**
 * Created by De Ontwikkelfabriek.
 * User: Postie
 * Date: 8/29/11
 * Time: 5:10 PM
 * Copyright 2011 De Ontwikkelfabriek
 */
 
class Coordinate {

    public $x;

    public $y;

    function __construct($x, $y = 0)
    {
        if(is_array($x))
        {
            $this->x = $x[0];
            $this->y = $x[1];
        }
        // passing only 1 argument but it's not a string
        elseif(func_num_args() == 1)
        {
            $t = explode(',', $x);
            $this->x = $t[0];
            $this->y = $t[1];
        }
        // passing 2 arguments
        else
        {
            $this->x = $x;
            $this->y = $y;
        }
        $this->x = intval($this->x);
        $this->y = intval($this->y);
    }

    public function __toString()
    {
        return '' . $this->x . ',' . $this->y;
    }

}
