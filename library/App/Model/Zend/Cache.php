<?php 
class Model_Class_Cache
{
	static protected $className = 'Model_Class';	
	static protected $cacheName = 'models';
	
	/*
	 * этот код нужно перенести в абстрастный класс 
	 * при версии php >= 5.3  
	 */	
	public static function getById($id)
	{				
		$key = self::createKeyById($id); 
	    if (!Zend_Registry::isRegistered($key)) 
	    {
	    	$cache = App_Resource::getResource('cachemanager')
        		     ->getCache(self::$cacheName); 
	    	if (!$obj = $cache->load($key )) {
				$obj = new self::$className();
		        $obj->getById($id);		        	        
				$cache->save($obj,$key);
			}		    	
		    Zend_Registry::set($key, $obj);		   
		}		
		return Zend_Registry::get($key);		
	}
	
	public static function getByCode($code)
	{				
		$key = self::createKeyById($code); 
	    if (!Zend_Registry::isRegistered($key)) 
	    {
	    	$cache = App_Resource::getResource('cachemanager')
        		     ->getCache(self::$cacheName); 
	    	if (!$obj = $cache->load($key )) {
				$obj = new self::$className();
		        $obj->getByCode($code);
				$cache->save($obj,$key);
			}		    	
		    Zend_Registry::set($key, $obj);		   
		}		
		return Zend_Registry::get($key);		
	}
	
	
	/**
	 * Очистка кеша по id
	 */
	public static function clearById($id)
	{
		$key = self::createKeyById($id);
		$cache = App_Resource::getResource('cachemanager')
        		 ->getCache(self::$cacheName); 
		$cache->remove($key);
	}	
	
	/**
	 * Генерируем уникальный ключ по id модели
	 */
	protected static function createKeyById($id)
	{
		$id = str_replace('-', '_', $id);
		return strtolower(self::$className) . '_' . $id;
	}

    public static function toRegistry($elements) {
    	foreach ($elements as $element) {
    		$key = self::createKeyById($element['id']);
    		$obj = $obj = new self::$className($element);
    		Zend_Registry::set($key, $obj);
    	}
    }
	
}