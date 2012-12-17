<?php
/**
 * Created by De Ontwikkelfabriek.
 * User: Postie
 * Date: 8/29/11
 * Time: 3:55 PM
 * Copyright 2011 De Ontwikkelfabriek
 */
 
class LegacyLabel implements LabelReader {


    public function getLabel($lineID, $shear)
    {
        /* so we have a next line. let's parse it while it has labels */
        $label = new Label();
        $keys = Config::$ROIkeys;

        //$label->id = trim($lineID[0]);
        $label->word = trim($lineID[1]);

        /* matching all the keys (coordinates) */
        foreach ($keys as $match)
        {
            $begin = stripos($lineID[0], $match);
            $end = stripos($lineID[0], '-', $begin + 1);

            if ($end === false)
                $value = substr($lineID[0], $begin + strlen($match));
            else
                $value = substr($lineID[0], $begin + strlen($match), ($end - $begin) - strlen($match));

            // ROI keys are '-x=', '-y=', etc.
            $label->$match[1] = $value;
        }
        // 'convert' the x,y,w,h to integers
        $label->toInt();
        // shear the label, if necessary
        $label->toShearOrNotToShear($shear);
        // shear the x if label = line
        return $label;
    }
}
