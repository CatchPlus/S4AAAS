<?php

class BooksController extends S4AAAS_Controller_Abstract
{

	private $_pageSetSize = 10; // size of page set returned when requestion some pages from a book
	protected $dbConn;

	public function init()
    {
		define('appini_path', APPLICATION_PATH.'/configs/application.ini');
		$appini = parse_ini_file(appini_path);
		$this->dbConn = mysql_connect($appini['resources.db.params.host'], $appini['resources.db.params.username'], $appini['resources.db.params.password']) or exit('No database connection.');
		mysql_select_db($appini['resources.db.params.dbname'], $this->dbConn) or exit(mysql_error());	
    }

    public function listAction() {
//        $count = 10;
        $institutions = S4AAAS_Model_Institution::fetchAllWithCollectionsAndBooks($count);
        $this->view->institutions=$institutions;
        $this->view->count = $count;
		$this->view->status = "OK";
    }
    
    public function listPagesAction() {
		if(!$this->userHasRight())
		{
			$this->view->status = "AUTHENTICATION FAILURE";
			return;
		}
		
		$setFound = false;
		$books = $this->user->fetchTranscribeBooks();

		while(!$setFound && (count($books) > 0))
		{
			$bookTmp = array_splice($books, rand(0, count($books)-1), 1);
			$book = $bookTmp[0];
			
			$count = $this->_pageSetSize; //size of pageset
			$pages = $this->getPageSet($book, $this->user);
//			$pages = $this->getPageSetFromBookForTranscription($book, $count);
			if (count($pages)>0)
			{
				$totalPageCount = 0;
				foreach ($pages as $set)
				{
					$totalPageCount += count($set);
				}
				
				if ($totalPageCount > 0)
					$setFound = true;
			}
		}
		
//		if ($setFound == false)
//		{
//			$this->view->status = "NO PAGE SET FOUND";
//			return;
//		}
		if ($setFound == false)
		{
			$this->view->status = "OK";
			$this->view->pagesAvailable = false;
			return;
		}
		else
		{	
	//		$this->view->pageCount = count($pages);
	//		$pagesSegmented = $this->segmentPageSet($pages);
	//		$this->view->pages = $pagesSegmented;

			$this->view->pagesAvailable = true;

			$this->view->pageCount = $totalPageCount;		

			$this->view->pages = $pages;

			$this->view->book = $book;
			$collection = $book->getCollection();
			$this->view->collection = $collection;
			$institution = $collection->getInstitution();
			$this->view->institution = $institution;

			$this->view->status = "OK";
		}
    }
	
	public function listBookPagesAction()
	{
        if(!$this->userHasRight())
		{
			$this->view->status = "AUTHENTICATION FAILURE";
			return;
		}
		
		$institutionId = $this->getRequest()->getParam('institution', null);
		$collectionId = $this->getRequest()->getParam('collection', null);
		$bookMonkId = $this->getRequest()->getParam('book', null);
		
		if (!$institutionId || !$collectionId || !$bookMonkId)
		{
			$this->view->status = "NOT ENOUGH PARAMS SPECIFIED";
			return;
		}
		
		$institution = S4AAAS_Model_Institution::fetchByMonkID($institutionId);
		if (!$institution)
		{
			$this->view->status = "INSTITUTION NOT FOUND";
			return;				
		}

		$collection = $institution->getCollection($collectionId);
		if (!$collection)
		{
			$this->view->status = "COLLECTION NOT FOUND";
			return;			
		}
		
		$book = $collection->getBook($bookMonkId);
		
		if (!$book)
		{
			$this->view->status = "BOOK NOT FOUND";
			return;			
		}

		
		$this->view->book = $book;
		$this->view->collection = $collection;
		$this->view->institution = $institution;
		
		$count = $this->_pageSetSize; //size of pageset
		$pagesSegmented = $this->getPageSet($book, $this->user);
		$this->view->pageCount = 10;

		$this->view->pages = $pagesSegmented;		
		$this->view->status = "OK";
	}
	
