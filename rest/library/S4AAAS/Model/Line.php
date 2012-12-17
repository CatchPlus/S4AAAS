<?php

class S4AAAS_Model_Db_LineDao extends Zend_Db_Table_Abstract {
    protected $_name = 'LINES';
    protected $_primary = array('ID');
    protected $_sequence = FALSE;
    
    public function getName() { return $this->_name; }
}

/**
 * Class that represents a user for creating (etc) and authentication
 * 
 * @author Herbert Kruitbosch
 */
class S4AAAS_Model_Line extends S4AAAS_Model_Abstract {
    private static $_dao=null;
    protected $_magicName = 'LINES';
    protected $_magicPrimary = array('ID');
    protected $_magicProperties = array(
        'ID'=>null,
        'PAGE_ID'=>null,
        'LINE_NO'=>null,
        'Y_TOP'=>null,
        'Y_BOT'=>null,
        'TRANSCRIPT'=>null,
		'IMAGE_RENDERED'=>0
    );
    
    public function __construct($data=null) {
        if($data!==null) {
			if (isset($data['ID']))
			{
				$this->setId($data['ID']);
			}
            $this->setPageId($data['PAGE_ID']);
            $this->setLineNo($data['LINE_NO']);
			$this->setYTop($data['Y_TOP']);
			$this->setYBot($data['Y_BOT']);
			$this->setTranscript($data['TRANSCRIPT']);
			if (isset($data['IMAGE_RENDERED']))
			{
				$this->setImageRendered($data['IMAGE_RENDERED']);
			}
        }
    }
	
	public function getPage()
	{
		$pageId = $this->getPageId();
		
		$page = S4AAAS_Model_Page::fetchById($pageId);
		
		return $page;
	}
	

