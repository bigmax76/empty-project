<?php
class App_Crawler_Proxy_Service
{
	// задержка между запросами на сервер
	public static $delay = 30; 
	
	public static $proxy = array(		   
           //'http://87.106.128.99/crawler/hotfrog.php?link=',
             'http://w006228a.dd10904.kasserver.com/hf.php?link=',
	       //'http://87.106.128.99/crawler/hotfrog.php?charset=utf-8&proxy=1&link=',
	       //'http://74.208.238.224/hf.php?link=',
	);
		
		
	public static function getAvalableProxy()
	{
		// имено здесь регулируем частоту обращениия к сайту
		sleep(rand(2,4));
		$index = rand(0,count(self::$proxy) - 1);		
		return self::$proxy[$index];
		
		// подумаем потом как сделать лучше
		while (true) {		
			foreach (self::$proxy as $key => $val) {
				if (!self::isBuzy($key)) {
					self::createBuzyTrigger($key);
					return $val;
				}
			}
			sleep(15); // ждем чтобы повторить попытку позже	
		}
	}
	
	protected static function isBuzy($index)
	{
		$file = self::getTriggerName($index);
		if (!file_exists($file))
			return false;
		
		clearstatcache();
		echo '<pre>'; print_r(stat($file)); echo '</pre>';
		// время создания файла
		$time = filectime($file);
		echo $time . '<br />';
		$current = time();
		echo $current . '<br />';
		$old = time() - $time;
		echo '$old=' .$old;
		if ((time() - $time) > self::$delay) {
			echo '111111111111';
			unlink($file);
			clearstatcache();
			return false;
		}
		return true;		
	}
	
	protected static function createBuzyTrigger($index)
	{
		$file = self::getTriggerName($index);
		$handle = fopen($file,"w");  	
	   	if ($handle === false) 
	   		throw new Exception('Не удалось создать файл ' . $file);
	   	fclose($handle);
	}
	
	protected static function getTriggerName($index)
	{
		return APPLICATION_PATH . '/log/trigger/' . $index . '_is_busy';
	}
}