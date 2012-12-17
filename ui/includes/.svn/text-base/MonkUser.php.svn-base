<?php

class MonkUser {

    /* the instance of the object */
    private static $instance;

    /* the nonce used for oAuth similar login */
    private $nonce = null;

    /* the token belonging to the user */
    private $token = null;

    /* the username of the user */
    private $username = null;

    /* the role of the user, obsolete */
    private $role = null;

    /* books for which the user is allowed to work on */
    private $allowedBooks = array();

    /* list of pages for current user he can work on. Obsolete */
    private $pages = array();

    /* result of the list_pages call */
    public $listPagesResult;

    /* singleton method */
    public static function getInstance()
    {
        if(!isset(self::$instance))
        {
            $c = __CLASS__;
            self::$instance = new $c;
        }

        return self::$instance;
    }
    
    public function setNonce($nonce)
    {
        $this->nonce = $nonce;
    }
    
    public function setToken($token)
    {
        $this->token = $token;
    }
    
    public function getNonce() {
        return $this->nonce;
    }

    public function getToken() {
        if(!$this->token)
        {
            if(isset($_SESSION['token']))
            {
                $this->token = $_SESSION['token'];
                return $_SESSION['token'];
            }
        }
        return $this->token;
    }

    public function getUsername() {

        if(isset($this->username))
            return $this->username;
        if(isset($_SESSION['MonkUser']))
            if($_SESSION['MonkUser'] instanceof MonkUser)
                return $_SESSION['MonkUser']->getUsername();
        
        return null;
    }

    public function getRole()
    {
        if(isset($this->role))
            return $this->role;
        if(isset($_SESSION['MonkUser']))
            if($_SESSION['MonkUser'] instanceof MonkUser)
                return $_SESSION['MonkUser']->getRole();
        return null;
    }

    public function setRole($role)
    {
        $this->role = $role;
    }

    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function addBook($book) {
        $this->allowedBooks[] = $book;
    }

    public function getBooks()
    {
        return $this->allowedBooks;
    }

    public function store()
    {
        $_SESSION['MonkUser'] = $this;
    }

    public function addPage($page)
    {
        $this->pages[] = $page;
    }

    public function getPages()
    {
        return $this->pages;
    }

    public function listPagesAsOptions()
    {
        $pages = array();
        if(isset($_SESSION['MonkUser']))
            $result = $_SESSION['MonkUser']->listPagesResult;
        else
            return $pages;

        foreach($result->institutions as $institution)
        {
            foreach($institution->collections as $collection)
            {
                foreach($collection->book as $book)
                {
                    foreach($book->pages as $pageSet)
                    {
                        foreach($pageSet as $pSet)
                        {
                            foreach($pSet->page as $page)
                            {
                                if($book->hn == '')
                                    $pages[$page->id] = $book->name . ' - pagina ' . $page->number;
                                else
                                    $pages[$page->id] = $book->hn . ' - pagina ' . $page->number;
                            }
                        }
                    }
                }
            }
        }
        return $pages;
    }

    public function getBookByPageId($book_id)
    {
        $returnBook = array();
        $result = $_SESSION['MonkUser']->listPagesResult;

        foreach($result->institutions as $institution)
        {
            foreach($institution->collections as $collection)
            {
                foreach($collection->book as $book)
                {
                    foreach($book->pages as $pageSet)
                    {
                        foreach($pageSet as $pSet)
                        {
                            foreach($pSet->page as $page)
                            {
                                if($book_id == $page->id)
                                {
                                    $returnBook['institution'] = $institution->id;
                                    $returnBook['collection'] = $collection->id;
                                    $returnBook['book'] = $book->id;
                                    $returnBook['page'] = $page->id;
                                    $returnBook['pageNumber'] = $page->number;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $returnBook;
    }


}


