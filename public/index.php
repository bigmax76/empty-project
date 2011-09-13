<?php
//header('P3P: CP="CAO PSA OUR"');

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

require_once 'Zend/Loader.php';
require_once 'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->registerNamespace('App_');
$autoloader->setDefaultAutoloader(array('App_Application_Bootstrap_Bootstrap', 'autoload'));

App_Stat::start();

$application = new App_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini',
    getCache()
);
$application->bootstrap()
            ->run();
            
App_Stat::show();

//------------------------
function getCache(){
	if (APPLICATION_ENV == 'production')  
	    return getApcCache();
	return getFileCache();
}

function getApcCache() {
	$cache = new Zend_Cache_Core(array('automatic_serialization'=>true));		       
	$backend = new Zend_Cache_Backend_Apc();	     	    
	$cache->setBackend($backend);
	return $cache;
}

function getFileCache() {
	$frontendOptions = array('lifetime' => 7200,'automatic_serialization' => true); 
	$backendOptions  = array(
		'cache_dir' => APPLICATION_PATH . '/../data/cache/models/',
	    'hashed_directory_level' => 1,
	    'hashed_directory_umask' => '0777',
	    'cache_file_umask'       => '0666',	
    ); 
	$cache = Zend_Cache::factory('Core','File',$frontendOptions, $backendOptions);
	return $cache;
}