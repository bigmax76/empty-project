<?php 
class App_Resource
{
	/*
	 * Возвращает Bootstrap Resource по его имени
	 */
	public static function getResource($name)
	{		
		return self::get($name);
	}
	
    /*
	 * Возвращает Bootstrap Resource по его имени
	 */
	public static function get($name)
	{		
		return Zend_Controller_Front::getInstance()
			       ->getParam('bootstrap')
			       ->getResource($name);
	}
}
