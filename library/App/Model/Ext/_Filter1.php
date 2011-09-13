<?php
// нужно позже сделать итераторомчтобы разгрузить view

class App_Model_Abstract_Filter
{
	// имя GET параметра отвечающее за фильтр
	protected $_param;
			
	// параметры среза
	protected $_slice;
	
	// элементы фильтра
	protected $_elements;
	
	// заголовок фильтра
	protected $_title;

    public function getParam()
	{		
		return $this->_param;
	}
	
    public function getTitle()
	{		
		return $this->_title;
	}
	
	public function getElements()
	{		
		return $this->_elements;
	}
	
    public function getSlice()
	{		
		return $this->_slice;
	}
	
	
	public function setParam($name)
	{
		$this->_param = $name;
		return $this;
	}
	
    public function setTitle($value)
	{
		$this->_title = $value;
		return $this;
	}
	
    public function setElements($elements)
	{
		$this->_elements = $elements;
		return $this;
	}
	
    public function setSlice(array $options)
	{
		$this->_slice = $options;
		return $this;
	}	
	
    /**
     * Возвращает id элемента модели соответствующий переданному коду
     */
    static public function getIdByCode($table_name, $code)
    {
    	if ($code == 'all')
    	    return;
        $db = new $table_name();
		$select  = $db->select();
		$select->where('code = ?', $code); 		       						 		
		$results = $db->fetchAll($select)->toArray(); 
		if (isset($results['0']))
			return $results['0']['id'];
		echo '<pre>getIdByCode($code)'; print_r($results); echo '</pre>'; 
		die;			    	   	
		return $results;
    }
    
    
}