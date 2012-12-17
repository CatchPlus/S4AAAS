<?php
/**
 * Created by De Ontwikkelfabriek.
 * User: Postie
 * Date: 7/27/11
 * Time: 11:50 AM
 * Copyright 2011 De Ontwikkelfabriek
 */
 
class AllowedBook {

    public $id;

    public $userRole;

    public $firstPage;

    public $lastPage;

    public $startPage;

    public $institution;

    public $collection;

    public $name;

    public $humanName;

    function __construct($id, $userRole, $firstPage, $lastPage, $startPage = 1)
    {
        $this->id        = $id;
        $this->userRole  = $userRole;
        $this->firstPage = $firstPage;
        $this->lastPage  = $lastPage;
        $this->startPage = $startPage;
    }


}
