<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initDoctype()
    {
        $this->bootstrap('view');
        $view = $this->getResource('view');
		$view->setEncoding('UTF-8');
        $view->doctype('XHTML1_STRICT');
    }

    protected function _initConfig() {
        $config = new Zend_Config($this->getOptions(), true);
        Zend_Registry::set('Config', $config);
        return $config;
    }
	
	protected function _initCutout()
	{
		$cutout = $this->getOption('cutout');
		$cutoutDataPath = $cutout['data']['path'];
		$cutoutRdfPath = $cutout['rdf']['path'];
		Zend_Registry::set('cutoutDataPath', $cutoutDataPath);
		Zend_Registry::set('cutoutRdfPath', $cutoutRdfPath);
	}
	
	protected function _initImages()
	{
		$images = $this->getOption('images');
		Zend_Registry::set('pageImagesPath', $images['pages']['path']);
	}
	
	protected function _initLogin()
	{
		$login = $this->getOption('login');
		$makeMonkPwPath = $login['makemonkpw']['path'];
		Zend_Registry::set('makeMonkPwPath', $makeMonkPwPath);
	}
	
	protected function _initServerName()
	{
		$server = $this->getOption('server');
		$serverName = $server['name'];
		Zend_Registry::set('servername', $serverName);
	}

    protected function _initRouter() {
        $ctrl = Zend_Controller_Front::getInstance();
        $router = $ctrl->getRouter();
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/routes.ini');
        $router->addConfig($config, 'routes');
    }

    protected function _initTrie() {
        $config = Zend_Registry::get('Config');
		$trieConfig = $config->get('trie')->toArray();
        S4AAAS_Trie_Query::initSettings($trieConfig); 
		Zend_Registry::set('labelsearchlogpath', $trieConfig['log']['path']);
    }
	/*
	protected function _initImageSettings()
	{
		$imagePath = 
		Zend_Registry::set('imagesPath', $imagePath);
	}
	 * 
	 */
}
