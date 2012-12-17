<?php

class S4AAAS_Model_Db_UserDao extends Zend_Db_Table_Abstract {
    protected $_name = 'USERS';
    protected $_primary = array('ID');
    protected $_sequence = FALSE;
    
    public function getName() { return $this->_name; }
}

/**
 * Class that represents a user for creating (etc) and authentication
 * 
 * @author Herbert Kruitbosch
 */
class S4AAAS_Model_User extends S4AAAS_Model_Abstract {
    private static $_dao=null;
    protected $_magicName = 'USERS';
    protected $_magicPrimary = array('ID');
    protected $_magicProperties = array(
        'ID' => null,
        'MONK_ID' => '',
        'PASSWORD' => '',
        'PERMISSIONS' => '',
        'BYUSER_ID' => '',
        'TIMESTAMP' => '',
        'DISABLED' => false,
        'DELETED' => false
    );
    
    public static function fetchById($id) {
        return self::fetchByField(self::getDao(), 'ID', $id, new S4AAAS_Model_User());
    }
    
    public static function fetchByMonkId($MonkId) {
        return self::fetchByField(self::getDao(), 'MONK_ID', $MonkId, new S4AAAS_Model_User());
    }
	
	public function canTranscribeABook()
	{
		if ($this->getPermissions() >= 3)
		{
			return true;
		}

		$userId = $this->getId();
        $dao = self::getDao();
        $select = $dao->select()
                ->setIntegrityCheck(false)
                ->from(array('ui' => 'USERINST'), array())
				->where('ui.USER_ID = ? ', $userId)
				->where('ui.PERMISSIONS > ? ', 2)
				->where('ui.DELETED = ?', 'no')
				->limit(1)
        ;
		$rows = self::getDao()->fetchAll($select);
		if (count($rows) > 0)
		{
			return true;
		}
		
        $select = $dao->select()
                ->setIntegrityCheck(false)
                ->from(array('ui' => 'USERCOL'), array())
				->where('ui.USER_ID = ? ', $userId)
				->where('ui.PERMISSIONS > ? ', 2)
				->where('ui.DELETED = ?', 'no')
				->limit(1)
        ;
		$rows = self::getDao()->fetchAll($select);
		if (count($rows) > 0)
		{
			return true;
		}
		
        $select = $dao->select()
                ->setIntegrityCheck(false)
                ->from(array('ui' => 'USERBOOK'), array())
				->where('ui.USER_ID = ? ', $userId)
				->where('ui.PERMISSIONS > ? ', 2)
				->where('ui.DELETED = ?', 'no')
				->limit(1)
        ;
		$rows = self::getDao()->fetchAll($select);
		if (count($rows) > 0)
		{
			return true;
		}
		
		return false;
}
	
	/*
	 * Fetch all books the user is allowed to transcribe
	 */
	public function fetchTranscribeBooks()
	{
		$userPermissions = $this->getPermissions();
		if ($userPermissions > 2)
		{
			return S4AAAS_Model_Book::getAllBooks();
		}
		
		$userId = $this->getId();
        $dao = self::getDao();
        $select1 = $dao->select()
                ->setIntegrityCheck(false)
                ->from(array('ui' => 'USERINST'), array())
				->join(array('i' => 'INSTITUTIONS'), 'ui.INSTITUTION_ID = i.ID', array())
                ->join(array('c' => 'COLLECTIONS'), 'i.ID = c.INSTITUTION_ID', array())
				->join(array('b' => 'BOOKS'), 'c.ID = b.COLLECTION_ID')
				->where('ui.USER_ID = ? ', $userId)
				->where('ui.PERMISSIONS > ? ', 2)
				->where('ui.DELETED = ?', 'no')
        ;

        $select2 = $dao->select()
                ->setIntegrityCheck(false)
                ->from(array('uc' => 'USERCOL'), array())
                ->join(array('c' => 'COLLECTIONS'), 'c.ID = uc.COLLECTION_ID', array())
				->join(array('b' => 'BOOKS'), 'c.ID = b.COLLECTION_ID')
				->where('uc.USER_ID = ? ', $userId)
				->where('uc.PERMISSIONS > ? ', 2)
				->where('uc.DELETED = ?', 'no')
        ;
		
        $select3 = $dao->select()
                ->setIntegrityCheck(false)
                ->from(array('ub' => 'USERBOOK'), array())
				->join(array('b' => 'BOOKS'), 'b.ID = ub.BOOK_ID')
				->where('ub.USER_ID = ? ', $userId)
				->where('ub.PERMISSIONS > ? ', 2)
				->where('ub.DELETED = ?', 'no')
        ;
		
		$select = $dao->select()
				->union(array($select1, $select2, $select3))
		;

		$rows = self::getDao()->fetchAll($select);
		$books = array();
		foreach ($rows as $row)
		{
			$books[] = new S4AAAS_Model_Book($row);
		}
		return $books;
	}


	public static function getDao() {
        if(self::$_dao===null) self::$_dao = new S4AAAS_Model_Db_UserDao();
        return self::$_dao;
    }
    
    public function hasPassword($password) {
        if($password===null || $password==='') return false;
        return $this->encriptedPassword($password) === $this->getPassword();
    }
	
	public function encriptedPassword($password)
	{
		$makeMonkPwPath = Zend_Registry::get('makeMonkPwPath');
		$command = "$makeMonkPwPath -enc '$password'";

		$output = array();
		$return = -1;
		$result = exec($command, $output, $return);

		return $result;
	}
}

?>