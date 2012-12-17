<?php

class S4AAAS_Model_Db_CollectionDao extends Zend_Db_Table_Abstract {
    protected $_name = 'COLLECTIONS';
    protected $_primary = array('ID');
    protected $_sequence = FALSE;
    
    public function getName() { return $this->_name; }
}

/**
 * Class that represents a user for creating (etc) and authentication
 * 
 * @author Herbert Kruitbosch
 */
class S4AAAS_Model_Collection extends S4AAAS_Model_Abstract {
    private static $_dao=null;
    protected $_magicName = 'COLLECTIONS';
    protected $_magicPrimary = array('ID');
    protected $_magicProperties = array(
        'ID'=>null,
        'MONK_ID'=>null,
        'INSTITUTION_ID'=>null,
        'SHORT_NAME'=>null,
        'LONG_NAME'=>null
    );

    
    public function __construct($data=null) {
        if($data!==null) {
			if (isset($data['ID']))
			{
				$this->setId($data['ID']);
			}
            $this->setMonkId($data['MONK_ID']);
            $this->setInstitutionId($data['INSTITUTION_ID']);
			$this->setShortName($data['SHORT_NAME']);
			$this->setLongName($data['LONG_NAME']);
        }
    }
	
	public function getInstitution()
	{
		$institutionId = $this->getInstitutionId();
		
		$institution = S4AAAS_Model_Institution::fetchById($institutionId);
		
		return $institution;
	}
	
	
	public function getBook($monkId)
	{
		$select = S4AAAS_Model_Book::getDao()->select()
			->from(array('b' => 'BOOKS'))
			->where('b.COLLECTION_ID'. ' = ? ', $this->getId())
			->where('b.MONK_DIR'. ' = ? ', $monkId);
		
		$row = S4AAAS_Model_Collection::getDao()->fetchRow($select);
		
		if ($row)
		{
			return new S4AAAS_Model_Book($row);
		}
		else
		{
			return null;
		}
	}
	
    
    public static function fetchById($id) {
        return self::fetchByField(self::getDao(), 'ID', $id, new S4AAAS_Model_Collection());
    }
   
	public static function fetchByMonkID($monkId)
	{
		return self::fetchByField(self::getDao(), 'MONK_ID', $monkId, new S4AAAS_Model_Collection());
	}
	
    public static function getDao() {
        if(self::$_dao===null) self::$_dao = new S4AAAS_Model_Db_CollectionDao();
        return self::$_dao;
    }
    
    public function hasPassword($password) {
        if($password===null || $password==='') return false;
        return $password === $this->getPassword();
    }
}

?>