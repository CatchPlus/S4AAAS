<?php

class S4AAAS_Model_Db_BookDao extends Zend_Db_Table_Abstract {
    protected $_name = 'BOOKS';
    protected $_primary = array('ID');
    protected $_sequence = FALSE;
    
    public function getName() { return $this->_name; }
}

class S4AAAS_Model_Db_UserbookDao extends Zend_Db_Table_Abstract {
    protected $_name = 'USERBOOK';
    protected $_primary = array('USER_ID');
    protected $_sequence = FALSE;
    
    public function getName() { return $this->_name; }
}

/**
 * Class that represents a user for creating (etc) and authentication
 * 
 * @author Herbert Kruitbosch
 */
class S4AAAS_Model_Book extends S4AAAS_Model_Abstract {
    private static $_dao=null;
    protected $_magicName = 'BOOKS';
    protected $_magicPrimary = array('ID');
    protected $_magicProperties = array(
        'ID'=>null,
		'BOOK_DIR'=>null,
        'COLLECTION_ID'=>null,
        'MONK_ID'=>null,
        'MONK_DIR'=>null,
        'SHORT_NAME'=>null,
        'LONG_NAME'=>null,
        'HANDLE_URL'=>null,
        'SHEAR'=>null,
        'NAVIS_ID'=>null,
    );
    
    public function __construct($data=null) {
        if($data!==null) {
			if (isset($data['ID']))
			{
				$this->setId($data['ID']);
			}
			if (isset($data['BOOK_DIR']))
			{
				$this->setBookDir($data['BOOK_DIR']);
			}
			
			$this->setCollectionId($data['COLLECTION_ID']);
            $this->setMonkId($data['MONK_ID']);
            $this->setMonkDir($data['MONK_DIR']);
			$this->setShortName($data['SHORT_NAME']);
			$this->setLongName($data['LONG_NAME']);
            $this->setHandleUrl($data['HANDLE_URL']);
            $this->setShear($data['SHEAR']);
            $this->setNavisId($data['NAVIS_ID']);
        }
    }
	
	public function getAllBooks()
	{
		$select = self::getDao()->select()
				->from(array('b' => 'BOOKS'));
		
		$rows = self::getDao()->fetchAll($select);
		
		$books = array();
		foreach($rows as $row)
		{
			$books[] = new S4AAAS_Model_Book($row);
		}

		return $books;
	}
	
	public function getPageFromTo($userId)
	{
		$dao = new S4AAAS_Model_Db_UserbookDao();
		$select = $dao->select()
				->from(array('ub' => 'USERBOOK'))
                ->where('ub.USER_ID = ?', $userId)
				->where('ub.BOOK_ID = ?', $this->getId())
				->limit(1);
		
		$rows = self::getDao()->fetchAll($select);
		if (count($rows) > 0)
		{
			return array('from' => $rows[0]['PAGE_FROM'], 'to' => $rows[0]['PAGE_TO']);
		}
		else
		{
			$pages = $this->getPages();
			if (!$pages || count($pages) == 0)
				return array('from' => 0, 'to' => 0);
			
			return array('from' => $pages[0]->getPageNo(), 'to' => end($pages)->getPageNo());
		}
	}
	
    public static function fetchById($id)
	{
        return self::fetchByField(self::getDao(), 'ID', $id, new S4AAAS_Model_Book());
    }

	public static function fetchByMonkID($monkId)
	{
		return self::fetchByField(self::getDao(), 'MONK_ID', $monkId, new S4AAAS_Model_Book());
	}

	public static function fetchByMonkDir($monkDir)
	{
		return self::fetchByField(self::getDao(), 'MONK_DIR', $monkDir, new S4AAAS_Model_Book());
	}
	
	public static function fetchByNavisId($navisId)
	{
		return self::fetchByField(self::getDao(), 'NAVIS_ID', $navisId, new S4AAAS_Model_Book());
	}
	
