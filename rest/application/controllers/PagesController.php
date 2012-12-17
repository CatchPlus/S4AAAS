<?php

class PagesController extends S4AAAS_Controller_Abstract
{

    public function init()
    {
		define('appini_path', APPLICATION_PATH.'/configs/application.ini');
		$appini = parse_ini_file(appini_path);
		$this->dbConn = mysql_connect($appini['resources.db.params.host'], $appini['resources.db.params.username'], $appini['resources.db.params.password']) or exit('No database connection.');
		mysql_select_db($appini['resources.db.params.dbname'], $this->dbConn) or exit(mysql_error());	

    }
	
	public function nextprevAction()
	{
		$institutionId = $this->getRequest()->getParam('institution', null);
		$collectionId = $this->getRequest()->getParam('collection', null);
		$bookMonkId = $this->getRequest()->getParam('book', null);
		$pageNo = $this->getRequest()->getParam('page', null);
				
		if (!$institutionId || !$collectionId || !$bookMonkId || !$pageNo)
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
		
		$page = $book->getPage($pageNo);
		if (!$page)
		{
			$this->view->status = "PAGE NOT FOUND";
			return;
		}
		
		//@TODO set right image url in view
		$prevNext = $page->getPrevNext();
		$this->view->prevPage = $prevNext['prev']['page'];
		$this->view->prevImage = $prevNext['prev']['image'];
		$this->view->nextPage = $prevNext['next']['page'];
		$this->view->nextImage = $prevNext['next']['image'];
		
		$this->view->status = "OK";	
	}
	
	public function requesttranscriptionAction()
	{
		if (!$this->userHasRight())
		{
			$this->view->status = "AUTHENTICATION FAILURE";
			return;
		}
		
		
		$institutionId = $this->getRequest()->getParam('institution');
		$collectionId = $this->getRequest()->getParam('collection');
		$bookDir = $this->getRequest()->getParam('book');
		$pageNo = $this->getRequest()->getParam('page');
				
		if (!$institutionId || !$collectionId || !$bookDir || !$pageNo)
		{
			$this->view->status = "NOT ENOUGH PARAMS SPECIFIED";
			return;
		}
		
		$institution = S4AAAS_Model_Institution::fetchByMonkId($institutionId);
		$collection = S4AAAS_Model_Collection::fetchByMonkId($collectionId);
		$book = S4AAAS_Model_Book::fetchByMonkDir($bookDir);
		if (!$book)
		{
			$this->view->status = "BOOK NOT FOUND";
			return;			
		}
		$page = $book->getPage($pageNo);
		if (!$page)
		{
			$this->view->status = "PAGE NOT FOUND";
			return;			
		}
		if (!$collection || !$institution)
		{
			$this->view->status = "OBJECT NOT FOUND";
			return;
		}
		
		// check if page is already locked
		if (!$page->isLockedForUser($this->user) && $page->isLocked())
		{
			$this->view->status = "PAGE ALREADY LOCKED";
			return;
		}
		
		//check the permissions
		$userMonkId = $this->user->getMonkId();
		$bookMonkDir = $book->getMonkDir();
		if (!($this->user->getPermissions() > 2))
		{
			$permission = $this->ancoQuery('SELECT MAX(PERM) FROM (SELECT COUNT(*) PERM FROM PAGES JOIN BOOKS ON PAGES.BOOK_ID = BOOKS.ID JOIN USERBOOK ON BOOKS.ID = USERBOOK.BOOK_ID JOIN USERS ON USERBOOK.USER_ID = USERS.ID WHERE PAGES.PAGE_NO = ' . $pageNo . ' AND BOOKS.MONK_DIR = \'' . $bookMonkDir . '\' AND USERS.MONK_ID = \'' . $userMonkId . '\' AND (USERBOOK.PERMISSIONS > 2 AND PAGES.PAGE_NO BETWEEN USERBOOK.PAGE_FROM AND USERBOOK.PAGE_TO) UNION SELECT COUNT(*) PERM FROM PAGES JOIN BOOKS ON PAGES.BOOK_ID = BOOKS.ID JOIN COLLECTIONS ON BOOKS.COLLECTION_ID = COLLECTIONS.ID JOIN USERCOL ON COLLECTIONS.ID = USERCOL.COLLECTION_ID JOIN USERS ON USERCOL.USER_ID = USERS.ID WHERE PAGES.PAGE_NO = ' . $pageNo . ' AND BOOKS.MONK_DIR = \'' . $bookMonkDir . '\' AND USERS.MONK_ID = \'' . $userMonkId . '\' AND USERCOL.PERMISSIONS > 2 UNION SELECT COUNT(*) PERM FROM PAGES JOIN BOOKS ON PAGES.BOOK_ID = BOOKS.ID JOIN COLLECTIONS ON BOOKS.COLLECTION_ID = COLLECTIONS.ID JOIN INSTITUTIONS ON COLLECTIONS.INSTITUTION_ID = INSTITUTIONS.ID JOIN USERINST ON INSTITUTIONS.ID = USERINST.INSTITUTION_ID JOIN USERS ON USERINST.USER_ID = USERS.ID WHERE PAGES.PAGE_NO = ' . $pageNo . ' AND BOOKS.MONK_DIR = \'' . $bookMonkDir . '\' AND USERS.MONK_ID = \'' . $userMonkId . '\' AND USERINST.PERMISSIONS > 2)SUB', true);

			if (!($permission[0] == '1'))
			{
				$this->view->status = "NO TRANSCRIBE PERMISSION";
				return;
			}
		}		
		// lock the page for the current user (if already locked for user, extend time)
		$page->setPageLock($this->user);
		
		$this->view->lineLabels = $this->getLineLabels($page);		
		$this->view->lines = $page->getLines();
		$this->view->page = $page;
		$this->view->book = $book;
		$this->view->collection = $collection;
		$this->view->institution = $institution;
		$this->view->imageAvailable = $this->pageImageAvailable($page, $book);

		$this->view->status = "OK";
	}
	
