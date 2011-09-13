<?php
class App_Crawler_Abstract
{	 
	// Имя таблицы бд для хранения адресов известных страниц  
	protected $_dbTableName = null;
	
	// Таблица бд для хранения адресов известных страниц (объект)
	protected $_dbTable = null;
	
	// Паттерн (подстрока) при котором url будет добавлен в очередь на парсинг. Оставить пустым если нужно парсить все.
	protected $_index_url_pattern_str = array();
	
	// Паттерн (регулярка) при котором url будет добавлен в очередь на парсинг. Оставить пустым если нужно парсить все.
	protected $_index_url_pattern_regexp = array();	
	
	// Паттерн (подстрока) при котором url не будет добавлен в очередь на парсинг. 
	protected $_no_index_url_pattern_str = array();
	
	// Паттерн (регулярка) при котором url не будет добавлен в очередь на парсинг.
	protected $_no_index_url_pattern_regexp = array();	
	
	// Массив наблюдателей за событиями системы. 	
	protected $_observers = array();
	
	// Очередь страниц для парсинга
	protected $_queue = null;
	
	// Текущий элемент очереди 
	protected $_message = null;
	
	// Текущий url 
	protected $_current_url;
	
	// Массив url для внеочередного парсинга
	protected $_extra_urls = array();
	
	// Домен в пределах которого производится парсинг, он же название очереди	
	protected $_domain;
	
	// Массив моделей отвечающих за парсинг проекта
	protected $_models = array();
	
	// Глобальные параметры парсинга (передаются в каждую модель)
	protected $_project_params = array();
	
	// обработчики событий могут вызвать прекращение процесса парсинга используя метод _exit()
	protected $_is_exit = false;
	
	// постфих служебной таблицы
	protected $_serviceTablePostfix = null;	
	
	protected $_hasCicleUrls = false;
		
	public function __construct($domain = null )
	{
		$this->setDomain($domain);	
			
		set_time_limit(0); 
		//ignore_user_abort(true);		
	}
	
	public function addModel(App_Crawler_Model_Interface $model)
	{
		$this->_models[] = $model;
	}
	
	/**
	 * Устанавливаем домен в пределах которого производим парсинг
	 */
	public function setDomain($name)
	{
		if (null === $name)
			throw new Exception('Необходимо указать домен в пределах которого производим парсинг!');
			
	    // TODO добавить проверку на корректность доменного имени
		$this->_domain = $name;
	}
  
    /**
     * Возврашает модель соответствующюю переданному url
     */
    public function getModel($url)
    {    	
    	foreach ($this->_models as $model) {
    		if ($model->isModelUrl($url))     						
    		    return $model;    		
    	}    	
    	return null;
    }
    
    public function getDbTable() {
        if (null === $this->_dbTable) {
        	
        	$this->setDbTable(App_Crawler_Service::getDbTable(null,null, $this->_serviceTablePostfix));
            //$this->setDbTable($this->_dbTableName);
        }
        return $this->_dbTable;
    }
    
    public function setDbTable($dbTable)
    {       	
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }
    
