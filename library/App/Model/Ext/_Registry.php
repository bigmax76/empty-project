<?php
/**
 * Шаблон кода реестра объектов определеного типа.
 * Возможность реализовать через наследование
 * доступна начиная с Php 5.3 (через get_called_class() )
 */
class App_Model_Abstract_Registry
{	
	// имя класса объекты которого будут хранится в реестре		
	protected static $_className = 'Class_Model_Name'; 	
	
	/* ничего ниже менять не требуется */
	
	// в этом массиве хранятся объекты реестра
	private static $objects   = array();    
	
    // делаем невозможным создать экземпляр класса  
	private function __construct() { } 
	
	/**
	 * если объект с нужным id хранится в реестре отдается он.
	 * иначе вызов на извлечение делегируется текущему мапперу
	 * и результат сохраняется в реестре
	 */ 	
    public static function getById($id)
    {
    	if (isset(self::$objects[$id]))
    	    return self::$objects[$id];  
    	        	
    	$obj = new self::$_className();
    	$obj->getById($id);
    	self::$objects[$obj->id] = $obj;  
    	  	
    	return $obj;          
    }    
}