<?php
abstract class App_Model_Pdo_DbMapper //extends App_Model_Abstract_Mapper
{	
	protected $_db_table;
	protected $_adapter = 'pdo';
	protected $_primary = 'id';
	
    public function getAdapter() {
        if (is_string($this->_adapter)) {  
        	$this->_adapter = App_Resource_Container::get($this->_adapter);
        }
        return $this->_adapter;
    }
    
    public function setAdapter(App_Model_Pdo_Adapter $adapter)
    {     	
        $this->_adapter = $adapter;
        return $this;
    }    

    /**
     * Извлечение объекта по первичному ключю
     */
    public function getById($id, $obj) {        
		$sql = "SELECT * FROM `"  . $this->_db_table . "`"
    	             . " WHERE `id` = " . addslashes($id)
    	             . " LIMIT 1";
    	$result = $this->getAdapter()->query($sql)->fetch(PDO::FETCH_OBJ); 
	    if (!empty($result))
            $this->setOptions($obj, $result);
        return null;
	}
	
	public function getByRange($range, $field = 'id')
	{
		$in = array();
		foreach ($range as $val) {
			$in[] = "'" . mysql_real_escape_string($val) . "'";
		}
		$sql = "SELECT * FROM `"  . $this->_db_table . "`"
    	             . " WHERE `" . $field . "` IN (" . implode(', ', $in) . ")";    	 
    	$res =  $this->getAdapter()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	    return $res;
	}
	
    /**
     * Извлечение объекта по значению поля `code`
     */
    public function getByCode($code, $obj)
	{		
		$sql = "SELECT * FROM `"  . $this->_db_table . "`"
    	             . " WHERE `code` = '" . mysql_real_escape_string($code) . "'"
    	             . " LIMIT 1";
    	$result = $this->getAdapter()->query($sql)->fetchObject();	   
    	if (!empty($result))
            $this->setOptions($obj, $result);
        return null;     
	}
	
    /**
     * Извлечение объекта по значению поля $name
     */
    public function getByField($name, $value, $obj)
	{	
		$sql = "SELECT * FROM `"  . $this->_db_table . "`"
    	             . " WHERE `" . mysql_real_escape_string($name) . "` = '" . mysql_real_escape_string($value) . "'"
    	             . " LIMIT 1";
    	$result = $this->getAdapter()->query($sql)->fetchObject();	   
    	if (!empty($result))
            $this->setOptions($obj, $result);
        return null; 
	}

	
    
	public function save(App_Model_Abstract $obj)
    {    	
    	$data = $this->getOptions($obj);
        if (null == ($id = $data[$this->_primary])) {
            unset($data[$this->_primary]);
            $id = $this->insert($data);
            $primary = $this->_primary;
            $obj->$primary = $id;
            return $id;
        }
        $this->update($data);
        return $id;
    }
    
	public function insert_ignore(App_Model_Abstract $obj)
	{
		$data = $this->getOptions($obj);
		$id = $this->insert($data, true);
		$primary = $this->_primary;
        $obj->$primary = $id;
        return $id;
	}
    
    public function delete($id)
	{
		$sql = "DELETE FROM `" . $this->_db_table . "`"
             . " WHERE (" . $this->_primary . " = '" . mysql_real_escape_string($id) . "') ";
		$stmt = $this->getAdapter()->prepare($sql);
        $stmt->execute();
	}
	
    /**
     * преобразуем объект в массив
     */
    public function toArray(App_Model_Abstract $obj) {    	
    	return $this->getOptions($obj);      
    }
	
    protected function insert($data, $isIgnore = false)
    {
    	$cols = array();
    	$vals = array();
        foreach ($data as $col => $val) {
           $cols[] = "`" . $col . "`";
           $vals[] = "?";
        }
        $ignore = ($isIgnore) ? 'IGNORE ' : '';
        
        $sql = "INSERT " . $ignore . "INTO `" 
             . $this->_db_table . "`"             
             . ' (' . implode(', ', $cols) . ') '
             . 'VALUES (' . implode(', ', $vals) . ')';
                
        $stmt = $this->getAdapter()->prepare($sql);
        $stmt->execute(array_values($data));
        return $this->getAdapter()->lastInsertId();         
    }
    
    protected function update($data)
    {
    	$set = array();
        foreach ($data as $col => $val) {
           $set[] = "`" . $col . "` = ?";           
        }
        $sql = "UPDATE `" . $this->_db_table . "`"
               . ' SET ' . implode(', ', $set)
               . " WHERE (" . $this->_primary . " = '" . $data[$this->_primary] . "')";
        $stmt = $this->getAdapter()->prepare($sql);
        $stmt->execute(array_values($data));
        return $data[$this->_primary];
    }
  

	



 }