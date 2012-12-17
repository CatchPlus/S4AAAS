<?php


/**
 * Description of ResultController
 *
 * @author Postie
 */
class ResultController {
    
    private $template;
    
    private $total;
    
    private $send;
    
    private $result;
    
    public function __construct()
    
    {
        $this->template = new Smarty();
        //$this->template->debugging = true;

        try {

            $this->result = new Result($_GET);

        } catch (Exception $e) {

            $this->template->assign("exceptionMessage", $e->getMessage());
            $this->template->assign('exceptionCode', $e->getCode());
            $this->template->assign('exceptionLine', $e->getLine());
            $this->template->assign('exceptionFile', $e->getFile());
            $this->template->assign('exceptionTrace', $e->getTraceAsString());

            $this->template->display('exception.tpl');

            exit;
        }

        if($this->result->validateURL())
        {
            // Try to get the total and send fields from the XM
            try
            {
                $this->total = (int) $this->result->getXMLField('total');  /* need to convert these fields to integer */
                $this->send = (int) $this->result->getXMLField('send');    /* otherwise Smarty handles them as strings */

            // well, these fields should be there, something went wrong.
            } catch (Exception $e) {

                $this->template->assign("exceptionMessage", $e->getMessage());
                $this->template->assign('exceptionCode', $e->getCode());
                $this->template->assign('exceptionLine', $e->getLine());
                $this->template->assign('exceptionFile', $e->getFile());
                $this->template->assign('exceptionTrace', $e->getTraceAsString());

                $this->template->display('exception.tpl');

                exit;
            }

            /** get the parsed data */
            $data = $this->result->getRawData();
            /* not really RAW XML bus as simple xml object. So raw XML in a PHP coat */


            $XML = $this->result->getRawXML();
//     echo "<pre>";
//     print_r($XML);
//     echo "</pre>";

            /* set the pagination stuff */
            $this->setPagination();

            $host = PathUtils::getHostType();
            
            if(isset($_SESSION['token'])){
                $this->template->assign('tokenIsSet', true);
                $this->template->assign('username', $_SESSION['username']);
            }else{
                $this->template->assign('tokenIsSet', false);
            }
            
            if($host == 'expert')
                $this->template->assign('showTopMenu', false);
            else
                $this->template->assign('showTopMenu', true);
            
            $this->template->assign('baseurl', PathUtils::getUrlPath(true));
            $this->template->assign('imgurl', Config::IMG_URL . Config::IMG_SUFFIX);

            $this->template->assign('searchTerm', $this->result->getSearchTerm());

            $this->template->assign('uri', str_replace('&', '&amp;', $this->result->getUri()));
            $this->template->assign('url', str_replace('&', '&amp;', $this->result->getUrl()));

            $this->template->assign('wordzoneAnnotations', $XML->wordzone_annotations);
            $this->template->assign('lineAnnotations', $XML->line_annotations);
            $this->template->assign('pageAnnotations', $XML->page_annotations);

            $this->template->assign('pageschoice', Config::$pages);
//            $this->template->assign('localurl', Config::LOCAL_SUFFIX);
//            $this->template->assign('no_image', Config::NO_IMAGE);
            
            $this->template->assign('BookIdLookup', Config::$bookIDLookup);

            $this->template->assign('types', Config::$annotations);

            $this->template->display('templates/result.tpl');
        } else {
            /* no valid url */
            // for now $this->validateURL will always retur true
            $this->template->assign("Error:", $this->result->getErrors());
            $this->template->display('templates/error.tpl');;
        }
    }
    
    private function setPagination()
    {
        $paginator = new Paginator();
        /* set number of rows per page */
        $paginator->setLimit($this->result->getRows());
        /* set total number of results */
        $paginator->setTotal($this->total);
        /* set the current offset */
        $paginator->setOffset($this->result->getOffset());
        /* marge is number of pages before the 'end' is updated */
        $paginator->setMarge(Config::MARGE_PAGINATION);
        /* maximum number of results per page */
        $paginator->setMaxPages(Config::MAX_PAGINATION);
        /* and set all the pages, make the calculations */
        $paginator->setPages();

        /* assign calculated values to the smarty template */
        $this->template->assign('rows', $this->result->getRows());
        $this->template->assign('offset', $this->result->getOffset());

        $this->template->assign('startPage', $paginator->getStartPage());
        $this->template->assign('endPage', $paginator->getEndPage());
        $this->template->assign('currentPage', $paginator->getCurrentPage());

        $this->template->assign('total', $this->total);
        $this->template->assign('totalPages', ceil($this->total / $this->result->getRows()));
    }
    
}

