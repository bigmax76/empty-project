<?php
class App_Html_Crawler extends App_Html_Crawler_Abstract
{	
    protected $_store = '/../data/crawler/crawler.sqlite';
	
    public function next() {
    	$url = $this->getNextUrl();
    	if (empty($url)) return false;
    	
    	$content = Model_Proxy_Service::getContent($url);    	
    	// если все ок собираем новые ссылки и возвращаем ответ
    	if ('200' == $content->getStatus()) {
    		$page  = new App_Html_Page($content);
    	    $links = $page->getAttrib('href', 'a');
    	    $this->addToQueue($links);
    	    $this->removeUrl();
    	    return $page;
    	}
    	
    	// если страница не найдена - удаляем ее из очереди и отдаем следующую
        if ('404' == $content->getStatus()) {    		
    	    $this->removeUrl();
    	    return $this->next;
    	}
    	
    	$this->skipUrl();
    	return $this->next;
    }
        
	public function addIndexPattern($pattern) {
		if (!empty($pattern))
			$this->_index_pattern[] = $pattern;
	}
	
    public function addNoIndexPattern($pattern)
	{
		if (!empty($pattern))
			$this->_no_index_pattern[] = $pattern;
	}
	
    /**
     * Переменная $name будет удалена из ссылок при добавлении в очередь 
     */
    public function removeParam($name){
    	if (!empty($name))
			$this->_remove_param[] = $name;
    }
    
	/**
	 * Url текущей страницы
	 */
	public function getCurrentUrl() {
		return $this->_currentUrl->getUri();
	}
	
	/**
	 * Воззвращает ссылки с текущей страници подлежащие индексации 
	 */
	public function getLinks() {
		return $this->_links;
	}

    public function __toString()
    {
    	$links = print_r($this->getLinks(), true);
    	$res = '<div style="text-align:left;font-size;14px;font-family: Arial">'
    	     . 'Парсится страница: ' . $this->getCurrentUrl() . '<br>'
             . 'Статус загрузки: <br>'
             . 'Ссылки собранные для индексации: <br>'		
             . '<pre>' . $links . '</pre>'
             . '</div>';

	    return $res;
    }
}