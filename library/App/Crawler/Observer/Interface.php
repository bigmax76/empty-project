<?php
interface App_Crawler_Observer_Interface
{
	/**
	 * Обработчик события $event_type
	 */
	public function notify(App_Crawler_Abstract $obj, $event_type);
}