<?php

class S4AAAS_Model_Db_LabelDao extends Zend_Db_Table_Abstract {
    protected $_name = 'LABELS';
    protected $_primary = array('ID');
    protected $_sequence = FALSE;
    
    public function getName() { return $this->_name; }
}

/**
 * Class that represents a user for creating (etc) and authentication
 * 
 * @author Herbert Kruitbosch
 */
class S4AAAS_Model_Label extends S4AAAS_Model_Abstract {
    private static $_dao=null;
    protected $_magicName = 'LABELS';
    protected $_magicPrimary = array('ID');
    protected $_magicProperties = array(
        'ID' => null,
        'LINE_ID' => null,
        'BYUSER_ID' => null,
        'TIMESTAMP' => null,
		'ROI' => null,
        'X' => null,
        'Y' => null,
        'WIDTH' => null,
        'HEIGHT' => null,
        'LABEL_TEXT' => null,
		'STATUS' => null
    );
    
    public function __construct($data=null) {
        if($data!==null) {
			if (isset($data['ID']))
			{
				$this->setId($data['ID']);
			}

            $this->setLineId($data['LINE_ID']);
            $this->setByuserId($data['BYUSER_ID']);
			$this->setTimestamp($data['TIMESTAMP']);
			$this->setRoi($data['ROI']);
			$this->setX($data['X']);
			$this->setY($data['Y']);
			$this->setWidth($data['WIDTH']);
			$this->setHeight($data['HEIGHT']);
			$this->setLabelText($data['LABEL_TEXT']);
			$this->setStatus($data['STATUS']);
        }
    }
	
	
	public function getLine()
	{
		$lineId = $this->getLineId();
		
		$line = S4AAAS_Model_Line::fetchById($lineId);
		
		return $line;
	}
	
    public static function fetchById($id) {
        return self::fetchByField(self::getDao(), 'ID', $id, new S4AAAS_Model_Label());
    }
    
    public static function getDao() {
        if(self::$_dao===null) self::$_dao = new S4AAAS_Model_Db_LabelDao();
        return self::$_dao;
    }
	
	private static function getSearchSelect($searchData, $justCountTotal=false)
	{
		$word = $searchData['word'];
		$match = $searchData['match'];
		$offset = $searchData['offset'];
		$rows = $searchData['rows'];
		$institutions = $searchData['institutions'];
		$collections = $searchData['collections'];
		$books = $searchData['books'];
		$annotationTypes = $searchData['annotationTypes'];

		$institution = new S4AAAS_Model_Institution();
        $collection = new S4AAAS_Model_Collection();
        $book = new S4AAAS_Model_Book();
        $page = new S4AAAS_Model_Page();
		$line = new S4AAAS_Model_Line();
		
		
		$select = self::getDao()->select()
				->setIntegrityCheck(false);
		if ($justCountTotal)
		{
			$select->from(array('la' => 'LABELS'), array('count(*) as total'));
		}
		else
		{
			$select->from(array('la' => 'LABELS'));
		}
		$select->join(array('li' => 'LINES'), 'li.ID = la.LINE_ID', $line->prefixedFields('li', 'line_'))
				->join(array('p' => 'PAGES'), 'p.ID = li.PAGE_ID', $page->prefixedFields('p', 'page_'))
				->join(array('b' => 'BOOKS'), 'b.ID = p.BOOK_ID', $book->prefixedFields('b', 'book_'))
				->join(array('c' => 'COLLECTIONS'), 'c.ID = b.COLLECTION_ID', $collection->prefixedFields('c', 'collection_'))
				->join(array('i' => 'INSTITUTIONS'), 'i.ID = c.INSTITUTION_ID', $institution->prefixedFields('i', 'institution_'))
				;
		
		// select the match criteria
		if ($match == 'prefix')
		{
			$select->where("la.LABEL_TEXT LIKE '$word%'");			
		}
		elseif($match == 'suffix')
		{
			$select->where("la.LABEL_TEXT LIKE '%$word'");			
		}
		elseif($match == 'wildcard')
		{
			$select->where("la.LABEL_TEXT LIKE '%$word%'");			
		}
		elseif($match == 'exact')
		{
			$select->where("la.LABEL_TEXT = '$word'");
		}
		else
		{
			throw new Exception("WRONG MATCH SUPPLIED match: ($match)");
		}

		// limit by some institutions
		if (!empty($institutions))
		{
			$whereInstitutions = "";
			foreach ($institutions as $institution)
			{
				$whereInstitutions .= "i.MONK_ID = '$institution' OR ";
			}
			$whereInstitutions = substr_replace($whereInstitutions ,"",-3);
			$select->where($whereInstitutions);
		}
		
		// limit by some collections
		if (!empty($collections))
		{
			$whereCollections = "";
			foreach ($collections as $collection)
			{
				$whereCollections .= "c.MONK_ID = '$collection' OR ";
			}
			$whereCollections = substr_replace($whereCollections ,"",-3);
			$select->where($whereCollections);
		}
		
		// limit by some books
		if (!empty($books))
		{
			$whereBooks = "";
			foreach ($books as $book)
			{
				$whereBooks .= "b.MONK_DIR = '$book' OR ";
			}
			$whereBooks = substr_replace($whereBooks ,"",-3);
			$select->where($whereBooks);				
		}
		
		//@TODO order the right way
		$select->order(array(
			'b.MONK_DIR',
			'p.PAGE_NO',
			'li.LINE_NO'
			));
		
		// limit the search results
		if (!$justCountTotal)
		{
			$select->limit($rows, $offset);
		}
		
		return $select;
	}
	
