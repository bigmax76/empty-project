<?php
abstract class App_Model_Image
{
	protected static $_dbTable;
	protected static $_data = array();
	
	public static function getDbTable() {
        if (is_string(static::$_dbTable)) {
        	$table = new App_Model_Image_DbTable(array('name' => static::$_dbTable));       
	        static::$_dbTable = $table;
        }
        return static::$_dbTable;
    }
    
    public static function getByRange($range, $field = 'id')
    {
    	if (!empty($range)) {
	        $db = self::getDbTable();
	    	$select = $db->select();
	    	$select->where("{$field} in (?)", $range);
	    	return $db->fetchAll($select)->toArray();	    	
    	}
    	return array();
    }
    
    public static function getList($parent_id)
    {
    	if (!empty(self::$_data) && isset(self::$_data[get_called_class()][$parent_id])) {
    		$config['data'] = self::$_data[get_called_class()][$parent_id];
    		$rowset = new App_Model_Image_Rowset($config);
    		return $rowset;
    	}    	
    	$db = self::getDbTable();
    	$select = $db->select();
    	$select->where("parent_id in (?)", $parent_id);
    	$rows = $db->fetchAll($select);
    	self::$_data[get_called_class()][$parent_id] = $rows->toArray();
		return $rows;
    }

    public static function prepareList($element, $key) {    	   	
    	$range = App_Common_Array::getCol($element, $key);
    	$rows  = static::getByRange($range, 'obj_id');
    	static::$_data[get_called_class()] = App_Common_Array::toAssocMulti($rows, 'obj_id');
    	return $rows;
    }
    
    /**
     * При добавлении и удалении фото возникает необходимость сбросить ранее подготовленные данные
     */
    public static function clearPrepear($parent_id) {
    	if (!empty(self::$_data) && isset(self::$_data[get_called_class()][$parent_id])) {
    		unset(self::$_data[get_called_class()][$parent_id]);
    	}  
    }
    
}