<?php
abstract class App_Model_Abstract_CakeMapper
{
	protected $_dbTable;
	
	protected $_dbTableName = Null;	

	abstract protected function setOptions(App_Model_CakeAbstract $obj, $data);
	
    abstract protected function getOptions(App_Model_CakeAbstract $obj);  
    
    public function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable($this->_dbTableName);
        }
        return $this->_dbTable;
    }
    
    public function setDbTable($dbTable)
    {   
    	
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof AppModel) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }   
    	
    /**
     * Извлечение объекта по первичному ключю
     */
    public function getById($id, App_Model_CakeAbstract $obj)
	{		
		$result = $this->getDbTable()->findById($id);		
		
	    if (empty($result))
            return Null;
              
		$data = (object) $result[$this->getDbTable()->name];			
		$this->setOptions($obj, $data);		      
	}
	
    /**
     * Извлечение объекта по значению поля "code"
     */	
    public function getByCode($code, App_Model_CakeAbstract $obj)
	{		
		$data = $this->getDbTable()->findByCode($id);		
	    if (empty($data))
            return Null;            
        $result = (object) $data[$this->getDbTable()->name];    
		$this->setOptions($obj, $result);		      
	}
	
    /**
     * Извлечение объекта по значению указанного поля $FieldName
     * Если требуется извлечь данные по полям отличным от id и code
     */	
    public function getByField($field_name, $value, App_Model_CakeAbstract $obj)
	{		
		$data = $this->getDbTable()->find(
			'first', array('conditions' => array($this->getDbTable()->name . '.' . strtolower($field_name) => $value))
		);		
		if (empty($data))
            return Null;  
        $result = (object) $data[$this->getDbTable()->name]; 		
		$this->setOptions($obj, $result);		      
	}
		
    public function save(App_Model_CakeAbstract $obj)
    {
    	$data = $this->getOptions($obj);             
        if (null == ($id = $obj->getId()))
        {
            unset($data['id']);                     
            $this->getDbTable()->save($data);   
            return $this->getDbTable()->getLastInsertId();                                 
        } 
        else {
            $result = $this->getDbTable()->save($data); 
            return $id;
        }  
    }
    
    public function delete($id)
	{
		$id = intval($id);
		return $this->getDbTable()->delete($id);
	}
    
    /**
     * преобразуем объект в массив
     */
    public function toArray(App_Model_CakeAbstract $obj)
    {    	
    	return $this->getOptions($obj);      
    }
}