<?php

class SearchController extends Zend_Controller_Action {

    public function init() {      
    }

    public function indexAction()
	{		
        try {
            $searchData = array();
            $request = $this->getRequest();
            $searchData['match'] = $request->getParam('match', null);
            $searchData['word'] = $request->getParam('word', null);
            $searchData['offset'] = $request->getParam('offset', 0);
            $searchData['rows'] = $request->getParam('rows', 100);

            $searchData['institutions'] = array_unique(array_filter(explode("|", $request->getParam('institutions', "")), 'strlen'));
            $searchData['collections'] = array_unique(array_filter(explode("|", $request->getParam('collections', "")), 'strlen'));
            $searchData['books'] = array_unique(array_filter(explode("|", $request->getParam('books', "")), 'strlen'));
            $searchData['annotationTypes'] = array_intersect(array("wordzone", "page", "line"), array_unique(array_filter(explode("|", $request->getParam('annotations', "")), 'strlen')));
            $searchData['wordzonetypes'] = array_unique(array_filter(explode("|", $request->getParam('wordzonetypes', "")), 'strlen'));
			
            if ($searchData['match'] === null) {
                $this->view->status = 'NO MATCH METHOD SPECIFIED';
                $this->errorCode = 'NO MATCH METHOD SPECIFIED';
            } else if (!in_array($searchData['match'], array('prefix', 'suffix', 'exact', 'wildcard')))
                $this->errorCode = 'UNKOWN MATCH METHOD SPECIFIED';
            else if ($searchData['word'] === null)
                $this->status = 'NO WORD SPECIFIED';
            else
			{
                $trieSearchResult = $this->trieSearch($searchData);   
                
				$dbSearchResult = $this->dbSearch($searchData);
                                
				$result = $this->mergeSearchResults($trieSearchResult, $dbSearchResult);
				
				$result = $this->addImagePaths($result);                           
				
                $this->view->pages = $result['pages'];
                $this->view->lines = $result['lines'];
                $this->view->wordzones = $result['wordzones'];
                $this->view->command = $result['command'];
                $this->view->match = $searchData['match'];
                $this->view->annotationTypes = $searchData['annotationTypes'];
                $this->view->rows = $searchData['rows'];
				$this->view->total = $trieSearchResult['total'] + $dbSearchResult['total'];
                $this->view->offset = $searchData['offset'];
                $this->view->wordzoneTypes = $searchData['wordzonetypes'];
                $this->view->status = "OK";
            }
        } catch (Exception $e) {
            print_r($e);
            $this->view->status = "ERROR";
        }
    }
	
