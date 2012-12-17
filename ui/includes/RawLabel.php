<?php
/**
 * Created by De Ontwikkelfabriek.
 * User: Postie
 * Date: 8/29/11
 * Time: 4:29 PM
 * Copyright 2011 De Ontwikkelfabriek
 */
 
class RawLabel implements LabelReader {

    public function getLabel($lineID, $shear)
    {
        $label = new Label();
        $label->word = trim($lineID[1]);

        $coos = array();

        $coordinates = LabelUtils::getCoordinatesFromString($lineID[0]);

        /* PRE: First point is ALWAYS left top
         * If not, this will fail
         *
         * Update 31-8-11: Doesn't matter which point is first in line, it will give correct values to javascript
         * as long as they are in clockwise order
         * Somehow I think it can be done more beautiful but hey, it works
        */
        $c = explode('-', $coordinates);

        /*
         * update 2-9-11
         * Negative x or x > image_width have to be reduced to 0 or image_width resp.
         * Therefore, PRE: First point is ALWAYS left-top point
         */

        // reuse coordinates
        $coordinates = array();
        foreach($c as $co)
        {
            $coordinates[] = new Coordinate(explode(',', $co));
        }

        /* sort coordinates */
        $coordinates = $this->sortCoordinates($coordinates);

        if(count($coordinates) == 4)
        {
            // normal label, no boundaries crossed
            $label->w = max(($coordinates[1]->x - $coordinates[0]->x), ($coordinates[2]->x - $coordinates[3]->x));
            $label->h = $coordinates[2]->y; // actually it is y2 - y0, but y0 = 0 
        }
        elseif(count($coordinates) == 5)
        {
            /* so a boundary is crossed. give other coordinates to the javascript part
             * x = First point is always left-top
             * y = always zero
             */
            $label->w = max(($coordinates[1]->x - $coordinates[0]->x), ($coordinates[3]->x - $coordinates[4]->x));
            $label->h = $coordinates[3]->y - $coordinates[0]->y;
        }
        else{
            $errorPoints = count($coordinates) == 1 ? ' point' : 'points';
            throw new Exception('Error in label. Label should have <strong>4</strong> or <strong>5</strong> points, but has <strong>' . count($coordinates) . '</strong>' . $errorPoints);
        }

        $label->x    = $coordinates[0]->x - round((.5 * $label->h) / tan(deg2rad($shear)));
        $label->y    = 0;
        $label->type = Config::LINE;

        return $label;
    }

    private function sortCoordinates($points)
    {
        // find index of first coordinates
        // first coordinates: y = 0, x = smallest
        $cur = 0;
        $index = -1;
        $oldIndex = -1;
        $oldX = -1;
        foreach($points as $point)
        {
            if($point->y == 0)
            {
                if($oldIndex < 0)
                {
                    $oldX = $point->x;
                    $oldIndex = $cur;
                }
                else
                {
                    $index = $point->x > $oldX ? $oldIndex : $cur;
                }
            }
            $cur++;
        }
        // reindex array with new startingpoint
        if($index != 0)
        {
            return array_merge(array_slice($points, $index), array_slice($points, 0, $index));
        }
        return $points;
    }
}

