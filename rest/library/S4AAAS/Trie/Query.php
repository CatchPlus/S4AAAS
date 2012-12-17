<?php

/**
 * A query to search the trie
 *
 * @author herbert
 */
class S4AAAS_Trie_Query {

    private $filters = array();
    private $types = array();
    private $limit = 100;
    private $offset = 0;
    private $searchType = 'prefix';
    private $keyword = null;
    private $annotationTypes = array();
    private $bookShortNames = array();
    private static $settings = array();

    public static function initSettings($settings) {
        self::$settings = $settings;
    }

    public function setSearchType($searchType) {
        $this->searchType = $searchType;
    }

    public function addAccept($exp) {
        $this->filters[] = array('accept', $exp);
    }

    public function addReject($exp) {
        $this->filters[] = array('reject', $exp);
    }

    public function setLimit($limit) {
        $this->limit = $limit;
    }

    public function setOffset($offset) {
        $this->offset = $offset;
    }

    public function setWord($keyword) {
        $this->keyword = strtolower($keyword);
    }

    public function addType($type) {
        $this->types[] = $type;
    }

    public function addInstitution($institution) {
        $this->filters[] = array('accept', '*\t*\t*\t' . $institution . '\t*\t*\t*\t*\t*\t*');
    }

    public function addCollection($collection) {
        $this->filters[] = array('accept', '*\t*\t*\t*\t' . $collection . '\t*\t*\t*\t*\t*');
    }

    public function addBook($book) {
        $this->filters[] = array('accept', '*\t*\t*\t*\t*\t' . $book . '\t*\t*\t*\t*');
    }

    public function addAnnotationType($annotationType) {
        if ($annotationType === 'wordzone')
            $this->annotationTypes[] = 'W';
        else if ($annotationType === 'page')
            $this->annotationTypes[] = 'P';
        else if ($annotationType === 'line')
            $this->annotationTypes[] = 'L';
        else
            throw new Exception("This annotation type is not supported, type " . print_r($annotationType, true) . " not supported.");
        $this->annotationTypes = array_unique($this->annotationTypes);
    }

    private function getSearchCommand($justCountTotal=false) {
        $command = self::$settings['searchCommand'];
        $annotationTypes = (count($this->annotationTypes) === 0 ? array('W', 'L', 'P') : $this->annotationTypes);

		foreach ($annotationTypes as $annotationType)
		{
			if ($annotationType === 'W')
			{
				foreach ($this->types as $type)
				{
					$keyPrefix = $annotationType . '\t' . $type . '\t';
					if ($this->keyword === null)
						$command .= ' --key=\'' . $keyPrefix . '\'';
					else if ($this->searchType === 'wildcard')
						$command .= ' --key=\'' . $keyPrefix . '(*' . $this->keyword . '*)\t\'';
					else if ($this->searchType === 'prefix')
						$command .= ' --key=\'' . $keyPrefix . '(' . $this->keyword . '*)\t\'';
					else if ($this->searchType === 'suffix')
						$command .= ' --key=\'' . $keyPrefix . '(*' . $this->keyword . ')\t\'';
					else if ($this->searchType === 'exact')
						$command .= ' --key=\'' . $keyPrefix . '(' . $this->keyword . ')\t\'';
					else
						throw new Exception("This search operation is not supported, type " . print_r($this->searchType, true) . " not supported.");
				}
			}
			else
			{
				$keyPrefix = $annotationType . '\t\t';
				if ($this->keyword === null)
					$command .= ' --key=\'' . $keyPrefix . '\'';
				else if ($this->searchType === 'wildcard')
					$command .= ' --key=\'' . $keyPrefix . '(*' . $this->keyword . '*)\t\'';
				else if ($this->searchType === 'prefix')
					$command .= ' --key=\'' . $keyPrefix . '(' . $this->keyword . '*)\t\'';
				else if ($this->searchType === 'suffix')
					$command .= ' --key=\'' . $keyPrefix . '(*' . $this->keyword . ')\t\'';
				else if ($this->searchType === 'exact')
					$command .= ' --key=\'' . $keyPrefix . '(' . $this->keyword . ')\t\'';
				else
					throw new Exception("This search operation is not supported, type " . print_r($this->searchType, true) . " not supported.");				
			}
		}
        foreach ($this->filters as $filter) {
            $command .= ($filter[0] === 'accept') ? ' --accept=' : ' --reject=';
            $command .= '\'' . $filter[1] . '\'';
        }
        if (count($this->filters) > 0)
            $command .= ' --reject \'*\' ';
        $command .= ' --substring=' . self::$settings['trieLocation']['bySubstring'];
        $command .= ' --trie=' . self::$settings['trieLocation']['byAnnotationTypeAndType'];
        if ($this->limit > 0 && $justCountTotal == false)
            $command .= ' --limit=' . $this->limit;
        if ($this->offset >= 0 && $justCountTotal == false)
            $command .= ' --offset=' . $this->offset;
        if ($justCountTotal)
            $command .= ' --count';

        return $command;
    }

