<?php
class App_Crawler_Service
{
	// имя служебной тоблицы по умолчанию
	static protected $tableName = 'crawler_service';
	
	static protected $dbTable   = null;
	
	/**
	 * Возвращает и при необходимости создает служебную таблицу
	 */
    public static function getDbTable($name = null, $prefix = null, $postfix = null ) {
		if (null === self::$dbTable) {
			if (defined('PREFIX_DB'))  // это костыль
			    $prefix = PREFIX_DB;
			if (null == $name)
				$name = self::$tableName;
			if (!empty($prefix))
				$name = $prefix . '_' . $name; 	
			if (!empty($postfix))
				$name = $name . '_' . $postfix; 
				
			//$name = (!defined('PREFIX_DB')) ? 'crawler_service' :  PREFIX_DB . '_crawler_service';
			$options = array(
				'name' => $name,
			);
			self::$dbTable = new App_Crawler_DbTable($options);			
		}
		return self::$dbTable;
	}
	
	/**
	 * Возвращает и при необходимости создает очередь страниц для парсинга
	 */
    public static function getQueue($name) 
    {
    	$db = App_Resource::getResource('db');
    	$config = $db->getConfig();
    	$options = array(
		    'name'          => $name,
		    'driverOptions' => array(
		        'host'      => $config['host'],
		        'port'      => '3306',
		        'username'  => $config['username'],
		        'password'  => $config['password'],
		        'dbname'    => $config['dbname'],
		        'type'      => 'pdo_mysql'
		    )
		);
		$adapter = new App_Crawler_Queue_Adapter_Db($options);
		$queue =  new Zend_Queue($adapter, $options);
		//$queue =  new Zend_Queue('Db', $options);
		return $queue;
	}
}