    public static function getDao()
	{
        if(self::$_dao===null) self::$_dao = new S4AAAS_Model_Db_BookDao();
        return self::$_dao;
    }
	
	public static function getRandomBook()
	{
		$select = self::getDao()->select()->limit(1)->order('rand()');
		
		$row = self::getDao()->fetchRow($select);
		
		return new S4AAAS_Model_Book($row);
	}
	
	public function getRandomAvailablePageSet($count = 10)
	{
		$pages = $this->getPagesOrdered();
		
		if (count($pages) == 0) //book doesn't contain any pages
		{
			return array();
		}
		
		$pageCount = count($pages);
		$numberOfSets = round($pageCount/$count);
		$setNumber = rand(0, $numberOfSets-1);
		
		$set = $this->getSetFromPages($pages, $count, $setNumber*$count);
		
		// if no available pages are found from the given setNumber on, just search from beginning of book
		if (count($set) == 0)
		{
			$set = $this->getSetFromPages($pages, $count, 0);			
		}
		
		return $set;
	}
	
	private function getSetFromPages($pages, $count, $startIdx)
	{		
		$set = array();
		$curCount = 0;
		$idx = $startIdx;
		while ($curCount < $count)
		{
			
			if (!isset($pages[$idx])) // no more pages in book
			{
				break;
			}
			
			$page = $pages[$idx];
			
			if (!$page->isLocked()) // check if page is not already locked
			{
				$set[] = $page;
				$curCount++;
			}
			$idx++;
		}

		return $set;		
	}
	
	public function getCollection()
	{
		$collectionId = $this->getCollectionId();
		
		$collection = S4AAAS_Model_Collection::fetchById($collectionId);
		
		return $collection;
	}
	
	public function getPage($pageNumber)
	{
		$bookId = $this->getId();
		
		$select = S4AAAS_Model_Page::getDao()->select()
				->from(array('p' => 'PAGES'))
                ->where('p.BOOK_ID' . ' = ?', $bookId)
				->where('p.PAGE_NO' . ' = ?', $pageNumber)
				->limit(1);
		
		$rows = self::getDao()->fetchAll($select);
		
		if (count($rows) == 0)
		{
			return null;
		}
		else
		{
			return new S4AAAS_Model_Page($rows[0]);
		}
		
	}
	
	public function getPages($count = -1)
	{
		$bookId = $this->getId();
		
		$select = S4AAAS_Model_Page::getDao()->select()
				->from(array('p' => 'PAGES'))
                ->where('p.BOOK_ID' . ' = ?', $bookId)
				->limit($count);
		
		$rows = self::getDao()->fetchAll($select);
		
		if ($count < 0)
		{
			$count = count($rows);
		}
		
		$pages = array();
		$idx = 0;
		foreach($rows as $row)
		{
			if ($idx < $count)
			{
				$pages[] = new S4AAAS_Model_Page($row);
			}
			else
			{
				break;
			}
			
			$idx += 1;
		}
		return $pages;
	}
	
	
	public function getPagesOrdered()
	{
		$bookId = $this->getId();
		
		$select = S4AAAS_Model_Page::getDao()->select()
				->from(array('p' => 'PAGES'))
                ->where('p.BOOK_ID' . ' = ?', $bookId)
				->order('PAGE_NO ASC');
		
		$rows = self::getDao()->fetchAll($select);
		
		$pages = array();
		foreach($rows as $row)
		{
			$pages[] = new S4AAAS_Model_Page($row);
		}
		return $pages;
	}
	
	public function pageCount()
	{
		$select = S4AAAS_Model_Page::getDao()->select()  
			   ->from(array('p' => 'PAGES'), '*, COUNT(*) AS COUNT')  
			   ->where('p.BOOK_ID =? ', $this->getId());  
		$row = S4AAAS_Model_Page::getDao()->fetchRow($select); 
		
		return $row['COUNT'];
	}
}

?>