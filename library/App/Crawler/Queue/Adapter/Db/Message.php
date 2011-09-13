<?php
class App_Crawler_Queue_Adapter_Db_Message extends Zend_Db_Table_Abstract
{
    protected $_name     = 'crawler_queue_message';
    protected $_primary  = 'message_id';
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
					  `message_id` bigint(20) unsigned NOT NULL auto_increment,
					  `queue_id` int(10) unsigned NOT NULL,
					  `handle` char(32) default NULL,
					  `body` varchar(8192) NOT NULL,
					  `md5` char(32) NOT NULL,
					  `timeout` decimal(14,4) unsigned default NULL,
					  `created` int(10) unsigned NOT NULL,
					  PRIMARY KEY  (`message_id`),
					  UNIQUE KEY `message_handle` (`handle`),
					  KEY `message_queueid` (`queue_id`)
    	             )ENGINE=InnoDB DEFAULT CHARSET=utf8';
    	$db = App_Resource::getResource('db');
    	//$db   = Zend_Registry::get('db');
    	$stmt = $db->query($sql);
    	$stmt->execute();    	
    }
}
