<?php
class App_Model_Service
{	
	protected static $_dbTableName;
    protected static $_dbTable;      // !!!нужно прописывать в дочерних классах для корректного срабатывания getDbTable()
	
	public static function getDbTable() {
		if (null === static::$_dbTable) {			
			static::$_dbTable = new static::$_dbTableName;
		}
		return static::$_dbTable;
	}
	
    public static function __callStatic($method, $params)
    {
    	if (stripos($method, 'Cache') !== false)
    		return self::_callCache($method, $params);
        
    	throw new Exception('Call to undefined method ' . $method);
    }  
    
    public static function _callCache($method, $params)
    {
    	$tmp = explode('Cache', $method);
    	$method = $tmp[0];
    	if(!method_exists(get_called_class(), $method))
            throw new Exception('Call to undefined method ' . $method);
       
    	$cache_id = strtolower($tmp[1]);
        $cache = App_Resource::get('cachemanager')->getCache($cache_id);       
        $class = 'Zend_Cache_Frontend_Function';
        if (!$cache instanceof $class)
        	throw new Exception('Cache ' . $cache_id . ' mast be instance of Zend_Cache_Frontend_Function');
        
        // учитываем возможные значения по умолчанию и дописываем последним параметром id сайта,
        // чтобы Zend_Cache сформировал id записи с учетом сайта
        $params[] = null;
        $params[] = null;
        $params[] = null;
        $params[] = null;
        $params[] = null;       
        $params[] = Model_Site_Service::getCurrent()->id;
       
        return $cache->call(array(get_called_class(), $method), $params);
    }
}