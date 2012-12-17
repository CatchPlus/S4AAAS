<?php

class S4AAAS_Model_Db_PagelockDao extends Zend_Db_Table_Abstract {
    protected $_name = 'PAGELOCKS';
    protected $_primary = array('PAGE_ID');
    protected $_sequence = FALSE;
    
    public function getName() { return $this->_name; }
}

/**
 * Class that represents a user for creating (etc) and authentication
 * 
 * @author Herbert Kruitbosch
 */
class S4AAAS_Model_Pagelock extends S4AAAS_Model_Abstract {
    private static $_dao=null;
    protected $_magicName = 'PAGELOCKS';
    protected $_magicPrimary = array('PAGE_ID');
    protected $_magicProperties = array(
        'PAGE_ID'=>null,
        'USER_ID'=>null,
        'LOCKED_UNTIL'=>null
    );

	
	public function __construct($data=null) {
        if($data!==null)
		{
            $this->setPageId($data['PAGE_ID']);
            $this->setUserId($data['USER_ID']);
			$this->setLockedUntil($data['LOCKED_UNTIL']);
        }
    }
	
	/*
	 * check if the current lock date is expired
	 */
	public function isValid()
	{
		$lockedUntil = new DateTime($this->getLockedUntil());
        $now = new DateTime();
        if($now < $lockedUntil)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	
    public static function fetchById($id) {
        return self::fetchByField(self::getDao(), 'ID', $id, new S4AAAS_Model_Page());
    }

    public static function getDao() {
        if(self::$_dao===null) self::$_dao = new S4AAAS_Model_Db_PageDao();
        return self::$_dao;
    }


}

?>