	private function getLineLabels($page)
	{
		$lines = $page->getLines();
		$dbLineLabels = array();
		foreach ($lines as $line)
		{
			$dbLineLabels[$line->getLineNo()] = $line->getLabels();
		}
		
		$trieLabels = $page->getTrieLabels();
		
		$trieLineLabels = array();
		foreach ($trieLabels as $label)
		{
			$trieLineLabels[$label['LINE']][] = $label;
		}
		
		$mergedLineLabels = array();
		foreach ($dbLineLabels as $lineNo => $dbLabels)
		{
			if (isset($trieLineLabels[$lineNo]))
			{
				$mergedLineLabels[$lineNo] = S4AAAS_Model_Line::mergeLabels($dbLabels, $trieLineLabels[$lineNo]);
			}
			else
			{
				$mergedLineLabels[$lineNo] = $dbLabels;
			}
		}
				
		return $mergedLineLabels;
	}
	
	private function pageImageAvailable($page, $book)
	{
		// add leading zeroes to page no.
		$pageNo = $page->getPageNo();
		while (strlen($pageNo) < 4)
		{
			$pageNo = '0'.$pageNo;
		}
		
		$imagePath = $this->getPageImagePath();
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

	public function finishtranscriptionAction()
	{
		if (!$this->userHasRight())
		{
			$this->view->status = "AUTHENTICATION FAILURE";
			return;
		}
		
		$institutionId = $this->getRequest()->getParam('institution');
		$collectionId = $this->getRequest()->getParam('collection');
		$bookId = $this->getRequest()->getParam('book');
		$pageNo = $this->getRequest()->getParam('page');
		
		if (!$institutionId || !$collectionId || !$bookId || !$pageNo)
		{
			$this->view->status = "NOT ENOUGH PARAMS SPECIFIED";
			return;
		}
		
		$institution = S4AAAS_Model_Institution::fetchByMonkId($institutionId);
		$collection = S4AAAS_Model_Collection::fetchByMonkId($collectionId);
		$book = S4AAAS_Model_Book::fetchByMonkDir($bookId);
		if (!$book)
		{
			$this->view->status = "BOOK NOT FOUND";
			return;			
		}
		$page = $book->getPage($pageNo);
		if (!$page || !$collection || !$institution)
		{
			$this->view->status = "OBJECT NOT FOUND";
			return;
		}

		// check if page is transcription is open for the user
		if (!$page->isLockedForUser($this->user))
		{
			$this->view->status = "PAGE NOT LOCKED FOR CURRENT USER";
			return;
		}

		
		// insert labels into databases
		$labels = $this->getLabelsFromPostData($page);
		if (!$labels)
		{
			$this->view->status = "NO LABELS GIVEN";
			return;
		}
		foreach ($labels as $label)
		{
			$label->setByuserId($this->user->getId());
			$date = new DateTime();
			$label->setTimestamp($date->format("Y-m-d H:i:s"));
			$userMonkId = $this->user->getMonkId();

			// set the status depending on the current users' rights
			$permission = $this->ancoQuery('SELECT CASE WHEN MAX(PERM) IS NULL THEN 1 ELSE MAX(PERM) END AS PERM FROM (SELECT USERBOOK.PERMISSIONS PERM FROM PAGES JOIN BOOKS ON PAGES.BOOK_ID = BOOKS.ID JOIN USERBOOK ON BOOKS.ID = USERBOOK.BOOK_ID JOIN USERS ON USERBOOK.USER_ID = USERS.ID WHERE PAGES.PAGE_NO = ' . $pageNo . ' AND BOOKS.MONK_DIR = \'' . $bookId . '\' AND USERS.MONK_ID = \'' . $userMonkId . '\' AND (PAGES.PAGE_NO BETWEEN USERBOOK.PAGE_FROM AND USERBOOK.PAGE_TO) UNION SELECT USERCOL.PERMISSIONS FROM PAGES JOIN BOOKS ON PAGES.BOOK_ID = BOOKS.ID JOIN COLLECTIONS ON BOOKS.COLLECTION_ID = COLLECTIONS.ID JOIN USERCOL ON COLLECTIONS.ID = USERCOL.COLLECTION_ID JOIN USERS ON USERCOL.USER_ID = USERS.ID WHERE PAGES.PAGE_NO = ' . $pageNo . ' AND BOOKS.MONK_DIR = \'' . $bookId . '\' AND USERS.MONK_ID = \'' . $userMonkId . '\' UNION SELECT USERINST.PERMISSIONS FROM PAGES JOIN BOOKS ON PAGES.BOOK_ID = BOOKS.ID JOIN COLLECTIONS ON BOOKS.COLLECTION_ID = COLLECTIONS.ID JOIN INSTITUTIONS ON COLLECTIONS.INSTITUTION_ID = INSTITUTIONS.ID JOIN USERINST ON INSTITUTIONS.ID = USERINST.INSTITUTION_ID JOIN USERS ON USERINST.USER_ID = USERS.ID WHERE PAGES.PAGE_NO = ' . $pageNo . ' AND BOOKS.MONK_DIR = \'' . $bookId . '\' AND USERS.MONK_ID = \'' . $userMonkId . '\')SUB ', true);
			if ($permission[0] == 3)
			{
				$label->setStatus('OPEN');
			}
			else
			{
				$label->setStatus('VERIFIED');
			}                       
			$label->insert();
		}
		
		// free page for other users
		$page->clearLock();
		
		$this->view->status = "OK";
	}
	
	private function getLabelsFromPostData($page)
	{
        $request = $this->getRequest();
        $body = $request->getRawBody();
        if($body===false)
		{
            return null;
        }
		else
		{
            $labels = null;
            $xml = simplexml_load_string($body);
			$labelsXml = $xml->labels->label;
			if (!$labelsXml)
			{
				return null;
			}
			
            foreach($labelsXml as $labelXml)
			{
				$label = new S4AAAS_Model_Label();
				$lineNo = (string)$labelXml->line_no;
				$line = S4AAAS_Model_Line::getLineFromPage($page, $lineNo);
				$label->setLineId($line->getId());
				$roi = $labelXml->roi;
				$label->setCoordinatesFromXml($roi);
				$label->setLabelText((string)$labelXml->word);
				
				$labels[] = $label;
            }
		}
		
		return $labels;
	}
	
	public function testAction()
	{
		$this->addPageTranscriptsToDatabase();
	}
	
	private function addPageTranscriptsToDatabase()
	{
		set_time_limit(0);
		
		$filename = '/home/rik/scan-pagetrans.sh.log';
		$writefile = '/home/rik/scan-pagetrans.sh.log2.meuk';
		$lines = file($filename);
		
		$idx = 0;
		foreach($lines as $line)
		{
			$parts = explode(' ', $line, 3);

			$bookMonkDir = $parts[0];
			
			if ($bookMonkDir == 'Sierinitialen')
			{
				continue;
			}
			
			
			$pageNo = $parts[1];
			
			if (!isset($parts[2]))
			{
				$transcript = '';
			}
			else
			{
				$transcript = $parts[2];
			}
			
			$book = S4AAAS_Model_Book::fetchByMonkDir($bookMonkDir);
			if ($book)
			{
				$page = S4AAAS_Model_Page::fetchByBookAndPage($book->getId(), ltrim($pageNo, '0'));			
			}
			else
			{
				file_put_contents($writefile, "BOOKNOTFOUND ".$line, FILE_APPEND);
				$page = S4AAAS_Model_Page::fetchByNavisId($bookMonkDir.'_'.$pageNo);
			}
			
			if ($page)
			{
				$page->setTranscript($transcript);
				$page->update();
			}
			else
			{
				file_put_contents($writefile, "PAGENOTFOUND ".$line, FILE_APPEND);
			}
		}	
	}
	
	private function addLineTranscriptsToDatabase()
	{
		set_time_limit(0);
		
		$filename = '/home/rik/scan-linetrans.sh.log2';
		$writefile = '/home/rik/scan-linetrans.sh.log2.meuk';
		$lines = file($filename);
		
		foreach($lines as $line)
		{
			$parts = explode(' ', $line, 6);
			
			if (count($parts) == 5)
			{
				continue;
			}

			if (count($parts) < 5)
			{
				file_put_contents($writefile, "ERROR ".$line, FILE_APPEND);
				continue;
			}

			$bookMonkDir = $parts[0];
			$pageNo = $parts[1];
			$lineNo = $parts[2];
			$yTop = $parts[3];
			$yBot = $parts[4];
			$transcript = $parts[5];
			
			$book = S4AAAS_Model_Book::fetchByMonkDir($bookMonkDir);
			if (!$book)
			{
				file_put_contents($writefile, "BOOKNOTFOUND ".$line, FILE_APPEND);
				continue;
			}
			
			$bookId = $book->getId();
			$page = S4AAAS_Model_Page::fetchByBookAndPage($bookId, ltrim($pageNo, '0'));
			if (!$page)
			{
				file_put_contents($writefile, "PAGENOTFOUND ".$line, FILE_APPEND);
				continue;
			}
			
			$lineObject = S4AAAS_Model_Line::getLineFromPage($page, ltrim($lineNo, '0'));
			if ($lineObject)
			{
				$lineObject->setTranscript($transcript);
				$lineObject->update();
			}
			else
			{
				$lineObject = new S4AAAS_Model_Line();
				$lineObject->setPageId($page->getId());
				$lineObject->setLineNo(ltrim($lineNo, '0'));
				$lineObject->setYTop($yTop);
				$lineObject->setYBot($yBot);
				$lineObject->setTranscript($transcript);
				$lineObject->insert();
			}
		}
	}
	
	private function fillDatabaseWithOriginalWidthsForPages()
	{
		set_time_limit(0);
		
		$filename = '/home/rik/resize-all.sh.log';
		$lines = file($filename);
		foreach($lines as $line)
		{
			$parts = explode(';', $line);
			$bookMonkDir = $parts[0];
			$pageImage = $parts[1];
			$origWidth = $parts[2];
			$pageNo = substr($pageImage, 0, -4);
			$pageNoTrimmed = ltrim($pageNo, '0');
			
			$book = S4AAAS_Model_Book::fetchByMonkDir($bookMonkDir);
			if ($book)
			{
				$bookNavisId = $book->getNavisId();
				$pageNavisId = $bookNavisId.'_'.$pageNo;
				$page = S4AAAS_Model_Page::fetchByNavisId($pageNavisId);
				if($page)
				{
					if (!($page->getOrigWidth() == 0))
					{
						$page->setOrigWidth($origWidth);
						$page->update();
					}
				}
			}
		}
	}
	
	private function genRandomString()
	{
		$length = 8;
		$characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVW";
		$numCharacters = strlen($characters);
		$string = '';    
		for ($p = 0; $p < $length; $p++) {
			$rn = mt_rand(0, $numCharacters);
			$string .= substr($characters, $rn, 1);
		}
		return $string;
	}

}

