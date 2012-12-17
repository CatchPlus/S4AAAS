<?php

class S4AAAS_Model_Db_PageDao extends Zend_Db_Table_Abstract {
    protected $_name = 'PAGES';
    protected $_primary = array('ID');
    protected $_sequence = FALSE;
    
    public function getName() { return $this->_name; }
}

/**
 * Class that represents a user for creating (etc) and authentication
 * 
 * @author Herbert Kruitbosch
 */
class S4AAAS_Model_Page extends S4AAAS_Model_Abstract {
    private static $_dao=null;
    protected $_magicName = 'PAGES';
    protected $_magicPrimary = array('ID');
    protected $_magicProperties = array(
        'ID'=>null,
        'BOOK_ID'=>null,
        'PAGE_NO'=>null,
        'ORIG_WIDTH'=>null,
        'TRANSCRIPT'=>null,
        'NAVIS_ID'=>null
    );
	
	// time for a page lock before being expired
	protected $_pageLockTime = null;
    
    public function __construct($data=null) {
        if($data!==null) {
			if (isset($data['ID']))
			{
				$this->setId($data['ID']);
			}

            $this->setBookId($data['BOOK_ID']);
            $this->setNavisId($data['NAVIS_ID']);
			$this->setPageNo($data['PAGE_NO']);
			$this->setOrigWidth($data['ORIG_WIDTH']);
			$this->setTranscript($data['TRANSCRIPT']);
        }
    }
	
	public function getBook()
	{
		$bookId = $this->getBookId();
		
		$book = S4AAAS_Model_Book::fetchById($bookId);
		
		return $book;
	}
	
	public function getLines()
	{
		$pageId = $this->getId();

		$select = S4AAAS_Model_Line::getDao()->select()
				->from(array('l' => 'LINES'))
                ->where('l.PAGE_ID = ?', $pageId);
		
		$rows = self::getDao()->fetchAll($select);
		
		$lines = array();
		foreach($rows as $row)
		{
			$lines[] = new S4AAAS_Model_Line($row);
		}

		return $lines;
	}

	
	public function getAllPages($bookId = -1)
	{
		$select = self::getDao()->select()
				->from(array('p' => 'PAGES'));
		
		if ($bookId >= 0)
		{
			$select->where('p.BOOK_ID'. ' = ? ', $bookId);
		}
		
		$rows = self::getDao()->fetchAll($select);
		
		$pages = array();
		foreach($rows as $row)
		{
			$pages[] = new S4AAAS_Model_Page($row);
		}

		return $pages;
	}
	
	public function getDbLabels()
	{
		$select = S4AAAS_Model_Label::getDao()->select()
				->from(array('l' => 'LABELS'))
                ->where('l.LINE_ID' . ' = ?', $lineId);
		
		$rows = self::getDao()->fetchAll($select);
		
		$labels = array();
		foreach($rows as $row)
		{
			$labels[] = new S4AAAS_Model_Label($row);
		}

		return $labels;
	}
	
	public function getTrieLabels()
	{
		$labels = S4AAAS_Trie_Query::getPageLabels($this);
		return $labels['wordzones'];
	}

    public static function getPageTranscripts($navisIds) {
        if(count($navisIds)===0) return array();
        $dao = self::getDao();
        $select = $dao->select()
                ->setIntegrityCheck(false)
                ->from(array('p' => 'PAGES'))
                ->joinLeft(array('l' => 'LINES'), 'l.PAGE_ID = p.ID', array('LINE_ID'=>'l.id'))
        ;
        
        $where = '';
        foreach($navisIds as $navisId) {
            if($where!=='') $where .= ',';
            $where .= $dao->getAdapter()->quoteInto("?", $navisId);
        }
        $where = 'p.NAVIS_ID IN (' . $where . ')';
        //$where .= ' AND (`l`.`LINE_NO` = SELECT MIN(`l2`.`LINE_NO`) FROM `LINES` AS `l2` WHERE `l2`.`PAGE_ID`=`l`.`PAGE_ID` GROUP BY `l2`.`PAGE_ID`))';
        $select->where($where);
        $select->where('l.LINE_NO = 1');
        $rows = self::getDao()->fetchAll($select);
        $results = array();
        foreach($rows as $row) $results[$row['NAVIS_ID']] = array('TRANSCRIPT' => $row['TRANSCRIPT'], 'LINE_ID'=>$row['LINE_ID'], 'PAGE_ID'=>$row['ID']);
        return $results;
    }
	
