<?php

class S4AAAS_Model_Db_AuthTokenDao extends Zend_Db_Table_Abstract {
    protected $_name = 'AUTHTOKENS';
    protected $_primary = array('ID');
    protected $_sequence = FALSE;
    
    public function getName() { return $this->_name; }
}

/**
 * Class that represents a user for creating (etc) and authentication
 * 
 * @author Herbert Kruitbosch
 */
class S4AAAS_Model_AuthToken extends S4AAAS_Model_Abstract {
    private static $_dao=null;
    protected $_magicName = 'AUTHTOKENS';
    protected $_magicPrimary = array('ID');
    protected $_magicProperties = array(
        'ID' => null,
        'TOKEN' => '',
        'USER_ID' => '',
        'VALID_UNTIL' => ''
    );
    
    private function genToken($length=32) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $string = '';    
        for ($p = 0; $p < $length; $p++) {
            $string .= $characters[mt_rand(0, strlen($characters)-1)];
        }
        return $string;
    }
    
    public function __construct($user=null, $secondsValid = 3600) {
        if($user!==null) {
            $this->setUserId($user->getId());
            $this->setToken($this->genToken());
            $date = new DateTime();
            $date->modify("+" . $secondsValid . " seconds");
            $this->setValidUntil($date->format("Y-m-d H:i:s"));
        }
    }
    
    public static function fetchByToken($token) {
        return self::fetchByField(self::getDao(), 'TOKEN', $token, new S4AAAS_Model_AuthToken());
    }
    
    public function getUser() {
        return S4AAAS_Model_User::fetchById($this->getUserId());
    }
    
    public static function getAuthenticatedUser($token) {
        $auth = self::fetchByToken($token);
        if($auth===null) return null;
        $validUntil = new DateTime($auth->getValidUntil());
        $now = new DateTime();
        if($validUntil<$now) return null;
        return $auth->getUser();
    }
    
    public static function getDao() {
        if(self::$_dao===null) self::$_dao = new S4AAAS_Model_Db_AuthTokenDao();
        return self::$_dao;
    }
}

?>