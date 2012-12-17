<?php

class S4AAAS_Model_Db_IntitutionDao extends Zend_Db_Table_Abstract {
    protected $_name = 'INSTITUTIONS';
    protected $_primary = array('ID');
    protected $_sequence = FALSE;
    
    public function getName() { return $this->_name; }
}

/**
 * Class that represents a user for creating (etc) and authentication
 * 
 * @author Herbert Kruitbosch
 */
class S4AAAS_Model_Institution extends S4AAAS_Model_Abstract {
    private static $_dao=null;
    protected $_magicName = 'INSTITUTIONS';
    protected $_magicPrimary = array('ID');
    protected $_magicProperties = array(
        'ID'=>null,
        'MONK_ID'=>null,
        'SHORT_NAME'=>null,
        'LONG_NAME'=>null
    );
    
    public static function fetchById($id) {
        return self::fetchByField(self::getDao(), 'ID', $id, new S4AAAS_Model_Institution());
    }

	public static function fetchByMonkID($monkId)
	{
		return self::fetchByField(self::getDao(), 'MONK_ID', $monkId, new S4AAAS_Model_Institution());
	}
	
    public static function getDao() {
        if(self::$_dao===null) self::$_dao = new S4AAAS_Model_Db_IntitutionDao();
        return self::$_dao;
    }
    
    public function hasPassword($password) {
        if($password===null || $password==='') return false;
        return $password === $this->getPassword();
    }
	
	public function getCollection($monkId)
	{
		$select = S4AAAS_Model_Collection::getDao()->select()
			->from(array('c' => 'COLLECTIONS'))
			->where('c.INSTITUTION_ID'. ' = ? ', $this->getId())
			->where('c.MONK_ID'. ' = ? ', $monkId);
		
		$row = S4AAAS_Model_Collection::getDao()->fetchRow($select);
		
		if ($row)
		{
			return new S4AAAS_Model_Collection($row);
		}
		else
		{
			return null;
		}
	}
    
    
    private static function fetchAllRecursive(&$count, $lastTable='institutions') {
        $dao = self::getDao();
        $institution = new S4AAAS_Model_Institution();
        $collection = new S4AAAS_Model_Collection();
        $book = new S4AAAS_Model_Book();
        $page = new S4AAAS_Model_Page();
        
        $select = $dao->select()
                ->setIntegrityCheck(false)
                ->from(array('i' => 'INSTITUTIONS'), $institution->prefixedFields('i', 'institution_'));
        if($lastTable!=='institutions')
		{
                $select->join(array('c' => 'COLLECTIONS'),
						'c.INSTITUTION_ID = i.ID',
                        $collection->prefixedFields('c', 'collection_')
				);
                if($lastTable!=='collections')
				{
                    $select->join(array('b' => 'BOOKS'),
							'b.COLLECTION_ID = c.ID',
                            $book->prefixedFields('b', 'book_')
					);
                    if($lastTable!=='books')
					{
                        $select->join(array('p' => 'PAGES'),
								'p.BOOK_ID = b.ID',
                                $page->prefixedFields('p', 'page_')
						);
                        $select->order(array('i.ID', 'c.ID', 'b.ID', 'p.ID'));
                    }
					else
					{
						$select->order(array('i.ID', 'c.ID', 'b.ID'));
					}
                }
				else
				{
					$select->order(array('i.ID', 'c.ID'));
				}
        }
		else
		{
			$select->order(array('i.ID'));
		}
		
        $_rows = $dao->fetchAll($select);
        
		$rows = array();
		foreach($_rows as $_row)
		{
			$rows[] = $_row->toArray();
		}
		
		$count=0;
        $collaterated = $institution->collect($rows, 'S4AAAS_Model_Institution', 'institution_');
        if($lastTable!=='institutions')
		{
            foreach($collaterated as &$institution)
			{
                $institution->collections = 
                    $institution->collect($rows, 'S4AAAS_Model_Collection', 'collection_', $institution->from, $institution->to);
                if($lastTable!=='collections')
				{
                    foreach($institution->collections as &$collection)
					{
                        $collection->books = 
                            $collection->collect($rows, 'S4AAAS_Model_Book', 'book_', $collection->from, $collection->to);
                        $count += count($collection->books);
                        if($lastTable!=='books')
						{
                            foreach($collection->books as &$book)
							{
                                $book->pages = 
                                    $book->collect($rows, 'S4AAAS_Model_Page', 'page_', $book->from, $book->to);
                            }
                        }
                    }
                }
            }
        }
        return $collaterated;
    }
    
    public static function fetchAllWithCollectionsAndBooks(&$count) {
        return self::fetchAllRecursive($count, 'books');
    }
    
    public static function fetchAllWithCollectionsAndBooksAndPages(&$count) {
        return self::fetchAllRecursive($count, 'pages');
    }
}

?>