	private function getPageSet($book, $user)
	{
		$bookMonkDir = $book->getMonkDir();
		$userMonkId = $user->getMonkId();
		$permission = $this->AncoQuery('SELECT * FROM (SELECT USERINST.PERMISSIONS, 1 PAGE_FROM, 99999 PAGE_TO FROM USERS JOIN USERINST ON USERS.ID = USERINST.USER_ID AND USERINST.DELETED = \'NO\' JOIN INSTITUTIONS ON USERINST.INSTITUTION_ID = INSTITUTIONS.ID JOIN COLLECTIONS ON INSTITUTIONS.ID = COLLECTIONS.INSTITUTION_ID JOIN BOOKS ON COLLECTIONS.ID = BOOKS.COLLECTION_ID WHERE USERS.MONK_ID = \'' . $userMonkId . '\' AND BOOKS.MONK_DIR = \'' . $bookMonkDir . '\' AND USERINST.PERMISSIONS > 2 UNION SELECT USERCOL.PERMISSIONS, 1 PAGE_FROM, 99999 PAGE_TO FROM USERS JOIN USERCOL ON USERS.ID = USERCOL.USER_ID AND USERCOL.DELETED = \'NO\' JOIN COLLECTIONS ON USERCOL.COLLECTION_ID = COLLECTIONS.ID JOIN BOOKS ON COLLECTIONS.ID = BOOKS.COLLECTION_ID WHERE USERS.MONK_ID = \'' . $userMonkId . '\' AND BOOKS.MONK_DIR = \'' . $bookMonkDir . '\' AND USERCOL.PERMISSIONS > 2 UNION SELECT USERBOOK.PERMISSIONS BOOK_PERMISSION, USERBOOK.PAGE_FROM, USERBOOK.PAGE_TO FROM USERS JOIN USERBOOK ON USERS.ID = USERBOOK.USER_ID AND USERBOOK.DELETED = \'NO\' JOIN BOOKS ON USERBOOK.BOOK_ID = BOOKS.ID WHERE USERS.MONK_ID = \'' . $userMonkId . '\' AND BOOKS.MONK_DIR = \'' . $bookMonkDir . '\' AND USERBOOK.PERMISSIONS > 2)SUB LIMIT 1');
	
		$pages = array();
		if (isset($permission[0]))
		{
			$pages = $this->AncoQuery('SELECT PAGES.PAGE_NO PAGES FROM PAGES JOIN BOOKS ON PAGES.BOOK_ID = BOOKS.ID WHERE BOOKS.MONK_DIR = \'' . $bookMonkDir . '\' AND PAGES.PAGE_NO BETWEEN ' . $permission[0]['PAGE_FROM'] . ' AND ' . $permission[0]['PAGE_TO'] . '', true);
		}
		
		
		$bookId = $book->getId();
		
		$count = $this->_pageSetSize;
		$pageCount = count($pages);
		$numberOfSets = round($pageCount/$count);
		$setNumber = rand(0, $numberOfSets-1);
		
		$set = $this->getSetFromPages($bookId, $pages, $count, $setNumber*$count);
			
		// if no available pages are found from the given setNumber on, just search from beginning of book
		if (count($set) == 0)
		{
			$set = $this->getSetFromPages($bookId, $pages, $count, 0);			
		}
				
		$pagesSegmented = $this->segmentPageSet($set);
		
		return $pagesSegmented;
	}
	
	
	private function getSetFromPages($bookId, $pages, $count, $startIdx)
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
			
			$page = S4AAAS_Model_Page::fetchByBookAndPage($bookId, $pages[$idx]);
			
