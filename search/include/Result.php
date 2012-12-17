<?php
//require_once('include/Validation.php');
//require_once('include/Constants.php');

class Result
{
    /**
     *
     * @var array The _GET data 
     */
    private $data;
    
    /**
     * @var integer row Parameter
     */
    
    /**
     * @var integer Contains the number of rows to show per page
     */
    private $rows;
    
    /**
     * @var integer Show rows from offset and beyond
     */
    private $offset;
    
    /**
     * @var string Part of the URL, containg the institutions, collections and books later on wordzonetypes and annotations too 
     * passed on to the external source
     */
    private $uri = array();
    
    /**
     * @var string The string being searched for
     */
    private $searchTerm;
    
    /**
     * @var Object XML object with the retrieved XML
     */
    private $xml;
    
    /**
     * Holding the local URL
     * @var type 
     */
    private $url = "";
    
    /**
     * Contains the errors encountered
     * @var array
     */
    private $errors = array();
    
    /**
     * Total number of results
     * @var integer
     */
    private $total = 0;
    
    private $log;
    
    function __construct($data)
    {
        $this->log = new KLogger("Result.log", Config::LOGGING);
        $this->data   = $data;
        $this->rows   = Config::DEFAULT_ROWS;
        $this->offset = Config::DEFAULT_OFFSET;
        
        
        $this->buildURI();
        
        $this->setRowsAndOffset();
        $this->setKeyword();
        $this->setXML();
    }
    
    /**
     * Let's build the URL for retrieval
     */
    private function buildURI() 
    {
        $fields = Config::$fields;
        $uri =  array();
        foreach($fields as $fieldName=>$fieldType)
        {   
            if(isset($this->data["$fieldName"])) {
                $uri = $this->getData($fieldName, $fieldType);
                if(!empty($uri))
                    $this->uri = array_merge($this->uri, $uri);
            }
        }
        if(isset($this->data['match']) && isset(Config::$matches[$this->data['match']]))
            $this->uri['match'] = $this->data['match'];

        $this->uri = array_map('urlencode', $this->uri);
    }
    
    /** 
     * Unused
     * @param type $field
     * @return type 
     */
    public function getXMLField($field)
    {
        if(!isset($this->xml->$field)) 
        {
            throw new Exception("XML field '$field' not found!");
        } else {
            return $this->xml->$field;
        }
    }
    
    /* convert the data fields to &dataName=dataValue 
     * Updated: puts the dataname=value in an array, creates uri from this later
     *          with http_build_query
     * Special case: 'all'. This sets empty array. 'all' only for
     * - collections
     * - books
     * - institutions
     * The default values of these parameters are all
     *
     * update:
     * - No more all, just no key at all when value is empty
     *   Relying on server default. "are you sure? (yes/no)" <-- this is a joke
     */
    private function getData($name, $type)
    {
        if(isset($this->data["$type"]))
        {
            /* smarty makes checkboex as array, have to convert URL to array
             * or check if it 's an array and then don't do an implode. But I like implosions
             */
            if(!is_array($this->data["$type"]))
                $this->data["$type"] = array($this->data["$type"]);
            $this->url  .= "&{$name}[]=" . implode("|", $this->data["$type"]);

            if(in_array('all', $this->data["$type"])) 
            {
                /* 'all' is defined, this is default so can just return empty string */
                /* just don't name an instituion 'all' */
                return array();
            }
            
            /* @todo validate allowed institutions */
            return array($type => implode("|", $this->data["$type"]));
        } else {
            /* parameter not set */
            return array();
        }
    }
    
    /**
     * Sets the &rows= and &offset= parameters
     */
    private function setRowsAndOffset()
    {
        /* rows have a max of 100, according to the Monk docs */
        if(isset($this->data['rows']))
        {
            $options = array(
                'options'   => array(
                    'default'   => Config::DEFAULT_ROWS,
                    'min_range' => 0,
                    'max_range' => 100
                )
            );
            $this->rows = filter_var($this->data['rows'], FILTER_VALIDATE_INT, $options);
        }
        
        /* offset doesn't have a max, according to the Monk docs */
        if(isset($this->data['offset']))
        {
            $options = array(
                'options' => array(
                    'default'   => Config::DEFAULT_OFFSET,
                    'min_range' => 0,
                )
            );
            $this->offset = filter_var($this->data['offset'], FILTER_VALIDATE_INT, $options);
            
        }
        
        $this->uri['offset'] = $this->offset;
        $this->uri['rows'] = $this->rows;
    }
    
    
    /**
     * Sets the keyword. If no keyword is found, redirect back to index
     */
    private function setKeyword()
    {
        if(isset($this->data['needle']) && (!(empty($this->data['needle']))))
        {
            $this->searchTerm = filter_var($this->data['needle'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH|FILTER_FLAG_STRIP_LOW);
        } else {
            /* no search term, redirect back to index */
            header('Location' . PathUtils::getUrlPath(true));
        }
    }
    
    /**
     * Sets the XML as simple xml string
     */
    private function setXML()
    {
        try {
			$uri = urlencode($this->searchTerm) . '?' .  urldecode(http_build_query($this->uri));
            $this->xml = SendRequest::getResult(Config::BASEURL . Config::RESULT_SUFFIX . $uri);
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Returns true if URL validates
     * @return boolean
     */
    public function validateURL()
    {
        /* TODO: Implement */
        return true;
    }

    /**
     * Return the error messages as array
     * @return array 
     */
    public function getErrors()
    {
        /* TODO: Implement */
        return $this->errors;
    }
    
    /**
     * Doesn't actually return valid XML but simple xml object
     * @return Simple XML Object
     */ 
    public function getRawXML() 
    {
        return $this->xml;
    }
    
    /**
     * Returns the search term (needle)
     * @return string
     */
    public function getSearchTerm()
    {
        return $this->searchTerm;
    }
    
    /**
     * Returns the URI part, contains the variable datafields in an array
     * @return array
     */
    public function getUri()
    {
        return $this->uri;
    }
    
    /**
     * Returns the numner of rows
     * @return integer 
     */
    public function getRows()
    {
        return $this->rows;
    }
    
    /**
     * Debug only
     * @return array
     */
    public function getRawData()
    {
        return $this->data;
    }
    
    public function getURL()
    {
        return $this->url;
    }
    
    public function getOffset()
    {
        return $this->offset;
    }
    
}
