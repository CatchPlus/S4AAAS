<?php

/**
 * Abstract of a controller, containing a preDispatch to authentica whenever a
 * post-body is set.
 *
 * @author Herbert Kruitbosch
 */
class S4AAAS_Controller_Abstract extends Zend_Controller_Action {
    protected $user = null;
    private $initedUser=false;
    
    public function initUser() {
        if($this->initedUser)
		{
			return;
		}
		
        $this->initedUser=true;
        $request = $this->getRequest();
        $body = $request->getRawBody();
        if($body===false)
		{
            $this->user=null;
        }
		else
		{
            $authtoken =null;
            $xml = simplexml_load_string($body);
            foreach($xml->children() as $child)
			{
                if('authtoken' === $child->getName())
				{
                    $authtoken = sprintf("%s", $child);
                    break;
                }
            }
			
            if($authtoken!==null)
			{
                $this->user = S4AAAS_Model_AuthToken::getAuthenticatedUser($authtoken);
            }
        }
    }
    
    public function userHasRight($right = '') {
        $this->initUser();
        if($this->user===null) return false;
        return true;
    }
    
    public function getUser() {
        $this->initUser();
        return $this->user;
    }
	
	public function checkUserToken($token)
	{
		if (!$token)
		{
			return null;
		}

		$user = S4AAAS_Model_AuthToken::getAuthenticatedUser($token);
		if (!$user)
		{
			return null;
		}
		
		return $user;
	}
	
	public function AncoQuery($sql, $column = false)
	{
		$s = array();
		if ($q = mysql_query($sql, $this->dbConn) or exit(mysql_error()))
		{
			while ($r = mysql_fetch_array($q))
			{
				$column ? $s[] = $r[0] : $s[] = $r;
			}
		}
		return $s;
	}
	
	public static function getPageImagePath()
	{
		return Zend_Registry::get('pageImagesPath');
	}
	public function getLineImagePath()
	{
		$bootstrap = $this->getInvokeArg('bootstrap');
		$config = $bootstrap->getOptions();
		return $config['images']['lines']['path'];
	}
	public function getImageCutoutPath()
	{
		$bootstrap = $this->getInvokeArg('bootstrap');
		$config = $bootstrap->getOptions();
		return $config['images']['cutout']['path'];
	}
}

?>