	public static function searchTotal($searchData)
	{
		$select = self::getSearchSelect($searchData, true);
		$rows = self::getDao()->fetchAll($select);
		
		return $rows[0]->total;
	}
	
	public static function search($searchData)
	{
		$select = self::getSearchSelect($searchData);
		
        $rows = self::getDao()->fetchAll($select);

		$result = array();
		foreach($rows as $row)
		{
			$result[] = $row->toArray();
		}
		return $result;
	}
	
	public function setCoordinatesFromXml($roiXml)
	{
		$numPositions =  count($roiXml->pos);
		$roiString = '';
		for ($idx = 0; $idx < $numPositions; $idx++)
		{
			$pos = $roiXml->pos[$idx];
			$roiString .= $pos->x.','.$pos->y.'-';
		}
		$roiString = substr($roiString,0,-1);
		
		$this->setRoi($roiString);
		
		$this->setOldCoordinatesFromRoi();
	}
	
	private function setOldCoordinatesFromRoi()
	{
		$shear = $this->getLine()->getPage()->getBook()->getShear();
		$roi = $this->getRoi();
		$xywh = self::roi_string2xywh($shear, $roi);
		$this->setX($xywh['x']);
		$this->setY($xywh['y']);
		$this->setWidth($xywh['w']);
		$this->setHeight($xywh['h']);
	}

	public static function fetchAllWithHigherStuff()
	{

		
		$select = self::getDao()->select()
				->setIntegrityCheck(false)
				->from(array('l' => 'LABELS'))
				->join(array('li' => 'LINES'), 'li.ID = l.LINE_ID')
				->join(array('p' => 'PAGES'), 'p.ID = li.PAGE_ID')
				->join(array('b' => 'BOOKS'), 'b.ID = p.BOOK_ID')
				->join(array('c' => 'COLLECTIONS'), 'c.ID = b.COLLECTION_ID')
				->join(array('i' => 'INSTITUTIONS'), 'i.ID = c.INSTITUTION_ID')
		;

		
        $rows = self::getDao()->fetchAll($select);
		
		foreach($rows as $row)
		{
			print_r($row);
		}
	}
	
	public function getRoiArray()
	{
		return self::roi2array($this->getRoi());
	}
	
	public static function roi2array($roi)
	{
		$xy = explode ('-', $roi);
		$roiArray = array();
		foreach ($xy as $pair)
		{
			$parts = explode(',', $pair);
			$roiArray[] = array('x' => $parts[0], 'y' => $parts[1]);
		}
		
		return $roiArray;
	}
	
	public static function roi_string2xywh_4positions($roi)
	{
		$positionStrings = explode('-', $roi);
		$positions = array();
		$xPositions = array();
		$yPositions = array();
		foreach($positionStrings as $positionString)
		{
			$position = explode(',', $positionString);
			$xPositions[] = $position[0];
			$yPositions[] = $position[1];
			$positions[] = $position;
		}
		
		sort($xPositions);
		sort($yPositions);
		
		$rec = array();
		$rec['x'] = ($xPositions[0] + $xPositions[1])/2;
		$rec['y'] = ($yPositions[0] + $yPositions[1])/2;
		$rec['w'] = ($xPositions[2] + $xPositions[3])/2 - $rec['x'];
		$rec['h'] = ($yPositions[2] + $yPositions[3])/2 - $rec['y'];
		
		return $rec;
	}
	
	public static function roi_string2xywh ($shear, &$roi)
	{
//		die ($shear.' - '.$roi);
		$xy = explode ('-', $roi);
		$n  = count ($xy);
		
		// Use a different method with there are 4 position in the roi.
		// This different method should be more correct, as the method below
		// returns the outline of the roi
//		if ($n == 4)
//		{
//			return self::roi_string2xywh_4positions($roi);
//		}
		
		$t  = tan ($shear * 0.0174532925);
		$xmin = "";
		$xmax = "";
		$ymin = "";
		$ymax = "";

		for ($i = 0; $i < $n; $i++) {
			$c  = explode (',', $xy[$i]);
			$xo = (int) (0.5 + $c[0] + $c[1] * $t);
			$yo = (int) (0.5 + $c[1]);

			if ((string)$xmin == "" || (int)$xmin > (int)$xo) $xmin = $xo;
			if ((string)$xmax == "" || (int)$xmax < (int)$xo) $xmax = $xo;

			if ((string)$ymin == "" || (int)$ymin > (int)$yo) $ymin = $yo;
			if ((string)$ymax == "" || (int)$ymax < (int)$yo) $ymax = $yo;

			// echo $xmin . ' ' . $xmax . ' ' . $ymin . ' ' . $ymax . "\n";
			// echo $xo . ' ' . $yo . "\n";
		}
		$ret = array();

		$ret['x'] = $xmin;
		$ret['y'] = $ymin;
		$ret['w'] = $xmax - $xmin;
		$ret['h'] = $ymax - $ymin;

		return $ret;
	}

	public static function roi_xywh2string ($shear, &$xywh)
	{
		$t  = tan ($shear * 0.0174532925);

		$yt =       $xywh['y'];
		$yb = $yt + $xywh['h'];
		$xl =       $xywh['x'];
		$xr = $xl + $xywh['w'];

		$oft = -$t * $yt;
		$ofb = -$t * $yb;

		return       (int)(0.5+$xl+$oft) . "," . $yt .
			   "-" . (int)(0.5+$xr+$oft) . "," . $yt .
			   "-" . (int)(0.5+$xr+$ofb) . "," . $yb .
			   "-" . (int)(0.5+$xl+$ofb) . "," . $yb;
	}
}

?>