    private function getSearchCommandForTotal() {
        return $this->getSearchCommand(true);
    }

    private function getLookupCommand() {
        //return "cat";
        $command = self::$settings['lookupCommand'];
        $command .= ' --full-index=\'' . self::$settings['trieLocation']['lookup'] . '\'';
        return $command;
    }

    public function __toString() {
        return $this->getSearchCommand() . ' | ' . $this->getLookupCommand();
    }

    // deprecated, piping in php sucks, I mean blocks for no apparant reason.
    private function getOutputPipe(&$searchProcess, &$lookupProcess, &$outputPipe) {
        $searchPipes = array();
        $lookupPipes = array();
        $searchCommand = $this->getSearchCommand();
        $lookupCommand = $this->getLookupCommand();
        $cwd = '/tmp';
        $env = array();
        $searchProcess = proc_open($searchCommand, array(
            0 => array("pipe", "r"), // stdin is a pipe that the child will read from
            1 => array("pipe", "w"), // stdout is a pipe that the child will write to
            2 => array("file", self::$settings['searcherrorlog'], "a") // stderr is a file to write to
                ), $searchPipes, $cwd, $env);

        $lookupProcess = proc_open($lookupCommand, array(
            0 => array("pipe", "r"), // stdin is a pipe that the child will read from
            1 => array("pipe", "w"), // stdout is a pipe that the child will write to
            2 => array("file", self::$settings['lookuperrorlog'], "a") // stderr is a file to write to
                ), $lookupPipes, $cwd, $env);
        proc_close($searchProcess);

        while (!feof($searchPipes[1])) {
            // this next line seems to clock from time to time.
            fwrite($lookupPipes[0], stream_get_line($searchPipes[1], 100));
        }

        fclose($searchPipes[0]);
        fclose($lookupPipes[0]);
        fclose($searchPipes[1]);
        $outputPipe = $lookupPipes[1];
    }

    private function getOutputLines() {
        $command = $this->getSearchCommand() . ' | ' . $this->getLookupCommand();
		
        $eenArray = array();
        $output = 0;
//		$shellResult = exec($command, $eenArray, $output);
        $shellResult = shell_exec($command);

        $result = array_filter(explode("\n", $shellResult), 'strlen');
        return $result;
    }

    private function getTotalAvailable() {
        $command = $this->getSearchCommandForTotal();
        $eenArray = array();
        $output = 0;
//		$shellResult = exec($command, $eenArray, $output);
        $shellResult = shell_exec($command);
        return $shellResult;
    }

    private $institutionsCache = array();
    private $collectionsCache = array();
    private $booksCache = array();
    private $pagesCache = array();
    private $linesCache = array();

    //Where $x = 'BOOK', 'COLLECTION' OR 'INSTITUTION'
    private function getXInfo(&$row, $x) {
        $y = null;
        $cacheField = strtolower($x) . 'sCache';
        $className = 'S4AAAS_Model_' . ucfirst(strtolower($x));
        if (!isset($this->{$cacheField}[$row[strtoupper($x)]])) {
            $y = $className::fetchByMonkID($row[strtoupper($x)]);
            $this->{$cacheField}[$row[strtoupper($x)]] = $y;
        } else
            $y = $this->{$cacheField}[$row[strtoupper($x)]];
        return $y;
    }

    private function getPageInfo(&$row) {
        $page = null;
        $navis_page_id = sprintf("%s_%04d", $row['NAVIS_ID'], $row['PAGE_NO']);
        if (!isset($this->pagesCache[$navis_page_id])) {
            $page = S4AAAS_Model_Page::fetchByNavisId($navis_page_id);
            $this->pagesCache[$navis_page_id] = $page;
        } else
            $page = $this->pagesCache[$navis_page_id];
        $row['PAGE_NAVIS_ID'] = $navis_page_id;
        $row['PAGE_ID'] = $page->getId();
        return $page;
    }

