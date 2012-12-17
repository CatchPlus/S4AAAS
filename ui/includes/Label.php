<?php
/**
 * Created by De Ontwikkelfabriek.
 * User: Postie
 * Date: 7/7/11
 * Time: 2:51 PM
 * Copyright 2011 De Ontwikkelfabriek
 */
 
class Label {

    // would use private instead of public but propert_exists returns false on private with php < 5.3.0
    //public $id;     /* lineid used with the label */

    public $x;

    public $y;

    public $w;

    public $h;

    public $word;

    public $type;

    public function getLabel()
    {
//        return array(
//            "txt" => $this->word,
//            "x"   => $this->x,
//            "y"   => $this->y,
//            "w"   => $this->w,
//            "h"   => $this->h
//        );
        return array(
            $this->word,
            (int) $this->x,
            (int) $this->y,
            (int) $this->w,
            (int) $this->h
        );
    }

    /**
     * Stores the properties as an integer
     * @return void
     */
    public function toInt()
    {
        $this->x = intval($this->x);
        $this->y = intval($this->y);
        $this->w = intval($this->w);
        $this->h = intval($this->h);
    }

    /**
     * Performs a shear on the line, if the y coordinate == 0
     * @param $shear
     * @return void
     */
    public function toShearOrNotToShear($shear)
    {
        if($this->y == 0)
            $this->x += ((.5 * $this->h) / tan(deg2rad($shear)));
    }

    static function sortByX($a, $b)
    {
        if($a->x == $b->x)
            return 0;
        return ($a->x > $b->x ? 1 : -1);
    }

}
