<?php
/**
 * Наблюдатель за процесом парсинга, 
 * Повторно включает заданные страницы в очередь парсинга,
 * через указанный промежуток времени 
 */
class App_Crawler_Observer_CycleParsing implements App_Crawler_Observer_Interface
{
	// url который нужно регулярно парсить
	protected $_url;
	
	// интервал для повторного парсинга страницы
	protected $_interval;
	
	// время последнегодобавления в очередь на парсинг
	protected $_last_parsing_ts; 
	
	// интервал через который необходимо это делать
	public function __construct($url, $interval)
	{
		// TODO Добавить проверку на корректность url 
		$this->_url             = $url;
		$this->_interval        = $interval;
		$this->_last_parsing_ts = microtime(true);
	}
	
	public function notify(App_Crawler_Abstract $crawler, $event_type)
	{
		$time = microtime(true) - $this->_last_parsing_ts;		
		if ( $time > $this->_interval )
		{
			$crawler->addExtraUrl($this->_url);
			//echo '<pre>$crawler->extraordinary_url'; print_r($crawler->extraordinary_url); echo '</pre>';
			$this->_last_parsing_ts       = microtime(true);			
			// сообщение для вывода на консоль
			//App_Output::send('Повторно добавленно в очередь: ' . $this->_url);			
		}			
	}
}