	// get pages before and after this page
	public function getPrevNext()
	{
		$book = $this->getBook();
		$pages = $book->getPagesOrdered();
		
		$prevNext = array('prev' => null, 'next' => null);
		
        $imageLookups = array();
        $imageLookupUntil = new DateTime();
        $imageLookupUntil->modify("+3600 seconds");
        $imageLookupUntil = $imageLookupUntil->format("Y-m-d H:i:s");		

		for ($idx = 0; $idx < count($pages); $idx++)
		{
			if ($pages[$idx]->getId() == $this->getId())
			{
				if ($idx > 0)
				{
					$prevImageLookup = array('VALID_UNTIL' => $imageLookupUntil, 'IMAGE_ID' => S4AAAS_Model_ImageLookup::genToken(), 'TYPE'=>'PAGE', 'OBJECT_ID'=>  $pages[$idx-1]->getId());
					$imageLookups[] = $prevImageLookup;
					$prevNext['prev'] = array();
					$prevNext['prev']['page'] = $pages[$idx-1];
					$prevNext['prev']['image'] = $prevImageLookup['IMAGE_ID'];
				}
				if ($idx < count($pages)-1)
				{
					$nextImageLookup = array('VALID_UNTIL' => $imageLookupUntil, 'IMAGE_ID' => S4AAAS_Model_ImageLookup::genToken(), 'TYPE'=>'PAGE', 'OBJECT_ID'=>  $pages[$idx+1]->getId());
					$imageLookups[] = $nextImageLookup;
					$prevNext['next'] = array();
					$prevNext['next']['page'] = $pages[$idx+1];
					$prevNext['next']['image'] = $nextImageLookup['IMAGE_ID'];
				}
				
				break;
			}
		}
		
        S4AAAS_Model_ImageLookup::storeAll($imageLookups);
		
		return $prevNext;
	}
	
    public static function fetchById($id) {
        return self::fetchByField(self::getDao(), 'ID', $id, new S4AAAS_Model_Page());
    }
	
    public static function fetchByNavisId($navisId) {
            return self::fetchByField(self::getDao(), 'NAVIS_ID', $navisId, new S4AAAS_Model_Page());
    }
	
	public static function fetchByBookAndPage($bookId, $pageNo)
	{
		$dao = self::getDao();
		$select = $dao->select()
				->from( array('p' => 'PAGES'))
				->where("p.BOOK_ID = $bookId")
				->where("p.PAGE_NO = $pageNo")
		;
		
		$rows = $dao->fetchAll($select);
		
		if (count($rows) > 0)
		{
			$page = new S4AAAS_Model_Page($rows[0]);
			return $page;
		}
		else
		{
			return null;
		}
	}
    
    public static function getPageLockDao() {
        return new Zend_Db_Table(array(
            'name' => 'PAGELOCKS',
            'primary' => 'PAGE_ID',
            'sequence' => false
        ));
    }
 
    public static function getDao() {
        if(self::$_dao===null) self::$_dao = new S4AAAS_Model_Db_PageDao();
        return self::$_dao;
    }
	
	/*
	 *  lock the current page for the given user
	 */
	public function setPageLock($user, $secondsValid = 3600)
	{
		$userId = $user->getId();
		$pageId = $this->getId();
		$this->clearLock();
		$lockedUntil = new DateTime();
		$lockedUntil->modify("+" . $secondsValid . " seconds");
		
		$pagelock = new S4AAAS_Model_Pagelock(array('PAGE_ID' => $pageId, 'USER_ID' => $userId, 'LOCKED_UNTIL' => $lockedUntil->format("Y-m-d H:i:s")));
		
		$pagelock->insert();
	}
	
	/*
	 * clear the lock for the current page
	 */
	public function clearLock()
	{
		$pageId = $this->getId();
		
		$dao = self::getPageLockDao();
		$where = $dao->getAdapter()->quoteInto('PAGE_ID = ?', $pageId);
		$dao->delete($where);
		
		return true;
	}
	
	/*
	 * check if the current page is locked
	 */
	public function isLocked()
	{
		$pageId = $this->getId();
		
        $dao = self::getPageLockDao();
        $select = $dao->select()
            ->setIntegrityCheck(false)
            ->where('PAGE_ID = ?', $pageId);
		
		$rows = self::getDao()->fetchAll($select);

		if (count($rows) == 1)
		{
			$pagelock = new S4AAAS_Model_Pagelock($rows[0]);
			if (!$pagelock->isValid())
			{
				$this->clearLock();
				return false;
			}
			return true;
		}
		elseif(count($rows) == 0)
		{
			return false;
		}
		else
		{
			throw new Exception('Multiple locks for one page!?');
		}

	}
	
	/*
	 * check if the current page is locked for the given user
	 */
	public function isLockedForUser($user)
	{
		$pageId = $this->getId();
		$userId = $user->getId();
		
        $dao = self::getPageLockDao();
        $select = $dao->select()
            ->setIntegrityCheck(false)
            ->where('PAGE_ID = ?', $pageId)
			->where('USER_ID = ?', $userId);
		
		$rows = self::getDao()->fetchAll($select);

		if (count($rows) == 1)
		{
			$pagelock = new S4AAAS_Model_Pagelock($rows[0]);
			if (!$pagelock->isValid())
			{
				$this->clearLock();
				return false;
			}
			return true;
		}
		elseif(count($rows) == 0)
		{
			return false;
		}
		else
		{
			throw new Exception('Multiple locks for one page!?');
		}
	}
    
    public static function cleanLocks() {
        $dao = self::getPageLockDao();
        $select = $dao->select();
		$rows = $dao->fetchAll($select);
		foreach($rows as $row)
		{
			$pagelock = new S4AAAS_Model_Pagelock($row);
			if (!$pagelock->isValid())
			{
				$where = $dao->getAdapter()->quoteInto('PAGE_ID = ?', $pagelock->getPageId());
				$dao->delete($where);
			}
		}
    }
	
	
	public function createImageToken()
	{
		$imageLookup = new S4AAAS_Model_ImageLookup();
		$imageLookup->setType('PAGE');
		$imageLookup->setObjectId($this->getId());
		$insertId = $imageLookup->insert();
		$imageLookup->setId($insertId);
		return $imageLookup;
	}

}

?>