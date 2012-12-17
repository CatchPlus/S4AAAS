<?php

class AuthController extends Zend_Controller_Action
{

    public function init() {
    }

    public function loginAction() {
        $request = $this->getRequest();
        try {
            $username=$request->getParam('username',null);
            if($username==null) { 
                $this->view->status = "USERNAME NOT SPECIFIED";
                return;
            }
			
			$postData = $request->getRawBody();
			$pwParts = explode('=', $postData);
			$password = $pwParts[1];
			
            if($password==null) { 
                $this->view->status = "PASSWORD NOT SPECIFIED";
                return;
            }
            $user = S4AAAS_Model_User::fetchByMonkId($username);
            if($user===null) $this->view->status = "AUTHENTICATION FAILED";
            else if(!$user->hasPassword($password)) $this->view->status = "AUTHENTICATION FAILED";
            else {
                $auth = new S4AAAS_Model_AuthToken($user);
                $auth->insert();
                $this->view->authenticationToken = $auth->getToken();
				
				if ($user->canTranscribeABook())
				{
					$this->view->transcribe = 'YES';
				}
				else
				{
					$this->view->transcribe = 'NO';
				}
				
				
                $this->view->status = "OK";
            }
        } catch(Exception $e) {
            $this->view->status = "ERROR";
        }
    }


}