			if (!$page->isLocked()) // check if page is not already locked
			{
				$set[] = $page;
				$curCount++;
			}
			$idx++;
		}

		return $set;		
	}

	public function listTranscribeBooksAction()
	{
        if(!$this->userHasRight())
		{
			$this->view->status = "AUTHENTICATION FAILURE";
			return;
		}
		
		$books = $this->user->fetchTranscribeBooks();
		
		$this->view->status = 'OK';
		$this->view->books = $books;
		$this->view->user = $this->user;
		
	}


	/*
	 * split a list of pages into segments with subsequent pages
	 */
	private function segmentPageSet($pages)
	{
		if (empty($pages))
		{
			return array(array());
		}
					
		$segments = array();
		$segment = array();
		
		$segment[] = array_shift($pages);
		$lastPageNo = $segment[0]->getPageNo();

		foreach ($pages as $page)
		{			
			// page is not a direct followup of the last page
			if ($page->getPageNo() > ($lastPageNo + 1))
			{
				$segments[] = $segment;
				$segment = array();
			}

			$segment[] = $page;
			$lastPageNo = $page->getPageNo();
		}
		
		$segments[] = $segment;
				
		return $segments;
		
	}
	
	private function getPageSetFromBookForTranscription($book, $count)
	{
		$pages = $book->getRandomAvailablePageSet($count);
		return $pages;
	}
	

	
	/*
	 * FUNCTION BELOW ARE NOT NEEDED
	 * 
	public function fixFoutAction()
	{
		return; //deze functie zou niet meer uitgevoerd hoeven te worden
		
		set_time_limit(0);

		//get all pages
		$bookId = 1;
		$pages = S4AAAS_Model_Page::getAllPages($bookId);
				
		$lastBookMonkId = '';
		$book = null;
		$bookId = 0;
		foreach ($pages as $page)
		{
			$pageNavisId = $page->getNavisId();
			$bookMonkId = substr($pageNavisId, 0, -5);
			
			if ($bookMonkId != $lastBookMonkId)
			{
				$book = S4AAAS_Model_Book::fetchByNavisId($bookMonkId);
				if (!$book)
				{
					$bookId = 0;
				}
				else
				{
					$bookId = $book->getId();
				}
				$lastBookMonkId = $bookMonkId;
			}
			
			$page->setBookId($bookId);
			$page->update();
			
		}		
	}
	
	public function insertscriptAction()
	{
		return; //deze functie zou niet meer uitgevoerd hoeven te worden

		set_time_limit(0);
	
		$fileName = '';
		$lastFile = '';
		$fileCount = 90;
		while (true)
		{
			if ($fileCount == 120)
			{
				break;
			}
			
			if (file_exists('../linesfiles/x'.$fileCount))
			{
				$fileName = '../linesfiles/x'.$fileCount;
			}
			else if (file_exists('../linesfiles/x0'.$fileCount))
			{
				$fileName = '../linesfiles/x0'.$fileCount;
			}
			else if (file_exists('../linesfiles/x00'.$fileCount))
			{
				$fileName = '../linesfiles/x00'.$fileCount;				
			}
			else
			{
				break;
			}
			
			$this->insertLinesFileToDb($fileName);
			$lastFile = $fileName;
			
			$fileCount += 1;
		}
		
		echo "Last file: ".$lastFile;
		
		$this->view->page = array(null);
	}
	
	private function insertLinesFileToDb($filename)
	{
		return; //deze functie zou niet meer uitgevoerd hoeven te worden
		
		$fp = fopen($filename, 'r');
	
		$page = null;
		$lastPageNavisId = '';

		$count = 0;
		while ($line = fgetcsv($fp, 0, chr(9)))
		{
			$messedupFirstPart = explode(' ', $line[0]);
			$FULL_NAME = $messedupFirstPart[0]; // full name (including page, line etc.)
			$NAVIS_ID = $messedupFirstPart[1]; // e.g. navis-NL-blalbla
			$PAGE = $line[1]; // page in the book
			$LINE = $line[2]; // line of the page
			$Y_TOP = $line[3];
			$Y_BOT = $line[4];
			$pageNavisId = $NAVIS_ID.'_'.$PAGE;
			
			// insert page into db if not already inserted
			if (!($lastPageNavisId == $pageNavisId))
			{
				$bookMonkId = substr($pageNavisId, 0, -5);
				$book = S4AAAS_Model_Book::fetchByNavisId($bookMonkId);
				
				$data = array(
					'BOOK_ID' => $book->getId(),
					'NAVIS_ID' => $pageNavisId,
					'PAGE_NO' => $PAGE,
					'ORIG_WIDTH' => 0,
					'TRANSCRIPT' => ''
				);
				
				$page = S4AAAS_Model_Page::fetchByNavisId($pageNavisId);
				if (!$page)
				{
					$page = new S4AAAS_Model_Page($data);
					$page->insert();
					$page = S4AAAS_Model_Page::fetchByNavisID($pageNavisId); //new fetch required to get the inserted Id
				}
				
				$lastPageNavisId = $pageNavisId;
			}

			// insert line into db
			$data = array(
				'PAGE_ID' => $page->getId(),
				'LINE_NO' => $LINE,
				'Y_TOP' => $Y_TOP,
				'Y_BOT' => $Y_BOT,
				'TRANSCRIPT' => ''
			);
			$line = new S4AAAS_Model_Line($data);
			$line->insert();
		}
		fclose($fp);
	}
*/

}

