<?php

class S4AAAS_Model_Db_ImageLookupDao extends Zend_Db_Table_Abstract {
    protected $_name = 'IMAGELOOKUP';
    protected $_primary = array('ID');
    protected $_sequence = FALSE;
    
    public function getName() { return $this->_name; }
}

/**
 * Class that represents a user for creating (etc) and authentication
 * 
 * @author Herbert Kruitbosch
 */
class S4AAAS_Model_ImageLookup extends S4AAAS_Model_Abstract {
    private static $_dao=null;
    protected $_magicName = 'IMAGELOOKUP';
    protected $_magicPrimary = array('ID');
    protected $_magicProperties = array(
        'ID' => null,
        'IMAGE_ID' => '',
        'TYPE' => '',
        'OBJECT_ID' => '',
        'VALID_UNTIL' => ''
    );
    
    public function __construct($secondsValid = 3600) {
                    $this->setImageId($this->genToken());

		$date = new DateTime();
		$date->modify("+" . $secondsValid . " seconds");
		$this->setValidUntil($date->format("Y-m-d H:i:s"));
    }

	public static function pageImageAvailable($line)
	{
		$page = $line->getPage();
		$book = $page->getBook();
		
		// add leading zeroes to page no.
		$pageNo = $page->getPageNo();
		while (strlen($pageNo) < 4)
		{
			$pageNo = '0'.$pageNo;
		}
		
		$imagePath = Zend_Registry::get('pageImagesPath');
		$imagePath .= '/'.$book->getBookDir();
		$imagePath .= '/'.$pageNo;
		$imagePath .= '.jpg';
		
		if (!file_exists($imagePath))
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	
    public static function genToken($length=16) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $string = '';    
        for ($p = 0; $p < $length; $p++) {
            $string .= $characters[mt_rand(0, strlen($characters)-1)];
        }
        return $string;
    }
    
    public static function fetchByImageId($imageId) {
            return self::fetchByField(self::getDao(), 'IMAGE_ID', $imageId, new S4AAAS_Model_ImageLookup());
    }
	
    public static function getDao() {
        if(self::$_dao===null) self::$_dao = new S4AAAS_Model_Db_ImageLookupDao();
        return self::$_dao;
    }
    
    public static function storeAll($imageLookups) {
        $dao = self::getDao();
        $adapter = $dao->getAdapter();
        $query = "";
        foreach($imageLookups as $imageLookup) {
            if($query!=="") $query .= ", ";
            $query.= $adapter->quoteInto("(?,", $imageLookup['IMAGE_ID']);
            $query.= $adapter->quoteInto("?,",  $imageLookup['TYPE']);
            $query.= $adapter->quoteInto("?,",  $imageLookup['OBJECT_ID']);
            $query.= $adapter->quoteInto("?)\n",  $imageLookup['VALID_UNTIL']);
        }
        
        $query = "INSERT INTO IMAGELOOKUP (IMAGE_ID,TYPE,OBJECT_ID,VALID_UNTIL) VALUES\n" . $query . ";";
        //print_r($query);
        $adapter->query($query);
    }
}

?>