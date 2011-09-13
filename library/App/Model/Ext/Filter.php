<?php
abstract class App_Model_Ext_Filter
{	
	// название таблицы базы данных
	protected $db_table_name = null;
	
	// экземпляр Zend_Db_Table
	protected $db_table = null;
	
	// имя модели отвечающей за фильтр
	protected $model_name = null;
	
	// get параметр определяющий фильтр
	protected $get_param = null;		
	
	// db параметр определяющий фильтр
	protected $db_param  = null;
	
	// массив элементов полученых после применения фильтра
	protected $filter_elements = null;
	
	// массив элементов полученых после применения фильтра
	protected $slice_elements = null;
	
	// объект среза содержащий фильтр
	protected $slice = null; 
	
	// id текущего значения фильтра
    protected $current_id = null;
	
	// информация по текущему значению фильтра
	protected $current = null;
	
	// объект кеша используемый с фильтрами
	protected static $cache = null;
		
	public function __construct($get_param, $db_param, $slice_elements = null)
	{
		$this->get_param = $get_param;
		$this->db_param  = $db_param;
		$this->elements  = $slice_elements;		
	}
	
    public function getElements()
	{		
		if (null === $this->filter_elements)
		{
			$cache = App_Model_Ext_Filter::getCache();
			if ($cache === null) {
				$this->filter_elements = $this->_getElements(); 
				return $this->filter_elements;
			}				
			
			if(!$elements = $cache->load($this->db_table_name)) 
			{   
			    $elements = $this->_getElements(); 	
			    $cache->save($elements, $this->db_table_name);
			}
			$this->filter_elements = $elements;			
			
		}
		return $this->filter_elements;
	}
	
	protected function _getElements()
	{
		$db = $this->getDbTable();
		$select   = $db->select()->order('sort asc');								 		
		$elements = $db->fetchAll($select)->toArray(); 
		return $elements;
	}
	
    public function getParam()
	{		
		return $this->get_param;
	}
	
    public function setParam($name)
	{
		$this->get_param = $name;
		return $this;
	}
	
    public function getDbParam()
	{		
		return $this->db_param;
	}
	
    public function getDbTable()
	{		
		if (null === $this->db_table) {
			$this->db_table = new $this->db_table_name();
		}
		return $this->db_table;
	}
	
	public function getSlice()
	{
		return $this->slice;		 
	}
	
    public function setSlice(App_Model_Ext_Slice $options)
	{
		$this->slice = $options;
		return $this;
	}
	
    public function getSliceOptions()
	{
		return $this->getSlice()->getOptions();		 
	}
	
   /**
     * Возвращает id элемента модели соответствующий переданному коду
     */
    public function getIdByCode($code)
    {       	
    	foreach ($this->getElements() as $element) {    		
    		if ($element['code'] == $code)
    		    return $element['id'];
    	}
    	return ;         
    }
    
    /**
     * Возвращает id текущего значения фильтра
     */
    public function getCurrentId($code = null)
    {
    	$options = $this->getSlice()->getDbOptions();
    	if (isset($options[$this->db_param]))
    	    return $options[$this->db_param];
    }
            
    /**
     * Возвращает информацию по текущему значению фильтра
     */
    public function getInfo()
    {
    	if (null === $this->current) {       		
    		$id = $this->getCurrentId();   
    		$elements =  $this->getElements();	
    		foreach ($elements as $element) {
    			if ($element['id'] == $id) {
    				$model = new $this->model_name($element);    		
    		        $this->current = $model;
    		        return $this->current;
    			}    			    
    		} 
    		$this->current = '';
   	    }    	
    	return $this->current;
    }
    
    /**
     * установка объекта кеша для использования совместно с App_Filter
     */
    static public function setCache($cache)
    {    	
    	self::$cache = self::_setCache($cache);
    }
    
    static public function getCache()
    {
    	return self::$cache;
    }
    
    protected static function _setCache($cache)
    {
        if ($cache === null) {
            return null;
        }
        if (is_string($cache)) {
            // //require_once 'Zend/Registry.php';
            $cache = Zend_Registry::get($cache);
        }
        if (!$cache instanceof Zend_Cache_Core) {
            // //require_once 'Zend/Db/Table/Exception.php';
            throw new Zend_Db_Table_Exception('Argument must be of type Zend_Cache_Core, or a Registry key where a Zend_Cache_Core object is stored');
        }
        return $cache;
    }
    
}