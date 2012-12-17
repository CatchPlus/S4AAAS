<?php
/**
 * Created by De Ontwikkelfabriek.
 * User: Postie
 * Date: 8/29/11
 * Time: 4:20 PM
 * Copyright 2011 De Ontwikkelfabriek
 */
 
class StringUtils {


    /* ucfirst function replacement */
    public static function ucf($string)
    {
        if (!function_exists('ucfirst')) {
            return substr_replace($string, strtoupper(substr($string, 0, 1)), 0, 1);
        } else {
            return ucfirst($string);
        }
    }

    /* lcfirst function replacement */
    public static function lcf($string)
    {
        if (!function_exists('ucfirst')) {
            return substr_replace($string, strtoupper(substr($string, 0, 1)), 0, 1);
        } else {
            return lcfirst($string);
        }
    }

}
