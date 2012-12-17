<?php
/**
 * Created by De Ontwikkelfabriek.
 * User: Postie
 * Date: 7/19/11
 * Time: 1:21 PM
 * Copyright 2011 De Ontwikkelfabriek
 * @description: Shows the index page
 */
 
class IndexPage extends PageView
{

//    private $smarty;

    function __construct()
    {
        $user = MonkUser::getInstance();
        $smartVars = array();
//        $smartVars["books"]      = $this->getBooks();
        $smartVars["books"]      = $user->getBooks();
        $smartVars['pages']      = $user->listPagesAsOptions();
        $smartVars["trainFiles"] = $this->getTrainFiles();
        $smartVars["username"] = $_SESSION["username"];
        
        if(isset($_SESSION[Config::PAGE_STORE]))
            unset($_SESSION[Config::PAGE_STORE]);

        if(isset($_SESSION['errorMessage']))
        {
            $smartVars["errorMessage"] = $_SESSION['errorMessage'];
            unset($_SESSION['errorMessage']);
        }
        else
        {
            $smartVars["errorMessage"] = array();
        }
        $smartVars['role'] = MonkUser::getInstance()->getRole();


        $this->smarty = new SmartyPage('index.tpl', $smartVars);
    }

//    public function render()
//    {
//        $this->smarty->render();
//    }

    private function getBooks()
    {
        $user = MonkUser::getInstance();
        $books = array();
        foreach($user->getBooks() as $book)
        {
            if($book instanceof AllowedBook)
            {
                $books[$book->id] = $book->id . ", pag. " . $book->firstPage . " t/m " . $book->lastPage;
            }
        }
        return $books;
    }

    private function getTrainFiles()
    {
        $user = MonkUser::getInstance();
        if($user->getRole() != UserRole::VERIFIER)
            return array();
        $return = array();
        $files = MonkLocal::getTrainFiles();
        foreach($files as $file)
        {
            $dummy = explode('.', $file);
            $return[$dummy[0] . '.' . $dummy[1]] = $dummy[0] . ': ' . $dummy[1] . ' (' . date("d-m-y", $dummy[2]) . ')';
        }
        return $return;
    }
}