    public function start($start_url = null)
	{		
		//App_Stat::start();		
		$this->setStartUrl($start_url);	
		$successStatus = array(200, 301, 302);
		$skipStatus    = array(500);
		$banStatus     = array(999);
		$logger = App_Resource::getResource('log');	
		do {	
			while ($this->next()) 
			{								
				//App_Stat::setPoint('Начало цикла');
				$this->createEvent(App_Crawler_Events::START_CYCLE);	// вызываем возникновение события END_CYCLE		      		
	
				//$logger->log('Выделение памяти до : $this->getPage' . memory_get_usage(), 1);
				$page = $this->getPage($this->getUrl());
				//$logger->log('Выделение памяти сразу после : $this->getPage' . memory_get_usage(), 1);
							
				
				//App_Stat::show();			
				// если страница успешно загружена
				if (in_array($page->getStatus(), $successStatus)) {				
					$this->crawler($page);  // собираем ссылки со страницы и добавляем в задание 
					//$logger->log('Выделение памяти - между фазами: ' . memory_get_usage(), 1);							
				    $this->parse($page);	// извлекаем нужные данные				       			
				}
	
				// если страница забанена
				if (in_array($page->getStatus(), $banStatus)) {
					App_Output::send('Нас забанили!!!');
					die;				
				}   
				
				// страница отсутствует или удалена
	            if (in_array($page->getStatus(), $skipStatus)) {
					App_Output::send('Страница удалена или отсутствует.');							
				} 			
				
				//App_Output::send('END_CYCLE');
				// вызываем возникновение события END_CYCLE
				//App_Output::send('Использование памяти: ' . memory_get_usage());
				
    	        
				//$logger->log('Выделение памяти - до unset page: ' . memory_get_usage(), 1);
				
    	        $this->createEvent(App_Crawler_Events::END_CYCLE);	
				unset($page);			

				//$logger->log('Выделение памяти - конец основного цикла: ' . memory_get_usage(), 1);
				// задержка между запросами перенесеня в App_Crowler_Proxy_Service
				//$this->sleep();	
				//App_Stat::setPoint('Конец цикла');							
			}		
			//App_Stat::show();	
			sleep(2);
			//App_Output::send('END_QUEUE');
			$this->createEvent(App_Crawler_Events::END_QUEUE);
			
		} while ($this->_hasCicleUrls && !$this->_is_exit);	
	    // если очередь пуста начинаем сначала
		$this->setStartUrl($start_url);	
		//App_Output::send('Конец парсинга');	
	}
	
	/**
	 * Устанавливает стартовую страницу для парсинга.  
	 */
	protected function setStartUrl($url = null)
	{
		// если параметр не передан пробуем угадать home page домена
		if (null === $url) {
			$url = 'http://' . $this->_domain;
		}
		
		// если ссылка не известна - добавляем ее в задание
		if ($this->isUnknownUrl($url))
			$this->addUrlToQueue($url);
	}
	
	/**
	 * Возвращает текущий url 
	 */
	public function getUrl()
	{
		return $this->_current_url;
	}
	
	protected function next()
	{			
        // если прервано пользователем 
		if ($this->_is_exit)
			return false;
		
		$url = $this->getCurrentUrl();
		if (!empty($url)) {
			$this->_current_url = $url;
			return true;
		}		
		return false;			
	}
	
	/**
	 * функция для тестирования 
     * парсит одну страницу
	 */
	public function parsePage($url)
	{
		$page = $this->getPage($url);
		// если страница успешно загружена
		if ($page->getStatus() == 200) {			
		    $this->parse($page);	 // извлекаем нужные данные		
		    //echo '<pre>Ссылки на странице:'; print_r($page->getLinks()); echo '</pre>';		   		
		}
		else {
			echo 'Status загрузки = ' . $page->getStatus() . '<br />';			
		}   			
		// вызываем возникновение события END_CYCLE
		$this->createEvent(App_Crawler_Events::END_CYCLE);
	}
	
	
	/**
	 * помещаем url в конец очереди, чтобы не циклится на не доступных страницах
	 */
	protected function skipUrl($url)
	{
		// удаляем текущий url из очереди
		$this->getQueue()->deleteMessage($this->_message); 
		
		// добавляем его в конец очереди
		$this->addUrlToQueue($url, true);
	}
		
	/**
	 * Собираем со страницы все подходящие ссылки
	 * и добавляем их в очередь на парсинг.
	 * Подходящие ссылки: новые, внутренние, не соответствующие no_index_pattern   
	 */
	protected function crawler(App_Crawler_HtmlPage $page)
	{
		// заглушка позволяющая не извлекать ссылки с некоторых страниц
		$model = $this->getModel($page->getUrl());	
       	if (null != $model && $model->_not_crawler_this_page) {    	
    		//echo 'Ссылки не были собраны <br />';    		
       		return;		
       	} 			
		unset($model);
		
		// извлекаем все ссылки содержащиеся на странице 
		//$logger = App_Resource::getResource('log');
		//$logger->log('Выделение памяти: ' . memory_get_usage(), 1);
		$links = $page->getLinks();   	
		foreach($links as $link) {			
			// если ссылка попадает в список индексируемых и к ней не применимы исключения
			if ($this->isIndexUrl($link) && !$this->isNoIndexUrl($link)) {				
				// добавляем в задание на парсинг все не известные адреса
				if ($this->isUnknownUrl($link))
			        $this->addUrlToQueue($link);
			}						
		}	   
		unset($links);   	
	}
	
