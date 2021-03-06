<?php
abstract class App_Model_Abstract_Mapper
{
	protected $_dbTable;
	protected $_dbTableName = Null;	
	
	abstract protected function setOptions(App_Model_Abstract $obj, $data);
	
    abstract protected function getOptions(App_Model_Abstract $shop);  
	
    /**
     * Чтобы предотвратить возможные утечки памяти 
     */
    public function __destruct(){
        unset($this->_dbTable);
        unset($this->_dbTableName);
    }

	/**
	 * @return Zend_Db_Table
	 */
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
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }
    	
    /**
     * Извлечение объекта по первичному ключю
     */
    public function getById($id, $obj)
	{		
		$result = $this->getDbTable()->find($id);
	    if (0 == count($result)) {
            return Null;
        }
		$data = $result->current();		
		$this->setOptions($obj, $data);		      
	}

	/**
     * Извлечение объекта по первичному ключю
     */
    public function getByPK($key, $obj)
	{
		$result = $this->getDbTable()->find($key);
	    if (0 == count($result)) {
            return Null;
        }
		$data = $result->current();
		$this->setOptions($obj, $data);
	}
	
    /**
     * Извлечение объекта по значению поля "code"
     */
    public function getByCode($code, App_Model_Abstract $obj)
	{		
		// это тоже работает, но я посчитал, что с quoteInto будет безопаснее(возможно ошибаюсь)
		//$select = $this->getDbTable()->select()->where('code = ?', $code);                                              /
		//$result = $this->getDbTable()->fetchRow($select);
		
		$where  = $this->getDbTable()->getAdapter()->quoteInto("code = ?", $code);
		$result = $this->getDbTable()->fetchRow($where);
		
	    if (0 == count($result)) {
            return Null;
        }
		$this->setOptions($obj, $result);		      
	}
	
    /**
     * Извлечение объекта по значению указанного поля $FieldName
     * Если требуется извлечь данные по полям отличным от id и code
     */
    public function getByField($field_name, $value, App_Model_Abstract $obj)
	{		
		// до версии php 5.2.9 может вызывать ошибку когда в запросе присутствует одновременно ' и ?
		$where  = $this->getDbTable()->getAdapter()->quoteInto("$field_name = ?", $value);
		$result = $this->getDbTable()->fetchRow($where);
		
	    if (0 == count($result)) {
            return Null;
        }
		$this->setOptions($obj, $result);		      
	}
		
    /**
     *
     */
    public function save(App_Model_Abstract $obj)
    {
    	$data = $this->getOptions($obj);             
        if (null == ($id = $obj->getId()))
        {
            unset($data['id']);                     
            //echo '<pre>'; print_r($data); echo '</pre>';          
            return $this->getDbTable()->insert($data);                                    
        } 
        else {        	
            $result = $this->getDbTable()->update($data, array('id = ?' => $id));             
            return $id;                      
        }  
    }
    
    public function delete($id)
	{
		$id = intval($id);
		return $this->getDbTable()->delete('id = ' . $id);
	}
    
    /**
     * преобразуем т объект в массив
     */
    public function toArray(App_Model_Abstract $obj)
    {    	
    	return $this->getOptions($obj);      
    }
}