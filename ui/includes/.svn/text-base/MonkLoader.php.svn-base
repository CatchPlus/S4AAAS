<?php
/**
 * Created by De Ontwikkelfabriek.
 * User: Postie
 * Date: 7/27/11
 * Time: 4:29 PM
 * Copyright 2011 De Ontwikkelfabriek
 */
 
class MonkLoader extends Reader {


    /**
     * Read from local file
     * @return void
     */
    public function readPage()
    {
        $pageId = null;

        
        if(isset($_REQUEST['page']))
        {
            $dummy = explode('.', $_REQUEST['page']);
            $username = $dummy[0];
            $pageId   = $dummy[1];
        }
        else
            throw new Exception('There is no page set');

        $page = new Page($pageId);

        $lines = MonkLocal::readLocalTrainData($_REQUEST['page']);
        foreach($lines as $lineId=>$labels)
        {
            $line = new Line($lineId);
            foreach($labels as $label)
            {
                if($label instanceof Label)
                    $line->add($label);
            }

            $page->add($line);
        }
        $_SESSION[Config::PAGE_STORE] = $page;
    }

    public function removeFile()
    {
        $page = '';
        if(isset($_GET['page']))
            $page = $_GET['page'];
        else
            throw new Exception("There is no page to delete");

        $user = MonkUser::getInstance();
        if($user->getRole() == UserRole::VERIFIER)
        {
            $file = ROOT . Config::SAVE_DIR . DIRECTORY_SEPARATOR . $page . '.csv';
            if(file_exists($file))
            {
                unlink($file);
                echo 'Bestand is verwijderd';
            }
            else
            {
                throw new Exception("File not found!");
            }
        }
        else
        {
            throw new Exception("No permission to delete file");
        }

    }

}
