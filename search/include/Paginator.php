<?php


/**
 * Description of Paginator
 *
 * @author Postie
 */
class Paginator {
    //put your code here
    
    /**
     *
     * @var integer number of rows per page
     */
    private $limit;
    
    /**
     *
     * @var integer total number of rows
     */
    private $total;
    
    /**
     *
     * @var integer Starting number
     */
    private $offset;
    
    /**
     *
     * @var integer current page number
     */
    private $currentPage;
    
    private $startPage;
    
    private $endPage;
    
    private $marge;
    
    private $maxPages;

    public function setPages()
    {
        /* determine startpage
         * determine endpage
         */
        $this->currentPage = ($this->offset / $this->limit) + 1;
        $this->startPage = max(1, ($this->currentPage - $this->marge) + 1);
        $this->endPage = min(ceil($this->total / $this->limit), max(($this->currentPage + $this->marge), $this->maxPages));
    }
    
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }
    
    public function getLimit()
    {
        return $this->limit;
    }
    
    public function setTotal($total)
    {
        $this->total = $total;
    }
    
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }
    
    public function setMarge($marge)
    {
        $this->marge = $marge;
    }
    
    public function getStartPage()
    {
        return $this->startPage;
    }
    
    public function getEndPage()
    {
        return $this->endPage;
    }
    
    public function getCurrentPage()
    {
        return $this->currentPage;
    }
    
    public function setMaxPages($pages)
    {
        $this->maxPages = $pages;
    }
    
}

