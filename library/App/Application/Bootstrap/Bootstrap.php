<?php
/**
 * Класс позволяет "магически" кешировать определенные в bootstrap ресурсы 
 * Например _initConfigCacheD1()
 */
class App_Application_Bootstrap_Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    public function __call($method_name, $params)
    {
    	$methods = get_class_methods($this);
    	foreach ($methods as $method) {
    		if (stripos($method, $method_name . 'Cache') !== false)
    		    return $this->_callCache($method, $params);
    	}
    	throw new Exception('Call to undefined method ' . $method);
    }  
    
    public function _callCache($method, $params)
    {
    	$tmp        = explode('Cache', $method);
    	$cache_name = strtolower($tmp[1]);
    	if(!method_exists(get_called_class(), $method))
            throw new Exception('Call to undefined method ' . $method);
        
        $this->bootstrap('cachemanager');
        $cache = $this->getResource('cachemanager')->getCache($cache_name);   
                
        if (empty($cache))
        	return $this->$method();
       
        $class = 'Zend_Cache_Core';
        if (!$cache instanceof $class)
        	throw new Exception('Cache ' . $cache_name . ' mast be instance of Zend_Cache_Frontend_Core');
        
        $cache_id = md5($method);              
        if (!$resource = $cache->load($cache_id)){        	
        	$resource  = $this->$method();
        	$cache->save($resource, $cache_id);
        }
        return $resource;
    }

    /**
     * Менее ресурсоемкое решение для автозагрузки.
     * Чтобы подменить им стандартный ZF автолоадер 
     * необходимо вызвать следующий код до инициализации Application
     * $autoloader = Zend_Loader_Autoloader::getInstance();
     * $autoloader->setDefaultAutoloader(array('App_Application_Bootstrap_Bootstrap', 'autoload'));
     */
    public static function autoload($path) {   
        include str_replace('_','/',$path) . '.php';
        return $path;   
    }
    
    public function getClassResources() {
        if (null === $this->_classResources) {
            $methodNames = get_class_methods($this);
            $this->_classResources = array();
            foreach ($methodNames as $method) {
                if (5 < strlen($method) && '_init' === substr($method, 0, 5)) {
                	$tmp    = explode('Cache', $method);
    	            $method = $tmp[0];
                    $this->_classResources[strtolower(substr($method, 5))] = $method;
                }
            }
        }        
        return $this->_classResources;
    }    
}