	public function getLabels()
	{
		$lineId = $this->getId();

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
	
	public static function mergeLabels($dbLabels, $trieLabels)
	{
		// minimum required overlay to see of labels are at the same location
		$minimumOverlay = 95; //percent
		$labels = array(); //the merged labels

		$dbLabelsIdx = 0;
		$trieLabelsIdx = 0;

//		echo '('.count($dbLabels).'-'.count($trieLabels).')';
		// while at least some of the arrays has items
        while (isset($dbLabels[$dbLabelsIdx]) || isset($trieLabels[$trieLabelsIdx]))
		{

            // check if one of the arrays isn't already empty (makes it easy)
            if (!isset($dbLabels[$dbLabelsIdx])) // we have had all items in this array
			{
				while (isset($trieLabels[$trieLabelsIdx]))
				{
					$thingy = self::trieLabelToDbLabel($trieLabels[$trieLabelsIdx]);
					$labels[] = $thingy;
					$trieLabelsIdx++;
				}
				return $labels;
            }
			elseif (!isset($trieLabels[$trieLabelsIdx])) // we have had all items in this array
			{
				return array_merge($labels, $dbLabels);
            }
			
			// lines are the same, so compare coordinates
			$overlay = self::getOverlay($dbLabels[$dbLabelsIdx], $trieLabels[$trieLabelsIdx]);
			
			// labels are the same
			if ($overlay >= $minimumOverlay)
			{
				$labels[] = $dbLabels[$dbLabelsIdx];
				$dbLabelsIdx++;
				$trieLabelsIdx++;
				continue;				
			}
			
			$dbX = $dbLabels[$dbLabelsIdx]->getX();
			$trieX = $trieLabels[$trieLabelsIdx]['X'];
			
			if ($dbX <= $trieX)
			{
				$labels[] = $dbLabels[$dbLabelsIdx];
				$dbLabelsIdx++;
				continue;
			}
			elseif ($trieX < $dbX)
			{
				$labels[] = self::trieLabelToDbLabel($trieLabels[$trieLabelsIdx]);
				$trieLabelsIdx++;
				continue;
			}
		}

		return $labels;	
	}
	
	public function getMergedLabels()
	{
		// minimum required overlay to see of labels are at the same location
		$minimumOverlay = 95; //percent
		$times = array();
		
		$times['getDbLabels']['start'] = microtime(true);		
		$dbLabels = $this->getLabels();
		$times['getDbLabels']['end'] = microtime(true);
		

		$times['getTrieLabels']['start'] = microtime(true);		
		$trieLabels = $this->getTrieLabels();
		$times['getTrieLabels']['end'] = microtime(true);
		$labels = array(); //the merged labels

		$dbLabelsIdx = 0;
		$trieLabelsIdx = 0;

		$times['mergingLoop']['start'] = microtime(true);
		// while at least some of the arrays has items
        while (isset($dbLabels[$dbLabelsIdx]) || isset($trieLabels[$trieLabelsIdx]))
		{

            // check if one of the arrays isn't already empty (makes it easy)
            if (!isset($dbLabels[$dbLabelsIdx])) // we have had all items in this array
			{
				echo 'merging leftover trielabels';
				while (isset($trieLabels[$trieLabelsIdx]))
				{
					$thingy = $this->trieLabelToDbLabel($trieLabels[$trieLabelsIdx]);
					$labels[] = $thingy;
					$trieLabelsIdx++;
				}
				return $labels;
            }
			elseif (!isset($trieLabels[$trieLabelsIdx])) // we have had all items in this array
			{
				echo "merging leftover dblabels";
				return array_merge($labels, $dbLabels);
            }
			
			// lines are the same, so compare coordinates
			$overlay = S4AAAS_Model_Line::getOverlay($dbLabels[$dbLabelsIdx], $trieLabels[$trieLabelsIdx]);
			
			// labels are the same
			if ($overlay >= $minimumOverlay)
			{
				echo 'there is an overlay';
				$labels[] = $dbLabels[$dbLabelsIdx];
				$dbLabelsIdx++;
				$trieLabelsIdx++;
				continue;				
			}
			
			$dbX = $dbLabels[$dbLabelsIdx]->getX();
			$trieX = $trieLabels[$trieLabelsIdx]['X'];
			
			if ($dbX <= $trieX)
			{
				echo 'add dblabel';
				$labels[] = $dbLabels[$dbLabelsIdx];
				$dbLabelsIdx++;
				continue;
			}
			elseif ($trieX < $dbX)
			{
				echo 'add trielabel';
				$labels[] = $this->trieLabelToDbLabel($trieLabels[$trieLabelsIdx]);
				$trieLabelsIdx++;
				continue;
			}
		}
		$times['mergingLoop']['end'] = microtime(true);
		
		foreach ($times as $name => $values)
		{
			$times[$name]['diff'] = $values['end'] - $values['start'];
		}
		
		print_r($times);
		die;
		return $labels;
		
	}
	
	private static function trieLabelToDbLabel($trieLabel)
	{
		$label = new S4AAAS_Model_Label();
		$label->setRoi($trieLabel['ROI']);
		$label->setX($trieLabel['X']);
		$label->setY($trieLabel['Y']);
		$label->setWidth($trieLabel['W']);
		$label->setHeight($trieLabel['H']);
		$label->setLabelText($trieLabel['TEXT']);
		$label->setStatus('VERIFIED');
		
		return $label;
	}
	
	public function getTrieLabels()
	{
		$labels = S4AAAS_Trie_Query::getLineLabels($this);
		return $labels['wordzones'];
	}
	
	private static function getOverlay($dbItem, $trieItem)
	{
		$dbX1 = $dbItem->getX();
		$trieX1 = $trieItem['X'];
		$dbX2 = $dbX1 + $dbItem->getWidth();
		$trieX2 = $trieX1 + $trieItem['W'];
		$x1Max = max($dbX1, $trieX1);
		$x2Min = min($dbX2, $trieX2);
		$overLayX = max(($x2Min - $x1Max), 0);

		$dbY1 = $dbItem->getY();
		$trieY1 = $trieItem['Y'];
		$dbY2 = $dbY1 + $dbItem->getHeight();
		$trieY2 = $trieY1 + $trieItem['H'];
		$y1Max = max($dbY1, $trieY1);
		$y2Min = min($dbY2, $trieY2);
		$overLayY = max(($y2Min - $y1Max), 0);
		
		$dbSurface = $dbItem->getWidth()*$dbItem->getHeight();
		$trieSurface = $trieItem['W']*$trieItem['H'];
		$overlaySurface = $overLayX*$overLayY;
		
		$overlayPercentage = $overlaySurface/($dbSurface + $trieSurface - $overlaySurface);

		return $overlayPercentage;
	}
	
    public static function fetchById($id) {
        return self::fetchByField(self::getDao(), 'ID', $id, new S4AAAS_Model_Line());
    }
	
    public static function getLineFromPage($page, $lineNo)
    {
        $select = self::getDao()->select()
                ->from(array('l' => 'LINES'))
                ->where('l.PAGE_ID'. ' = ? ', $page->getId())
                ->where('l.LINE_NO'. ' = ? ', $lineNo);

        $rows = self::getDao()->fetchAll($select);

        if (count($rows) > 0) {
                return new S4AAAS_Model_Line($rows[0]);
        } else {
                return null;
        }

    }

    public static function getLines($pagesAndLineNumbers) {
		$results = array();
		if (empty($pagesAndLineNumbers))
		{
			return $results;
		}
		
		
        $dao = self::getDao();
        $select = $dao->select()
                ->setIntegrityCheck(false)
                ->from(array('l' => 'LINES'))
                ->join(array('p' => 'PAGES'), 'l.PAGE_ID = p.ID', array('PAGE_NAVIS_ID' => 'p.NAVIS_ID'));
        
        $where = '';
        foreach($pagesAndLineNumbers as $pageAndLineNumber) {
            if($where!=='') $where .= ' OR ';
            else $where .= "\n    ";
            $where .= '(';
            $where .= $dao->getAdapter()->quoteInto("p.NAVIS_ID = ?", $pageAndLineNumber['pageid']);
            $where .= ' AND ';        
            $where .= $dao->getAdapter()->quoteInto("l.LINE_NO = ?", $pageAndLineNumber['lineno']);
            $where .= ")\n";
        }
        $select->where($where);
        $rows = self::getDao()->fetchAll($select);

        foreach($rows as $row) {
            if(!isset($results[$row['PAGE_NAVIS_ID']])) $results[$row['PAGE_NAVIS_ID']] = array();
            $results[$row['PAGE_NAVIS_ID']][$row['LINE_NO']] = new S4AAAS_Model_Line($row);
        }
		
        return $results;
    }
	
    public static function fetchByPageIdAndLineNo($pageId, $lineNo) {
        $dao = new S4AAAS_Model_Db_LineDao();
        $select = self::plainSelect($dao)
                ->where('PAGE_ID = ?', $pageId)
                ->where('LINE_NO = ?', $lineNo);
        $rows = $dao->fetchAll($select);
        if(count($rows) > 0) {
            $result = new S4AAAS_Model_Line();
            $result->populate($rows[0]->toArray());
            return $result;
        }
        return null;
    }
	
	public static function getLine($navisId, $pageNo, $lineNo)
	{
		$page = S4AAAS_Model_Page::fetchByNavisId($navisId.'_'.$pageNo);
		if ($page)
		{
			return self::getLineFromPage($page, $lineNo);
		}
		
		return null;
	}
	    
    public static function getDao() {
        if(self::$_dao===null) self::$_dao = new S4AAAS_Model_Db_LineDao();
        return self::$_dao;
    }
	
	public function createImageToken()
	{
		$imageLookup = new S4AAAS_Model_ImageLookup();
		$imageLookup->setType('LINE');
		$imageLookup->setObjectId($this->getId());
		$insertId = $imageLookup->insert();
		$imageLookup->setId($insertId);
		return $imageLookup;
	}
}

?>