	private function addImagePaths(&$result)
	{
		$wordzones = &$result['wordzones'];
		$lines = &$result['lines'];
		$pages = &$result['pages'];
		
		if (empty($wordzones) && empty($lines) && empty($pages))
			return $result;
		
		
        $pageNavisIdsAndLineNumbers=array();
        $pageNavisIds = array();
		
		
        $imageLookups = array();
        $imageLookupUntil = new DateTime();
        $imageLookupUntil->modify("+3600 seconds");
        $imageLookupUntil = $imageLookupUntil->format("Y-m-d H:i:s");
		

		
		
		foreach($wordzones as $wordzone)
		{
			$pageNavisIdsAndLineNumbers[] = array('pageid'=>$wordzone['PAGE_ID'], 'lineno'=>$wordzone['LINE']);
		}
		foreach ($lines as $line)
		{
			$pageNavisIdsAndLineNumbers[] = array('pageid'=>$line['PAGE_ID'], 'lineno'=>$line['LINE']);
		}
		foreach($pages as $page)
		{
			$pageNavisIds[] = $page['PAGE_ID'];
		}
		
		
            
        $_lines = S4AAAS_Model_Line::getLines($pageNavisIdsAndLineNumbers);
        $_pages = S4AAAS_Model_Page::getPageTranscripts($pageNavisIds);
        
        foreach($wordzones as &$wordzone) {
            $l=null;
            if(isset($_lines[$wordzone['PAGE_ID']]) && isset($_lines[$wordzone['PAGE_ID']][$wordzone['LINE']])) 
                $l = $_lines[$wordzone['PAGE_ID']][$wordzone['LINE']];

			if ($l && S4AAAS_Model_ImageLookup::pageImageAvailable($l))
            {			
				$lineImageLookup = array('VALID_UNTIL' => $imageLookupUntil, 'IMAGE_ID' => S4AAAS_Model_ImageLookup::genToken(), 'TYPE'=>'LINE', 'OBJECT_ID'=>($l===null ? -1: $l->getId()));
				$imageLookups[] = $lineImageLookup;
				$wordzone["LINE_IMAGE_ID"] = $lineImageLookup['IMAGE_ID'];
			}
			else
			{
				$wordzone["LINE_IMAGE_ID"] = '';				
			}
			
			$pageImageLookup = array('VALID_UNTIL' => $imageLookupUntil, 'IMAGE_ID' => S4AAAS_Model_ImageLookup::genToken(), 'TYPE'=>'PAGE', 'OBJECT_ID'=>($l===null ? -1: $l->getPageId()));
            $imageLookups[] = $pageImageLookup;
            $wordzone["PAGE_IMAGE_ID"] = $pageImageLookup['IMAGE_ID'];
        }
        
        foreach($lines as &$line) {
            $l=null;
            if(isset($_lines[$line['PAGE_ID']]) && isset($_lines[$line['PAGE_ID']][$line['LINE']])) 
                $l = $_lines[$line['PAGE_ID']][$line['LINE']];

            if ($l === null)
			{
				$line['LINE_TRANSCRIPTION'] = 'TRANSCRIPT NOT FOUND IN THE DATABASE';
			}
			else
			{
				$line['LINE_TRANSCRIPTION'] = $l->getTranscript();
			}

			if ($l && S4AAAS_Model_ImageLookup::pageImageAvailable($l))
            {
				$lineImageLookup = array('VALID_UNTIL' => $imageLookupUntil, 'IMAGE_ID' => S4AAAS_Model_ImageLookup::genToken(), 'TYPE'=>'LINE', 'OBJECT_ID'=>($l===null ? -1: $l->getId()));
				$imageLookups[] = $lineImageLookup;
				$line["LINE_IMAGE_ID"] = $lineImageLookup['IMAGE_ID'];
			}
			else
			{
				$line["LINE_IMAGE_ID"] = '';
			}
			$pageImageLookup = array('VALID_UNTIL' => $imageLookupUntil, 'IMAGE_ID' => S4AAAS_Model_ImageLookup::genToken(), 'TYPE'=>'PAGE', 'OBJECT_ID'=>($l===null ? -1: $l->getPageId()));
            $imageLookups[] = $pageImageLookup;
            $line["PAGE_IMAGE_ID"] = $pageImageLookup['IMAGE_ID'];
        }

	foreach($pages as &$page) {
            $p = null;
            if(isset($_pages[$page['PAGE_ID']])) $p = $_pages[$page['PAGE_ID']];
            
            $page["LINE_IMAGE_ID"] = '';
            
            $page['PAGE_TRANSCRIPTION'] = ($p===null ? 'TRANSCRIPT NOT FOUND IN THE DATABASE' : $p['TRANSCRIPT']);
            
            $pageImageLookup = array('VALID_UNTIL' => $imageLookupUntil, 'IMAGE_ID' => S4AAAS_Model_ImageLookup::genToken(), 'TYPE'=>'PAGE', 'OBJECT_ID'=>($p===null ? -1: $p['PAGE_ID']));
//            $imageLookups[] = $lineImageLookup;
            $imageLookups[] = $pageImageLookup;
            $page["PAGE_IMAGE_ID"] = $pageImageLookup['IMAGE_ID'];
			
//			print_r($_pages[$page['PAGE_ID']]);
//			die;
			
            $l=null;
            if(isset($_pages[$page['PAGE_ID']])) 
                $l = S4AAAS_Model_Line::fetchById($_pages[$page['PAGE_ID']]['LINE_ID']);
			
			
			if ($l && S4AAAS_Model_ImageLookup::pageImageAvailable($l))
            {			
				$lineImageLookup = array('VALID_UNTIL' => $imageLookupUntil, 'IMAGE_ID' => S4AAAS_Model_ImageLookup::genToken(), 'TYPE'=>'LINE', 'OBJECT_ID'=>($l===null ? -1: $l->getId()));
				$imageLookups[] = $lineImageLookup;
				$page["LINE_IMAGE_ID"] = $lineImageLookup['IMAGE_ID'];
			}
			else
			{
				$page["LINE_IMAGE_ID"] = '';				
			}			

        }
        
        S4AAAS_Model_ImageLookup::storeAll($imageLookups);
		
		return $result;		
	}

