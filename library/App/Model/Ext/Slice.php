<?php
class App_Model_Ext_Slice
{
	// название таблицы базы данных 
	protected $_db_table_name = null;
	
	// экземпляр Zend_Db_Table
	protected $_db_table = null;
	
	// объект Zend_Controller_Request_Http
	protected $_request; 
	
	// обработаный массив параметров среза (символьные коды)
	protected $_options = null;
	
	// массив параметров среза соответствующий базе данных
	protected $_db_options = null;
	
	// массив фильтров определяющих срез
	protected $_filter = array();
	
	// элементы среза
	protected $_elements = null;
	
	// доступные значения фильтров среди найденных элементов
	protected $_avalable_options = null;
	
	public function __construct(Zend_Controller_Request_Http $request)
	{
		$this->_request = $request;	
		//$this->getOptions();	    
	}

	
    public function getDbTable()
	{		
		if (null === $this->_db_table) {
			$this->_db_table = new $this->_db_table_name();
		}
		return $this->_db_table;
	}
	
	/**
	 * $name - имя get переменной на которую будет откликатся фильтр
	 */
	public function addFilter($name, App_Model_Ext_Filter $filter)
	{
		$filter->setSlice($this);
		$this->_filter[$name] = $filter;
	}
	
	
	/**
	 * возвращаем сконфигурированный фильтр по его имени
	 */
	public function getFilter($name)
	{
		if (isset($this->_filter[$name])) {
			$filter = $this->_filter[$name];
			//$filter->setSliceOptions($this->getOptions());
			return $filter;
		}		    
	}
	
	// текущие параметры среза
    public function getOptions()
    {    
    	if ($this->_options === null)
    	{
	    	$options = $this->getDefaultOptions();	    		    	
	    	foreach ($options as $key => $val) 	    	{
	    		$param = $this->_request->getParam($key);
	    		if (!empty($param))
	    		{
	    			$options[$key] = (string)$param;
	    		}
	    	}
    		$this->_options = $options;
    	}    	    	
    	return $this->_options;	
    }   
      
    public function getDbOptions()
    {    	
    	if (null === $this->_db_options)
    	{
    		$options = $this->getOptions();    	
	    	$result = array();  
	        foreach ($this->_filter as $key => $filter) {
	        	$param    = $filter->getParam();
	        	$db_param = $filter->getDbParam();
	        	$id = $filter->getIdByCode($options[$param]);
	        	if (!empty($id)) {
	        	    $result[$db_param] = $id;
	        	}    		
	    	}  
	    	$this->_db_options = $result;
    	}    	  	
        return $this->_db_options;
    }
    
    // параметры среза по умолчанию
    protected function getDefaultOptions() {
    	$result = array();
    	foreach ($this->_filter as $key => $val) {
    		$result[$key] = 'all';
    	}
    	//echo '<pre>$result'; print_r($result); echo '</pre>';    	
    	return $result;	     	
    }
   
    /**
     * Возвращает элементы принадлежащие текущему срезу
     */
    public function getElements()
    {
    	$db = $this->getDbTable();
		$select  = $db->select();
		$select->order('sort asc');	
        // динамически формируем where 
        // $select->where('brand_id = ?', $brand_id);
		foreach ($this->getDbOptions() as $key => $val) {			
			if (!empty($key) && !empty($val)) {
				$key = $key . ' = ?';
			    $select->where($key, $val);
			}				
		}											 		
		$results = $db->fetchAll($select)->toArray(); 
		//echo '<pre>'; print_r($results); echo '</pre>'; 			    	   	
		return $results;    	
    }
    
   /**
     * Возвращает информацию по текущему значению фильтра
     */
    public function getFilterInfo($filter_name)
    {
    	$result = null;
        if (isset($this->_filter[$filter_name])) {
			$result = $this->getFilter($filter_name)->getInfo();						
		}
    	return $result;
    }
    
}