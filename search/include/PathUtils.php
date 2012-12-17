<?php
/**
 * Created by De Ontwikkelfabriek.
 * User: Postie
 * Date: 8/18/11
 * Time: 3:16 PM
 * Copyright 2011 De Ontwikkelfabriek
 */
 
abstract class PathUtils {

    public static function getUrlPath($absolute = false)
    {
        $prefix = $absolute ? 'http://' : '';

        if(dirname($_SERVER['SCRIPT_NAME']) == '/')
            return $prefix . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
        else
            return $prefix . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/';
    }

    public static function getHostType()
    {
        $host = trim($_SERVER['HTTP_HOST']);
        switch($host)
        {
            case 'localhost' :
                return 'local';
                break;
            case '217.21.192.132':
                return 'public';
                break;
            case 'monk.target-imedia.nl':
                return 'public';
                break;
            case 's4aaas.target-imedia.nl':
                return 's4aaas';
                break;
            default:
                return 'public';
                break;
        }
    }

}
