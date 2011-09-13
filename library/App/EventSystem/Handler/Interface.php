<?php
/**
 * Интерфейс которому должны следовать все обработчики событий
 */
interface App_EventSystem_Handler_Interface
{
	/**
	 * Действие которое будет вызыват Event System при наступлении события.
	 * Объект $App_EventSystem передается чтобы иметь возможность доступа 
	 * к методам Zend_Controller_Action_Helper_Abstract (например объекту запроса)
	 */
	public function direct($event_type, $context, Zend_Controller_Action_Helper_Abstract $helper);
}