    // @TODO implement
    private function mergeSearchResults($entireTrieResult, $entireDbResult) {
		
		// minimum required overlay to see of labels are at the same location
		$minimumOverlay = 95; //percent
		
        $result = array();
		
		$trieResult = $entireTrieResult['wordzones'];
		$dbResult = $entireDbResult['wordzones'];
		$dbResultIdx = 0;
		$trieResultIdx = 0;
		$lastBookMonkId = '';
		
		// while at least some of the arrays has items
        while (isset($dbResult[$dbResultIdx]) || isset($trieResult[$trieResultIdx]))
		{

            // check if one of the arrays isn't already empty (makes it easy)
            if (!isset($trieResult[$trieResultIdx])) // we have had all items in this array
			{
				while (isset($dbResult[$dbResultIdx]))
				{
					$thingy = $this->dbLabelToTrieLabel($dbResult[$dbResultIdx]);
					$result[] = $thingy;
					$dbResultIdx++;
				}
                break;
            }
			elseif (!isset($dbResult[$dbResultIdx])) // we have had all items in this array
			{
                $result = array_merge($result, $trieResult);
                break;
            }


			// store the last set bookmonk id
			if (!empty($result))
			{
				$lastItem = end($result);
				$lastBookMonkId = $lastItem['BOOK']; //@TODO get the correct key
			}
				
			// compare result arrays and add the element from one of the arrays to the resultset
            $dbItem = $dbResult[$dbResultIdx];
			$trieItem = $trieResult[$trieResultIdx];
			
			$dbBook = $dbItem['book_MONK_DIR']; //@TODO check if correct key
			$trieBook = $trieItem['BOOK'];
			
			// Books are different, pick one
			if (!($dbBook == $trieBook))
			{
				// It's our first item, just pick the one from the trieResult. (from dbResult would also be fine)
				// OR
				// Both book id's don't match with lastBookMonkId, so just pick one of the two
				if (($lastBookMonkId == '') || (($dbBook != $lastBookMonkId) && ($trieBook != $lastBookMonkId)))
				{
					$result[] = $trieResult[$trieResultIdx];
					$trieResultIdx++;
					continue;
				}
				elseif ($lastBookMonkId == $dbBook)
				{
					$result[] = $this->dbLabelToTrieLabel($dbResult[$dbResultIdx]);
					$dbResultIdx++;
					continue;
				}
				elseif ($lastBookMonkId == $trieBook)
				{
					$result[] = $trieResult[$trieResultIdx];
					$trieResultIdx++;
					continue;
				}
				else
				{
					throw new Exception("Error: lastBookMonkId=$lastBookMonkId dbBook=$dbBook trieBook=$trieBook");
					return;
				}
			}
			
			$dbPage = $dbItem['page_PAGE_NO']; // @TODO check if correct key
			$triePage = $trieItem['PAGE'];
			
			if ($dbPage < $triePage)
			{
				$result[] = $this->dbLabelToTrieLabel($dbResult[$dbResultIdx]);
				$dbResultIdx++;
				continue;					
			}
			elseif($trieBook < $dbPage)
			{
				$result[] = $trieResult[$trieResultIdx];
				$trieResultIdx++;
				continue;
			}
			
			//pages are the same, so compare the lines
			$dbLine = $dbItem['line_LINE_NO']; // @TODO check if correct key
			$trieLine = $trieItem['LINE'];
			
			if ($dbLine < $trieItem)
			{
				$result[] = $this->dbLabelToTrieLabel($dbResult[$dbResultIdx]);
				$dbResultIdx++;
				continue;			
			}
			elseif ($trieLine < $dbLine)
			{
				$result[] = $trieResult[$trieResultIdx];
				$trieResultIdx++;
				continue;
			}
			
			// lines are the same, so compare coordinates
			$overlay = S4AAAS_Model_Line::getOverlay($dbItem, $trieItem);
			
			// labels are the same
			if ($overlay >= $minimumOverlay)
			{
				$result[] = $this->dbLabelToTrieLabel($dbResult[$dbResultIdx]);
				$dbResultIdx++;
				$trieResultIdx++;
				continue;				
			}
			
			$dbX = $dbItem['X'];
			$trieX = $trieItem['X'];
			
			if ($dbX <= $trieX)
			{
				$result[] = $this->dbLabelToTrieLabel($dbResult[$dbResultIdx]);
				$dbResultIdx++;
				continue;
			}
			elseif ($trieX < $dbX)
			{
				$result[] = $trieResult[$trieResultIdx];
				$trieResultIdx++;
				continue;
			}

        }
		
		$fullResult = array();
		$fullResult['wordzones'] = $result;
		//@TODO also merge lines and pages
		$fullResult['lines'] = $entireTrieResult['lines'];
		$fullResult['pages'] = $entireTrieResult['pages'];
		$fullResult['command'] = $entireTrieResult['command'];

        return $fullResult;
    }
	