    private function getLineInfo(&$row) {
        if (!isset($this->linesCache[$row['PAGE_ID']]))
            $this->linesCache[$row['PAGE_ID']] = array();
        if (!isset($this->linesCache[$row['PAGE_ID']][$row['LINE_NO']])) {
            $line = S4AAAS_Model_Line::fetchByPageIdAndLineNo($row['PAGE_ID'], $row['LINE_NO']);
            $this->linesCache[$row['PAGE_ID']][$row['LINE_NO']] = $line;
        } else
            $line = $this->linesCache[$row['PAGE_ID']][$row['LINE_NO']];
        return $line;
    }

    /**
     *
     * @param S4AAAS_Model_Line $line
     * @return array $labels
     * 
     * Get all the labels of a line given the line.
     */
    public static function getLineLabels($line) {
        $page = $line->getPage();
        if (!$page)
            throw new Exception("page not found");

        $book = $page->getBook();
        if (!$book)
            throw new Exception("book not found");

        $collection = $book->getCollection();
        if (!$collection)
            throw new Exception("collection not found");

        $institution = $collection->getInstitution();
        if (!$institution)
            throw new Exception("institution not found");

        $query = new S4AAAS_Trie_Query();
        $query->addType('HUMAN');
        $query->addType('JAVA');
        $query->addAccept('*\t*\t*\t' . $institution->getMonkId() . '\t' . $collection->getMonkId() . '\t' . $book->getMonkDir() . '\t' . sprintf("%04d", $page->getPageNo()) . '\t' . sprintf("%03d", $line->getLineNo()) . '\t*\t*');
        $query->setSearchType('prefix');
        $labels = $query->execute();

        return $labels;
    }

    /**
     *
     * @param S4AAAS_Model_Line $page
     * @return array $labels
     * 
     * Get all the labels of a page given the page.
     */
    public static function getPageLabels($page) {
        $book = $page->getBook();
        if (!$book)
            throw new Exception("book not found");

        $collection = $book->getCollection();
        if (!$collection)
            throw new Exception("collection not found");

        $institution = $collection->getInstitution();
        if (!$institution)
            throw new Exception("institution not found");

//		$command = self::$settings['searchCommand'].' --trie='.self::$settings['trieLocation']['byPage'].' --key="'.$institution->getMonkId().'\t'.$collection->getMonkId().'\t'.$book->getMonkDir().'\t'.sprintf("%04d", $page->getPageNo()).'" --accept="*\t*\t*\t*\t*\tHUMAN" --accept="*\t*\t*\t*\t*\tJAVA" --reject= | '.self::$settings['lookupCommand'].' --full-index='.self::$settings['trieLocation']['lookup'];
        $command = self::$settings['searchCommand'] . ' --trie=' . self::$settings['trieLocation']['byPage'] . ' --key="W\t' . $institution->getMonkId() . '\t' . $collection->getMonkId() . '\t' . $book->getMonkDir() . '\t' . sprintf("%04d", $page->getPageNo()) . '" --accept="W\t*\t*\t*\t*\t*\t*\tHUMAN" --accept="W\t*\t*\t*\t*\t*\t*\tJAVA" --reject= | ' . self::$settings['lookupCommand'] . ' --full-index=' . self::$settings['trieLocation']['lookup'];
        $log = "Institution: " . $institution->getMonkId() . ", Collection: " . $collection->getMonkId() . ", Book: " . $book->getMonkDir() . ", Page: " . $page->getPageNo() . ". Command: " . $command . "\n";
        file_put_contents(Zend_Registry::get('labelsearchlogpath'), $log, FILE_APPEND);

        $shellResult = shell_exec($command);
        $result = array_filter(explode("\n", $shellResult), 'strlen');

        $query = new S4AAAS_Trie_Query();
        $labels = $query->convertLines($result);



//		$query->addType('HUMAN');
//		$query->addType('JAVA');
//		$query->addAccept('*\t*\t*\t' . $institution->getMonkId() . '\t' . $collection->getMonkId() . '\t' . $book->getMonkDir() . '\t' . sprintf("%04d", $page->getPageNo()) . '\t*\t*\t*');
//		$query->setSearchType('prefix');
//		$labels = $query->execute();

        return $labels;
    }

