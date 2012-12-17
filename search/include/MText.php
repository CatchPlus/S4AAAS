<?php
/**
 * Created by De Ontwikkelfabriek.
 * User: Postie
 * Date: 6/28/11
 * Time: 2:32 PM
 * Copyright 2011 De Ontwikkelfabriek
 */
 
class MText {


    /**
     * Returns translated string if type($text) = string
     * Returns translated array if type($text) = array
     * */

    private static $translations = array();
    private static $language = 'NL';

    public static function _($text)
    {
        /* load the ini file into a session. faster? */

        if(isset($_COOKIE['language']))
            self::$language = $_COOKIE['language'];

        if(file_exists(ROOT . 'language/translations.ini'))
            self::$translations = parse_ini_file(ROOT . 'language/translations.ini', true);
        else
            return $text;

        switch(true)
        {
            case is_string($text):
                return self::translateString($text);
            case is_array($text):
                return self::translateArray($text);
            default:
                return $text;
        }
    }

    private static function translateString($text)
    {
        if(isset(self::$translations[self::$language]["{$text}"]))
            return self::$translations[self::$language]["{$text}"];
        else
            return $text;
    }

    private static function translateArray($text)
    {
        $return = array();

        foreach($text as $key=>$value)
        {
            if(isset(self::$translations[self::$language]["{$value}"]))
                $return[$key] = self::$translations[self::$language]["{$value}"];
            else
                $return[$key] = $value;
        }

        return $return;
    }
}
