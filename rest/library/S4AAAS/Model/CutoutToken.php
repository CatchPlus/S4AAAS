<?php

class S4AAAS_Model_Db_CutoutTokenDao extends Zend_Db_Table_Abstract {
    protected $_name = 'CUTOUTTOKENS';
    protected $_primary = array('ID');
    protected $_sequence = FALSE;
    
    public function getName() { return $this->_name; }
}

class S4AAAS_Model_CutoutToken extends S4AAAS_Model_Abstract {
    private static $_dao=null;
    protected $_magicName = 'CUTOUTTOKENS';
    protected $_magicPrimary = array('ID');
    protected $_magicProperties = array(
        'ID' => null,
		'IPADDRESS' => '',	
        'TOKEN' => '',
		'ORIG_WIDTH' => '',
		'ANGLE' => '',
		'X1' => '',
		'Y1' => '',
		'X2' => '',
		'Y2' => '',
        'VALID_UNTIL' => '',
		'STATUS' => ''
    );
    
    private function genToken($length=32) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $string = '';    
        for ($p = 0; $p < $length; $p++) {
            $string .= $characters[mt_rand(0, strlen($characters)-1)];
        }
        return $string;
    }
    
    public function __construct($secondsValid = 3600)
	{
		$this->setToken($this->genToken());
		$date = new DateTime();
		$date->modify("+" . $secondsValid . " seconds");
		$this->setValidUntil($date->format("Y-m-d H:i:s"));
		$this->setStatus('UNPROCESSED');
    }
	
	public function clearLineStrips()
	{
		$tokenId = $this->getId();
		
		$dao = S4AAAS_Model_CutoutLine::getDao();
		$where = $dao->getAdapter()->quoteInto('CUTOUTTOKEN_ID = ?', $tokenId);
		$dao->delete($where);
		
		return true;
	}
    
    public static function fetchByToken($token)
	{
        return self::fetchByField(self::getDao(), 'TOKEN', $token, new S4AAAS_Model_CutoutToken());
    }
    
    public static function getDao() {
        if(self::$_dao===null) self::$_dao = new S4AAAS_Model_Db_CutoutTokenDao();
        return self::$_dao;
    }
}

?>