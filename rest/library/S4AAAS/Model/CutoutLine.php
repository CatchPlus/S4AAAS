<?php

class S4AAAS_Model_Db_CutoutLineDao extends Zend_Db_Table_Abstract {
    protected $_name = 'CUTOUTLINES';
    protected $_primary = array('ID', 'CUTOUTTOKEN_ID');
    protected $_sequence = FALSE;
    
    public function getName() { return $this->_name; }
}

class S4AAAS_Model_CutoutLine extends S4AAAS_Model_Abstract {
    private static $_dao=null;
    protected $_magicName = 'CUTOUTLINES';
    protected $_magicPrimary = array('CUTOUTTOKEN_ID', 'CUTOUTTOKEN_ID');
    protected $_magicProperties = array(
        'CUTOUTTOKEN_ID' => '',
		'LINE_ID' => '',	
		'Y1' => '',
		'Y2' => ''
    );
	
	public function __construct($row=null)
	{
		if ($row)
		{
			$this->setCutouttokenId($row['CUTOUTTOKEN_ID']);
			$this->setLineId($row['LINE_ID']);
			$this->setY1($row['Y1']);
			$this->setY2($row['Y2']);
		}
	}
	
	public static function fetchFromToken($tokenId)
	{
		$select = self::getDao()->select()
				->from(array('c' => 'CUTOUTLINES'))
				->where('c.CUTOUTTOKEN_ID'. ' = ? ', $tokenId)
			;
		
		$rows = self::getDao()->fetchAll($select);
		$lines = array();
		foreach($rows as $row)
		{
			$lines[] = new S4AAAS_Model_CutoutLine($row);
		}

		return $lines;
	}
	
	public static function fetchByTokenIdAndLineId($tokenId, $lineId)
	{
		$select = self::getDao()->select()
				->from(array('c' => 'CUTOUTLINES'))
				->where('c.CUTOUTTOKEN_ID'. ' = ? ', $tokenId)
				->where('c.LINE_ID'. ' = ? ', $lineId)
			;
		
		$rows = self::getDao()->fetchAll($select);
		if (count($rows)==0)
		{
			return null;
		}

		return new S4AAAS_Model_CutoutLine($rows[0]);
	}
        
    public static function getDao() {
        if(self::$_dao===null) self::$_dao = new S4AAAS_Model_Db_CutoutLineDao();
        return self::$_dao;
    }
}

?>