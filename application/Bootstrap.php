<?php
class Bootstrap extends App_Application_Bootstrap_Bootstrap
{
    protected function _initCacheLoader() {
	    $cache = APPLICATION_PATH . '/../data/cache/plugin_loader.php';
	    if (file_exists($cache)) include_once $cache;
	    Zend_Loader_PluginLoader::setIncludeFileCache($cache);
	    //App_Resource_Container::init(); // нужно чтобы сработал кеш	   
	}
	
	protected function _initConfig() {
    	$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
    	return $config;
    }
    
    protected function _initRouter() {
    	include APPLICATION_PATH . '/configs/routes.php';    	
    	return $router;
    }
    
    protected function _initNavigation() {
		require_once 'Symfony/Yaml/sfYamlParser.php';
		$yaml  = new sfYamlParser();
		$pages = $yaml->parse(file_get_contents(APPLICATION_PATH . '/configs/navigation.yml'));
		$navigation = new Zend_Navigation($pages['navigation']);
		return $navigation;
	}
	
    protected function _initViewBasePathSpec() {
		$this->bootstrap('view');		
		$view = $this->getResource('view');
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
		$viewRenderer->setViewBasePathSpec(':moduleDir');
		$viewRenderer->setView($view);			
	}

}

