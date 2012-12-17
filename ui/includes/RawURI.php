<?php
/**
 * Created by De Ontwikkelfabriek.
 * User: Postie
 * Date: 8/29/11
 * Time: 11:40 AM
 * Copyright 2011 De Ontwikkelfabriek
 */
 
class RawURI implements ShapeURI
{

    public function getURI($label, $shear)
    {
        // getting middle point from javascript part, need right most point
        // rotate around center for $shear degrees
        $shift = $this->calcShift(intval($label['h']), $shear);

        // building op the array
        $uri = array();
        $uri['label']  = $label['txt'];         // getting the actual word
        $uri['dist']   = $label['dist'];        // distance also in POST
        $uri['lineid'] = $label['lineid'];      // as lineid

        $h      = intval($label['h']);          // getting integer values to work with
        $w      = intval($label['w']);
        $x      = intval($label['x']) + $shift; // 'rotate' x coordinate
        $width  = intval($label['width']);      // getting width from post data

        $coordinates = array();

        // first point is always x, 0
        $coordinates[] = new Coordinate($x, 0);

        // second point is x+w,0 OR image_width, 0
        $coordinates[] = new Coordinate(min(($x + $w), $width), 0);

        // third point depends on boundary crossing
        if(($x + $w) > $width)
            $coordinates[] = new Coordinate($width, round(tan(deg2rad($shear))) * (($x + $w) - $width));

        // bottom right point (can be third or fourth)
        $xBottom       = max(0, ($x + $w) - round(tan(deg2rad($shear)) * $h));
        $coordinates[] = new Coordinate($xBottom, $h);

        // left bottom point
        $coordinates[] = new Coordinate(max(0, ($xBottom - $w)), $h);

        // optional point
        if(($xBottom - $w) < 0)
            $coordinates[] = new Coordinate(0, $x * round(tan(deg2rad(90 - $shear))));

        // convert coordinates to roi url items
        $uri['roi']         = implode('-', $coordinates);
        $uri['coordinates'] = $coordinates;
        $uri['pts']         = PTS::LINE;

        return $uri;
    }

    private function calcShift($height, $shear)
    {
        return round((0.5 * $height) / tan(deg2rad(90 - $shear)));
    }

}