    private function getGeneralInfo(&$row) {
        $page = null;
        $line = null;
        $book = null;
        $collection = null;
        $institution = null;
        //$institution = $this->getXInfo($row, 'INSTITUTION');
        //$collection = $this->getXInfo($row, 'COLLECTION');
        //$book = $this->getXInfo($row, 'BOOK');
        //$page = $this->getPageInfo($row);
        //$line = $this->getLineInfo($row);

        $row['INSTITUTION_SHORT_NAME'] = ($institution === null ? 'institution not found' : $institution->getShortName());
        $row['INSTITUTION_SHORT_NAME'] = ($institution === null ? 'institution not found' : $institution->getLongName());
        $row['COLLECTION_SHORT_NAME'] = ($collection === null ? 'collection not found' : $collection->getShortName());
        $row['COLLECTION_SHORT_NAME'] = ($collection === null ? 'collection not found' : $collection->getLongName());
        $row['BOOK_SHORT_NAME'] = ($book === null ? 'book not found' : $book->getShortName());
        $row['BOOK_LONG_NAME'] = ($book === null ? 'book not found' : $book->getLongName());
        $row['BOOK_NAVIS_ID'] = $row['NAVIS_ID'];
        $row['PAGE_NO'] = ($page === null ? 'page not found' : $page->getPageNo());
        $row['PAGE_ORIG_WIDTH'] = ($page === null ? 'page not found' : $page->getOrigWidth());
        $row['PAGE_TRANSCRIPT'] = ($page === null ? 'page not found' : $page->getTranscript());
        $row['BOOK_ID'] = ($page === null ? 'page not found' : $page->getBookId());
        $row['LINE_YBOT'] = ($line === null ? 'line not found' : $line->getYBot());
        $row['LINE_YTOP'] = ($line === null ? 'line not found' : $line->getYTop());
        $row['PAGE_NAVIS_ID'] = 'not set';
    }

    private function lineToInfo($line) {
        $data = explode("\t", $line);
        $row = array(
            'TRANSCRIPT_TYPE' => $data[0],
            'TEXT' => $data[1],
            'INSTITUTION' => $data[2],
            'COLLECTION' => $data[3],
            'BOOK' => split("-", $data[4], 2),
            'NAVIS_ID' => $data[4],
            'PAGE_NO' => intval($data[5]),
            'LINE_NO' => intval($data[6]),
            'TYPE' => $data[7],
            'LINE' => $line,
            'FILE_LINE_PATH_FACTOR' => $data[10]
        );
        $row['BOOK'] = $row['BOOK'][1];
        $this->getGeneralInfo($row);
        return $row;
    }

    private function convertLines($outputLines) {
        $wordzones = array();
        $lines = array();
        $pages = array();

        // fill the book short names array from database for quick lookup
        $this->fillBookShortNamesArray();
        $pageNavisIdsAndLineNumbers = array();
        $pageNavisIds = array();

        foreach ($outputLines as $line) {
            $data = explode("\t", $line);

            $extraData = $this->getExtraData($data);

            $row = array(
                'TRANSCRIPT_TYPE' => $data[0],
                'TEXT' => $data[1],
                'INSTITUTION' => $data[2],
                'COLLECTION' => $data[3],
                'BOOK' => $data[4],
                'PAGE' => $this->leftRemove($data[5], '0'),
                'LINE' => $this->leftRemove($data[6], '0'),
                'TYPE' => $data[7],
                'LINE_ID' => $data[9],
                'FILE_LINE_PATH_FACTOR' => $data[10],
                'SHORT_NAME' => '',
                'Y1' => '',
                'Y2' => '',
                'X' => '',
                'Y' => '',
                'W' => '',
                'H' => '',
                'ROI' => ''
            );

            if (isset($extraData['SHORT_NAME'])) {
                $row['SHORT_NAME'] = $extraData['SHORT_NAME'];
            } else {
                $row['SHORT_NAME'] = $data['4'];
            }

            if (isset($extraData['Y1']) && isset($extraData['Y2'])) {
                $row['Y1'] = $extraData['Y1'];
                $row['Y2'] = $extraData['Y2'];
            }

            if (isset($extraData['X']) && isset($extraData['Y']) && isset($extraData['W']) && isset($extraData['H'])) {
                $row['X'] = $extraData['X'];
                $row['Y'] = $extraData['Y'];
                $row['W'] = $extraData['W'];
                $row['H'] = $extraData['H'];
                $roi = array('x' => $row['X'], 'y' => $row['Y'], 'w' => $row['W'], 'h' => $row['H']);
                $row['ROI'] = S4AAAS_Model_Label::roi_xywh2string(0, $roi);
            }

            if ($row['TRANSCRIPT_TYPE'] === 'W') {
                $row['PAGE_ID'] = $data[4] . '_' . $data[5];
                $pageNavisIdsAndLineNumbers[] = array('pageid' => $data[4] . '_' . $data[5], 'lineno' => $row['LINE']);
                $wordzones[] = $row;
            } elseif ($row['TRANSCRIPT_TYPE'] === 'L') {
                $row['PAGE_ID'] = $data[4] . '_' . $data[5];
                $pageNavisIdsAndLineNumbers[] = array('pageid' => $row['PAGE_ID'], 'lineno' => $row['LINE']);
                //$row['LINE_TRANSCRIPTION'] = $this->getLineTranscription($row['PAGE_ID'], $row['LINE']);
                $row['LINE_TRANSCRIPTION'] = '';
                $lines[] = $row;
            } elseif ($row['TRANSCRIPT_TYPE'] === 'P') {
                $row['PAGE_ID'] = $data[4] . '_' . $data[5];
                $pageNavisIds[] = $row['PAGE_ID'];
//				$row['PAGE_TRANSCRIPTION'] = $this->getPageTranscription($row['PAGE_ID']);
                $row['PAGE_TRANSCRIPTION'] = '';
                $pages[] = $row;
            } else {
                throw new Exception("WRONG TRANSCRIPTION TYPE SET IN TRIERESULT ROW");
            }
        }

        $result = array(
            'wordzones' => $wordzones,
            'pages' => $pages,
            'lines' => $lines,
            'command' => $this->__toString()
        );

        return $result;
    }

