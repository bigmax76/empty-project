<?php
class App_Model_Service
{
	/**
	 * @var Zend_Db_Table
	 */
    protected static $_dbTable;
    protected static $_multiOptions;// = array();

	/**
	 * @static
	 * @return Zend_Db_Table
	 */
    public static function getDbTable() {
    	if (is_string(static::$_dbTable)) { 
	        static::$_dbTable = new static::$_dbTable();           
        }
        return static::$_dbTable;	
	}
	
    public static function getList(App_Model_Filter $filter = null, $page = 1, $perPage = 1000000)
	{
		$select  = self::getSelect($filter);
		$adapter = new Zend_Paginator_Adapter_DbTableSelect($select);
		$paginator = new Zend_Paginator($adapter);
		$paginator->setCurrentPageNumber($page)
		          ->setItemCountPerPage($perPage);
		return $paginator;
//		return self::getDbTable()->fetchAll($select)->toArray();
	}
	
    public static function fetchAll(App_Model_Filter $filter = null)
	{
		$select  = self::getSelect($filter);		
		return self::getDbTable()->fetchAll($select)->toArray();
	}
	
    public static function fetchRow(App_Model_Filter $filter = null)
	{
		$select  = self::getSelect($filter);
		$result = self::getDbTable()->fetchRow($select);
		if (!empty($result))
		    return $result->toArray();
		return array();		
	}
	
	public static function delete(App_Model_Filter $filter = null)
	{
		$select  = self::getSelect($filter);
		self::getDbTable()->delete($select->getPart('where'));
	}
	
	/**
	 * Возвращает объект Zend_Db_Table_Select соответствующий переданному фильтру
	 */
	public static function getSelect(App_Model_Filter $filter = null)
	{
		$db = self::getDbTable();
		$select  = $db->select();
		
	    if (!empty($filter)) {
			$params = $filter->getParams();			
			foreach ($params as $param) {				
				$select->where($db->getAdapter()->quoteIdentifier($param['name']) . ' ' . $param['operator'], $param['value']);
			}
			$sorts = $filter->getSorts();
			if (!empty($sorts))
				$select->order($sorts);			
		}
		return $select;
	}
	
	public static function getByRange($range, $field = null)
    {   
    	// TODO не факт что корректно работает если $rаnge instanceof Zend_Paginator
    	if (!empty($field)) {
    		$result = array();
    		foreach ($range as $row){
    			$result[] = $row[$field];
    		}
    		$range = $result;
    	}    	
    	if (empty($range))
    		return array();
    	//ksort($ids);
    	$db = self::getDbTable();
    	$select = $db->select();
    	$select->where('id in (?)', $range);
    	$result = $db->fetchAll($select)->toArray();    	
    	return $result;
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
    
   	/**
     * Возвращает подготовленный массив для исользования
     * совместно с Zend_Form_Element_Select
     */
	public static function getMultiOptions($first_row = null)
	{
    	if (!isset(self::$_multiOptions[get_called_class()]))
    	{   		
    		$result = array();
	    	$rows = self::getList();
	    	foreach ($rows as $row) {
	    		$result[$row['id']] = $row['name'];
	    	}
	    	if (!empty($first_row))
	    	    $result['0'] = '------';
			ksort($result);
			self::$_multiOptions[get_called_class()] = $result;
    	}		
    	return self::$_multiOptions[get_called_class()];
	}    
}