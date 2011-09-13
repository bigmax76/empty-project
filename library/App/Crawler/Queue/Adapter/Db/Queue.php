<?php
class App_Crawler_Queue_Adapter_Db_Queue extends Zend_Db_Table_Abstract
{
    protected $_name     = 'crawler_queue';
    protected $_primary  = 'queue_id';
    protected $_sequence = true;
    
    /**
     * Автоматически создаем таблицу если ее нет
     */
    public function __construct($options = array())
    {
    	parent::__construct($options);
    	$this->createTableIfNotExists();     	
    }
    
    protected function createTableIfNotExists()
    {
    	$sql = 'CREATE TABLE IF NOT EXISTS `' . $this->_name . '` (                             
					   `queue_id` int(10) unsigned NOT NULL auto_increment,
					   `queue_name` varchar(100) NOT NULL,
					   `timeout` smallint(5) unsigned NOT NULL default 30,
					   PRIMARY KEY  (`queue_id`)
    	             ) ENGINE=InnoDB DEFAULT CHARSET=utf8';
    	$db = App_Resource::getResource('db');
    	//$db   = Zend_Registry::get('db');
    	$stmt = $db->query($sql);
    	$stmt->execute();    	
    }
}
