<?php
class App_Crawler_DbTable extends Zend_Db_Table_Abstract {
    protected $_name = 'crawler_service';
    protected $_primary = 'id';     

    /**
     * Автоматически создаем таблицу если ее нет
     */
    public function __construct($options)
    {
    	parent::__construct($options);
    	$this->createTableIfNotExists();     	
    }
    
    protected function createTableIfNotExists()
    {
    	$sql = 'CREATE TABLE IF NOT EXISTS `' . $this->_name . '` (                             
               `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,      
               `queue` varchar(255) DEFAULT NULL,                      
               `url`   varchar(255) DEFAULT NULL,                          
               PRIMARY KEY (`id`)                                     
             ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8';
    	$db = App_Resource::getResource('db');
    	//$db   = Zend_Registry::get('db');
    	$stmt = $db->query($sql);
    	$stmt->execute();    	
    }
}