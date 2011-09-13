<?php
/**
 * Наблюдатель за процесом парсинга.
 * Через заданное колличество загруженных страниц прекращает выполнение скрипта
 */
class App_Crawler_Observer_StopByPageCnt implements App_Crawler_Observer_Interface
{	
	// количество загрузок страниц, после которого работа скрипта будет прекращена
	protected $_max_cnt;
	
	// текущий счетчик загруженых страниц
	protected $_cnt = null;
			
	// интервал через который необходимо это делать
	public function __construct($cnt = 0)
	{		
		$this->_max_cnt  = (int) $cnt;
		$this->_cnt = 0; 
	}
	
	public function notify(App_Crawler_Abstract $crawler, $event_type)
	{
		if ($this->_max_cnt > 0) {
			$this->_cnt ++;	// увеличиваем счетчик загруженных страниц
			if ($this->_max_cnt <= $this->_cnt) {								
				//echo 'Скрипт прекращен по превышении заданного количества загрузок страниц: ' . $this->_max_cnt . '<br /> ';	
				$crawler->_exit();				
			}		
				
		}
	}
}