	/**
	 * Тест на соответствие no_index_pattern (ссылки не подлежащие индексации)
	 * (тупиковые, дублирующие, не требующие индексаци ссылки)
     */
	protected function isNoIndexUrl($url)
	{
	    foreach ($this->_no_index_url_pattern_str as $pattern) {
	    	if (strpos($url, $pattern)) 					
				return true;						        
		}		
		return false;
	}
	
	/**
	 * Тест на соответствие index_pattern (ссылки подлежащие индексации)
     */
	protected function isIndexUrl($url)
	{
		//App_Output::send($url);
		if (empty($this->_index_url_pattern_str))
			return true;
			
	    foreach ($this->_index_url_pattern_str as $pattern) {
			if (strpos($url, $pattern)) 					
				return true;			        
		}		
		return false;
	}
	
	
	
	protected function parse(App_Crawler_HtmlPage $page)
	{			
		//App_Output::send('$page->getUrl() = ' . $page->getUrl());
		$model = $this->getModel($page->getUrl());	
       	if (null != $model) {
       		$model->setDom($page->getDom()) 
       		      ->setUrl($page->getUrl())
       		      ->save();       		
       	} 	
       	// удаляем текущий url из очереди
       	if ($this->_message != null) {
		    $this->getQueue()->deleteMessage($this->_message);
		    $this->_message = null;
       	}	       	
	}
	
	/**
	 * Определяем известна ли нам ссылка
	 */
	protected function isUnknownUrl($url)
	{		
		$table = $this->getDbTable();
		$select = $table->select();
		$select->where("queue = ?", $this->_domain)
		       ->where("url = ?", $url);
		$row = $table->fetchRow($select); 	
        unset($table);
        unset($select);
		
		if (!empty($row))
		    return false;		
		return true;
	}
	
	protected function sleep()
	{
		$sec = rand(5,10);
		sleep($sec);
	}
	
	/**
	 * возвращает объект App_Crowler_PageHtml (экземпляр страницы)
	 * соответствующий url 
	 */
	protected function getPage($url)
	{		
    	$page = new App_Crawler_HtmlPage($this->_domain, $url); 
    	return $page;
	}
	
	
	/**
	 * возвращаем url текущей страницы для парсинга
	 */
	protected function getCurrentUrl() 
	{			
		//echo '<pre>$this->_extra_urls'; print_r($this->_extra_urls); echo '</pre>';
		// если имеются внеочередные страницы - возвращаем их
		if (!empty($this->_extra_urls)) {			
			return array_shift($this->_extra_urls);
		}	
						
		// иначе берем из очереди 
		return $this->_getCurrentUrl();			
	}
	
	
	/**
	 * Выборка нового url из очереди и сохранение объекта 
	 * очереди для дальнейшей работы 
	 */
	protected function _getCurrentUrl() {
		$url = null;
		$messages = $this->getQueue()->receive(1);
		foreach ($messages as $message) {
			$url = $message->body;			
            $this->_message = $message;
		}
		return $url; 	
	}

    /**
     * возвращеет очередь страниц для парсинга
     */
    protected function getQueue() {    	
    	if (null === $this->_queue) {    		
			$this->_queue = App_Crawler_Service::getQueue($this->_domain);
    	}
    	return $this->_queue;
    }
    
    
    
    /**
     * Очистка очереди страниц для парсинга
     */
    public function clear()
    {    	
    	$this->getQueue()->deleteQueue($this->_domain);
    	$this->_queue = null;
    }
    
