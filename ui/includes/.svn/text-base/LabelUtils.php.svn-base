<?php
/**
 * Created by De Ontwikkelfabriek.
 * User: Postie
 * Date: 8/30/11
 * Time: 1:26 PM
 * Copyright 2011 De Ontwikkelfabriek
 */
 
abstract class LabelUtils {

    public static function getCoordinatesFromString($line)
    {
        $line = urldecode($line);

        $start = stripos($line, Config::ROI);
        $end   = stripos($line, Config::PTS);
        
        if($end === false)
            $coordinates = substr($line, $start + strlen(Config::ROI));
        else
            $coordinates = substr($line, $start + strlen(Config::ROI), ($end - $start) - strlen(Config::ROI));

        return $coordinates;
    }

}
