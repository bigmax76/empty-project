<?php
/**
 * Наблюдатель за процесом парсинга.
 * через заданное колличество секунд прекращает выполнение скрипта
 */
class App_Crawler_Observer_StopByTime implements App_Crawler_Observer_Interface
{	
	// время в секундах после которого работа скрипта будет прекращена
	protected $_time;
	
	//точка отсчета
	protected $_start = null;
			
	// интервал через который необходимо это делать
	public function __construct($time = 0)
	{		
		$this->_time  = (int) $time;
		$this->_start = microtime(true); 
	}
	
	public function notify(App_Crawler_Abstract $crawler, $event_type)
	{
		if ($this->_time > 0) {
			$time = microtime(true) - $this->_start;		
			if ( $time > $this->_time ) {	
				//App_Output::send('Скрипт прекращен по истечении заданного времени: ' . $this->_time . ' сек.');							
				$crawler->_exit();		 
			}	
		}				
	}
}