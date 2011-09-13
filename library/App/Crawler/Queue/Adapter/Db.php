<?php
/**
 * Этот класс необходим для подмены названий служебных таблиц очереди
 * вместо стандартных названий и для их автоматического создания
 */
class App_Crawler_Queue_Adapter_Db extends Zend_Queue_Adapter_Db
{
	public function __construct($options, Zend_Queue $queue = null)
    {    	
    	   	    	
        parent::__construct($options, $queue);
       
        // почему то переключаются режимы выдачи из таблиц 
        $adapter = App_Resource::getResource('db');
        $db = clone($adapter);
        $db->setFetchMode(Zend_Db::FETCH_ASSOC);
        
        $this->_queueTable   = new App_Crawler_Queue_Adapter_Db_Queue(array(
            'db' => $db,
        ));
        $this->_messageTable = new App_Crawler_Queue_Adapter_Db_Message(array(
            'db' => $db,
        ));
    }
}
