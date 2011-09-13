<?php
class App_Html_Crawler_Abstract
{
	protected $_store;
	protected $_index_pattern    = array();
	protected $_no_index_pattern = array();
	protected $_remove_param     = array();
	
	protected $_adapter;
	protected $_queue;
	protected $_host;
	protected $_dbTableName;
	protected $_currentUrl;	
	protected $_queueMsg;   // текущий элемент очереди
	protected $_links;      // подходящие ссылки найденные краулером на текущей странице
	
		
	public function __construct($url) 
	{
		if (!Zend_Uri_Http::check($url)) 		
			throw new App_Html_Crawler_Exception("'$url' is not a valid HTTP URI");
		
		$uri = Zend_Uri::factory($url);
		$this->_host = $uri->getHost();
		$this->createServiceBase();
		$this->append($url);
	}
	
	protected function getAdapter() {
		if (null === $this->_adapter) {			
			$adapter = Zend_Db::factory('Pdo_Sqlite', $this->getOptions());
			$this->setAdapter($adapter);
		}
		return $this->_adapter;
	}

	protected function getQueue() {
		if (null === $this->_queue) {
			$options = array(
			    'name'          => $this->_host,
			    'driverOptions' => $this->getOptions(),
            );
			$queue = new Zend_Queue('Db', $options);
			$this->setQueue($queue);
		}
		return $this->_queue;
	}

	protected function setAdapter(Zend_Db_Adapter_Pdo_Sqlite $_adapter) {
		$this->_adapter = $_adapter;
		return $this;
	}

	protected function setQueue(Zend_Queue $_queue) {
		$this->_queue = $_queue;
		return $this;
	}
	
    protected function createServiceBase()
    {
    	$db = $this->getAdapter();
    	    	
    	$sql = 'CREATE TABLE IF NOT EXISTS queue (
				  queue_id INTEGER PRIMARY KEY AUTOINCREMENT,
				  queue_name VARCHAR(100) NOT NULL,
				  timeout INTEGER NOT NULL DEFAULT 30
				)';
        $db->query($sql);

		$sql = 'CREATE TABLE IF NOT EXISTS message (
				  message_id INTEGER PRIMARY KEY AUTOINCREMENT,
				  queue_id INTEGER,
				  handle CHAR(32),
				  body VARCHAR(8192) NOT NULL,
				  md5 CHAR(32) NOT NULL,
				  timeout REAL,
				  created INTEGER,
				  FOREIGN KEY (queue_id) REFERENCES queue(queue_id)
				)';
	    $db->query($sql);
	    
	    $sql = 'CREATE TABLE IF NOT EXISTS ' . $this->getDbTableName() . ' (
				  id  INTEGER PRIMARY KEY AUTOINCREMENT,
				  url VARCHAR(200) NOT NULL
				)';
        $db->query($sql);
    }

	protected function getOptions()
	{
		$options = array(
		    'host'     => 'localhost',
		    'type'     => 'pdo_sqlite',
		    'username' => 'crawler',
		    'password' => 'pass45word',
		    'dbname'   => APPLICATION_PATH . $this->_store,
		);
		return $options;
	}    

    /**
     * Добавляет url к заданию
     */
    protected function append($url) {
    	if ($this->isUnknownUrl($url)) {
    	    $this->getQueue()->send($url);
    	    $this->saveUrl($url);
    	}
    }
    
    protected function isUnknownUrl($url)
    {
    	$db = $this->getAdapter();
    	$sql = 'SELECT * FROM ' . $this->getDbTableName() 
    			     . ' WHERE `url` = "' . $url . '" ';
        $res = $db->fetchRow($sql);        
        if (empty($res))
        	return true;
        return false;        
    }
    
    /**
     * добавляем url к базе известных адресов
     */
    protected function saveUrl($url) {
    	$db = $this->getAdapter();		 
		$db->insert($this->getDbTableName(), array('url' => $url));
    }
    
    protected function getDbTableName() {
    	if (null === $this->_dbTableName) {
    		$this->_dbTableName = str_replace( '.', '_', $this->_host);
    	}
    	return $this->_dbTableName;
    } 
    
    protected function filterUrl(array $urls) 
    {
    	$result = array();
        foreach ($urls as $url) {
    		// пропускаем все внешние ссылки
    		if (Zend_Uri::check($url))
    			continue;
    		// пропускаем ссылки соответствующие no_index_pattern
    		if ($this->isNoIndexUrl($url))
    		    continue;
    		// пропускаем ссылки не соответствующие index_pattern
    		if (!$this->isIndexUrl($url))
    		    continue;    		   
    		$result[] = $url;    		
    	}
    	// удаляем дубликаты
    	$result = array_unique($result);
    	return $result;
    }
    
    /**
     * Тест на соответствие index_pattern (ссылки подлежащие индексации)
     */
    protected function isIndexUrl($url) 
    {    	
    	if (empty($this->_index_pattern))
    		return true;    	
    	foreach ($this->_index_pattern as $pattern) {
			if (strpos($url, $pattern) !== false) 					
				return true;			        
		}		
		return false;    		
    }
    
	/**
	 * Тест на соответствие no_index_pattern (ссылки не подлежащие индексации)
	 * (тупиковые, дублирующие, не требующие индексаци ссылки)
     */
	protected function isNoIndexUrl($url) {
	    foreach ($this->_no_index_pattern as $pattern) {
	    	if (strpos($url, $pattern) !== false) 					
				return true;						        
		}
		return false;
	}
	
    protected function getNextUrl()
    {
    	$queueMsg = $this->getQueue()->receive(1)->current();
    	if (empty($queueMsg)) return;
    	
    	$this->_queueMsg   = $queueMsg;
    	$this->_currentUrl = Zend_Uri::factory($queueMsg->body);
    		
    	return $this->getCurrentUrl();
    }
   
    protected function addToQueue(array $links)
    {    	
    	$links = $this->filterUrl($links);   // отсеиваем неподходящие ссылки
    	$links = $this->normalize($links);   // и нормализуем оставшиеся (приводим к абсолютному виду)
    	$links = $this->removeParams($links);// удаляем из url указанные пользователем переменные
    	$links = array_unique($links);
    	$this->_links = $links;
    	foreach ($links as $link) {
    		$this->append($link);
    	}    	
    }
    
    /**
     * Ставит текущий url в конец очереди
     */
    protected function skipUrl()
    {
    	$this->append($this->_currentUrl);
    	$this->removeUrl();
    	
    }
    	
    protected function normalize($links)
    {    	
    	$result = array();
        foreach ($links as $link) {
        	// формируем ссылку относительно корня
    		if (strpos($link, '/') == 0) {
				$link = $this->_currentUrl->getScheme(). '://' .  $this->_host . $link;
    		} else {
    			$link = $this->_currentUrl->getUri() . '/' . $link;
    		}				
			$result[] = $link;
    	}
    	return $result;
    }
    
    /**
     * Удаляет из урл указанные пользователем переменные
     */
    protected function removeParams($links)
    {
    	$result = array();
        foreach ($links as $link) {
        	$uri = Zend_Uri_Http::factory($link);
        	$query = $uri->getQueryAsArray();        	
        	foreach ($query as $key => $val) {
        		if (in_array($key, $this->_remove_param))
        			unset($query[$key]);
        	}
        	$uri->setQuery($query);        	
			$result[] = $uri->getUri();
    	}
    	return $result;
    }
    
    /**
     * Удаляет текущий url из очереди
     */
    protected function removeUrl() {
    	$this->getQueue()->deleteMessage($this->_queueMsg);
    }

}