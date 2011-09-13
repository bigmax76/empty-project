<?php
/**
 * Наблюдатель за процесом парсинга.
 * Выводит на экран отладочную информацию
 */
class App_Crawler_Observer_ConsoleLog implements App_Crawler_Observer_Interface
{	
	public function notify(App_Crawler_Abstract $crawler, $event_type)
	{
		if ($event_type == App_Crawler_Events::START_CYCLE) {
			//echo $crawler->getUrl() . '<br />';		
			App_Output::send('Парсится: ' . $crawler->getUrl());		
            @ob_flush();flush();	 			
		}				
	}	
}