    /*protected function clearQueueAndStartAgain()
    {
    	$this->clear();
    	$this->getQueue()->send('http://' . $this->_domain);    	
    }*/
    
    protected function addUrlToQueue($url, $is_skip_url = false)
    {
    	// добавляем url в задание на парсинг
    	$this->getQueue()->send($url);
    	
    	// сохраняем url в таблице известных ссылок
    	if (!$is_skip_url)  // иначе это уже известный адрес
    	{
    		$table = $this->getDbTable();
	    	$data = array(
			    'queue' => $this->_domain,
			    'url'   => $url,		    
			);		 
			$table->insert($data);	
    	}
    	
    }
    
    /**
     * Добавляем наблюдателя прослушивающего событие $event_type
     */
    public function addObserver($event_type, App_Crawler_Observer_Interface $observer)
    {
    	$this->_observers[$event_type][] = $observer;
    }
    
    /**
     * Функция инициирует событие $event_type 
     * и его обработку соответствующими наблюдателями
     */
    protected function createEvent($event_type)
    {    	
	    if(isset($this->_observers[$event_type]) && is_array($this->_observers[$event_type] ) )
	    {
	        foreach ($this->_observers[$event_type] as $oserver )
	        {
	            $oserver->notify($this,$event_type);
	        }
	    }
    }
    
    /**
     * добовляем url для парсинга вне очереди.
     */
    public function addExtraUrl($url)
    {
    	$this->_extra_urls[] = $url;
    }    
    
    /**
     * Устанавливает время (в секундах), по истечении которого процесс парсинга будет прекращен
     */
    public function stopByTime($time = 0)
    {
    	$time = (int)$time; 
    	if ($time > 0 ) {
    		$this->addObserver(App_Crawler_Events::END_CYCLE, new App_Crawler_Observer_StopByTime($time));
    	}
    }
    
    /**
     * Устанавливает количество загрузок страниц, по истечении которого процесс парсинга будет прекращен
     */
    public function stopByPageCnt($cnt = 0)
    {
    	$cnt = (int)$cnt; 
    	if ($cnt > 0 ) {
    		$this->addObserver(App_Crawler_Events::END_CYCLE, new App_Crawler_Observer_StopByPageCnt($cnt));
    	}
    }
    
    /**
     * Устанавливает режим вывода на экран отладочной информации
     */
    public function consoleLog($turn_on = false)
    {
    	$turn_on = (boolean)$turn_on; 
    	if ($turn_on) {
    		$this->addObserver(App_Crawler_Events::START_CYCLE, new App_Crawler_Observer_ConsoleLog());
    		$this->addObserver(App_Crawler_Events::END_CYCLE,   new App_Crawler_Observer_ConsoleLog());
    	}
    	// TODO дописать процедуру отключения
    }
    
    /**
     * Задает страницы которые необходимо парсить через заданный промежуток времени
     */
    public function cycleParsing($url, $interval)
    {
    	$this->_hasCicleUrls = true;
    	$handler = new App_Crawler_Observer_CycleParsing($url, $interval);
    	$this->addObserver(App_Crawler_Events::END_CYCLE, $handler);  
    	$this->addObserver(App_Crawler_Events::END_QUEUE, $handler);	
    }
    
   
    /**
     * Установка глобальных параметров проекта (будут доступны в каждой модели)
     */
    public function setProjectParams(array $params)
    {
    	$this->_project_params = $params;
    }
    
    /**
     * Получение глобальных параметров проекта (будут доступны в каждой модели)
     */
    public function getProjectParams()
    {
    	return $this->_project_params;
    }
    
    /**
     *  Возвращает true если парсинг завершен (очередь задач пуста)
     */
    public function isComplete() {
    	$cnt = $this->getQueue()->count();    	
    	if ($cnt > 0) 
    		return false;    		
    	return true;
    }
    
    /**
     * Вызов этого метода прграммами обработчиками событий вызовет прекращение процесса парсинга
     */
    public function _exit()
    {
    	$this->_is_exit = true;
    }
}