	public function overlayAction()
	{
		//nothing to do here
		return;
	}
	
	/*
	 * Takes a db result and returns an array used for the view with search results
	 */
	private function dbLabelToTrieLabel($dbLabel)
	{
		$row = array(
			'TRANSCRIPT_TYPE' => 'wordzone',
			'TEXT' => $dbLabel['LABEL_TEXT'],
			'INSTITUTION' => $dbLabel['institution_MONK_ID'],
			'COLLECTION' => $dbLabel['collection_MONK_ID'],
			'BOOK' => $dbLabel['book_MONK_DIR'],
			'PAGE' => $dbLabel['page_PAGE_NO'],
			'LINE' => $dbLabel['line_LINE_NO'],
			'TYPE' => " ",
			'LINE_ID' => " ",
			'PAGE_ID' => $dbLabel['page_NAVIS_ID'],
			'FILE_LINE_PATH_FACTOR' => " ",
			'SHORT_NAME' => $dbLabel['book_SHORT_NAME'],
			'ROI' => $dbLabel['ROI'],
			'Y1' => $dbLabel['line_Y_TOP'],
			'Y2' => $dbLabel['line_Y_BOT'],
			'X' => $dbLabel['X'],
			'Y' => $dbLabel['Y'],
			'W' => $dbLabel['WIDTH'],
			'H' => $dbLabel['HEIGHT'],
			'PAGE_IMAGE_ID' => " ",
			'LINE_IMAGE_ID' => " "
		);
				
		return $row;
			
	}

    private function trieSearch($searchData) {
        $word = $searchData['word'];
        $match = $searchData['match'];
        $offset = $searchData['offset'];
        $rows = $searchData['rows'];
        $institutions = $searchData['institutions'];
        $collections = $searchData['collections'];
        $books = $searchData['books'];
        $annotationTypes = $searchData['annotationTypes'];
		$wordzoneTypes = $searchData['wordzonetypes'];

        $trieQuery = new S4AAAS_Trie_Query();
        $trieQuery->setWord($word);
        $trieQuery->setSearchType($match);
        $trieQuery->setOffset($offset);
        $trieQuery->setLimit($rows);
        foreach ($institutions as $institution)
            $trieQuery->addInstitution($institution);
        foreach ($collections as $collection)
            $trieQuery->addCollection($collection);
        foreach ($books as $book)
            $trieQuery->addBook($book);
        foreach ($annotationTypes as $annotationType)
            $trieQuery->addAnnotationType($annotationType);
		foreach ($wordzoneTypes as $type)
		{
			$trieQuery->addType($type);
		}

		$result = $trieQuery->execute();

        return $result;
    }

    private function dbSearch($searchData)
	{
		$result = array();
		
		if (!empty($searchData['wordzonetypes']) && !in_array('HUMAN', $searchData['wordzonetypes']))
		{
			$result['wordzones'] = array();
			$result['total'] = 0;
		}
		else
		{
			$result['wordzones'] = S4AAAS_Model_Label::search($searchData);
			$result['total'] = S4AAAS_Model_Label::searchTotal($searchData);			
		}

		return $result;
    }
	
	/*	Junk stub
	private function fillDbWithSomeLabels($trieSearchResult)
	{
		for ($idx = 0; $idx < 10; $idx++)
		{
			$result = $trieSearchResult['wordzones'][$idx];
			$label = new S4AAAS_Model_Label();
			$label->setLineId();
			$label->setByUser();
			$label->setTimestamp();
			$label->set
		}
	}
	 * 
	 */
    
    public function testAction() {
        $line = S4AAAS_Model_Line::fetchById($this->getRequest()->getParam('id'));
        $labels = S4AAAS_Trie_Query::getLineLabels($line);
        print_r($labels);
    }

    public function suggestionAction() {
        try {
            $request = $this->getRequest();
            $prefix = $request->getParam('prefix', null);
            if ($prefix === null)
                $this->errorCode = 'NO PREFIX SPECIFIED';
            else {
                $trieQuery = new S4AAAS_Trie_Query();
                $trieQuery->setWord($prefix);
                $trieQuery->setSearchType('prefix');
                $trieQuery->addType('HUMAN');
                $trieQuery->addType('JAVA');
                $trieQuery->setLimit(-1);
                $result = $trieQuery->getSuggestions();
                //$result[]=count($result);
                $this->view->results = $result;
                $this->view->status = "OK";
                $this->view->command = $trieQuery->__toString();
            }
        } catch (Exception $e) {
            $this->view->status = "ERROR";
        }
    }
}
