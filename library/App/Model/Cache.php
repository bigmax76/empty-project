<?php
class App_Model_Cache
{
	protected static $_cache_manager;
	
    public static function __callStatic($method, $params)
    {
    	if (stripos($method, 'Cache') !== false)
    		return self::_callCache($method, $params);
        
    	throw new Exception('Call to undefined method ' . $method);
    }  
    
    public static function _callCache($method, $params)
    {
    	$tmp        = explode('Cache', $method);
    	$method     = $tmp[0];
    	$cache_name = strtolower($tmp[1]);    	
    	if(!method_exists(get_called_class(), $method))
            throw new Exception('Call to undefined method ' . $method);
       
        $cache = self::getCacheManager()->getCache($cache_name);       
        $class = 'Zend_Cache_Frontend_Function';
        if (!$cache instanceof $class)
        	throw new Exception('Cache ' . $cache_name . ' mast be instance of Zend_Cache_Frontend_Function');
        return $cache->call(array(get_called_class(), $method), $params);
    }
    
    public static function getCacheManager() {
    	if (null === self::$_cache_manager){    		
    		self::$_cache_manager = App_Resource::get('cachemanager');
    	}
    	return self::$_cache_manager;
    }
    
    public static function setCacheManager($cachemanager) {
    	self::$_cache_manager = $cachemanager;    	
    }
}