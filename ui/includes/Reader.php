<?php
/**
 * Created by De Ontwikkelfabriek.
 * User: Postie
 * Date: 7/7/11
 * Time: 4:51 PM
 * Copyright 2011 De Ontwikkelfabriek
 */
 
abstract class Reader {

    protected $institution;

    protected $collection;

    protected $page;

    protected $line;

    protected $bookId;


    function __construct()
    {
        if(isset($_REQUEST['bookid']))
            $this->bookId = $_REQUEST['bookid'];
        if(isset($_REQUEST['line']))
        {
            $this->line = $_REQUEST['line'];
            return;
        }
        if(isset($_REQUEST['page']))
        {
            $this->page = $_REQUEST['page'];
            return;
        }
        elseif(isset($_REQUEST['bookid']))
        {
            $this->bookId = $_REQUEST['bookid'];
            return;
        }
        else
            throw new Exception("There is no pageid or bookid");


        // determine what kind of reader we should have
        // for now, only monk remote reader

    }


    public function asJSON()
    {
        if(isset($_SESSION[Config::PAGE_STORE]))
        {
            if($_SESSION[Config::PAGE_STORE] instanceof Page)
            {
                return $_SESSION[Config::PAGE_STORE]->lineIDsAsJson();
            }
            else
            {
                throw new Exception("Oops, did you delete your cookies?");
            }

        }
        else
        {
            throw new Exception("Oops, did you delete your cookies?");
        }
    }


    public function lineLabelsAsJSON()
    {
        if(!isset($_REQUEST['line']))
            return array();
        if(!isset($_SESSION[Config::PAGE_STORE]))
            return array();
        if(!$_SESSION[Config::PAGE_STORE] instanceof Page)
            return array();
        return $_SESSION[Config::PAGE_STORE]->lineLabelsAsJSON($this->line);

    }

    

    abstract public function readPage();

}
