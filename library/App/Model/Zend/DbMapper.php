<?php
abstract class App_Model_Zend_DbMapper
{
	protected static $_dbTable;
	protected $_stmts = array();
	
	abstract protected function setOptions(App_Model_Abstract $obj, $data);
	
    abstract protected function getOptions(App_Model_Abstract $shop);  
	
    public function getDbTable() {
    	if (is_string(static::$_dbTable)) { 
	        static::$_dbTable = new static::$_dbTable();           
        }
        return static::$_dbTable;
    }
    
    public function setDbTable($dbTable) {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        static::$_dbTable = $dbTable;
        return $this;
    }
    	
    /**
     * Извлечение объекта по первичному ключю
     */
    public function getById($id, App_Model_Abstract $obj) {		
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

    public function save(App_Model_Abstract $obj)
    {    	
        if (null == ($id = $obj->getId())) {        	
        	$data = $this->getFilteredOptions($obj);
            //unset($data['id']);
            $id = $this->getDbTable()->insert($data);
            $obj->id = $id;
            return $id;
        } 
        else {
        	$data = $this->getOptions($obj);
            $this->getDbTable()->update($data, array('id = ?' => $id));
            return $id;                      
        }  
    }
    
    public function delete($id)
	{
		if (is_array($id))
		{
			$where = array();
			foreach($id as $k => $v)
				$where[] = $this->getDbTable()->getAdapter()->quoteInto($k.' = ?', $v);
		}
		else
			$where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $id); // Baaaad
		return $this->getDbTable()->delete($where);
	}
    
	public function insertIgnore(App_Model_Abstract $obj)
	{
        $table_name = $this->getDbTable()->info('name');
        $adapter = $this->getDbTable()->getAdapter();
        
        $cols = array();
    	$vals = array();
    	
    	// ? TODO на вставку нужны фильтрованные 
    	$data = $this->getFilteredOptions($obj);
    	// на update не фильтрованные
    	// $data = $this->getOptions($obj);    	
    	
        foreach ($data as $col => $val) {
           $cols[] = "`" . $col . "`";
           $vals[] = "?";
        }

        $sql = "INSERT IGNORE INTO `" . $table_name . "`"
             . ' (' . implode(', ', $cols) . ') '
             . 'VALUES (' . implode(', ', $vals) . ') '
             . 'ON DUPLICATE KEY UPDATE ' . $this->getUpdateFields($data);

		$adapter->query($sql, array_values($data));
        return $adapter->lastInsertId();
	}
	
	protected function getUpdateFields($data) {
		$adapter = $this->getDbTable()->getAdapter();

		$fields = array();
		$fields[] = 'id=LAST_INSERT_ID(id)';
		unset($data['id']);		
		foreach ($data as $key => $val) {
			$fields[] = $adapter->quoteIdentifier($key) . '=VALUES(' . $adapter->quoteIdentifier($key) . ')';
		}
		return implode(', ', $fields);
	}
	
	protected function prepareStatement($stmt) {
		if (!isset($this->_stmts[$stmt])) {
			$this->_stmts[$stmt] = $this->getDbTable()->getAdapter()->prepare($stmt);
		}
		return $this->_stmts[$stmt];
	}
	
    /**
     * преобразуем объект в массив
     */
    public function toArray(App_Model_Abstract $obj)
    {    	
    	return $this->getOptions($obj);      
    }
    
    
    /**
     * фильтруем "null" значения
     * TODO сравнить по скорости с другими вариантами
     * например $data = array_filter($data, function($a){return !is_null($a);});
     */
    public function getFilteredOptions($obj) 
    {
    	$data = $this->getOptions($obj);
        return array_diff_key($data, array_filter($data, 'is_null'));
    }
}