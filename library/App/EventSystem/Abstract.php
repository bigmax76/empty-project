<?php
class App_EventSystem_Abstract extends Zend_Controller_Action_Helper_Abstract
{
	// массив обработчиков событий 	
	protected $_handler = array();
	
	/**
	 * Создание события $event_type и вызов соответствующих обработчиков
	 * @param unknown_type $event_type
	 * @param unknown_type $context
	 */
	public function send($event_type, $context)
	{
	    if(isset($this->_handler[$event_type]) && is_array($this->_handler[$event_type] ) )
	    {
	        foreach ($this->_handler[$event_type] as $handler )
	        {
	            $handler->direct($event_type, $context, $this);
	        }
	    }
	}
	
    /**
     * Добавляем обработчик для события $event_type
     */
    public function addHandler($event_type, App_EventSystem_Handler_Interface $handler)
    {
    	$this->_handler[$event_type][] = $handler;
    }
}