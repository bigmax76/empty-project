<?php
abstract class App_Model_Zend_DbMapper
{
	protected $_dbTable;
	protected $_dbTableName = Null;
	
	abstract protected function setOptions(App_Model_Abstract $obj, $data);
	
    abstract protected function getOptions(App_Model_Abstract $shop);  
	
    public function getDbTable()
    {
        if (null === $this->_dbTable) {
        	$table = App_Resource_Container::getClass($this->_dbTableName);
        	$this->setDbTable($table);
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
    public function getById($id, App_Model_Abstract $obj)
	{		
		$result = $this->getDbTable()->find($id);
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
     * у текущего маппера работает только для id 
     */
    public function getByRange($range, $field = 'id') {
		return $this->getDbTable()->find($range)->toArray();
	}
	
    /**
     *
     */
    public function save(App_Model_Abstract $obj)
    {
    	$data = $this->getOptions($obj);             
        if (null == ($id = $obj->id)) {
            unset($data['id']);
            $id = $this->getDbTable()->insert($data);
            $obj->id = $id;
            return $id;
        }
        else {
            $this->getDbTable()->update($data, array('id = ?' => $id));
            return $id;                      
        }  
    }
    
    public function delete($id)
	{
		$id = intval($id);
		return $this->getDbTable()->delete('id = ' . $id);
	}
    
    /**
     * преобразуем объект в массив
     */
    public function toArray(App_Model_Abstract $obj)
    {    	
    	return $this->getOptions($obj);      
    }
}