    public function execute() {
        $outputLines = $this->getOutputLines();
        $result = $this->convertLines($outputLines);
        $totalAvailable = $this->getTotalAvailable();
        $result['total'] = $totalAvailable;
        return $result;
    }

    private function getLineTranscription($pageId, $lineNo) {
        $page = S4AAAS_Model_Page::fetchByNavisId($pageId);
        if ($page) {
            $line = S4AAAS_Model_Line::getLineFromPage($page, $lineNo);
            if ($line) {
                return $line->getTranscript();
            } else {
                return '';
            }
        } else {
            return '';
        }
    }

    private function getPageTranscription($pageId) {
        $page = S4AAAS_Model_Page::fetchByNavisId($pageId);
        if ($page) {
            return $page->getTranscript();
        } else {
            return '';
        }
    }

    private function getExtraData($row) {
        $extraData = array();

        if (isset($this->bookShortNames[$row[4]])) {
            $extraData['SHORT_NAME'] = $this->bookShortNames[$row[4]];
        }
		
		$page = S4AAAS_Model_Page::fetchByNavisId($row[4] . '_' . $row[5]);
		$ratio = 800 / $page->getOrigWidth();

        $string = $row[9];
        $prefixString = $row[4] . '_' . $row[5] . '-line-' . $row[6] . '-';
        $parts = explode($prefixString, $string, 2);

        // some row arrays have a messed up '$row[9]' entry
        // should check where this comes from
        if (!isset($parts[1])) {
            return;
        }

        $otherParts = explode('-', $parts[1]);
		
        if (count($otherParts) > 1) {
            $extraData['Y1'] = ceil($this->leftRemove(substr($otherParts[0], 3), '0') * $ratio);
            $extraData['Y2'] = ceil($this->leftRemove(substr($otherParts[1], 3), '0') * $ratio);
        }
        if (count($otherParts) > 7) {
            $extraData['X'] = ceil($this->leftRemove(substr($otherParts[4], 2), '0') * $ratio);
            $extraData['Y'] = ceil($this->leftRemove(substr($otherParts[5], 2), '0') * $ratio);
            $extraData['W'] = ceil($this->leftRemove(substr($otherParts[6], 2), '0') * $ratio);
            $extraData['H'] = ceil($this->leftRemove(substr($otherParts[7], 2), '0') * $ratio);
        }

        return $extraData;
    }

    private function fillBookShortNamesArray() {
        $books = S4AAAS_Model_Book::getAllBooks();
        foreach ($books as $book) {
            $monkDir = $book->getMonkDir();
            $shortName = $book->getShortName();
            $this->bookShortNames[$monkDir] = $shortName;
        }
    }

    private static function leftRemove($string, $char) {
        $result = ltrim($string, $char);
        if ($result == "" && $string != "") {
            return $char;
        } else {
            return $result;
        }
    }

    public function getSuggestions() {
        $result = array();
        foreach ($this->getOutputLines() as $line) {
            $row = explode("\t", $line);
            $text = $row[1];
            if (!in_array($text, $result))
                $result[] = $text;
        }
        return $result;
    }

}

?>
