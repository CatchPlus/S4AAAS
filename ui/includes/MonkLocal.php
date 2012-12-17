<?php
/**
 * Created by De Ontwikkelfabriek.
 * User: Postie
 * Date: 7/27/11
 * Time: 3:41 PM
 * Copyright 2011 De Ontwikkelfabriek
 */
 
abstract class MonkLocal {

    static function getTrainFiles()
    {
        /* get a list of traindata from directory */
        $files = array();
        if($handle = opendir(ROOT . Config::SAVE_DIR))
        {
            while(false !== ($file = readdir($handle)))
            {
                if($file != '.' && $file != '..')
                {
                    $files[] = str_ireplace('.csv', '.' . filemtime(ROOT . Config::SAVE_DIR . DIRECTORY_SEPARATOR . $file) . '.csv', $file);
                }

            }
        }
        sort($files);
        return $files;
    }

    static function readLocalTrainData($file)
    {
        $data = array();
        if(stripos($file, '.csv') === false)
            $file .= '.csv';

        if(file_exists(ROOT . Config::SAVE_DIR . DIRECTORY_SEPARATOR . $file))
        {
            $fp = fopen(ROOT . Config::SAVE_DIR . DIRECTORY_SEPARATOR . $file , 'r');
            while($line = fgetcsv($fp, 1000, ','))
            {
                $label = new Label();
                $label->x    = $line[1];
                $label->y    = $line[2];
                $label->w    = $line[3];
                $label->h    = $line[4];
                $label->word = $line[5];

                $data[$line[0]][] = $label;
            }
        }
        
        return $data;

    }

}
