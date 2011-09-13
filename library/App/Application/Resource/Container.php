<?php
require_once 'Symfony/DependencyInjection/sfServiceContainerAutoloader.php';
require_once 'Symfony/Yaml/sfYaml.php';

class App_Application_Resource_Container extends App_Model_Cache
{
	protected static $_container; 
	protected static $_classes = array();
	
	public static function get($service) {
		return self::getContainer()->getService($service);
	}
	
	public static function getContainer() {
		if (null === self::$_container) 
			throw new App_Exception('Не задан service container');		
		return self::$_container;
	}
	
    public static function setContainer(sfServiceContainerBuilder $container) {
		self::$_container = $container;
	}

	public static function init() {
		require_once 'Symfony/DependencyInjection/sfServiceContainerAutoloader.php';
        require_once 'Symfony/Yaml/sfYaml.php';
        sfServiceContainerAutoloader::register();
	}
	
    public static function loadYaml($file) {
    	sfServiceContainerAutoloader::register();
		$container = new sfServiceContainerBuilder();
		$loader    = new sfServiceContainerLoaderFileYaml($container);
		$loader->load($file);
		return $container;
    }
    
    public static function getClass($class){
    	$store = self::$_classes; // так быстрее
		if (isset($store[$class])) 
			return $store[$class];
					
		self::$_classes[$class] = new $class;	
		return self::$_classes[$class];
	}
	
	/**
	 * иногда возникает необходимость сбросить данные
	 */
	public static function resetClasses() {
		self::$_classes = array();
	}
}