<?php
class Monk {
    /**
     * @var integer start from row number offset
     *      default 0
     *      min 0
     *      max unlimited
     *      multi value: mo
     */
    private $offset;
    
    /**
     * @var integer shows the number of rows
     *      
     */
    private $rows;

    /* arrays holding the book collection */
    private $institutions = array();
    
    private $collections = array();
    
    private $books = array();

    private $match;
    
    private $url;
    
    private $XML;                       // holding the XML as simple_xml_object
    
    private $log;                       // to log output to files

    private $rawXML;                    // the real raw XML, for debugging used only
    
    function __construct($url, $rows = 5)
    {
        $this->url = $url;
        $this->rows = $rows;
        $this->log = new KLogger('monk.log', Config::LOGGING);


        $this->XML = $this->getXML();
        $this->collectOptions();
        
    }
    
    public function getOffset() {
        return $this->offset;
    }

    public function getRows() {
        return $this->rows;
    }

    public function getInstitutions() {
        return $this->institutions;
    }

    public function getCollections() {
        return $this->collections;
    }

    public function getBooks() {
        return $this->books;
    }

    public function getMatch() {
        return $this->match;
    }

    public function setOffset($offset) {
        $this->offset = $offset;
    }

    public function setRows($rows) {
        $this->rows = $rows;
    }

    public function addInstitutions($institution) {
        $this->institutions[] = $institution;
    }

    public function addCollections($collection) {
        $this->collections[] = $collection;
    }

    public function addBooks($book) {
        $this->books[] = $book;
    }

    public function setMatch($match) {
        $this->match = $match;
    }
    
    public function __toString() {
        return $this->url;
    }

    /*
     * Collect the data from the Monk server and make a simple xml object from it
     * Throws an exception on failure
     */
    private function getXML()
    {
        try {
            return SendRequest::getResult($this->url);
        } catch (Exception $e) {
            $template = new Smarty();
            $template->assign("exceptionMessage", $e->getMessage());
            $template->assign('exceptionCode', $e->getCode());
            $template->assign('exceptionLine', $e->getLine());
            $template->assign('exceptionFile', $e->getFile());
            $template->assign('exceptionTraceAsString', $e->getTraceAsString());
            $template->display('exception.tpl');
            exit;
        }
    }

    /**
     * Returns the collection formatted as a JSON string
     * preserves the hierarchy
     * @return string A JSON string
     */
    public function collectionAsJson()
    {
//        echo "<pre>";
//        print_r($this->XML);
//        echo "</pre>";
        $json = <<<JSON
[
    {"title": "Instituten", "isFolder": true, "key": "instituten",  "activate": true,
        "children": [
JSON;
        $k = 0;
        foreach($this->XML->institution as $institution)
        {
            $institutionName = !empty($institution->institution_hn) ? utf8_decode($institution->institution_hn) : $institution->id;
            $json .= <<<JSON

            {"title": "$institutionName", "isFolder": true, "key": "$institution->institution_id", "expand": true,
                "children": [
JSON;
            $j = 0;
            foreach($institution->collection as $collection) {
                $collectionName = !empty($collection->collection_hn) ? utf8_decode($collection->collection_hn) : utf_decode($collection->collection_id);
                $json .= <<<JSON


                    {"title": "$collectionName", "isFolder": true, "key": "$collection->collection_id", "expand": true,
                        "children": [
JSON;
                $i = 0;
                foreach($collection->book as $book) {
                    $bookName = !empty($book->book_hn) ? utf8_decode($book->book_hn) : utf8_decode($book->book_name);
                    $json .= <<<JSON

                            {"title": "$bookName", "key": "$book->book_id" }
JSON;
                    $i++;
                    if($i != count($collection->book))
                        $json .= ',';
                }
                $json .= <<<JSON

                        ]
                    }
JSON;
                $j++;
                if($j != count($institution->collection))
                    $json .= ',';
            }
            $json .= <<<JSON

                ]
            }
JSON;
            $k++;
            if($k != count($this->XML->institution))
                $json .= ',';
        }
    $json .= <<<JSON

        ]
    }
]
JSON;
        return $json;
    }

    /*
     * Collects the data but loses the hierarchy
     */
    private function collectOptions()
    {
        $data = $this->XML;
        
        $ins = array();
        $cols = array();
        $bks = array();
        foreach($data->institution as $institution) 
        {   
            /* parent */
            $ins["$institution->institution_id"] = "$institution->institution_id";
            foreach($institution->collection as $collection)
            {
                $cols["$collection->collection_id"] = "$collection->collection_id";
                foreach($collection->book as $book) 
                {
                    $bks["$book->book_id"] = "$book->book_name";
                }

            }
        }
        
        $this->institutions = $ins;
        $this->collections = $cols;
        $this->books = $bks;
    }
}
?>
