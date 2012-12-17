<?php
/**
 * Created by De Ontwikkelfabriek.
 * User: Postie
 * Date: 8/30/11
 * Time: 1:25 PM
 * Copyright 2011 De Ontwikkelfabriek
 */
 
class RectLabel implements LabelReader {

    public function getLabel($lineID, $shear)
    {
        $label = new Label();
        $label->word = trim($lineID[1]);

        $coordinates = LabelUtils::getCoordinatesFromString($lineID[0]);

        /* point 1 = upper left corner
         * point 2 = bottom right corner
         */

        $c = explode('-', $coordinates);
        
        if(count($c) == 2)
        {
            // derive x and y from first point
            $coordinate = explode(',', $c[0]);
            $co1 = new Coordinate($coordinate[0], $coordinate[1]);


            $coordinate = explode(',', $c[1]);
            $co2 = new Coordinate($coordinate[0], $coordinate[1]);

            $label->x = min($co1->x, $co2->x);
            $label->y = min($co1->y, $co2->y);

            // derive width and height from second point
            $label->w = max($co1->x, $co2->x) - $label->x ;
            $label->h = max($co1->y, $co2->y) - $label->y;

        } else {
            throw new Exception("A rect label should have 2 points. This one has " . count($c) . (count($c) == 1 ? " point" : " points"));
        }


        $label->type = Config::SQUARE;

        return $label;

    }


}
