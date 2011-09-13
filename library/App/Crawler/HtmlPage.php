<?php
class App_Crawler_HtmlPage
{	        
	/**
	 * домен который парсим
	 */
	protected $_domain = '';
	
	/**
	 * url запрашиваемой страницы
	 * (возможно лучше заменить на _url)
	 */
	protected $_page = null;
	
	/**
	 * массив моделей отвечающих за парсинг проекта
	 */
	protected $_models = array();
	
	/**
	 * ответ сервера
	 */
	protected $_response = null;
	
	/**
	 * @var unknown_type
	 */
	protected $_dom = null;
		
	/**
	 * @param Домен который парсим $domain
	 * @param url страницs, которую парсим $page
	 */
	public function __construct($domain, $page)
	{
		$this->_domain = $domain;
		$this->_page    = $page;
	}
	
	public function getUrl()
	{
		return $this->_page;
	}
	
	public function addModel(App_Crawler_Model_Interface $model)
	{
		$this->_models[] = $model;
	}
	
	public function setDomain($name)
	{
		$this->_domain = $name;
	}
	
	public function getResponse()
	{
		if (null === $this->_response)
    	{
    		$client          = $this->getClient();
	    	$this->_response = $client->request();
    	}
    	return $this->_response;
	}
	
	protected function getClient()
	{		
		$options = array(
	    		'maxredirects' => 0,
	            'timeout'      => 30,
	    	);    	
        $page = $this->getProxyUrl($this->_page);
       
	    $client = new Zend_Http_Client($page , $options);	
	    //$client = new Zend_Http_Client($this->_page , $options);
	    return $client;
	}
	
	protected function getProxyUrl($url)
	{		
		$link = str_replace(array("?","&"), array("___________", "::::"), $url) . "&with_replace=1";		
		$proxy = App_Crawler_Proxy_Service::getAvalableProxy();
		$url = $proxy . $link;
		//echo $url;
		//die;
		return $url;
		
	}
	
	protected function createBuzyTrigger($file)
	{
		$handle = fopen($file,"w");  	
	   	if ($handle === false) 
	   		throw new Exception('Не удалось создать файл ' . $file);
	   	fclose($handle);
	}
	
    /**
     * возвращает html запрашиваемой страницы
     */	
    public function getHtml()
    {
    	return $this->getResponse()->getBody();	       	
    }
    
    public function getStatus()
    {    	
    	return $this->getResponse()->getStatus();	
    }
    
    /**
     * извлекаем ссылки содержащиеся на странице 
     */
    public function getLinks($skip_external = true)
    {
    	$result = array();
    	
    	// получаем все таги <a> 
    	$elements = $this->getDom()->query('a');
        
    	//$domain = 'www.maxbuild.kh.ua';  	  
        $url_filter = new App_Filter_HrefToAbsolutUrl($this->_domain, $this->_page);
       	    
    	// обрабатываем каждую полученную ссылку
        foreach ($elements as $element) 
        {                 	
        	$url = $element->getAttribute('href'); 
        	$url = $url_filter->filter($url, true);
        	if (empty($url)) continue;
        	
        	$result[] = $url;        	
        }    
        // удаляем дубликаты и сортируем
        $result = array_unique($result);
        sort($result);
        
        unset($url_filter);
        
        return $result;
    }
    
    public function getDom()
    {
    	if (null === $this->_dom)
    	{    		
    		$dom = new Zend_Dom_Query();    		
    	    $dom->setDocument($this->getHtml());     	    
    	    $this->_dom = $dom;    	   
    	}    	
    	return $this->_dom; 
    }
    
    /**
     * Возврашает модель соответствующюю переданному url
     */
    public function getModel($url)
    {
    	foreach ($this->_models as $model) {
    		if ($model->isModelUrl($url)) {    			
    		    return $model;
    		}
    	}
    	
    	return null;
    }
}